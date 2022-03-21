<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIFlowMatTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIFlowMat::class, new ilQTIFlowMat());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIFlowMat();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
