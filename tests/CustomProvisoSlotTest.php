<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\Proviso\Condition;
use Amtgard\IAM\Proviso\Grant;
use Amtgard\IAM\Orn\OrnSegmentLabel;
use Amtgard\IAM\RequirementFactory;
use Amtgard\IAM\ServiceIdentifier;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Tests\Amtgard\IAM\Fixtures\CustomProvisoClaim;
use Tests\Amtgard\IAM\Fixtures\CustomProvisoRequirement;

class CustomProvisoSlotTest extends TestCase
{
    private const PREFIX = 'ProvisoExample';
    private const ORN = 'ProvisoExample:42:7:Widget/Read';

    protected function setUp(): void
    {
        parent::setUp();
        OrnClassMap::registerClaim(self::PREFIX, CustomProvisoClaim::class);
        OrnClassMap::registerRequirement(self::PREFIX, CustomProvisoRequirement::class);
    }

    public function testClaimFactoryParsesCustomProvisoSlots(): void
    {
        $claim = ClaimFactory::createOrn(self::ORN);

        self::assertInstanceOf(CustomProvisoClaim::class, $claim);
        self::assertEquals(42, $claim->getProviso('tenant-id')->getId());
        self::assertEquals(7, $claim->getProviso('org unit')->getId());
        self::assertEquals('tenant-id', $claim->getSegment('tenant-id')->getSegmentLabel()->name);
    }

    public function testRequirementFactoryParsesCustomProvisoSlots(): void
    {
        $requirement = RequirementFactory::createOrn(self::ORN);

        self::assertInstanceOf(CustomProvisoRequirement::class, $requirement);
        self::assertEquals(42, $requirement->getSegment(OrnSegmentLabel::from('tenant-id'))->getSegmentValue());
    }

    public function testBuildOrnRoundTripWithCustomSlots(): void
    {
        $claim = new CustomProvisoClaim(ServiceIdentifier::from(self::PREFIX), self::ORN);

        self::assertSame(self::ORN, $claim->buildOrn());
    }

    public function testPolicyEvaluatesCustomProvisoSlots(): void
    {
        $claim = new CustomProvisoClaim(ServiceIdentifier::from(self::PREFIX), self::ORN);
        $requirement = new CustomProvisoRequirement(ServiceIdentifier::from(self::PREFIX), self::ORN);
        $policy = new Policy([$claim]);

        self::assertTrue($requirement->allows($claim));
        self::assertTrue($policy->isAuthorized($requirement));
    }

    public function testProvisoAllowsMatchesCustomSlots(): void
    {
        $condition = new Condition('tenant-id', 42);
        $grant = new Grant('tenant-id', 42);

        self::assertTrue($condition->allows($grant));
    }

    public function testWhenCustomSlotIdsDoNotMatch_thenNotAllowed(): void
    {
        $condition = new Condition('org unit', 7);
        $grant = new Grant('org unit', 8);

        self::assertFalse($condition->allows($grant));
    }

    public function testGetServiceThrowsForCustomProvisoSlot(): void
    {
        $grant = new Grant('tenant-id', 1);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Segment label tenant-id is not a built-in OrkServices catalog entry.");

        $grant->getService();
    }

    public function testServiceFormatCanMixBuiltinAndCustomSlots(): void
    {
        $claim = new class(ServiceIdentifier::from(self::PREFIX), 'ProvisoExample:1:2:Widget/Read') extends CustomProvisoClaim {
            protected function serviceFormat(): array
            {
                return [\Amtgard\IAM\OrkServices::Configuration, 'tenant-id'];
            }
        };

        self::assertEquals(1, $claim->getProviso(\Amtgard\IAM\OrkServices::Configuration)->getId());
        self::assertEquals(2, $claim->getProviso('tenant-id')->getId());
        self::assertEquals(
            \Amtgard\IAM\OrkServices::Configuration,
            $claim->getProviso(\Amtgard\IAM\OrkServices::Configuration)->getService()
        );
    }

    public function testWhenGrantSlotNotInServiceFormat_thenAllowsGrantThrows(): void
    {
        $requirement = new CustomProvisoRequirement(ServiceIdentifier::from(self::PREFIX), self::ORN);
        $grant = new Grant('unknown-slot', 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Claim grant could not be matched to a requirement condition.');

        $requirement->allowsGrant($grant);
    }
}
