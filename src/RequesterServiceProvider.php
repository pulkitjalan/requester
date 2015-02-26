<?php

namespace PulkitJalan\Requester;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class RequesterServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->app['PulkitJalan\Requester\Requester'] = function ($app) {
            return $app['requester'];
        };

        $this->publishes([
            __DIR__.'/config/config.php' => config_path('requester.php'),
        ], 'config');

        if (config('requester.log.enabled')) {
            $logger = $this->app['log']->getMonolog();

            if (!empty(config('requester.log.file'))) {
                $logger->pushHandler(new StreamHandler(config('requester.log.file'), Logger::INFO));
            }

            $this->app['requester']->addLogger($logger, config('requester.log.format'));
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'requester');

        $this->app['requester'] = $this->app->share(function ($app) {
            return new Requester(new GuzzleClient(), config('requester'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['requester', 'PulkitJalan\Requester\Requester'];
    }
}
