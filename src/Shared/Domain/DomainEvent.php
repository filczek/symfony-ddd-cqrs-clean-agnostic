<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use DateTimeImmutable;

interface DomainEvent
{
    public function aggregateId(): mixed;
    public function occurredOn(): DateTimeImmutable;
}
