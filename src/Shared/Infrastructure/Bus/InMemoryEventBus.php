<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Event\EventBus;
use App\Shared\Domain\DomainEvent;

final class InMemoryEventBus implements EventBus
{
    /** @var DomainEvent[] */
    private $events = [];

    public function publish(DomainEvent ...$events): void
    {
        $this->events = [...$this->events, ...$events];
    }

    /** @return DomainEvent[] */
    public function all(): iterable
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }

    public function wasPublishedTimes(string $event, int $times): bool
    {
        $amount = 0;

        foreach ($this->all() as $published_event) {
            if ($published_event instanceof $event) {
                $amount++;
            }
        }

        return $amount === $times;
    }

    public function wasPublishedOnce(string $event): bool
    {
        return $this->wasPublishedTimes($event, 1);
    }
}
