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
 * Class ilTestNavigationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestNavigationToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestNavigationToolbarGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_lng();

        $this->testObj = new ilTestNavigationToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilTestPlayerAbstractGUI::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestNavigationToolbarGUI::class, $this->testObj);
    }

    public function testSuspendTestButtonEnabled(): void
    {
        $this->testObj->setSuspendTestButtonEnabled(false);
        $this->assertFalse($this->testObj->isSuspendTestButtonEnabled());

        $this->testObj->setSuspendTestButtonEnabled(true);
        $this->assertTrue($this->testObj->isSuspendTestButtonEnabled());
    }

    public function testUserPassOverviewButtonEnabled(): void
    {
        $this->testObj->setUserPassOverviewEnabled(false);
        $this->assertFalse($this->testObj->isUserPassOverviewEnabled());

        $this->testObj->setUserPassOverviewEnabled(true);
        $this->assertTrue($this->testObj->isUserPassOverviewEnabled());
    }

    public function testQuestionTreeVisible(): void
    {
        $this->testObj->setQuestionTreeVisible(false);
        $this->assertFalse($this->testObj->isQuestionTreeVisible());

        $this->testObj->setQuestionTreeVisible(true);
        $this->assertTrue($this->testObj->isQuestionTreeVisible());
    }

    public function testQuestionSelectionButtonEnabled(): void
    {
        $this->testObj->setQuestionSelectionButtonEnabled(false);
        $this->assertFalse($this->testObj->isQuestionSelectionButtonEnabled());

        $this->testObj->setQuestionSelectionButtonEnabled(true);
        $this->assertTrue($this->testObj->isQuestionSelectionButtonEnabled());
    }

    public function testFinishTestButtonEnabled(): void
    {
        $this->testObj->setFinishTestButtonEnabled(false);
        $this->assertFalse($this->testObj->isFinishTestButtonEnabled());

        $this->testObj->setFinishTestButtonEnabled(true);
        $this->assertTrue($this->testObj->isFinishTestButtonEnabled());
    }

    public function testFinishTestCommand(): void
    {
        $finishTestCommand = 'testString';
        $this->testObj->setFinishTestCommand($finishTestCommand);
        $this->assertEquals($finishTestCommand, $this->testObj->getFinishTestCommand());
    }

    public function testFinishTestButtonPrimary(): void
    {
        $this->testObj->setFinishTestButtonPrimary(false);
        $this->assertFalse($this->testObj->isFinishTestButtonPrimary());

        $this->testObj->setFinishTestButtonPrimary(true);
        $this->assertTrue($this->testObj->isFinishTestButtonPrimary());
    }

    public function testDisabledStateEnabled(): void
    {
        $this->testObj->setDisabledStateEnabled(false);
        $this->assertFalse($this->testObj->isDisabledStateEnabled());

        $this->testObj->setDisabledStateEnabled(true);
        $this->assertTrue($this->testObj->isDisabledStateEnabled());
    }
}
