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
 * Class ilParticipantsTestResultsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilParticipantsTestResultsTableGUITest extends ilTestBaseTestCase
{
    private ilParticipantsTestResultsTableGUI $tableGui;
    private ilParticipantsTestResultsGUI $parentObj_mock;

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

        $this->parentObj_mock = $this->createMock(ilParticipantsTestResultsGUI::class);
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->parentObj_mock
            ->expects($this->any())
            ->method("getTestObj")
            ->willReturn($objTest_mock);

        $this->tableGui = new ilParticipantsTestResultsTableGUI($this->parentObj_mock, "", $DIC['ui.factory'], $DIC['ui.renderer']);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilParticipantsTestResultsTableGUI::class, $this->tableGui);
    }

    public function testAccessResultsCommandsEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isAccessResultsCommandsEnabled());
        $this->tableGui->setAccessResultsCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isAccessResultsCommandsEnabled());

        $this->tableGui->setAccessResultsCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isAccessResultsCommandsEnabled());
    }

    public function testManageResultsCommandsEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isManageResultsCommandsEnabled());
        $this->tableGui->setManageResultsCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isManageResultsCommandsEnabled());

        $this->tableGui->setManageResultsCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isManageResultsCommandsEnabled());
    }

    public function testNumericOrdering(): void
    {
        $this->assertTrue($this->tableGui->numericOrdering("scored_pass"));
        $this->assertTrue($this->tableGui->numericOrdering("answered_questions"));
        $this->assertTrue($this->tableGui->numericOrdering("points"));
        $this->assertTrue($this->tableGui->numericOrdering("percent_result"));
        $this->assertFalse($this->tableGui->numericOrdering("randomText"));
    }
}
