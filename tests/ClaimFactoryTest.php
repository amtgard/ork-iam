<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\ORN\Definitions\AttendanceClaim;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ClaimFactory;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ClaimFactoryTest extends TestCase
{
    public function testCreateOrn() {
        $orn = ClaimFactory::createOrn("Attendance:1:2:3:4:5:6:ORK/AddAttendance");
        assertEquals(new AttendanceClaim(OrkServices::Attendance, "Attendance:1:2:3:4:5:6:ORK/AddAttendance"), $orn);
    }

    public function testCreateOrnClaim_withGlob() {
        $orn = ClaimFactory::createOrn("Attendance:*::::::*");
        assertEquals(new AttendanceClaim(OrkServices::Attendance, "Attendance:*::::::*"), $orn);
    }
}