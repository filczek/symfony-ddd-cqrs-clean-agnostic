<?php

declare(strict_types=1);

namespace App\Tests\Post\Application;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Application\UseCases\PublishPost\PublishPostCommand;
use App\Post\Application\UseCases\UpdatePost\UpdatePostCommand;
use App\Post\Domain\Events\PostContentWasChanged;
use App\Post\Domain\Events\PostTitleWasChanged;
use App\Post\Domain\Exceptions\PostContentCannotBeChanged;
use App\Post\Domain\Exceptions\PostTitleCannotBeChanged;
use App\Post\Domain\Exceptions\PostUpdateConflict;
use App\Post\Domain\ValueObjects\PostId;
use App\Shared\Domain\ValueObjects\Version;
use App\Tests\TestCase;
use App\Tests\Traits\WithInMemoryEvents;

class UpdatePostCommandTest extends TestCase
{
    use WithInMemoryEvents;

    protected function setUp(): void
    {
        parent::setUp();

        static::setupInMemoryEventBus();
    }

    public function posts(): PostModule
    {
        return static::getContainer()->get(PostModule::class);
    }

    /** @test */
    public function updateAll(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: $before_update->version,
            title: "New title",
            content: "New content"
        ));
        $after_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $expected_version = Version::from($before_update->version)->next()->next();
        $this->assertTrue($expected_version->equals(Version::from($after_update->version)));

        $this->assertSame("New title", $after_update->title);
        $this->assertSame("New content", $after_update->content);

        $this->assertEventWasPublishedOnce(PostTitleWasChanged::class);
        $this->assertEventWasPublishedOnce(PostContentWasChanged::class);
    }

    /** @test */
    public function nothingChanges(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));
        $this->getInMemoryEventBus()->clear();

        // When
        $this->posts()->update(UpdatePostCommand::fromArray(['id' => $id, 'version' => $before_update->version]));
        $after_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $this->assertSame($before_update->version, $after_update->version);
        $this->assertSame($before_update->title, $after_update->title);
        $this->assertSame($before_update->content, $after_update->content);

        $this->assertCountOfDispatchedEvents(0);
    }

    /** @test */
    public function nothingChangesIfSame(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));
        $this->getInMemoryEventBus()->clear();

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: $before_update->version,
            title: "Title",
            content: "Content"
        ));
        $after_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $this->assertSame($before_update->version, $after_update->version);
        $this->assertSame($before_update->title, $after_update->title);
        $this->assertSame($before_update->content, $after_update->content);

        $this->assertCountOfDispatchedEvents(0);
    }

    /** @test */
    public function throwsOnVersionMismatch(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $this->expectException(PostUpdateConflict::class);

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: Version::from($before_update->version)->next()->toString(),
        ));
    }

    /** @test */
    public function changesToEmptyTitleIfNotPublished(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: $before_update->version,
            title: "New title"
        ));
        $after_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $expected_version = Version::from($before_update->version)->next();
        $this->assertTrue($expected_version->equals(Version::from($after_update->version)));

        $this->assertSame("New title", $after_update->title);
        $this->assertSame($before_update->content, $after_update->content);

        $this->assertEventWasPublishedOnce(PostTitleWasChanged::class);
        $this->assertEventWasNotPublished(PostContentWasChanged::class);
    }

    /** @test */
    public function changesToEmptyContentIfNotPublished(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: $before_update->version,
            content: "New content"
        ));
        $after_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $expected_version = Version::from($before_update->version)->next();
        $this->assertTrue($expected_version->equals(Version::from($after_update->version)));

        $this->assertSame($before_update->title, $after_update->title);
        $this->assertSame("New content", $after_update->content);

        $this->assertEventWasNotPublished(PostTitleWasChanged::class);
        $this->assertEventWasPublishedOnce(PostContentWasChanged::class);
    }

    /** @test */
    public function throwsOnEmptyTitleIfPublished(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $this->posts()->publish(new PublishPostCommand(id: $id));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $this->expectException(PostTitleCannotBeChanged::class);

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: $before_update->version,
            title: ""
        ));
    }

    /** @test */
    public function throwsOnEmptyContentIfPublished(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id, title: "Title", content: "Content"));
        $this->posts()->publish(new PublishPostCommand(id: $id));
        $before_update = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $this->expectException(PostContentCannotBeChanged::class);

        // When
        $this->posts()->update(new UpdatePostCommand(
            id: $id,
            version: $before_update->version,
            content: ""
        ));
    }
}
