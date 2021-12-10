<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillLevelThresholdsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillLevelThresholdsTableGUITest extends ilTestBaseTestCase
{
    private ilTestSkillLevelThresholdsTableGUI $tableGui;
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
        $this->setGlobalVariable("ilPluginAdmin", new ilPluginAdmin());
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));

        $this->parentObj_mock = $this->createMock(ilObjTestGUI::class);
        $this->parentObj_mock->object = $this->createMock(ilObjTest::class);
        $this->tableGui = new ilTestSkillLevelThresholdsTableGUI(
            $this->parentObj_mock,
            0,
            "",
            $ctrl_mock,
            $lng_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillLevelThresholdsTableGUI::class, $this->tableGui);
    }

    public function testQuestionAssignmentColumnsEnabled() : void
    {
        $this->assertIsBool($this->tableGui->areQuestionAssignmentColumnsEnabled());
        $this->tableGui->setQuestionAssignmentColumnsEnabled(false);
        $this->assertFalse($this->tableGui->areQuestionAssignmentColumnsEnabled());
        $this->tableGui->setQuestionAssignmentColumnsEnabled(true);
        $this->assertTrue($this->tableGui->areQuestionAssignmentColumnsEnabled());
    }
}
