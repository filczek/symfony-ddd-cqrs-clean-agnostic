<?php

declare(strict_types=1);

namespace App\Post\Domain\Exceptions;

use InvalidArgumentException;

final class PostTitleCannotBeChanged extends InvalidArgumentException
{
    public static function emptyTitleNotAllowedWhenPublished(): self
    {
        throw new self("A title is required for published posts. Please ensure a valid title is provided.");
    }
}
