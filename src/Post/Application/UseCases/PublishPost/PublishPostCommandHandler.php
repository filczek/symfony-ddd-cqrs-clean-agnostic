<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\PublishPost;

use App\Post\Domain\PostRepository;
use App\Post\Domain\ValueObjects\PostId;
use App\Shared\Application\Command\CommandHandler;
use App\Shared\Application\Event\EventBus;

final class PublishPostCommandHandler implements CommandHandler
{
    public function __construct(
        private PostRepository $posts,
        private EventBus $events
    ) {
    }

    public function __invoke(PublishPostCommand $command): void
    {
        $post = $this->posts->ofId(PostId::from($command->id));

        $post->publish();

        $this->posts->update($post);
        $this->events->publish(...$post->recordedEvents());
    }
}
