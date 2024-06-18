<?php

declare(strict_types=1);

namespace App\Post\Interface\Api;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetPost
{
    public function __construct(
        private PostModule $posts
    ) {
    }

    #[Route('/api/v1/posts/{id}', name: 'api.post.get', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $id = $request->get('id');

        $post = $this->posts->ofId(
            FindPostByIdQuery::fromArray(['id' => $id, ...$request->getPayload()->all()])
        );

        return new JsonResponse($post);
    }
}
