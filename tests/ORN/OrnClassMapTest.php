<?php

namespace Tests\Amtgard\IAM\ORN;

use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\Definitions\ORN\AttendanceRequirement;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\OrkServices;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrnClassMapTest extends TestCase
{
    public function testClaimMapContainsSupportedServices(): void
    {
        self::assertSame(AttendanceClaim::class, OrnClassMap::getClaimClass(OrkServices::Attendance));
        self::assertSame(OrkClaim::class, OrnClassMap::getClaimClass(OrkServices::ORK));
    }

    public function testRequirementMapContainsSupportedServices(): void
    {
        self::assertSame(AttendanceRequirement::class, OrnClassMap::getRequirementClass(OrkServices::Attendance));
        self::assertSame(OrkRequirement::class, OrnClassMap::getRequirementClass(OrkServices::ORK));
    }

    public function testWhenClaimServiceNotRegistered_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No claim class registered for service Mundane.');

        OrnClassMap::getClaimClass(OrkServices::Mundane);
    }

    public function testWhenRequirementServiceNotRegistered_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No requirement class registered for service Mundane.');

        OrnClassMap::getRequirementClass(OrkServices::Mundane);
    }
}
