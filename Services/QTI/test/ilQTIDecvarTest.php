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
            ['integer', ilQTIDecvar::VARTYPE_INTEGER],
            ['1', ilQTIDecvar::VARTYPE_INTEGER],
            ['string', ilQTIDecvar::VARTYPE_STRING],
            ['2', ilQTIDecvar::VARTYPE_STRING],
            ['decimal', ilQTIDecvar::VARTYPE_DECIMAL],
            ['3', ilQTIDecvar::VARTYPE_DECIMAL],
            ['scientific', ilQTIDecvar::VARTYPE_SCIENTIFIC],
            ['4', ilQTIDecvar::VARTYPE_SCIENTIFIC],
            ['boolean', ilQTIDecvar::VARTYPE_BOOLEAN],
            ['5', ilQTIDecvar::VARTYPE_BOOLEAN],
            ['enumerated', ilQTIDecvar::VARTYPE_ENUMERATED],
            ['6', ilQTIDecvar::VARTYPE_ENUMERATED],
            ['set', ilQTIDecvar::VARTYPE_SET],
            ['7', ilQTIDecvar::VARTYPE_SET],
            ['8', null],
            ['', null],
            ['Some random input.', null],
        ];
    }
}
