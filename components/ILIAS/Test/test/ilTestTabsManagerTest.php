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

use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilTestTabsManagerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestTabsManagerTest extends ilTestBaseTestCase
{
    private ilTestTabsManager $testObj;

    private $tabs_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tabs_mock = $this->createMock(ilTabsGUI::class);
        $this->setGlobalVariable("ilTabs", $this->tabs_mock);
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();

        $this->testObj = new ilTestTabsManager(
            $this->tabs_mock,
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilAccess::class),
            $this->createMock(ilTestAccess::class),
            $this->createMock(ilTestObjectiveOrientedContainer::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestTabsManager::class, $this->testObj);
    }

    public function testActivateTab(): void
    {
        $this->tabs_mock->expects($this->exactly(2))->method("activateTab");
        $this->testObj->activateTab(ilTestTabsManager::TAB_ID_EXAM_DASHBOARD);
        $this->testObj->activateTab(ilTestTabsManager::TAB_ID_RESULTS);
        $this->testObj->activateTab("randomString");
    }

    public function testActivateSubTab(): void
    {
        $this->tabs_mock->expects($this->exactly(10))->method("activateSubTab");

        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_FIXED_PARTICIPANTS);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_TIME_EXTENSION);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_PARTICIPANTS_RESULTS);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_RESULTS);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_LO_RESULTS);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_HIGHSCORE);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_SKILL_RESULTS);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_SOLUTIONS);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_LIST_VIEW);
        $this->testObj->activateSubTab(ilTestTabsManager::SUBTAB_ID_QST_PAGE_VIEW);
        $this->testObj->activateSubTab(ilTestTabsManager::TAB_ID_EXAM_DASHBOARD);
        $this->testObj->activateSubTab("randomString");
    }

    public function testTestOBJ(): void
    {
        $mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestOBJ($mock);
        $this->assertEquals($mock, $this->testObj->getTestOBJ());
    }

    public function testTestSession(): void
    {
        $mock = $this->createMock(ilTestSession::class);
        $this->testObj->setTestSession($mock);
        $this->assertEquals($mock, $this->testObj->getTestSession());
    }

    public function testTestQuestionSetConfig(): void
    {
        $mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testObj->setTestQuestionSetConfig($mock);
        $this->assertEquals($mock, $this->testObj->getTestQuestionSetConfig());
    }

    public function testParentBackLabel(): void
    {
        $this->testObj->setParentBackLabel("Test");
        $this->assertEquals("Test", $this->testObj->getParentBackLabel());
    }

    public function testParentBackHref(): void
    {
        $this->testObj->setParentBackHref("Test");
        $this->assertEquals("Test", $this->testObj->getParentBackHref());
    }

    public function testHasParentBackLink(): void
    {
        $this->assertFalse($this->testObj->hasParentBackLink());

        $this->testObj->setParentBackHref("Test");
        $this->assertFalse($this->testObj->hasParentBackLink());

        $this->testObj->setParentBackLabel("Test");
        $this->assertTrue($this->testObj->hasParentBackLink());
    }
}
