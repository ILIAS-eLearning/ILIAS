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

use ILIAS\Administration\MemorySetting;
use ILIAS\Dashboard\Access\DashboardAccess;
use PHPUnit\Framework\TestCase;

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
            DashboardAccess::class,
            [
            ]
        );

        $memory_settings = new MemorySetting();
        $memory_settings->clear();
        $this->view_settings = new ilPDSelectedItemsBlockViewSettings(
            $user,
            ilPDSelectedItemsBlockConstants::VIEW_SELECTED_ITEMS,
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
        $this->assertTrue($settings->enabledMemberships());
    }

    public function testDisableMemberships()
    {
        $settings = $this->view_settings;
        $settings->enableMemberships(false);
        $this->assertFalse($settings->enabledMemberships());
    }

    public function testSelectedItemsEnabledPerDefault()
    {
        $settings = $this->view_settings;
        $this->assertTrue($settings->enabledSelectedItems());
    }

    public function testDisableSelectedItems()
    {
        $settings = $this->view_settings;
        $settings->enableSelectedItems(false);
        $this->assertFalse($settings->enabledSelectedItems());
    }
}
