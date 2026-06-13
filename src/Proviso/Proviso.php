<?php

namespace Amtgard\IAM\Proviso;

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORN\OrnSegmentLabel;
use Amtgard\IAM\ProvisoSlot;

/**
 * Label + value binding for one ORN middle segment.
 *
 * @deprecated 2.0.0 Renamed to {@see \Amtgard\IAM\ORN\OrnSegment}.
 */
abstract class Proviso
{
    public function getSegmentLabel(): OrnSegmentLabel
    {
        return $this->getSlot();
    }

    /**
     * @deprecated 2.0.0 Use {@see getSegmentLabel()} instead.
     */
    abstract public function getSlot(): OrnSegmentLabel;

    /**
     * @deprecated 2.0.0 Use {@see setSegmentLabel()} instead.
     */
    abstract public function setSlot(OrnSegmentLabel $slot);

    public function setSegmentLabel(OrnSegmentLabel $label): void
    {
        $this->setSlot($label);
    }

    public function getSegmentValue(): null|string|int
    {
        return $this->getId();
    }

    /**
     * @deprecated 2.0.0 Use {@see getSegmentValue()} instead.
     */
    abstract public function getId(): null|string|int;

    /**
     * @deprecated 2.0.0 Use {@see setSegmentValue()} instead.
     */
    abstract public function setId(null|string|int $id);

    public function setSegmentValue(null|string|int $value): void
    {
        $this->setId($value);
    }

    public function getService(): OrkServices
    {
        return $this->getSegmentLabel()->toOrkServices()
            ?? throw new \LogicException(
                "Segment label {$this->getSegmentLabel()->name} is not a built-in OrkServices catalog entry."
            );
    }

    public function __construct(OrnSegmentLabel|ProvisoSlot|OrkServices|string $label, null|string|int $value)
    {
        $this->setSegmentLabel(OrnSegmentLabel::from($label));
        if ((is_string($value) && $value === '*')) {
            $this->setSegmentValue($value);
        } elseif (is_numeric($value)) {
            settype($value, 'int');
            $this->setSegmentValue($value);
        } elseif (strlen($value) === 0) {
            $this->setSegmentValue(null);
        } else {
            throw new \InvalidArgumentException("Invalid segment value. Value must be an integer or the string '*'");
        }
    }

    public function allows(Proviso $other): bool {
        return !is_null($this->getSegmentValue())
            && $other->getSegmentLabel()->equals($this->getSegmentLabel())
            && ($other->getSegmentValue() === '*' || $this->getSegmentValue() === '*' || $this->getSegmentValue() === $other->getSegmentValue());
    }

}
