<?php

declare(strict_types=1);

namespace App\Post\Interface\Api;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Application\UseCases\UpdatePost\UpdatePostCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdatePost extends AbstractController
{
    public function __construct(
        private PostModule $posts
    ) {
    }

    #[Route('/api/v1/posts/{id}', name: 'api.post.update', methods: ['PATCH'])]
    public function __invoke(Request $request): JsonResponse
    {
        $id = $request->get('id');

        $this->posts->update(
            UpdatePostCommand::fromArray([...$request->getPayload()->all(), 'id' => $id])
        );

        $post = $this->posts->ofId(
            FindPostByIdQuery::fromArray(['id' => $id])
        );

        return new JsonResponse($post);
    }
}
