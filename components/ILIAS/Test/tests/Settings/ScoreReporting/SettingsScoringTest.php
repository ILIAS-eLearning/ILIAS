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

use ILIAS\Test\Scoring\Settings\Settings as SettingsScoring;
use ilTestBaseTestCase;

class SettingsScoringTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $settingsScoring = new SettingsScoring(0);
        $this->assertInstanceOf(SettingsScoring::class, $settingsScoring);
    }

    /**
     * @dataProvider getAndWithCountSystemDataProvider
     */
    public function testGetAndWithCountSystem(bool $IO): void
    {
        $settingsScoring = new SettingsScoring(0);
        $settingsScoring = $settingsScoring->withCountSystem($IO);
        $this->assertEquals($IO, $settingsScoring->getCountSystem());
    }

    public static function getAndWithCountSystemDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithScoreCuttingDataProvider
     */
    public function testGetAndWithScoreCutting(bool $IO): void
    {
        $settingsScoring = new SettingsScoring(0);
        $settingsScoring = $settingsScoring->withScoreCutting($IO);
        $this->assertEquals($IO, $settingsScoring->getScoreCutting());
    }

    public static function getAndWithScoreCuttingDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithPassScoringDataProvider
     */
    public function testGetAndWithPassScoring(bool $IO): void
    {
        $settingsScoring = new SettingsScoring(0);
        $settingsScoring = $settingsScoring->withPassScoring($IO);
        $this->assertEquals($IO, $settingsScoring->getPassScoring());
    }

    public static function getAndWithPassScoringDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
