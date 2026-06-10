<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;

class OrnServiceResolver
{
    public static function resolvePrefix(string $orn): ServiceIdentifier
    {
        $parts = explode(':', $orn, 2);
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('Invalid orn format.');
        }

        return ServiceIdentifier::from($parts[0]);
    }

    /**
     * @return array{0: ServiceIdentifier, 1: class-string}
     */
    public static function resolveForClaim(string $orn): array
    {
        $serviceId = self::resolvePrefix($orn);
        $class = OrnClassMap::getClaimClass($serviceId->name);

        return [$serviceId, $class];
    }

    /**
     * @return array{0: ServiceIdentifier, 1: class-string}
     */
    public static function resolveForRequirement(string $orn): array
    {
        $serviceId = self::resolvePrefix($orn);
        $class = OrnClassMap::getRequirementClass($serviceId->name);

        return [$serviceId, $class];
    }
}
