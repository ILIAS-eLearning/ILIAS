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

namespace ILIAS\Awareness\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class ShowStatusInWhoIsOnline implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'awrn_user_show';
    }

    public function isAvailable(): bool
    {
        return (new \ilSetting('awrn'))->get('awrn_enabled', '0') === '1';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::PrivacySettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Main;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $default = $settings->get('hide_own_online_status') === 'n'
            ? $lng->txt('user_awrn_show')
            : $lng->txt('user_awrn_hide');

        return $field_factory->select(
            $lng->txt('awrn_user_show'),
            [
                    'x' => "{$lng->txt('user_awrn_default')} ({$default})",
                    'n' => $lng->txt('user_awrn_show'),
                    'y' => $lng->txt('user_awrn_hide')
                ],
            $lng->txt('awrn_hide_from_awareness_info')
        )->withRequired(true)
            ->withValue($this->buildSetterValue($settings, $user));
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('awrn');

        $default = ($settings->get('hide_own_online_status') === 'n')
            ? $lng->txt('user_awrn_show')
            : $lng->txt('user_awrn_hide');

        $input = new \ilSelectInputGUI($lng->txt('awrn_user_show'), 'hide_own_online_status');
        $input->setOptions(
            [
                '' => $lng->txt('user_awrn_default') . ' (' . $default . ')',
                'n' => $lng->txt('user_awrn_show'),
                'y' => $lng->txt('user_awrn_hide')
            ]
        );
        $input->setDisabled((bool) $settings->get('usr_settings_disable_hide_own_online_status'));
        $input->setInfo($lng->txt('awrn_hide_from_awareness_info'));
        $input->setValue(
            $this->buildSetterValue($settings, $user)
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $settings->get('hide_own_online_status') === 'n'
            ? $lng->txt('user_awrn_show')
            : $lng->txt('user_awrn_hide');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        $user_value = $this->retrieveValueFromUser($user);
        $settings_value = $settings->get('hide_own_online_status') === 'n';
        return $user_value !== null && $user_value !== $settings_value;
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        if ($input === null) {
            $user->deletePref('hide_own_online_status');
            return $user;
        }
        $user->setPref('hide_own_online_status', $input);
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): ?bool
    {
        $value = $user->getPref('hide_own_online_status');
        return !empty($value) ? $value === 'n' : null;
    }

    private function buildSetterValue(
        \ilSetting $settings,
        ?\ilObjUser $user
    ): string {
        if ($user === null) {
            return $settings->get('hide_own_online_status', 'x');
        }

        $user_value = $user->getPref('hide_own_online_status');
        if (!empty($user_value)) {
            return $user_value;
        }

        return 'x';
    }
}
