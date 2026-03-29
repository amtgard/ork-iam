<?php

namespace Amtgard\IAM\Proviso;

/*
 * A single requirement for a given ORN
 *
 * In order to have a valid claim (permissions) on
 */

use Amtgard\IAM\OrkServices;

class Condition extends Proviso
{

    private OrkServices $service;
    private null|string|int $id;

    public function getService(): OrkServices
    {
        return $this->service;
    }

    public function setService(OrkServices $service)
    {
        $this->service = $service;
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