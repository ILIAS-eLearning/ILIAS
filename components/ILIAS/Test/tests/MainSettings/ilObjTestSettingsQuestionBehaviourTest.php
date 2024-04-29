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

class ilObjTestSettingsQuestionBehaviourTest extends ilTestBaseTestCase
{
    private function getTestInstance(): ilObjTestSettingsQuestionBehaviour
    {
        return new ilObjTestSettingsQuestionBehaviour(
            0,
            0,
            true,
            0,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true
        );
    }

    /**
     * @dataProvider getAndWithQuestionTitleOutputModeDataProvider
     */
    public function testGetAndWithQuestionTitleOutputMode(int $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withQuestionTitleOutputMode($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getQuestionTitleOutputMode());
    }

    public static function getAndWithQuestionTitleOutputModeDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithInstantFeedbackDataProvider
     */
    public function testGetAndWithAutosaveEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withAutosaveEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getAutosaveEnabled());
    }

    public static function getAndWithInstantFeedbackDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithAutosaveIntervalDataProvider
     */
    public function testGetAndWithAutosaveInterval(int $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withAutosaveInterval($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getAutosaveInterval());
    }

    public static function getAndWithAutosaveIntervalDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithShuffleQuestionsDataProvider
     */
    public function testGetAndWithShuffleQuestions(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withShuffleQuestions($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getShuffleQuestions());
    }

    public static function getAndWithShuffleQuestionsDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithQuestionHintsEnabledDataProvider
     */
    public function testGetAndWithQuestionHintsEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withQuestionHintsEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getQuestionHintsEnabled());
    }

    public static function getAndWithQuestionHintsEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithInstantFeedbackPointsEnabledDataProvider
     */
    public function testGetAndWithInstantFeedbackPointsEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withInstantFeedbackPointsEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getInstantFeedbackPointsEnabled());
    }

    public static function getAndWithInstantFeedbackPointsEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithInstantFeedbackGenericEnabledDataProvider
     */
    public function testGetAndWithInstantFeedbackGenericEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withInstantFeedbackGenericEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getInstantFeedbackGenericEnabled());
    }

    public static function getAndWithInstantFeedbackGenericEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithInstantFeedbackSpecificEnabledDataProvider
     */
    public function testGetAndWithInstantFeedbackSpecificEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withInstantFeedbackSpecificEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getInstantFeedbackSpecificEnabled());
    }

    public static function getAndWithInstantFeedbackSpecificEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithInstantFeedbackSolutionEnabledDataProvider
     */
    public function testGetAndWithInstantFeedbackSolutionEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withInstantFeedbackSolutionEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getInstantFeedbackSolutionEnabled());
    }

    public static function getAndWithInstantFeedbackSolutionEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithForceInstantFeedbackOnNextQuestionDataProvider
     */
    public function testGetAndWithForceInstantFeedbackOnNextQuestion(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withForceInstantFeedbackOnNextQuestion($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getForceInstantFeedbackOnNextQuestion());
    }

    public static function getAndWithForceInstantFeedbackOnNextQuestionDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithLockAnswerOnInstantFeedbackEnabledDataProvider
     */
    public function testGetAndWithLockAnswerOnInstantFeedbackEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withLockAnswerOnInstantFeedbackEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getLockAnswerOnInstantFeedbackEnabled());
    }

    public static function getAndWithLockAnswerOnInstantFeedbackEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithLockAnswerOnNextQuestionEnabledDataProvider
     */
    public function testGetAndWithLockAnswerOnNextQuestionEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withLockAnswerOnNextQuestionEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getLockAnswerOnNextQuestionEnabled());
    }

    public static function getAndWithLockAnswerOnNextQuestionEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithCompulsoryQuestionsEnabledDataProvider
     */
    public function testGetAndWithCompulsoryQuestionsEnabled(bool $IO): void
    {
        $ilObjTestSettingsQuestionBehaviour = $this->getTestInstance();
        $ilObjTestSettingsQuestionBehaviour = $ilObjTestSettingsQuestionBehaviour->withCompulsoryQuestionsEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsQuestionBehaviour::class, $ilObjTestSettingsQuestionBehaviour);
        $this->assertEquals($IO, $ilObjTestSettingsQuestionBehaviour->getCompulsoryQuestionsEnabled());
    }

    public static function getAndWithCompulsoryQuestionsEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }
}
