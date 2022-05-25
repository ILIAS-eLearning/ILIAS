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

class ilQTIMaterialTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilQTIMaterial::class, new ilQTIMaterial());
    }

    public function testAddMattext() : void
    {
        $instance = new ilQTIMaterial();
        $text = $this->getMockBuilder(ilQTIMattext::class)->disableOriginalConstructor()->getMock();
        $instance->addMattext($text);
        $this->assertEquals([['material' => $text, 'type' => 'mattext']], $instance->materials);
    }

    public function testAddMatimage() : void
    {
        $instance = new ilQTIMaterial();
        $image = $this->getMockBuilder(ilQTIMatimage::class)->disableOriginalConstructor()->getMock();
        $instance->addMatimage($image);
        $this->assertEquals([['material' => $image, 'type' => 'matimage']], $instance->materials);
    }

    public function testAddMatapplet() : void
    {
        $instance = new ilQTIMaterial();
        $applet = $this->getMockBuilder(ilQTIMatapplet::class)->disableOriginalConstructor()->getMock();
        $instance->addMatapplet($applet);
        $this->assertEquals([['material' => $applet, 'type' => 'matapplet']], $instance->materials);
    }

    public function testSetGetFlow() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals(0, $instance->getFlow());

        $instance->setFlow(8);
        $this->assertEquals(8, $instance->getFlow());
    }

    public function testSetGetLabel() : void
    {
        $instance = new ilQTIMaterial();

        $this->assertEquals(null, $instance->getLabel());

        $instance->setLabel('Some input.');
        $this->assertEquals('Some input.', $instance->getLabel());
    }
}
