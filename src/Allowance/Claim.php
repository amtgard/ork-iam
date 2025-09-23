<?php

namespace Amtgard\IAM\Allowance;

use Amtgard\IAM\OrkResourceName;
use Amtgard\IAM\OrkService;
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
        $this->grants[$proviso->getService()->name] = $proviso;
    }

    public function getProviso(OrkService $service): Proviso
    {
        return $this->grants[$service->name];
    }

    public function getProvisos(): array
    {
        return $this->grants;
    }

    protected function buildProviso(OrkService $service, int|string $id): Proviso
    {
        return new Grant($service, $id);
    }

}