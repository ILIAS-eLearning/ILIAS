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

use ILIAS\Test\Settings\MainSettings\SettingsAdditional;

class SettingsAdditionalTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getSkillsServiceEnabledDataProvider
     */
    public function testGetAndWithSkillsServiceEnabled(bool $io): void
    {
        $settings_additional = (new SettingsAdditional(0))->withSkillsServiceEnabled($io);

        $this->assertInstanceOf(SettingsAdditional::class, $settings_additional);
        $this->assertEquals($io, $settings_additional->getSkillsServiceEnabled());
    }

    public static function getSkillsServiceEnabledDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getHideInfoTabDataProvider
     */
    public function testGetAndWithHideInfoTab(bool $io): void
    {
        $settings_additional = (new SettingsAdditional(0))->withHideInfoTab($io);

        $this->assertInstanceOf(SettingsAdditional::class, $settings_additional);
        $this->assertEquals($io, $settings_additional->getHideInfoTab());
    }

    public static function getHideInfoTabDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
