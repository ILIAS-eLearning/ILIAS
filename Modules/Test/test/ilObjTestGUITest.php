<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestGUITest extends ilTestBaseTestCase
{
    private ilObjTestGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->markTestSkipped("DB causing issues");

        $this->addGlobal_lng();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilPluginAdmin();
        $this->addGlobal_tree();
        $this->addGlobal_http();
        $this->addGlobal_ilLocator();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilSetting();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_filesystem();
        $this->addGlobal_upload();
        $this->addGlobal_objDefinition();
        $this->addGlobal_tpl();
        $this->addGlobal_ilErr();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilias();

        $this->testObj = new ilObjTestGUI();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestGUI::class, $this->testObj);
    }

    public function testTestAccess() : void
    {
        $this->assertNull($this->testObj->getTestAccess());
        $testAccess_mock = $this->createMock(ilTestAccess::class);

        $this->testObj->setTestAccess($testAccess_mock);
        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testGetTabsManager() : void
    {
        $this->assertNull($this->testObj->getTabsManager());
        $testTabsManager_mock = $this->createMock(ilTestTabsManager::class);

        $this->testObj->setTabsManager($testTabsManager_mock);
        $this->assertEquals($testTabsManager_mock, $this->testObj->getTabsManager());
    }

    public function testRunObject() : void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirect")
            ->with($this->testObj, "infoScreen");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->runObject();
    }

    public function testOutEvaluationObject() : void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirectByClass")
            ->with("iltestevaluationgui", "outEvaluation");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->outEvaluationObject();
    }

    public function testBackObject() : void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirect")
            ->with($this->testObj, "questions");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->backObject();
    }

    public function testCancelRandomSelectObject() : void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirect")
            ->with($this->testObj, "questions");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->cancelRandomSelectObject();
    }

    public function testCancelCreateQuestionObject() : void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirect")
            ->with($this->testObj, "questions");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->cancelCreateQuestionObject();
    }

    public function testCancelRemoveQuestionsObject() : void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirect")
            ->with($this->testObj, "questions");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->cancelRemoveQuestionsObject();
    }

    public function testMoveQuestionsObject() : void
    {
        $_POST['q_id'] = 1;

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method("redirect")
            ->with($this->testObj, "questions");
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObj = new ilObjTestGUI();

        $testObj->moveQuestionsObject();
    }
}
