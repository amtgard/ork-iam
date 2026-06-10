<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;

class ClaimFactory
{
    public static function createOrn(string $orn) {
        [$serviceId, $ornClass] = OrnServiceResolver::resolveForClaim($orn);
        return new $ornClass($serviceId, $orn);
    }
}