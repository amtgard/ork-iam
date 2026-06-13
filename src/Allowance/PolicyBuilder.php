<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\ClaimFactory;

class PolicyBuilder
{
    /** @var array<string, Claim> */
    private array $claims = [];

    public static function create(): self
    {
        return new self();
    }

    public static function from(Policy $policy): self
    {
        $builder = new self();
        foreach ($policy->getClaims() as $claim) {
            $builder->addClaim($claim);
        }

        return $builder;
    }

    public function addOrn(string $orn): self
    {
        return $this->addClaim(ClaimFactory::createOrn($orn));
    }

    public function addClaim(Claim $claim): self
    {
        $this->claims[$claim->buildOrn()] = $claim;

        return $this;
    }

    public function merge(Policy $policy): self
    {
        foreach ($policy->getClaims() as $claim) {
            $this->addClaim($claim);
        }

        return $this;
    }

    public function build(): Policy
    {
        return Policy::withClaims(array_values($this->claims));
    }
}
