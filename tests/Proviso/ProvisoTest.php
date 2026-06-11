<?php

namespace Tests\Amtgard\IAM\Proviso;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Proviso\Condition;
use Amtgard\IAM\Proviso\Grant;
use LogicException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProvisoTest extends TestCase
{
    public function testGrantStoresNumericId(): void
    {
        $grant = new Grant(OrkServices::Kingdom, '7');

        self::assertEquals(OrkServices::Kingdom, $grant->getService());
        self::assertEquals(7, $grant->getId());
    }

    public function testGrantStoresWildcardId(): void
    {
        $grant = new Grant(OrkServices::Kingdom, '*');

        self::assertEquals('*', $grant->getId());
    }

    public function testGrantStoresEmptyIdAsNull(): void
    {
        $grant = new Grant(OrkServices::Kingdom, '');

        self::assertNull($grant->getId());
    }

    public function testWhenInvalidProvisoId_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid segment value. Value must be an integer or the string '*'");

        new Grant(OrkServices::Kingdom, 'invalid');
    }

    public function testAllowsExactMatch(): void
    {
        $condition = new Condition(OrkServices::Kingdom, 7);
        $grant = new Grant(OrkServices::Kingdom, 7);

        self::assertTrue($condition->allows($grant));
    }

    public function testAllowsWildcardGrant(): void
    {
        $condition = new Condition(OrkServices::Kingdom, 7);
        $grant = new Grant(OrkServices::Kingdom, '*');

        self::assertTrue($condition->allows($grant));
    }

    public function testWhenIdsDoNotMatch_thenNotAllowed(): void
    {
        $condition = new Condition(OrkServices::Kingdom, 7);
        $grant = new Grant(OrkServices::Kingdom, 8);

        self::assertFalse($condition->allows($grant));
    }

    public function testGrantExposesBuiltinSlotViaGetService(): void
    {
        $grant = new Grant(OrkServices::Kingdom, 7);

        self::assertEquals(OrkServices::Kingdom, $grant->getService());
        self::assertEquals('Kingdom', $grant->getSegmentLabel()->name);
    }

    public function testWhenCustomSlot_thenGetServiceThrows(): void
    {
        $grant = new Grant('custom-slot', 7);

        $this->expectException(LogicException::class);

        $grant->getService();
    }

    public function testConditionGetOrnMatcher(): void
    {
        $condition = new class(OrkServices::Attendance, 1) extends Condition {
            public function exposeMatcher(OrkServices $service): string
            {
                return $this->getOrnMatcher($service);
            }
        };

        self::assertStringContainsString('Attendance:', $condition->exposeMatcher(OrkServices::Attendance));
    }
}
