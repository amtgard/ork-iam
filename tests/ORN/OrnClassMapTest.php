<?php

namespace Tests\Amtgard\IAM\ORN;

use Amtgard\IAM\ORN\Definitions\AttendanceClaim;
use Amtgard\IAM\ORN\Definitions\AttendanceRequirement;
use Amtgard\IAM\ORN\Definitions\OrkClaim;
use Amtgard\IAM\ORN\Definitions\OrkRequirement;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\OrkServices;
use PHPUnit\Framework\TestCase;

class OrnClassMapTest extends TestCase
{
    public function testClaimMapContainsSupportedServices(): void
    {
        self::assertSame(AttendanceClaim::class, OrnClassMap::$ORN_CLAIM_MAP[OrkServices::Attendance->value]);
        self::assertSame(OrkClaim::class, OrnClassMap::$ORN_CLAIM_MAP[OrkServices::ORK->value]);
    }

    public function testRequirementMapContainsSupportedServices(): void
    {
        self::assertSame(AttendanceRequirement::class, OrnClassMap::$ORN_REQUIREMENT_MAP[OrkServices::Attendance->value]);
        self::assertSame(OrkRequirement::class, OrnClassMap::$ORN_REQUIREMENT_MAP[OrkServices::ORK->value]);
    }
}
