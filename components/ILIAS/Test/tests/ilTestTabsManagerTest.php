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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestTabsManagerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestTabsManagerTest extends ilTestBaseTestCase
{
    private ilTestTabsManager $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;

        $this->addGlobal_ilTabs();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();

        $this->testObj = new ilTestTabsManager(
            $DIC['ilTabs'],
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
        global $DIC;
        $DIC['ilTabs']->expects($this->exactly(2))->method('activateTab');
        $this->testObj->activateTab(ilTestTabsManager::TAB_ID_EXAM_DASHBOARD);
        $this->testObj->activateTab(ilTestTabsManager::TAB_ID_RESULTS);
        $this->testObj->activateTab('randomString');
    }

    public function testActivateSubTab(): void
    {
        global $DIC;
        $DIC['ilTabs']->expects($this->exactly(10))->method('activateSubTab');

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
        $this->testObj->activateSubTab('randomString');
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
        $parent_back_label = 'Test';
        $this->testObj->setParentBackLabel($parent_back_label);
        $this->assertEquals($parent_back_label, $this->testObj->getParentBackLabel());
    }

    public function testParentBackHref(): void
    {
        $parent_back_href = 'Test';
        $this->testObj->setParentBackHref($parent_back_href);
        $this->assertEquals($parent_back_href, $this->testObj->getParentBackHref());
    }

    public function testHasParentBackLink(): void
    {
        $this->assertFalse($this->testObj->hasParentBackLink());
        $parent_back_x = 'Test';

        $this->testObj->setParentBackHref($parent_back_x);
        $this->assertFalse($this->testObj->hasParentBackLink());

        $this->testObj->setParentBackLabel($parent_back_x);
        $this->assertTrue($this->testObj->hasParentBackLink());
    }
}
