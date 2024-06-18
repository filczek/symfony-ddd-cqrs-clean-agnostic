<?php

declare(strict_types=1);

namespace App\Post\Interface\Api;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\ListPosts\ListPostsQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class ListPosts
{
    public function __construct(
        private PostModule $posts
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $values = [
            ...$request->toArray(),
            'page' => (int) $request->get('page', 1),
            'per_page' => (int) $request->get('per_page', 15),
        ];

        $posts = $this->posts->paginate(
            ListPostsQuery::fromArray($values)
        );

        return new JsonResponse($posts);
    }
}
