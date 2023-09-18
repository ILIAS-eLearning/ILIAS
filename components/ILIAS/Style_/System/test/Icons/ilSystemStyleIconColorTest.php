<?php

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
 *********************************************************************/

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilSystemStyleIconColorTest extends TestCase
{
    public function testConstruct(): void
    {
        $color = new ilSystemStyleIconColor('id', 'name', 'FF0000', 'description');

        $this->assertEquals('id', $color->getId());
        $this->assertEquals('name', $color->getName());
        $this->assertEquals('FF0000', $color->getColor());
        $this->assertEquals('description', $color->getDescription());
    }

    public function testSetMethods(): void
    {
        $color = new ilSystemStyleIconColor('id', 'name', 'FF0000', 'description');

        $color->setId('idnew');
        $color->setName('namenew');
        $color->setColor('EE0000');
        $color->setDescription('descriptionnew');

        $this->assertEquals('idnew', $color->getId());
        $this->assertEquals('namenew', $color->getName());
        $this->assertEquals('EE0000', $color->getColor());
        $this->assertEquals('descriptionnew', $color->getDescription());
    }

    public function testLowerCaseColor(): void
    {
        $color = new ilSystemStyleIconColor('id', 'name', 'abcdef', 'description');

        $this->assertEquals('ABCDEF', $color->getColor());
    }

    public function testInvalidColor1(): void
    {
        try {
            new ilSystemStyleIconColor('id', 'name', '#FF0000', 'description');
            $this->fail();
        } catch (ilSystemStyleColorException $e) {
            $this->assertEquals(ilSystemStyleColorException::INVALID_COLOR_EXCEPTION, $e->getCode());
        }
    }
    public function testInvalidColor2(): void
    {
        try {
            new ilSystemStyleIconColor('id', 'name', 'ZZ0000', 'description');
            $this->fail();
        } catch (ilSystemStyleColorException $e) {
            $this->assertEquals(ilSystemStyleColorException::INVALID_COLOR_EXCEPTION, $e->getCode());
        }
    }
    public function testValidColor3(): void
    {
        try {
            new ilSystemStyleIconColor('id', 'name', 'F00', 'description');
            $this->assertTrue(true);
        } catch (ilSystemStyleColorException $e) {
            $this->fail();
        }
    }

    public function testGetDominantAspect(): void
    {
        $white = new ilSystemStyleIconColor('id', 'name', 'FFFFFF', 'description');
        $black = new ilSystemStyleIconColor('id', 'name', '000000', 'description');

        $grey = new ilSystemStyleIconColor('id', 'name', 'AAAAAA', 'description');
        $red = new ilSystemStyleIconColor('id', 'name', 'FF0000', 'description');
        $green = new ilSystemStyleIconColor('id', 'name', '00FF00', 'description');
        $blue = new ilSystemStyleIconColor('id', 'name', '0000FF', 'description');

        $this->assertEquals(ilSystemStyleIconColor::GREY, $white->getDominatAspect());
        $this->assertEquals(ilSystemStyleIconColor::GREY, $black->getDominatAspect());
        $this->assertEquals(ilSystemStyleIconColor::GREY, $grey->getDominatAspect());
        $this->assertEquals(ilSystemStyleIconColor::RED, $red->getDominatAspect());
        $this->assertEquals(ilSystemStyleIconColor::GREEN, $green->getDominatAspect());
        $this->assertEquals(ilSystemStyleIconColor::BLUE, $blue->getDominatAspect());
    }

    public function testGetPerceivedBrightness(): void
    {
        $white = new ilSystemStyleIconColor('id', 'name', 'FFFFFF', 'description');
        $black = new ilSystemStyleIconColor('id', 'name', '000000', 'description');

        $grey = new ilSystemStyleIconColor('id', 'name', 'AAAAAA', 'description');
        $red = new ilSystemStyleIconColor('id', 'name', 'FF0000', 'description');
        $green = new ilSystemStyleIconColor('id', 'name', '00FF00', 'description');
        $blue = new ilSystemStyleIconColor('id', 'name', '0000FF', 'description');

        $this->assertEquals(255, ceil($white->getPerceivedBrightness()));
        $this->assertEquals(0, ceil($black->getPerceivedBrightness()));
        $this->assertEquals(170, ceil($grey->getPerceivedBrightness()));
        $this->assertEquals(140, ceil($red->getPerceivedBrightness()));
        $this->assertEquals(196, ceil($green->getPerceivedBrightness()));
        $this->assertEquals(87, ceil($blue->getPerceivedBrightness()));
    }

    public function testCompareColors(): void
    {
        $white = new ilSystemStyleIconColor('id', 'name', 'FFFFFF', 'description');
        $black = new ilSystemStyleIconColor('id', 'name', '000000', 'description');

        $grey = new ilSystemStyleIconColor('id', 'name', 'AAAAAA', 'description');
        $red = new ilSystemStyleIconColor('id', 'name', 'FF0000', 'description');
        $green = new ilSystemStyleIconColor('id', 'name', '00FF00', 'description');
        $blue = new ilSystemStyleIconColor('id', 'name', '0000FF', 'description');

        $this->assertEquals(1, ilSystemStyleIconColor::compareColors($white, $black));
        $this->assertEquals(-1, ilSystemStyleIconColor::compareColors($black, $white));
        $this->assertEquals(0, ilSystemStyleIconColor::compareColors($grey, $grey));

        $this->assertEquals(-1, ilSystemStyleIconColor::compareColors($red, $green));
        $this->assertEquals(1, ilSystemStyleIconColor::compareColors($red, $blue));
        $this->assertEquals(-1, ilSystemStyleIconColor::compareColors($blue, $green));
    }
}
