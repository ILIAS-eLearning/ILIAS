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
 * Class ilTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsGUITest extends ilTestBaseTestCase
{
    private ilTestResultsGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_tpl();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();

        $this->testObj = new ilTestResultsGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class),
            $DIC['ilCtrl'],
            $DIC['ilAccess'],
            $DIC['ilDB'],
            $DIC['ilUser'],
            $DIC['lng'],
            $this->createMock(\ILIAS\DI\LoggingServices::class),
            $DIC['component.repository'],
            $DIC['ilTabs'],
            $DIC['ilToolbar'],
            $DIC['tpl'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $this->createMock(ILIAS\Skill\Service\SkillService::class),
            $this->createMock(ILIAS\Test\InternalRequestService::class),
            $this->createMock(\ILIAS\TestQuestionPool\QuestionInfoService::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultsGUI::class, $this->testObj);
    }

    public function testObjectiveParent(): void
    {
        $mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveParent($mock);
        $this->assertEquals($mock, $this->testObj->getObjectiveParent());
    }

    public function testTestObj(): void
    {
        $mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($mock);
        $this->assertEquals($mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig(): void
    {
        $mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testObj->setQuestionSetConfig($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionSetConfig());
    }

    public function testTestAccess(): void
    {
        $mock = $this->createMock(ilTestAccess::class);
        $this->testObj->setTestAccess($mock);
        $this->assertEquals($mock, $this->testObj->getTestAccess());
    }

    public function testTestSession(): void
    {
        $mock = $this->createMock(ilTestSession::class);
        $this->testObj->setTestSession($mock);
        $this->assertEquals($mock, $this->testObj->getTestSession());
    }

    public function testTestTabs(): void
    {
        $mock = $this->createMock(ilTestTabsManager::class);
        $this->testObj->setTestTabs($mock);
        $this->assertEquals($mock, $this->testObj->getTestTabs());
    }
}
