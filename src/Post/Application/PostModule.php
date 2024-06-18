<?php

declare(strict_types=1);

namespace App\Post\Application;

use App\Post\Application\Dto\PostDto;
use App\Post\Application\Dto\PostDtoPaginatedResult;
use App\Post\Application\UseCases\CreatePost\CreatePostCommand;
use App\Post\Application\UseCases\DeletePost\DeletePostCommand;
use App\Post\Application\UseCases\FindPostById\FindPostByIdQuery;
use App\Post\Application\UseCases\ListPosts\ListPostsQuery;
use App\Post\Application\UseCases\PublishPost\PublishPostCommand;
use App\Post\Application\UseCases\UpdatePost\UpdatePostCommand;
use App\Post\Domain\Exceptions\PostCannotBePublished;
use App\Shared\Application\Module;

final class PostModule extends Module
{
    public function create(CreatePostCommand $command): void
    {
        $this->handle($command);
    }

    public function ofId(FindPostByIdQuery $query): PostDto
    {
        return $this->execute($query);
    }

    public function paginate(ListPostsQuery $query): PostDtoPaginatedResult
    {
        return $this->execute($query);
    }

    public function update(UpdatePostCommand $command): void
    {
        $this->handle($command);
    }

    /**
     * @throws PostCannotBePublished
     */
    public function publish(PublishPostCommand $command): void
    {
        $this->handle($command);
    }

    public function delete(DeletePostCommand $command): void
    {
        $this->handle($command);
    }
}
