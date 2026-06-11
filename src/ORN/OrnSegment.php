<?php

namespace Amtgard\IAM\Orn;

use Amtgard\IAM\Catalog\ServiceCatalog;

/**
 * Label + value binding for one ORN middle segment.
 */
abstract class OrnSegment
{
    abstract public function getLabel(): OrnSegmentLabel;
    abstract public function setLabel(OrnSegmentLabel $label);

    abstract public function getValue(): null|string|int;
    abstract public function setValue(null|string|int $value);

    public function toCatalogEntry(): ServiceCatalog
    {
        return $this->getLabel()->toCatalogEntry()
            ?? throw new \LogicException(
                "Segment label {$this->getLabel()->name} is not a built-in ServiceCatalog entry."
            );
    }

    public function __construct(OrnSegmentLabel|ServiceCatalog|string $label, null|string|int $value)
    {
        $this->setLabel(OrnSegmentLabel::from($label));
        if ((is_string($value) && $value === '*')) {
            $this->setValue($value);
        } elseif (is_numeric($value)) {
            settype($value, 'int');
            $this->setValue($value);
        } elseif (strlen($value) === 0) {
            $this->setValue(null);
        } else {
            throw new \InvalidArgumentException("Invalid segment value. Value must be an integer or the string '*'");
        }
    }

    public function allows(OrnSegment $other): bool
    {
        return !is_null($this->getValue())
            && $other->getLabel()->equals($this->getLabel())
            && ($other->getValue() === '*' || $this->getValue() === '*' || $this->getValue() === $other->getValue());
    }
}
