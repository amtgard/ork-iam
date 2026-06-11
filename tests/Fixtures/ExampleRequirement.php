<?php

namespace Tests\Amtgard\IAM\Fixtures;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Requirement\Requirement;

class ExampleRequirement extends Requirement
{
    protected function serviceFormat(): array
    {
        return [OrkServices::Configuration];
    }

    protected function getResourceMap(?string $resource = null): array
    {
        $map = [
            'Widget' => ['Read'],
        ];

        return $resource ? $map[$resource] : $map;
    }
}
