<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use App\Shared\Domain\ValueObjects\Version;

abstract class AggregateRoot
{
    use RecordsEvents {
        applyThat as apply;
    }

    abstract public function version(): Version;

    abstract protected function changeVersion(Version $version): void;

    protected function applyThat(DomainEvent $event): void
    {
        $this->apply($event);
        $this->changeVersion($this->version()->next());
    }
}
