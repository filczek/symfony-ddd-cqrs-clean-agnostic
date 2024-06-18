<?php

declare(strict_types=1);

namespace App\Tests\Post\Application;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Domain\Exceptions\PostNotFound;
use App\Post\Domain\ValueObjects\PostId;
use App\Tests\TestCase;
use App\Tests\Traits\WithInMemoryEvents;

class FindPostByIdCommandTest extends TestCase
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
    public function ofId(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();
        $this->posts()->create(new CreatePostCommand(id: $id));

        // When
        $actual = $this->posts()->ofId(new FindPostByIdQuery(id: $id));

        // Then
        $this->assertSame($id, $actual->id);
    }

    /** @test */
    public function throwsWhenNotExists(): void
    {
        // Given
        $id = PostId::nextIdentity()->toString();

        // Then
        $this->expectException(PostNotFound::class);

        // When
        $this->posts()->ofId(new FindPostByIdQuery(id: $id));
    }
}
