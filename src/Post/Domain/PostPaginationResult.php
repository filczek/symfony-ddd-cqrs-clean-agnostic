<?php

declare(strict_types=1);

namespace App\Post\Domain;

use App\Shared\Infrastructure\PaginationMetadata;

final readonly class PostPaginationResult
{
    public function __construct(
        public PostCollection $posts,
        public PaginationMetadata $pagination,
    ) {
    }
}
