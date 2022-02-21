<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI $tableGui;
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
        $this->tableGui = new ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI(
            $ctrl_mock,
            $lng_mock,
            $this->parentObj_mock,
            ""
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI::class, $this->tableGui);
    }

    public function testDefinitionEditModeEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isDefinitionEditModeEnabled());
        $this->tableGui->setDefinitionEditModeEnabled(false);
        $this->assertFalse($this->tableGui->isDefinitionEditModeEnabled());
        $this->tableGui->setDefinitionEditModeEnabled(true);
        $this->assertTrue($this->tableGui->isDefinitionEditModeEnabled());
    }

    public function testQuestionAmountColumnEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isQuestionAmountColumnEnabled());
        $this->tableGui->setQuestionAmountColumnEnabled(false);
        $this->assertFalse($this->tableGui->isQuestionAmountColumnEnabled());
        $this->tableGui->setQuestionAmountColumnEnabled(true);
        $this->assertTrue($this->tableGui->isQuestionAmountColumnEnabled());
    }
}
