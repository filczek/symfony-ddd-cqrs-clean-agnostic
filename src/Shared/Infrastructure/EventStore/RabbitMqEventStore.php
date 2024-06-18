<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventStore;

use App\Shared\Domain\DomainEvent;
use App\Shared\Domain\EventStream;
use App\Shared\Infrastructure\Serializer\JsonSerializer;
use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Config;
use Psy\Configuration;
use VladimirYuldashev\LaravelQueueRabbitMQ\Contracts\RabbitMQQueueContract;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

final readonly class RabbitMqEventStore implements EventStore
{
    public function __construct(
        private RabbitMQConnector $connector,
        private JsonSerializer $serializer,
    ) {
    }

    public function append(EventStream $event_stream): void
    {
        $config = Config::get('queue.connections.rabbitmq');
        $queue = $this->connector->connect($config);

        $exchangeName = 'events_exchange';
        $routingKey = 'events';

        $queue->declareExchange(name: $exchangeName, type: 'direct', durable: true, autoDelete: false);
        $queue->declareQueue(name: 'events_queue', durable: true, autoDelete: false);
        $queue->bindQueue(queue: 'events_queue', exchange: $exchangeName, routingKey: $routingKey);

        /** @var DomainEvent $event */
        foreach ($event_stream as $event) {
            $json = $this->serializer->serialize($event);

            $queue->pushRaw(payload: $json, queue: 'events_queue');

//            $queue->push((string) $event->aggregateId(), $json);
        }
    }

    public function eventsFor(string $id): EventStream
    {
        // TODO: Implement eventsFor() method.
    }
}
