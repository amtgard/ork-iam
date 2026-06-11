<?php

namespace Tests\Amtgard\IAM\Allowance;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function testToJson() {
        $claim1 = new OrkClaim(ServiceCatalog::ORK, "ORK:1:::::*");
        $claim2 = new OrkClaim(ServiceCatalog::ORK, "ORK:2:::::*");
        $claim3 = new OrkClaim(ServiceCatalog::ORK, "ORK::3::::*");
        $claim4 = new OrkClaim(ServiceCatalog::ORK, "ORK:::::4:*");

        $policy = new Policy([$claim1, $claim2, $claim3, $claim4]);
        $policyJson = $policy->toJson();
        self::assertStringContainsString("ORK:1:::::*", $policyJson);
        self::assertStringContainsString("ORK:2:::::*", $policyJson);
        self::assertStringContainsString("ORK::3::::*", $policyJson);
        self::assertStringContainsString("ORK:::::4:*", $policyJson);
    }

    public function testWhenNoMatch_thenPolicyDoesNotGrant() {
        $claim2 = new OrkClaim(ServiceCatalog::ORK, "ORK:2:::::*");
        $claim3 = new OrkClaim(ServiceCatalog::ORK, "ORK::3::::*");
        $claim4 = new OrkClaim(ServiceCatalog::ORK, "ORK:::::4:*");

        $policy = new Policy([$claim2, $claim3, $claim4]);
        $requirement = new OrkRequirement(ServiceCatalog::ORK, "ORK:1:7:8:9:10:ORK/AddKingdom");
        self::assertFalse($policy->isAuthorized($requirement));
    }

    public function testWhenAnyRequirementAllows_thenPolicyGrants() {

        $claim1 = new OrkClaim(ServiceCatalog::ORK, "ORK:1:::::*");
        $claim2 = new OrkClaim(ServiceCatalog::ORK, "ORK:2:::::*");
        $claim3 = new OrkClaim(ServiceCatalog::ORK, "ORK::3::::*");
        $claim4 = new OrkClaim(ServiceCatalog::ORK, "ORK:::::4:*");

        $policy = new Policy([$claim1, $claim2, $claim3, $claim4]);
        $requirement = new OrkRequirement(ServiceCatalog::ORK, "ORK:1:7:8:9:10:ORK/AddKingdom");
        self::assertTrue($policy->isAuthorized($requirement));
    }

    public function testIsComparesCanonicalPolicyJson(): void
    {
        $claim1 = new OrkClaim(ServiceCatalog::ORK, "ORK:1:::::*");
        $claim2 = new OrkClaim(ServiceCatalog::ORK, "ORK:2:::::*");

        $policyA = new Policy([$claim1, $claim2]);
        $policyB = new Policy([$claim2, $claim1]);

        self::assertTrue($policyA->is($policyB));
    }

    public function testIsReturnsFalseForDifferentPolicies(): void
    {
        $policyA = new Policy([new OrkClaim(ServiceCatalog::ORK, "ORK:1:::::*")]);
        $policyB = new Policy([new OrkClaim(ServiceCatalog::ORK, "ORK:2:::::*")]);

        self::assertFalse($policyA->is($policyB));
    }
}