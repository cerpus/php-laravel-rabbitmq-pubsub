<?php

declare(strict_types=1);

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Cerpus\LaravelRabbitMQPubSub\Exceptions\MissingConfigException;
use Cerpus\PubSub\PubSub;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;

class RabbitMQPubSub
{
    private array $declaredQueues = [];

    public function __construct(
        private PubSub $pubSub,
        private Container $container,
        private array $consumers,
    ) {
    }

    /**
     * @throws LaravelRabbitMQPubSubException
     * @private
     */
    public function setupConsumer(): void
    {
        foreach ($this->consumers as $topicName => $topicInfo) {
            if (!array_key_exists('subscriptions', $topicInfo)) {
                throw MissingConfigException::create("rabbitMQPubSub.consumers.$topicName.subscriptions");
            }

            foreach ($topicInfo['subscriptions'] as $subscriptionName => $subscriptionInfo) {
                if (in_array($subscriptionName, $this->declaredQueues, true)) {
                    throw new LaravelRabbitMQPubSubException("Duplicate subscription $subscriptionName");
                }
                $this->declaredQueues[] = $subscriptionName;

                if (!isset($subscriptionInfo['handler'])) {
                    throw MissingConfigException::create("rabbitMQPubSub.consumers.$topicName.subscriptions.$subscriptionName.handler");
                }

                try {
                    $handler = $this->container->make($subscriptionInfo['handler']);
                } catch (BindingResolutionException) {
                    throw new LaravelRabbitMQPubSubException('Cannot create handler '.$subscriptionInfo['handler']);
                }

                if (!$handler instanceof RabbitMQPubSubConsumerHandler) {
                    throw new LaravelRabbitMQPubSubException("Handler does not implement RabbitMQPubSubConsumerHandler");
                }

                $this->pubSub->subscribe(
                    $subscriptionName,
                    $topicName,
                    fn(string $data) => $handler->consume($data),
                );
            }
        }
    }

    public function publish(string $topicName, string $data): void
    {
        $this->pubSub->publish($topicName, $data);
    }

    /**
     * @throws LaravelRabbitMQPubSubException
     */
    public function listenWithConsumersSetUp(): void
    {
        $this->setupConsumer();

        $this->pubSub->listen();
    }
}
