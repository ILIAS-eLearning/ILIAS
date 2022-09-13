<?php

declare(strict_types=1);

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

/**
 * Calculates an <code>ilDateList</code> for a given calendar entry and recurrence rule.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls
 * @ingroup ServicesCalendar
 */
class ilCalendarRecurrenceCalculator
{
    protected string $timezone = ilTimeZone::UTC;
    protected ilLogger $log;

    protected bool $limit_reached = false;
    protected ?ilDateList $valid_dates = null;
    protected ?ilDateTime $period_start = null;
    protected ?ilDateTime $period_end = null;
    protected ?ilDateTime $start = null;

    protected ilDatePeriod $event;
    protected ilCalendarRecurrenceCalculation $recurrence;
    protected int $duration = 0;
    protected string $frequence_context = '';

    public function __construct(ilDatePeriod $entry, ilCalendarRecurrenceCalculation $rec)
    {
        $this->log = $GLOBALS['DIC']->logger()->cal();
        $this->event = $entry;
        $this->recurrence = $rec;

        $this->duration = (int) $entry->getEnd()->get(IL_CAL_UNIX) - (int) $entry->getStart()->get(IL_CAL_UNIX);
    }

    /**
     * Get duration of event
     */
    protected function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * calculate date list
     * @param ilDateTime ilDateTime start of period
     * @param ilDateTime ilDateTime end of period
     * @param int limit number of returned dates
     * @return ilDateList List of recurring dates
     */
    public function calculateDateList(ilDateTime $a_start, ilDateTime $a_end, int $a_limit = -1): ilDateList
    {
        $this->valid_dates = $this->initDateList();

        // Check invalid settings: e.g no frequence given, invalid start/end dates ...
        if (!$this->validateRecurrence()) {
            $this->valid_dates->add($this->event->getStart());
            return $this->valid_dates;
        }

        // Performance fix: Switching the timezone for many dates seems to be
        // quite time consuming.
        // Therfore we adjust the timezone of all input dates (start,end, event start)
        // to the same tz (UTC for fullday events, Recurrence tz for all others).
        $this->adjustTimeZones($a_start, $a_end);

        // Add start of event if it is in the period
        if ((ilDateTime::_after($this->event->getStart(), $this->period_start, IL_CAL_DAY) and
                ilDateTime::_before($this->event->getStart(), $this->period_end, IL_CAL_DAY)) or
            ilDateTime::_equals($this->event->getStart(), $this->period_start, IL_CAL_DAY)) {
            // begin-patch aptar
            $this->valid_dates->add($this->event->getStart());
            #$this->valid_dates->add($this->event->getStart());
            // end patch aptar
        }

        // Calculate recurrences based on frequency (e.g. MONTHLY)
        $time = microtime(true);

        $start = $this->optimizeStartingTime();

        #echo "ZEIT: ADJUST: ".(microtime(true) - $time).'<br>';
        $counter = 0;
        do {
            ++$counter;
            // initialize context for applied rules
            // E.g
            // RRULE:FREQ=YEARLY;BYMONTH=1;BYWEEKNO=1,50	=> context for BYWERKNO is monthly because it filters to the weeks in JAN
            // RRULE:FREQ=YEARLY;BYWEEKNO=1,50				=> context for BYWERKNO is yearly because it adds all weeks.
            $this->frequence_context = $this->recurrence->getFrequenceType();

            $freq_res = $this->initDateList();
            $freq_res->add($start);

            // Fixed sequence of applying rules (RFC 2445 4.3.10)
            $freq_res = $this->applyBYMONTHRules($freq_res);
            #echo "BYMONTH: ".$freq_res;

            $freq_res = $this->applyBYWEEKNORules($freq_res);
            #echo "BYWEEKNO: ".$freq_res;

            $freq_res = $this->applyBYYEARDAYRules($freq_res);
            #echo "BYYEARDAY: ".$freq_res;

            $freq_res = $this->applyBYMONTHDAYRules($freq_res);
            #echo "BYMONTHDAY: ".$freq_res;

            #$time = microtime(true);
            $freq_res = $this->applyBYDAYRules($freq_res);
            #echo "ZEIT: ".(microtime(true) - $time);
            #echo "BYDAY: ".$freq_res;

            $freq_res = $this->applyBYSETPOSRules($freq_res);
            #echo "BYSETPOS: ".$freq_res;

            $freq_res = $this->applyLimits($freq_res);
            #echo $freq_res;

            $start = $this->incrementByFrequency($start);

            if (ilDateTime::_after($start, $this->period_end) or $this->limit_reached) {
                break;
            }
        } while (true);

        $this->applyExclusionDates();
        $this->applyDurationPeriod($this->valid_dates, $this->period_start, $this->period_end);
        $this->valid_dates->sort();

        // Restore default timezone
        ilTimeZone::_restoreDefaultTimeZone();
        return $this->valid_dates;
    }

