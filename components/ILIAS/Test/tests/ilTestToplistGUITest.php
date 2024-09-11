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

use ILIAS\Data\Factory;
use ILIAS\Test\Results\Toplist\TestTopListRepository;

/**
 * Class ilTestToplistGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestToplistGUITest extends ilTestBaseTestCase
{
    private ilTestToplistGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->testObj = new ilTestToplistGUI(
            $this->getTestObjMock(),
            $this->createMock(TestTopListRepository::class),
            $DIC['ilCtrl'],
            $DIC['tpl'],
            $DIC['lng'],
            $DIC['ilUser'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $this->createMock(Factory::class),
            $DIC['http']
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestToplistGUI::class, $this->testObj);
    }
}
