<?php

namespace Amtgard\IAM\Definitions\ORN;

use Amtgard\IAM\Requirement\Requirement;
use AttendanceFormat;

class AttendanceRequirement extends Requirement
{

    protected function serviceFormat(): array
    {
        return AttendanceFormat::serviceFormat();
    }

    protected function getResourceMap(string $resource = null): array
    {
        return AttendanceFormat::getValidResourceMap($resource);
    }
}