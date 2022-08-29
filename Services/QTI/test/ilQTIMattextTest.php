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

class ilQTIMattextTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTIMattext::class, new ilQTIMattext());
    }

    public function testSetGetTexttype(): void
    {
        $instance = new ilQTIMattext();
        $instance->setTexttype('Some input.');
        $this->assertEquals('Some input.', $instance->getTexttype());
    }

    public function testSetGetLabel(): void
    {
        $instance = new ilQTIMattext();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetCharset(): void
    {
        $instance = new ilQTIMattext();
        $instance->setCharset('Some input.');
        $this->assertEquals('Some input.', $instance->getCharset());
    }

    public function testSetGetUri(): void
    {
        $instance = new ilQTIMattext();
        $instance->setUri('Some input.');
        $this->assertEquals('Some input.', $instance->getUri());
    }

    /**
     * @dataProvider xmlSpaces
     */
    public function testSetGetXmlspace(string $input, ?string $expected): void
    {
        $instance = new ilQTIMattext();
        $instance->setXmlspace($input);
        $this->assertEquals($expected, $instance->getXmlspace());
    }

    public function testSetGetXmllang(): void
    {
        $instance = new ilQTIMattext();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetEntityref(): void
    {
        $instance = new ilQTIMattext();
        $instance->setEntityref('Some input.');
        $this->assertEquals('Some input.', $instance->getEntityref());
    }

    public function testSetGetWidth(): void
    {
        $instance = new ilQTIMattext();
        $instance->setWidth('Some input.');
        $this->assertEquals('Some input.', $instance->getWidth());
    }

    public function testSetGetHeight(): void
    {
        $instance = new ilQTIMattext();
        $instance->setHeight('Some input.');
        $this->assertEquals('Some input.', $instance->getHeight());
    }

    public function testSetGetX0(): void
    {
        $instance = new ilQTIMattext();
        $instance->setX0('Some input.');
        $this->assertEquals('Some input.', $instance->getX0());
    }

    public function testSetGetY0(): void
    {
        $instance = new ilQTIMattext();
        $instance->setY0('Some input.');
        $this->assertEquals('Some input.', $instance->getY0());
    }

    public function testSetGetContent(): void
    {
        $instance = new ilQTIMattext();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }

    public function xmlSpaces(): array
    {
        class_exists(ilQTIMattext::class); // Force autoload to define the constants.
        return [
            ['preserve', ilQTIMattext::SPACE_PRESERVE],
            [ '1', ilQTIMattext::SPACE_PRESERVE],
            ['default', ilQTIMattext::SPACE_DEFAULT],
            ['2', ilQTIMattext::SPACE_DEFAULT],
            ['Random input', null],
        ];
    }
}
