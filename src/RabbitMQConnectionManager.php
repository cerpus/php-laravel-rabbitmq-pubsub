<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnectionManager
{
    private AbstractConnection $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (!config('rabbitMQPubSub.connection.host')) {
            throw new LaravelRabbitMQPubSubException("Missing config rabbitMQPubSub.connection.host");
        }
        if (!config('rabbitMQPubSub.connection.port')) {
            throw new LaravelRabbitMQPubSubException("Missing config rabbitMQPubSub.connection.port");
        }
        if (!config('rabbitMQPubSub.connection.username')) {
            throw new LaravelRabbitMQPubSubException("Missing config rabbitMQPubSub.connection.username");
        }
        if (!config('rabbitMQPubSub.connection.password')) {
            throw new LaravelRabbitMQPubSubException("Missing config rabbitMQPubSub.connection.password");
        }

        if (config('rabbitMQPubSub.connection.secure', false)) {
            $this->connection = new AMQPSSLConnection(
                config('rabbitMQPubSub.connection.host'),
                config('rabbitMQPubSub.connection.port'),
                config('rabbitMQPubSub.connection.username'),
                config('rabbitMQPubSub.connection.password'),
                config('rabbitMQPubSub.connection.vhost', '/'),
                [
                    'capath' => '/etc/ssl/certs',
                    'fail_if_no_peer_cert' => false,
                    'verify_peer' => false
                ]
            );
        } else {
            $this->connection = new AMQPStreamConnection(
                config('rabbitMQPubSub.connection.host'),
                config('rabbitMQPubSub.connection.port'),
                config('rabbitMQPubSub.connection.username'),
                config('rabbitMQPubSub.connection.password'),
                config('rabbitMQPubSub.connection.vhost', '/')
            );
        }
    }

    /**
     * @return AbstractConnection
     */
    public function getConnection(): AbstractConnection
    {
        return $this->connection;
    }
}
