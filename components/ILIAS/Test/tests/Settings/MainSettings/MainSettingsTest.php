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

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\MainSettings\SettingsAccess;
use ILIAS\Test\Settings\MainSettings\SettingsAdditional;
use ILIAS\Test\Settings\MainSettings\SettingsFinishing;
use ILIAS\Test\Settings\MainSettings\SettingsGeneral;
use ILIAS\Test\Settings\MainSettings\SettingsIntroduction;
use ILIAS\Test\Settings\MainSettings\SettingsParticipantFunctionality;
use ILIAS\Test\Settings\MainSettings\SettingsQuestionBehaviour;
use ILIAS\Test\Settings\MainSettings\SettingsTestBehaviour;
use ILIAS\Test\Settings\TestSettings;
use PHPUnit\Framework\MockObject\Exception;

class MainSettingsTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(MainSettings::class, $this->createInstanceOf(MainSettings::class));
    }

    /**
     * @dataProvider throwOnDifferentTestIdDataProvider
     * @throws Exception|ReflectionException
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
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [1]
        ];
    }

    /**
     * @dataProvider throwOnDifferentTestIdExceptionDataProvider
     * @throws Exception|ReflectionException
     */
    public function testThrowOnDifferentTestIdException(array $input): void
    {
        $test_settings = $this->createConfiguredMock(TestSettings::class, ['getTestId' => $input['test_id_1']]);
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
            'negative_one_zero' => [['test_id_1' => -1, 'test_id_2' => 0]],
            'negative_one_one' => [['test_id_1' => -1, 'test_id_2' => 1]],
            'zero_negative_one' => [['test_id_1' => 0, 'test_id_2' => -1]],
            'zero_one' => [['test_id_1' => 0, 'test_id_2' => 1]],
            'one_negative_one' => [['test_id_1' => 1, 'test_id_2' => -1]],
            'one_negative_zero' => [['test_id_1' => 1, 'test_id_2' => 0]]
        ];
    }

    /**
     * @dataProvider getAndWithTestIdDataProvider
     * @throws Exception
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
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [1]
        ];
    }

    /**
     * @dataProvider getAndWithObjIdDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetAndWithObjId(int $IO): void
    {
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withObjId($IO);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($IO, $main_settings->getObjId());
    }

    public static function getAndWithObjIdDataProvider(): array
    {
        return [
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [1]
        ];
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithGeneralSettings(): void
    {
        $settings_general = $this->createMock(SettingsGeneral::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withGeneralSettings($settings_general);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_general, $main_settings->getGeneralSettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithIntroductionSettings(): void
    {
        $settings_introduction = $this->createMock(SettingsIntroduction::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withIntroductionSettings($settings_introduction);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_introduction, $main_settings->getIntroductionSettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithAccessSettings(): void
    {
        $settings_access = $this->createMock(SettingsAccess::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withAccessSettings($settings_access);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_access, $main_settings->getAccessSettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithTestBehaviourSettings(): void
    {
        $settings_test_behaviour = $this->createMock(SettingsTestBehaviour::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withTestBehaviourSettings($settings_test_behaviour);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_test_behaviour, $main_settings->getTestBehaviourSettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithQuestionBehaviourSettings(): void
    {
        $settings_question_behaviour = $this->createMock(SettingsQuestionBehaviour::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withQuestionBehaviourSettings($settings_question_behaviour);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_question_behaviour, $main_settings->getQuestionBehaviourSettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithParticipantFunctionalitySettings(): void
    {
        $settings_participant_functionality = $this->createMock(SettingsParticipantFunctionality::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withParticipantFunctionalitySettings($settings_participant_functionality);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_participant_functionality, $main_settings->getParticipantFunctionalitySettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithFinishingSettings(): void
    {
        $settings_finishing = $this->createMock(SettingsFinishing::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withFinishingSettings($settings_finishing);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_finishing, $main_settings->getFinishingSettings());
    }

    /**
     * @throws Exception|ReflectionException
     */
    public function testGetAndWithAdditionalSettings(): void
    {
        $settings_additional = $this->createMock(SettingsAdditional::class);
        $main_settings = $this->createInstanceOf(MainSettings::class, [
            'test_id' => 0,
            'obj_id' => 0
        ])->withAdditionalSettings($settings_additional);

        $this->assertInstanceOf(MainSettings::class, $main_settings);
        $this->assertEquals($settings_additional, $main_settings->getAdditionalSettings());
    }

    /**
     * @throws Exception
     */
    public function testGetArrayForLog(): void
    {
        $main_settings = new MainSettings(
            0,
            0,
            $this->createConfiguredMock(SettingsGeneral::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsIntroduction::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsAccess::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsTestBehaviour::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsQuestionBehaviour::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsParticipantFunctionality::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsFinishing::class, ['toLog' => []]),
            $this->createConfiguredMock(SettingsAdditional::class, ['toLog' => []])
        );

        $array_for_log = $main_settings->getArrayForLog($this->createMock(AdditionalInformationGenerator::class));

        $this->assertIsArray($array_for_log);
        $this->assertCount(0, $array_for_log);
    }
}
