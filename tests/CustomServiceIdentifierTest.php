<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\RequirementFactory;
use Amtgard\IAM\ServiceIdentifier;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Amtgard\IAM\Fixtures\ExampleClaim;
use Tests\Amtgard\IAM\Fixtures\ExampleRequirement;

class CustomServiceIdentifierTest extends TestCase
{
    private const CUSTOM_PREFIX = 'Example';
    private const CUSTOM_ORN = 'Example:1:Widget/Read';

    protected function setUp(): void
    {
        parent::setUp();
        OrnClassMap::registerClaim(self::CUSTOM_PREFIX, ExampleClaim::class);
        OrnClassMap::registerRequirement(self::CUSTOM_PREFIX, ExampleRequirement::class);
    }

    public function testRegisterCustomStringAndResolveClass(): void
    {
        self::assertSame(ExampleClaim::class, OrnClassMap::getClaimClass(self::CUSTOM_PREFIX));
        self::assertSame(ExampleRequirement::class, OrnClassMap::getRequirementClass(self::CUSTOM_PREFIX));
        self::assertTrue(OrnClassMap::isRegistered(self::CUSTOM_PREFIX));
    }

    public function testClaimFactoryRoundTrip(): void
    {
        $claim = ClaimFactory::createOrn(self::CUSTOM_ORN);

        self::assertInstanceOf(ExampleClaim::class, $claim);
        self::assertEquals(self::CUSTOM_PREFIX, $claim->getServiceIdentifier()->name);
        self::assertEquals(1, $claim->getProviso(\Amtgard\IAM\OrkServices::Configuration)->getId());
    }

    public function testRequirementFactoryRoundTrip(): void
    {
        $requirement = RequirementFactory::createOrn(self::CUSTOM_ORN);

        self::assertInstanceOf(ExampleRequirement::class, $requirement);
        self::assertEquals(self::CUSTOM_PREFIX, $requirement->getServiceIdentifier()->name);
    }

    public function testWhenUnregisteredPrefix_thenFactoryThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No claim class registered for service Unregistered.');

        ClaimFactory::createOrn('Unregistered:0:Widget/Read');
    }

    public function testWhenBuiltinNameRegisteredAsCustomString_thenThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot register custom class for built-in service identifier 'Attendance'");

        OrnClassMap::registerClaim('Attendance', ExampleClaim::class);
    }

    public function testValidateCustomServiceNameRejectsBuiltinCollision(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Custom service name 'ORK' collides with a built-in OrkServices identifier.");

        OrnClassMap::validateCustomServiceName('ORK');
    }

    public function testPolicyEvaluatesCustomServiceClaim(): void
    {
        $claim = new ExampleClaim(ServiceIdentifier::from(self::CUSTOM_PREFIX), self::CUSTOM_ORN);
        $requirement = new ExampleRequirement(ServiceIdentifier::from(self::CUSTOM_PREFIX), self::CUSTOM_ORN);
        $policy = new Policy([$claim]);

        self::assertTrue($requirement->allows($claim));
        self::assertTrue($policy->isAuthorized($requirement));
    }

    public function testRegisterClaimWithEnumOverloadStillWorks(): void
    {
        self::assertSame(
            \Amtgard\IAM\Definitions\ORN\AttendanceClaim::class,
            OrnClassMap::getClaimClass(\Amtgard\IAM\OrkServices::Attendance)
        );
    }
}
