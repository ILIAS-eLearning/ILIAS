<?php

namespace ScoreReporting;

use ilObjTestSettingsResultDetails;
use ilTestBaseTestCase;

class ilObjTestSettingsResultDetailsTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $this->assertInstanceOf(ilObjTestSettingsResultDetails::class, $ilObjTestSettingsResultDetails);
    }

    public function testToForm(): void
    {
        $this->markTestSkipped();
    }

    public function testToStorage(): void
    {
        $this->markTestSkipped();
    }

    /**
     * @dataProvider getAndWithResultsPresentationDataProvider
     */
    public function testGetAndWithResultsPresentation(int $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withResultsPresentation($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getResultsPresentation());
    }

    public function getAndWithResultsPresentationDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndShowExamIdInTestResultsDataProvider
     */
    public function testGetAndShowExamIdInTestResults(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowExamIdInTestResults($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowExamIdInTestResults());
    }

    public function getAndShowExamIdInTestResultsDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    public function testCompareResultPresentation(): void
    {
        $this->markTestSkipped();
    }

    public function testModifyResultPresentation(): void
    {
        $this->markTestSkipped();
    }

    /**
     * @dataProvider getAndWithShowPassDetailsDataProvider
     */
    public function testGetAndWithShowPassDetails(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowPassDetails($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowPassDetails());
    }

    public function getAndWithShowPassDetailsDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowSolutionPrintviewDataProvider
     */
    public function testGetAndWithShowSolutionPrintview(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowSolutionPrintview($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowSolutionPrintview());
    }

    public function getAndWithShowSolutionPrintviewDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowSolutionFeedbackDataProvider
     */
    public function testGetShowSolutionFeedback(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowSolutionFeedback($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowSolutionFeedback());
    }

    public function getAndWithShowSolutionFeedbackDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowSolutionAnswersOnlyDataProvider
     */
    public function testGetAndWithShowSolutionAnswersOnly(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowSolutionAnswersOnly($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowSolutionAnswersOnly());
    }

    public function getAndWithShowSolutionAnswersOnlyDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowSolutionSignatureDataProvider
     */
    public function testGetAndWithShowSolutionSignature(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowSolutionSignature($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowSolutionSignature());
    }

    public function getAndWithShowSolutionSignatureDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowSolutionSuggestedDataProvider
     */
    public function testGetAndWithShowSolutionSuggested(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowSolutionSuggested($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowSolutionSuggested());
    }

    public function getAndWithShowSolutionSuggestedDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowSolutionListComparisonDataProvider
     */
    public function testGetAndWithShowSolutionListComparison(bool $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withShowSolutionListComparison($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getShowSolutionListComparison());
    }

    public function getAndWithShowSolutionListComparisonDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithExportSettingsDataProvider
     */
    public function testGetAndWithExportSettings(int $IO): void
    {
        $ilObjTestSettingsResultDetails = new ilObjTestSettingsResultDetails(0);
        $ilObjTestSettingsResultDetails = $ilObjTestSettingsResultDetails->withExportSettings($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultDetails->getExportSettings());
    }

    public function getAndWithExportSettingsDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    public function testCompareExportSetting(): void
    {
        $this->markTestSkipped();
    }

    public function testModifyExportSetting(): void
    {
        $this->markTestSkipped();
    }
}