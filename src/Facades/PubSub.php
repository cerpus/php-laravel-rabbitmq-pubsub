<?php

declare(strict_types=1);

namespace Cerpus\LaravelRabbitMQPubSub\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void publish(string $topic, string $data)
 * @method static void subscribe(string $name, string $topic, Closure $handler)
 * @method static void listen()
 * @method static void close()
 *
 * @see \Cerpus\PubSub\PubSub
 */
class PubSub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Cerpus\PubSub\PubSub::class;
    }
}
