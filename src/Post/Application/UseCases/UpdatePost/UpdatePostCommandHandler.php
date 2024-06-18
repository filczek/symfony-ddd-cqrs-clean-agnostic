<?php

declare(strict_types=1);

namespace App\Post\Application\UseCases\UpdatePost;

use App\Post\Domain\Exceptions\PostUpdateConflict;
use App\Post\Domain\Post;
use App\Post\Domain\PostRepository;
use App\Post\Domain\ValueObjects\PostContent;
use App\Post\Domain\ValueObjects\PostId;
use App\Post\Domain\ValueObjects\PostTitle;
use App\Shared\Application\Command\CommandHandler;
use App\Shared\Application\Event\EventBus;
use App\Shared\Domain\ValueObjects\Version;

final class UpdatePostCommandHandler implements CommandHandler
{
    public function __construct(
        private PostRepository $posts,
        private EventBus $events
    ) {
    }

    public function __invoke(UpdatePostCommand $command): void
    {
        $post = $this->posts->ofId(PostId::from($command->id));
        $this->throwIfVersionMismatch($post, $command);

        if (is_string($command->title)) {
            $post->changeTitle(PostTitle::from($command->title));
        }

        if (is_string($command->content)) {
            $post->changeContent(PostContent::from($command->content));
        }

        $this->posts->update($post);
        $this->events->publish(...$post->recordedEvents());
    }

    private function throwIfVersionMismatch(Post $post, UpdatePostCommand $command): void
    {
        $version = Version::from($command->version);

        if ($post->version()->equals($version)) {
            return;
        }

        throw PostUpdateConflict::postHasBeenUpdatedByAnotherUser();
    }
}
