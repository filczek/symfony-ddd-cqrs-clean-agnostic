<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use BadMethodCallException;
use SplFixedArray;

final class EventStream extends SplFixedArray
{
    public function __construct(array $events)
    {
        parent::__construct(count($events));

        $i = 0;
        foreach ($events as $event) {
            parent::offsetSet($i++, $event);
        }
    }

    public function offsetSet(mixed $index, mixed $value): void
    {
        throw new BadMethodCallException("EventStream is immutable.");
    }

    public function offsetUnset(mixed $index): void
    {
        throw new BadMethodCallException("EventStream is immutable.");
    }

}
