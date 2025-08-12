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

use ILIAS\Test\Settings\TestSettings;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\MainSettings\SettingsGeneral;
use ILIAS\Test\Settings\MainSettings\SettingsIntroduction;
use ILIAS\Test\Settings\MainSettings\SettingsAccess;
use ILIAS\Test\Settings\MainSettings\SettingsTestBehaviour;
use ILIAS\Test\Settings\MainSettings\SettingsQuestionBehaviour;
use ILIAS\Test\Settings\MainSettings\SettingsParticipantFunctionality;
use ILIAS\Test\Settings\MainSettings\SettingsFinishing;
use ILIAS\Test\Settings\MainSettings\SettingsAdditional;

class MainSettingsTest extends ilTestBaseTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithGeneralSettingsDataProvider')]
    public function testGetAndWithGeneralSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withGeneralSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getGeneralSettings());
    }

    public static function getAndWithGeneralSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsGeneral =>
                $test_case->createMock(SettingsGeneral::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithIntroductionSettingsDataProvider')]
    public function testGetAndWithIntroductionSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withIntroductionSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getIntroductionSettings());
    }

    public static function getAndWithIntroductionSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsIntroduction =>
                $test_case->createMock(SettingsIntroduction::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithAccessSettingsDataProvider')]
    public function testGetAndWithAccessSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withAccessSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getAccessSettings());
    }

    public static function getAndWithAccessSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsAccess =>
                $test_case->createMock(SettingsAccess::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithTestBehaviourSettingsDataProvider')]
    public function testGetAndWithTestBehaviourSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withTestBehaviourSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getTestBehaviourSettings());
    }

    public static function getAndWithTestBehaviourSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsTestBehaviour =>
                $test_case->createMock(SettingsTestBehaviour::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithQuestionBehaviourSettingsDataProvider')]
    public function testGetAndWithQuestionBehaviourSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withQuestionBehaviourSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getQuestionBehaviourSettings());
    }

    public static function getAndWithQuestionBehaviourSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsQuestionBehaviour =>
                $test_case->createMock(SettingsQuestionBehaviour::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithParticipantFunctionalitySettingsDataProvider')]
    public function testGetAndWithParticipantFunctionalitySettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withParticipantFunctionalitySettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getParticipantFunctionalitySettings());
    }

    public static function getAndWithParticipantFunctionalitySettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsParticipantFunctionality =>
                $test_case->createMock(SettingsParticipantFunctionality::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithFinishingSettingsDataProvider')]
    public function testGetAndWithFinishingSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withFinishingSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getFinishingSettings());
    }

    public static function getAndWithFinishingSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsFinishing =>
                $test_case->createMock(SettingsFinishing::class)
        ]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithAdditionalSettingsDataProvider')]
    public function testGetAndWithAdditionalSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createMock(SettingsIntroduction::class),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withAdditionalSettings($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getAdditionalSettings());
    }

    public static function getAndWithAdditionalSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): SettingsAdditional =>
                $test_case->createMock(SettingsAdditional::class)
        ]];
    }
}
