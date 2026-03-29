<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;

class RequirementFactory
{
    public static function createOrn(string $orn) {
        $service = OrkServices::from(explode(':',$orn, 2)[0]);
        $ornClass = OrnClassMap::$ORN_REQUIREMENT_MAP[$service->value];
        return new $ornClass($service, $orn);
    }
}