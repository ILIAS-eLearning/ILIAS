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

namespace ILIAS\Test\Tests\Presentation;

use ILIAS\Test\Presentation\TabsManager;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class TabsManagerTest extends \ilTestBaseTestCase
{
    private TabsManager $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilTabs();

        $this->testObj = new TabsManager(
            $DIC['ilTabs'],
            $this->createMock(\ilLanguage::class),
            $this->createMock(\ilCtrl::class),
            $this->createMock(\ilAccess::class),
            $this->createMock(\ilTestAccess::class),
            $this->getTestObjMock(),
            $this->createMock(\ilTestObjectiveOrientedContainer::class),
            $this->createMock(\ilTestSession::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(TabsManager::class, $this->testObj);
    }

    public function testActivateTab(): void
    {
        global $DIC;
        $DIC['ilTabs']->expects($this->exactly(2))->method('activateTab');
        $this->testObj->activateTab(TabsManager::TAB_ID_PARTICIPANTS);
        $this->testObj->activateTab(TabsManager::TAB_ID_YOUR_RESULTS);
        $this->testObj->activateTab('randomString');
    }

    public function testActivateSubTab(): void
    {
        global $DIC;
        $DIC['ilTabs']->expects($this->exactly(9))->method('activateSubTab');

        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_FIXED_PARTICIPANTS);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_TIME_EXTENSION);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_PARTICIPANTS_RESULTS);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_MY_RESULTS);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_LO_RESULTS);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_HIGHSCORE);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_SKILL_RESULTS);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_MY_SOLUTIONS);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_QST_LIST_VIEW);
        $this->testObj->activateSubTab(TabsManager::SUBTAB_ID_QST_PAGE_VIEW);
        $this->testObj->activateSubTab(TabsManager::TAB_ID_PARTICIPANTS);
        $this->testObj->activateSubTab('randomString');
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
