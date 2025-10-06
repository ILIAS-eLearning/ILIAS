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

namespace ILIAS\Contact\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class AllowContactRequest implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'allow_contact_request';
    }

    public function isAvailable(): bool
    {
        return \ilBuddySystem::getInstance()->isEnabled();
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('buddy_allow_to_contact_me');
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
        $lng->loadLanguageModule('buddysystem');
        return $field_factory->checkbox(
            $lng->txt('buddy_allow_to_contact_me'),
            $lng->txt('buddy_allow_to_contact_me_info')
        )->withValue(
            $user !== null
                ? $user->getPref('bs_allow_to_contact_me') === 'y'
                : $settings->get('bs_allow_to_contact_me') === 'y'
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('buddysystem');
        $input = new \ilCheckboxInputGUI($lng->txt('buddy_allow_to_contact_me'));
        $input->setChecked(
            $user !== null
                ? $user->getPref('bs_allow_to_contact_me') === 'y'
                : $settings->get('bs_allow_to_contact_me') === 'y'
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $settings->get('bs_allow_to_contact_me') === 'y'
            ? $lng->txt('buddy_allow_to_contact_me_yes')
            : $lng->txt('buddy_allow_to_contact_me_no');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return $this->retrieveValueFromUser($user) !== $settings->get('bs_allow_to_contact_me');
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        if ($input === null) {
            $user->deletePref('bs_allow_to_contact_me');
            return $user;
        }
        $user->setPref('bs_allow_to_contact_me', $input ? 'y' : 'n');
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): ?bool
    {
        $value = $user->getPref('bs_allow_to_contact_me');
        return $value !== null ? $value === 'y' : null;
    }
}
