<?php

namespace Cerpus\LaravelRabbitMQPubSub;

use Cerpus\LaravelRabbitMQPubSub\Commands\ConsumerCommand;
use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Cerpus\LaravelRabbitMQPubSub\Exceptions\MissingConfigException;
use Cerpus\PubSub\Connection\ConnectionFactory;
use Cerpus\PubSub\Connection\ConnectionInterface;
use Cerpus\PubSub\PubSub;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RabbitMQPubSubServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsumerCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton(PubSub::class, function () {
            /** @var ConnectionInterface $connection */
            $connection = $this->app->make(ConnectionInterface::class);
            $pubSub = new PubSub($connection);

            $consumers = config('rabbitMQPubSub.consumers')
                ?? throw MissingConfigException::create('rabbitMQPubSub.consumers');

            $declaredQueues = [];

            foreach ($consumers as $topicName => $topicInfo) {
                if (!array_key_exists('subscriptions', $topicInfo)) {
                    throw MissingConfigException::create("rabbitMQPubSub.consumers.$topicName.subscriptions");
                }

                foreach ($topicInfo['subscriptions'] as $subscriptionName => $subscriptionInfo) {
                    if (in_array($subscriptionName, $declaredQueues, true)) {
                        throw new LaravelRabbitMQPubSubException("Duplicate subscription $subscriptionName");
                    }
                    $declaredQueues[] = $subscriptionName;

                    if (!isset($subscriptionInfo['handler'])) {
                        throw MissingConfigException::create("rabbitMQPubSub.consumers.$topicName.subscriptions.$subscriptionName.handler");
                    }

                    try {
                        $handler = $this->app->make($subscriptionInfo['handler']);
                    } catch (BindingResolutionException) {
                        throw new LaravelRabbitMQPubSubException('Cannot create handler '.$subscriptionInfo['handler']);
                    }

                    if (!$handler instanceof RabbitMQPubSubConsumerHandler) {
                        throw new LaravelRabbitMQPubSubException("Handler does not implement RabbitMQPubSubConsumerHandler");
                    }

                    $pubSub->subscribe(
                        $subscriptionName,
                        $topicName,
                        fn(string $data) => $handler->consume($data),
                    );
                }
            }

            return $pubSub;
        });

        $this->app->singleton(ConnectionInterface::class, function () {
            return $this->app->make(ConnectionFactory::class)->connect();
        });

        $this->app->singleton(ConnectionFactory::class, function () {
            $host = config('rabbitMQPubSub.connection.host')
                ?? throw MissingConfigException::create("rabbitMQPubSub.connection.host");
            $port = config('rabbitMQPubSub.connection.port')
                ?? throw MissingConfigException::create("rabbitMQPubSub.connection.port");
            $username = config('rabbitMQPubSub.connection.username')
                ?? throw MissingConfigException::create("rabbitMQPubSub.connection.username");
            $password = config('rabbitMQPubSub.connection.password')
                ?? throw MissingConfigException::create("rabbitMQPubSub.connection.password");

            return new ConnectionFactory(
                $host,
                (int) $port,
                $username,
                $password,
                config('rabbitMQPubSub.connection.vhost', '/'),
                (bool) config('rabbitMQPubSub.connection.secure'),
                [
                    'capath' => '/etc/ssl/certs',
                    'fail_if_no_peer_cert' => false,
                    'verify_peer' => false
                ],
            );
        });
    }

    public function provides(): array
    {
        return [
            PubSub::class,
            ConnectionInterface::class,
            ConnectionFactory::class,
        ];
    }
}
