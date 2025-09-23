<?php

namespace Amtgard\IAM\Proviso;

use Amtgard\IAM\OrkService;

abstract class Proviso
{
    public abstract function getService(): OrkService;
    public abstract function setService(OrkService $service);

    public abstract function getId(): null|string|int;
    public abstract function setId(null|string|int $id);
    public function __construct(OrkService $service, null|string|int $id)
    {
        $this->setService($service);
        if ((is_string($id) && $id === '*')) {
            $this->setId($id);
        } elseif (is_numeric($id)) {
            settype($id, 'int');
            $this->setId($id);
        } elseif (strlen($id) === 0) {
            $this->setId(null);
        } else {
            throw new \InvalidArgumentException("Invalid proviso. Proviso must be a integer or the string '*'");
        }
    }

    /*
     * Whether this proviso allows the other $proviso
     */
    public function allows(Proviso $proviso): bool {
        return !is_null($this->getId()) && $proviso->getService()->value === $this->getService()->value &&
            ($proviso->getId() === '*' || $this->getId() === '*' || $this->getId() === $proviso->getId());
    }

}