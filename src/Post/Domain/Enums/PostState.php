<?php

declare(strict_types=1);

namespace App\Post\Domain\Enums;

enum PostState: string
{
    case SoftDeleted = "deleted";
    case Draft = "draft";
//    case ScheduledForPublishing = "scheduled";
    case Published = "published";

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
