<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Orn\OrnPrefix;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrnPrefixTest extends TestCase
{
    public function testFromBuiltinNormalizesToEnum(): void
    {
        $id = OrnPrefix::from('Attendance');

        self::assertTrue($id->isBuiltin());
        self::assertEquals(ServiceCatalog::Attendance, $id->toCatalogEntry());
        self::assertEquals('Attendance', $id->name);
    }

    public function testFromCustomIdentifier(): void
    {
        $id = OrnPrefix::from('Example');

        self::assertFalse($id->isBuiltin());
        self::assertNull($id->toCatalogEntry());
        self::assertEquals('Example', (string) $id);
    }

    public function testEqualsComparesByName(): void
    {
        $a = OrnPrefix::from('Example');
        $b = OrnPrefix::from('Example');

        self::assertTrue($a->equals($b));
    }

    public function testWhenLowercasePrefix_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid custom ORN prefix 'example'");

        OrnPrefix::from('example');
    }

    public function testWhenEmptyPrefix_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ORN prefix cannot be empty.');

        OrnPrefix::from('');
    }
}
