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
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI $tableGui;
    private ilTestRandomQuestionSetConfigGUI $parentObj_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilComponentRepository();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        ;
        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getActivePluginsInSlot")->willReturn(new ArrayIterator());
        $this->setGlobalVariable("component.factory", $component_factory);

        $this->parentObj_mock = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $this->tableGui = new ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI(
            $this->parentObj_mock,
            "",
            [],
            []
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI::class, $this->tableGui);
    }

    public function testDefinitionEditModeEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isDefinitionEditModeEnabled());
        $this->tableGui->setDefinitionEditModeEnabled(false);
        $this->assertFalse($this->tableGui->isDefinitionEditModeEnabled());
        $this->tableGui->setDefinitionEditModeEnabled(true);
        $this->assertTrue($this->tableGui->isDefinitionEditModeEnabled());
    }

    public function testQuestionAmountColumnEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isQuestionAmountColumnEnabled());
        $this->tableGui->setQuestionAmountColumnEnabled(false);
        $this->assertFalse($this->tableGui->isQuestionAmountColumnEnabled());
        $this->tableGui->setQuestionAmountColumnEnabled(true);
        $this->assertTrue($this->tableGui->isQuestionAmountColumnEnabled());
    }
}
