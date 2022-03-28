<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRenderHotspotTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderHotspot::class, new ilQTIRenderHotspot());
    }

    public function testSetGetMinnumber() : void
    {
        $instance = new ilQTIRenderHotspot();
        $instance->setMinnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMinnumber());
    }

    public function testSetGetMaxnumber() : void
    {
        $instance = new ilQTIRenderHotspot();
        $instance->setMaxnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxnumber());
    }
}
