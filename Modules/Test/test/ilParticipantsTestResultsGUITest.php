<?php

declare(strict_types=1);

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

/**
 * Class ilParticipantsTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilParticipantsTestResultsGUITest extends ilTestBaseTestCase
{
    private ilParticipantsTestResultsGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_tpl();
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_http();
        $this->addGlobal_refinery();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();
        $this->testObj = new ilParticipantsTestResultsGUI();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilParticipantsTestResultsGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->assertNull($this->testObj->getTestObj());

        $this->testObj->setTestObj($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig(): void
    {
        $testQuestionSetConfig_mock = $this->createMock(ilTestQuestionSetConfig::class);

        $this->assertNull($this->testObj->getQuestionSetConfig());

        $this->testObj->setQuestionSetConfig($testQuestionSetConfig_mock);
        $this->assertEquals($testQuestionSetConfig_mock, $this->testObj->getQuestionSetConfig());
    }

    public function testTestAccess(): void
    {
        $testAccess_mock = $this->createMock(ilTestAccess::class);

        $this->assertNull($this->testObj->getTestAccess());

        $this->testObj->setTestAccess($testAccess_mock);
        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testObjectiveParent(): void
    {
        $objectiveParent_mock = $this->createMock(ilTestObjectiveOrientedContainer::class);

        $this->assertNull($this->testObj->getObjectiveParent());

        $this->testObj->setObjectiveParent($objectiveParent_mock);
        $this->assertEquals($objectiveParent_mock, $this->testObj->getObjectiveParent());
    }
}
