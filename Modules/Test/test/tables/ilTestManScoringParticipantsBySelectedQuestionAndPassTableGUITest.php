<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUITest extends ilTestBaseTestCase
{
    private ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI $tableGui;
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

        $objTest_mock = $this->createMock(ilObjTest::class);
        $objTest_mock->expects($this->any())
                     ->method("getTestQuestions")
                     ->willReturnCallback(function () {
                         return [];
                     });
        $objTest_mock->expects($this->any())
                     ->method("getPotentialRandomTestQuestions")
                     ->willReturnCallback(function () {
                         return [];
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
        $this->parentObj_mock->expects($this->any())->method('getObject')->willReturn($objTest_mock);

        $this->tableGui = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI::class, $this->tableGui);
    }
}
