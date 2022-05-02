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

class ilQTIResponseLabelTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponseLabel::class, new ilQTIResponseLabel());
    }

    /**
     * @dataProvider rshuffles
     */
    public function testSetGetRshuffle(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setRshuffle($input);
        $this->assertEquals($expected, $instance->getRshuffle());
    }

    /**
     * @dataProvider areas
     */
    public function testSetGetRarea(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setRarea($input);
        $this->assertEquals($expected, $instance->getRarea());
    }

    /**
     * @dataProvider rranges
     */
    public function testSetGetRrange(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setRrange($input);
        $this->assertEquals($expected, $instance->getRrange());
    }

    public function testSetGetLabelrefid() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setLabelrefid('Some input.');
        $this->assertEquals('Some input.', $instance->getLabelrefid());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetMatchGroup() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setMatchGroup('Some input.');
        $this->assertEquals('Some input.', $instance->getMatchGroup());
    }

    public function testSetGetMatchMax() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setMatchMax('Some input.');
        $this->assertEquals('Some input.', $instance->getMatchMax());
    }

    public function testSetGetContent() : void
    {
        $instance = new ilQTIResponseLabel();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function rshuffles() : array
    {
        class_exists(ilQTIResponseLabel::class); // Force autoload to define the constants.

        return [
            ['1', ilQTIResponseLabel::RSHUFFLE_NO],
            ['no', ilQTIResponseLabel::RSHUFFLE_NO],
            ['2', ilQTIResponseLabel::RSHUFFLE_YES],
            ['yes', ilQTIResponseLabel::RSHUFFLE_YES],
            ['Random input', null],
        ];
    }

    public function areas() : array
    {
        class_exists(ilQTIResponseLabel::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIResponseLabel::RAREA_ELLIPSE],
            ['ellipse', ilQTIResponseLabel::RAREA_ELLIPSE],
            ['2', ilQTIResponseLabel::RAREA_RECTANGLE],
            ['rectangle', ilQTIResponseLabel::RAREA_RECTANGLE],
            ['3', ilQTIResponseLabel::RAREA_BOUNDED],
            ['bounded', ilQTIResponseLabel::RAREA_BOUNDED],
            ['Random input', null],
        ];
    }

    public function rranges() : array
    {
        class_exists(ilQTIResponseLabel::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIResponseLabel::RRANGE_EXACT],
            ['excact', ilQTIResponseLabel::RRANGE_EXACT],
            ['2', ilQTIResponseLabel::RRANGE_RANGE],
            ['range', ilQTIResponseLabel::RRANGE_RANGE],
        ];
    }
}
