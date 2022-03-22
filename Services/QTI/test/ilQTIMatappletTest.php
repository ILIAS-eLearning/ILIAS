<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMatappletTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMatapplet::class, new ilQTIMatapplet());
    }

    public function testSetGetEmbedded() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setEmbedded('Some input.');
        $this->assertEquals('Some input.', $instance->getEmbedded());
    }

    public function testSetGetLabel() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetUri() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setUri('Some input.');
        $this->assertEquals('Some input.', $instance->getUri());
    }

    public function testSetGetX0() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setX0('Some input.');
        $this->assertEquals('Some input.', $instance->getX0());
    }

    public function testSetGetY() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setY0('Some input.');
        $this->assertEquals('Some input.', $instance->getY0());
    }

    public function testSetGetWidth() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setWidth('Some input.');
        $this->assertEquals('Some input.', $instance->getWidth());
    }

    public function testSetGetHeight() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setHeight('Some input.');
        $this->assertEquals('Some input.', $instance->getHeight());
    }

    public function testSetGetEntityref() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setEntityref('Some input.');
        $this->assertEquals('Some input.', $instance->getEntityref());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIMatapplet();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }
}
