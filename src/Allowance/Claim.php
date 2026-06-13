<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\OrkResourceName;
use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\ORN\Grant;
use Amtgard\IAM\ORN\OrnSegment;
use Amtgard\IAM\ORN\OrnSegmentLabel;
use Amtgard\IAM\Resource;
use Amtgard\Traits\Builder\Builder;

abstract class Claim extends OrkResourceName
{
    use Builder;

    /** @var Grant[] */
    private array $grants = [];

    protected function validResource(Resource $resource): bool
    {
        return $resource->resource == '*' ||
            (isset($this->getResourceMap()[$resource->resource]) &&
                ($resource->procedure === '*' ||
                    in_array($resource->procedure, $this->getResourceMap($resource->resource))));
    }

    public function setSegment(OrnSegment $binding): void
    {
        $this->grants[$binding->getLabel()->name] = $binding;
    }

    public function getSegment(OrnSegmentLabel|ServiceCatalog|string $label): OrnSegment
    {
        $key = ($label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label))->name;

        return $this->grants[$key];
    }

    public function getSegments(): array
    {
        return $this->grants;
    }

    protected function buildSegment(OrnSegmentLabel $label, int|string $value): OrnSegment
    {
        return new Grant($label, $value);
    }
}
