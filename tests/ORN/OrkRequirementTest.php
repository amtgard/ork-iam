<?php

namespace Tests\Amtgard\IAM\ORN;

use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Proviso\Grant;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrkRequirementTest extends TestCase
{
    public function testAllowsMatchingOrkClaim(): void
    {
        $requirement = new OrkRequirement(OrkServices::ORK, 'ORK:1:7:8:9:10:ORK/AddKingdom');
        $claim = new OrkClaim(OrkServices::ORK, 'ORK:1:::::*');

        self::assertTrue($requirement->allows($claim));
    }

    public function testWhenGrantServiceNotInFormat_thenThrows(): void
    {
        $requirement = new OrkRequirement(OrkServices::ORK, 'ORK:1:7:8:9:10:ORK/AddKingdom');
        $grant = new Grant(OrkServices::Mundane, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Claim grant could not be matched to a requirement condition.');

        $requirement->allowsGrant($grant);
    }
}
