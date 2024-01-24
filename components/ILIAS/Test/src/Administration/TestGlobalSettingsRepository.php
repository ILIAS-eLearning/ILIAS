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

namespace ILIAS\Test\Administration;

class TestGlobalSettingsRepository
{
    private const SETTINGS_KEY_LOGGING_ENABLED = 'assessment_logging';

    public function __construct(
        private \ilSetting $settings
    ) {
    }

    public function getLoggingSettings(): TestLoggingSettings
    {
        return new TestLoggingSettings(
            $this->settings->get(self::SETTINGS_KEY_LOGGING_ENABLED) === '1'
        );
    }

    public function storeLoggingSettings(TestLoggingSettings $logging_settings): void
    {
        $this->settings->set(self::SETTINGS_KEY_LOGGING_ENABLED, $logging_settings->isLoggingEnabled() ? '1' : '0');
    }
}
