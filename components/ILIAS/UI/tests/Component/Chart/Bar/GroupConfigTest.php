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

namespace Component\Chart\Bar;

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component\Chart\Bar\GroupConfig;
use ILIAS;
use ILIAS_UI_TestBase;

/**
 * Test on Group Configuration implementation.
 */
class GroupConfigTest extends ILIAS_UI_TestBase
{
    public function getDataFactory(): ILIAS\Data\Factory
    {
        return new ILIAS\Data\Factory();
    }

    public function testWithStacked(): void
    {
        $df = $this->getDataFactory();

        $gc = new GroupConfig();
        $gc1 = $gc->withStacked();

        $this->assertFalse($gc->isStacked());
        $this->assertTrue($gc1->isStacked());
    }
}
