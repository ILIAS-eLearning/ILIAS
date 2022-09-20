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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation as I;

/**
 * Test on minimize button implementation.
 */
class MinimizeButtonTest extends ILIAS_UI_TestBase
{
    public function setUp(): void
    {
        $this->button_factory = new I\Component\Button\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Button\\Minimize",
            $this->button_factory->minimize()
        );
    }
}
