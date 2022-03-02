<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilQTIResponseVarTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponseVar::class, new ilQTIResponseVar('a'));
    }

    public function testSetGetVartype() : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setVartype('Some input.');
        $this->assertEquals('Some input.', $instance->getVartype());
    }

    /**
     * @dataProvider cases
     */
    public function testSetGetCase($input, $expected) : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setCase($input);
        $this->assertEquals($expected, $instance->getCase());
    }

    public function testSetGetRespident() : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setRespident('Some input.');
        $this->assertEquals('Some input.', $instance->getRespident());
    }

    public function testSetGetIndex() : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setIndex('Some input.');
        $this->assertEquals('Some input.', $instance->getIndex());
    }

    /**
     * @dataProvider setMatches
     */
    public function testSetGetSetmatch($input, $expected) : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setSetmatch($input);
        $this->assertEquals($expected, $instance->getSetmatch());
    }

    /**
     * @dataProvider areaTypes
     */
    public function testSetGetAreatype($input, $expected) : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setAreatype($input);
        $this->assertEquals($expected, $instance->getAreatype());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function cases() : array
    {
        class_exists(ilQTIResponseVar::class); // Force autoload to define the constants.
        return [
            ['1', CASE_YES],
            ['yes', CASE_YES],
            ['2', CASE_NO],
            ['no', CASE_NO],
        ];
    }

    public function setMatches() : array
    {
        class_exists(ilQTIRespcondition::class); // Force autoload to define the constants.
        return [
            ['1', SETMATCH_PARTIAL],
            ['partial', SETMATCH_PARTIAL],
            ['2', SETMATCH_EXACT],
            ['exact', SETMATCH_EXACT],
        ];
    }

    public function areaTypes() : array
    {
        class_exists(ilQTIRespcondition::class); // Force autoload to define the constants.
        return [
            ['1', AREATYPE_ELLIPSE],
            ['ellipse', AREATYPE_ELLIPSE],
            ['2', AREATYPE_RECTANGLE],
            ['rectangle', AREATYPE_RECTANGLE],
            ['3', AREATYPE_BOUNDED],
            ['bounded', AREATYPE_BOUNDED],
        ];
    }
}
