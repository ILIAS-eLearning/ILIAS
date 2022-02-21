<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

class ilIndividualAssessmentCommonSettingsGUITest extends TestCase
{
    public function test_createObject() : void
    {
        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $ctrl = $this->createMock(ilCtrl::class);
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $obj_service = $this->createMock(ilObjectService::class);

        $obj = new ilIndividualAssessmentCommonSettingsGUI(
            $iass,
            $ctrl,
            $tpl,
            $lng,
            $obj_service
        );

        $this->assertInstanceOf(ilIndividualAssessmentCommonSettingsGUI::class, $obj);
    }

    public function test_executeCommand_with_unknown_command() : void
    {
        $iass = $this->createMock(ilObjIndividualAssessment::class);
        $ctrl = $this->createMock(ilCtrl::class);
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $obj_service = $this->createMock(ilObjectService::class);

        $ctrl
            ->expects($this->once())
            ->method("getCmd")
            ->willReturn("unknown_command")
        ;

        $obj = new ilIndividualAssessmentCommonSettingsGUI(
            $iass,
            $ctrl,
            $tpl,
            $lng,
            $obj_service
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unknown command unknown_command");

        $obj->executeCommand();
    }
}
