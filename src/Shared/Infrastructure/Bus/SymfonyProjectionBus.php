<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Projection\Projection;
use App\Shared\Application\Projection\ProjectionBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyProjectionBus implements ProjectionBus
{
    public function __construct(
        private MessageBusInterface $projectionBus,
    ) {
    }

    public function project(Projection ...$projections): void
    {
        foreach ($projections as $projection) {
            $this->dispatch($projection);
        }
    }

    public function dispatch(Projection $projection): void
    {
        try {
            $this->projectionBus->dispatch($projection);
        } catch (HandlerFailedException $e) {
            $exceptions = $e->getWrappedExceptions();

            throw $exceptions[array_key_first($exceptions)];
        }
    }
}
