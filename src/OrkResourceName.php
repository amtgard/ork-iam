<?php

namespace Amtgard\IAM;

use Amtgard\IAM\ORN\OrnSegmentLabel;
use Amtgard\IAM\Proviso\Proviso;
use Amtgard\Traits\Builder\Builder;
use Amtgard\Traits\Builder\PostInit;

/**
 * For a given service, each object produced by the service carries an ORN, which is a list of requirements.
 *
 * @see docs/ORN-ONTOLOGY.md
 */
abstract class OrkResourceName
{

    use Builder;

    protected ServiceIdentifier $service;
    protected string $orn;

    public function getPrefix(): ServiceIdentifier
    {
        return $this->getServiceIdentifier();
    }

    public function getServiceIdentifier(): ServiceIdentifier
    {
        return $this->service;
    }

    public function getService(): OrkServices
    {
        return $this->service->toOrkServices()
            ?? throw new \LogicException(
                "Service prefix {$this->service->name} is not a built-in OrkServices case."
            );
    }

    public function setSegment(Proviso $binding): void
    {
        $this->setProviso($binding);
    }

    /**
     * @deprecated 2.0.0 Use {@see setSegment()} instead.
     */
    abstract public function setProviso(Proviso $proviso);

    public function getSegment(OrnSegmentLabel|ProvisoSlot|OrkServices|string $label): Proviso
    {
        return $this->getProviso($label);
    }

    /**
     * @deprecated 2.0.0 Use {@see getSegment()} instead.
     */
    abstract public function getProviso(OrnSegmentLabel|ProvisoSlot|OrkServices|string $slot): Proviso;

    /**
     * @return Proviso[]
     */
    public function getSegments(): array
    {
        return $this->getProvisos();
    }

    /**
     * @return Proviso[]
     * @deprecated 2.0.0 Use {@see getSegments()} instead.
     */
    abstract public function getProvisos(): array;

    protected Resource $resource;
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Ordered segment labels defining this ORN type's middle-segment layout.
     *
     * @return (OrnSegmentLabel|OrkServices|string)[]
     */
    public function ornSegmentSchema(): array
    {
        return $this->serviceFormat();
    }

    /**
     * @return (OrnSegmentLabel|OrkServices|string)[]
     * @deprecated 2.0.0 Use {@see ornSegmentSchema()} instead.
     */
    abstract protected function serviceFormat(): array;

    /**
     * @return OrnSegmentLabel[]
     */
    protected function ornSegmentLabels(): array
    {
        return array_map(
            fn ($label) => $label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label),
            $this->ornSegmentSchema()
        );
    }

    /**
     * @return OrnSegmentLabel[]
     * @deprecated 2.0.0 Use {@see ornSegmentLabels()} instead.
     */
    protected function provisoSlots(): array
    {
        return $this->ornSegmentLabels();
    }

    public function segmentOffset(OrnSegmentLabel|ProvisoSlot|OrkServices|string $label): int
    {
        $segmentLabel = $label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label);

        return $segmentLabel->offsetIn($this->ornSegmentSchema());
    }

    public function buildOrn(): string {
        return $this->service->name . ':' .
            implode(':',
                array_map(function (OrnSegmentLabel $label) {
                    $id = current(array_filter($this->getProvisos(),
                        fn ($binding) => $binding->getSegmentLabel()->equals($label)
                    ))->getSegmentValue();
                    return is_null($id) ? '' : $id;
                }, $this->ornSegmentLabels())) . ':' . $this->resource->toString();
    }

    private function parseOrn(string $orn): array {
        try {
            return array_map(function (OrnSegmentLabel $label, string $value) {
                return $this->buildSegment($label, $value);
            }, $this->ornSegmentLabels(), explode(':', $orn, -1));
        } catch (\TypeError $e) {
            throw new \InvalidArgumentException("Invalid proviso set.");
        }
    }

    protected function buildSegment(OrnSegmentLabel $label, string|int $value): Proviso
    {
        return $this->buildProviso($label, $value);
    }

    /**
     * @deprecated 2.0.0 Use {@see buildSegment()} instead.
     */
    abstract protected function buildProviso(OrnSegmentLabel|ProvisoSlot $slot, string|int $id): Proviso;

    protected function getOrnMatcher(ServiceIdentifier $service): string {
        $matcher = '/^' . preg_quote($service->name, '/') . ':(\d+:|:|\*:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
        return $matcher;
    }

    protected function validOrnFormat(ServiceIdentifier $service, $orn): bool {
        $matcher = $this->getOrnMatcher($service);
        return preg_match($matcher, $orn);
    }

    /**
     * @param String $resource
     * @return array
     */
    protected abstract function getResourceMap(String $resource = null): array;

    protected abstract function validResource(Resource $resource): bool;

    protected function validSegmentBindings(array $bindings): bool
    {
        return count($bindings) === count($this->ornSegmentLabels());
    }

    /**
     * @deprecated 2.0.0 Use {@see validSegmentBindings()} instead.
     */
    protected function validProvisos(array $provisos): bool {
        return $this->validSegmentBindings($provisos);
    }

    #[PostInit]
    public function init() {
        $ornParts = explode(':', $this->orn, 2);
        $this->service = ServiceIdentifier::from($ornParts[0]);
        if (!$this->validOrnFormat($this->service, $this->orn)) {
            throw new \InvalidArgumentException("Invalid orn format.");
        }
        $ornProvisos = explode(':', $ornParts[1]);
        $this->resource = new Resource(end($ornProvisos));
        if (!$this->validResource($this->resource)) {
            throw new \InvalidArgumentException("Invalid resource definition.");
        }
        $bindings = $this->parseOrn($ornParts[1]);
        if (!$this->validSegmentBindings($bindings)) {
            throw new \InvalidArgumentException("Invalid proviso set.");
        }
        foreach ($bindings as $binding) {
            $this->setSegment($binding);
        }
    }

    public function __construct(ServiceIdentifier|OrkServices|string $service, string $orn) {
        if ($service instanceof ServiceIdentifier) {
            $this->service = $service;
        } elseif ($service instanceof OrkServices) {
            $this->service = ServiceIdentifier::from($service->value);
        } else {
            $this->service = ServiceIdentifier::from($service);
        }
        $this->orn = $orn;
        $this->init();
    }

}
