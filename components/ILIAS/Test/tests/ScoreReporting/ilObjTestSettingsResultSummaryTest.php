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

use ilObjTestSettingsResultSummary;
use ilTestBaseTestCase;

class ilObjTestSettingsResultSummaryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $this->assertInstanceOf(ilObjTestSettingsResultSummary::class, $ilObjTestSettingsResultSummary);
    }

    /**
     * @dataProvider getAndWithScoreReportingDataProvider
     */
    public function testGetAndWithScoreReporting(int $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withScoreReporting($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getScoreReporting());
    }

    public function getAndWithScoreReportingDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getScoreReportingEnabledDataProvider
     */
    public function testGetScoreReportingEnabled(bool $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withScoreReporting($IO ? 1 : 0);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getScoreReportingEnabled());
    }

    public function getScoreReportingEnabledDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithReportingDateDataProvider
     */
    public function testGetAndWithReportingDate(?\DateTimeImmutable $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withReportingDate($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getReportingDate());
    }

    public function getAndWithReportingDateDataProvider(): array
    {
        return [
            [null],
            [new \DateTimeImmutable()],
        ];
    }

    /**
     * @dataProvider getAndWithShowGradingStatusEnabledDataProvider
     */
    public function testGetAndWithShowGradingStatusEnabled(bool $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withShowGradingStatusEnabled($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getShowGradingStatusEnabled());
    }

    public function getAndWithShowGradingStatusEnabledDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowGradingMarkEnabledDataProvider
     */
    public function testGetAndWithShowGradingMarkEnabled(bool $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withShowGradingMarkEnabled($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getShowGradingMarkEnabled());
    }

    public function getAndWithShowGradingMarkEnabledDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithPassDeletionAllowedDataProvider
     */
    public function testGetAndWithPassDeletionAllowed(bool $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withPassDeletionAllowed($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getPassDeletionAllowed());
    }

    public function getAndWithPassDeletionAllowedDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getAndWithShowPassDetailsDataProvider
     */
    public function testGetAndWithShowPassDetails(bool $IO): void
    {
        $ilObjTestSettingsResultSummary = new ilObjTestSettingsResultSummary(0);
        $ilObjTestSettingsResultSummary = $ilObjTestSettingsResultSummary->withShowPassDetails($IO);
        $this->assertEquals($IO, $ilObjTestSettingsResultSummary->getShowPassDetails());
    }

    public function getAndWithShowPassDetailsDataProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }
}