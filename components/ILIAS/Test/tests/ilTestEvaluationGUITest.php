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
 * Class ilTestEvaluationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationGUITest extends ilTestBaseTestCase
{
    private ilTestEvaluationGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilias();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_http();
        $this->addGlobal_ilErr();
        $this->addGlobal_GlobalScreenService();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilLog();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilNavigationHistory();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();

        $this->testObj = new ilTestEvaluationGUI(
            $this->getTestObjMock()
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestEvaluationGUI::class, $this->testObj);
    }

    public function testTestAccess(): void
    {
        $testAccess_mock = $this->createMock(ilTestAccess::class);

        $this->testObj->setTestAccess($testAccess_mock);

        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testGetEvaluationQuestionId(): void
    {
        $data = [
            ['question_id' => 20, 'original_id' => 0],
            ['question_id' => 20, 'original_id' => -210],
            ['question_id' => 20, 'original_id' => 125],
        ];

        foreach ($data as $value) {
            $this->assertEquals($value['original_id'] > 0 ? $value['original_id'] : $value['question_id'], $this->testObj->getEvaluationQuestionId($value['question_id'], $value['original_id']));
        }
    }
}
