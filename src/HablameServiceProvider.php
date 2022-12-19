<?php

namespace Sideso\Hablame;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Sideso\Hablame\Hablame;
use Sideso\Hablame\HablameChannel;

class HablameServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(Hablame::class, static function ($app) {
            return new Hablame(
                account: $app['config']['services.hablame.account'],
                apiKey: $app['config']['services.hablame.api_key'],
                token: $app['config']['services.hablame.token'],
                httpClient: new HttpClient(),
                sourceCode: $app['config']['services.hablame.source_code']
            );
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('hablame', function ($app) {
                return new HablameChannel($app[Hablame::class]);
            });
        });
    }
}
