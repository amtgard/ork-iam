<?php

namespace Amtgard\IAM\Requirement;

use Amtgard\IAM\Allowance\Claim;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\OrkResourceName;
use Amtgard\IAM\Orn\Condition;
use Amtgard\IAM\Orn\OrnSegment;
use Amtgard\IAM\Orn\OrnSegmentLabel;
use Amtgard\IAM\Resource;

abstract class Requirement extends OrkResourceName
{
    /** @var Condition[] */
    private array $conditions = [];

    protected function validResource(Resource $resource): bool
    {
        return isset($this->getResourceMap()[$resource->resource]) &&
            in_array($resource->procedure, $this->getResourceMap($resource->resource));
    }

    public function setSegment(OrnSegment $binding): void
    {
        $this->conditions[$binding->getLabel()->name] = $binding;
    }

    public function getSegment(OrnSegmentLabel|ServiceCatalog|string $label): OrnSegment
    {
        $key = ($label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label))->name;

        return $this->conditions[$key];
    }

    public function getSegments(): array
    {
        return $this->conditions;
    }

    protected function buildSegment(OrnSegmentLabel $label, int|string $value): OrnSegment
    {
        return new Condition($label, $value);
    }

    public function allows(Claim $claim): bool
    {
        if (!$claim->getPrefix()->equals($this->getPrefix())) {
            return false;
        }

        foreach ($claim->getSegments() as $grant) {
            if ($this->allowsGrant($grant)) {
                return $this->resourceComparison($claim);
            }
        }

        return false;
    }

    protected function getOrnMatcher(\Amtgard\IAM\Orn\OrnPrefix $prefix): string
    {
        return '/^' . preg_quote($prefix->name, '/') . ':(\d+:|:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
    }

    public function allowsGrant(OrnSegment $grant): bool
    {
        foreach ($this->ornSegmentLabels() as $label) {
            if ($grant->getLabel()->equals($label)) {
                return $this->getSegment($label)->allows($grant);
            }
        }
        throw new \InvalidArgumentException('Claim grant could not be matched to a requirement condition.');
    }

    public function resourceComparison(Claim $claim)
    {
        return $claim->getResource()->resource === '*' ||
            ($claim->getResource()->resource === $this->getResource()->resource && $claim->getResource()->procedure === '*') ||
            ($claim->getResource()->resource === $this->getResource()->resource && $claim->getResource()->procedure === $this->getResource()->procedure);
    }
}
