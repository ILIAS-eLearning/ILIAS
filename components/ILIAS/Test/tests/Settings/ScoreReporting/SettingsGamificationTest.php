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

use ILIAS\Test\Settings\ScoreReporting\SettingsGamification;
use ilTestBaseTestCase;

class SettingsGamificationTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $gamificationTest = new SettingsGamification(0);
        $this->assertInstanceOf(SettingsGamification::class, $gamificationTest);
    }

    /**
     * @dataProvider getAndWithHighscoreEnabledDataProvider
     */
    public function testGetAndWithHighscoreEnabled(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreEnabled($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreEnabled());
    }

    public static function getAndWithHighscoreEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreOwnTableDataProvider
     */
    public function testGetAndWithHighscoreOwnTable(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreOwnTable($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreOwnTable());
    }

    public static function getAndWithHighscoreOwnTableDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreTopTableDataProvider
     */
    public function testGetAndWithHighscoreTopTable(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreTopTable($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreTopTable());
    }

    public static function getAndWithHighscoreTopTableDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreTopNumDataProvider
     */
    public function testGetAndWithHighscoreTopNum(int $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreTopNum($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreTopNum());
    }

    public static function getAndWithHighscoreTopNumDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreAnonDataProvider
     */
    public function testGetAndWithHighscoreAnon(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreAnon($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreAnon());
    }

    public static function getAndWithHighscoreAnonDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreAchievedTSDataProvider
     */
    public function testGetAndWithHighscoreAchievedTS(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreAchievedTS($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreAchievedTS());
    }

    public static function getAndWithHighscoreAchievedTSDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreScoreDataProvider
     */
    public function testGetAndWithHighscoreScore(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreScore($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreScore());
    }

    public static function getAndWithHighscoreScoreDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscorePercentageDataProvider
     */
    public function testGetAndWithHighscorePercentage(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscorePercentage($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscorePercentage());
    }

    public static function getAndWithHighscorePercentageDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreHintsDataProvider
     */
    public function testGetAndWithHighscoreHints(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreHints($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreHints());
    }

    public static function getAndWithHighscoreHintsDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreWTimeDataProvider
     */
    public function testGetAndWithHighscoreWTime(bool $IO): void
    {
        $gamificationTest = new SettingsGamification(0);
        $gamificationTest = $gamificationTest->withHighscoreWTime($IO);
        $this->assertEquals($IO, $gamificationTest->getHighscoreWTime());
    }

    public static function getAndWithHighscoreWTimeDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
