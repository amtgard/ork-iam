<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;

class RequirementFactory
{
    public static function createOrn(string $orn) {
        [$serviceId, $ornClass] = OrnServiceResolver::resolveForRequirement($orn);
        return new $ornClass($serviceId, $orn);
    }
}