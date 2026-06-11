<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\ClaimFactory;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ClaimFactoryTest extends TestCase
{
    public function testCreateOrn() {
        $orn = ClaimFactory::createOrn("Attendance:1:2:3:4:5:6:ORK/AddAttendance");
        assertEquals(new AttendanceClaim(ServiceCatalog::Attendance, "Attendance:1:2:3:4:5:6:ORK/AddAttendance"), $orn);
    }

    public function testCreateOrnClaim_withGlob() {
        $orn = ClaimFactory::createOrn("Attendance:*::::::*");
        assertEquals(new AttendanceClaim(ServiceCatalog::Attendance, "Attendance:*::::::*"), $orn);
    }

    public function testClaimGetProvisoReturnsConfiguredGrant(): void
    {
        $claim = ClaimFactory::createOrn("Attendance:1:2:3:4:5:6:ORK/AddAttendance");

        self::assertEquals(1, $claim->getSegment(ServiceCatalog::Configuration)->getValue());
    }
}