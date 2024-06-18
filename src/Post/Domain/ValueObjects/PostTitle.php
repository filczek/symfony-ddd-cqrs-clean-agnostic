<?php

declare(strict_types=1);

namespace App\Post\Domain\ValueObjects;

use Symfony\Component\String\UnicodeString;

final class PostTitle extends UnicodeString
{
    public static function from(string $value): self
    {
        $title = new self($value);

        return $title->trim();
    }
}
