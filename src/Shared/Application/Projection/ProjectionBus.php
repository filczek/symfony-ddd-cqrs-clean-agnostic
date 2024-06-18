<?php

declare(strict_types=1);

namespace App\Shared\Application\Projection;

interface ProjectionBus
{
    public function project(Projection ...$projections): void;
}
