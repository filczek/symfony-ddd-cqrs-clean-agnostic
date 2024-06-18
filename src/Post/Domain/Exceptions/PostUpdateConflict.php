<?php

declare(strict_types=1);

namespace App\Post\Domain\Exceptions;

use App\Shared\Infrastructure\Exceptions\ConcurrencyException;

final class PostUpdateConflict extends ConcurrencyException
{
    public static function postHasBeenUpdatedByAnotherUser(): self
    {
        return new self("The post has been updated by another user. Please refresh and try again.");
    }
}
