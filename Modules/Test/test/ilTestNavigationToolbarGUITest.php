<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTestPlayerAbstractGUI::class)
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

    public function testQuestionListButtonEnabled(): void
    {
        $this->testObj->setQuestionListButtonEnabled(false);
        $this->assertFalse($this->testObj->isQuestionListButtonEnabled());

        $this->testObj->setQuestionListButtonEnabled(true);
        $this->assertTrue($this->testObj->isQuestionListButtonEnabled());
    }

    public function testQuestionTreeButtonEnabled(): void
    {
        $this->testObj->setQuestionTreeButtonEnabled(false);
        $this->assertFalse($this->testObj->isQuestionTreeButtonEnabled());

        $this->testObj->setQuestionTreeButtonEnabled(true);
        $this->assertTrue($this->testObj->isQuestionTreeButtonEnabled());
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
        $this->testObj->setFinishTestCommand("testString");
        $this->assertEquals("testString", $this->testObj->getFinishTestCommand());
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
