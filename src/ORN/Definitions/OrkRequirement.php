<?php

namespace Amtgard\IAM\Definitions\ORN;

use Amtgard\IAM\Requirement\Requirement;use OrkFormat;

class OrkRequirement extends Requirement
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