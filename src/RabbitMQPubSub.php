<?php

declare(strict_types=1);

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\PubSub\PubSub;

/**
 * @deprecated Use {@link \Cerpus\PubSub\PubSub} instead.
 */
class RabbitMQPubSub
{
    public function __construct(private PubSub $pubSub)
    {
    }

    public function setupConsumer(): void
    {
        // no-op, kept for backward compat
    }

    public function publish(string $topicName, string $data): void
    {
        $this->pubSub->publish($topicName, $data);
    }
}
