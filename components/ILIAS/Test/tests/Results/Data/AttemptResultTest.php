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

namespace ILIAS\Test\Tests\Results\Data;

use ILIAS\Test\Results\Data\AttemptResult;
use ILIAS\Test\Results\Data\QuestionResult;

class AttemptResultTest extends \ilTestBaseTestCase
{
    public static function getSettingsDataProvider(): array
    {
        return [
            [new \ilTestPass()]
        ];
    }

    /**
     * @dataProvider getActiveIdDataProvider
     */
    public function testGetActiveId(int $IO): void
    {
        $ilTestPassResult = new AttemptResult(
            $IO,
            0,
            []
        );
        $this->assertEquals($IO, $ilTestPassResult->getActiveId());
    }

    public static function getActiveIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getPassDataProvider
     */
    public function testGetAttempt(int $IO): void
    {
        $ilTestPassResult = new AttemptResult(
            0,
            $IO,
            []
        );
        $this->assertEquals($IO, $ilTestPassResult->getAttempt());
    }

    public static function getPassDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getQuestionResultsDataProvider
     */
    public function testGetQuestionResults(\Closure $IO): void
    {
        $IO = $IO($this);
        $ilTestPassResult = new AttemptResult(
            0,
            0,
            $IO
        );
        $this->assertEquals($IO, $ilTestPassResult->getQuestionResults());
    }

    public static function getQuestionResultsDataProvider(): array
    {
        return [
            [static fn(self $test_case): array => []],
            [static fn(self $test_case): array => [
                $test_case->createMock(QuestionResult::class)
            ]],
            [static fn(self $test_case): array => [
                $test_case->createMock(QuestionResult::class),
                $test_case->createMock(QuestionResult::class),
            ]]
        ];

    }
}
