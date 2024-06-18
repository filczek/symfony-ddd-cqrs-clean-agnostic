<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Projection\Projection;
use App\Shared\Application\Projection\ProjectionBus;

final class InMemoryProjectionBus implements ProjectionBus
{
    /** @var Projection[] */
    private $projections = [];

    public function project(Projection ...$projections): void
    {
        $this->projections = [...$this->projections, ...$projections];
    }

    /** @return Projection[] */
    public function all(): iterable
    {
        return $this->projections;
    }

    public function wasProjected(string $projection): bool
    {
        foreach ($this->all() as $projected) {
            if ($projected instanceof $projection) {
                return true;
            }
        }

        return false;
    }
}
