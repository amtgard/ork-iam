<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\ORN\OrnPrefix;

class OrnServiceResolver
{
    public static function resolvePrefix(string $orn): OrnPrefix
    {
        $parts = explode(':', $orn, 2);
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('Invalid orn format.');
        }

        return OrnPrefix::from($parts[0]);
    }

    /**
     * @return array{0: OrnPrefix, 1: class-string}
     */
    public static function resolveForClaim(string $orn): array
    {
        $prefix = self::resolvePrefix($orn);
        $class = OrnClassMap::getClaimClass($prefix->name);

        return [$prefix, $class];
    }

    /**
     * @return array{0: OrnPrefix, 1: class-string}
     */
    public static function resolveForRequirement(string $orn): array
    {
        $prefix = self::resolvePrefix($orn);
        $class = OrnClassMap::getRequirementClass($prefix->name);

        return [$prefix, $class];
    }
}
