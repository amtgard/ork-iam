<?php

namespace Tests\Amtgard\IAM\ORN;

use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\Definitions\ORN\AttendanceRequirement;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Proviso\Condition;
use Amtgard\IAM\Proviso\Proviso;
use InvalidArgumentException;
use Phake;
use PHPUnit\Framework\TestCase;

class AttendanceRequirementTest extends TestCase
{
    private AttendanceRequirement $requirement;
    private static String $ATTENDANCE_ORN = "Attendance:1:2:3:4:5:6:ORK/AddAttendance";
    public function setUp(): void
    {
        parent::setUp();
        $this->requirement = new AttendanceRequirement(OrkServices::Attendance, self::$ATTENDANCE_ORN);

        $claim = Phake::mock(AttendanceClaim::class);
        Phake::when($claim)->getServe->thenReturn(OrkServices::Attendance);
        $configurationGrant = Phake::mock(Proviso::class);
        Phake::when($configurationGrant)->getService->thenReturn(OrkServices::Configuration);
        $gameGrant = Phake::mock(Proviso::class);
        Phake::when($gameGrant)->getService->thenReturn(OrkServices::Game);
        $kingdomGrant = Phake::mock(Proviso::class);
        Phake::when($kingdomGrant)->getService->thenReturn(OrkServices::Kingdom);
        $parkGrant = Phake::mock(Proviso::class);
        Phake::when($parkGrant)->getService->thenReturn(OrkServices::Park);
        $eventGrant = Phake::mock(Proviso::class);
        Phake::when($eventGrant)->getService->thenReturn(OrkServices::Event);
        $instanceGrant = Phake::mock(Proviso::class);
        Phake::when($instanceGrant)->getService->thenReturn(OrkServices::EventInstance);
        Phake::when($claim)->getProvisos()->thenReturn([
            OrkServices::Configuration->name => $configurationGrant,
            OrkServices::Game->name => $gameGrant,
            OrkServices::Kingdom->name => $kingdomGrant,
            OrkServices::Park->name => $parkGrant,
            OrkServices::Event->name => $eventGrant,
            OrkServices::EventInstance->name => $instanceGrant,
        ]);
    }

    public function testConstructor() {
        self::assertEquals($this->requirement->getService(), OrkServices::Attendance);
        self::assertEquals((new Condition(OrkServices::Event, "5")), $this->requirement->getProviso(OrkServices::Event));
    }

    public function testWhenProvisoIsGlob_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid orn format.");
        new AttendanceRequirement(OrkServices::Attendance, "Attendance:1:2:*:4:5::ORK/AddAttendance");
    }

    public function testAllowsExact() {
        self::assertTrue($this->requirement->allows(new AttendanceClaim(OrkServices::Attendance, self::$ATTENDANCE_ORN)));
    }

    public function testWhenOneMatch_thenAllow() {
        $oneMatchOrn = "Attendance::8:::5::ORK/AddAttendance";
        self::assertTrue($this->requirement->allows(new AttendanceClaim(OrkServices::Attendance, $oneMatchOrn)));
    }

    public function testWhenRequirement_hasEmptySlots_thenReject() {
        $requirement = new AttendanceRequirement(OrkServices::Attendance, "Attendance:1:2:3:4:5::ORK/AddAttendance");
        $noMatchOrn = "Attendance::8:::::ORK/AddAttendance";
        self::assertFalse($requirement->allows(new AttendanceClaim(OrkServices::Attendance, $noMatchOrn)));
    }

    public function testWhenNoMatch_thenReject() {
        $noMatchOrn = "Attendance::8:::::ORK/AddAttendance";
        self::assertFalse($this->requirement->allows(new AttendanceClaim(OrkServices::Attendance, $noMatchOrn)));
    }

    public function testWhenOneMatch_andResourceGlob_thenAllow() {
        $oneMatchOrn = "Attendance:::::5::*";
        self::assertTrue($this->requirement->allows(new AttendanceClaim(OrkServices::Attendance, $oneMatchOrn)));
    }

    public function testWhenOneMatch_andProcedureGlob_thenAllow() {
        $oneMatchOrn = "Attendance:::::5::ORK/*";
        self::assertTrue($this->requirement->allows(new AttendanceClaim(OrkServices::Attendance, $oneMatchOrn)));
    }

    public function testBuildOrnReturnsInputString() {
        self::assertEquals(self::$ATTENDANCE_ORN, $this->requirement->buildOrn());
    }

    public function testWhenInvalidOrn_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid orn format.");
        new AttendanceRequirement(OrkServices::Attendance, "not_an_orn");
    }

    public function testWhenAlmostValidOrn_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid orn format.");
        new AttendanceRequirement(OrkServices::Attendance, "Attendance:1:two:3:4:5::ORK/AddAttendance");
    }

    public function testWhenInvalidResource_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid resource definition.");
        new AttendanceRequirement(OrkServices::Attendance, "Attendance:1::3:4:5::ORK/AddAttendances");
    }

    public function testGetProvisoReturnsConfiguredGrant(): void
    {
        $proviso = $this->requirement->getProviso(OrkServices::Event);

        self::assertEquals(OrkServices::Event, $proviso->getService());
        self::assertEquals(5, $proviso->getId());
    }

    public function testWhenInvalidProvisos_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid proviso set.");
        new AttendanceRequirement(OrkServices::Attendance, "Attendance:1::3:4::ORK/AddAttendance");
    }

    public function testWhenProvisoCountMismatch_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid proviso set.");
        new AttendanceRequirement(OrkServices::Attendance, "Attendance:1:2:::ORK/AddAttendance");
    }

    public function testWhenServiceDoesNotMatch_thenNotAllowed() {
        $wrongService = "ORK::8:::5:ORK/AddKingdom";
        self::assertFalse($this->requirement->allows(new OrkClaim(OrkServices::ORK, $wrongService)));
    }
}