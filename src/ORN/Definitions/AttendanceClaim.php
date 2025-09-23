<?php

namespace Amtgard\IAM\Definitions\ORN;

use Amtgard\IAM\Allowance\Claim;
use AttendanceFormat;

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