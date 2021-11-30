<?php

namespace Cerpus\LaravelRabbitMQPubSub\Tests\Dummies;

use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;

class HandlerImplementation implements RabbitMQPubSubConsumerHandler
{
    public function consume(string $data)
    {
    }
}
