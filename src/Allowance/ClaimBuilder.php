<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\ORN\OrnSegmentLabel;
use Amtgard\IAM\ProvisoSlot;
use Amtgard\IAM\ServiceIdentifier;

class ClaimBuilder
{
    private ServiceIdentifier $prefix;

    /** @var array<string, int|string> */
    private array $segments = [];

    private ?string $resource = null;
    private ?string $procedure = null;

    public static function forPrefix(OrkServices|string|ServiceIdentifier $prefix): self
    {
        $builder = new self();
        if ($prefix instanceof ServiceIdentifier) {
            $builder->prefix = $prefix;
        } elseif ($prefix instanceof OrkServices) {
            $builder->prefix = ServiceIdentifier::from($prefix->value);
        } else {
            $builder->prefix = ServiceIdentifier::from($prefix);
        }

        return $builder;
    }

    public function segment(
        OrkServices|ProvisoSlot|OrnSegmentLabel|string $label,
        int|string|null $value
    ): self {
        $segmentLabel = $label instanceof OrnSegmentLabel
            ? $label
            : OrnSegmentLabel::from($label);
        $this->segments[$segmentLabel->name] = $value ?? '';

        return $this;
    }

    public function resource(string $resource, ?string $procedure = null): self
    {
        $this->resource = $resource;
        $this->procedure = $procedure;

        return $this;
    }

    public function build(): Claim
    {
        return ClaimFactory::createOrn($this->buildOrnString());
    }

    public function buildOrnString(): string
    {
        $claimClass = OrnClassMap::getClaimClass($this->prefix->name);
        $schema = (new \ReflectionClass($claimClass))
            ->newInstanceWithoutConstructor()
            ->ornSegmentSchema();

        $segmentValues = array_map(
            function ($label) {
                $name = ($label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label))->name;
                if (!array_key_exists($name, $this->segments)) {
                    return '';
                }

                $value = $this->segments[$name];

                return $value === '' ? '' : (string) $value;
            },
            $schema
        );

        $resourcePart = $this->buildResourcePart();

        return $this->prefix->name . ':' . implode(':', $segmentValues) . ':' . $resourcePart;
    }

    private function buildResourcePart(): string
    {
        if ($this->resource === null) {
            return '*';
        }

        if ($this->procedure === null || $this->procedure === '') {
            return $this->resource;
        }

        return $this->resource . '/' . $this->procedure;
    }
}
