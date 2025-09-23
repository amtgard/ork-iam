<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\Allowance\Claim;

class <?=$claimClass ?> extends Claim
{

    protected function serviceFormat(): array
    {
        return <?=$formatClass ?>::serviceFormat();
    }

    protected function getResourceMap(string $resource = null): array
    {
        return <?=$formatClass ?>::getValidResourceMap($resource);
    }
}<?php
