<?php

namespace Cerpus\LaravelRabbitMQPubSub\Facades;

use Illuminate\Support\Facades\Facade;

class RabbitMQPubSub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rabbitMQPubSub';
    }
}
