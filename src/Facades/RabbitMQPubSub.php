<?php

namespace Cerpus\LaravelRabbitMQPubSub\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void publish(string $topicName, string $data)
 * @method static void setupConsumer()
 * @method static void listenWithConsumersSetUp()
 * @see \Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub
 */
class RabbitMQPubSub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub::class;
    }
}
