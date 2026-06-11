<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Orn\OrnSegmentLabel;
use Amtgard\IAM\ProvisoSlot;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrnSegmentLabelTest extends TestCase
{
    public function testFromOrkServices(): void
    {
        $label = OrnSegmentLabel::from(OrkServices::Kingdom);

        self::assertTrue($label->isBuiltin());
        self::assertSame(OrkServices::Kingdom, $label->toOrkServices());
        self::assertSame('Kingdom', $label->name);
    }

    public function testFromBuiltinStringNormalizesToOrkServices(): void
    {
        $label = OrnSegmentLabel::from('Kingdom');

        self::assertTrue($label->isBuiltin());
        self::assertSame(OrkServices::Kingdom, $label->toOrkServices());
    }

    public function testFromCustomStringWithoutRestriction(): void
    {
        $label = OrnSegmentLabel::from('tenant-id');

        self::assertFalse($label->isBuiltin());
        self::assertNull($label->toOrkServices());
        self::assertSame('tenant-id', $label->name);
    }

    public function testFromCustomStringAllowsSpacesAndMixedCase(): void
    {
        $label = OrnSegmentLabel::from('org unit');

        self::assertFalse($label->isBuiltin());
        self::assertSame('org unit', $label->name);
    }

    public function testEqualsComparesByName(): void
    {
        $fromEnum = OrnSegmentLabel::from(OrkServices::Event);
        $fromString = OrnSegmentLabel::from('Event');

        self::assertTrue($fromEnum->equals($fromString));
    }

    public function testOffsetInReturnsZeroBasedIndex(): void
    {
        $schema = [OrkServices::Configuration, 'tenant-id', 'org unit'];
        $label = OrnSegmentLabel::from('tenant-id');

        self::assertSame(1, $label->offsetIn($schema));
    }

    public function testWhenLabelNotInSchema_thenOffsetInThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Label missing not found in segment schema.");

        OrnSegmentLabel::from('missing')->offsetIn(['tenant-id']);
    }

    public function testWhenEmpty_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ORN segment label cannot be empty.');

        OrnSegmentLabel::from('');
    }

    public function testProvisoSlotExtendsOrnSegmentLabel(): void
    {
        $legacy = ProvisoSlot::from('tenant-id');

        self::assertInstanceOf(OrnSegmentLabel::class, $legacy);
        self::assertSame('tenant-id', $legacy->name);
    }
}
