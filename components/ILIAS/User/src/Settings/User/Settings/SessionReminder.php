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

use ILIAS\User\Settings\User\SettingDefinition;
use ILIAS\User\Settings\User\AvailablePages;
use ILIAS\User\Settings\User\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

class SessionReminder implements SettingDefinition
{
    private readonly \ilSessionReminder $session_reminder;

    public function __construct()
    {
        $this->session_reminder = \ilSessionReminder::byLoggedInUser();
    }

    public function getIdentifier(): string
    {
        return 'session_reminder';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
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
        ?\ilObjUser $current_user = null
    ): \ilFormPropertyGUI {
        $input = new \ilNumberInputGUI($lng->txt('session_reminder_input'));
        $input->setInfo(
            sprintf(
                $lng->txt('session_reminder_lead_time_info'),
                \ilSessionReminder::LEAD_TIME_DISABLED,
                \ilSessionReminder::SUGGESTED_LEAD_TIME,
                \ilDatePresentation::secondsToString(\ilSession::getSessionExpireValue(), true)
            )
        );
        $input->setSize(3);
        $input->setMinValue(\ilSessionReminder::LEAD_TIME_DISABLED);
        $input->setMaxValue($this->session_reminder->getMaxPossibleLeadTime());
        if ($current_user === null) {
            return $input;
        }
        $input->setValue((string) $this->retrieveValueFromUser($current_user));
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings
    ): string {
        return $this->session_reminder->getGlobalSessionReminderLeadTime() . ' ' . $lng->txt('minutes');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $current_user
    ): bool {
        return  $this->retrieveValueFromUser($current_user) !== $this->session_reminder->getGlobalSessionReminderLeadTime();
    }

    public function persistUserInput(
        \ilObjUser $current_user,
        mixed $input
    ): \ilObjUser {
        $current_user->setPref(
            'session_reminder_lead_time',
            $input !== null ? (string) $input : (string) $this->session_reminder->getGlobalSessionReminderLeadTime()
        );
        return $current_user;
    }

    public function retrieveValueFromUser(\ilObjUser $current_user): int
    {
        return $this->session_reminder->getEffectiveLeadTime();
    }
}
