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

namespace ILIAS\Test\Access\test;

use ilTestPassResultsSettings;
use PHPUnit\Framework\TestCase;

class ilTestPassResultsSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->testPassResultsSettings = new ilTestPassResultsSettings();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestPassResultsSettings::class, $this->testPassResultsSettings);
    }

    /**
     * @dataProvider getShowHiddenQuestionsProvider
     */
    public function testGetShowHiddenQuestions(?bool $IO): void
    {
        $testPassResultsSettings = is_null($IO)
            ? $this->testPassResultsSettings
            : new ilTestPassResultsSettings(show_hidden_questions: $IO);

        $this->assertEquals($IO ?? false, $testPassResultsSettings->getShowHiddenQuestions());
    }

    public static function getShowHiddenQuestionsProvider(): array
    {
        return [
            'default' => [null],
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider getShowOptionalQuestionsProvider
     */
    public function testGetShowOptionalQuestions(?bool $IO): void
    {
        $testPassResultsSettings = is_null($IO)
            ? $this->testPassResultsSettings
            : new ilTestPassResultsSettings(show_optional_questions: $IO);

        $this->assertEquals($IO ?? false, $testPassResultsSettings->getShowOptionalQuestions());
    }

    public static function getShowOptionalQuestionsProvider(): array
    {
        return [
            'default' => [null],
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider getShowBestSolutionProvider
     */
    public function testGetShowBestSolution(?bool $IO): void
    {
        $testPassResultsSettings = is_null($IO)
            ? $this->testPassResultsSettings
            : new ilTestPassResultsSettings(show_best_solution: $IO);

        $this->assertEquals($IO ?? true, $testPassResultsSettings->getShowBestSolution());
    }

    public static function getShowBestSolutionProvider(): array
    {
        return [
            'default' => [null],
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider getShowFeedbackProvider
     */
    public function testGetShowFeedback(?bool $IO): void
    {
        $testPassResultsSettings = is_null($IO)
            ? $this->testPassResultsSettings
            : new ilTestPassResultsSettings(show_feedback: $IO);

        $this->assertEquals($IO ?? true, $testPassResultsSettings->getShowFeedback());
    }

    public static function getShowFeedbackProvider(): array
    {
        return [
            'default' => [null],
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider getQuestionTextOnlyProvider
     */
    public function testGetQuestionTextOnly(?bool $IO): void
    {
        $testPassResultsSettings = is_null($IO)
            ? $this->testPassResultsSettings
            : new ilTestPassResultsSettings(question_text_only: $IO);

        $this->assertEquals($IO ?? false, $testPassResultsSettings->getQuestionTextOnly());
    }

    public static function getQuestionTextOnlyProvider(): array
    {
        return [
            'default' => [null],
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider getShowRecapitulationProvider
     */
    public function testGetShowRecapitulation(?bool $IO): void
    {
        $testPassResultsSettings = is_null($IO)
            ? $this->testPassResultsSettings
            : new ilTestPassResultsSettings(show_recapitulation: $IO);

        $this->assertEquals($IO ?? false, $testPassResultsSettings->getShowRecapitulation());
    }

    public static function getShowRecapitulationProvider(): array
    {
        return [
            'default' => [null],
            'true' => [true],
            'false' => [false]
        ];
    }
}
