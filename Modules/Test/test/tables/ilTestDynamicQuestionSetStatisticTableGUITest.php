<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestDynamicQuestionSetStatisticTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDynamicQuestionSetStatisticTableGUITest extends ilTestBaseTestCase
{
    private ilTestDynamicQuestionSetStatisticTableGUI $tableGui;
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
        $this->setGlobalVariable("ilPluginAdmin", new ilPluginAdmin($this->createMock(ilComponentRepository::class)));
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));

        $this->parentObj_mock = $this->getMockBuilder(ilObjTestGUI::class)->disableOriginalConstructor()->onlyMethods(array('getObject'))->getMock();
        $this->parentObj_mock->expects($this->any())->method('getObject')->willReturn($this->createMock(ilObjTest::class));
        $this->tableGui = new ilTestDynamicQuestionSetStatisticTableGUI(
            $ctrl_mock,
            $lng_mock,
            $this->parentObj_mock,
            "",
            0
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestDynamicQuestionSetStatisticTableGUI::class, $this->tableGui);
    }

    public function testFilterSelection() : void
    {
        $this->assertNull($this->tableGui->getFilterSelection());

        $this->tableGui->setFilterSelection(new ilTestDynamicQuestionSetFilterSelection());
        $this->assertInstanceOf(
            ilTestDynamicQuestionSetFilterSelection::class,
            $this->tableGui->getFilterSelection()
        );
    }

    public function testInitTitle() : void
    {
        $this->tableGui->initTitle("tastas");
        $this->assertEquals("testTranslation", $this->tableGui->title);
    }

    public function testTaxIds() : void
    {
        $this->assertIsArray($this->tableGui->getTaxIds());
        $expected = [10, 1250, 1233591, 12350];
        $this->tableGui->setTaxIds($expected);
        $this->assertEquals($expected, $this->tableGui->getTaxIds());
    }

    public function testAnswerStatusFilterEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isAnswerStatusFilterEnabled());
        $this->tableGui->setAnswerStatusFilterEnabled(false);
        $this->assertFalse($this->tableGui->isAnswerStatusFilterEnabled());
        $this->tableGui->setAnswerStatusFilterEnabled(true);
        $this->assertTrue($this->tableGui->isAnswerStatusFilterEnabled());
    }

    public function testTaxonomyFilterEnabled() : void
    {
        $this->assertIsBool($this->tableGui->isTaxonomyFilterEnabled());
        $this->tableGui->setTaxonomyFilterEnabled(false);
        $this->assertFalse($this->tableGui->isTaxonomyFilterEnabled());
        $this->tableGui->setTaxonomyFilterEnabled(true);
        $this->assertTrue($this->tableGui->isTaxonomyFilterEnabled());
    }
}
