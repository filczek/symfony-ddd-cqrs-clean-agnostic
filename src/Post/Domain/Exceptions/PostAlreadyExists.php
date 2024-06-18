<?php

declare(strict_types=1);

namespace App\Post\Domain\Exceptions;

use App\Shared\Infrastructure\Exceptions\RecordNotFoundException;

final class PostAlreadyExists extends RecordNotFoundException
{
    public static function withIdOf(mixed $id): self
    {
        return new self("Post with ID '$id' already exists.");
    }
}
