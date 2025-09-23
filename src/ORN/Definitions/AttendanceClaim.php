<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\Allowance\Claim;

class AttendanceClaim extends Claim
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