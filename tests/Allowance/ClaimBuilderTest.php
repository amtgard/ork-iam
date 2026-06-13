<?php

namespace Tests\Amtgard\IAM\Allowance;

use Amtgard\IAM\Allowance\ClaimBuilder;
use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\OrkServices;
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

        $claim = ClaimBuilder::forPrefix(OrkServices::Attendance)
            ->segment(OrkServices::Configuration, 1)
            ->segment(OrkServices::Game, 2)
            ->segment(OrkServices::Kingdom, 3)
            ->segment(OrkServices::Park, 4)
            ->segment(OrkServices::Event, 5)
            ->segment(OrkServices::EventInstance, 6)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertInstanceOf(AttendanceClaim::class, $claim);
        self::assertSame($orn, $claim->buildOrn());
        self::assertEquals(ClaimFactory::createOrn($orn), $claim);
    }

    public function testUnsetSegmentsRenderAsEmptyInOrn(): void
    {
        $claim = ClaimBuilder::forPrefix(OrkServices::Attendance)
            ->segment(OrkServices::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertSame('Attendance:1::::::ORK/AddAttendance', $claim->buildOrn());
    }

    public function testBuildMatchesClaimFactoryCreateOrn(): void
    {
        $orn = 'Attendance:*::::::ORK/AddAttendance';
        $built = ClaimBuilder::forPrefix(OrkServices::Attendance)
            ->segment(OrkServices::Configuration, '*')
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
        $orn = ClaimBuilder::forPrefix(OrkServices::Attendance)
            ->segment(OrkServices::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->buildOrnString();

        self::assertSame('Attendance:1::::::ORK/AddAttendance', $orn);
    }

    public function testForPrefixAcceptsStringAndServiceIdentifier(): void
    {
        $fromString = ClaimBuilder::forPrefix('Attendance')
            ->segment(OrkServices::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        $fromIdentifier = ClaimBuilder::forPrefix(\Amtgard\IAM\ServiceIdentifier::from('Attendance'))
            ->segment(OrkServices::Configuration, 1)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertEquals($fromString, $fromIdentifier);
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
        $claim = ClaimBuilder::forPrefix(OrkServices::Attendance)
            ->segment(OrkServices::Configuration, null)
            ->resource('ORK', 'AddAttendance')
            ->build();

        self::assertSame('Attendance:::::::ORK/AddAttendance', $claim->buildOrn());
    }

    public function testDefaultResourceIsWildcard(): void
    {
        $orn = ClaimBuilder::forPrefix(OrkServices::Attendance)
            ->segment(OrkServices::Configuration, 1)
            ->buildOrnString();

        self::assertSame('Attendance:1::::::*', $orn);
    }
}
