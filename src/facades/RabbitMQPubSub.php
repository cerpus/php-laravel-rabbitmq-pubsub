<?php

namespace Cerpus\LaravelRabbitMQPubSub\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;

class RabbitMQPubSub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rabbitMQPubSub';
    }

    public function publish(string $topic, $data)
    {
        Log::info("Publish message to $topic");
    }
}
