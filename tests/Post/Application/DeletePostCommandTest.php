<?php

declare(strict_types=1);

namespace App\Tests\Post\Application;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\DeletePost\DeletePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Domain\Events\PostWasDeleted;
use App\Post\Domain\Exceptions\PostNotFound;
use App\Post\Domain\ValueObjects\PostId;
use App\Tests\TestCase;
use App\Tests\Traits\WithInMemoryEvents;

class DeletePostCommandTest extends TestCase
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
    public function deletePost(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id));

        // When
        $this->posts()->delete(DeletePostCommand::fromArray(['id' => $id]));

        // Then
        $this->assertEventWasPublishedOnce(PostWasDeleted::class);

        $this->expectException(PostNotFound::class);
        $this->posts()->ofId(new FindPostByIdQuery(id: $id));
    }
}
