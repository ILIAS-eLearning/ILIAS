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
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilias();
        $this->addGlobal_tree();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilUser();

        $this->testObj = new ilTestEvaluationGUI($this->createMock(ilObjTest::class));
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
        $this->assertEquals(20, $this->testObj->getEvaluationQuestionId(20, 0));
        $this->assertEquals(20, $this->testObj->getEvaluationQuestionId(20, -210));
        $this->assertEquals(125, $this->testObj->getEvaluationQuestionId(20, 125));
    }
}
