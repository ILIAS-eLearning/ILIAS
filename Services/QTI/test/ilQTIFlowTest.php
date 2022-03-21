<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIFlowTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIFlow::class, new ilQTIFlow());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIFlow();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
