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

namespace ILIAS\Chatroom\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class AllowOnScreenChatConversations implements SettingDefinition
{
    private const string KEY_ENABLE_BROWSER_NOTIFICATIONS = 'chat_osc_browser_notifications';

    public function getIdentifier(): string
    {
        return 'chat_osc_accept_msg';
    }

    public function isAvailable(): bool
    {
        return (new \ilSetting('chatroom'))->get('enable_osc', '0') === '1';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('chat_osc_accept_msg');
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
        $chat_settings = new \ilSetting('chatroom');
        $lng->loadLanguageModule('chatroom_adm');

        if ($chat_settings->get('enable_browser_notifications', '0') !== '1') {
            return $field_factory->checkbox(
                $lng->txt('chat_osc_accept_msg'),
                $lng->txt('chat_osc_accept_msg_info')
            )->withValue(
                $user !== null
                    ? $user->getPref('chat_osc_accept_msg') === 'y'
                    : $settings->get('chat_osc_accept_msg') === 'y'
            );
        }

        return $field_factory->optionalGroup(
            [
                self::KEY_ENABLE_BROWSER_NOTIFICATIONS => $field_factory->checkbox(
                    $lng->txt('osc_enable_browser_notifications_label'),
                    \sprintf(
                        $lng->txt('osc_enable_browser_notifications_info'),
                        (int) $chat_settings->get('conversation_idle_state_in_minutes')
                    )
                )->withDisabled(
                    $settings->get('usr_settings_disable_chat_osc_accept_msg', '0') === '1'
                )
            ],
            $lng->txt('chat_osc_accept_msg'),
            $lng->txt('chat_osc_accept_msg_info')
        )->withValue(
            $this->buildValueForNotificationGroup($settings, $user)
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('chatroom');

        $input = new \ilCheckboxInputGUI($lng->txt('chat_osc_accept_msg'));
        $input->setChecked(
            $user !== null
                ? $user->getPref('chat_osc_accept_msg') === 'y'
                : $settings->get('chat_osc_accept_msg') === 'y'
        );

        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $settings->get($this->getIdentifier()) === 'y'
            ? $lng->txt('chat_osc_accepts_messages_yes')
            : $lng->txt('chat_no_use_typing_broadcast');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return $this->retrieveValueFromUser($user) !== $settings->get($this->getIdentifier());
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        if ($input === null) {
            $user->deletePref($this->getIdentifier());
            $user->deletePref(self::KEY_ENABLE_BROWSER_NOTIFICATIONS);

            return $user;
        }

        if (\is_bool($input)) {
            $user->setPref($this->getIdentifier(), (string) $input);

            return $user;
        }

        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): ?bool
    {
        $value = $user->getPref($this->getIdentifier());

        return $value !== null ? $value === 'y' : null;
    }

    private function buildValueForNotificationGroup(
        \ilSetting $settings,
        ?\ilObjUser $user
    ): ?array {
        $active = $settings->get($this->getIdentifier()) === 'y';
        $notification = false;
        if ($user !== null) {
            $active = $user->getPref($this->getIdentifier()) === 'y';
            $notification = $user->getPref(self::KEY_ENABLE_BROWSER_NOTIFICATIONS) === 'y';
        }

        if ($active === false) {
            return null;
        }

        return [
            self::KEY_ENABLE_BROWSER_NOTIFICATIONS => $notification
        ];
    }
}
