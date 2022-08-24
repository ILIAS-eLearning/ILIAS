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

class ilQTISectionTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQTISection::class, new ilQTISection());
    }

    public function testSetGetIdent(): void
    {
        $instance = new ilQTISection();
        $instance->setIdent('Some input.');
        $this->assertEquals('Some input.', $instance->getIdent());
    }

    public function testSetGetTitle(): void
    {
        $instance = new ilQTISection();
        $instance->setTitle('Some input.');
        $this->assertEquals('Some input.', $instance->getTitle());
    }

    public function testSetGetXmllang(): void
    {
        $instance = new ilQTISection();
        $instance->setXmllang('Some input.');
        $this->assertEquals('Some input.', $instance->getXmllang());
    }

    public function testSetGetComment(): void
    {
        $instance = new ilQTISection();
        $instance->setComment('Some input.');
        $this->assertEquals('Some input.', $instance->getComment());
    }
}
