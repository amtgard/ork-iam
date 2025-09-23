<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\Requirement\Requirement;

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