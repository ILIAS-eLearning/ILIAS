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

use ILIAS\Test\Settings\MainSettings\SettingsQuestionBehaviour;

class SettingsQuestionBehaviourTest extends ilTestBaseTestCase
{
    private function getTestInstance(): SettingsQuestionBehaviour
    {
        return new SettingsQuestionBehaviour(
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
    public function testGetAndWithQuestionTitleOutputMode(int $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withQuestionTitleOutputMode($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getQuestionTitleOutputMode());
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
    public function testGetAndWithAutosaveEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withAutosaveEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getAutosaveEnabled());
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
    public function testGetAndWithAutosaveInterval(int $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withAutosaveInterval($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getAutosaveInterval());
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
    public function testGetAndWithShuffleQuestions(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withShuffleQuestions($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getShuffleQuestions());
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
    public function testGetAndWithQuestionHintsEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withQuestionHintsEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getQuestionHintsEnabled());
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
    public function testGetAndWithInstantFeedbackPointsEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withInstantFeedbackPointsEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getInstantFeedbackPointsEnabled());
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
    public function testGetAndWithInstantFeedbackGenericEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withInstantFeedbackGenericEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getInstantFeedbackGenericEnabled());
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
    public function testGetAndWithInstantFeedbackSpecificEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withInstantFeedbackSpecificEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getInstantFeedbackSpecificEnabled());
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
    public function testGetAndWithInstantFeedbackSolutionEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withInstantFeedbackSolutionEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getInstantFeedbackSolutionEnabled());
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
    public function testGetAndWithForceInstantFeedbackOnNextQuestion(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withForceInstantFeedbackOnNextQuestion($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getForceInstantFeedbackOnNextQuestion());
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
    public function testGetAndWithLockAnswerOnInstantFeedbackEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withLockAnswerOnInstantFeedbackEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getLockAnswerOnInstantFeedbackEnabled());
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
    public function testGetAndWithLockAnswerOnNextQuestionEnabled(bool $io): void
    {
        $Settings_question_behaviour = $this->getTestInstance()->withLockAnswerOnNextQuestionEnabled($io);

        $this->assertInstanceOf(SettingsQuestionBehaviour::class, $Settings_question_behaviour);
        $this->assertEquals($io, $Settings_question_behaviour->getLockAnswerOnNextQuestionEnabled());
    }

    public static function getAndWithLockAnswerOnNextQuestionEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }
}
