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

require_once(__DIR__ . "/../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Navigation;

class NavigationTest extends ILIAS_UI_TestBase
{
    public function testImplementsFactoryInterface()
    {
        $f = new Navigation\Factory(
            $this->getDataFactory(),
            $this->getRefinery(),
            $this->createMock(ILIAS\UI\Storage::class)
        );

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Navigation\\Factory", $f);

        $sequence = $f->sequence(
            $this->createMock(C\Navigation\Sequence\SegmentRetrieval::class)
        );
        $this->assertInstanceOf("ILIAS\\UI\\Component\\Navigation\\Sequence\\Sequence", $sequence);
    }

}
