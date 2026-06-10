<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ServiceIdentifier;

class OrnClassMap
{
    private static array $claimMap = [];
    private static array $requirementMap = [];

    public static function registerClaim(string|OrkServices $service, string $claimClass): void
    {
        $key = self::key($service);
        self::assertCustomDoesNotCollideWithBuiltin($service, $key);
        self::$claimMap[$key] = $claimClass;
    }

    public static function registerRequirement(string|OrkServices $service, string $requirementClass): void
    {
        $key = self::key($service);
        self::assertCustomDoesNotCollideWithBuiltin($service, $key);
        self::$requirementMap[$key] = $requirementClass;
    }

    public static function getClaimClass(string|OrkServices $service): string
    {
        $key = self::key($service);
        if (!isset(self::$claimMap[$key])) {
            throw new \InvalidArgumentException(
                "No claim class registered for service $key."
            );
        }

        return self::$claimMap[$key];
    }

    public static function getRequirementClass(string|OrkServices $service): string
    {
        $key = self::key($service);
        if (!isset(self::$requirementMap[$key])) {
            throw new \InvalidArgumentException(
                "No requirement class registered for service $key."
            );
        }

        return self::$requirementMap[$key];
    }

    public static function isRegistered(string|OrkServices $service, bool $asRequirement = false): bool
    {
        $key = self::key($service);
        $map = $asRequirement ? self::$requirementMap : self::$claimMap;

        return isset($map[$key]);
    }

    /**
     * Ensures a custom string identifier does not use a built-in OrkServices name.
     */
    public static function validateCustomServiceName(string $name): void
    {
        ServiceIdentifier::from($name);
        if (OrkServices::tryFrom($name) !== null) {
            throw new \InvalidArgumentException(
                "Custom service name '$name' collides with a built-in OrkServices identifier."
            );
        }
    }

    /** @internal */
    public static function reset(): void
    {
        self::$claimMap = [];
        self::$requirementMap = [];
    }

    private static function key(string|OrkServices $service): string
    {
        return $service instanceof OrkServices ? $service->value : $service;
    }

    private static function assertCustomDoesNotCollideWithBuiltin(string|OrkServices $service, string $key): void
    {
        if ($service instanceof OrkServices) {
            return;
        }

        if (OrkServices::tryFrom($key) !== null) {
            throw new \InvalidArgumentException(
                "Cannot register custom class for built-in service identifier '$key'. Use OrkServices::$key instead."
            );
        }
    }
}
