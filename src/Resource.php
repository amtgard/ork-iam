<?php

namespace Amtgard\IAM;

class Resource
{
    public string $resource;
    public ?string $procedure;

    public function __construct(string $resourceString) {
        if (strpos($resourceString, '/') !== false) {
            $resourceParts = explode('/', $resourceString);
            $this->resource = $resourceParts[0];
            $this->procedure = $resourceParts[1];
        } else {
            $this->resource = $resourceString;
            $this->procedure = null;
        }
    }

    public function toString(): string {
        if (is_null($this->procedure)) {
            return $this->resource;
        } else {
            return $this->resource . '/' . $this->procedure;
        }
    }
}