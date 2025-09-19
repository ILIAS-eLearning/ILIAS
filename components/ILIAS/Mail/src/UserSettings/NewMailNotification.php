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

namespace ILIAS\Mail\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class NewMailNotification implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'new_mail_notification';
    }

    public function isAvailable(): bool
    {
        return (new \ilSetting())->get('mail_notification') === '1';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('cron_mail_notification');
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Communication;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('mail');
        return $field_factory->checkbox(
            $this->getLabel($lng),
            $lng->txt('mail_cronjob_notification_info')
        )->withValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : false
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $setting,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('mail');
        $input = new \ilCheckboxInputGUI($this->getLabel($lng));
        $input->setInfo($lng->txt('mail_cronjob_notification_info'));
        $input->setChecked(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : false
        );
        $input->setValue('1');
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $lng->txt('no');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        $value = $this->retrieveValueFromUser($user);
        return $value !== false;
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $mail_options = new \ilMailOptions($user->getId());
        $mail_options->setIsCronJobNotificationStatus($input === true || $input === '1');
        $mail_options->updateOptions();
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): bool
    {
        return (new \ilMailOptions($user->getId()))->isCronJobNotificationEnabled();
    }
}
