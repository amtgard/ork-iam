<?php

namespace Amtgard\IAM\Proviso;

/*
 * A single requirement for a given ORN
 *
 * In order to have a valid claim (permissions) on
 */

use Amtgard\IAM\OrkService;

class Condition extends Proviso
{

    private OrkService $service;
    private null|string|int $id;

    public function getService(): OrkService
    {
        return $this->service;
    }

    public function setService(OrkService $service)
    {
        $this->service = $service;
    }

    protected function getOrnMatcher(OrkService $service): string {
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