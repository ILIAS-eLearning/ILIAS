<?php declare(strict_types=1);

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
        $this->addGlobal_ilComponentRepository();
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
