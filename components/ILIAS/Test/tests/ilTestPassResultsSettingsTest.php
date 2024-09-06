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

use ilTestBaseTestCase;
use ilTestPassResultsSettings;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class ilTestPassResultsSettingsTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $test_pass_results_settings = $this->createInstanceOf(ilTestPassResultsSettings::class);
        $this->assertInstanceOf(ilTestPassResultsSettings::class, $test_pass_results_settings);
    }

    /**
     * @dataProvider getShowHiddenQuestionsProvider
     * @throws ReflectionException|Exception
     */
    public function testGetShowHiddenQuestions(?bool $IO): void
    {
        $test_pass_results_settings = is_null($IO)
            ? $this->createInstanceOf(ilTestPassResultsSettings::class)
            : $this->createInstanceOf(ilTestPassResultsSettings::class, ['show_hidden_questions' => $IO]);

        $this->assertEquals($IO ?? false, $test_pass_results_settings->getShowHiddenQuestions());
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
     * @throws ReflectionException|Exception
     */
    public function testGetShowOptionalQuestions(?bool $IO): void
    {
        $test_pass_results_settings = is_null($IO)
            ? $this->createInstanceOf(ilTestPassResultsSettings::class)
            : $this->createInstanceOf(ilTestPassResultsSettings::class, ['show_optional_questions' => $IO]);

        $this->assertEquals($IO ?? false, $test_pass_results_settings->getShowOptionalQuestions());
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
     * @throws ReflectionException|Exception
     */
    public function testGetShowBestSolution(?bool $IO): void
    {
        $test_pass_results_settings = is_null($IO)
            ? $this->createInstanceOf(ilTestPassResultsSettings::class)
            : $this->createInstanceOf(ilTestPassResultsSettings::class, ['show_best_solution' => $IO]);

        $this->assertEquals($IO ?? true, $test_pass_results_settings->getShowBestSolution());
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
     * @throws ReflectionException|Exception
     */
    public function testGetShowFeedback(?bool $IO): void
    {
        $test_pass_results_settings = is_null($IO)
            ? $this->createInstanceOf(ilTestPassResultsSettings::class)
            : $this->createInstanceOf(ilTestPassResultsSettings::class, ['show_feedback' => $IO]);

        $this->assertEquals($IO ?? true, $test_pass_results_settings->getShowFeedback());
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
     * @throws ReflectionException|Exception
     */
    public function testGetQuestionTextOnly(?bool $IO): void
    {
        $test_pass_results_settings = is_null($IO)
            ? $this->createInstanceOf(ilTestPassResultsSettings::class)
            : $this->createInstanceOf(ilTestPassResultsSettings::class, ['question_text_only' => $IO]);

        $this->assertEquals($IO ?? false, $test_pass_results_settings->getQuestionTextOnly());
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
     * @throws ReflectionException|Exception
     */
    public function testGetShowRecapitulation(?bool $IO): void
    {
        $test_pass_results_settings = is_null($IO)
            ? $this->createInstanceOf(ilTestPassResultsSettings::class)
            : $this->createInstanceOf(ilTestPassResultsSettings::class, ['show_recapitulation' => $IO]);

        $this->assertEquals($IO ?? false, $test_pass_results_settings->getShowRecapitulation());
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
