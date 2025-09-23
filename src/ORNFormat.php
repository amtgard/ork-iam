<?php

namespace Amtgard\IAM;

abstract class ORNFormat
{
    public static abstract function serviceFormat(): array;

    public static abstract function getValidResourceMap($resource = null): array;
}