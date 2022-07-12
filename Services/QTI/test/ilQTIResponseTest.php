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

class ilQTIResponseTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIResponse::class, new ilQTIResponse());
    }

    public function testSetGetIdent() : void
    {
        $instance = new ilQTIResponse();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    /**
     * @dataProvider rtimings
     */
    public function testSetGetRtiming(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponse();
        $instance->setRtiming($input);
        $this->assertEquals($expected, $instance->getRtiming());
    }

    /**
     * @dataProvider numtypes
     */
    public function testSetGetNumtype(string $input, ?string $expected) : void
    {
        $instance = new ilQTIResponse();
        $instance->setNumtype($input);
        $this->assertEquals($expected, $instance->getNumtype());
    }

    public function rtimings() : array
    {
        class_exists(ilQTIResponse::class); // Force autoload to define the constants.

        return [
            ['no', ilQTIResponse::RTIMING_NO],
            ['1', ilQTIResponse::RTIMING_NO],
            ['yes', ilQTIResponse::RTIMING_YES],
            ['2', ilQTIResponse::RTIMING_YES],
            ['Random input.', null],
        ];
    }

    public function numtypes() : array
    {
        class_exists(ilQTIResponse::class); // Force autoload to define the constants.
        return [
            ['integer', ilQTIResponse::NUMTYPE_INTEGER],
            ['1', ilQTIResponse::NUMTYPE_INTEGER],
            ['decimal', ilQTIResponse::NUMTYPE_DECIMAL],
            ['2', ilQTIResponse::NUMTYPE_DECIMAL],
            ['scientific', ilQTIResponse::NUMTYPE_SCIENTIFIC],
            ['3', ilQTIResponse::NUMTYPE_SCIENTIFIC],
            ['Random input.', null],
        ];
    }
}
