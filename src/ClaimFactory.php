<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;

class ClaimFactory
{
    public static function createOrn(string $orn) {
        $service = OrkService::from(explode(':',$orn, 2)[0]);
        $ornClass = OrnClassMap::$ORN_CLASS_MAP[$service->value];
        return new $ornClass($service, $orn);
    }
}