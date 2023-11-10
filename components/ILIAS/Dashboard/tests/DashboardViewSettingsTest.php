<?php

use PHPUnit\Framework\TestCase;

/**
 * Test dashboard settings repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DashboardViewSettingsTest extends TestCase
{
    protected ilPDSelectedItemsBlockViewSettings $view_settings;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createConfiguredMock(
            ilObjUser::class,
            [
            ]
        );

        $access = $this->createConfiguredMock(
            \ILIAS\Dashboard\Access\DashboardAccess::class,
            [
            ]
        );

        $memory_settings = new \ILIAS\Administration\MemorySetting();
        $memory_settings->clear();
        $this->view_settings = new ilPDSelectedItemsBlockViewSettings(
            $user,
            ilPDSelectedItemsBlockViewSettings::VIEW_SELECTED_ITEMS,
            $memory_settings,
            $access
        );
    }

    protected function tearDown(): void
    {
    }

    public function testMembershipsEnabledPerDefault()
    {
        $settings = $this->view_settings;
        $this->assertEquals(
            true,
            $settings->enabledMemberships()
        );
    }

    public function testDisableMemberships()
    {
        $settings = $this->view_settings;
        $settings->enableMemberships(false);
        $this->assertEquals(
            false,
            $settings->enabledMemberships()
        );
    }

    public function testSelectedItemsEnabledPerDefault()
    {
        $settings = $this->view_settings;
        $this->assertEquals(
            true,
            $settings->enabledSelectedItems()
        );
    }

    public function testDisableSelectedItems()
    {
        $settings = $this->view_settings;
        $settings->enableSelectedItems(false);
        $this->assertEquals(
            false,
            $settings->enabledSelectedItems()
        );
    }
}
