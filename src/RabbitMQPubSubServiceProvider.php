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
        App::bind('rabbitMQPubSub', function (Application $app) {
            return new RabbitMQPubSub($app->make(RabbitMQConnectionManager::class));
        });

        App::bind(RabbitMQConnectionManager::class, function () {
            return new RabbitMQConnectionManager();
        });
    }
}
