<?php

declare(strict_types=1);

namespace App\Post\Infrastructure\Persistence\Doctrine;

use App\Post\Infrastructure\Persistence\PostSnapshot;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;

#[Entity]
#[Index(name: 'id_with_version', columns: ['id', 'version'])]
#[Index(name: 'published_at', columns: ['published_at'])]
#[Index(name: 'deleted_at', columns: ['deleted_at'])]
class Post
{
    #[Id]
    #[Column(type: Types::GUID)]
    public string $id;

    #[Column(type: Types::BIGINT, options: ['default' => 0])]
    public string $version;

    #[Column(type: Types::STRING)]
    public string $state;

    #[Column(type: Types::TEXT)]
    public string $title;

    #[Column(type: Types::TEXT)]
    public string $content;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $created_at;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $published_at;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $deleted_at;

    public static function fromSnapshot(PostSnapshot $snapshot): self
    {
        $entity = new self();
        $entity->populateFromSnapshot($snapshot);

        return $entity;
    }

    public function populateFromSnapshot(PostSnapshot $snapshot): void
    {
        $this->id = $snapshot->id;
        $this->version = $snapshot->version;
        $this->state = $snapshot->state;
        $this->title = $snapshot->title;
        $this->content = $snapshot->content;
        $this->created_at = new DateTimeImmutable($snapshot->created_at);
        $this->published_at = $snapshot->published_at ? new DateTimeImmutable($snapshot->published_at) : null;
        $this->deleted_at = $snapshot->deleted_at ? new DateTimeImmutable($snapshot->deleted_at) : null;
    }

    public function toSnapshot(): PostSnapshot
    {
        return new PostSnapshot(
            id: $this->id,
            version: $this->version,
            state: $this->state,
            title: $this->title,
            content: $this->content,
            created_at: $this->created_at->format(DATE_ATOM),
            published_at: $this->published_at?->format(DATE_ATOM),
            deleted_at: $this->deleted_at?->format(DATE_ATOM),
        );
    }
}
