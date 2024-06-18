<?php

declare(strict_types=1);

namespace App\Post\Interface\Api;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\PublishPost\PublishPostCommand;
use App\Post\Domain\Exceptions\PostCannotBePublished;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class PublishPost
{
    public function __construct(
        private PostModule $posts
    ) {
    }

    #[Route('/api/v1/posts/{id}/publish', name: 'api.post.publish', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $id = $request->get('id');

        try {
            $this->posts->publish(
                PublishPostCommand::fromArray(['id' => $id, ...$request->getPayload()->all()]),
            );

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (PostCannotBePublished $e) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
