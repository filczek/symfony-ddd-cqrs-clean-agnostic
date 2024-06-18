<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventStore;

use App\Shared\Domain\EventStream;

interface EventStore
{
    public function append(EventStream $event_stream): void;

    public function eventsFor(string $id): EventStream;
}
