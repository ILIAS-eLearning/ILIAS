<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdsGUITest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdsGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdsGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            112
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdsGUI::class, $this->testObj);
    }

    public function testQuestionContainerId(): void
    {
        $this->testObj->setQuestionContainerId(12);
        $this->assertEquals(12, $this->testObj->getQuestionContainerId());
    }

    public function testQuestionAssignmentColumnsEnabled(): void
    {
        $this->testObj->setQuestionAssignmentColumnsEnabled(false);
        $this->assertFalse($this->testObj->areQuestionAssignmentColumnsEnabled());

        $this->testObj->setQuestionAssignmentColumnsEnabled(true);
        $this->assertTrue($this->testObj->areQuestionAssignmentColumnsEnabled());
    }

    public function testTestId(): void
    {
        $this->assertEquals(112, $this->testObj->getTestId());
    }
}
