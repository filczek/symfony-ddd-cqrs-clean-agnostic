<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use Stringable;
use Symfony\Component\String\Slugger\AsciiSlugger;

readonly class Slug implements Stringable
{
    public static function from(mixed $value): static
    {
        if ($value instanceof static) {
            return self::from($value->value);
        }

        $value = (new AsciiSlugger())
            ->slug($value)
            ->toString();

        return new static($value);
    }

    private function __construct(
        private string $value,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
