<?php

declare(strict_types=1);

namespace App\Post\Domain;

use App\Post\Infrastructure\Persistence\PostSnapshotCollection;
use Doctrine\Common\Collections\ArrayCollection;

/** @extends ArrayCollection<int, Post> */
final class PostCollection extends ArrayCollection
{
    public static function fromSnapshotCollection(PostSnapshotCollection $snapshots): self
    {
        $aggregates = new self();

        foreach ($snapshots as $snapshot) {
            $aggregates->add(Post::fromSnapshot($snapshot));
        }

        return $aggregates;
    }
}
