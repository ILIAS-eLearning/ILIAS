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

/**
 * Class for date presentation
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilDatePresentation
{
    public static bool $use_relative_dates = true;
    private static ?ilLanguage $lang = null;
    public static ?ilDateTime $today = null;
    public static ?ilDateTime $tomorrow = null;
    public static ?ilDateTime $yesterday = null;
    protected static array $weekdays = [
        0 => 'Su_short',
        1 => 'Mo_short',
        2 => 'Tu_short',
        3 => 'We_short',
        4 => 'Th_short',
        5 => 'Fr_short',
        6 => 'Sa_short'
    ];

    /**
     * set use relative dates
     */
    public static function setUseRelativeDates(bool $a_status): void
    {
        self::$use_relative_dates = $a_status;
    }

    public static function useRelativeDates(): bool
    {
        return self::$use_relative_dates;
    }

    public static function setLanguage(ilLanguage $a_lng): void
    {
        self::$lang = $a_lng;
    }

    public static function getLanguage(): ilLanguage
    {
        global $DIC;

        $lng = $DIC->language();

        return self::$lang ?: $lng;
    }

    /**
     * reset to defaults
     */
    public static function resetToDefaults(): void
    {
        global $DIC;

        $lng = $DIC->language();
        self::setLanguage($lng);
        self::setUseRelativeDates(true);
    }

    public static function formatDate(
        ilDateTime $date,
        bool $a_skip_day = false,
        bool $a_include_wd = false,
        bool $include_seconds = false,
        ?ilObjUser $user = null,
    ): string {
        global $DIC;
        if ($user) {
            $lng = new ilLanguage($user->getLanguage());
        } else {
            $user = $DIC->user();
            $lng = self::getLanguage();
        }

        $lng->loadLanguageModule('dateplaner');

        if ($date->isNull()) {
            return $lng->txt('no_date');
        }

        $has_time = !$date instanceof ilDate;

        // Converting pure dates to user timezone might return wrong dates
        $date_info = [];
        if ($has_time) {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', $user->getTimeZone());
        } else {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');
        }

        $date_str = '';
        if (!$a_skip_day) {
            $sep = ', ';
            if (self::isToday($date) && self::useRelativeDates()) {
                $date_str = $lng->txt('today');
            } elseif (self::isTomorrow($date) && self::useRelativeDates()) {
                $date_str = $lng->txt('tomorrow');
            } elseif (self::isYesterday($date) && self::useRelativeDates()) {
                $date_str = $lng->txt('yesterday');
            } else {
                $date_str = '';
                if ($a_include_wd) {
                    $date_str = $lng->txt(self::$weekdays[$date_info['wday']]) . ', 	';
                }
                $date_str .= $date_info['mday'] . '. ' .
                    ilCalendarUtil::_numericMonthToString(
                        $date_info['mon'],
                        false,
                        $lng
                    ) . ' ' .
                    $date_info['year'];
            }
        } else {
            $sep = '';
        }

        if (!$has_time) {
            return $date_str;
        }

        $sec = ($include_seconds)
            ? ':s'
            : '';

        switch ($user->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_24:
                return $date_str . $sep .
                    $date->get(
                        IL_CAL_FKT_DATE,
                        'H:i' . $sec,
                        $user->getTimeZone()
                    );
            case ilCalendarSettings::TIME_FORMAT_12:
                return $date_str . $sep .
                    $date->get(
                        IL_CAL_FKT_DATE,
                        'g:ia' . $sec,
                        $user->getTimeZone()
                    );
        }

        return '';
    }

    /**
     * Format a period of two dates
     * Shows:    14. Jul 2008 18:00 - 20:00
     * or:        Today 18:00 - 20:00
     * or:        14. Jul 2008 - 16. Jul 2008
     * or:        14. Jul 2008, 12:00 - 16. Jul 2008, 14:00
     */
    public static function formatPeriod(
        ilDateTime $start,
        ilDateTime $end,
        bool $a_skip_starting_day = false,
        ?ilObjUser $user = null
    ): string {
        global $DIC;
        if (!$user) {
            $user = $DIC->user();
        }
        $has_time = !$start instanceof ilDate;

        // Same day
        if (ilDateTime::_equals($start, $end, IL_CAL_DAY, $user->getTimeZone())) {
            if (!$has_time) {
                return self::formatDate(
                    $start,
                    false,
                    false,
                    false,
                    $user
                );
            }
            $date_str = '';
            $sep = '';
            if (!$a_skip_starting_day) {
                $date_str = self::formatDate(
                    new ilDate(
                        $start->get(IL_CAL_DATE, '', $user->getTimeZone()),
                        IL_CAL_DATE
                    ),
                    false,
                    false,
                    false,
                    $user
                );
                $sep = ', ';
            }

            if (ilDateTime::_equals($start, $end)) {
                switch ($user->getTimeFormat()) {
                    case ilCalendarSettings::TIME_FORMAT_24:
                        return $date_str . $sep .
                            $start->get(
                                IL_CAL_FKT_DATE,
                                'H:i',
                                $user->getTimeZone()
                            );
                    case ilCalendarSettings::TIME_FORMAT_12:
                        return $date_str . $sep .
                            $start->get(
                                IL_CAL_FKT_DATE,
                                'h:i a',
                                $user->getTimeZone()
                            );
                }
            } else {
                switch ($user->getTimeFormat()) {
                    case ilCalendarSettings::TIME_FORMAT_24:
                        return $date_str . $sep .
                            $start->get(
                                IL_CAL_FKT_DATE,
                                'H:i',
                                $user->getTimeZone()
                            ) . ' - ' .
                            $end->get(IL_CAL_FKT_DATE, 'H:i', $user->getTimeZone());

                    case ilCalendarSettings::TIME_FORMAT_12:
                        return $date_str . $sep .
                            $start->get(
                                IL_CAL_FKT_DATE,
                                'g:ia',
                                $user->getTimeZone()
                            ) . ' - ' .
                            $end->get(IL_CAL_FKT_DATE, 'g:ia', $user->getTimeZone());
                }
            }
        }

        // Different days
        return
            self::formatDate(
                $start,
                $a_skip_starting_day,
                false,
                false,
                $user
            ) . ' - ' .
            self::formatDate(
                $end,
                false,
                false,
                false,
                $user
            );
    }

    /**
     * Check if date is "today"
     */
    public static function isToday(ilDateTime $date): bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!is_object(self::$today)) {
            self::$today = new ilDateTime(time(), IL_CAL_UNIX, $ilUser->getTimeZone());
        }

        return ilDateTime::_equals(self::$today, $date, IL_CAL_DAY, $ilUser->getTimeZone());
    }

    /**
     * Check if date is yesterday
     */
    public static function isYesterday(ilDateTime $date): bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        if (!is_object(self::$yesterday)) {
            self::$yesterday = new ilDateTime(time(), IL_CAL_UNIX, $ilUser->getTimeZone());
            self::$yesterday->increment(IL_CAL_DAY, -1);
        }

        return ilDateTime::_equals(self::$yesterday, $date, IL_CAL_DAY, $ilUser->getTimeZone());
    }

    /**
     * Check if date is tomorrow
     */
    public static function isTomorrow(ilDateTime $date): bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        if (!is_object(self::$tomorrow)) {
            self::$tomorrow = new ilDateTime(time(), IL_CAL_UNIX, $ilUser->getTimeZone());
            self::$tomorrow->increment(IL_CAL_DAY, 1);
        }

        return ilDateTime::_equals(self::$tomorrow, $date, IL_CAL_DAY, $ilUser->getTimeZone());
    }

    /**
     * converts seconds to string:
     * Long: 7 days 4 hour(s) ...
     */
    public static function secondsToString(
        int $seconds,
        bool $force_with_seconds = false,
        ?ilLanguage $a_lng = null
    ): string {
        global $DIC;

        $lng = $DIC['lng'];
        $message = null;

        if ($a_lng) {
            $lng = $a_lng;
        }

        $seconds = $seconds ?: 0;

        // #13625
        if ($seconds > 0) {
            $days = floor($seconds / 86400);
            $rest = $seconds % 86400;

            $hours = floor($rest / 3600);
            $rest %= 3600;

            $minutes = floor($rest / 60);
            $seconds = $rest % 60;
        } else {
            $days = ceil($seconds / 86400);
            $rest = $seconds % 86400;

            $hours = ceil($rest / 3600);
            $rest %= 3600;

            $minutes = ceil($rest / 60);
            $seconds = $rest % 60;
        }

        if ($days) {
            $message = $days . ' ' . ($days == 1 ? $lng->txt('day') : $lng->txt('days'));
        }
        if ($hours) {
            if ($message) {
                $message .= ' ';
            }
            $message .= ($hours . ' ' . ($hours == 1 ? $lng->txt('hour') : $lng->txt('hours')));
        }
        if ($minutes) {
            if ($message) {
                $message .= ' ';
            }
            $message .= ($minutes . ' ' . ($minutes == 1 ? $lng->txt('minute') : $lng->txt('minutes')));
        }
        if ($force_with_seconds && $seconds) {
            if ($message) {
                $message .= ' ';
            }
            $message .= ($seconds . ' ' . ($seconds == 1 ? $lng->txt('second') : $lng->txt('seconds')));
        }
        if (!$days && !$hours && !$minutes) {
            return $seconds . ' ' . ($seconds == 1 ? $lng->txt('second') : $lng->txt('seconds'));
        }

        return $message;
    }
}
