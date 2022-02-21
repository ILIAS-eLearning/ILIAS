<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPassOverviewTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassOverviewTableGUITest extends ilTestBaseTestCase
{
    private ilTestPassOverviewTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

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

        $this->parentObj_mock = $this->createMock(ilObjTestGUI::class);
        $this->parentObj_mock->object = $this->createMock(ilObjTest::class);
        $this->tableGui = new ilTestPassOverviewTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPassOverviewTableGUI::class, $this->tableGui);
    }

    public function testNumericOrdering() : void
    {
        $this->assertTrue($this->tableGui->numericOrdering("pass"));
        $this->assertTrue($this->tableGui->numericOrdering("date"));
        $this->assertTrue($this->tableGui->numericOrdering("percentage"));
        $this->assertFalse($this->tableGui->numericOrdering("randomText"));
    }

    public function testResultPresentationEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isResultPresentationEnabled());
        $this->tableGui->setResultPresentationEnabled(false);
        $this->assertFalse($this->tableGui->isResultPresentationEnabled());
        $this->tableGui->setResultPresentationEnabled(true);
        $this->assertTrue($this->tableGui->isResultPresentationEnabled());
    }

    public function testPdfPresentationEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isPdfPresentationEnabled());
        $this->tableGui->setPdfPresentationEnabled(false);
        $this->assertFalse($this->tableGui->isPdfPresentationEnabled());
        $this->tableGui->setPdfPresentationEnabled(true);
        $this->assertTrue($this->tableGui->isPdfPresentationEnabled());
    }

    public function testObjectiveOrientedPresentationEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isObjectiveOrientedPresentationEnabled());
        $this->tableGui->setObjectiveOrientedPresentationEnabled(false);
        $this->assertFalse($this->tableGui->isObjectiveOrientedPresentationEnabled());
        $this->tableGui->setObjectiveOrientedPresentationEnabled(true);
        $this->assertTrue($this->tableGui->isObjectiveOrientedPresentationEnabled());
    }

    public function testActiveId() : void
    {
        $this->tableGui->setActiveId(20);
        $this->assertEquals(20, $this->tableGui->getActiveId());
    }

    public function testPassDetailsCommand() : void
    {
        $this->assertIsString($this->tableGui->getPassDetailsCommand());
        $this->tableGui->setPassDetailsCommand("testString");
        $this->assertEquals("testString", $this->tableGui->getPassDetailsCommand());
    }

    public function testPassDeletionCommand() : void
    {
        $this->assertIsString($this->tableGui->getPassDeletionCommand());
        $this->tableGui->setPassDeletionCommand("testString");
        $this->assertEquals("testString", $this->tableGui->getPassDeletionCommand());
    }
}
