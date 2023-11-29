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
 * Class ilTestQuestionsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionsTableGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionsTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getActivePluginsInSlot")->willReturn(new ArrayIterator());
        $this->setGlobalVariable("component.factory", $component_factory);

        $this->parentObj_mock = $this->getMockBuilder(ilObjTestGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getObject'])
            ->getMock();
        $this->parentObj_mock->expects($this->any())->method('getObject')->willReturn($this->createMock(ilObjTest::class));
        $this->tableGui = new ilTestQuestionsTableGUI($this->parentObj_mock, "", 0, $DIC['ui.factory'], $DIC['ui.renderer'], $this->createMock(\ILIAS\TestQuestionPool\QuestionInfoService::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionsTableGUI::class, $this->tableGui);
    }

    public function testQuestionManagingEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isQuestionManagingEnabled());
        $this->tableGui->setQuestionManagingEnabled(false);
        $this->assertFalse($this->tableGui->isQuestionManagingEnabled());
        $this->tableGui->setQuestionManagingEnabled(true);
        $this->assertTrue($this->tableGui->isQuestionManagingEnabled());
    }

    public function testPositionInsertCommandsEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isPositionInsertCommandsEnabled());
        $this->tableGui->setPositionInsertCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isPositionInsertCommandsEnabled());
        $this->tableGui->setPositionInsertCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isPositionInsertCommandsEnabled());
    }

    public function testQuestionPositioningEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isQuestionPositioningEnabled());
        $this->tableGui->setQuestionPositioningEnabled(false);
        $this->assertFalse($this->tableGui->isQuestionPositioningEnabled());
        $this->tableGui->setQuestionPositioningEnabled(true);
        $this->assertTrue($this->tableGui->isQuestionPositioningEnabled());
    }

    public function testObligatoryQuestionsHandlingEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isObligatoryQuestionsHandlingEnabled());
        $this->tableGui->setObligatoryQuestionsHandlingEnabled(false);
        $this->assertFalse($this->tableGui->isObligatoryQuestionsHandlingEnabled());
        $this->tableGui->setObligatoryQuestionsHandlingEnabled(true);
        $this->assertTrue($this->tableGui->isObligatoryQuestionsHandlingEnabled());
    }

    public function testTotalPoints(): void
    {
        $this->assertIsFloat($this->tableGui->getTotalPoints());
        $this->tableGui->setTotalPoints(125.251);
        $this->assertEquals(125.251, $this->tableGui->getTotalPoints());
    }

    public function testQuestionRemoveRowButtonEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isQuestionRemoveRowButtonEnabled());
        $this->tableGui->setQuestionRemoveRowButtonEnabled(false);
        $this->assertFalse($this->tableGui->isQuestionRemoveRowButtonEnabled());
        $this->tableGui->setQuestionRemoveRowButtonEnabled(true);
        $this->assertTrue($this->tableGui->isQuestionRemoveRowButtonEnabled());
    }
}
