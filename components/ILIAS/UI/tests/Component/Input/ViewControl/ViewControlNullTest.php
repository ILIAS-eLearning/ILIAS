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

use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;
use ILIAS\Data\Result;
use ILIAS\UI\Implementation\Component\Input\InputData;

require_once('ViewControlBaseTest.php');

class ViewControlNullTest extends ViewControlBaseTest
{
    public function testViewControlFieldNullRendering(): void
    {
        $vc = $this->buildVCFactory()->nullControl();
        $this->assertEquals('', $this->getDefaultRenderer()->render($vc));
    }
}
