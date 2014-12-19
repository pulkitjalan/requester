<?php

namespace PulkitJalan\Requester;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as GuzzleClient;

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
        $this->app[Requester::class] = function ($app) {
            return $app['requester'];
        };
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->config->package('pulkitjalan/requester', realpath(__DIR__.'/config'), 'requester');

        $this->app['requester'] = $this->app->share(function ($app) {
            return new Requester(new GuzzleClient(), $config = $app->config->get('requester::config'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['requester', Requester::class];
    }
}
