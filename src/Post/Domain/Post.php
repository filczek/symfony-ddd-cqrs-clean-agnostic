<?php

declare(strict_types=1);

namespace App\Post\Domain;

use DateTimeImmutable;
use App\Post\Domain\Enums\PostState;
use App\Post\Domain\Events\PostContentWasChanged;
use App\Post\Domain\Events\PostTitleWasChanged;
use App\Post\Domain\Events\PostWasCreated;
use App\Post\Domain\Events\PostWasDeleted;
use App\Post\Domain\Events\PostWasPublished;
use App\Post\Domain\Exceptions\PostCannotBePublished;
use App\Post\Domain\Exceptions\PostContentCannotBeChanged;
use App\Post\Domain\Exceptions\PostTitleCannotBeChanged;
use App\Post\Domain\ValueObjects\PostContent;
use App\Post\Domain\ValueObjects\PostId;
use App\Post\Domain\ValueObjects\PostTitle;
use App\Post\Infrastructure\Persistence\PostSnapshot;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\ValueObjects\Version;
use Override;
use Symfony\Component\Clock\Clock;

final class Post extends AggregateRoot
{
    public static function create(
        string $id,
        ?string $title = null,
        ?string $content = null
    ): self {
        $id = PostId::from($id);
        $title = $title ?? "";
        $content = $content ?? "";

        $post = new self(
            id: $id,
            version: Version::create(),
            state: PostState::Draft,
            title: PostTitle::from($title),
            content: PostContent::from($content),
            created_at: Clock::get()->now(),
            published_at: null,
            deleted_at: null
        );

        $post->recordThat(PostWasCreated::with($post));

        return $post;
    }

    public static function fromSnapshot(PostSnapshot $snapshot): self
    {
        return new self(
            id: PostId::from($snapshot->id),
            version: Version::from($snapshot->version),
            state: PostState::from($snapshot->state),
            title: PostTitle::from($snapshot->title),
            content: PostCOntent::from($snapshot->content),
            created_at: new DateTimeImmutable($snapshot->created_at),
            published_at: $snapshot->published_at ? new DateTimeImmutable($snapshot->published_at) : null,
            deleted_at: $snapshot->deleted_at ? new DateTimeImmutable($snapshot->deleted_at) : null,
        );
    }

    private function __construct(
        private PostId $id,
        private Version $version,
        private PostState $state,
        private PostTitle $title,
        private PostContent $content,
        private DateTimeImmutable $created_at,
        private ?DateTimeImmutable $published_at,
        private ?DateTimeImmutable $deleted_at,
    ) {
    }

    public function id(): PostId
    {
        return $this->id;
    }

    #[Override]
    public function version(): Version
    {
        return $this->version;
    }

    #[Override]
    protected function changeVersion(Version $version): void
    {
        $this->version = $version;
    }

    public function state(): PostState
    {
        return $this->state;
    }

    private function changeState(PostState $state): void
    {
        $this->state = $state;
    }

    public function title(): PostTitle
    {
        return $this->title;
    }

    public function changeTitle(PostTitle $title): void
    {
        if ($this->isPublished() && $title->isEmpty()) {
            throw PostTitleCannotBeChanged::emptyTitleNotAllowedWhenPublished();
        }

        if ($this->title()->equalsTo($title)) {
            return;
        }

        $this->recordAndApplyThat(PostTitleWasChanged::to(
            new_title: $title,
            old_title: $this->title(),
            id: $this->id(),
        ));
    }

    private function applyPostTitleWasChanged(PostTitleWasChanged $event): void
    {
        $this->title = PostTitle::from($event->new_title);
    }

    public function content(): PostContent
    {
        return $this->content;
    }

    public function changeContent(PostContent $content): void
    {
        if ($this->isPublished() && $content->isEmpty()) {
            throw PostContentCannotBeChanged::emptyContentNotAllowedWhenPublished();
        }

        if ($this->content()->equalsTo($content)) {
            return;
        }

        $this->recordAndApplyThat(PostContentWasChanged::to(
            new_content: $content,
            old_content: $this->content(),
            id: $this->id()
        ));
    }

    private function applyPostContentWasChanged(PostContentWasChanged $event): void
    {
        $this->content = PostContent::from($event->new_content);
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->published_at;
    }

    public function isPublished(): bool
    {
        return $this->state->equals(PostState::Published);
    }

    /**
     * @throws PostCannotBePublished
     */
    private function ensurePostCanBePublished(): void
    {
        if ($this->isPublished()) {
            throw PostCannotBePublished::becauseItHasAlreadyBeenPublished();
        }

        if ($this->title()->isEmpty()) {
            throw PostCannotBePublished::dueToEmptyTitle();
        }

        if ($this->content()->isEmpty()) {
            throw PostCannotBePublished::dueToEmptyContent();
        }
    }

    /**
     * @throws PostCannotBePublished
     */
    public function publish(): void
    {
        $this->ensurePostCanBePublished();

        $this->recordAndApplyThat(PostWasPublished::withIdOf($this->id));
    }

    private function applyPostWasPublished(PostWasPublished $event): void
    {
        $this->changeState(PostState::Published);
        $this->published_at = $event->occurredOn();
        $this->deleted_at = null;
    }

    public function delete(): void
    {
        $this->recordAndApplyThat(PostWasDeleted::withIdOf($this->id));
    }

    private function applyPostWasDeleted(PostWasDeleted $event): void
    {
        $this->changeState(PostState::SoftDeleted);
        $this->deleted_at = $event->occurredOn();
    }

    public function isDeleted(): bool
    {
        return $this->state()->equals(PostState::SoftDeleted);
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }
}
