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
 * Class ilTestSkillLevelThresholdsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdsGUITest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdsGUI $testObj;

    private int $testId = 112;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillLevelThresholdsGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->testId
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdsGUI::class, $this->testObj);
    }

    public function testQuestionContainerId(): void
    {
        $questionContainerId = 12;
        $this->testObj->setQuestionContainerId($questionContainerId);
        $this->assertEquals($questionContainerId, $this->testObj->getQuestionContainerId());
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
        $this->assertEquals($this->testId, $this->testObj->getTestId());
    }
}
