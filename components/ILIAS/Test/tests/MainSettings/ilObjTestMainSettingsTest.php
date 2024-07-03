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

class ilObjTestMainSettingsTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider throwOnDifferentTestIdDataProvider
     */
    public function testThrowOnDifferentTestId(int $IO): void
    {
        $testSettings = $this->createConfiguredMock(TestSettings::class, ['getTestId' => $IO]);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            $IO,
            $IO,
            $this->createConfiguredMock(ilObjTestSettingsGeneral::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsIntroduction::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsAccess::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsTestBehaviour::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsQuestionBehaviour::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsParticipantFunctionality::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsFinishing::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsAdditional::class, ['getTestId' => $IO])
        );

        $output = self::callMethod($ilObjTestMainSettings, 'throwOnDifferentTestId', [$testSettings]);

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
        $testSettings = $this->createMock(TestSettings::class);
        $testSettings->method('getTestId')->willReturn($input['test_id_1']);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            $input['test_id_2'],
            $input['test_id_2'],
            $this->createConfiguredMock(ilObjTestSettingsGeneral::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsIntroduction::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsAccess::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsTestBehaviour::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsQuestionBehaviour::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsParticipantFunctionality::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsFinishing::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsAdditional::class, ['getTestId' => $input['test_id_2']])
        );
        $this->expectException(LogicException::class);
        self::callMethod($ilObjTestMainSettings, 'throwOnDifferentTestId', [$testSettings]);
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
        $ilObjTestMainSettings = (new ilObjTestMainSettings(
            0,
            0,
            $this->createConfiguredMock(
                ilObjTestSettingsGeneral::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsGeneral::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsIntroduction::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsIntroduction::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsAccess::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsAccess::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsTestBehaviour::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsTestBehaviour::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsQuestionBehaviour::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsQuestionBehaviour::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsParticipantFunctionality::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsParticipantFunctionality::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsFinishing::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsFinishing::class)]
            ),
            $this->createConfiguredMock(
                ilObjTestSettingsAdditional::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsAdditional::class)]
            )
        ))->withTestId($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getTestId());
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
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withGeneralSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getGeneralSettings());
    }

    public static function getAndWithGeneralSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsGeneral =>
                $test_case->createMock(ilObjTestSettingsGeneral::class)
        ]];
    }

    /**
     * @dataProvider getAndWithIntroductionSettingsDataProvider
     */
    public function testGetAndWithIntroductionSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withIntroductionSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getIntroductionSettings());
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
    public function testGetAndWithAccessSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withAccessSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getAccessSettings());
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
    public function testGetAndWithTestBehaviourSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withTestBehaviourSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getTestBehaviourSettings());
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
    public function testGetAndWithQuestionBehaviourSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withQuestionBehaviourSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getQuestionBehaviourSettings());
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
    public function testGetAndWithParticipantFunctionalitySettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withParticipantFunctionalitySettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getParticipantFunctionalitySettings());
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
    public function testGetAndWithFinishingSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withFinishingSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getFinishingSettings());
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
    public function testGetAndWithAdditionalSettings(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class)
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withAdditionalSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getAdditionalSettings());
    }

    public static function getAndWithAdditionalSettingsDataProvider(): array
    {
        return [[
            static fn(self $test_case): ilObjTestSettingsAdditional =>
                $test_case->createMock(ilObjTestSettingsAdditional::class)
        ]];
    }
}
