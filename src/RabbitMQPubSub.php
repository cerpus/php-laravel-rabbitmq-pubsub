<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPubSub
{
    private AbstractConnection $connection;
    private AMQPChannel $channel;
    private array $declaredChannels = [];
    private array $declaredQueues = [];

    /**
     * @throws Exception
     */
    public function __construct(RabbitMQConnectionManager $rabbitMQConnectionManager)
    {
        $this->connection = $rabbitMQConnectionManager->getConnection();
        $this->channel = $this->connection->channel();
    }

    private function ensureTopicIsDeclared(string $topicName): void
    {
        if (!in_array($topicName, $this->declaredChannels)) {
            $this->declaredChannels[] = $topicName;
            $this->channel->exchange_declare($topicName, 'fanout', false, true, false);
        }
    }

    public function publish(string $topicName, string $data): void
    {
        $this->ensureTopicIsDeclared($topicName);
        $this->channel->basic_publish(new AMQPMessage($data), $topicName);
    }

    public function setupConsumer(): void
    {
        if (!config('rabbitMQPubSub.consumers')) {
            throw new LaravelRabbitMQPubSubException("Missing config rabbitMQPubSub.consumers");
        }

        foreach (config('rabbitMQPubSub.consumers') as $topicName => $topicInfo) {
            $this->ensureTopicIsDeclared($topicName);
            if (!array_key_exists('subscriptions', $topicInfo)) {
                throw new LaravelRabbitMQPubSubException("Missing subscriptions key for rabbitMQPubSub.consumers.$topicName");
            }

            foreach ($topicInfo['subscriptions'] as $subscriptionName => $subscriptionInfo) {
                if (in_array($subscriptionName, $this->declaredQueues)) {
                    throw new LaravelRabbitMQPubSubException("Duplicate subscription $subscriptionName");
                }

                $this->declaredQueues[] = $subscriptionName;

                if (!array_key_exists('handler', $subscriptionInfo)) {
                    throw new LaravelRabbitMQPubSubException("Missing handler declaration for rabbitMQPubSub.consumers.$topicName.subscriptions.$subscriptionName");
                }

                if (!class_exists($subscriptionInfo['handler'])) {
                    throw new LaravelRabbitMQPubSubException("Class does not exists: " . $subscriptionInfo['handler']);
                }

                $handler = app()->make($subscriptionInfo['handler']);

                if (!($handler instanceof RabbitMQPubSubConsumerHandler)) {
                    throw new LaravelRabbitMQPubSubException("Handler does not implement RabbitMQPubSubConsumerHandler");
                }

                $this->channel->queue_declare($subscriptionName, false, true, false, false);
                $this->channel->queue_bind($subscriptionName, $topicName);

                $callback = function (AMQPMessage $msg) use ($handler) {
                    $handler->consume($msg->body);
                    $msg->ack();
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
