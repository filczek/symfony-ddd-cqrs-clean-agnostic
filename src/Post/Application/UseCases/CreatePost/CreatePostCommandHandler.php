<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\CreatePost;

use App\Post\Domain\Post;
use App\Post\Domain\PostRepository;
use App\Shared\Application\Command\CommandHandler;
use App\Shared\Application\Event\EventBus;

final readonly class CreatePostCommandHandler implements CommandHandler
{
    public function __construct(
        private PostRepository $posts,
        private EventBus $events
    ) {
    }

    public function __invoke(CreatePostCommand $command): void
    {
        $post = Post::create(
            id: $command->id,
            title: $command->title,
            content: $command->content
        );

        $this->posts->create($post);
        $this->events->publish(...$post->recordedEvents());
    }
}
