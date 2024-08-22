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
 * Class ilTestParticipantsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();
        $this->addGlobal_ilAccess();
        $this->addGlobal_tpl();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_lng();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilComponentFactory();

        $this->testObj = new ilTestParticipantsGUI(
            $this->getTestObjMock(),
            $this->createMock(ilTestQuestionSetConfig::class),
            $DIC['ilAccess'],
            $DIC['tpl'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['lng'],
            $DIC['ilCtrl'],
            $DIC['ilDB'],
            $DIC['ilTabs'],
            $DIC['ilToolbar'],
            $DIC['component.factory'],
            $this->createMock(\ILIAS\Test\ExportImport\Factory::class),
            $this->createMock(\ILIAS\Test\RequestDataCollector::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantsGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $mock = $this->getTestObjMock();
        $this->testObj->setTestObj($mock);
        $this->assertEquals($mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig(): void
    {
        $mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testObj->setQuestionSetConfig($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionSetConfig());
    }

    public function testObjectiveParent(): void
    {
        $mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveParent($mock);
        $this->assertEquals($mock, $this->testObj->getObjectiveParent());
    }

    public function testTestAccess(): void
    {
        $mock = $this->createMock(ilTestAccess::class);
        $this->testObj->setTestAccess($mock);
        $this->assertEquals($mock, $this->testObj->getTestAccess());
    }
}
