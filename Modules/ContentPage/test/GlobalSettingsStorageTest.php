<?php declare(strict_types=1);

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

namespace ILIAS\ContentPage;

use ILIAS\ContentPage\GlobalSettings\Settings;
use ILIAS\ContentPage\GlobalSettings\StorageImpl;
use PHPUnit\Framework\TestCase;
use ilSetting;

class GlobalSettingsStorageTest extends TestCase
{
    public function testGlobalSettings() : void
    {
        $settings = new Settings();

        $this->assertFalse($settings->isReadingTimeEnabled());

        $settingsWithEnabledReadingTime = $settings->withEnabledReadingTime();
        $this->assertFalse($settings->isReadingTimeEnabled());
        $this->assertTrue($settingsWithEnabledReadingTime->isReadingTimeEnabled());

        $settingsWithDisabledReadingTime = $settingsWithEnabledReadingTime->withDisabledReadingTime();
        $this->assertTrue($settingsWithEnabledReadingTime->isReadingTimeEnabled());
        $this->assertFalse($settingsWithDisabledReadingTime->isReadingTimeEnabled());
    }

    public function testGlobalSettingsStorage() : void
    {
        $iliasSettings = new class() extends ilSetting {
            /** @var array<string, string> */
            private array $map = [];

            public function __construct()
            {
            }

            public function get(string $a_keyword, ?string $a_default_value = null) : ?string
            {
                return $this->map[$a_keyword] ?? null;
            }

            public function set(string $a_key, string $a_val) : void
            {
                $this->map[$a_key] = $a_val;
            }
        };

        $storage = new StorageImpl($iliasSettings);

        $settings = new Settings();
        $settings = $settings->withEnabledReadingTime();

        $storage->store($settings);

        $retrievedSettings = $storage->getSettings();
        $this->assertTrue($settings->isReadingTimeEnabled());

        $storage->store($settings->withDisabledReadingTime());
        $this->assertFalse($storage->getSettings()->isReadingTimeEnabled());
    }
}
