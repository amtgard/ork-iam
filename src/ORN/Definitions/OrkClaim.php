<?php

namespace Amtgard\IAM\Definitions\ORN;

use Amtgard\IAM\Allowance\Claim;use OrkFormat;

class OrkClaim extends Claim
{

    /**
     * @inheritDoc
     */
    protected function serviceFormat(): array
    {
        return OrkFormat::serviceFormat();
    }

    /**
     * @inheritDoc
     */
    protected function getResourceMap(string $resource = null): array
    {
        return OrkFormat::getValidResourceMap($resource);
    }
}