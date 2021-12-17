<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilParticipantsTestResultsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilParticipantsTestResultsTableGUITest extends ilTestBaseTestCase
{
    private ilParticipantsTestResultsTableGUI $tableGui;
    private ilParticipantsTestResultsGUI $parentObj_mock;

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
        $this->setGlobalVariable("ilPluginAdmin", new ilPluginAdmin());
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));

        $this->parentObj_mock = $this->createMock(ilParticipantsTestResultsGUI::class);
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->parentObj_mock
            ->expects($this->any())
            ->method("getTestObj")
            ->willReturn($objTest_mock);

        $this->parentObj_mock->object = $objTest_mock;
        $this->tableGui = new ilParticipantsTestResultsTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilParticipantsTestResultsTableGUI::class, $this->tableGui);
    }

    public function testAccessResultsCommandsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isAccessResultsCommandsEnabled());
        $this->tableGui->setAccessResultsCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isAccessResultsCommandsEnabled());

        $this->tableGui->setAccessResultsCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isAccessResultsCommandsEnabled());
    }

    public function testManageResultsCommandsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isManageResultsCommandsEnabled());
        $this->tableGui->setManageResultsCommandsEnabled(true);
        $this->assertTrue($this->tableGui->isManageResultsCommandsEnabled());

        $this->tableGui->setManageResultsCommandsEnabled(false);
        $this->assertFalse($this->tableGui->isManageResultsCommandsEnabled());
    }

    public function testAnonymity() : void
    {
        $this->tableGui->setAnonymity(true);
        $this->assertTrue($this->tableGui->getAnonymity());

        $this->tableGui->setAnonymity(false);
        $this->assertFalse($this->tableGui->getAnonymity());
    }

    public function testNumericOrdering() : void
    {
        $this->assertTrue($this->tableGui->numericOrdering("scored_pass"));
        $this->assertTrue($this->tableGui->numericOrdering("answered_questions"));
        $this->assertTrue($this->tableGui->numericOrdering("points"));
        $this->assertTrue($this->tableGui->numericOrdering("percent_result"));
        $this->assertFalse($this->tableGui->numericOrdering("randomText"));
    }
}
