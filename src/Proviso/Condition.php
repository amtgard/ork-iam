<?php

namespace Amtgard\IAM\Proviso;

/*
 * A single requirement for a given ORN
 *
 * In order to have a valid claim (permissions) on
 */

use Amtgard\IAM\OrkServices;
use Amtgard\IAM\ORN\OrnSegmentLabel;

class Condition extends Proviso
{

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

    protected function getOrnMatcher(OrkServices $service): string {
        $matcher = '/^' . $service->name . ':(\d+:|\*:|:)+((\w+|\*)|((\w+)\/(\w+|\*)))$/';
        return $matcher;
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
