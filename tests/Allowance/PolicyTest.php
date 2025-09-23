<?php

namespace Tests\Amtgard\IAM\Allowance;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\OrkService;
use Amtgard\IAM\ORN\Definitions\OrkClaim;
use Amtgard\IAM\ORN\Definitions\OrkRequirement;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function testToJson() {
        $claim1 = new OrkClaim(OrkService::ORK, "ORK:1:::::*");
        $claim2 = new OrkClaim(OrkService::ORK, "ORK:2:::::*");
        $claim3 = new OrkClaim(OrkService::ORK, "ORK::3::::*");
        $claim4 = new OrkClaim(OrkService::ORK, "ORK:::::4:*");

        $policy = new Policy([$claim1, $claim2, $claim3, $claim4]);
        $policyJson = $policy->toJson();
        self::assertStringContainsString("ORK:1:::::*", $policyJson);
        self::assertStringContainsString("ORK:2:::::*", $policyJson);
        self::assertStringContainsString("ORK::3::::*", $policyJson);
        self::assertStringContainsString("ORK:::::4:*", $policyJson);
    }

    public function testWhenNoMatch_thenPolicyDoesNotGrant() {
        $claim2 = new OrkClaim(OrkService::ORK, "ORK:2:::::*");
        $claim3 = new OrkClaim(OrkService::ORK, "ORK::3::::*");
        $claim4 = new OrkClaim(OrkService::ORK, "ORK:::::4:*");

        $policy = new Policy([$claim2, $claim3, $claim4]);
        $requirement = new OrkRequirement(OrkService::ORK, "ORK:1:7:8:9:10:ORK/AddKingdom");
        self::assertFalse($policy->grants($requirement));
    }

    public function testWhenAnyRequirementAllows_thenPolicyGrants() {

        $claim1 = new OrkClaim(OrkService::ORK, "ORK:1:::::*");
        $claim2 = new OrkClaim(OrkService::ORK, "ORK:2:::::*");
        $claim3 = new OrkClaim(OrkService::ORK, "ORK::3::::*");
        $claim4 = new OrkClaim(OrkService::ORK, "ORK:::::4:*");

        $policy = new Policy([$claim1, $claim2, $claim3, $claim4]);
        $requirement = new OrkRequirement(OrkService::ORK, "ORK:1:7:8:9:10:ORK/AddKingdom");
        self::assertTrue($policy->grants($requirement));
    }
}