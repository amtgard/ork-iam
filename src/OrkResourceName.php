<?php

namespace Amtgard\IAM;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\Orn\OrnPrefix;
use Amtgard\IAM\Orn\OrnSegment;
use Amtgard\IAM\Orn\OrnSegmentLabel;
use Amtgard\Traits\Builder\Builder;
use Amtgard\Traits\Builder\PostInit;

/**
 * @see docs/ORN-ONTOLOGY.md
 */
abstract class OrkResourceName
{
    use Builder;

    protected OrnPrefix $prefix;
    protected string $orn;

    public function getPrefix(): OrnPrefix
    {
        return $this->prefix;
    }

    public function toCatalogEntry(): ServiceCatalog
    {
        return $this->prefix->toCatalogEntry()
            ?? throw new \LogicException(
                "ORN prefix {$this->prefix->name} is not a built-in ServiceCatalog entry."
            );
    }

    abstract public function setSegment(OrnSegment $binding): void;

    abstract public function getSegment(OrnSegmentLabel|ServiceCatalog|string $label): OrnSegment;

    /** @return OrnSegment[] */
    abstract public function getSegments(): array;

    protected Resource $resource;

    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * @return (OrnSegmentLabel|ServiceCatalog|string)[]
     */
    abstract public function ornSegmentSchema(): array;

    /** @return OrnSegmentLabel[] */
    protected function ornSegmentLabels(): array
    {
        return array_map(
            fn ($label) => $label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label),
            $this->ornSegmentSchema()
        );
    }

    public function segmentOffset(OrnSegmentLabel|ServiceCatalog|string $label): int
    {
        $segmentLabel = $label instanceof OrnSegmentLabel ? $label : OrnSegmentLabel::from($label);

        return $segmentLabel->offsetIn($this->ornSegmentSchema());
    }

    public function buildOrn(): string
    {
        return $this->prefix->name . ':' .
            implode(':',
                array_map(function (OrnSegmentLabel $label) {
                    $value = current(array_filter(
                        $this->getSegments(),
                        fn ($binding) => $binding->getLabel()->equals($label)
                    ))->getValue();

                    return is_null($value) ? '' : $value;
                }, $this->ornSegmentLabels())) . ':' . $this->resource->toString();
    }

    private function parseOrn(string $orn): array
    {
        try {
            return array_map(function (OrnSegmentLabel $label, string $value) {
                return $this->buildSegment($label, $value);
            }, $this->ornSegmentLabels(), explode(':', $orn, -1));
        } catch (\TypeError $e) {
            throw new \InvalidArgumentException('Invalid segment binding set.');
        }
    }

    abstract protected function buildSegment(OrnSegmentLabel $label, string|int $value): OrnSegment;

    protected function getOrnMatcher(OrnPrefix $prefix): string
    {
        return '/^' . preg_quote($prefix->name, '/') . ':(\d+:|:|\*:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
    }

    protected function validOrnFormat(OrnPrefix $prefix, $orn): bool
    {
        return preg_match($this->getOrnMatcher($prefix), $orn);
    }

    protected abstract function getResourceMap(String $resource = null): array;

    protected abstract function validResource(Resource $resource): bool;

    protected function validSegmentBindings(array $bindings): bool
    {
        return count($bindings) === count($this->ornSegmentLabels());
    }

    #[PostInit]
    public function init()
    {
        $ornParts = explode(':', $this->orn, 2);
        $this->prefix = OrnPrefix::from($ornParts[0]);
        if (!$this->validOrnFormat($this->prefix, $this->orn)) {
            throw new \InvalidArgumentException('Invalid orn format.');
        }
        $ornSegments = explode(':', $ornParts[1]);
        $this->resource = new Resource(end($ornSegments));
        if (!$this->validResource($this->resource)) {
            throw new \InvalidArgumentException('Invalid resource definition.');
        }
        $bindings = $this->parseOrn($ornParts[1]);
        if (!$this->validSegmentBindings($bindings)) {
            throw new \InvalidArgumentException('Invalid segment binding set.');
        }
        foreach ($bindings as $binding) {
            $this->setSegment($binding);
        }
    }

    public function __construct(OrnPrefix|ServiceCatalog|string $prefix, string $orn)
    {
        if ($prefix instanceof OrnPrefix) {
            $this->prefix = $prefix;
        } elseif ($prefix instanceof ServiceCatalog) {
            $this->prefix = OrnPrefix::from($prefix->value);
        } else {
            $this->prefix = OrnPrefix::from($prefix);
        }
        $this->orn = $orn;
        $this->init();
    }
}
