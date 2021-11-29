<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class RabbitMQPubSubServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        App::bind('rabbitMQPubSub', function () {
            return new RabbitMQPubSub();
        });
    }
}
