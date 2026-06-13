<?php

namespace Tests\Amtgard\IAM\Allowance;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\Allowance\PolicyBuilder;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\PolicyFactory;
use PHPUnit\Framework\TestCase;
use Tests\Amtgard\IAM\Fixtures\IdpJwtPolicyFixture;

class PolicyBuilderTest extends TestCase
{
    public function testCreateBuildsPolicyFromOrnStrings(): void
    {
        $policy = PolicyBuilder::create()
            ->addOrn('ORK:1:::::*')
            ->addOrn('ORK:2:::::*')
            ->build();

        self::assertInstanceOf(Policy::class, $policy);
        self::assertTrue($policy->isAuthorized(
            new OrkRequirement(OrkServices::ORK, 'ORK:1:7:8:9:10:ORK/AddKingdom')
        ));
    }

    public function testFromSeedsBuilderWithExistingPolicy(): void
    {
        $seed = PolicyFactory::fromOrn(['ORK:1:::::*']);
        $policy = PolicyBuilder::from($seed)
            ->addOrn('ORK:2:::::*')
            ->build();

        self::assertTrue($policy->isAuthorized(
            new OrkRequirement(OrkServices::ORK, 'ORK:2:7:8:9:10:ORK/AddKingdom')
        ));
    }

    public function testMergeCombinesGlobalAndIntegratorLines(): void
    {
        $global = PolicyFactory::fromOrn(IdpJwtPolicyFixture::GLOBAL_POLICY_LINES);
        $integrator = PolicyFactory::fromOrn(IdpJwtPolicyFixture::INTEGRATOR_POLICY_LINES);

        $merged = PolicyBuilder::from($global)
            ->merge($integrator)
            ->build();

        $combined = PolicyFactory::fromOrn(
            array_merge(IdpJwtPolicyFixture::GLOBAL_POLICY_LINES, IdpJwtPolicyFixture::INTEGRATOR_POLICY_LINES)
        );

        self::assertTrue($merged->is($combined));
    }

    public function testMergeDedupesDuplicateOrnLines(): void
    {
        $policyA = PolicyFactory::fromOrn(['ORK:1:::::*', 'ORK:2:::::*']);
        $policyB = PolicyFactory::fromOrn(['ORK:1:::::*']);

        $merged = PolicyBuilder::from($policyA)
            ->merge($policyB)
            ->build();

        self::assertSame($policyA->toJson(), $merged->toJson());
        self::assertCount(2, $merged->getClaims());
    }

    public function testEitherGlobalOrIntegratorLineCanAuthorize(): void
    {
        $policy = PolicyBuilder::create()
            ->addOrn('ORK:1:::::*')
            ->addOrn('ORK::3::::*')
            ->build();

        self::assertTrue($policy->isAuthorized(
            new OrkRequirement(OrkServices::ORK, 'ORK:1:7:8:9:10:ORK/AddKingdom')
        ));
        self::assertTrue($policy->isAuthorized(
            new OrkRequirement(OrkServices::ORK, 'ORK:7:3:8:9:10:ORK/AddKingdom')
        ));
    }

    public function testAddClaimAcceptsBuiltClaim(): void
    {
        $claim = new OrkClaim(OrkServices::ORK, 'ORK:1:::::*');
        $policy = PolicyBuilder::create()
            ->addClaim($claim)
            ->build();

        self::assertTrue($policy->isAuthorized(
            new OrkRequirement(OrkServices::ORK, 'ORK:1:7:8:9:10:ORK/AddKingdom')
        ));
    }
}
