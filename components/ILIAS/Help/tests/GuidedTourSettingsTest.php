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

use PHPUnit\Framework\TestCase;
use ILIAS\Help\GuidedTour\Settings\PermissionType;
use ILIAS\Help\GuidedTour\InternalDataService;

class GuidedTourSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    public function testSettings(): void
    {
        $data = new InternalDataService();
        $settings = $data->settings(
            14,
            true,
            "screen_ids",
            PermissionType::Read,
            "en"
        );

        $this->assertEquals(
            14,
            $settings->getObjId()
        );

        $this->assertEquals(
            true,
            $settings->isActive()
        );

        $this->assertEquals(
            "screen_ids",
            $settings->getScreenIds()
        );

        $this->assertEquals(
            PermissionType::Read,
            $settings->getPermission()
        );

        $this->assertEquals(
            "en",
            $settings->getLanguage()
        );
    }
}
