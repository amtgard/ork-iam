<?php

namespace Amtgard\IAM\Proviso;

use Amtgard\IAM\Orn\OrnSegmentLabel;

class Grant extends Proviso {
    private OrnSegmentLabel $slot;
    private null|string|int $id;

    public function getSlot(): OrnSegmentLabel
    {
        return $this->slot;
    }

    public function setSlot(OrnSegmentLabel $slot)
    {
        $this->slot = $slot;
    }

    public function getId(): null|string|int
    {
        return $this->id;
    }

    public function setId(null|int|string $id)
    {
        $this->id = $id;
    }
}
