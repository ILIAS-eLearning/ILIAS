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

namespace ScoreReporting;

use ILIAS\Test\Settings\ScoreReporting\SettingsResultDetails;
use ilTestBaseTestCase;

class SettingsResultDetailsTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $this->assertInstanceOf(SettingsResultDetails::class, $settingsResultDetails);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithResultsPresentationDataProvider')]
    public function testGetAndWithResultsPresentation(int $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withResultsPresentation($IO);
        $this->assertEquals($IO, $settingsResultDetails->getResultsPresentation());
    }

    public static function getAndWithResultsPresentationDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndShowExamIdInTestResultsDataProvider')]
    public function testGetAndShowExamIdInTestResults(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowExamIdInTestResults($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowExamIdInTestResults());
    }

    public static function getAndShowExamIdInTestResultsDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowPassDetailsDataProvider')]
    public function testGetAndWithShowPassDetails(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowPassDetails($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowPassDetails());
    }

    public static function getAndWithShowPassDetailsDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowSolutionPrintviewDataProvider')]
    public function testGetAndWithShowSolutionPrintview(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowSolutionPrintview($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowSolutionPrintview());
    }

    public static function getAndWithShowSolutionPrintviewDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowSolutionFeedbackDataProvider')]
    public function testGetShowSolutionFeedback(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowSolutionFeedback($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowSolutionFeedback());
    }

    public static function getAndWithShowSolutionFeedbackDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowSolutionAnswersOnlyDataProvider')]
    public function testGetAndWithShowSolutionAnswersOnly(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowSolutionAnswersOnly($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowSolutionAnswersOnly());
    }

    public static function getAndWithShowSolutionAnswersOnlyDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowSolutionSignatureDataProvider')]
    public function testGetAndWithShowSolutionSignature(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowSolutionSignature($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowSolutionSignature());
    }

    public static function getAndWithShowSolutionSignatureDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowSolutionSuggestedDataProvider')]
    public function testGetAndWithShowSolutionSuggested(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowSolutionSuggested($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowSolutionSuggested());
    }

    public static function getAndWithShowSolutionSuggestedDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithShowSolutionListComparisonDataProvider')]
    public function testGetAndWithShowSolutionListComparison(bool $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withShowSolutionListComparison($IO);
        $this->assertEquals($IO, $settingsResultDetails->getShowSolutionListComparison());
    }

    public static function getAndWithShowSolutionListComparisonDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAndWithExportSettingsDataProvider')]
    public function testGetAndWithExportSettings(int $IO): void
    {
        $settingsResultDetails = new SettingsResultDetails(0);
        $settingsResultDetails = $settingsResultDetails->withExportSettings($IO);
        $this->assertEquals($IO, $settingsResultDetails->getExportSettings());
    }

    public static function getAndWithExportSettingsDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }
}
