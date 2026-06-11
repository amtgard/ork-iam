<?php

namespace Tests\Amtgard\IAM\Fixtures;

use Amtgard\IAM\Allowance\Claim;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Resource;

class ExampleClaim extends Claim
{
    public function ornSegmentSchema(): array
    {
        return [ServiceCatalog::Configuration];
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
