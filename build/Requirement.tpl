namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\Requirement\Requirement;

class <?=$requirementClass ?> extends Requirement
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
