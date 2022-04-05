<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIOutcomesTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIOutcomes::class, new ilQTIOutcomes());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIOutcomes();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
