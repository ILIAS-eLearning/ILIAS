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

namespace ILIAS\Test\Tests\Settings\ScoreReporting;

use ILIAS\Test\Scoring\Settings\Settings;
use ilTestBaseTestCase;

class SettingsTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Settings::class, new Settings(0));
    }

    /**
     * @dataProvider getAndWithCountSystemDataProvider
     */
    public function testGetAndWithCountSystem(int $IO): void
    {
        $settings = new Settings(0);
        $this->assertEquals(0, $settings->getCountSystem());
        $this->assertInstanceOf(Settings::class, $settings = $settings->withCountSystem($IO));
        $this->assertEquals($IO, $settings->getCountSystem());
    }

    public static function getAndWithCountSystemDataProvider(): array
    {
        return [
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [0]
        ];
    }

    /**
     * @dataProvider getAndWithScoreCuttingDataProvider
     */
    public function testGetAndWithScoreCutting(int $IO): void
    {
        $settings = new Settings(0);
        $this->assertEquals(0, $settings->getScoreCutting());
        $this->assertInstanceOf(Settings::class, $settings = $settings->withScoreCutting($IO));
        $this->assertEquals($IO, $settings->getScoreCutting());
    }

    public static function getAndWithScoreCuttingDataProvider(): array
    {
        return [
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [0]
        ];
    }

    /**
     * @dataProvider getAndWithPassScoringDataProvider
     */
    public function testGetAndWithPassScoring(int $IO): void
    {
        $settings = new Settings(0);
        $this->assertEquals(0, $settings->getPassScoring());
        $this->assertInstanceOf(Settings::class, $settings = $settings->withPassScoring($IO));
        $this->assertEquals($IO, $settings->getPassScoring());
    }

    public static function getAndWithPassScoringDataProvider(): array
    {
        return [
            'negative_one' => [-1],
            'zero' => [0],
            'one' => [0]
        ];
    }
}
