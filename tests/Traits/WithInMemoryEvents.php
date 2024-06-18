<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use App\Shared\Application\Event\EventBus;
use App\Shared\Infrastructure\Bus\InMemoryEventBus;

trait WithInMemoryEvents
{
    public static function setupInMemoryEventBus(): void
    {
        static::getContainer()->set(EventBus::class, new InMemoryEventBus());
    }

    public static function clearDispatchedEvents(): void
    {
        static::getInMemoryEventBus()->clear();
    }

    public static function getInMemoryEventBus(): InMemoryEventBus
    {
        return static::getContainer()->get(EventBus::class);
    }

    public static function assertCountOfDispatchedEvents(int $count): void
    {
        static::assertCount($count, static::getInMemoryEventBus()->all());
    }

    public static function assertEventWasPublishedOnce(string $command): void
    {
        static::assertTrue(static::getInMemoryEventBus()->wasPublishedOnce($command));
    }

    public static function assertEventWasNotPublished(string $command): void
    {
        static::assertTrue(static::getInMemoryEventBus()->wasPublishedTimes($command, 0));
    }
}
