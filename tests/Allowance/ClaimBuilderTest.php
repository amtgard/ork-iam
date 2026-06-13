<?php

namespace Tests\Amtgard\IAM\Allowance;

use Amtgard\IAM\Allowance\ClaimBuilder;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\ORN\OrnPrefix;
use PHPUnit\Framework\TestCase;
use Tests\Amtgard\IAM\Fixtures\CustomProvisoClaim;

class ClaimBuilderTest extends TestCase
{
    private const CUSTOM_PREFIX = 'ClaimBuilderExample';

    protected function setUp(): void
    {
        parent::setUp();
        OrnClassMap::registerClaim(self::CUSTOM_PREFIX, CustomProvisoClaim::class);
    }

    public function testBuildsAttendanceClaimMatchingHandWrittenOrn(): void
    {
        $orn = 'Attendance:1:2:3:4:5:6:ORK/AddAttendance';

        $claim = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, 1)
            ->segment(ServiceCatalog::Game, 2)
            ->segment(ServiceCatalog::Kingdom, 3)
            ->segment(ServiceCatalog::Park, 4)
            ->segment(ServiceCatalog::Event, 5)
            ->segment(ServiceCatalog::EventInstance, 6)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertInstanceOf(AttendanceClaim::class, $claim);
        self::assertSame($orn, $claim->buildOrn());
        self::assertEquals(ClaimFactory::createOrn($orn), $claim);
    }

    public function testUnsetSegmentsRenderAsEmptyInOrn(): void
    {
        $claim = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertSame('Attendance:1::::::ORK/AddAttendance', $claim->buildOrn());
    }

    public function testBuildMatchesClaimFactoryCreateOrn(): void
    {
        $orn = 'Attendance:*::::::ORK/AddAttendance';
        $built = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, '*')
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertEquals(ClaimFactory::createOrn($orn), $built);
        self::assertSame($orn, $built->buildOrn());
    }

    public function testSupportsCustomSegmentLabels(): void
    {
        $orn = 'ClaimBuilderExample:42:7:Widget/Read';

        $claim = ClaimBuilder::forPrefix(self::CUSTOM_PREFIX)
            ->segment('tenant-id', 42)
            ->segment('org unit', 7)
            ->resource('Widget', 'Read')
            ->build();

        self::assertSame($orn, $claim->buildOrn());
        self::assertEquals(ClaimFactory::createOrn($orn), $claim);
    }

    public function testBuildOrnStringWithoutBuild(): void
    {
        $orn = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->buildOrnString();

        self::assertSame('Attendance:1::::::ORK/AddAttendance', $orn);
    }

    public function testForPrefixAcceptsStringAndOrnPrefix(): void
    {
        $fromString = ClaimBuilder::forPrefix('Attendance')
            ->segment(ServiceCatalog::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        $fromPrefix = ClaimBuilder::forPrefix(OrnPrefix::from('Attendance'))
            ->segment(ServiceCatalog::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertEquals($fromString, $fromPrefix);
    }

    public function testResourceWithoutProcedureUsesResourceOnly(): void
    {
        $claim = ClaimBuilder::forPrefix(self::CUSTOM_PREFIX)
            ->segment('tenant-id', 1)
            ->segment('org unit', 2)
            ->resource('Widget')
            ->build();

        self::assertSame('ClaimBuilderExample:1:2:Widget', $claim->buildOrn());
    }

    public function testNullSegmentValueRendersAsEmpty(): void
    {
        $claim = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, null)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertSame('Attendance:::::::ORK/AddAttendance', $claim->buildOrn());
    }

    public function testDefaultResourceIsWildcard(): void
    {
        $orn = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, 1)
            ->buildOrnString();

        self::assertSame('Attendance:1::::::*', $orn);
    }

    public function testBuilderUsesServiceCatalogEnum(): void
    {
        $claim = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
            ->segment(ServiceCatalog::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertSame('Attendance:1::::::ORK/AddAttendance', $claim->buildOrn());
    }

    public function testOrnPrefixCustomIntegratorPrefixStillWorks(): void
    {
        $claim = ClaimBuilder::forPrefix(OrnPrefix::from(self::CUSTOM_PREFIX))
            ->segment('tenant-id', 42)
            ->segment('org unit', 7)
            ->resource('Widget', 'Read')
            ->build();

        self::assertSame('ClaimBuilderExample:42:7:Widget/Read', $claim->buildOrn());
    }
}
