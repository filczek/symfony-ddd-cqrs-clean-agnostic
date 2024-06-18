<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\FindPostById;

use App\Post\Application\Dto\PostDto;
use App\Post\Domain\Exceptions\PostNotFound;
use App\Post\Domain\PostRepository;
use App\Post\Domain\ValueObjects\PostId;
use App\Shared\Application\Query\QueryHandler;

final readonly class FindPostByIdQueryHandler implements QueryHandler
{
    public function __construct(
        private PostRepository $posts
    ) {
    }

    public function __invoke(FindPostByIdQuery $query): PostDto
    {
        $id = PostId::from($query->id);

        $post = $this->posts->ofId($id);

        if ($post->isDeleted()) {
            throw PostNotFound::withIdOf($id);
        }

        return PostDto::fromPost($post);
    }
}