    /**
     * Apply duration period
     */
    protected function applyDurationPeriod(ilDateList $list, ilDateTime $start, ilDateTime $end): void
    {
        $list_copy = clone $list;
        foreach ($list_copy as $start_date) {
            $end_date = clone $start_date;
            $end_date->increment(ilDateTime::MINUTE, $this->getDuration() / 60);

            if (
                (
                    ilDateTime::_after($start_date, $this->period_end)
                ) ||
                (
                    ilDateTime::_before($end_date, $this->period_start)
                )
            ) {
                $this->log->debug('Removed invalid date ' . $start_date . ' <-> ' . $end_date);
                $list->remove($start_date);
            }
        }
    }

    /**
     * Adjust timezone
     */
    protected function adjustTimeZones(ilDateTime $a_start, ilDateTime $a_end): void
    {
        $this->timezone = $this->event->isFullday() ? ilTimeZone::UTC : $this->recurrence->getTimeZone();
        ilTimeZone::_setDefaultTimeZone($this->timezone);

        $this->period_start = clone $a_start;
        $this->period_end = clone $a_end;
        $this->start = clone $this->event->getStart();

        try {
            if ($this->event->isFullday()) {
                $this->period_start->switchTimeZone(ilTimeZone::UTC);
                $this->period_end->switchTimeZone(ilTimeZone::UTC);
                $this->start->switchTimeZone(ilTimeZone::UTC);
            } else {
                $this->period_start->switchTimeZone($this->recurrence->getTimeZone());
                $this->period_end->switchTimeZone($this->recurrence->getTimeZone());
                $this->start->switchTimeZone($this->recurrence->getTimeZone());
            }
            return;
        } catch (ilDateTimeException $e) {
            $this->log->debug(': ' . $e->getMessage());
            return;
        }
    }

    protected function optimizeStartingTime(): ilDateTime
    {
        // starting time cannot be optimzed if RRULE UNTIL is given.
        // In that case we have to calculate all dates until "UNTIL" is reached.
        if ($this->recurrence->getFrequenceUntilCount() > 0) {
            // Switch the date to the original defined timzone for this recurrence
            return $this->createDate($this->start->get(IL_CAL_UNIX, '', $this->timezone));
        }
        $optimized = $start = $this->createDate($this->start->get(IL_CAL_UNIX, '', $this->timezone));
        while (ilDateTime::_before($start, $this->period_start)) {
            $optimized = clone $start;
            $start = $this->incrementByFrequency($start);
        }
        return $optimized;
    }

    protected function incrementByFrequency(ilDateTime $start): ilDateTime
    {
        switch ($this->recurrence->getFrequenceType()) {
            case ilCalendarRecurrence::FREQ_YEARLY:
                $start->increment(ilDateTime::YEAR, $this->recurrence->getInterval());
                break;

            case ilCalendarRecurrence::FREQ_MONTHLY:
                $start->increment(ilDateTime::MONTH, $this->recurrence->getInterval());
                break;

            case ilCalendarRecurrence::FREQ_WEEKLY:
                $start->increment(ilDateTime::WEEK, $this->recurrence->getInterval());
                break;

            case ilCalendarRecurrence::FREQ_DAILY:
                $start->increment(ilDateTime::DAY, $this->recurrence->getInterval());
                break;

            default:
                $this->log->warning('No frequence defined.');
                break;
        }
        return $start;
    }

