<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\Requirement\Requirement;

class OrkRequirement extends Requirement
{

    protected function serviceFormat(): array
    {
        return OrkFormat::serviceFormat();
    }

    protected function getResourceMap(string $resource = null): array
    {
        return OrkFormat::getValidResourceMap($resource);
    }
}