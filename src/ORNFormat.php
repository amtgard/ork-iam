<?php

namespace Amtgard\IAM;

abstract class ORNFormat
{
    /**
     * @return (\Amtgard\IAM\ORN\OrnSegmentLabel|\Amtgard\IAM\OrkServices|string)[]
     */
    public static function ornSegmentSchema(): array
    {
        return static::serviceFormat();
    }

    /**
     * @return (\Amtgard\IAM\ORN\OrnSegmentLabel|\Amtgard\IAM\OrkServices|string)[]
     * @deprecated 2.0.0 Use {@see ornSegmentSchema()} instead.
     */
    public static abstract function serviceFormat(): array;

    public static abstract function getValidResourceMap($resource = null): array;
}