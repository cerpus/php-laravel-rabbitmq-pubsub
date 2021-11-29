<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPubSub
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private array $declaredChannels = [];
    private array $declaredQueues = [];

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            config('rabbitMQPubSub.connection.host'),
            config('rabbitMQPubSub.connection.port'),
            config('rabbitMQPubSub.connection.username'),
            config('rabbitMQPubSub.connection.password')
        );
        $this->channel = $this->connection->channel();
    }

    private function ensureTopicIsDeclared(string $topicName): void
    {
        if (!in_array($topicName, $this->declaredChannels)) {
            $this->declaredChannels[] = $topicName;
            $this->channel->exchange_declare($topicName, 'fanout', false, true, false);
        }
    }

    public function publish(string $topicName, string $data)
    {
        $this->ensureTopicIsDeclared($topicName);
        $this->channel->basic_publish(new AMQPMessage($data), $topicName);
    }

    public function setupConsumer()
    {
        foreach (config('rabbitMQPubSub.consumers') as $topicName => $topicInfo) {
            $this->ensureTopicIsDeclared($topicName);
            if (!array_key_exists('subscriptions', $topicInfo)) {
                throw new LaravelRabbitMQPubSubException("missing subscriptions key for rabbitMQPubSub.consumers.$topicName");
            }

            foreach ($topicInfo['subscriptions'] as $subscriptionName => $subscriptionInfo) {
                if (in_array($subscriptionName, $this->declaredQueues)) {
                    throw new LaravelRabbitMQPubSubException("Duplicate subscription $subscriptionName");
                }

                if (!array_key_exists('handler', $subscriptionInfo)) {
                    throw new LaravelRabbitMQPubSubException("Missing handler declaration for rabbitMQPubSub.consumers.$topicName.subscriptions.$subscriptionName");
                }

                if (!class_exists($subscriptionInfo['handler'])) {
                    throw new LaravelRabbitMQPubSubException("Class does not exists: " . $subscriptionInfo['handler']);
                }

                $handler = new $subscriptionInfo['handler']();

                if (!($handler instanceof RabbitMQPubSubConsumerHandler)) {
                    throw new LaravelRabbitMQPubSubException("Handler does not implement RabbitMQPubSubConsumerHandler");
                }

                $this->channel->queue_declare($subscriptionName, false, true, false, false);
                $this->channel->queue_bind($subscriptionName, $topicName);

                $callback = function ($msg) use ($handler) {
                    $handler->consume($msg->body);
                };

                $this->channel->basic_consume($subscriptionName, '', false, false, false, false, $callback);
            }
        }

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }
}
