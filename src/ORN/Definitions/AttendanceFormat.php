<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORNFormat;

class AttendanceFormat extends ORNFormat
{

    public static function serviceFormat(): array
    {
        return [
            OrkServices::Configuration,
            OrkServices::Game,
            OrkServices::Kingdom,
            OrkServices::Park,
            OrkServices::Event,
            OrkServices::EventInstance,
        ];
    }

    public static function getValidResourceMap($resource = null): array {
        $map = [
            "ORK" => [ "AddAttendance", "SetAttendance", "RemoveAttendance" ],
            "Classes" => [ "GetClasses", "SetClass" ]
        ];
        return $resource ? $map[$resource] : $map;
    }

}