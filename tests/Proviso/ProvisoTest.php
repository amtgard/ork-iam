<?php

namespace Tests\Amtgard\IAM\Proviso;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\ORN\Condition;
use Amtgard\IAM\ORN\Grant;
use LogicException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProvisoTest extends TestCase
{
    public function testGrantStoresNumericId(): void
    {
        $grant = new Grant(ServiceCatalog::Kingdom, '7');

        self::assertEquals(ServiceCatalog::Kingdom, $grant->toCatalogEntry());
        self::assertEquals(7, $grant->getValue());
    }

    public function testGrantStoresWildcardId(): void
    {
        $grant = new Grant(ServiceCatalog::Kingdom, '*');

        self::assertEquals('*', $grant->getValue());
    }

    public function testGrantStoresEmptyIdAsNull(): void
    {
        $grant = new Grant(ServiceCatalog::Kingdom, '');

        self::assertNull($grant->getValue());
    }

    public function testWhenInvalidProvisoId_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid segment value. Value must be an integer or the string '*'");

        new Grant(ServiceCatalog::Kingdom, 'invalid');
    }

    public function testAllowsExactMatch(): void
    {
        $condition = new Condition(ServiceCatalog::Kingdom, 7);
        $grant = new Grant(ServiceCatalog::Kingdom, 7);

        self::assertTrue($condition->allows($grant));
    }

    public function testAllowsWildcardGrant(): void
    {
        $condition = new Condition(ServiceCatalog::Kingdom, 7);
        $grant = new Grant(ServiceCatalog::Kingdom, '*');

        self::assertTrue($condition->allows($grant));
    }

    public function testWhenIdsDoNotMatch_thenNotAllowed(): void
    {
        $condition = new Condition(ServiceCatalog::Kingdom, 7);
        $grant = new Grant(ServiceCatalog::Kingdom, 8);

        self::assertFalse($condition->allows($grant));
    }

    public function testGrantExposesBuiltinSlotViaGetService(): void
    {
        $grant = new Grant(ServiceCatalog::Kingdom, 7);

        self::assertEquals(ServiceCatalog::Kingdom, $grant->toCatalogEntry());
        self::assertEquals('Kingdom', $grant->getLabel()->name);
    }

    public function testWhenCustomSlot_thenGetServiceThrows(): void
    {
        $grant = new Grant('custom-slot', 7);

        $this->expectException(LogicException::class);

        $grant->toCatalogEntry();
    }

    public function testConditionGetOrnMatcher(): void
    {
        $condition = new class(ServiceCatalog::Attendance, 1) extends Condition {
            public function exposeMatcher(ServiceCatalog $service): string
            {
                return $this->getOrnMatcher($service);
            }
        };

        self::assertStringContainsString('Attendance:', $condition->exposeMatcher(ServiceCatalog::Attendance));
    }
}
