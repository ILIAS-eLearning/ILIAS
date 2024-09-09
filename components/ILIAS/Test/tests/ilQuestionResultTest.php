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

namespace ILIAS\Test\Tests;

use ilQuestionResult;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class ilQuestionResultTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class);
        $this->assertInstanceOf(ilQuestionResult::class, $il_question_result);
    }

    /**
     * @dataProvider getIdDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetId(int $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['id' => $IO]);
        $this->assertEquals($IO, $il_question_result->getId());
    }

    public static function getIdDataProvider(): array
    {
        return [
            'zero' => [0],
            'one' => [1],
            'two' => [2]
        ];
    }

    /**
     * @dataProvider getTypeDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetType(string $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['type' => $IO]);
        $this->assertEquals($IO, $il_question_result->getType());
    }

    public static function getTypeDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider getTitleDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetTitle(string $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['title' => $IO]);
        $this->assertEquals($IO, $il_question_result->getTitle());
    }

    public static function getTitleDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider getUserAnswerDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetUserAnswer(string $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['usr_solution' => $IO]);
        $this->assertEquals($IO, $il_question_result->getUserAnswer());
    }

    public static function getUserAnswerDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider getBestSolutionDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetBestSolution(string $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['best_solution' => $IO]);
        $this->assertEquals($IO, $il_question_result->getBestSolution());
    }

    public static function getBestSolutionDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider getQuestionScoreDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetQuestionScore(float $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['question_score' => $IO]);
        $this->assertEquals($IO, $il_question_result->getQuestionScore());
    }

    public static function getQuestionScoreDataProvider(): array
    {
        return [
            'zero' => [0.0],
            'one' => [1.0],
            'two' => [2.0]
        ];
    }

    /**
     * @dataProvider getUserScoreDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetUserScore(float $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['usr_score' => $IO]);
        $this->assertEquals($IO, $il_question_result->getUserScore());
    }

    public static function getUserScoreDataProvider(): array
    {
        return [
            'zero' => [0.0],
            'one' => [1.0],
            'two' => [2.0]
        ];
    }

    /**
     * @dataProvider getUserScorePercentDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetUserScorePercent(array $input, float $output): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['question_score' => $input['question_score'], 'usr_score' => $input['usr_score']]);
        $this->assertEquals($output, $il_question_result->getUserScorePercent());
    }

    public static function getUserScorePercentDataProvider(): array
    {
        return [
            'zero' => [['question_score' => 1.0, 'usr_score' => 0.0], 0.0],
            'fifty' => [['question_score' => 1.0, 'usr_score' => 0.5], 50.0],
            'hundred_1' => [['question_score' => 1.0, 'usr_score' => 1.0], 100.0],
            'hundred_2' => [['question_score' => 0.0, 'usr_score' => 1.0], 100.0]
        ];
    }

    /**
     * @dataProvider getCorrectDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetCorrect(array $input, int $output): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['question_score' => $input['question_score'], 'usr_score' => $input['usr_score'], 'best_solution' => $input['best_solution']]);
        $this->assertEquals($output, $il_question_result->getCorrect());
    }

    public static function getCorrectDataProvider(): array
    {
        return [
            'none' => [['question_score' => 1.0, 'usr_score' => 0.0, 'best_solution' => ''], 3],
            'full' => [['question_score' => 1.0, 'usr_score' => 1.0, 'best_solution' => ''], 1],
            'partial' => [['question_score' => 1.0, 'usr_score' => 0.5, 'best_solution' => ''], 2]
        ];
    }

    /**
     * @dataProvider getFeedbackDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetFeedback(string $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['feedback' => $IO]);
        $this->assertEquals($IO, $il_question_result->getFeedback());
    }

    public static function getFeedbackDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider isWorkedThroughDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsWorkedThrough(bool $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['workedthrough' => $IO]);
        $this->assertEquals($IO, $il_question_result->isWorkedThrough());
    }

    public static function isWorkedThroughDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true]
        ];
    }

    /**
     * @dataProvider isAnsweredDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsAnswered(bool $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['answered' => $IO]);
        $this->assertEquals($IO, $il_question_result->isAnswered());
    }

    public static function isAnsweredDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true]
        ];
    }

    /**
     * @dataProvider getContentForRecapitulationDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetContentForRecapitulation(?string $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['content_for_recapitulation' => $IO]);
        $this->assertEquals($IO, $il_question_result->getContentForRecapitulation());
    }

    public static function getContentForRecapitulationDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'null' => [null]
        ];
    }

    /**
     * @dataProvider getNumberOfRequestedHintsDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetNumberOfRequestedHints(int $IO): void
    {
        $il_question_result = $this->createInstanceOf(ilQuestionResult::class, ['requested_hints' => $IO]);
        $this->assertEquals($IO, $il_question_result->getNumberOfRequestedHints());
    }

    public static function getNumberOfRequestedHintsDataProvider(): array
    {
        return [
            'zero' => [0],
            'one' => [1],
            'two' => [2]
        ];
    }
}
