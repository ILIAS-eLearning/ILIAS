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

class ilQTIMatappletTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTIMatapplet::class, new ilQTIMatapplet());
    }

    public function testSetGetEmbedded(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setEmbedded('Some input.');
        $this->assertEquals('Some input.', $instance->getEmbedded());
    }

    public function testSetGetLabel(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }

    public function testSetGetUri(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setUri('Some input.');
        $this->assertEquals('Some input.', $instance->getUri());
    }

    public function testSetGetX0(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setX0('Some input.');
        $this->assertEquals('Some input.', $instance->getX0());
    }

    public function testSetGetY(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setY0('Some input.');
        $this->assertEquals('Some input.', $instance->getY0());
    }

    public function testSetGetWidth(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setWidth('Some input.');
        $this->assertEquals('Some input.', $instance->getWidth());
    }

    public function testSetGetHeight(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setHeight('Some input.');
        $this->assertEquals('Some input.', $instance->getHeight());
    }

    public function testSetGetEntityref(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setEntityref('Some input.');
        $this->assertEquals('Some input.', $instance->getEntityref());
    }

    public function testSetGetContent(): void
    {
        $instance = new ilQTIMatapplet();
        $instance->setContent('Some input.');
        $this->assertEquals('Some input.', $instance->getContent());
    }
}
