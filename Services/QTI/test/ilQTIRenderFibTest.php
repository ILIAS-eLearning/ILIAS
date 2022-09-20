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

class ilQTIRenderFibTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTIRenderFib::class, new ilQTIRenderFib());
    }

    public function testSetGetMinnumber(): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setMinnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMinnumber());
    }

    public function testSetGetMaxnumber(): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setMaxnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxnumber());
    }

    /**
     * @dataProvider prompts
     */
    public function testSetGetPrompt(string $input, ?string $expected): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setPrompt($input);
        $this->assertEquals($expected, $instance->getPrompt());
    }

    /**
     * @dataProvider fibtypes
     */
    public function testSetGetFibtype(string $input, ?string $expected): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setFibtype($input);
        $this->assertEquals($expected, $instance->getFibtype());
    }

    public function testSetGetRows(): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setRows('Some input.');
        $this->assertEquals('Some input.', $instance->getRows());
    }

    public function testSetGetMaxchars(): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setMaxchars('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxchars());
    }

    public function testSetGetColumns(): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setColumns('Some input.');
        $this->assertEquals('Some input.', $instance->getColumns());
    }

    public function testSetGetCharset(): void
    {
        $instance = new ilQTIRenderFib();
        $instance->setCharset('Some input.');
        $this->assertEquals('Some input.', $instance->getCharset());
    }

    public function prompts(): array
    {
        class_exists(ilQTIRenderFib::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIRenderFib::PROMPT_BOX],
            ['box', ilQTIRenderFib::PROMPT_BOX],
            ['2', ilQTIRenderFib::PROMPT_DASHLINE],
            ['dashline', ilQTIRenderFib::PROMPT_DASHLINE],
            ['3', ilQTIRenderFib::PROMPT_ASTERISK],
            ['asterisk', ilQTIRenderFib::PROMPT_ASTERISK],
            ['4', ilQTIRenderFib::PROMPT_UNDERLINE],
            ['underline', ilQTIRenderFib::PROMPT_UNDERLINE],
        ];
    }

    public function fibtypes(): array
    {
        class_exists(ilQTIRenderFib::class); // Force autoload to define the constants.
        return [
            ['1', ilQTIRenderFib::FIBTYPE_STRING],
            ['string', ilQTIRenderFib::FIBTYPE_STRING],
            ['2', ilQTIRenderFib::FIBTYPE_INTEGER],
            ['integer', ilQTIRenderFib::FIBTYPE_INTEGER],
            ['3', ilQTIRenderFib::FIBTYPE_DECIMAL],
            ['decimal', ilQTIRenderFib::FIBTYPE_DECIMAL],
            ['4', ilQTIRenderFib::FIBTYPE_SCIENTIFIC],
            ['scientific', ilQTIRenderFib::FIBTYPE_SCIENTIFIC],
        ];
    }
}
