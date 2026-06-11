<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\Definitions\ORN\AttendanceRequirement;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\RequirementFactory;
use PHPUnit\Framework\TestCase;

class RequirementFactoryTest extends TestCase
{
    public function testCreateOrnBuildsOrkRequirement(): void
    {
        $requirement = RequirementFactory::createOrn('ORK:1:7:8:9:10:ORK/AddKingdom');

        self::assertInstanceOf(OrkRequirement::class, $requirement);
        self::assertEquals(ServiceCatalog::ORK, $requirement->toCatalogEntry());
    }

    public function testCreateOrnBuildsAttendanceRequirement(): void
    {
        $requirement = RequirementFactory::createOrn('Attendance:1:2:3:4:5:6:ORK/AddAttendance');

        self::assertInstanceOf(AttendanceRequirement::class, $requirement);
        self::assertEquals(ServiceCatalog::Attendance, $requirement->toCatalogEntry());
    }
}
