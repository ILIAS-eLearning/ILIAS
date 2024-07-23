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

namespace Test\tests;

use ilQuestionResult;
use ilTestBaseTestCase;

class ilQuestionResultTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertInstanceOf(ilQuestionResult::class, $questionResult);
    }

    /**
     * @dataProvider getIdDataProvider
     */
    public function testGetId(int $IO): void
    {
        $questionResult = new ilQuestionResult(
            $IO,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getId());
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
     */
    public function testGetType(string $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            $IO,
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getType());
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
     */
    public function testGetTitle(string $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            $IO,
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getTitle());
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
     */
    public function testGetUserAnswer(string $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            $IO,
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getUserAnswer());
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
     */
    public function testGetBestSolution(string $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            $IO,
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getBestSolution());
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
     */
    public function testGetQuestionScore(float $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            $IO,
            0.0,
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getQuestionScore());
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
     */
    public function testGetUserScore(float $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            $IO,
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getUserScore());
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
     */
    public function testGetUserScorePercent(array $input, float $output): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            $input['question_score'],
            $input['usr_score'],
            '',
            '',
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($output, $questionResult->getUserScorePercent());
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
     */
    public function testGetCorrect(array $input, int $output): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            $input['question_score'],
            $input['usr_score'],
            '',
            $input['best_solution'],
            '',
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($output, $questionResult->getCorrect());
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
     */
    public function testGetFeedback(string $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            $IO,
            true,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->getFeedback());
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
     */
    public function testIsWorkedThrough(bool $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            $IO,
            true,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->isWorkedThrough());
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
     */
    public function testIsAnswered(bool $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            $IO,
            0,
            ''
        );

        $this->assertEquals($IO, $questionResult->isAnswered());
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
     */
    public function testGetContentForRecapitulation(?string $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            true,
            0,
            $IO
        );

        $this->assertEquals($IO, $questionResult->getContentForRecapitulation());
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
     */
    public function testGetNumberOfRequestedHints(int $IO): void
    {
        $questionResult = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
            '',
            '',
            '',
            true,
            true,
            $IO,
            ''
        );

        $this->assertEquals($IO, $questionResult->getNumberOfRequestedHints());
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
