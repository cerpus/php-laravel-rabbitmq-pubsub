<?php

declare(strict_types=1);

namespace Cerpus\LaravelRabbitMQPubSub\Exceptions;

class MissingConfigException extends LaravelRabbitMQPubSubException
{
    public static function create(string $key): self
    {
        return new self('Missing config ' . $key);
    }
}
