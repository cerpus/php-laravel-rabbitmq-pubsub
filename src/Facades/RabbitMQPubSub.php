<?php

namespace Cerpus\LaravelRabbitMQPubSub\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @deprecated Use {@link \Cerpus\LaravelRabbitMQPubSub\Facades\PubSub} instead.
 * @see \Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub
 */
class RabbitMQPubSub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub::class;
    }
}
