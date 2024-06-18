<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use ReflectionClass;

trait RecordsEvents
{
    /** @var DomainEvent[] */
    protected array $recorded_events = [];

    /** @return DomainEvent[] */
    public function recordedEvents(): array
    {
        return $this->recorded_events;
    }

    public function clearRecordedEvents(): void
    {
        $this->recorded_events = [];
    }

    protected function recordThat(DomainEvent $event): void
    {
        $this->recorded_events[] = $event;
    }

    protected function applyThat(DomainEvent $event): void
    {
        $event_name = (new ReflectionClass($event))->getShortName();

        (new ReflectionClass($this))
            ->getMethod("apply{$event_name}")
            ->invoke($this, $event);
    }

    protected function recordAndApplyThat(DomainEvent $event): void
    {
        $this->recordThat($event);
        $this->applyThat($event);
    }
}
