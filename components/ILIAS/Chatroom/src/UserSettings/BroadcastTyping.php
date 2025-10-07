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

class BroadcastTyping implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'chat_broadcast_typing';
    }

    public function isAvailable(): bool
    {
        return (new \ilSetting('chatroom'))->get('chat_enabled', '0') === '1';
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
        $lng->loadLanguageModule('chatroom_adm');

        return $field_factory->checkbox(
            $lng->txt('chat_broadcast_typing'),
            $lng->txt('chat_broadcast_typing_info')
        )->withValue(
            $user !== null
                ? $user->getPref('chat_broadcast_typing') === 'y'
                : $settings->get('chat_broadcast_typing') === 'y'
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('chatroom_adm');

        $input = new \ilCheckboxInputGUI($lng->txt('chat_broadcast_typing'));
        $input->setInfo($lng->txt('chat_broadcast_typing_info'));
        $input->setChecked(
            $user !== null
                ? $user->getPref('chat_broadcast_typing') === 'y'
                : $settings->get('chat_broadcast_typing') === 'y'
        );

        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $settings->get($this->getIdentifier()) === 'y'
            ? $lng->txt('chat_use_typing_broadcast')
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

            return $user;
        }

        $user->setPref($this->getIdentifier(), (string) $input);

        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): ?bool
    {
        $value = $user->getPref($this->getIdentifier());

        return $value !== null ? $value === 'y' : null;
    }
}
