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

namespace ILIAS\User\Settings\User;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
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
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        return $field_factory->numeric(
            $lng->txt('session_reminder_input'),
            sprintf(
                $lng->txt('session_reminder_lead_time_info'),
                \ilSessionReminder::LEAD_TIME_DISABLED,
                \ilSessionReminder::SUGGESTED_LEAD_TIME,
                \ilDatePresentation::secondsToString(\ilSession::getSessionExpireValue(), true)
            )
        )->withAdditionalTransformation(
            $refinery->int()->isGreaterThanOrEqual(
                \ilSessionReminder::LEAD_TIME_DISABLED
            )
        )->withAdditionalTransformation(
            $refinery->int()->isLessThanOrEqual(
                $this->session_reminder->getMaxPossibleLeadTime()
            )
        )->withValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : $this->session_reminder->getGlobalSessionReminderLeadTime()
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
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
        $input->setValue(
            $user !== null
                ? (string) $this->retrieveValueFromUser($user)
                : (string) $this->session_reminder->getGlobalSessionReminderLeadTime()
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $this->session_reminder->getGlobalSessionReminderLeadTime() . ' ' . $lng->txt('minutes');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return  $this->retrieveValueFromUser($user) !== $this->session_reminder->getGlobalSessionReminderLeadTime();
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        $user->setPref(
            'session_reminder_lead_time',
            $input !== null ? (string) $input : (string) $this->session_reminder->getGlobalSessionReminderLeadTime()
        );
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): int
    {
        return $this->session_reminder->getEffectiveLeadTime();
    }
}
