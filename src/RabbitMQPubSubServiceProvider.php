<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Commands\ConsumerCommand;
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
        App::bind('rabbitMQPubSub', function () {
            return new RabbitMQPubSub();
        });
    }
}
