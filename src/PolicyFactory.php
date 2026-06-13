<?php

namespace Amtgard\IAM;

use Amtgard\IAM\Allowance\PolicyBuilder;

class PolicyFactory
{
    public static function fromOrn(array $orns) {
        $builder = PolicyBuilder::create();
        foreach ($orns as $orn) {
            $builder->addOrn($orn);
        }

        return $builder->build();
    }
}