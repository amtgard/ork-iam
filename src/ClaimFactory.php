<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;

class ClaimFactory
{
    public static function createOrn(string $orn) {
        $service = OrkServices::from(explode(':',$orn, 2)[0]);
        $ornClass = OrnClassMap::getClaimClass($service);
        return new $ornClass($service, $orn);
    }
}