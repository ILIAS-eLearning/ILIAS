<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIPresentationTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIPresentation::class, new ilQTIPresentation());
    }

    public function testSetGetLabel() : void
    {
        $instance = new ilQTIPresentation();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetXmllang() : void
    {
        $instance = new ilQTIPresentation();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetX0() : void
    {
        $instance = new ilQTIPresentation();
        $instance->setX0('Some input.');
        $this->assertEquals('Some input.', $instance->getX0());
    }

    public function testSetGetY0() : void
    {
        $instance = new ilQTIPresentation();
        $instance->setY0('Some input.');
        $this->assertEquals('Some input.', $instance->getY0());
    }

    public function testSetGetWidth() : void
    {
        $instance = new ilQTIPresentation();
        $instance->setWidth('Some input.');
        $this->assertEquals('Some input.', $instance->getWidth());
    }

    public function testSetGetHeight() : void
    {
        $instance = new ilQTIPresentation();
        $instance->setHeight('Some input.');
        $this->assertEquals('Some input.', $instance->getHeight());
    }
}
