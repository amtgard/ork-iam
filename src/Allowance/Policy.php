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

    public function isAuthorized(Requirement $requirement): bool {
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
        sort($ornSet);
        return json_encode($ornSet);
    }

    public function is(Policy $target): bool {
        return $this->toJson() === $target->toJson();
    }
}
