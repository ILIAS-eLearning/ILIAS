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

class ilQTISetvarTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTISetvar::class, new ilQTISetvar());
    }

    /**
     * @dataProvider actions
     */
    public function testSetGetAction(string $input, ?string $expected): void
    {
        $instance = new ilQTISetvar();
        $instance->setAction($input);
        $this->assertEquals($expected, $instance->getAction());
    }

    public function testSetGetContent(): void
    {
        $instance = new ilQTISetvar();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function testSetGetVarname(): void
    {
        $instance = new ilQTISetvar();
        $instance->setVarname('Some input.');
        $this->assertEquals('Some input.', $instance->getVarname());
    }

    public function actions(): array
    {
        class_exists(ilQTISetvar::class); // Force autoload to define the constants.
        return [
            ['set', ilQTISetvar::ACTION_SET],
            ['1', ilQTISetvar::ACTION_SET],
            ['add', ilQTISetvar::ACTION_ADD],
            ['2', ilQTISetvar::ACTION_ADD],
            ['subtract', ilQTISetvar::ACTION_SUBTRACT],
            ['3', ilQTISetvar::ACTION_SUBTRACT],
            ['multiply', ilQTISetvar::ACTION_MULTIPLY],
            ['4', ilQTISetvar::ACTION_MULTIPLY],
            ['divide', ilQTISetvar::ACTION_DIVIDE],
            ['5', ilQTISetvar::ACTION_DIVIDE],
            ['6', null],
            ['Some input.', null],
        ];
    }
}
