<?php

namespace PulkitJalan\Requester;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;

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

        if ($this->app->config->get('requester::log.enabled')) {
            $logger = $this->app->log->getMonolog();

            if (!empty($this->app->config->get('requester::log.file'))) {
                $logger->pushHandler(new StreamHandler($this->app->config->get('requester::log.file'), Logger::INFO));
            }

            $this->app['requester']->addLogger($logger, $this->app->config->get('requester::log.format'));
        }
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
