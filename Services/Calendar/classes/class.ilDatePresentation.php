<?php declare(strict_types=1);
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

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

    protected static array $weekdays = array(
        0 => "Su_short",
        1 => "Mo_short",
        2 => "Tu_short",
        3 => "We_short",
        4 => "Th_short",
        5 => "Fr_short",
        6 => "Sa_short"
    );

    /**
     * set use relative dates
     */
    public static function setUseRelativeDates(bool $a_status) : void
    {
        self::$use_relative_dates = $a_status;
    }

    public static function useRelativeDates() : bool
    {
        return self::$use_relative_dates;
    }

    public static function setLanguage(ilLanguage $a_lng) : void
    {
        self::$lang = $a_lng;
    }

    public static function getLanguage() : ilLanguage
    {
        global $DIC;

        $lng = $DIC->language();
        return self::$lang ?: $lng;
    }

    /**
     * reset to defaults
     */
    public static function resetToDefaults()
    {
        global $DIC;

        $lng = $DIC->language();
        self::setLanguage($lng);
        self::setUseRelativeDates(true);
    }

    public static function formatDate(ilDateTime $date, bool $a_skip_day = false, bool $a_include_wd = false, bool $include_seconds = false) : string
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        if ($date->isNull()) {
            return self::getLanguage()->txt('no_date');
        }

        $has_time = !is_a($date, 'ilDate');

        // Converting pure dates to user timezone might return wrong dates
        if ($has_time) {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', $ilUser->getTimeZone());
        } else {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');
        }

        $date_str = null;
        if (!$a_skip_day) {
            $sep = ", ";
            if (self::isToday($date) and self::useRelativeDates()) {
                $date_str = self::getLanguage()->txt('today');
            } elseif (self::isTomorrow($date) and self::useRelativeDates()) {
                $date_str = self::getLanguage()->txt('tomorrow');
            } elseif (self::isYesterday($date) and self::useRelativeDates()) {
                $date_str = self::getLanguage()->txt('yesterday');
            } else {
                $date_str = "";
                if ($a_include_wd) {
                    $date_str = $lng->txt(self::$weekdays[$date->get(IL_CAL_FKT_DATE, 'w')]) . ", 	";
                }
                $date_str .= $date->get(IL_CAL_FKT_DATE, 'd') . '. ' .
                    ilCalendarUtil::_numericMonthToString($date_info['mon'], false) . ' ' .
                    $date_info['year'];
            }
        } else {
            $sep = "";
        }

        if (!$has_time) {
            return $date_str;
        }

        $sec = ($include_seconds)
            ? ":s"
            : "";

        switch ($ilUser->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_24:
                return $date_str . $sep . $date->get(IL_CAL_FKT_DATE, 'H:i' . $sec, $ilUser->getTimeZone());

            case ilCalendarSettings::TIME_FORMAT_12:
                return $date_str . $sep . $date->get(IL_CAL_FKT_DATE, 'g:ia' . $sec, $ilUser->getTimeZone());
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
    public static function formatPeriod(ilDateTime $start, ilDateTime $end, bool $a_skip_starting_day = false) : string
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $has_time = !is_a($start, 'ilDate');

        // Same day
        if (ilDateTime::_equals($start, $end, IL_CAL_DAY, $ilUser->getTimeZone())) {
            if (!$has_time) {
                return self::formatDate($start);
            } else {
                $date_str = "";
                $sep = "";
                if (!$a_skip_starting_day) {
                    $date_str = self::formatDate(
                        new ilDate($start->get(IL_CAL_DATE, '', $ilUser->getTimeZone()), IL_CAL_DATE)
                    );
                    $sep = ", ";
                }

                // $start == $end
                if (ilDateTime::_equals($start, $end)) {
                    switch ($ilUser->getTimeFormat()) {
                        case ilCalendarSettings::TIME_FORMAT_24:
                            return $date_str . $sep . $start->get(IL_CAL_FKT_DATE, 'H:i', $ilUser->getTimeZone());

                        case ilCalendarSettings::TIME_FORMAT_12:
                            return $date_str . $sep . $start->get(IL_CAL_FKT_DATE, 'h:i a', $ilUser->getTimeZone());
                    }
                } else {
                    switch ($ilUser->getTimeFormat()) {
                        case ilCalendarSettings::TIME_FORMAT_24:
                            return $date_str . $sep . $start->get(
                                IL_CAL_FKT_DATE,
                                'H:i',
                                $ilUser->getTimeZone()
                            ) . ' - ' .
                                $end->get(IL_CAL_FKT_DATE, 'H:i', $ilUser->getTimeZone());

                        case ilCalendarSettings::TIME_FORMAT_12:
                            return $date_str . $sep . $start->get(
                                IL_CAL_FKT_DATE,
                                'g:ia',
                                $ilUser->getTimeZone()
                            ) . ' - ' .
                                $end->get(IL_CAL_FKT_DATE, 'g:ia', $ilUser->getTimeZone());
                    }
                }
            }
        }
        // Different days
        return self::formatDate($start, $a_skip_starting_day) . ' - ' . self::formatDate($end);
    }

    /**
     * Check if date is "today"
     */
    public static function isToday(ilDateTime $date) : bool
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
    public static function isYesterday(ilDateTime $date) : bool
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
    public static function isTomorrow(ilDateTime $date) : bool
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
    ) : string {
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
            $rest = $rest % 3600;

            $minutes = floor($rest / 60);
            $seconds = $rest % 60;
        } else {
            $days = ceil($seconds / 86400);
            $rest = $seconds % 86400;

            $hours = ceil($rest / 3600);
            $rest = $rest % 3600;

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
        if (!$days and !$hours and !$minutes) {
            return $seconds . ' ' . ($seconds == 1 ? $lng->txt('second') : $lng->txt('seconds'));
        } else {
            return $message;
        }
    }
}
