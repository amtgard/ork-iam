<?php

namespace Amtgard\IAM\Requirement;

use Amtgard\IAM\Allowance\Claim;
use Amtgard\IAM\OrkResourceName;
use Amtgard\IAM\OrkServices;
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
        $this->conditions[$proviso->getService()->name] = $proviso;
    }

    public function getProviso(OrkServices $service): Proviso
    {
        return $this->conditions[$service->name];
    }

    public function getProvisos(): array
    {
        return $this->conditions;
    }

    public function buildProviso(OrkServices $service, int|string $id): Proviso
    {
        return new Condition($service, $id);
    }

    public function allows(Claim $claim): bool {
        if (!$claim->getServiceIdentifier()->equals($this->getServiceIdentifier())) {
            return false;
        }

        foreach ($claim->getProvisos() as $grant) {
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
        if (in_array($grant->getService(), $this->serviceFormat())) {
            $condition = $this->getProviso($grant->getService());
            return $condition->allows($grant);
        }
        throw new \InvalidArgumentException("Claim grant could not be matched to a requirement condition.");
    }

    public function resourceComparison(Claim $claim) {
        return $claim->getResource()->resource === '*' ||
            ($claim->getResource()->resource === $this->getResource()->resource && $claim->getResource()->procedure === '*') ||
            ($claim->getResource()->resource === $this->getResource()->resource && $claim->getResource()->procedure === $this->getResource()->procedure);
    }
}