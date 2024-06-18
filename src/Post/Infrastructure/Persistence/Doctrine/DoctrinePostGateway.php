<?php

declare(strict_types=1);

namespace App\Post\Infrastructure\Persistence\Doctrine;

use App\Post\Domain\Exceptions\PostAlreadyExists;
use App\Post\Domain\Exceptions\PostNotFound;
use App\Post\Infrastructure\Persistence\PostGatewayInterface;
use App\Post\Infrastructure\Persistence\PostSnapshot;
use App\Post\Infrastructure\Persistence\PostSnapshotPaginationResult;
use App\Shared\Infrastructure\Exceptions\ConcurrencyException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\EntityIdentityCollisionException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
final class DoctrinePostGateway extends ServiceEntityRepository implements PostGatewayInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Post::class);
    }

    public function create(PostSnapshot $snapshot): void
    {
        $post = Post::fromSnapshot($snapshot);

        $em = $this->getEntityManager();

        try {
            $em->persist($post);
            $em->flush();
        } catch (EntityIdentityCollisionException) {
            throw PostAlreadyExists::withIdOf($snapshot->id);
        }
    }

    public function ofId(string $id): PostSnapshot
    {
        /** @var Post $post */
        $post = $this->getEntityManager()
            ->getRepository(Post::class)
            ->findOneBy(['id' => $id, 'deleted_at' => null]);

        if (is_null($post)) {
            throw PostNotFound::withIdOf($id);
        }

        return $post->toSnapshot();
    }

    public function forPage(int $page, int $per_page): PostSnapshotPaginationResult
    {
        throw new \Exception("Not implemented.");
    }

    public function update(PostSnapshot $snapshot, string $previous_version): void
    {
        $em = $this->getEntityManager();

        /** @var Post $post */
        $post = $em->getRepository(Post::class)
            ->findOneBy([
                'id' => $snapshot->id,
                'version' => $previous_version,
            ]);

        if (null === $post) {
            throw new ConcurrencyException("Failed to update post (ID: {$snapshot->id}) because the version has changed since the last read.");
        }

        $post->populateFromSnapshot($snapshot);

        $em->persist($post);
        $em->flush();
    }
}
