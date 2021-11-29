<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;

class ServiceProvider extends Facade
{
    public function register()
    {
        App::bind('rabbitMQPubSub', function()
        {
            return new RabbitMQPubSub();
        });
    }
}
