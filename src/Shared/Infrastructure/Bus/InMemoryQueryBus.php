<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Query\Query;
use App\Shared\Application\Query\QueryBus;

final class InMemoryQueryBus implements QueryBus
{
    /** @var Query[] */
    private $queries = [];

    public function execute(Query $query): mixed
    {
        $this->queries[] = $query;

        return null;
    }

    /** @return Query[] */
    public function all(): array
    {
        return $this->queries;
    }

    public function wasQueried(Query $query): bool
    {
        foreach ($this->queries as $queried) {
            if ($queried instanceof $query) {
                return true;
            }
        }

        return false;
    }
}
