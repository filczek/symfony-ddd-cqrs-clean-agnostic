<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\ListPosts;

use App\Post\Application\Dto\PostDtoCollection;
use App\Post\Application\Dto\PostDtoPaginatedResult;
use App\Post\Domain\PostRepository;
use App\Shared\Application\Query\QueryHandler;

final class ListPostsQueryHandler implements QueryHandler
{
    public function __construct(
        private PostRepository $posts
    ) {
    }

    public function __invoke(ListPostsQuery $query): PostDtoPaginatedResult
    {
        $result = $this->posts->forPage($query->page, $query->per_page);

        return new PostDtoPaginatedResult(
            data: PostDtoCollection::fromPostCollection($result->posts),
            pagination: $result->pagination
        );
    }
}
