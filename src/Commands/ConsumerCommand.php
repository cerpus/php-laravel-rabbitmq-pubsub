<?php

namespace Cerpus\LaravelRabbitMQPubSub\Commands;

use Cerpus\LaravelRabbitMQPubSub\Facades\RabbitMQPubSub;
use Illuminate\Console\Command;

class ConsumerCommand extends Command
{
    protected $signature = 'laravel-rabbitmq-pubsub:consumer';
    protected $description = 'Consume messages from rabbitmq';

    public function handle()
    {
        $this->info("working");
        RabbitMQPubSub::setupConsumer();
    }
}
