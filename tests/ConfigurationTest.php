<?php

namespace Cerpus\LaravelRabbitMQPubSub\Tests;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Cerpus\LaravelRabbitMQPubSub\Tests\Dummies\HandlerImplementation;
use Cerpus\PubSub\Connection\ConnectionFactory;
use Cerpus\PubSub\Connection\ConnectionInterface;
use Illuminate\Support\Facades\Artisan;

class ConfigurationTest extends TestCase
{
    public function invalidConsumersConfigurationDataProvider(): array
    {
        return [
            'Everything empty' => [
                'configData' => [],
                'errorMessage' => 'Missing config rabbitMQPubSub.consumers'
            ],
            'Missing subscription' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'consumers' => [
                            'test' => []
                        ]
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.consumers.test.subscriptions',
            ],
            'Unknown handler' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'consumers' => [
                            'test' => [
                                'subscriptions' => [
                                    'test' => [
                                        'handler' => 'smth'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'errorMessage' => 'Cannot create handler smth',
            ],
            'Handler not implementing RabbitMQPubSubConsumerHandler' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'consumers' => [
                            'test' => [
                                'subscriptions' => [
                                    'test' => [
                                        'handler' => \Exception::class
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'errorMessage' => 'Handler does not implement RabbitMQPubSubConsumerHandler'
            ],
            'Missing handler' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'consumers' => [
                            'test' => [
                                'subscriptions' => [
                                    'test' => []
                                ]
                            ]
                        ]
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.consumers.test.subscriptions.test.handler',
            ],
            'Duplicate subscription' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'consumers' => [
                            'test' => [
                                'subscriptions' => [
                                    'test' => [
                                        'handler' => HandlerImplementation::class
                                    ]
                                ]
                            ],
                            'test2' => [
                                'subscriptions' => [
                                    'test' => [
                                        'handler' => HandlerImplementation::class
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'errorMessage' => 'Duplicate subscription test'
            ],
        ];
    }

    /**
     * @dataProvider invalidConsumersConfigurationDataProvider
     */
    public function testThrowsOnInvalidConsumersConfigTest(array $configData, string $errorMessage)
    {
        config($configData);
        $connection = $this->getMockBuilder(ConnectionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instance(ConnectionInterface::class, $connection);

        $this->expectException(LaravelRabbitMQPubSubException::class);
        $this->expectExceptionMessage($errorMessage);
        Artisan::call('laravel-rabbitmq-pubsub:consumer');
    }

    public function testValidConsumersConfigTest()
    {
        config([
            'rabbitMQPubSub' => [
                'consumers' => [
                    'test' => [
                        'subscriptions' => [
                            'test' => [
                                'handler' => HandlerImplementation::class
                            ],
                            'test2' => [
                                'handler' => HandlerImplementation::class
                            ]
                        ]
                    ],
                    'test2' => [
                        'subscriptions' => [
                            'test3' => [
                                'handler' => HandlerImplementation::class
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $mockConnection = $this->createMock(ConnectionInterface::class);
        $mockConnection
            ->expects($this->once())
            ->method('listen');
        $this->instance(ConnectionInterface::class, $mockConnection);

        $this->artisan('laravel-rabbitmq-pubsub:consumer')->assertSuccessful();
    }

    public function invalidConnectionConfigurationDataProvider(): array
    {
        return [
            'Everything empty' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'host' => 'test'
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.connection.host'
            ],
            'Missing host' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'connection' => [
                            'port' => 'test',
                            'username' => 'test',
                            'password' => 'test',
                        ]
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.connection.host'
            ],
            'Missing port' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'connection' => [
                            'host' => 'test',
                            'username' => 'test',
                            'password' => 'test',
                        ]
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.connection.port'
            ],
            'Missing username' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'connection' => [
                            'host' => 'test',
                            'port' => 'test',
                            'password' => 'test',
                        ]
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.connection.username'
            ],
            'Missing password' => [
                'configData' => [
                    'rabbitMQPubSub' => [
                        'connection' => [
                            'host' => 'test',
                            'port' => 'test',
                            'username' => 'test',
                        ]
                    ]
                ],
                'errorMessage' => 'Missing config rabbitMQPubSub.connection.password'
            ],
        ];
    }

    /**
     * @dataProvider invalidConnectionConfigurationDataProvider
     */
    public function testThrowsOnInvalidConnectionConfigTest(array $configData, string $errorMessage)
    {
        config($configData);

        $this->expectException(LaravelRabbitMQPubSubException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->app->make(ConnectionFactory::class);
    }
}
