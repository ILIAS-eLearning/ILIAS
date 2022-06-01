<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    public function testSetGetCase(string $input, ?string $expected) : void
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
    public function testSetGetSetmatch(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponseVar('a');
        $instance->setSetmatch($input);
        $this->assertEquals($expected, $instance->getSetmatch());
    }

    /**
     * @dataProvider areaTypes
     */
    public function testSetGetAreatype(string $input, ?string $expected) : void
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
            ['1', ilQTIResponseVar::CASE_YES],
            ['yes', ilQTIResponseVar::CASE_YES],
            ['2', ilQTIResponseVar::CASE_NO],
            ['no', ilQTIResponseVar::CASE_NO],
        ];
    }

    public function setMatches() : array
    {
        class_exists(ilQTIRespcondition::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIResponseVar::SETMATCH_PARTIAL],
            ['partial', ilQTIResponseVar::SETMATCH_PARTIAL],
            ['2', ilQTIResponseVar::SETMATCH_EXACT],
            ['exact', ilQTIResponseVar::SETMATCH_EXACT],
        ];
    }

    public function areaTypes() : array
    {
        class_exists(ilQTIRespcondition::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIResponseVar::AREATYPE_ELLIPSE],
            ['ellipse', ilQTIResponseVar::AREATYPE_ELLIPSE],
            ['2', ilQTIResponseVar::AREATYPE_RECTANGLE],
            ['rectangle', ilQTIResponseVar::AREATYPE_RECTANGLE],
            ['3', ilQTIResponseVar::AREATYPE_BOUNDED],
            ['bounded', ilQTIResponseVar::AREATYPE_BOUNDED],
        ];
    }
}
