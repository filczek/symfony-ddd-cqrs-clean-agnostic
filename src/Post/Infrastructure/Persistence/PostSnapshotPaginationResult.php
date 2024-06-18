<?php

declare(strict_types=1);

namespace App\Post\Infrastructure\Persistence;

use App\Shared\Infrastructure\PaginationMetadata;

final readonly class PostSnapshotPaginationResult
{
    public function __construct(
        public PostSnapshotCollection $snapshots,
        public PaginationMetadata $pagination
    ) {
    }
}
