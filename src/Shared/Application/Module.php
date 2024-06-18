<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Application\Command\Command;
use App\Shared\Application\Command\CommandBus;
use App\Shared\Application\Query\Query;
use App\Shared\Application\Query\QueryBus;

abstract class Module
{
    public function __construct(
        private CommandBus $command_bus,
        private QueryBus $query_bus
    ) {
    }

    protected function handle(Command $command): mixed
    {
        return $this->command_bus->handle($command);
    }

    protected function execute(Query $query): mixed
    {
        return $this->query_bus->execute($query);
    }
}