    protected function applyBYMONTHRules(ilDateList $list): ilDateList
    {
        // return unmodified, if no bymonth rules are available
        if (!$this->recurrence->getBYMONTHList()) {
            return $list;
        }
        $month_list = $this->initDateList();
        foreach ($list->get() as $date) {
            foreach ($this->recurrence->getBYMONTHList() as $month) {
                // YEARLY rules extend the seed to every month given in the BYMONTH rule
                // Rules < YEARLY must match the month of the seed
                if ($this->recurrence->getFrequenceType() == ilCalendarRecurrence::FREQ_YEARLY) {
                    $month_date = $this->createDate($date->get(IL_CAL_UNIX, '', $this->timezone));
                    $month_date->increment(
                        ilDateTime::MONTH,
                        -($date->get(IL_CAL_FKT_DATE, 'n', $this->timezone) - $month)
                    );

                    #echo "BYMONTH: ".$month_date;
                    $month_list->add($month_date);
                } elseif ($date->get(IL_CAL_FKT_DATE, 'n', $this->timezone) == $month) {
                    $month_list->add($date);
                }
            }
        }
        // decrease the frequence_context for YEARLY rules
        if ($this->recurrence->getFrequenceType() == ilCalendarRecurrence::FREQ_YEARLY) {
            $this->frequence_context = ilCalendarRecurrence::FREQ_MONTHLY;
        }
        return $month_list;
    }

    /**
     * Apply BYWEEKNO rules (1 to 53 and -1 to -53).
     * This rule can only be applied to YEARLY rules (RFC 2445 4.3.10)
     */
    protected function applyBYWEEKNORules(ilDateList $list): ilDateList
    {
        if ($this->recurrence->getFrequenceType() != ilCalendarRecurrence::FREQ_YEARLY) {
            return $list;
        }
        // return unmodified, if no byweekno rules are available
        if (!$this->recurrence->getBYWEEKNOList()) {
            return $list;
        }
        $weeks_list = $this->initDateList();
        foreach ($list->get() as $seed) {
            $weeks_in_year = date('W', mktime(0, 0, 0, 12, 28, $seed->get(IL_CAL_FKT_DATE, 'Y', $this->timezone)));
            $this->log->debug(': Year ' . $seed->get(
                IL_CAL_FKT_DATE,
                'Y',
                $this->timezone
            ) . ' has ' . $weeks_in_year . ' weeks');
            foreach ($this->recurrence->getBYWEEKNOList() as $week_no) {
                $week_no = $week_no < 0 ? ((int) $weeks_in_year + $week_no + 1) : $week_no;

                switch ($this->frequence_context) {
                    case ilCalendarRecurrence::FREQ_MONTHLY:
                        $this->log->debug(': Handling BYWEEKNO in MONTHLY context');
                        // Check if week matches
                        if ($seed->get(IL_CAL_FKT_DATE, 'W', $this->timezone) == $week_no) {
                            $weeks_list->add($seed);
                        }
                        break;

                    case ilCalendarRecurrence::FREQ_YEARLY:
                        $this->log->debug(': Handling BYWEEKNO in YEARLY context');
                        $week_diff = $week_no - $seed->get(IL_CAL_FKT_DATE, 'W', $this->timezone);

                        // TODO: think about tz here
                        $new_week = $this->createDate($seed->get(IL_CAL_UNIX, '', $this->timezone));
                        $new_week->increment(ilDateTime::WEEK, $week_diff);
                        $weeks_list->add($new_week);
                        break;
                }
            }
        }
        $this->frequence_context = ilCalendarRecurrence::FREQ_WEEKLY;
        return $weeks_list;
    }

    protected function applyBYYEARDAYRules(ilDateList $list): ilDateList
    {
        // return unmodified, if no byweekno rules are available
        if (!$this->recurrence->getBYYEARDAYList()) {
            return $list;
        }
        $days_list = $this->initDateList();
        foreach ($list->get() as $seed) {
            $num_days = date('z', mktime(0, 0, 0, 12, 31, $seed->get(IL_CAL_FKT_DATE, 'Y', $this->timezone)));
            $this->log->debug(': Year ' . $seed->get(
                IL_CAL_FKT_DATE,
                'Y',
                $this->timezone
            ) . ' has ' . $num_days . ' days.');

            foreach ($this->recurrence->getBYYEARDAYList() as $day_no) {
                $day_no = $day_no < 0 ? ((int) $num_days + $day_no + 1) : $day_no;

                $day_diff = $day_no - $seed->get(IL_CAL_FKT_DATE, 'z', $this->timezone);
                $new_day = $this->createDate($seed->get(IL_CAL_UNIX, '', $this->timezone));
                $new_day->increment(ilDateTime::DAY, $day_diff);

                switch ($this->frequence_context) {
                    case ilCalendarRecurrence::FREQ_DAILY:
                        // Check if day matches
                        if ($seed->get(IL_CAL_FKT_DATE, 'z', $this->timezone) == $day_no) {
                            $days_list->add($new_day);
                        }
                        break;
                    case ilCalendarRecurrence::FREQ_WEEKLY:
                        // Check if week matches
                        if ($seed->get(IL_CAL_FKT_DATE, 'W', $this->timezone) == $new_day->get(
                            IL_CAL_FKT_DATE,
                            'W',
                            $this->timezone
                        )) {
                            $days_list->add($new_day);
                        }
                        break;
                    case ilCalendarRecurrence::FREQ_MONTHLY:
                        // Check if month matches
                        if ($seed->get(IL_CAL_FKT_DATE, 'n', $this->timezone) == $new_day->get(
                            IL_CAL_FKT_DATE,
                            'n',
                            $this->timezone
                        )) {
                            $days_list->add($new_day);
                        }
                        break;
                    case ilCalendarRecurrence::FREQ_YEARLY:
                        // Simply add
                        $days_list->add($new_day);
                        break;
                }
            }
        }

        $this->frequence_context = ilCalendarRecurrence::FREQ_DAILY;
        return $days_list;
    }

