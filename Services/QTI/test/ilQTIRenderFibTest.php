<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIRenderFibTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderFib::class, new ilQTIRenderFib());
    }

    public function testSetGetMinnumber() : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setMinnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMinnumber());
    }

    public function testSetGetMaxnumber() : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setMaxnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxnumber());
    }

    /**
     * @dataProvider prompts
     */
    public function testSetGetPrompt($input, $expected) : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setPrompt($input);
        $this->assertEquals($expected, $instance->getPrompt());
    }

    /**
     * @dataProvider fibtypes
     */
    public function testSetGetFibtype($input, $expected) : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setFibtype($input);
        $this->assertEquals($expected, $instance->getFibtype());
    }

    public function testSetGetRows() : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setRows('Some input.');
        $this->assertEquals('Some input.', $instance->getRows());
    }

    public function testSetGetMaxchars() : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setMaxchars('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxchars());
    }

    public function testSetGetColumns() : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setColumns('Some input.');
        $this->assertEquals('Some input.', $instance->getColumns());
    }

    public function testSetGetCharset() : void
    {
        $instance = new ilQTIRenderFib();
        $instance->setCharset('Some input.');
        $this->assertEquals('Some input.', $instance->getCharset());
    }

    public function prompts() : array
    {
        class_exists(ilQTIRenderFib::class); // Force autoload to define the constants.
        return [
            ['1', PROMPT_BOX],
            ['box', PROMPT_BOX],
            ['2', PROMPT_DASHLINE],
            ['dashline', PROMPT_DASHLINE],
            ['3', PROMPT_ASTERISK],
            ['asterisk', PROMPT_ASTERISK],
            ['4', PROMPT_UNDERLINE],
            ['underline', PROMPT_UNDERLINE],
        ];
    }

    public function fibtypes() : array
    {
        class_exists(ilQTIRenderFib::class); // Force autoload to define the constants.
        return [
            ['1', FIBTYPE_STRING],
            ['string', FIBTYPE_STRING],
            ['2', FIBTYPE_INTEGER],
            ['integer', FIBTYPE_INTEGER],
            ['3', FIBTYPE_DECIMAL],
            ['decimal', FIBTYPE_DECIMAL],
            ['4', FIBTYPE_SCIENTIFIC],
            ['scientific', FIBTYPE_SCIENTIFIC],
        ];
    }
}
