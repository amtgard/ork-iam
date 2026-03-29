<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORNFormat;

class OrkFormat extends ORNFormat
{

    public static function serviceFormat(): array
    {
        return [
            OrkServices::Configuration,
            OrkServices::Game,
            OrkServices::Kingdom,
            OrkServices::Park,
            OrkServices::Event,
        ];
    }

    public static function getValidResourceMap($resource = null): array {
        $map = [
            "ORK" => [ "AddKingdom" ]
        ];
        return $resource ? $map[$resource] : $map;
    }

}