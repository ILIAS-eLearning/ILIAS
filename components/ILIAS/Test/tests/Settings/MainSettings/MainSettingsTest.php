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
    /**
     * @dataProvider throwOnDifferentTestIdDataProvider
     */
    public function testThrowOnDifferentTestId(int $IO): void
    {
        $test_settings = $this->createConfiguredMock(TestSettings::class, ['getTestId' => $IO]);
        $main_settings = new MainSettings(
            $IO,
            0,
            $this->createConfiguredMock(SettingsGeneral::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsIntroduction::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsAccess::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsTestBehaviour::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsQuestionBehaviour::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsParticipantFunctionality::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsFinishing::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(SettingsAdditional::class, ['getTestId' => $IO])
        );

        $output = self::callMethod($main_settings, 'throwOnDifferentTestId', [$test_settings]);

        $this->assertNull($output);
    }

    public static function throwOnDifferentTestIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider throwOnDifferentTestIdExceptionDataProvider
     */
    public function testThrowOnDifferentTestIdException(array $input): void
    {
        $test_settings = $this->createMock(TestSettings::class);
        $test_settings->method('getTestId')->willReturn($input['test_id_1']);
        $main_settings = new MainSettings(
            $input['test_id_2'],
            0,
            $this->createConfiguredMock(SettingsGeneral::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsIntroduction::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsAccess::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsTestBehaviour::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsQuestionBehaviour::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsParticipantFunctionality::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsFinishing::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(SettingsAdditional::class, ['getTestId' => $input['test_id_2']])
        );
        $this->expectException(LogicException::class);
        self::callMethod($main_settings, 'throwOnDifferentTestId', [$test_settings]);
    }

    public static function throwOnDifferentTestIdExceptionDataProvider(): array
    {
        return [
            [['test_id_1' => -1, 'test_id_2' => 0]],
            [['test_id_1' => 0, 'test_id_2' => 1]],
            [['test_id_1' => 1, 'test_id_2' => -1]]
        ];
    }

    /**
     * @dataProvider getAndWithTestIdDataProvider
     */
    public function testGetAndWithTestId(int $IO): void
    {
        $main_settings = (new MainSettings(
            0,
            0,
            $this->createConfiguredMock(
                SettingsGeneral::class,
                ['withTestId' => $this->createMock(SettingsGeneral::class)]
            ),
            $this->createConfiguredMock(
                SettingsIntroduction::class,
                ['withTestId' => $this->createMock(SettingsIntroduction::class)]
            ),
            $this->createConfiguredMock(
                SettingsAccess::class,
                ['withTestId' => $this->createMock(SettingsAccess::class)]
            ),
            $this->createConfiguredMock(
                SettingsTestBehaviour::class,
                ['withTestId' => $this->createMock(SettingsTestBehaviour::class)]
            ),
            $this->createConfiguredMock(
                SettingsQuestionBehaviour::class,
                ['withTestId' => $this->createMock(SettingsQuestionBehaviour::class)]
            ),
            $this->createConfiguredMock(
                SettingsParticipantFunctionality::class,
                ['withTestId' => $this->createMock(SettingsParticipantFunctionality::class)]
            ),
            $this->createConfiguredMock(
                SettingsFinishing::class,
                ['withTestId' => $this->createMock(SettingsFinishing::class)]
            ),
            $this->createConfiguredMock(
                SettingsAdditional::class,
                ['withTestId' => $this->createMock(SettingsAdditional::class)]
            )
        ))->withTestId($IO);


        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getTestId());
    }

    public static function getAndWithTestIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithGeneralSettingsDataProvider
     */
    public function testGetAndWithGeneralSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithIntroductionSettingsDataProvider
     */
    public function testGetAndWithIntroductionSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithAccessSettingsDataProvider
     */
    public function testGetAndWithAccessSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithTestBehaviourSettingsDataProvider
     */
    public function testGetAndWithTestBehaviourSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithQuestionBehaviourSettingsDataProvider
     */
    public function testGetAndWithQuestionBehaviourSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithParticipantFunctionalitySettingsDataProvider
     */
    public function testGetAndWithParticipantFunctionalitySettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithFinishingSettingsDataProvider
     */
    public function testGetAndWithFinishingSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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

    /**
     * @dataProvider getAndWithAdditionalSettingsDataProvider
     */
    public function testGetAndWithAdditionalSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $main_settings = (new MainSettings(
            0,
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
