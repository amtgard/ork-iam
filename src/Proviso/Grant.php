<?php

namespace Amtgard\IAM\Proviso;

use Amtgard\IAM\OrkServices;

class Grant extends Proviso {
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

    public function getId(): null|string|int
    {
        return $this->id;
    }

    public function setId(null|int|string $id)
    {
        $this->id = $id;
    }
}