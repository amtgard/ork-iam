<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\ORN\Definitions\AttendanceClaim;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORN\Definitions\AttendanceRequirement;
use Amtgard\IAM\ORN\Definitions\OrkClaim;
use Amtgard\IAM\ORN\Definitions\OrkRequirement;

class OrnClassMap {

    public static $ORN_CLAIM_MAP = [
        OrkServices::Attendance->value => AttendanceClaim::class,
        OrkServices::ORK->value => OrkClaim::class,
    ];

    public static $ORN_REQUIREMENT_MAP = [
        OrkServices::Attendance->value => AttendanceRequirement::class,
        OrkServices::ORK->value => OrkRequirement::class,
    ];
}
