<?php

declare(strict_types=1);

namespace App\Post\Interface\Api;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Domain\ValueObjects\PostId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreatePost
{
    public function __construct(
        private PostModule $posts
    ) {
    }

    #[Route('/api/v1/posts', name: 'api.post.create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $id = PostId::nextIdentity()->toString();

        $this->posts->create(
            CreatePostCommand::fromArray(['id' => $id, ...$request->getPayload()->all()])
        );

        $post = $this->posts->ofId(
            FindPostByIdQuery::fromArray(['id' => $id])
        );

        return new JsonResponse($post, Response::HTTP_CREATED);
    }
}
