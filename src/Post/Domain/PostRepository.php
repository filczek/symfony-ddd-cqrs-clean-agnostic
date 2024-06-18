<?php

declare(strict_types=1);

namespace App\Post\Domain;

use App\Post\Domain\ValueObjects\PostId;
use App\Post\Infrastructure\Persistence\PostGatewayInterface;
use App\Post\Infrastructure\Persistence\PostSnapshotMapper;
use App\Shared\Domain\ValueObjects\Version;

final class PostRepository
{
    public function __construct(
        private PostGatewayInterface $gateway
    ) {
    }

    public function create(Post $post): void
    {
        $snapshot = PostSnapshotMapper::fromPost($post);

        $this->gateway->create($snapshot);
    }

    public function ofId(PostId $post_id): Post
    {
        $snapshot = $this->gateway->ofId($post_id->toString());

        return Post::fromSnapshot($snapshot);
    }

    public function forPage(int $page, int $per_page): PostPaginationResult
    {
        $result = $this->gateway->forPage(page: $page, per_page: $per_page);

        $posts = PostCollection::fromSnapshotCollection($result->snapshots);

        return new PostPaginationResult(
            posts: $posts,
            pagination: $result->pagination
        );
    }

    public function update(Post $post): void
    {
        // TODO think about better implementation of this
        $previous_version = $post->version()->toInteger() - count($post->recordedEvents());
        $previous_version = Version::from($previous_version)->toString();

        $snapshot = PostSnapshotMapper::fromPost($post);

        $this->gateway->update($snapshot, $previous_version);
    }
}
