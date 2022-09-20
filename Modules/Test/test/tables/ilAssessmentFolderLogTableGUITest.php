<?php

declare(strict_types=1);

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

/**
 * Class ilAssessmentFolderLogTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilAssessmentFolderLogTableGUITest extends ilTestBaseTestCase
{
    private ilAssessmentFolderLogTableGUI $tableGui;
    private ilObjAssessmentFolderGUI $parentObj_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $lng_mock = $this->createMock(ilLanguage::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
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
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));

        $this->parentObj_mock = $this->getMockBuilder(ilObjAssessmentFolderGUI::class)->disableOriginalConstructor()->onlyMethods(array('getObject'))->getMock();
        $this->parentObj_mock->method('getObject')->willReturn($this->createMock(ilObjTest::class));
        $this->tableGui = new ilAssessmentFolderLogTableGUI($this->parentObj_mock, "");
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilAssessmentFolderLogTableGUI::class, $this->tableGui);
    }
}
