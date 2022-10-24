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
