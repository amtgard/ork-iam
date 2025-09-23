<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\ORN\Definitions\AttendanceClaim;
use Amtgard\IAM\OrkService;
use Amtgard\IAM\ClaimFactory;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ClaimFactoryTest extends TestCase
{
    public function testCreateOrn() {
        $orn = ClaimFactory::createOrn("Attendance:1:2:3:4:5:6:ORK/AddAttendance");
        assertEquals(new AttendanceClaim(OrkService::Attendance, "Attendance:1:2:3:4:5:6:ORK/AddAttendance"), $orn);
    }
}