<?php

namespace Amtgard\IAM\Requirement;

use Amtgard\IAM\Allowance\Claim;
use Amtgard\IAM\OrkResourceName;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORN\OrnSegmentLabel;
use Amtgard\IAM\ProvisoSlot;
use Amtgard\IAM\Proviso\Condition;
use Amtgard\IAM\Proviso\Proviso;
use Amtgard\IAM\Resource;

abstract class Requirement extends OrkResourceName
{
    /**
     * @var Condition[]
     */
    private array $conditions;


    protected function validResource(Resource $resource): bool
    {
        $r = $resource->resource;
        return isset($this->getResourceMap()[$resource->resource]) &&
            in_array($resource->procedure, $this->getResourceMap($resource->resource));
    }

    public function setProviso(Proviso $proviso)
    {
        $this->conditions[$proviso->getSegmentLabel()->name] = $proviso;
    }

    public function getProviso(OrnSegmentLabel|ProvisoSlot|OrkServices|string $slot): Proviso
    {
        $key = ($slot instanceof OrnSegmentLabel ? $slot : OrnSegmentLabel::from($slot))->name;

        return $this->conditions[$key];
    }

    public function getProvisos(): array
    {
        return $this->conditions;
    }

    public function buildProviso(OrnSegmentLabel|ProvisoSlot $slot, int|string $id): Proviso
    {
        return new Condition($slot, $id);
    }

    public function allows(Claim $claim): bool {
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

    protected function getOrnMatcher(\Amtgard\IAM\ServiceIdentifier $service): string {
        $matcher = '/^' . preg_quote($service->name, '/') . ':(\d+:|:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
        return $matcher;
    }

    function allowsGrant(Proviso $grant): bool {
        foreach ($this->ornSegmentLabels() as $label) {
            if ($grant->getSegmentLabel()->equals($label)) {
                return $this->getSegment($label)->allows($grant);
            }
        }
        throw new \InvalidArgumentException("Claim grant could not be matched to a requirement condition.");
    }

    public function resourceComparison(Claim $claim) {
        return $claim->getResource()->resource === '*' ||
            ($claim->getResource()->resource === $this->getResource()->resource && $claim->getResource()->procedure === '*') ||
            ($claim->getResource()->resource === $this->getResource()->resource && $claim->getResource()->procedure === $this->getResource()->procedure);
    }
}
