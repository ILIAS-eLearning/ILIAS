<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRenderHotspotTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderHotspot::class, new ilQTIRenderHotspot());
    }
}