    protected function applyBYMONTHDAYRules(ilDateList $list): ilDateList
    {
        // return unmodified, if no byweekno rules are available
        if (!$this->recurrence->getBYMONTHDAYList()) {
            return $list;
        }
        $days_list = $this->initDateList();
        foreach ($list->get() as $seed) {
            $num_days = ilCalendarUtil::_getMaxDayOfMonth(
                (int) $seed->get(IL_CAL_FKT_DATE, 'Y', $this->timezone),
                (int) $seed->get(IL_CAL_FKT_DATE, 'n', $this->timezone)
            );
            foreach ($this->recurrence->getBYMONTHDAYList() as $bymonth_no) {
                $day_no = $bymonth_no < 0 ? ($num_days + $bymonth_no + 1) : $bymonth_no;
                if ($this->frequence_context != ilCalendarRecurrence::FREQ_YEARLY) {
                    if ($day_no < 1 or $day_no > $num_days) {
                        $this->log->debug(': Ignoring BYMONTHDAY rule: ' . $day_no . ' for month ' .
                            $seed->get(IL_CAL_FKT_DATE, 'M', $this->timezone));
                        continue;
                    }
                }
                $day_diff = $day_no - $seed->get(IL_CAL_FKT_DATE, 'j', $this->timezone);
                $new_day = $this->createDate($seed->get(IL_CAL_UNIX, '', $this->timezone));
                $new_day->increment(ilDateTime::DAY, $day_diff);

                switch ($this->frequence_context) {
                    case ilCalendarRecurrence::FREQ_DAILY:
                        // Check if day matches
                        if ($seed->get(IL_CAL_FKT_DATE, 'j', $this->timezone) == $day_no) {
                            $days_list->add($new_day);
                        }
                        break;

                    case ilCalendarRecurrence::FREQ_WEEKLY:
                        // Check if week matches
                        if ($seed->get(IL_CAL_FKT_DATE, 'W', $this->timezone) == $new_day->get(
                            IL_CAL_FKT_DATE,
                            'W',
                            $this->timezone
                        )) {
                            $days_list->add($new_day);
                        }
                        break;

                    case ilCalendarRecurrence::FREQ_MONTHLY:
                        // seed and new day are in the same month.
                        $days_list->add($new_day);
                        break;

                    case ilCalendarRecurrence::FREQ_YEARLY:
                        $h = (int) ($this->event->isFullday() ? 0 : $seed->get(IL_CAL_FKT_DATE, 'H', $this->timezone));
                        $i = (int) ($this->event->isFullday() ? 0 : $seed->get(IL_CAL_FKT_DATE, 'i', $this->timezone));
                        $s = (int) ($this->event->isFullday() ? 0 : $seed->get(IL_CAL_FKT_DATE, 's', $this->timezone));
                        $y = (int) $seed->get(IL_CAL_FKT_DATE, 'Y', $this->timezone);

                        // TODO: the chosen monthday has to added to all months
                        for ($month = 1; $month <= 12; $month++) {
                            $num_days = ilCalendarUtil::_getMaxDayOfMonth(
                                $y,
                                $month
                            );
                            $day_no = $bymonth_no < 0 ? ($num_days + $bymonth_no + 1) : $bymonth_no;
                            if ($day_no < 1 or $day_no > $num_days) {
                                $this->log->debug(': Ignoring BYMONTHDAY rule: ' . $day_no . ' for month ' . $month);
                            } else {
                                $tz_obj = ilTimeZone::_getInstance($this->timezone);
                                $tz_obj->switchTZ();
                                $unix = mktime($h, $i, $s, $month, $day_no, $y);
                                $tz_obj->restoreTZ();
                                $new_day = $this->createDate($unix);
                                $days_list->add($new_day);
                            }
                        }
                        break;
                }
            }
        }
        $this->frequence_context = ilCalendarRecurrence::FREQ_DAILY;
        return $days_list;
    }

