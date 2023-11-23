<?php

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

    public function testToForm(): void
    {
        $this->markTestSkipped();
    }

    public function testToStorage(): void
    {
        $this->markTestSkipped();
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