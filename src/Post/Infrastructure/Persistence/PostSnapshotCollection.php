<?php

declare(strict_types=1);

namespace App\Post\Infrastructure\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

/** @extends ArrayCollection<int, PostSnapshot> */
final class PostSnapshotCollection extends ArrayCollection
{

}
