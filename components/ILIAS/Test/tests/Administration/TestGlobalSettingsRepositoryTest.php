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

use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Administration\TestLoggingSettings;

class TestGlobalSettingsRepositoryTest extends ilTestBaseTestCase
{
    private TestGlobalSettingsRepository $testGlobalSettingsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testGlobalSettingsRepository = new TestGlobalSettingsRepository(new ilSetting("assessment"));
    }

    /**
     * @dataProvider provideLoggingSettings
     */
    public function test_get_and_storeLoggingSettings($testLoggingSettings): void
    {
        $this->testGlobalSettingsRepository->storeLoggingSettings($testLoggingSettings);
        $settings = $this->testGlobalSettingsRepository->getLoggingSettings();
        $this->assertEquals($testLoggingSettings->isLoggingEnabled(), $settings->isLoggingEnabled());
        $this->assertEquals($testLoggingSettings->isIPLoggingEnabled(), $settings->isIPLoggingEnabled());
    }

    public static function provideLoggingSettings(): array
    {
        return [
            "dataset 1: both enabled" => [
                "testLoggingSettings" => new TestLoggingSettings(true, true)
            ],
            "dataset 2: only logging enabled " => [
                "testLoggingSettings" => new TestLoggingSettings(true, false)
            ],
            "dataset 3: only ip logging enabled" => [
                "testLoggingSettings" => new TestLoggingSettings(false, true)
            ],
            "dataset 4: both disabled" => [
                "testLoggingSettings" => new TestLoggingSettings(false, false)
            ]
        ];
    }
}
