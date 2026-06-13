<?php

namespace Amtgard\IAM;

use Amtgard\IAM\Catalog\ServiceCatalog;
use Amtgard\IAM\ORN\OrnSegmentLabel;

abstract class ORNFormat
{
    /**
     * @return (OrnSegmentLabel|ServiceCatalog|string)[]
     */
    public static abstract function ornSegmentSchema(): array;

    public static abstract function getValidResourceMap($resource = null): array;
}
