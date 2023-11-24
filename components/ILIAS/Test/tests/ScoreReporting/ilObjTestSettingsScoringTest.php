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

use ilObjTestSettingsScoring;
use ilTestBaseTestCase;

class ilObjTestSettingsScoringTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilObjTestSettingsScoring = new ilObjTestSettingsScoring(0);
        $this->assertInstanceOf(ilObjTestSettingsScoring::class, $ilObjTestSettingsScoring);
    }

    /**
     * @dataProvider getAndWithCountSystemDataProvider
     */
    public function testGetAndWithCountSystem(bool $IO): void
    {
        $ilObjTestSettingsScoring = new ilObjTestSettingsScoring(0);
        $ilObjTestSettingsScoring = $ilObjTestSettingsScoring->withCountSystem($IO);
        $this->assertEquals($IO, $ilObjTestSettingsScoring->getCountSystem());
    }

    public function getAndWithCountSystemDataProvider(): array
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
        $ilObjTestSettingsScoring = new ilObjTestSettingsScoring(0);
        $ilObjTestSettingsScoring = $ilObjTestSettingsScoring->withScoreCutting($IO);
        $this->assertEquals($IO, $ilObjTestSettingsScoring->getScoreCutting());
    }

    public function getAndWithScoreCuttingDataProvider(): array
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
        $ilObjTestSettingsScoring = new ilObjTestSettingsScoring(0);
        $ilObjTestSettingsScoring = $ilObjTestSettingsScoring->withPassScoring($IO);
        $this->assertEquals($IO, $ilObjTestSettingsScoring->getPassScoring());
    }

    public function getAndWithPassScoringDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}