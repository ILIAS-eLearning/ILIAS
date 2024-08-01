<?php

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
