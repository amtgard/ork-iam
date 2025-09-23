<?php

namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\OrkService;
use Amtgard\IAM\ORNFormat;

class OrkFormat extends ORNFormat
{

    public static function serviceFormat(): array
    {
        return [
            OrkService::Configuration,
            OrkService::Game,
            OrkService::Kingdom,
            OrkService::Park,
            OrkService::Event,
        ];
    }

    public static function getValidResourceMap($resource = null): array {
        $map = [
            "ORK" => [ "AddKingdom" ]
        ];
        return $resource ? $map[$resource] : $map;
    }

}