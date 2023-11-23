<?php

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

    public function getSettingsDataProvider(): array
    {
        return [
            [new ilTestPassResultsSettings()],
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

    public function getActiveIdDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
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

    public function getPassDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getQuestionResultsDataProvider
     */
    public function testGetQuestionResults(array $IO): void
    {
        $ilTestPassResult = new ilTestPassResult(
            new ilTestPassResultsSettings(),
            0,
            0,
            $IO
        );
        $this->assertEquals($IO, $ilTestPassResult->getQuestionResults());
    }

    public function getQuestionResultsDataProvider(): array
    {
        return [
            [[]],
            [[$this->createMock(ilQuestionResult::class)]],
            [[$this->createMock(ilQuestionResult::class), $this->createMock(ilQuestionResult::class)]],
        ];
    }
}