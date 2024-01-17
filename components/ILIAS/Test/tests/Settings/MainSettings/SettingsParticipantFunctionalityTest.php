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

use ILIAS\Test\Settings\MainSettings\SettingsParticipantFunctionality;

class SettingsParticipantFunctionalityTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithUsePreviousAnswerAllowedDataProvider
     */
    public function testGetAndWithUsePreviousAnswerAllowed(bool $io): void
    {
        $Settings_participant_functionality = (new SettingsParticipantFunctionality(0))->withUsePreviousAnswerAllowed($io);

        $this->assertInstanceOf(SettingsParticipantFunctionality::class, $Settings_participant_functionality);
        $this->assertEquals($io, $Settings_participant_functionality->getUsePreviousAnswerAllowed());
    }

    public static function getAndWithUsePreviousAnswerAllowedDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithSuspendTestAllowedDataProvider
     */
    public function testGetAndWithSuspendTestAllowed(bool $io): void
    {
        $Settings_participant_functionality = (new SettingsParticipantFunctionality(0))->withSuspendTestAllowed($io);

        $this->assertInstanceOf(SettingsParticipantFunctionality::class, $Settings_participant_functionality);
        $this->assertEquals($io, $Settings_participant_functionality->getSuspendTestAllowed());
    }

    public static function getAndWithSuspendTestAllowedDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithPostponedQuestionsMoveToEndDataProvider
     */
    public function testGetAndWithPostponedQuestionsMoveToEnd(bool $io): void
    {
        $Settings_participant_functionality = (new SettingsParticipantFunctionality(0))->withPostponedQuestionsMoveToEnd($io);

        $this->assertInstanceOf(SettingsParticipantFunctionality::class, $Settings_participant_functionality);
        $this->assertEquals($io, $Settings_participant_functionality->getPostponedQuestionsMoveToEnd());
    }

    public static function getAndWithPostponedQuestionsMoveToEndDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithQuestionListEnabledDataProvider
     */
    public function testGetAndWithQuestionListEnabled(bool $io): void
    {
        $Settings_participant_functionality = (new SettingsParticipantFunctionality(0))->withQuestionListEnabled($io);

        $this->assertInstanceOf(SettingsParticipantFunctionality::class, $Settings_participant_functionality);
        $this->assertEquals($io, $Settings_participant_functionality->getQuestionListEnabled());
    }

    public static function getAndWithQuestionListEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithUsrPassOverviewModeDataProvider
     */
    public function testGetAndWithUsrPassOverviewMode(int $io): void
    {
        $Settings_participant_functionality = (new SettingsParticipantFunctionality(0))->withUsrPassOverviewMode($io);

        $this->assertInstanceOf(SettingsParticipantFunctionality::class, $Settings_participant_functionality);
        $this->assertEquals($io, $Settings_participant_functionality->getUsrPassOverviewMode());
    }

    public static function getAndWithUsrPassOverviewModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithUsrPassOverviewEnabledDataProvider
     */
    public function testGetAndWithQuestionMarkingEnabled(bool $io): void
    {
        $Settings_participant_functionality = (new SettingsParticipantFunctionality(0));
        $Settings_participant_functionality = $Settings_participant_functionality->withQuestionMarkingEnabled($io);

        $this->assertInstanceOf(SettingsParticipantFunctionality::class, $Settings_participant_functionality);
        $this->assertEquals($io, $Settings_participant_functionality->getQuestionMarkingEnabled());
    }

    public static function getAndWithUsrPassOverviewEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
