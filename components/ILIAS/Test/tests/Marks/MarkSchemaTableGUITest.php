<?php

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

declare(strict_types=1);

use ILIAS\Test\Marks\MarkSchemaTableGUI;
use ILIAS\Test\Marks\MarkSchemaGUI;
use ILIAS\Test\Marks\MarkSchema;
use ILIAS\Test\Marks\MarkSchemaAware;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class MarkSchemaTableGUITest extends ilTestBaseTestCase
{
    private MarkSchemaTableGUI $tableGui;
    private MarkSchemaGUI $parentObj_mock;

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

        $this->parentObj_mock = $this->createMock(MarkSchemaGUI::class);

        $assMarkSchema = $this->createMock(MarkSchema::class);
        $assMarkSchema->expects($this->any())
                      ->method("getMarkSteps")
                      ->willReturn([]);

        $markSchemaAware_mock = $this->createMock(MarkSchemaAware::class);
        $markSchemaAware_mock
            ->expects($this->any())
            ->method("getMarkSchema")
            ->willReturn($assMarkSchema);
        $this->tableGui = new MarkSchemaTableGUI(
            $this->parentObj_mock,
            "",
            $markSchemaAware_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(MarkSchemaTableGUI::class, $this->tableGui);
    }
}
