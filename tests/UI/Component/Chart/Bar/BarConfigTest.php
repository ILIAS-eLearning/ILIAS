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
 *********************************************************************/

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Chart\Bar\BarConfig;

/**
 * Test on Bar Configuration implementation.
 */
class BarConfigTest extends ILIAS_UI_TestBase
{
    protected function getDataFactory(): ILIAS\Data\Factory
    {
        return new ILIAS\Data\Factory();
    }

    public function test_with_color(): void
    {
        $df = $this->getDataFactory();

        $bc = new BarConfig();
        $color = $df->color("#000000");
        $bc1 = $bc->withColor($color);

        $this->assertEquals(null, $bc->getColor());
        $this->assertEquals($color, $bc1->getColor());
    }

    public function test_with_width(): void
    {
        $df = $this->getDataFactory();

        $bc = new BarConfig();
        $width = 0.5;
        $bc1 = $bc->withRelativeWidth($width);

        $this->assertEquals(null, $bc->getRelativeWidth());
        $this->assertEquals($width, $bc1->getRelativeWidth());
    }
}
