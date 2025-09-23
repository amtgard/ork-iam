<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\ORN\Definitions\AttendanceClaim;
use Amtgard\IAM\OrkService;
class OrnClassMap {

    public static $ORN_CLASS_MAP = [
        OrkService::Attendance->value => AttendanceClaim::class
    ];
}
