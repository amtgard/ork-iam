<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\ORN\OrnPrefix;
use Amtgard\IAM\ORN\OrnSegmentLabel;

class ClaimBuilder
{
    private OrnPrefix $prefix;

    /** @var array<string, int|string> */
    private array $segments = [];

    private ?string $resource = null;
    private ?string $procedure = null;

    public static function forPrefix(ServiceCatalog|string|OrnPrefix $prefix): self
    {
        $builder = new self();
        if ($prefix instanceof OrnPrefix) {
            $builder->prefix = $prefix;
        } elseif ($prefix instanceof ServiceCatalog) {
            $builder->prefix = OrnPrefix::from($prefix->value);
        } else {
            $builder->prefix = OrnPrefix::from($prefix);
        }

        return $builder;
    }

    public function segment(
        ServiceCatalog|OrnSegmentLabel|string $label,
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
