<?php

namespace Cerpus\LaravelRabbitMQPubSub\Tests;

use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            RabbitMQPubSubServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
