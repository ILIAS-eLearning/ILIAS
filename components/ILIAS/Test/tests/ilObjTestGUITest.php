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

use ILIAS\Test\Questions\QuestionsTableQuery;
use ILIAS\DI\Container;

class QuestionsTableQueryMock extends QuestionsTableQuery
{
    public function __construct()
    {
    }
    private function getHereURL(): string
    {
        return 'http://www.ilias.de';
    }
}

/**
 * Class ilObjTestGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestGUITest extends ilTestBaseTestCase
{
    private ilObjTestGUI $testObj;

    protected function setUp(): void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', 'data/');
        }

        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_tree();
        $this->addGlobal_ilLocator();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilSetting();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_filesystem();
        $this->addGlobal_ilErr();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilias();
        $this->addGlobal_ilNavigationHistory();
        $this->addGlobal_skillService();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilRbacAdmin();
        $this->addGlobal_objectService();
        $this->addGlobal_GlobalScreenService();
        $this->addGlobal_resourceStorage();

        $this->testObj = $this->getNewTestGUI();
    }

    protected function getNewTestGUI(): ilObjTestGUI
    {
        $table_query = $this->getMockBuilder(QuestionsTableQueryMock::class)->getMock();
        return new class ($table_query) extends ilObjTestGUI {
            public function __construct(
                protected QuestionsTableQuery $mock_table_query
            ) {
                parent::__construct();
            }
            protected function getQuestionsTableQuery(): QuestionsTableQuery
            {
                return $this->mock_table_query;
            }
        };
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestGUI::class, $this->testObj);
    }

    public function testTestAccess(): void
    {
        $testAccess_mock = $this->createMock(ilTestAccess::class);

        $this->testObj->setTestAccess($testAccess_mock);
        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testRunObject(): void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);
        $testObj = $this->getNewTestGUI();
        $ctrl_mock
            ->expects($this->once())
            ->method('redirectByClass')
            ->with([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);

        $testObj->runObject();
    }

    public function testOutEvaluationObject(): void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->expects($this->once())
            ->method('redirectByClass')
            ->with('iltestevaluationgui', 'outEvaluation')
        ;
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);

        $testObj = $this->getNewTestGUI();

        $testObj->outEvaluationObject();
    }

    public function testBackObject(): void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);
        $testObj = $this->getNewTestGUI();
        $ctrl_mock
            ->expects($this->once())
            ->method('redirect')
            ->with($testObj, ilObjTestGUI::SHOW_QUESTIONS_CMD)
        ;
        $testObj->backObject();
    }

    public function testCancelCreateQuestionObject(): void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);
        $testObj = $this->getNewTestGUI();
        $ctrl_mock
            ->expects($this->once())
            ->method('redirect')
            ->with($testObj, ilObjTestGUI::SHOW_QUESTIONS_CMD)
        ;
        $testObj->cancelCreateQuestionObject();
    }

    public function testCancelRemoveQuestionsObject(): void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);
        $testObj = $this->getNewTestGUI();
        $ctrl_mock
            ->expects($this->once())
            ->method('redirect')
            ->with($testObj, ilObjTestGUI::SHOW_QUESTIONS_CMD)
        ;
        $testObj->cancelRemoveQuestionsObject();
    }

    public function testMoveQuestionsObject(): void
    {
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $this->setGlobalVariable('ilCtrl', $ctrl_mock);
        $testObj = $this->getNewTestGUI();
        $ctrl_mock
            ->expects($this->once())
            ->method('redirect')
            ->with($testObj, ilObjTestGUI::SHOW_QUESTIONS_CMD)
        ;
        $testObj->moveQuestionsObject();
    }
}