    protected function applyBYDAYRules(ilDateList $list): ilDateList
    {
        // return unmodified, if no byday rules are available
        if (!$this->recurrence->getBYDAYList()) {
            return $list;
        }

        $days_list = $this->initDateList();

        // generate a list of e.g all Sundays for the given year
        // or e.g a list of all week days in a give month (FREQ = MONTHLY,WEEKLY or DAILY)
        $day_array = [];
        foreach ($list->get() as $seed) {
            $seed_info = $seed->get(IL_CAL_FKT_GETDATE);

            // TODO: maybe not correct in dst cases
            $date_info = $seed->get(IL_CAL_FKT_GETDATE);
            $date_info['mday'] = 1;
            $date_info['mon'] = 1;
            $start = $this->createDate($date_info, IL_CAL_FKT_GETDATE);

            switch ($this->frequence_context) {
                case ilCalendarRecurrence::FREQ_YEARLY:
                    $day_array = $this->getYearWeekDays($seed);
                    break;

                case ilCalendarRecurrence::FREQ_MONTHLY:
                    $day_array = $this->getMonthWeekDays($seed_info['year'], $seed_info['mon']);
                    break;

                case ilCalendarRecurrence::FREQ_WEEKLY:
                    // TODO or RFC bug: FREQ>WEEKLY;BYMONTH=1;BYDAY=FR returns FR 1.2.2008
                    // Ical says: apply BYMONTH rules and after that apply byday rules on that date list.
                    $day_array = $this->getWeekWeekDays($seed_info);
                    break;

                case ilCalendarRecurrence::FREQ_DAILY:
                    $day_array[strtoupper(substr($seed->get(IL_CAL_FKT_DATE, 'D'), 0, 2))] = array($seed_info['yday']);
                    break;

            }
            foreach ($this->recurrence->getBYDAYList() as $byday) {
                $year_day = array();
                $day = strtoupper(substr($byday, -2));
                $num_by_day = (int) $byday;

                if ($num_by_day) {
                    if ($num_by_day > 0) {
                        if (isset($day_array[$day][$num_by_day - 1])) {
                            $year_day = array($day_array[$day][$num_by_day - 1]);
                        }
                    } elseif (isset($day_array[$day][count($day_array[$day]) + $num_by_day])) {
                        $year_day = array($day_array[$day][count($day_array[$day]) + $num_by_day]);
                    }
                } elseif (isset($day_array[$day])) {
                    $year_day = $day_array[$day];
                }
                foreach ($year_day as $day) {
                    switch ($this->frequence_context) {
                        case ilCalendarRecurrence::FREQ_WEEKLY:
                        case ilCalendarRecurrence::FREQ_DAILY:
                        case ilCalendarRecurrence::FREQ_MONTHLY:
                        case ilCalendarRecurrence::FREQ_YEARLY:
                            $tmp_date = clone $start;
                            $tmp_date->increment(IL_CAL_DAY, $day);
                            $days_list->add($tmp_date);
                            break;
                    }
                }
            }
        }
        return $days_list;
    }

    /**
     * get a list of year week days according to the BYMONTH rule
     */
    protected function getYearWeekDays(ilDateTime $seed): array
    {
        $time = microtime(true);

        $year_days = array();

        $current_year = $seed->get(IL_CAL_FKT_DATE, 'Y');
        $start = new ilDate($current_year . '-01-01', IL_CAL_DATE);
        $offset = $start->get(IL_CAL_FKT_DATE, 'w');
        $days = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA');
        for ($i = 0; $i < $offset; $i++) {
            next($days);
        }

        $num_days = ilCalendarUtil::_isLeapYear($current_year) ? 366 : 365;
        for ($i = 0; $i < $num_days; $i++) {
            if (!($current_day = current($days))) {
                $current_day = reset($days);
            }
            $year_days[$current_day][] = $i;
            next($days);
        }
        return $year_days;
    }

