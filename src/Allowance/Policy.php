<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\Requirement\Requirement;

class Policy
{
    /**
     * @var Claim[]
     */
    protected array $claims;

    public function __construct(array $claims) {
        $this->claims = $claims;
        asort($this->claims);
    }

    public function grants(Requirement $requirement): bool {
        foreach ($this->claims as $claim) {
            if ($requirement->allows($claim)) {
                return true;
            }
        }
        return false;
    }

    public function toJson() {
        $ornSet = [];
        foreach ($this->claims as $orn) {
            $ornSet[] = $orn->buildOrn();
        }
        return json_encode($ornSet);
    }
}