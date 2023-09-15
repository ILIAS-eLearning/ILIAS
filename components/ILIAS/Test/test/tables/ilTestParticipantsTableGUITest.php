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
 * Class ilTestParticipantsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTableGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTableGUI $tableGui;
    private ilTestParticipantsGUI $parentObj_mock;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_tpl();
        $this->addGlobal_ilComponentRepository();

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

        $this->parentObj_mock = $this->createMock(ilTestParticipantsGUI::class);
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->parentObj_mock
            ->expects($this->any())
            ->method("getTestObj")
            ->willReturn($objTest_mock);

        //$this->parentObj_mock->setTestObj($objTest_mock);
        $this->tableGui = new ilTestParticipantsTableGUI($this->parentObj_mock, "", $DIC['ui.factory'], $DIC['ui.renderer']);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantsTableGUI::class, $this->tableGui);
    }

    public function testManageResultsCommandsEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isManageResultsCommandsEnabled());
        $this->tableGui->setManageResultsCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isManageResultsCommandsEnabled());
        $this->tableGui->setManageResultsCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isManageResultsCommandsEnabled());
    }

    public function testManageInviteesCommandsEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isManageInviteesCommandsEnabled());
        $this->tableGui->setManageInviteesCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isManageInviteesCommandsEnabled());
        $this->tableGui->setManageInviteesCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isManageInviteesCommandsEnabled());
    }

    public function testRowKeyDataField(): void
    {
        $this->tableGui->setRowKeyDataField("test");
        $this->assertEquals("test", $this->tableGui->getRowKeyDataField());
    }

    public function testParticipantHasSolutionsFilterEnabled(): void
    {
        $this->assertIsBool($this->tableGui->isParticipantHasSolutionsFilterEnabled());
        $this->tableGui->setParticipantHasSolutionsFilterEnabled(false);
        $this->assertFalse($this->tableGui->isParticipantHasSolutionsFilterEnabled());
        $this->tableGui->setParticipantHasSolutionsFilterEnabled(true);
        $this->assertTrue($this->tableGui->isParticipantHasSolutionsFilterEnabled());
    }

    public function testNumericOrdering(): void
    {
        $this->assertTrue($this->tableGui->numericOrdering("access"));
        $this->assertTrue($this->tableGui->numericOrdering("tries"));
        $this->assertFalse($this->tableGui->numericOrdering("randomString"));
    }
}
