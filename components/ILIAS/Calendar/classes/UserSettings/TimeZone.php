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

namespace ILIAS\Calendar\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class TimeZone implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'timezone';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('cal_user_timezone');
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::DateTime;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('dateplaner');
        return $field_factory->select(
            $lng->txt('cal_user_timezone'),
            \ilCalendarUtil::_getShortTimeZoneList(),
            $lng->txt('cal_timezone_info')
        )->withValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : \ilCalendarSettings::_getInstance()->getDefaultTimeZone()
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('dateplaner');
        $input = new \ilSelectInputGUI($lng->txt('cal_user_timezone'));
        $input->setOptions(\ilCalendarUtil::_getShortTimeZoneList());
        $input->setInfo($lng->txt('cal_timezone_info'));
        $input->setValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : \ilCalendarSettings::_getInstance()->getDefaultTimeZone()
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return \ilCalendarUtil::_getShortTimeZoneList()[\ilCalendarSettings::_getInstance()->getDefaultTimeZone()];
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return $this->retrieveValueFromUser($user) !== \ilCalendarSettings::_getInstance()->getDefaultTimeZone();
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        $calendar_settings = \ilCalendarUserSettings::_getInstance($user->getId());
        $calendar_settings->setTimeZone(
            $input !== null ? $input : \ilCalendarSettings::_getInstance()->getDefaultTimeZone()
        );
        $calendar_settings->save();
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        return $user->getTimeZone();
    }
}
