<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Commands\ConsumerCommand;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class RabbitMQPubSubServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsumerCommand::class,
            ]);
        }
    }

    public function register()
    {
        App::singleton(RabbitMQPubSub::class, function (Application $app) {
            return new RabbitMQPubSub($app, $app->make(RabbitMQConnectionManager::class));
        });

        App::singleton(RabbitMQConnectionManager::class, function () {
            return new RabbitMQConnectionManager();
        });
    }
}
