<?php

namespace Tests\Amtgard\IAM\Fixtures;

use Amtgard\IAM\Allowance\Claim;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Resource;

class ExampleClaim extends Claim
{
    protected function serviceFormat(): array
    {
        return [OrkServices::Configuration];
    }

    protected function getResourceMap(?string $resource = null): array
    {
        return [];
    }

    protected function validResource(Resource $resource): bool
    {
        return true;
    }
}
