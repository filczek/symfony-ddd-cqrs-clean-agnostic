<?php

declare(strict_types=1);

namespace App\Tests\Post\Application;

use App\Post\Application\PostModule;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\ListPosts\ListPostsQuery;
use App\Post\Application\UseCases\PublishPost\PublishPostCommand;
use App\Post\Domain\ValueObjects\PostId;
use App\Tests\TestCase;
use App\Tests\Traits\WithInMemoryEvents;

class ListPostsQueryTest extends TestCase
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
    public function lists(): void
    {
        // Given
        array_map(fn () => $this->createProject(), range(1, 5));
        $published_posts = array_map(fn () => $this->createRandomPostAndPublishIt(), range(1, 10));
        array_map(fn () => $this->createProject(), range(1, 5));

        $page = 1;
        $per_page = 5;

        // When
        $result = $this->posts()->paginate(ListPostsQuery::fromArray(['page' => 1, 'per_page' => $per_page]));

        // Then
        $this->assertCount($per_page, $result->data);

        $this->assertSame($page, $result->pagination->page);
        $this->assertSame($per_page, $result->pagination->per_page);
        $this->assertSame(count($published_posts), $result->pagination->total_items);
        $this->assertSame(count($published_posts) / $per_page, $result->pagination->total_pages);
    }

    public function createProject(): PostId
    {
        $id = PostId::nextIdentity();

        $command = new CreatePostCommand(
            id: $id->toString(),
            title: "Title",
            content: "Content"
        );

        $this->posts()->create($command);

        return $id;
    }

    public function createRandomPostAndPublishIt(): PostId
    {
        $id = $this->createProject();

        $this->posts()->publish(new PublishPostCommand(id: $id->toString()));

        return $id;
    }

}
