<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIMattextTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMattext::class, new ilQTIMattext());
    }

    public function testSetGetTexttype() : void
    {
        $instance = new ilQTIMattext();
        $instance->setTexttype('Some input.');
        $this->assertEquals('Some input.', $instance->getTexttype());
    }

    public function testSetGetLabel() : void
    {
        $instance = new ilQTIMattext();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetCharset() : void
    {
        $instance = new ilQTIMattext();
        $instance->setCharset('Some input.');
        $this->assertEquals('Some input.', $instance->getCharset());
    }

    public function testSetGetUri() : void
    {
        $instance = new ilQTIMattext();
        $instance->setUri('Some input.');
        $this->assertEquals('Some input.', $instance->getUri());
    }

    /**
     * @dataProvider xmlSpaces
     */
    public function testSetGetXmlspace(string $input, ?string $expected) : void
    {
        $instance = new ilQTIMattext();
        $instance->setXmlspace($input);
        $this->assertEquals($expected, $instance->getXmlspace());
    }

    public function testSetGetXmllang() : void
    {
        $instance = new ilQTIMattext();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetEntityref() : void
    {
        $instance = new ilQTIMattext();
        $instance->setEntityref('Some input.');
        $this->assertEquals('Some input.', $instance->getEntityref());
    }

    public function testSetGetWidth() : void
    {
        $instance = new ilQTIMattext();
        $instance->setWidth('Some input.');
        $this->assertEquals('Some input.', $instance->getWidth());
    }

    public function testSetGetHeight() : void
    {
        $instance = new ilQTIMattext();
        $instance->setHeight('Some input.');
        $this->assertEquals('Some input.', $instance->getHeight());
    }

    public function testSetGetX0() : void
    {
        $instance = new ilQTIMattext();
        $instance->setX0('Some input.');
        $this->assertEquals('Some input.', $instance->getX0());
    }

    public function testSetGetY0() : void
    {
        $instance = new ilQTIMattext();
        $instance->setY0('Some input.');
        $this->assertEquals('Some input.', $instance->getY0());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIMattext();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function xmlSpaces() : array
    {
        class_exists(ilQTIMattext::class); // Force autoload to define the constants.
        return [
            ['preserve', SPACE_PRESERVE],
            [ '1', SPACE_PRESERVE],
            ['default', SPACE_DEFAULT],
            ['2', SPACE_DEFAULT],
            ['Random input', null],
        ];
    }
}
