<?php

use ILIAS\Test\Administration\TestGlobalSettingsRepository;
use ILIAS\Test\Administration\TestLoggingSettings;

class TestGlobalSettingsRepositoryTest extends ilTestBaseTestCase
{
    private TestGlobalSettingsRepository $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilTabs();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_rbacreview();

        $this->testObj = new TestGlobalSettingsRepository(new ilSetting("assessment"));
    }

    /**
     * @dataProvider provideLoggingSettings
     */
    public function test_get_and_storeLoggingSettings($testLoggingSettings): void
    {
        $this->testObj->storeLoggingSettings($testLoggingSettings);
        $settings = $this->testObj->getLoggingSettings();
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
