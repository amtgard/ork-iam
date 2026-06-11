<?php

namespace Amtgard\IAM\ORN;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Orn\OrnPrefix;

class OrnClassMap
{
    private static array $claimMap = [];
    private static array $requirementMap = [];

    public static function registerClaim(string|ServiceCatalog $prefix, string $claimClass): void
    {
        $key = self::key($prefix);
        self::assertCustomDoesNotCollideWithBuiltin($prefix, $key);
        self::$claimMap[$key] = $claimClass;
    }

    public static function registerRequirement(string|ServiceCatalog $prefix, string $requirementClass): void
    {
        $key = self::key($prefix);
        self::assertCustomDoesNotCollideWithBuiltin($prefix, $key);
        self::$requirementMap[$key] = $requirementClass;
    }

    public static function getClaimClass(string|ServiceCatalog $prefix): string
    {
        $key = self::key($prefix);
        if (!isset(self::$claimMap[$key])) {
            throw new \InvalidArgumentException("No claim class registered for prefix $key.");
        }

        return self::$claimMap[$key];
    }

    public static function getRequirementClass(string|ServiceCatalog $prefix): string
    {
        $key = self::key($prefix);
        if (!isset(self::$requirementMap[$key])) {
            throw new \InvalidArgumentException("No requirement class registered for prefix $key.");
        }

        return self::$requirementMap[$key];
    }

    public static function isRegistered(string|ServiceCatalog $prefix, bool $asRequirement = false): bool
    {
        $key = self::key($prefix);
        $map = $asRequirement ? self::$requirementMap : self::$claimMap;

        return isset($map[$key]);
    }

    public static function validateCustomPrefix(string $name): void
    {
        OrnPrefix::from($name);
        if (ServiceCatalog::tryFrom($name) !== null) {
            throw new \InvalidArgumentException(
                "Custom prefix '$name' collides with a built-in ServiceCatalog entry."
            );
        }
    }

    /** @internal */
    public static function reset(): void
    {
        self::$claimMap = [];
        self::$requirementMap = [];
    }

    private static function key(string|ServiceCatalog $prefix): string
    {
        return $prefix instanceof ServiceCatalog ? $prefix->value : $prefix;
    }

    private static function assertCustomDoesNotCollideWithBuiltin(string|ServiceCatalog $prefix, string $key): void
    {
        if ($prefix instanceof ServiceCatalog) {
            return;
        }

        if (ServiceCatalog::tryFrom($key) !== null) {
            throw new \InvalidArgumentException(
                "Cannot register custom class for built-in prefix '$key'. Use ServiceCatalog::$key instead."
            );
        }
    }
}
