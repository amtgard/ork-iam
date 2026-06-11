<?php

namespace Tests\Amtgard\IAM;

use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\PolicyFactory;
use PHPUnit\Framework\TestCase;

class PolicyFactoryTest extends TestCase
{
    public function testFromOrnBuildsPolicyFromOrnStrings(): void
    {
        $policy = PolicyFactory::fromOrn([
            'ORK:1:::::*',
            'ORK:2:::::*',
        ]);

        self::assertInstanceOf(Policy::class, $policy);
        self::assertTrue($policy->isAuthorized(
            new OrkRequirement(ServiceCatalog::ORK, 'ORK:1:7:8:9:10:ORK/AddKingdom')
        ));
    }
}