    protected function getMonthWeekDays(int $year, int $month): array
    {
        static $month_days = array();

        if (isset($month_days[$year][$month])) {
            return $month_days[$year][$month];
        }

        $month_str = $month < 10 ? ('0' . $month) : $month;
        $begin_month = new ilDate($year . '-' . $month_str . '-01', IL_CAL_DATE);
        $begin_month_info = $begin_month->get(IL_CAL_FKT_GETDATE);

        $days = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA');
        for ($i = 0; $i < $begin_month_info['wday']; $i++) {
            next($days);
        }
        for ($i = $begin_month_info['yday']; $i < $begin_month_info['yday'] + ilCalendarUtil::_getMaxDayOfMonth(
            $year,
            $month
        ); $i++) {
            if (!($current_day = current($days))) {
                $current_day = reset($days);
            }
            $month_days[$year][$month][$current_day][] = $i;
            next($days);
        }
        return $month_days[$year][$month];
    }

    /**
     * get weedays of week
     */
    protected function getWeekWeekDays(array $seed_info): array
    {
        $days = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA');

        $start_day = $seed_info['yday'] - $seed_info['wday'];
        foreach ($days as $num => $day) {
            $week_days[$day][] = $start_day++;
        }
        return $week_days;
    }

    /**
     * Apply BYSETPOST rules
     */
    protected function applyBYSETPOSRules(ilDateList $list): ilDateList
    {
        // return unmodified, if no bysetpos rules are available
        if (!$this->recurrence->getBYSETPOSList()) {
            return $list;
        }
        $pos_list = $this->initDateList();
        $list->sort();
        $candidates = $list->get();
        $candidates_count = count($candidates);
        foreach ($this->recurrence->getBYSETPOSList() as $position) {
            if ($position > 0 and $date = $list->getAtPosition($position)) {
                $pos_list->add($date);
            }
            if ($position < 0 and $date = $list->getAtPosition($candidates_count + $position + 1)) {
                $pos_list->add($date);
            }
        }
        return $pos_list;
    }

    /**
     * Apply limits (count or until)
     */
    protected function applyLimits(ilDateList $list): bool
    {
        $list->sort();
        // Check valid dates before starting time
        foreach ($list->get() as $check_date) {
            if (ilDateTime::_before($check_date, $this->event->getStart(), IL_CAL_DAY)) {
                $this->log->debug('Removed invalid date: ' . $check_date . ' before starting date:  ' . $this->event->getStart());
                $list->remove($check_date);
            }
        }

        // Check count if given
        if ($this->recurrence->getFrequenceUntilCount()) {
            foreach ($list->get() as $res) {
                // check smaller than since the start time counts as one
                if (count($this->valid_dates->get()) < $this->recurrence->getFrequenceUntilCount()) {
                    $this->valid_dates->add($res);
                } else {
                    $this->limit_reached = true;
                    return false;
                }
            }
            return true;
        } elseif ($this->recurrence->getFrequenceUntilDate()) {
            $date = $this->recurrence->getFrequenceUntilDate();
            foreach ($list->get() as $res) {
                if (ilDateTime::_after($res, $date, IL_CAL_DAY)) {
                    $this->limit_reached = true;
                    return false;
                }
                $this->valid_dates->add($res);
            }
            return true;
        }
        $this->valid_dates->merge($list);
        return true;
    }

    protected function applyExclusionDates(): void
    {
        if (!$this->recurrence->getExclusionDates()) {
            return;
        }
        foreach ($this->recurrence->getExclusionDates() as $excl) {
            $this->valid_dates->removeByDAY($excl->getDate());
        }
    }

    protected function initDateList(): ilDateList
    {
        return new ilDateList($this->event->isFullday() ? ilDateList::TYPE_DATE : ilDateList::TYPE_DATETIME);
    }

    protected function createDate($a_date, $a_format_type = IL_CAL_UNIX): ilDateTime
    {
        if ($this->event->isFullday()) {
            return new ilDate($a_date, $a_format_type);
        } else {
            // TODO: the timezone for this recurrence must be stored in the db
            return new ilDateTime($a_date, $a_format_type, $this->timezone);
        }
    }

    protected function validateRecurrence(): bool
    {
        return $this->recurrence->validate();
    }
}
