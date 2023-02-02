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

use PHPUnit\Framework\TestCase;

class ilIndividualAssessmentCommonSettingsGUITest extends TestCase
{
    public function test_createObject(): void
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

    public function test_executeCommand_with_unknown_command(): void
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
