<?php

namespace App\Providers;

use App\MicroApi\Services\UserService;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cashier::useCurrency(config('cart.currency'), config('cart.currency_symbol'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 以单例模式绑定 HttpClient 实例到 App 容器
        $this->app->singleton('HttpClient', function ($app) {
            return new HttpClient([
                'base_uri' => config('services.micro.api_gateway'),
                'timeout'  => config('services.micro.timeout'),
                'headers'  => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        });

        // 以单例模式绑定用户底层服务到服务容器
        $this->app->singleton('microUserService', function ($app) {
            return new UserService();
        });
    }
}
