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
            $this->createConfiguredMock(ilObjTestSettingsGeneral::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsIntroduction::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsAccess::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsTestBehaviour::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsQuestionBehaviour::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsParticipantFunctionality::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsFinishing::class, ['getTestId' => $IO]),
            $this->createConfiguredMock(ilObjTestSettingsAdditional::class, ['getTestId' => $IO]),
        );

        $output = self::callMethod($ilObjTestMainSettings, 'throwOnDifferentTestId', [$testSettings]);

        $this->assertNull($output);
    }

    public function throwOnDifferentTestIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
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
            $this->createConfiguredMock(ilObjTestSettingsGeneral::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsIntroduction::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsAccess::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsTestBehaviour::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsQuestionBehaviour::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsParticipantFunctionality::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsFinishing::class, ['getTestId' => $input['test_id_2']]),
            $this->createConfiguredMock(ilObjTestSettingsAdditional::class, ['getTestId' => $input['test_id_2']]),
        );
        $this->expectException(LogicException::class);
        self::callMethod($ilObjTestMainSettings, 'throwOnDifferentTestId', [$testSettings]);
    }

    public function throwOnDifferentTestIdExceptionDataProvider(): array
    {
        return [
            [['test_id_1' => -1, 'test_id_2' => 0]],
            [['test_id_1' => 0, 'test_id_2' => 1]],
            [['test_id_1' => 1, 'test_id_2' => -1]],
        ];
    }

    /**
     * @dataProvider getAndWithTestIdDataProvider
     */
    public function testGetAndWithTestId(int $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createConfiguredMock(
                ilObjTestSettingsIntroduction::class,
                ['withTestId' => $this->createMock(ilObjTestSettingsIntroduction::class)],
            ),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withTestId($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getTestId());
    }

    public function getAndWithTestIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndWithGeneralSettingsDataProvider
     */
    public function testGetAndWithGeneralSettings(ilObjTestSettingsGeneral $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withGeneralSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getGeneralSettings());
    }

    public function getAndWithGeneralSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsGeneral::class)],
        ];
    }

    /**
     * @dataProvider getAndWithIntroductionSettingsDataProvider
     */
    public function testGetAndWithIntroductionSettings(ilObjTestSettingsIntroduction $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withIntroductionSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getIntroductionSettings());
    }

    public function getAndWithIntroductionSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsIntroduction::class)],
        ];
    }

    /**
     * @dataProvider getAndWithAccessSettingsDataProvider
     */
    public function testGetAndWithAccessSettings(ilObjTestSettingsAccess $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withAccessSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getAccessSettings());
    }

    public function getAndWithAccessSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsAccess::class)],
        ];
    }

    /**
     * @dataProvider getAndWithTestBehaviourSettingsDataProvider
     */
    public function testGetAndWithTestBehaviourSettings(ilObjTestSettingsTestBehaviour $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withTestBehaviourSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getTestBehaviourSettings());
    }

    public function getAndWithTestBehaviourSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsTestBehaviour::class)],
        ];
    }

    /**
     * @dataProvider getAndWithQuestionBehaviourSettingsDataProvider
     */
    public function testGetAndWithQuestionBehaviourSettings(ilObjTestSettingsQuestionBehaviour $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withQuestionBehaviourSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getQuestionBehaviourSettings());
    }

    public function getAndWithQuestionBehaviourSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsQuestionBehaviour::class)],
        ];
    }

    /**
     * @dataProvider getAndWithParticipantFunctionalitySettingsDataProvider
     */
    public function testGetAndWithParticipantFunctionalitySettings(ilObjTestSettingsParticipantFunctionality $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withParticipantFunctionalitySettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getParticipantFunctionalitySettings());
    }

    public function getAndWithParticipantFunctionalitySettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsParticipantFunctionality::class)],
        ];
    }

    /**
     * @dataProvider getAndWithFinishingSettingsDataProvider
     */
    public function testGetAndWithFinishingSettings(ilObjTestSettingsFinishing $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withFinishingSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getFinishingSettings());
    }

    public function getAndWithFinishingSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsFinishing::class)],
        ];
    }

    /**
     * @dataProvider getAndWithAdditionalSettingsDataProvider
     */
    public function testGetAndWithAdditionalSettings(ilObjTestSettingsAdditional $IO): void
    {
        $ilObjTestMainSettings = new ilObjTestMainSettings(
            0,
            $this->createMock(ilObjTestSettingsGeneral::class),
            $this->createMock(ilObjTestSettingsIntroduction::class),
            $this->createMock(ilObjTestSettingsAccess::class),
            $this->createMock(ilObjTestSettingsTestBehaviour::class),
            $this->createMock(ilObjTestSettingsQuestionBehaviour::class),
            $this->createMock(ilObjTestSettingsParticipantFunctionality::class),
            $this->createMock(ilObjTestSettingsFinishing::class),
            $this->createMock(ilObjTestSettingsAdditional::class),
        );
        $ilObjTestMainSettings = $ilObjTestMainSettings->withAdditionalSettings($IO);

        $this->assertInstanceOf(ilObjTestMainSettings::class, $ilObjTestMainSettings);
        $this->assertEquals($IO, $ilObjTestMainSettings->getAdditionalSettings());
    }

    public function getAndWithAdditionalSettingsDataProvider(): array
    {
        return [
            [$this->createMock(ilObjTestSettingsAdditional::class)],
        ];
    }
}