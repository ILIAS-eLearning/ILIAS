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

class ilQTIRenderChoiceTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIRenderChoice::class, new ilQTIRenderChoice());
    }

    public function testSetGetMinnumber() : void
    {
        $instance = new ilQTIRenderChoice();
        $instance->setMinnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMinnumber());
    }

    public function testSetGetMaxnumber() : void
    {
        $instance = new ilQTIRenderChoice();
        $instance->setMaxnumber('Some input.');
        $this->assertEquals('Some input.', $instance->getMaxnumber());
    }
}
