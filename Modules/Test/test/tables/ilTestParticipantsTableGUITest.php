<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTableGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTableGUI $tableGui;
    private ilTestParticipantsGUI $parentObj_mock;

    protected function setUp() : void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
                  ->method("getFormAction")
                  ->willReturnCallback(function () {
                      return "testFormAction";
                  });

        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        $this->setGlobalVariable("tpl", $this->createMock(ilGlobalPageTemplate::class));
        $this->setGlobalVariable("component.repository", $this->createMock(ilComponentRepository::class));
        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getActivePluginsInSlot")->willReturn(new ArrayIterator());
        $this->setGlobalVariable("component.factory", $component_factory);
        $this->setGlobalVariable("ilPluginAdmin", new ilPluginAdmin($this->createMock(ilComponentRepository::class)));
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));

        $this->parentObj_mock = $this->createMock(ilTestParticipantsGUI::class);
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->parentObj_mock
            ->expects($this->any())
            ->method("getTestObj")
            ->willReturn($objTest_mock);

        $this->parentObj_mock->object = $objTest_mock;
        $this->tableGui = new ilTestParticipantsTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantsTableGUI::class, $this->tableGui);
    }

    public function testManageResultsCommandsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isManageResultsCommandsEnabled());
        $this->tableGui->setManageResultsCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isManageResultsCommandsEnabled());
        $this->tableGui->setManageResultsCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isManageResultsCommandsEnabled());
    }

    public function testManageInviteesCommandsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isManageInviteesCommandsEnabled());
        $this->tableGui->setManageInviteesCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isManageInviteesCommandsEnabled());
        $this->tableGui->setManageInviteesCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isManageInviteesCommandsEnabled());
    }

    public function testRowKeyDataField() : void
    {
        $this->tableGui->setRowKeyDataField("test");
        $this->assertEquals("test", $this->tableGui->getRowKeyDataField());
    }

    public function testAnonymity() : void
    {
        $this->tableGui->setAnonymity("test");
        $this->assertEquals("test", $this->tableGui->getAnonymity());
    }

    public function testParticipantHasSolutionsFilterEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isParticipantHasSolutionsFilterEnabled());
        $this->tableGui->setParticipantHasSolutionsFilterEnabled(false);
        $this->assertFalse($this->tableGui->isParticipantHasSolutionsFilterEnabled());
        $this->tableGui->setParticipantHasSolutionsFilterEnabled(true);
        $this->assertTrue($this->tableGui->isParticipantHasSolutionsFilterEnabled());
    }

    public function testNumericOrdering() : void
    {
        $this->assertTrue($this->tableGui->numericOrdering("access"));
        $this->assertTrue($this->tableGui->numericOrdering("tries"));
        $this->assertFalse($this->tableGui->numericOrdering("randomString"));
    }
}
