<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTISectionTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTISection::class, new ilQTISection());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTISection();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle() : void
    {
        $instance = new ilQTISection();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetXmllang() : void
    {
        $instance = new ilQTISection();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetComment() : void
    {
        $instance = new ilQTISection();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
