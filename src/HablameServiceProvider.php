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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('hablame.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'hablame');

        $this->app->singleton(Hablame::class, static function ($app) {
            return new Hablame(
                account: $app['config']['hablame.account'],
                apiKey: $app['config']['hablame.api_key'],
                token: $app['config']['hablame.token'],
                httpClient: new HttpClient(),
                sourceCode: $app['config']['hablame.source_code']
            );
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('hablame', function ($app) {
                return new HablameChannel($app[Hablame::class]);
            });
        });
    }
}
