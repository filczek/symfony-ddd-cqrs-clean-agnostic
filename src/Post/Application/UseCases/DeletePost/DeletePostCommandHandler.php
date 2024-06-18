<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\DeletePost;

use App\Post\Domain\PostRepository;
use App\Post\Domain\ValueObjects\PostId;
use App\Shared\Application\Command\CommandHandler;
use App\Shared\Application\Event\EventBus;

final class DeletePostCommandHandler implements CommandHandler
{
    public function __construct(
        private PostRepository $posts,
        private EventBus $events
    ) {
    }

    public function __invoke(DeletePostCommand $command): void
    {
        $post = $this->posts->ofId(PostId::from($command->id));

        $post->delete();

        $this->posts->update($post);
        $this->events->publish(...$post->recordedEvents());
    }
}
