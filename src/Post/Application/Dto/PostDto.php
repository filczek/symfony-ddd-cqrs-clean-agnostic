<?php

declare(strict_types=1);

namespace App\Post\Application\Dto;

use App\Post\Domain\Post;
use App\Shared\Application\Dto\Dto;

readonly class PostDto extends Dto
{
    public static function fromPost(Post $post): self
    {
        return new self(
            id: $post->id()->toString(),
            version: $post->version()->toString(),
            state: $post->state()->value,
            title: $post->title()->toString(),
            content: $post->content()->toString(),
            created_at: $post->createdAt()->format(DATE_ATOM),
            published_at: $post->publishedAt()?->format(DATE_ATOM),
        );
    }

    public function __construct(
        public string $id,
        public string $version,
        public string $state,
        public string $title,
        public string $content,
        public string $created_at,
        public ?string $published_at
    ) {
    }
}
