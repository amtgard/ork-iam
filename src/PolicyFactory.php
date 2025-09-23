<?php

namespace Amtgard\IAM;

use Amtgard\IAM\Allowance\Policy;

class PolicyFactory
{
    public static function fromOrn(array $orns) {
        $ornSet = [];
        foreach ($orns as $orn) {
            $ornSet[] = ClaimFactory::createOrn($orn);
        }
        return new Policy($ornSet);
    }
}