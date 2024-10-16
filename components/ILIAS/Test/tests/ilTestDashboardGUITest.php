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

/**
 * Class ilTestDashboardGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDashboardGUITest extends ilTestBaseTestCase
{
    private ilTestDashboardGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;

        parent::setUp();

        $this->addGlobal_ilAccess();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();

        $this->testObj = new ilTestDashboardGUI(
            $this->getTestObjMock(),
            $DIC['ilUser'],
            $DIC['ilAccess'],
            $this->createMock(ilTestAccess::class),
            $DIC['tpl'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['lng'],
            $DIC['refinery'],
            $DIC['ilDB'],
            $DIC['ilCtrl'],
            $DIC['ilTabs'],
            $this->createMock(ILIAS\Test\Presentation\TabsManager::class),
            $DIC['ilToolbar'],
            $DIC['component.factory'],
            $this->createMock(\ILIAS\Test\ExportImport\Factory::class),
            $this->createMock(\ILIAS\Test\RequestDataCollector::class),
            $this->createMock(\ILIAS\Test\ResponseHandler::class),
            $this->createMock(\ILIAS\Test\Participants\ParticipantRepository::class),
            $this->createMock(ilTestQuestionSetConfig::class),
            $this->createMock(ilTestObjectiveOrientedContainer::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestDashboardGUI::class, $this->testObj);
    }
}
