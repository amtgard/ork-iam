<?php

namespace Tests\Amtgard\IAM;

use PHPUnit\Framework\TestCase;
use Amtgard\IAM\Resource;
use function PHPUnit\Framework\assertEquals;

class ResourceTest extends TestCase
{
    public function testResourceToString() {
        $resource = new Resource("resource/procedure");
        assertEquals("resource/procedure", $resource->toString());
    }
}