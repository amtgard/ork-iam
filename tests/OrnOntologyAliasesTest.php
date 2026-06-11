<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\Orn\OrnSegmentLabel;
use Amtgard\IAM\Orn\OrnPrefix;
use PHPUnit\Framework\TestCase;
use Tests\Amtgard\IAM\Fixtures\CustomProvisoClaim;
use Tests\Amtgard\IAM\Fixtures\CustomProvisoRequirement;

class OrnOntologyAliasesTest extends TestCase
{
    private const PREFIX = 'OntologyExample';
    private const ORN = 'OntologyExample:42:7:Widget/Read';

    protected function setUp(): void
    {
        parent::setUp();
        OrnClassMap::registerClaim(self::PREFIX, CustomProvisoClaim::class);
        OrnClassMap::registerRequirement(self::PREFIX, CustomProvisoRequirement::class);
    }

    public function testGetPrefixMatchesServiceIdentifier(): void
    {
        $claim = new CustomProvisoClaim(OrnPrefix::from(self::PREFIX), self::ORN);

        self::assertTrue($claim->getPrefix()->equals($claim->getPrefix()));
        self::assertSame(self::PREFIX, $claim->getPrefix()->name);
    }

    public function testSegmentAliasesMatchProvisoAccessors(): void
    {
        $claim = new CustomProvisoClaim(OrnPrefix::from(self::PREFIX), self::ORN);

        self::assertSame($claim->getSegments(), $claim->getSegments());
        self::assertSame(
            $claim->getSegment('tenant-id')->getValue(),
            $claim->getSegment('tenant-id')->getValue()
        );
        self::assertTrue(
            $claim->getSegment('tenant-id')->getLabel()->equals(OrnSegmentLabel::from('tenant-id'))
        );
    }

    public function testSegmentOffset(): void
    {
        $claim = new CustomProvisoClaim(OrnPrefix::from(self::PREFIX), self::ORN);

        self::assertSame(0, $claim->segmentOffset('tenant-id'));
        self::assertSame(1, $claim->segmentOffset('org unit'));
        self::assertSame(1, OrnSegmentLabel::from('org unit')->offsetIn($claim->ornSegmentSchema()));
    }

    public function testOrnSegmentSchemaDelegatesToServiceFormat(): void
    {
        $claim = new CustomProvisoClaim(OrnPrefix::from(self::PREFIX), self::ORN);

        self::assertSame(['tenant-id', 'org unit'], $claim->ornSegmentSchema());
    }
}
