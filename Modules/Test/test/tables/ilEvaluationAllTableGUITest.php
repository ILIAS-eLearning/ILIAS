<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilEvaluationAllTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilEvaluationAllTableGUITest extends ilTestBaseTestCase
{
    private ilEvaluationAllTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;

    protected function setUp() : void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->willReturnCallback(function () {
                     return "testTranslation";
                 });

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
        $this->setGlobalVariable("ilSetting", $this->createMock(ilSetting::class));
        $this->setGlobalVariable("rbacreview", $this->createMock(ilRbacReview::class));
        $this->setGlobalVariable("ilUser", $this->createMock(ilObjUser::class));

        $this->parentObj_mock = $this->createMock(ilObjTestGUI::class);
        $this->parentObj_mock->object = $this->createMock(ilObjTest::class);
        $this->tableGui = new ilEvaluationAllTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilEvaluationAllTableGUI::class, $this->tableGui);
    }

    public function testNumericOrdering() : void
    {
        $tableGui = new ilEvaluationAllTableGUI(
            $this->parentObj_mock,
            "",
            false,
            false
        );
        $this->assertTrue($tableGui->numericOrdering("reached"));
        $this->assertTrue($tableGui->numericOrdering("hint_count"));
        $this->assertTrue($tableGui->numericOrdering("exam_id"));
        $this->assertFalse($tableGui->numericOrdering("name"));

        $tableGui = new ilEvaluationAllTableGUI(
            $this->parentObj_mock,
            "",
            true,
            true
        );
        $this->assertTrue($tableGui->numericOrdering("reached"));
        $this->assertTrue($tableGui->numericOrdering("hint_count"));
        $this->assertTrue($tableGui->numericOrdering("exam_id"));
        $this->assertTrue($tableGui->numericOrdering("name"));

        $tableGui = new ilEvaluationAllTableGUI(
            $this->parentObj_mock,
            "",
            true,
            false
        );
        $this->assertTrue($tableGui->numericOrdering("reached"));
        $this->assertTrue($tableGui->numericOrdering("hint_count"));
        $this->assertTrue($tableGui->numericOrdering("exam_id"));
        $this->assertTrue($tableGui->numericOrdering("name"));

        $tableGui = new ilEvaluationAllTableGUI(
            $this->parentObj_mock,
            "",
            false,
            true
        );
        $this->assertTrue($tableGui->numericOrdering("reached"));
        $this->assertTrue($tableGui->numericOrdering("hint_count"));
        $this->assertTrue($tableGui->numericOrdering("exam_id"));
        $this->assertFalse($tableGui->numericOrdering("name"));
    }

    public function testGetSelectableColumns()
    {
        $expected = [
            "gender" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "email" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "institution" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "street" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "city" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "zipcode" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "department" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
            "matriculation" => [
                "txt" => "testTranslation",
                "default" => false,
            ],
        ];

        $tableGui = new ilEvaluationAllTableGUI(
            $this->parentObj_mock,
            "",
            false,
            false
        );

        $this->assertEquals($expected, $tableGui->getSelectableColumns());

        $tableGui = new ilEvaluationAllTableGUI(
            $this->parentObj_mock,
            "",
            true,
            false
        );

        $this->assertEquals([], $tableGui->getSelectableColumns());
    }

    public function testGetSelectedColumns() : void
    {
        $expected = [];
        $this->assertEquals($expected, $this->tableGui->getSelectedColumns());
    }
}
