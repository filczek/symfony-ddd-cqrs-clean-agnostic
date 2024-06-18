<?php

declare(strict_types=1);

namespace App\Post\Interface\Api;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\DeletePost\DeletePostCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class DeletePost
{
    public function __construct(
        private PostModule $posts
    ) {
    }

    #[Route('/api/v1/posts/{id}', name: 'api.post.delete', methods: ['DELETE'])]
    public function __invoke(Request $request): JsonResponse
    {
        $id = $request->get('id');

        $this->posts->delete(
            DeletePostCommand::fromArray(['id' => $id, ...$request->getPayload()->all()])
        );

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
