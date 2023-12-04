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

class ilObjTestSettingsAdditionalTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getSkillsServiceEnabledDataProvider
     */
    public function testGetAndWithSkillsServiceEnabled(bool $IO): void
    {
        $ilObjTestSettingsAdditional = new ilObjTestSettingsAdditional(0);
        $ilObjTestSettingsAdditional = $ilObjTestSettingsAdditional->withSkillsServiceEnabled($IO);

        $this->assertInstanceOf(ilObjTestSettingsAdditional::class, $ilObjTestSettingsAdditional);
        $this->assertEquals($IO, $ilObjTestSettingsAdditional->getSkillsServiceEnabled());
    }

    public function getSkillsServiceEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getHideInfoTabDataProvider
     */
    public function testGetAndWithHideInfoTab(bool $IO): void
    {
        $ilObjTestSettingsAdditional = new ilObjTestSettingsAdditional(0);
        $ilObjTestSettingsAdditional = $ilObjTestSettingsAdditional->withHideInfoTab($IO);

        $this->assertInstanceOf(ilObjTestSettingsAdditional::class, $ilObjTestSettingsAdditional);
        $this->assertEquals($IO, $ilObjTestSettingsAdditional->getHideInfoTab());
    }

    public function getHideInfoTabDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}