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

class ilObjTestSettingsParticipantFunctionalityTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithUsePreviousAnswerAllowedDataProvider
     */
    public function testGetAndWithUsePreviousAnswerAllowed(bool $IO): void
    {
        $ilObjTestSettingsParticipantFunctionality = (new ilObjTestSettingsParticipantFunctionality(0));
        $ilObjTestSettingsParticipantFunctionality = $ilObjTestSettingsParticipantFunctionality->withUsePreviousAnswerAllowed($IO);

        $this->assertInstanceOf(ilObjTestSettingsParticipantFunctionality::class, $ilObjTestSettingsParticipantFunctionality);
        $this->assertEquals($IO, $ilObjTestSettingsParticipantFunctionality->getUsePreviousAnswerAllowed());
    }

    public function getAndWithUsePreviousAnswerAllowedDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithSuspendTestAllowedDataProvider
     */
    public function testGetAndWithSuspendTestAllowed(bool $IO): void
    {
        $ilObjTestSettingsParticipantFunctionality = (new ilObjTestSettingsParticipantFunctionality(0));
        $ilObjTestSettingsParticipantFunctionality = $ilObjTestSettingsParticipantFunctionality->withSuspendTestAllowed($IO);

        $this->assertInstanceOf(ilObjTestSettingsParticipantFunctionality::class, $ilObjTestSettingsParticipantFunctionality);
        $this->assertEquals($IO, $ilObjTestSettingsParticipantFunctionality->getSuspendTestAllowed());
    }

    public function getAndWithSuspendTestAllowedDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithPostponedQuestionsMoveToEndDataProvider
     */
    public function testGetAndWithPostponedQuestionsMoveToEnd(bool $IO): void
    {
        $ilObjTestSettingsParticipantFunctionality = (new ilObjTestSettingsParticipantFunctionality(0));
        $ilObjTestSettingsParticipantFunctionality = $ilObjTestSettingsParticipantFunctionality->withPostponedQuestionsMoveToEnd($IO);

        $this->assertInstanceOf(ilObjTestSettingsParticipantFunctionality::class, $ilObjTestSettingsParticipantFunctionality);
        $this->assertEquals($IO, $ilObjTestSettingsParticipantFunctionality->getPostponedQuestionsMoveToEnd());
    }

    public function getAndWithPostponedQuestionsMoveToEndDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithQuestionListEnabledDataProvider
     */
    public function testGetAndWithQuestionListEnabled(bool $IO): void
    {
        $ilObjTestSettingsParticipantFunctionality = (new ilObjTestSettingsParticipantFunctionality(0));
        $ilObjTestSettingsParticipantFunctionality = $ilObjTestSettingsParticipantFunctionality->withQuestionListEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsParticipantFunctionality::class, $ilObjTestSettingsParticipantFunctionality);
        $this->assertEquals($IO, $ilObjTestSettingsParticipantFunctionality->getQuestionListEnabled());
    }

    public function getAndWithQuestionListEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithUsrPassOverviewModeDataProvider
     */
    public function testGetAndWithUsrPassOverviewMode(int $IO): void
    {
        $ilObjTestSettingsParticipantFunctionality = (new ilObjTestSettingsParticipantFunctionality(0));
        $ilObjTestSettingsParticipantFunctionality = $ilObjTestSettingsParticipantFunctionality->withUsrPassOverviewMode($IO);

        $this->assertInstanceOf(ilObjTestSettingsParticipantFunctionality::class, $ilObjTestSettingsParticipantFunctionality);
        $this->assertEquals($IO, $ilObjTestSettingsParticipantFunctionality->getUsrPassOverviewMode());
    }

    public function getAndWithUsrPassOverviewModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndWithUsrPassOverviewEnabledDataProvider
     */
    public function testGetAndWithQuestionMarkingEnabled(bool $IO): void
    {
        $ilObjTestSettingsParticipantFunctionality = (new ilObjTestSettingsParticipantFunctionality(0));
        $ilObjTestSettingsParticipantFunctionality = $ilObjTestSettingsParticipantFunctionality->withQuestionMarkingEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsParticipantFunctionality::class, $ilObjTestSettingsParticipantFunctionality);
        $this->assertEquals($IO, $ilObjTestSettingsParticipantFunctionality->getQuestionMarkingEnabled());
    }

    public function getAndWithUsrPassOverviewEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}