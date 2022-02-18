<?php

namespace Cerpus\LaravelRabbitMQPubSub\Commands;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub;
use Illuminate\Console\Command;

class ConsumerCommand extends Command
{
    protected $signature = 'laravel-rabbitmq-pubsub:consumer';
    protected $description = 'Consume messages from rabbitmq';

    public function __construct(
        private RabbitMQPubSub $pubSub,
    ) {
        parent::__construct();
    }

    /**
     * @throws LaravelRabbitMQPubSubException
     */
    public function handle()
    {
        $this->info("Processing queues");

        $this->pubSub->listenWithConsumersSetUp();
    }
}
