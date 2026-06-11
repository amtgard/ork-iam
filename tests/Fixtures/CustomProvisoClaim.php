<?php

namespace Tests\Amtgard\IAM\Fixtures;

use Amtgard\IAM\Allowance\Claim;
use Amtgard\IAM\Resource;

class CustomProvisoClaim extends Claim
{
    protected function serviceFormat(): array
    {
        return ['tenant-id', 'org unit'];
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
