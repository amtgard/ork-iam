<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\PolicyFactory;

class PolicyDocument
{
    public function __construct(
        private Policy $policy,
        private ?array $integratorDocument = null,
    ) {
    }

    public static function fromOrnList(array $orns): self
    {
        return new self(PolicyFactory::fromOrn($orns));
    }

    public static function fromJson(string $json): self
    {
        $orns = json_decode($json, true);
        if (!is_array($orns)) {
            throw new \InvalidArgumentException('Policy JSON must decode to an array of ORN strings.');
        }

        return self::fromOrnList($orns);
    }

    /**
     * Build from verified JWT claims after signature validation.
     *
     * @param array{policy_lines: string[], integrator_document?: array} $payload
     */
    public static function fromVerifiedPayload(array $payload): self
    {
        if (!isset($payload['policy_lines']) || !is_array($payload['policy_lines'])) {
            throw new \InvalidArgumentException('Verified payload must include policy_lines array.');
        }

        $integratorDocument = $payload['integrator_document'] ?? null;
        if ($integratorDocument !== null && !is_array($integratorDocument)) {
            throw new \InvalidArgumentException('integrator_document must be an array when present.');
        }

        return new self(PolicyFactory::fromOrn($payload['policy_lines']), $integratorDocument);
    }

    /**
     * @alias fromVerifiedPayload
     */
    public static function fromVerifiedJwt(array $payload): self
    {
        return self::fromVerifiedPayload($payload);
    }

    public function policy(): Policy
    {
        return $this->policy;
    }

    public function integratorDocument(): ?array
    {
        return $this->integratorDocument;
    }
}
