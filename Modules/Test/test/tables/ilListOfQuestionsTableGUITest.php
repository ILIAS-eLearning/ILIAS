<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilListOfQuestionsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilListOfQuestionsTableGUITest extends ilTestBaseTestCase
{
    private ilListOfQuestionsTableGUI $tableGui;
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
        $this->tableGui = new ilListOfQuestionsTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilListOfQuestionsTableGUI::class, $this->tableGui);
    }

    public function testShowPointsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isShowPointsEnabled());
        $this->tableGui->setShowPointsEnabled(true);
        $this->assertTrue($this->tableGui->isShowPointsEnabled());

        $this->tableGui->setShowPointsEnabled(false);
        $this->assertFalse($this->tableGui->isShowPointsEnabled());
    }

    public function testShowMarkerEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isShowMarkerEnabled());
        $this->tableGui->setShowMarkerEnabled(true);
        $this->assertTrue($this->tableGui->isShowMarkerEnabled());

        $this->tableGui->setShowMarkerEnabled(false);
        $this->assertFalse($this->tableGui->isShowMarkerEnabled());
    }

    public function testShowObligationsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isShowObligationsEnabled());
        $this->tableGui->setShowObligationsEnabled(true);
        $this->assertTrue($this->tableGui->isShowObligationsEnabled());

        $this->tableGui->setShowObligationsEnabled(false);
        $this->assertFalse($this->tableGui->isShowObligationsEnabled());
    }

    public function testObligationsFilterEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isObligationsFilterEnabled());
        $this->tableGui->setObligationsFilterEnabled(true);
        $this->assertTrue($this->tableGui->isObligationsFilterEnabled());

        $this->tableGui->setObligationsFilterEnabled(false);
        $this->assertFalse($this->tableGui->isObligationsFilterEnabled());
    }

    public function testObligationsNotAnswered() : void
    {
        $this->assertIsBool($this->tableGui->areObligationsNotAnswered());
        $this->tableGui->setObligationsNotAnswered(true);
        $this->assertTrue($this->tableGui->areObligationsNotAnswered());

        $this->tableGui->setObligationsNotAnswered(false);
        $this->assertFalse($this->tableGui->areObligationsNotAnswered());
    }

    public function testFinishTestButtonEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isFinishTestButtonEnabled());
        $this->tableGui->setFinishTestButtonEnabled(true);
        $this->assertTrue($this->tableGui->isFinishTestButtonEnabled());

        $this->tableGui->setFinishTestButtonEnabled(false);
        $this->assertFalse($this->tableGui->isFinishTestButtonEnabled());
    }
}
