<?php

namespace Tests\Amtgard\IAM\Fixtures;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Requirement\Requirement;

class ExampleRequirement extends Requirement
{
    public function ornSegmentSchema(): array
    {
        return [ServiceCatalog::Configuration];
    }

    protected function getResourceMap(?string $resource = null): array
    {
        $map = [
            'Widget' => ['Read'],
        ];

        return $resource ? $map[$resource] : $map;
    }
}
