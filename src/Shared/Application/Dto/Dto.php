<?php

declare(strict_types=1);

namespace App\Shared\Application\Dto;

use JsonSerializable;

abstract readonly class Dto implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
