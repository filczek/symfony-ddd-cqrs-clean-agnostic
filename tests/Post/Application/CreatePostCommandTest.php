<?php

declare(strict_types=1);

namespace App\Tests\Post\Application;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Domain\Enums\PostState;
use App\Post\Domain\Events\PostWasCreated;
use App\Post\Domain\Exceptions\PostAlreadyExists;
use App\Post\Domain\ValueObjects\PostId;
use App\Tests\Traits\WithInMemoryEvents;
use Generator;
use InvalidArgumentException;
use App\Tests\TestCase;

class CreatePostCommandTest extends TestCase
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

    /**
     * @test
     * @dataProvider examples
     */
    public function create(CreatePostCommand $command): void
    {
        // Given

        // When
        $this->posts()->create($command);

        // Then
        $post = $this->posts()->ofId(
            FindPostByIdQuery::fromArray(['id' => $command->id])
        );

        $this->assertSame($command->id, $post->id);
        $this->assertSame(PostState::Draft->value, $post->state);
        $this->assertSame(is_null($command->title) ? '' : $command->title, $post->title);
        $this->assertSame(is_null($command->content) ? '' : $command->content, $post->content);

        self::assertEventWasPublishedOnce(PostWasCreated::class);
        self::assertCountOfDispatchedEvents(1);
    }

    public static function examples(): Generator
    {
        // Happy path
        yield 'Create post with valid command' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Title 1', content: 'Content 1')];
        yield 'Create post with different command data' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Title 2', content: 'Content 2')];

        // Edge cases
        yield 'Create post with empty title' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: '', content: 'Content 5')];
        yield 'Create post with null title' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: null, content: 'Content 6')];
        yield 'Create post with empty content' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Title 7', content: '')];
        yield 'Create post with null content' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Title 8', content: null)];

        // Unicode characters
        yield 'Create post with Unicode characters in title and content' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Title 9 with ünicode', content: 'Content 9 with ünicode')];
        yield 'Create post with Unicode characters in different fields' => [new CreatePostCommand(id: PostId::nextIdentity()->toString(), title: 'Title 10 with ünicode', content: 'Content 10')];
    }

    /** @test */
    public function throwsIfPostAlreadyExists(): void
    {
        // Given
        $command = CreatePostCommand::fromArray(['id' => PostId::nextIdentity()->toString()]);

        // Then
        $this->posts()->create($command);

        // When
        $this->expectException(PostAlreadyExists::class);
        $this->posts()->create($command);
    }

    /** @test */
    public function throwsOnInvalidId(): void
    {
        // Given
        $command = new CreatePostCommand(id: '', title: 'Title 3', content: 'Content 3');

        // Then
        $this->expectException(InvalidArgumentException::class);

        // When
        $this->posts()->create($command);
    }
}
