<?php

namespace Tests\Amtgard\IAM\ORN;

use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\Definitions\ORN\AttendanceRequirement;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\Catalog\ServiceCatalog;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrnClassMapTest extends TestCase
{
    public function testClaimMapContainsSupportedServices(): void
    {
        self::assertSame(AttendanceClaim::class, OrnClassMap::getClaimClass(ServiceCatalog::Attendance));
        self::assertSame(OrkClaim::class, OrnClassMap::getClaimClass(ServiceCatalog::ORK));
    }

    public function testRequirementMapContainsSupportedServices(): void
    {
        self::assertSame(AttendanceRequirement::class, OrnClassMap::getRequirementClass(ServiceCatalog::Attendance));
        self::assertSame(OrkRequirement::class, OrnClassMap::getRequirementClass(ServiceCatalog::ORK));
    }

    public function testWhenClaimServiceNotRegistered_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No claim class registered for prefix Mundane.');

        OrnClassMap::getClaimClass(ServiceCatalog::Mundane);
    }

    public function testWhenRequirementServiceNotRegistered_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No requirement class registered for prefix Mundane.');

        OrnClassMap::getRequirementClass(ServiceCatalog::Mundane);
    }

    public function testGetClaimClassAcceptsStringKey(): void
    {
        self::assertSame(AttendanceClaim::class, OrnClassMap::getClaimClass('Attendance'));
    }
}
