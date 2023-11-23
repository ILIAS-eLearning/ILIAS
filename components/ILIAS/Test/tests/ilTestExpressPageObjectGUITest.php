<?php

class ilTestExpressPageObjectGUITest extends ilTestBaseTestCase
{
    private ilTestExpressPageObjectGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilToolbar();

        $this->testObj = new ilTestExpressPageObjectGUI(
            0,
            0,
            null,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestExpressPageObjectGUI::class, $this->testObj);
    }

    public function testNextQuestion(): void
    {
        $this->markTestSkipped();
    }

    public function testPrevQuestion(): void
    {
        $this->markTestSkipped();
    }

    public function testExecuteCommand(): void
    {
        $this->markTestSkipped();
    }

    public function testAddPageOfQuestions(): void
    {
        $this->markTestSkipped();
    }

    public function testHandleToolbarCommand(): void
    {
        $this->markTestSkipped();
    }

    public function testAddQuestion(): void
    {
        $this->markTestSkipped();
    }

    public function testQuestions(): void
    {
        $this->markTestSkipped();
    }

    public function testRedirectToQuestionEditPage(): void
    {
        $this->markTestSkipped();
    }

    public function testRedirectToQuestionPoolSelectionPage(): void
    {
        $this->markTestSkipped();
    }

    public function testInsertQuestions(): void
    {
        $this->markTestSkipped();
    }
}