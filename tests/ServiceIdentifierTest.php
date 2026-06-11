<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ServiceIdentifier;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServiceIdentifierTest extends TestCase
{
    public function testFromBuiltinNormalizesToEnum(): void
    {
        $id = ServiceIdentifier::from('Attendance');

        self::assertTrue($id->isBuiltin());
        self::assertEquals(OrkServices::Attendance, $id->toOrkServices());
        self::assertEquals('Attendance', $id->name);
    }

    public function testFromCustomIdentifier(): void
    {
        $id = ServiceIdentifier::from('Example');

        self::assertFalse($id->isBuiltin());
        self::assertNull($id->toOrkServices());
        self::assertEquals('Example', (string) $id);
    }

    public function testEqualsComparesByName(): void
    {
        $a = ServiceIdentifier::from('Example');
        $b = ServiceIdentifier::from('Example');

        self::assertTrue($a->equals($b));
    }

    public function testWhenLowercasePrefix_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid custom service identifier 'example'");

        ServiceIdentifier::from('example');
    }

    public function testWhenEmptyPrefix_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service identifier cannot be empty.');

        ServiceIdentifier::from('');
    }
}
