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

declare(strict_types=1);

class ilObjTestSettingsIntroductionTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithIntroductionEnabledDataProvider
     */
    public function testGetAndWithIntroductionEnabled(bool $IO): void
    {
        $ilObjTestSettingsIntroduction = (new ilObjTestSettingsIntroduction(0));
        $ilObjTestSettingsIntroduction = $ilObjTestSettingsIntroduction->withIntroductionEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsIntroduction::class, $ilObjTestSettingsIntroduction);
        $this->assertEquals($IO, $ilObjTestSettingsIntroduction->getIntroductionEnabled());
    }

    public function getAndWithIntroductionEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getAndWithIntroductionTextDataProvider
     */
    public function testGetAndWithIntroductionText(string $IO): void
    {
        $ilObjTestSettingsIntroduction = (new ilObjTestSettingsIntroduction(0));
        $ilObjTestSettingsIntroduction = $ilObjTestSettingsIntroduction->withIntroductionText($IO);

        $this->assertInstanceOf(ilObjTestSettingsIntroduction::class, $ilObjTestSettingsIntroduction);
        $this->assertEquals($IO, $ilObjTestSettingsIntroduction->getIntroductionText());
    }

    public function getAndWithIntroductionTextDataProvider(): array
    {
        return [
            [''],
            ['string']
        ];
    }

    /**
     * @dataProvider getAndWithIntroductionPageIdDataProvider
     */
    public function testGetAndWithIntroductionPageId(?int $IO): void
    {
        $ilObjTestSettingsIntroduction = (new ilObjTestSettingsIntroduction(0));
        $ilObjTestSettingsIntroduction = $ilObjTestSettingsIntroduction->withIntroductionPageId($IO);

        $this->assertInstanceOf(ilObjTestSettingsIntroduction::class, $ilObjTestSettingsIntroduction);
        $this->assertEquals($IO, $ilObjTestSettingsIntroduction->getIntroductionPageId());
    }

    public function getAndWithIntroductionPageIdDataProvider(): array
    {
        return [
            [null],
            [-1],
            [0],
            [1]
        ];
    }

    /**
     * @dataProvider getAndWithExamConditionsCheckboxEnabledDataProvider
     */
    public function testGetAndWithExamConditionsCheckboxEnabled(bool $IO): void
    {
        $ilObjTestSettingsIntroduction = (new ilObjTestSettingsIntroduction(0));
        $ilObjTestSettingsIntroduction = $ilObjTestSettingsIntroduction->withExamConditionsCheckboxEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsIntroduction::class, $ilObjTestSettingsIntroduction);
        $this->assertEquals($IO, $ilObjTestSettingsIntroduction->getExamConditionsCheckboxEnabled());
    }

    public function getAndWithExamConditionsCheckboxEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}