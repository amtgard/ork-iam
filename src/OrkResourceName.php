<?php

namespace Amtgard\IAM;

use Amtgard\IAM\Proviso\Proviso;
use Amtgard\Traits\Builder\Builder;
use Amtgard\Traits\Builder\PostInit;

/**
 * For a given service, each object produced by the service carries an ORN, which is a list of requirements
 */
abstract class OrkResourceName
{

    use Builder;

    protected \Amtgard\IAM\OrkService $service;
    protected string $orn;

    public function getService(): \Amtgard\IAM\OrkService
    {
        return $this->service;
    }

    public abstract function setProviso(Proviso $proviso);
    public abstract function getProviso(\Amtgard\IAM\OrkService $service): Proviso;

    /**
     * @return Proviso[]
     */
    public abstract function getProvisos(): array;

    protected \Amtgard\IAM\Resource $resource;
    public function getResource(): \Amtgard\IAM\Resource
    {
        return $this->resource;
    }
    /**
     * @return \Amtgard\IAM\OrkService
     */
    abstract protected function serviceFormat(): array;

    public function buildOrn(): string {
        return $this->service->name . ':' .
            implode(':',
                array_map(function ($s) {
                    $id = current(array_filter($this->getProvisos(),
                        function($o) use ($s) {
                        return $o->getService() === $s;
                    }))->getId();
                    return is_null($id) ? '' : $id;
                }, $this->serviceFormat())) . ':' . $this->resource->toString();
    }

    private function parseOrn(string $orn): array {
        try {
            return array_map(function ($svc, $id) {
                return $this->buildProviso($svc, $id);
            }, $this->serviceFormat(), explode(':', $orn, -1));
        } catch (\TypeError $e) {
            throw new \InvalidArgumentException("Invalid proviso set.");
        }
    }

    protected abstract function buildProviso(\Amtgard\IAM\OrkService $service, string|int $id): Proviso;

    protected function getOrnMatcher(\Amtgard\IAM\OrkService $service): string {
        $matcher = '/^' . $service->name . ':(\d+:|:|\*:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
        return $matcher;
    }

    protected function validOrnFormat(\Amtgard\IAM\OrkService $service, $orn): bool {
        $matcher = $this->getOrnMatcher($service);
        return preg_match($matcher, $orn);
    }

    /**
     * @param String $resource
     * @return array
     */
    protected abstract function getResourceMap(String $resource = null): array;

    protected abstract function validResource(\Amtgard\IAM\Resource $resource): bool;

    protected function validProvisos(array $provisos): bool {
        return count($provisos) === count($this->serviceFormat());
    }

    #[PostInit]
    public function init() {
        if (!$this->validOrnFormat($this->service, $this->orn)) {
            throw new \InvalidArgumentException("Invalid orn format.");
        }
        $ornParts = explode(':', $this->orn, 2);
        $this->service = \Amtgard\IAM\OrkService::from($ornParts[0]);
        $ornProvisos = explode(':', $ornParts[1]);
        $this->resource = new \Amtgard\IAM\Resource(end($ornProvisos));
        if (!$this->validResource($this->resource)) {
            throw new \InvalidArgumentException("Invalid resource definition.");
        }
        $provisos = $this->parseOrn($ornParts[1]);
        if (!$this->validProvisos($provisos)) {
            throw new \InvalidArgumentException("Invalid proviso set.");
        }
        foreach ($provisos as $proviso) {
            $this->setProviso($proviso);
        }
    }

    public function __construct(\Amtgard\IAM\OrkService $service, string $orn) {
        $this->service = $service;
        $this->orn = $orn;
        $this->init();
    }

}