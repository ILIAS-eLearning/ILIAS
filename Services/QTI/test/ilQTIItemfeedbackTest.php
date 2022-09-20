<?php

declare(strict_types=1);

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

class ilQTIItemfeedbackTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTIItemfeedback::class, new ilQTIItemfeedback());
    }

    /**
     * @depends testConstruct
     * @dataProvider views
     */
    public function testSetGetView(string $input, ?string $expected): void
    {
        $instance = new ilQTIItemfeedback();
        $instance->setView($input);
        $this->assertEquals($expected, $instance->getView());
    }

    public function testSetGetIdent(): void
    {
        $instance = new ilQTIItemfeedback();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle(): void
    {
        $instance = new ilQTIItemfeedback();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function views(): array
    {
        class_exists(ilQTIItemfeedback::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIItemfeedback::VIEW_ALL],
            ['all', ilQTIItemfeedback::VIEW_ALL],
            ['2', ilQTIItemfeedback::VIEW_ADMINISTRATOR],
            ['administrator', ilQTIItemfeedback::VIEW_ADMINISTRATOR],
            ['3', ilQTIItemfeedback::VIEW_ADMINAUTHORITY],
            ['adminauthority', ilQTIItemfeedback::VIEW_ADMINAUTHORITY],
            ['4', ilQTIItemfeedback::VIEW_ASSESSOR],
            ['assessor', ilQTIItemfeedback::VIEW_ASSESSOR],
            ['5', ilQTIItemfeedback::VIEW_AUTHOR],
            ['author', ilQTIItemfeedback::VIEW_AUTHOR],
            ['6', ilQTIItemfeedback::VIEW_CANDIDATE],
            ['candidate', ilQTIItemfeedback::VIEW_CANDIDATE],
            ['7', ilQTIItemfeedback::VIEW_INVIGILATORPROCTOR],
            ['invigilatorproctor', ilQTIItemfeedback::VIEW_INVIGILATORPROCTOR],
            ['8', ilQTIItemfeedback::VIEW_PSYCHOMETRICIAN],
            ['psychometrician', ilQTIItemfeedback::VIEW_PSYCHOMETRICIAN],
            ['9', ilQTIItemfeedback::VIEW_SCORER],
            ['scorer', ilQTIItemfeedback::VIEW_SCORER],
            ['10', ilQTIItemfeedback::VIEW_TUTOR],
            ['tutor', ilQTIItemfeedback::VIEW_TUTOR],
            ['11', null],
            ['Random input.', null],
        ];
    }
}
