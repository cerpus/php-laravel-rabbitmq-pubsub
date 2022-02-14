<?php

namespace Cerpus\LaravelRabbitMQPubSub\Commands;

use Cerpus\PubSub\PubSub;
use Illuminate\Console\Command;

class ConsumerCommand extends Command
{
    protected $signature = 'laravel-rabbitmq-pubsub:consumer';
    protected $description = 'Consume messages from rabbitmq';

    public function __construct(private PubSub $pubSub)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Processing queues");

        $this->pubSub->listen();
        $this->pubSub->close();
    }
}
