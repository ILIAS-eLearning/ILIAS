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

use ILIAS\Test\MainSettings\MainSettings;
use ILIAS\Test\MainSettings\TestSettings;
use ILIAS\Test\MainSettings\SettingsGeneral;
use ILIAS\Test\MainSettings\SettingsIntroduction;
use ILIAS\Test\MainSettings\SettingsAccess;
use ILIAS\Test\MainSettings\SettingsTestBehaviour;
use ILIAS\Test\MainSettings\SettingsQuestionBehaviour;
use ILIAS\Test\MainSettings\SettingsParticipantFunctionality;
use ILIAS\Test\MainSettings\SettingsFinishing;
use ILIAS\Test\MainSettings\SettingsAdditional;

class MainSettingsTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider throwOnDifferentTestIdDataProvider
     */
    public function testThrowOnDifferentTestId(int $io): void
    {
        $test_settings = $this->createConfiguredMock(TestSettings::class, ['getTestId' => $io]);
        $main_settings = new MainSettings(
            $io,
            $this->createConfiguredMock(SettingsGeneral::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsIntroduction::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsAccess::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsTestBehaviour::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsQuestionBehaviour::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsParticipantFunctionality::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsFinishing::class, ['getTestId' => $io]),
            $this->createConfiguredMock(SettingsAdditional::class, ['getTestId' => $io])
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
    public function testGetAndWithTestId(int $io): void
    {
        $main_settings = (new MainSettings(
            0,
            $this->createMock(SettingsGeneral::class),
            $this->createConfiguredMock(
                SettingsIntroduction::class,
                ['withTestId' => $this->createMock(SettingsIntroduction::class)]
            ),
            $this->createMock(SettingsAccess::class),
            $this->createMock(SettingsTestBehaviour::class),
            $this->createMock(SettingsQuestionBehaviour::class),
            $this->createMock(SettingsParticipantFunctionality::class),
            $this->createMock(SettingsFinishing::class),
            $this->createMock(SettingsAdditional::class)
        ))->withTestId($io);


        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getTestId());
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
    public function testGetAndWithGeneralSettings(\Closure $io): void
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
        ))->withGeneralSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getGeneralSettings());
    }

    public static  function getAndWithGeneralSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsGeneral =>
                $test_case->createMock(ilObjTestSettingsGeneral::class)
        ]];
    }

    /**
     * @dataProvider getAndWithIntroductionSettingsDataProvider
     */
    public function testGetAndWithIntroductionSettings(\Closure $io): void
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
        ))->withIntroductionSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getIntroductionSettings());
    }

    public static function getAndWithIntroductionSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsIntroduction =>
                $test_case->createMock(ilObjTestSettingsIntroduction::class)
        ]];
    }

    /**
     * @dataProvider getAndWithAccessSettingsDataProvider
     */
    public function testGetAndWithAccessSettings(\Closure $io): void
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
        ))->withAccessSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getAccessSettings());
    }

    public static function getAndWithAccessSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsAccess =>
                $test_case->createMock(ilObjTestSettingsAccess::class)
        ]];
    }

    /**
     * @dataProvider getAndWithTestBehaviourSettingsDataProvider
     */
    public function testGetAndWithTestBehaviourSettings(\Closure $io): void
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
        ))->withTestBehaviourSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getTestBehaviourSettings());
    }

    public static function getAndWithTestBehaviourSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsTestBehaviour =>
                $test_case->createMock(ilObjTestSettingsTestBehaviour::class)
        ]];
    }

    /**
     * @dataProvider getAndWithQuestionBehaviourSettingsDataProvider
     */
    public function testGetAndWithQuestionBehaviourSettings(\Closure $io): void
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
        ))->withQuestionBehaviourSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getQuestionBehaviourSettings());
    }

    public static function getAndWithQuestionBehaviourSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsQuestionBehaviour =>
                $test_case->createMock(ilObjTestSettingsQuestionBehaviour::class)
        ]];
    }

    /**
     * @dataProvider getAndWithParticipantFunctionalitySettingsDataProvider
     */
    public function testGetAndWithParticipantFunctionalitySettings(\Closure $io): void
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
        ))->withParticipantFunctionalitySettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getParticipantFunctionalitySettings());
    }

    public static function getAndWithParticipantFunctionalitySettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsParticipantFunctionality =>
                $test_case->createMock(ilObjTestSettingsParticipantFunctionality::class)
        ]];
    }

    /**
     * @dataProvider getAndWithFinishingSettingsDataProvider
     */
    public function testGetAndWithFinishingSettings(\Closure $io): void
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
        ))->withFinishingSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getFinishingSettings());
    }

    public static function getAndWithFinishingSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsFinishing =>
                $test_case->createMock(ilObjTestSettingsFinishing::class)
        ]];
    }

    /**
     * @dataProvider getAndWithAdditionalSettingsDataProvider
     */
    public function testGetAndWithAdditionalSettings(\Closure $io): void
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
        ))->withAdditionalSettings($io);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($io, $main_settings->getAdditionalSettings());
    }

    public static function getAndWithAdditionalSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsAdditional =>
                $test_case->createMock(ilObjTestSettingsAdditional::class)
        ]];
    }
}
