<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIDecvarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIDecvar::class, new ilQTIDecvar());
    }

    public function testSetGetVarname() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setVarname('Some input.');
        $this->assertEquals('Some input.', $instance->getVarname());
    }

    /**
     * @dataProvider vartypes
     */
    public function testSetGetVartype(string $input, ?string $expected) : void
    {
        $instance = new ilQTIDecvar();
        $instance->setVartype($input);
        $this->assertEquals($expected, $instance->getVartype());
    }

    public function testSetGetDefaultval() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setDefaultval('Some input.');
        $this->assertEquals('Some input.', $instance->getDefaultval());
    }

    public function testSetGetMinvalue() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setMinvalue('Some input.');
        $this->assertEquals('Some input.', $instance->getMinvalue());
    }

    public function testSetGetMaxvalue() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setMaxvalue('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxvalue());
    }

    public function testSetGetMembers() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setMembers('Some input.');
        $this->assertEquals('Some input.', $instance->getMembers());
    }

    public function testSetGetCutvalue() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setCutvalue('Some input.');
        $this->assertEquals('Some input.', $instance->getCutvalue());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIDecvar();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function vartypes() : array
    {
        class_exists(ilQTIDecvar::class); // Force autoload to define the constants.
        return [
            ['integer', VARTYPE_INTEGER],
            ['1', VARTYPE_INTEGER],
            ['string', VARTYPE_STRING],
            ['2', VARTYPE_STRING],
            ['decimal', VARTYPE_DECIMAL],
            ['3', VARTYPE_DECIMAL],
            ['scientific', VARTYPE_SCIENTIFIC],
            ['4', VARTYPE_SCIENTIFIC],
            ['boolean', VARTYPE_BOOLEAN],
            ['5', VARTYPE_BOOLEAN],
            ['enumerated', VARTYPE_ENUMERATED],
            ['6', VARTYPE_ENUMERATED],
            ['set', VARTYPE_SET],
            ['7', VARTYPE_SET],
            ['8', null],
            ['', null],
            ['Some random input.', null],
        ];
    }
}
