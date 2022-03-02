<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIPresentationMaterialTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIPresentationMaterial::class, new ilQTIPresentationMaterial());
    }

    public function testAddFlowMat() : void
    {
        $flowMat = $this->getMockBuilder(ilQTIFlowMat::class)->disableOriginalConstructor()->getMock();
        $instance = new ilQTIPresentationMaterial();

        $this->assertEquals(null, $instance->getFlowMat(0));
        $this->assertEquals(null, $instance->getFlowMat(1));

        $instance->addFlowMat($flowMat);

        $this->assertEquals($flowMat, $instance->getFlowMat(0));
        $this->assertEquals(null, $instance->getFlowMat(1));
    }
}
