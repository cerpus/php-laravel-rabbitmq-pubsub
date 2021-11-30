<?php

namespace Cerpus\LaravelRabbitMQPubSub\Tests;

use Cerpus\LaravelRabbitMQPubSub\Exceptions\LaravelRabbitMQPubSubException;
use Cerpus\LaravelRabbitMQPubSub\RabbitMQConnectionManager;
use Cerpus\LaravelRabbitMQPubSub\Tests\Dummies\HandlerImplementation;
use Illuminate\Support\Facades\Artisan;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

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
                'errorMessage' => 'Missing subscriptions key for rabbitMQPubSub.consumers.test'
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
                'errorMessage' => 'Class does not exists: smth'
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
                'errorMessage' => 'Missing handler declaration for rabbitMQPubSub.consumers.test.subscriptions.test'
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
        $connectionManager = $this->getMockBuilder(RabbitMQConnectionManager::class)->disableOriginalConstructor()->getMock();
        $this->instance(RabbitMQConnectionManager::class, $connectionManager);

        $mockConnection = $this->getMockBuilder(AMQPStreamConnection::class)->disableOriginalConstructor()->getMock();
        $mockChannel    = $this->getMockBuilder(AMQPChannel::class)->disableOriginalConstructor()->getMock();

        $connectionManager->expects($this->any())->method('getConnection')->willReturn($mockConnection);
        $mockConnection->expects($this->any())->method('channel')->willReturn($mockChannel);

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

        $connectionManager = $this->getMockBuilder(RabbitMQConnectionManager::class)->disableOriginalConstructor()->getMock();
        $this->instance(RabbitMQConnectionManager::class, $connectionManager);

        $mockConnection = $this->getMockBuilder(AMQPStreamConnection::class)->disableOriginalConstructor()->getMock();
        $mockChannel    = $this->getMockBuilder(AMQPChannel::class)->disableOriginalConstructor()->getMock();

        $connectionManager->expects($this->exactly(1))->method('getConnection')->willReturn($mockConnection);
        $mockConnection->expects($this->exactly(1))->method('channel')->willReturn($mockChannel);
        $mockChannel->expects($this->exactly(2))->method('exchange_declare');
        $mockChannel->expects($this->exactly(3))->method('queue_declare');
        $mockChannel->expects($this->exactly(3))->method('queue_bind');
        $mockChannel->expects($this->exactly(3))->method('basic_consume');

        Artisan::call('laravel-rabbitmq-pubsub:consumer');
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

        new RabbitMQConnectionManager();
    }
}
