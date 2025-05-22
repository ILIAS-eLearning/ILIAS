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

use DateTimeImmutable;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultSummary;
use ILIAS\Test\Settings\ScoreReporting\ScoreReportingTypes;
use ilTestBaseTestCase;

class SettingsResultSummaryTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $settingsResultSummary = new SettingsResultSummary(0);
        $this->assertInstanceOf(SettingsResultSummary::class, $settingsResultSummary);
    }

    /**
     * @dataProvider getAndWithScoreReportingDataProvider
     */
    public function testGetAndWithScoreReporting(ScoreReportingTypes $IO): void
    {
        $this->assertEquals(
            $IO,
            (new SettingsResultSummary(0))->withScoreReporting($IO)->getScoreReporting()
        );
    }

    public static function getAndWithScoreReportingDataProvider(): array
    {
        return [
            [ScoreReportingTypes::SCORE_REPORTING_DISABLED],
            [ScoreReportingTypes::SCORE_REPORTING_FINISHED],
            [ScoreReportingTypes::SCORE_REPORTING_AFTER_PASSED]
        ];
    }

    /**
     * @dataProvider getAndWithReportingDateDataProvider
     */
    public function testGetAndWithReportingDate(?\DateTimeImmutable $IO): void
    {
        $settingsResultSummary = new SettingsResultSummary(0);
        $settingsResultSummary = $settingsResultSummary->withReportingDate($IO);
        $this->assertEquals($IO, $settingsResultSummary->getReportingDate());
    }

    public static function getAndWithReportingDateDataProvider(): array
    {
        return [
            [null],
            [new DateTimeImmutable()]
        ];
    }

    /**
     * @dataProvider getAndWithShowGradingStatusEnabledDataProvider
     */
    public function testGetAndWithShowGradingStatusEnabled(bool $IO): void
    {
        $settingsResultSummary = new SettingsResultSummary(0);
        $settingsResultSummary = $settingsResultSummary->withShowGradingStatusEnabled($IO);
        $this->assertEquals($IO, $settingsResultSummary->getShowGradingStatusEnabled());
    }

    public static function getAndWithShowGradingStatusEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithShowGradingMarkEnabledDataProvider
     */
    public function testGetAndWithShowGradingMarkEnabled(bool $IO): void
    {
        $settingsResultSummary = new SettingsResultSummary(0);
        $settingsResultSummary = $settingsResultSummary->withShowGradingMarkEnabled($IO);
        $this->assertEquals($IO, $settingsResultSummary->getShowGradingMarkEnabled());
    }

    public static function getAndWithShowGradingMarkEnabledDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithPassDeletionAllowedDataProvider
     */
    public function testGetAndWithPassDeletionAllowed(bool $IO): void
    {
        $settingsResultSummary = new SettingsResultSummary(0);
        $settingsResultSummary = $settingsResultSummary->withPassDeletionAllowed($IO);
        $this->assertEquals($IO, $settingsResultSummary->getPassDeletionAllowed());
    }

    public static function getAndWithPassDeletionAllowedDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @dataProvider getAndWithShowPassDetailsDataProvider
     */
    public function testGetAndWithShowPassDetails(bool $IO): void
    {
        $settingsResultSummary = new SettingsResultSummary(0);
        $settingsResultSummary = $settingsResultSummary->withShowPassDetails($IO);
        $this->assertEquals($IO, $settingsResultSummary->getShowPassDetails());
    }

    public static function getAndWithShowPassDetailsDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }
}
