<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\OrkResourceName;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Orn\OrnSegmentLabel;
use Amtgard\IAM\ProvisoSlot;
use Amtgard\IAM\Proviso\Grant;
use Amtgard\IAM\Proviso\Proviso;
use Amtgard\IAM\Resource;
use Amtgard\Traits\Builder\Builder;

abstract class Claim extends OrkResourceName
{
    use Builder;

    /**
     * @var Grant[]
     */
    private array $grants = [];

    protected function validResource(Resource $resource): bool
    {
        return $resource->resource == '*' ||
            (isset($this->getResourceMap()[$resource->resource]) &&
                ($resource->procedure === '*' ||
                    in_array($resource->procedure, $this->getResourceMap($resource->resource))));
    }

    public function setProviso(Proviso $proviso)
    {
        $this->grants[$proviso->getSegmentLabel()->name] = $proviso;
    }

    public function getProviso(OrnSegmentLabel|ProvisoSlot|OrkServices|string $slot): Proviso
    {
        $key = ($slot instanceof OrnSegmentLabel ? $slot : OrnSegmentLabel::from($slot))->name;

        return $this->grants[$key];
    }

    public function getProvisos(): array
    {
        return $this->grants;
    }

    protected function buildProviso(OrnSegmentLabel|ProvisoSlot $slot, int|string $id): Proviso
    {
        return new Grant($slot, $id);
    }

}
