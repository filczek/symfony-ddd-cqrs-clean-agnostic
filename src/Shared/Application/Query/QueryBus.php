<?php

declare(strict_types=1);

namespace App\Shared\Application\Query;

interface QueryBus
{
    public function execute(Query $query): mixed;
}
