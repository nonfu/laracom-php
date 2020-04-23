<?php
namespace App\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\RequestOptions;
use OpenTracing\Span;
use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\uri_for;
use const OpenTracing\Formats\TEXT_MAP;

class GuzzleClient extends Client
{
    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return mixed|ResponseInterface
     */
    public function request($method, $uri = '', array $options = [])
    {
        $config = app()->get('jaeger.config');
        $tracer = $config->initTracer('laravel.client.call');

        $spanContext = $tracer->extract(TEXT_MAP, $_SERVER);
        $spanName = $this->generateJaegerSpanName($uri, $options);
        $span = $tracer->startSpan($spanName, [
            'child_of' => $spanContext,
            'tags' => [
                'http.method' => $method,
                'http.url' => $spanName,
                'span.kind' => 'client',
            ],
        ]);

        $traceHeaders = [];
        $tracer->inject($span->spanContext, TEXT_MAP, $traceHeaders);

        $options[RequestOptions::HEADERS] = array_merge($options[RequestOptions::HEADERS] ?? [], $traceHeaders);
        $options[RequestOptions::SYNCHRONOUS] = true;

        return $this
            ->requestAsync($method, $uri, $options)
            ->then(
                function (ResponseInterface $response) use ($span) {
                    $this->finishSpanOnFulfilled($response, $span);

                    return $response;
                },
                function (RequestException $exception) use ($span, $options) {
                    $this->finishSpanOnFailed($exception, $span, $options);

                    throw $exception;
                }
            )
            ->wait();
    }

    private function finishSpanOnFulfilled(ResponseInterface $response, Span $span)
    {
        $span->setTag('http.status_code', $response->getStatusCode());
        $span->finish();
    }

    private function finishSpanOnFailed(RequestException $exception, Span $span, array $options)
    {
        $span->setTag('error', true);
        $span->setTag('http.status_code', $exception->getCode());

        unset($options[RequestOptions::SYNCHRONOUS]);

        $span->log([
            'exception' => $exception->getMessage(),
            'request_options' => json_encode($options),
        ]);
        $span->finish();
    }

    /**
     * 将调用 api 的 host + path 作为 span name.
     *
     * @param $uri
     * @param array $options
     *
     * @return string
     */
    private function generateJaegerSpanName($uri, array $options)
    {
        $options = array_merge(
            [
                'base_uri' => $this->getConfig('base_uri'),
            ],
            $options
        );

        $uri = uri_for(null === $uri ? '' : $uri);

        if (!empty($options['base_uri'])) {
            $uri = UriResolver::resolve(uri_for($options['base_uri']), $uri);
        }

        return $uri->getHost() . $uri->getPath();
    }
}
