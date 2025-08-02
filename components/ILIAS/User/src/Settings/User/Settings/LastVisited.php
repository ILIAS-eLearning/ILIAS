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

namespace ILIAS\User\Settings\User\Settings;

use ILIAS\User\Settings\User\SettingConfiguration;
use ILIAS\User\Settings\User\AvailablePages;
use ILIAS\User\Settings\User\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

class LastVisited implements SettingConfiguration
{
    private readonly ?\ilNavigationHistory $navigation_history;

    public function __construct()
    {
        global $DIC;
        $this->navigation_history = $DIC['ilNavigationHistory'] ?? null;
    }

    public function getIdentifier(): string
    {
        return 'last_visited';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLanguageVariable(): string
    {
        return 'last_visited';
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Additional;
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $input = new \ilSelectInputGUI($lng->txt('user_store_last_visited'));
        $options = [
            0 => $lng->txt('user_lv_keep_entries'),
            1 => $lng->txt('user_lv_keep_only_for_session'),
            2 => $lng->txt('user_lv_do_not_store')
        ];
        $input->setOptions($options);
        $input->setValue(
            (int) ($current_user->prefs['store_last_visited'] ?? '0')
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings
    ): string {
        return $lng->txt('user_lv_keep_entries');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $current_user
    ): bool {
        return ($current_user->prefs['store_last_visited'] ?? '0') !== '0';
    }

    public function storeUserChoice(
        \ilObjUser $current_user,
        mixed $input
    ): void {
        $current_user->setPref('store_last_visited', $input ?? '0');
        if ((int) $input > 0) {
            $this->navigation_history->deleteDBEntries();
            if ($input === '2') {
                $this->navigation_history->deleteSessionEntries();
            }
        }
    }
}
