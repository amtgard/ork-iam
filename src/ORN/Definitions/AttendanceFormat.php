<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\OrkService;
use Amtgard\IAM\ORNFormat;

class AttendanceFormat extends ORNFormat
{

    public static function serviceFormat(): array
    {
        return [
            OrkService::Configuration,
            OrkService::Game,
            OrkService::Kingdom,
            OrkService::Park,
            OrkService::Event,
            OrkService::EventInstance,
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