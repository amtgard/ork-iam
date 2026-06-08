<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\OrkServices;

class OrnClassMap
{
    private static array $claimMap = [];
    private static array $requirementMap = [];

    public static function registerClaim(OrkServices $service, string $claimClass): void
    {
        self::$claimMap[$service->value] = $claimClass;
    }

    public static function registerRequirement(OrkServices $service, string $requirementClass): void
    {
        self::$requirementMap[$service->value] = $requirementClass;
    }

    public static function getClaimClass(OrkServices $service): string
    {
        if (!isset(self::$claimMap[$service->value])) {
            throw new \InvalidArgumentException(
                "No claim class registered for service {$service->name}."
            );
        }

        return self::$claimMap[$service->value];
    }

    public static function getRequirementClass(OrkServices $service): string
    {
        if (!isset(self::$requirementMap[$service->value])) {
            throw new \InvalidArgumentException(
                "No requirement class registered for service {$service->name}."
            );
        }

        return self::$requirementMap[$service->value];
    }

    /** @internal */
    public static function reset(): void
    {
        self::$claimMap = [];
        self::$requirementMap = [];
    }
}
