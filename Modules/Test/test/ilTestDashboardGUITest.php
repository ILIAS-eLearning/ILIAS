<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestDashboardGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDashboardGUITest extends ilTestBaseTestCase
{
    private ilTestDashboardGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestDashboardGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class)
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
