<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Jaeger\Config;
use Jaeger\Sampler\ProbabilisticSampler;
use Jaeger\Transport\TransportUdp;
use const OpenTracing\Formats\TEXT_MAP;

class JaegerServiceProvider extends ServiceProvider
{
    /**
     * laravel 在 bootstrap 中, 会先注册所有的 provider, 再调用所有 provider 的 boot
     * 而在 provider 中, laravel 有 Event / Log / Routing 三个内置的 base provider
     * 在接入 jaeger 的逻辑中, 除了初始化 trace 和创建全局 span 以外, span 和 trace 的提交都是通过事件监听完成
     * 因此为了获取更加接近于请求实际的跟踪信息 (主要是请求时间) 以及避免其他无法预料的错误
     * 需要将 JaegerServiceProvider 在 app.php 中配置为第一个 provider, 并在 register 中进行初始化.
     *
     * @see https://github.com/mauri870/laravel-jaeger-demo/blob/master/app/Providers/AppServiceProvider.php
     */
    public function register()
    {
        $config = Config::getInstance();

        try {
            $tracer = $config->initTracer(config('app.name'), config('services.jaeger.agent'));
        } catch (\Exception $exception) {
            Log::error("Init tracer failed: " . $exception->getMessage());
            return;
        }

        $tags = [
            'span.kind' => 'server',
            'type' => 'fpm',
        ];
        $operationName = '';

        if (app()->runningInConsole()) {
            $tags['type'] = 'cli';
            $this->registerCommandStartingListener();
        } else {
            $tags['http.url'] = $operationName = request()->getPathInfo();
        }

        $spanContext = $tracer->extract(TEXT_MAP, $_SERVER);
        try {
            $span = $tracer->startSpan($operationName, [
                'child_of' => $spanContext,
                'tags' => $tags,
            ]);
        } catch (\Exception $exception) {
            Log::warning("Start span with context failed: " . $exception->getMessage());
            // 如果 spanContext 为空，则将当前 span 作为 root span
            $span = $tracer->startSpan($operationName);
        }
        $span->addBaggageItem("version", "2.0.0");
        $tracer->inject($span->getContext(), TEXT_MAP, $_SERVER);

        $this->app->instance('jaeger.config', $config);
        $this->app->instance('jaeger.tracer', $tracer);
        $this->app->instance('jaeger.span', $span);
        $this->app->instance('jaeger.flushed', false);

        $this->registerRequestHandledListener();
        $this->registerMessageLoggedListener();
        $this->registerTerminateHandler();
        $this->registerQueueJobProcessListener();
    }

    /**
     * 消息队列消费记录
     * 由于队列使用了异步信号监听, 会导致 register_shutdown_handler() 失效, 所以选择在执行完一个 job 之后 flush 一次
     */
    protected function registerQueueJobProcessListener()
    {
        $span = null;

        Event::listen(JobProcessing::class, function (JobProcessing $event) use (&$span) {
            $tracer = $this->app->get('jaeger.tracer');
            $spanName = sprintf('job.%s', $event->job->resolveName());
            $span = $tracer->startSpan($spanName, [
                'child_of' => $this->app->get('jaeger.span'),
                'tags' => [
                    'span.kind' => 'server',
                    'type' => 'cli',    // 这里暂时不考虑 sync 的情况
                    'job.name' => $event->job->getName(),
                    'job.id' => $event->job->getJobId(),
                ],
            ]);
            $tracer->inject($span->spanContext, TEXT_MAP, $_SERVER);
        });

        Event::listen(JobProcessed::class, function () use (&$span) {
            $span->finish();
            $span = null;
            $tracer = $this->app->get('jaeger.tracer');
//            $tracer->spanThrifts = [];
            $tracer->flush();
        });

        $failListener = function ($event) use (&$span) {
            $span->setTag('error', true);
            $span->log([
                'exception' => $event->exception->getMessage(),
            ]);
            $span->finish();
            $span = null;
            $tracer = $this->app->get('jaeger.tracer');
            $tracer->spanThrifts = [];
            $tracer->flush();
        };

        Event::listen(JobFailed::class, $failListener);
        Event::listen(JobExceptionOccurred::class, $failListener);
    }

    /**
     * 当处于 cli 模式下运行时, 匹配到 command 之后将 command.name 作为 span name.
     */
    protected function registerCommandStartingListener()
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $this->app->get('jaeger.span')->overwriteOperationName($event->command);
            $this->app->get('jaeger.span')->setTag('command.name', $event->command);
        });
    }

    /**
     * 注册请求相关事件, 如果命中路由, 则将路由名作为 spanName.
     */
    protected function registerRequestHandledListener()
    {
        Event::listen(RouteMatched::class, function (RouteMatched $event) {
            $this->app->get('jaeger.span')->overwriteOperationName('/' . ltrim($event->request->route()->uri(), '/'));
        });
        Event::listen(RequestHandled::class, function (RequestHandled $event) {
            $this->app->get('jaeger.span')->setTag('http.status', $event->response->getStatusCode());
        });
    }

    /**
     * 注册日志记录事件, 通过事件标记 span 失败及记录日志.
     */
    protected function registerMessageLoggedListener()
    {
        Event::listen(MessageLogged::class, function (MessageLogged $event) {
            if ('error' === $event->level) {
                $this->app->get('jaeger.span')->setTag('error', true);
                $this->app->get('jaeger.span')->log((array) $event);
            }
        });
    }

    /**
     * 注册退出 callback.
     */
    protected function registerTerminateHandler()
    {
        app()->terminating(function () {
            $this->flushJaegerTracer();
            $this->app->instance('jaeger.flushed', true);
        });

        register_shutdown_function(function () {
            if (!$this->app->has('jaeger.flushed') || $this->app->get('jaeger.flushed')) {
                return;
            }

            $this->flushJaegerTracer();
        });
    }

    protected function flushJaegerTracer()
    {
        $this->app->get('jaeger.span')->finish();
        $this->app->get('jaeger.config')->flush();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
