<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\ListPosts;

use App\Shared\Application\Query\Query;

final readonly class ListPostsQuery implements Query
{
    public static function fromArray(array $array): self
    {
        return new self(
            page: $array['page'],
            per_page: $array['per_page'],
        );
    }

    public function __construct(
        public int $page,
        public int $per_page,
    ) {
    }
}
