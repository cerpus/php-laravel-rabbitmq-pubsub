<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPubSub
{
    private AMQPStreamConnection $connection;
    private array $channels = [];

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
    }

    private function getTopicChannel(string $topicName): AMQPChannel
    {
        if (!array_key_exists($topicName, $this->channels)) {
            $channel = $this->connection->channel();
            $channel->exchange_declare($topicName, 'fanout', false, true, false);
            $this->channels[$topicName] = $channel;
        }

        return $this->channels[$topicName];
    }

    public function publish(string $topicName, string $data)
    {
        $this->getTopicChannel($topicName)->basic_publish(new AMQPMessage($data), $topicName);
    }
}
