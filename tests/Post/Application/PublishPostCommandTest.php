<?php

declare(strict_types=1);

namespace App\Tests\Post\Application;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Application\UseCases\PublishPost\PublishPostCommand;
use App\Post\Domain\Enums\PostState;
use App\Post\Domain\Events\PostWasPublished;
use App\Post\Domain\Exceptions\PostCannotBePublished;
use App\Post\Domain\ValueObjects\PostId;
use Generator;
use App\Tests\TestCase;
use App\Tests\Traits\WithInMemoryEvents;

class PublishPostCommandTest extends TestCase
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
    public function publish(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()
            ->create(new CreatePostCommand(
                id: $id,
                title: "Hello world!",
                content: "Some example content."
            ));
        $this->getInMemoryEventBus()->clear();

        // When
        $this->posts()->publish(PublishPostCommand::fromArray(['id' => $id]));

        // Then
        $post = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        $this->assertSame($id, $post->id);
        $this->assertSame(PostState::Published->value, $post->state);

        $this->assertCountOfDispatchedEvents(1);
        $this->assertEventWasPublishedOnce(PostWasPublished::class);
    }

    /** @test */
    public function publishingAlreadyPublishedPostThrowsException(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()
            ->create(new CreatePostCommand(
                id: $id,
                title: "Some title",
                content: "Some content."
            ));
        $this->getInMemoryEventBus()->clear();
        $this->posts()->publish(new PublishPostCommand(id: $id));

        // Then
        $this->expectException(PostCannotBePublished::class);

        // When
        $this->posts()->publish(new PublishPostCommand(id: $id));
    }

    /**
     * @test
     * @dataProvider examples
     */
    public function cannotBePublished(CreatePostCommand $command): void
    {
        // Given
        $this->posts()->create($command);
        $this->getInMemoryEventBus()->clear();

        // Then
        $this->expectException(PostCannotBePublished::class);

        // When
        $this->posts()->publish(new PublishPostCommand(id: $command->id));
    }

    public static function examples(): Generator
    {
        yield 'Cannot publish post due to empty title' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: '', content: 'Some content'), false];
        yield 'Cannot publish post due to empty content' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Some title', content: ''), false];
    }
}
