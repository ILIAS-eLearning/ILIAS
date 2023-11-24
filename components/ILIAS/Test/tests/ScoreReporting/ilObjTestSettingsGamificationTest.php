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

use ilObjTestSettingsGamification;
use ilTestBaseTestCase;

class ilObjTestSettingsGamificationTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $this->assertInstanceOf(ilObjTestSettingsGamification::class, $ilObjTestSettingsGamification);
    }

    /**
     * @dataProvider getAndWithHighscoreEnabledDataProvider
     */
    public function testGetAndWithHighscoreEnabled(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreEnabled($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreEnabled());
    }

    public function getAndWithHighscoreEnabledDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreOwnTableDataProvider
     */
    public function testGetAndWithHighscoreOwnTable(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreOwnTable($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreOwnTable());
    }

    public function getAndWithHighscoreOwnTableDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreTopTableDataProvider
     */
    public function testGetAndWithHighscoreTopTable(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreTopTable($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreTopTable());
    }

    public function getAndWithHighscoreTopTableDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreTopNumDataProvider
     */
    public function testGetAndWithHighscoreTopNum(int $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreTopNum($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreTopNum());
    }

    public function getAndWithHighscoreTopNumDataProvider(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreAnonDataProvider
     */
    public function testGetAndWithHighscoreAnon(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreAnon($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreAnon());
    }

    public function getAndWithHighscoreAnonDataProvider(): array
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
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreAchievedTS($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreAchievedTS());
    }

    public function getAndWithHighscoreAchievedTSDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreScoreDataProvider
     */
    public function testGetAndWithHighscoreScore(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreScore($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreScore());
    }

    public function getAndWithHighscoreScoreDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscorePercentageDataProvider
     */
    public function testGetAndWithHighscorePercentage(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscorePercentage($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscorePercentage());
    }

    public function getAndWithHighscorePercentageDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreHintsDataProvider
     */
    public function testGetAndWithHighscoreHints(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreHints($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreHints());
    }

    public function getAndWithHighscoreHintsDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider getAndWithHighscoreWTimeDataProvider
     */
    public function testGetAndWithHighscoreWTime(bool $IO): void
    {
        $ilObjTestSettingsGamification = new ilObjTestSettingsGamification(0);
        $ilObjTestSettingsGamification = $ilObjTestSettingsGamification->withHighscoreWTime($IO);
        $this->assertEquals($IO, $ilObjTestSettingsGamification->getHighscoreWTime());
    }

    public function getAndWithHighscoreWTimeDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }
}