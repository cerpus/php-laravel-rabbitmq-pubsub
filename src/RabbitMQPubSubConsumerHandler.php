<?php

namespace Cerpus\LaravelRabbitMQPubSub;

interface RabbitMQPubSubConsumerHandler
{
    public function consume(string $data);
}
