<?php

namespace Amtgard\IAM\Orn;

use Amtgard\IAM\Catalog\ServiceCatalog;

class Condition extends OrnSegment
{
    private OrnSegmentLabel $label;
    private null|string|int $value;

    public function getLabel(): OrnSegmentLabel
    {
        return $this->label;
    }

    public function setLabel(OrnSegmentLabel $label): void
    {
        $this->label = $label;
    }

    public function getValue(): null|string|int
    {
        return $this->value;
    }

    public function setValue(null|int|string $value): void
    {
        $this->value = $value;
    }

    protected function getOrnMatcher(ServiceCatalog $catalog): string
    {
        return '/^' . $catalog->name . ':(\d+:|\*:|:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
    }
}
