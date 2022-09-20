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
 * Class ilMarkSchemaTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilMarkSchemaTableGUITest extends ilTestBaseTestCase
{
    private ilMarkSchemaTableGUI $tableGui;
    private ilMarkSchemaGUI $parentObj_mock;

    protected function setUp(): void
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
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable("ilToolbar", $this->createMock(ilToolbarGUI::class));

        $this->parentObj_mock = $this->createMock(ilMarkSchemaGUI::class);

        $assMarkSchema = $this->createMock(ASS_MarkSchema::class);
        $assMarkSchema->expects($this->any())
                      ->method("getMarkSteps")
                      ->willReturn([]);

        $markSchemaAware_mock = $this->createMock(ilMarkSchemaAware::class);
        $markSchemaAware_mock
            ->expects($this->any())
            ->method("getMarkSchema")
            ->willReturn($assMarkSchema);
        $this->tableGui = new ilMarkSchemaTableGUI(
            $this->parentObj_mock,
            "",
            "",
            $markSchemaAware_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilMarkSchemaTableGUI::class, $this->tableGui);
    }
}
