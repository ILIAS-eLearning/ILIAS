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

class ilQTIPresentationTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTIPresentation::class, new ilQTIPresentation());
    }

    public function testSetGetLabel(): void
    {
        $instance = new ilQTIPresentation();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetXmllang(): void
    {
        $instance = new ilQTIPresentation();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetX0(): void
    {
        $instance = new ilQTIPresentation();
        $instance->setX0('Some input.');
        $this->assertEquals('Some input.', $instance->getX0());
    }

    public function testSetGetY0(): void
    {
        $instance = new ilQTIPresentation();
        $instance->setY0('Some input.');
        $this->assertEquals('Some input.', $instance->getY0());
    }

    public function testSetGetWidth(): void
    {
        $instance = new ilQTIPresentation();
        $instance->setWidth('Some input.');
        $this->assertEquals('Some input.', $instance->getWidth());
    }

    public function testSetGetHeight(): void
    {
        $instance = new ilQTIPresentation();
        $instance->setHeight('Some input.');
        $this->assertEquals('Some input.', $instance->getHeight());
    }
}
