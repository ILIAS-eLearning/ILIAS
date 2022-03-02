<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResprocessingTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResprocessing::class, new ilQTIResprocessing());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTIResprocessing();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }

    public function testSetGetScoremodel() : void
    {
        $instance = new ilQTIResprocessing();
        $instance->setScoremodel('Some input.');
        $this->assertEquals('Some input.', $instance->getScoremodel());
    }
}
