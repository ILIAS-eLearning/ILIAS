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

namespace Results;

use ilQuestionResult;
use ilTestBaseTestCase;
use ilTestPassResult;
use ilTestPassResultsSettings;

class ilTestPassResultTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getSettingsDataProvider
     */
    public function testGetSettings(ilTestPassResultsSettings $IO): void
    {
        $ilTestPassResult = new ilTestPassResult(
            $IO,
            0,
            0,
            []
        );
        $this->assertEquals($IO, $ilTestPassResult->getSettings());
    }

    public static function getSettingsDataProvider(): array
    {
        return [
            [new ilTestPassResultsSettings()]
        ];
    }

    /**
     * @dataProvider getActiveIdDataProvider
     */
    public function testGetActiveId(int $IO): void
    {
        $ilTestPassResult = new ilTestPassResult(
            new ilTestPassResultsSettings(),
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
    public function testGetPass(int $IO): void
    {
        $ilTestPassResult = new ilTestPassResult(
            new ilTestPassResultsSettings(),
            0,
            $IO,
            []
        );
        $this->assertEquals($IO, $ilTestPassResult->getPass());
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
        $ilTestPassResult = new ilTestPassResult(
            new ilTestPassResultsSettings(),
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
                $test_case->createMock(ilQuestionResult::class)
            ]],
            [static fn(self $test_case): array => [
                $test_case->createMock(ilQuestionResult::class),
                $test_case->createMock(ilQuestionResult::class),
            ]]
        ];

    }
}
