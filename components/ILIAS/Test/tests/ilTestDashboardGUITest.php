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

        $this->addGlobal_ilUser();
        $this->addGlobal_ilAccess();
        $this->addGlobal_tpl();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_lng();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();

        $this->testObj = new ilTestDashboardGUI(
            $this->createMock(ilObjTest::class),
            $DIC['ilUser'],
            $DIC['ilAccess'],
            $DIC['tpl'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['lng'],
            $DIC['ilDB'],
            $DIC['ilCtrl'],
            $DIC['ilTabs'],
            $DIC['ilToolbar'],
            $this->createMock(ilTestQuestionSetConfig::class),
            $this->createMock(\ILIAS\Test\InternalRequestService::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestDashboardGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig(): void
    {
        $testQuestionSetConfig_mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testObj->setQuestionSetConfig($testQuestionSetConfig_mock);
        $this->assertEquals($testQuestionSetConfig_mock, $this->testObj->getQuestionSetConfig());
    }

    public function testTestAccess(): void
    {
        $testAccess_mock = $this->createMock(ilTestAccess::class);
        $this->testObj->setTestAccess($testAccess_mock);
        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testTestTabs(): void
    {
        $testTabsManager_mock = $this->createMock(ilTestTabsManager::class);
        $this->testObj->setTestTabs($testTabsManager_mock);
        $this->assertEquals($testTabsManager_mock, $this->testObj->getTestTabs());
    }

    public function testObjectiveParent(): void
    {
        $objectiveParent_mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveParent($objectiveParent_mock);
        $this->assertEquals($objectiveParent_mock, $this->testObj->getObjectiveParent());
    }
}
