<?php

declare(strict_types=1);

namespace App\Post\Application\Dto;

use App\Post\Domain\PostCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<int, PostDto>
 */
final class PostDtoCollection extends ArrayCollection
{
    public static function fromPostCollection(PostCollection $posts): self
    {
        $dto_collection = new self();

        foreach ($posts as $post) {
            $dto_collection->add(PostDto::fromPost($post));
        }

        return $dto_collection;
    }
}
