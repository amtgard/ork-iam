<?php

namespace Tests\Amtgard\IAM\ORN;

use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\Definitions\ORN\AttendanceRequirement;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Orn\Condition;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AttendanceRequirementTest extends TestCase
{
    private AttendanceRequirement $requirement;
    private static String $ATTENDANCE_ORN = "Attendance:1:2:3:4:5:6:ORK/AddAttendance";
    public function setUp(): void
    {
        parent::setUp();
        $this->requirement = new AttendanceRequirement(ServiceCatalog::Attendance, self::$ATTENDANCE_ORN);
    }

    public function testConstructor() {
        self::assertEquals($this->requirement->toCatalogEntry(), ServiceCatalog::Attendance);
        self::assertEquals((new Condition(ServiceCatalog::Event, "5")), $this->requirement->getSegment(ServiceCatalog::Event));
    }

    public function testWhenProvisoIsGlob_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid orn format.");
        new AttendanceRequirement(ServiceCatalog::Attendance, "Attendance:1:2:*:4:5::ORK/AddAttendance");
    }

    public function testAllowsExact() {
        self::assertTrue($this->requirement->allows(new AttendanceClaim(ServiceCatalog::Attendance, self::$ATTENDANCE_ORN)));
    }

    public function testWhenOneMatch_thenAllow() {
        $oneMatchOrn = "Attendance::8:::5::ORK/AddAttendance";
        self::assertTrue($this->requirement->allows(new AttendanceClaim(ServiceCatalog::Attendance, $oneMatchOrn)));
    }

    public function testWhenRequirement_hasEmptySlots_thenReject() {
        $requirement = new AttendanceRequirement(ServiceCatalog::Attendance, "Attendance:1:2:3:4:5::ORK/AddAttendance");
        $noMatchOrn = "Attendance::8:::::ORK/AddAttendance";
        self::assertFalse($requirement->allows(new AttendanceClaim(ServiceCatalog::Attendance, $noMatchOrn)));
    }

    public function testWhenNoMatch_thenReject() {
        $noMatchOrn = "Attendance::8:::::ORK/AddAttendance";
        self::assertFalse($this->requirement->allows(new AttendanceClaim(ServiceCatalog::Attendance, $noMatchOrn)));
    }

    public function testWhenOneMatch_andResourceGlob_thenAllow() {
        $oneMatchOrn = "Attendance:::::5::*";
        self::assertTrue($this->requirement->allows(new AttendanceClaim(ServiceCatalog::Attendance, $oneMatchOrn)));
    }

    public function testWhenOneMatch_andProcedureGlob_thenAllow() {
        $oneMatchOrn = "Attendance:::::5::ORK/*";
        self::assertTrue($this->requirement->allows(new AttendanceClaim(ServiceCatalog::Attendance, $oneMatchOrn)));
    }

    public function testBuildOrnReturnsInputString() {
        self::assertEquals(self::$ATTENDANCE_ORN, $this->requirement->buildOrn());
    }

    public function testWhenInvalidOrn_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid custom ORN prefix 'not_an_orn'");
        new AttendanceRequirement(ServiceCatalog::Attendance, "not_an_orn");
    }

    public function testWhenAlmostValidOrn_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid orn format.");
        new AttendanceRequirement(ServiceCatalog::Attendance, "Attendance:1:two:3:4:5::ORK/AddAttendance");
    }

    public function testWhenInvalidResource_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid resource definition.");
        new AttendanceRequirement(ServiceCatalog::Attendance, "Attendance:1::3:4:5::ORK/AddAttendances");
    }

    public function testGetProvisoReturnsConfiguredGrant(): void
    {
        $proviso = $this->requirement->getSegment(ServiceCatalog::Event);

        self::assertEquals(ServiceCatalog::Event, $proviso->toCatalogEntry());
        self::assertEquals(5, $proviso->getValue());
    }

    public function testWhenInvalidProvisos_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid segment binding set.");
        new AttendanceRequirement(ServiceCatalog::Attendance, "Attendance:1::3:4::ORK/AddAttendance");
    }

    public function testWhenProvisoCountMismatch_thenThrows() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid segment binding set.");
        new AttendanceRequirement(ServiceCatalog::Attendance, "Attendance:1:2:::ORK/AddAttendance");
    }

    public function testWhenServiceDoesNotMatch_thenNotAllowed() {
        $wrongService = "ORK::8:::5:ORK/AddKingdom";
        self::assertFalse($this->requirement->allows(new OrkClaim(ServiceCatalog::ORK, $wrongService)));
    }
}