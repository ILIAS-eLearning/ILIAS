<?php

declare(strict_types=1);
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
 * Represents a list of calendar appointments (including recurring events) for a specific user
 * in a given time range.
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarSchedule
{
    public const TYPE_DAY = 1;
    public const TYPE_WEEK = 2;
    public const TYPE_MONTH = 3;
    public const TYPE_INBOX = 4;
    public const TYPE_HALF_YEAR = 6;

    // @deprecated
    public const TYPE_PD_UPCOMING = 5;

    protected int $limit_events = -1;
    protected array $schedule = array();
    protected string $timezone = ilTimeZone::UTC;
    protected int $weekstart;
    protected int $type = 0;

    protected bool $subitems_enabled = false;

    protected ?ilDate $start = null;
    protected ?ilDate $end = null;
    protected ilObjUser $user;
    protected ilCalendarUserSettings $user_settings;
    protected ?ilDBInterface $db;
    protected array $filters = array();

    /**
     * @var bool strict_period true if no extra range of days are needed. (e.g. month view needs days before and after)
     */
    protected bool $strict_period = true;
    protected ilLogger $logger;

    public function __construct(ilDate $seed, int $a_type, ?int $a_user_id = null, bool $a_strict_period = false)
    {
        global $DIC;

        $this->logger = $DIC->logger()->cal();
        $this->db = $DIC->database();
        $this->type = $a_type;

        //this strict period is just to avoid possible side effects.
        //I there are none, we can get rid of this strict period control and remove it from the constructor
        //and from the calls in ilCalendarView getEvents.
        $this->strict_period = $a_strict_period;

        $this->user = $DIC->user();
        if ($a_user_id !== null && $a_user_id !== $DIC->user()->getId()) {
            $this->user = new ilObjUser($a_user_id);
        }
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $this->weekstart = $this->user_settings->getWeekStart();
        if ($this->user->getTimeZone()) {
            $this->timezone = $this->user->getTimeZone();
        }
        $this->initPeriod($seed);

        // portfolio does custom filter handling (booking group ids)
        if (ilCalendarCategories::_getInstance()->getMode() != ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION) {
            // consultation hour calendar views do not mind calendar category visibility
            if (ilCalendarCategories::_getInstance()->getMode() != ilCalendarCategories::MODE_CONSULTATION) {
                // this is the "default" filter which handles currently hidden categories for the user
                $this->addFilter(new ilCalendarScheduleFilterHidden($this->user->getId()));
            } else {
                // handle booking visibility (target object, booked out)
                //this filter deals with consultation hours
                $this->addFilter(new ilCalendarScheduleFilterBookings($this->user->getId()));
            }

            if (ilCalendarCategories::_getInstance()->getMode() === ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP) {
                //this filter deals with booking pool reservations
                $this->addFilter(new ilCalendarScheduleFilterBookingPool($this->user->getId()));
            }
            $this->addFilter(new ilCalendarScheduleFilterExercise($this->user->getId()));
            $this->addFilter(new ilCalendarScheduleFilterTimings($this->user->getId()));
        }
    }

    /**
     * Check if events are limited
     */
    protected function areEventsLimited(): bool
    {
        return $this->limit_events != -1;
    }

    public function getEventsLimit(): int
    {
        return $this->limit_events;
    }

    public function setEventsLimit(int $a_limit): void
    {
        $this->limit_events = $a_limit;
    }

    public function addSubitemCalendars(bool $a_status): void
    {
        $this->subitems_enabled = $a_status;
    }

    public function enabledSubitemCalendars(): bool
    {
        return $this->subitems_enabled;
    }

    public function addFilter(ilCalendarScheduleFilter $a_filter): void
    {
        $this->filters[] = $a_filter;
    }

    public function getByDay(ilDate $a_start, string $a_timezone): array
    {
        $start = new ilDateTime($a_start->get(IL_CAL_DATETIME), IL_CAL_DATETIME, $this->timezone);
        $fstart = new ilDate($a_start->get(IL_CAL_UNIX), IL_CAL_UNIX);
        $fend = clone $fstart;

        $f_unix_start = $fstart->get(IL_CAL_UNIX);
        $fend->increment(ilDateTime::DAY, 1);
        $f_unix_end = $fend->get(IL_CAL_UNIX);

        $unix_start = $start->get(IL_CAL_UNIX);
        $start->increment(ilDateTime::DAY, 1);
        $unix_end = $start->get(IL_CAL_UNIX);

        $counter = 0;

        $tmp_date = new ilDateTime($unix_start, IL_CAL_UNIX, $this->timezone);
        $tmp_schedule = array();
        $tmp_schedule_fullday = array();
        foreach ($this->schedule as $schedule) {
            if ($schedule['fullday']) {
                if (($f_unix_start == $schedule['dstart']) or
                    $f_unix_start == $schedule['dend'] or
                    ($f_unix_start > $schedule['dstart'] and $f_unix_end <= $schedule['dend'])) {
                    $tmp_schedule_fullday[] = $schedule;
                }
            } elseif (($schedule['dstart'] == $unix_start) or
                (($schedule['dstart'] <= $unix_start) and ($schedule['dend'] > $unix_start)) or
                (($schedule['dstart'] >= $unix_start) and ($schedule['dstart'] < $unix_end))) {
                $tmp_schedule[] = $schedule;
            }
        }

        //order non full day events by starting date;
        usort($tmp_schedule, function ($a, $b) {
            return $a['dstart'] <=> $b['dstart'];
        });

        //merge both arrays keeping the full day events first and then rest ordered by starting date.
        return array_merge($tmp_schedule_fullday, $tmp_schedule);
    }

    public function calculate(): void
    {
        $events = $this->getEvents();

        // we need category type for booking handling
        $ids = array();
        foreach ($events as $event) {
            $ids[] = $event->getEntryId();
        }

        $cat_map = ilCalendarCategoryAssignments::_getAppointmentCalendars($ids);
        $cat_types = array();
        foreach (array_unique($cat_map) as $cat_id) {
            $cat = new ilCalendarCategory($cat_id);
            $cat_types[$cat_id] = $cat->getType();
        }

        $counter = 0;
        foreach ($events as $event) {
            // Calculdate recurring events
            if ($recs = ilCalendarRecurrences::_getRecurrences($event->getEntryId())) {
                $duration = $event->getEnd()->get(IL_CAL_UNIX) - $event->getStart()->get(IL_CAL_UNIX);
                foreach ($recs as $rec) {
                    $calc = new ilCalendarRecurrenceCalculator($event, $rec);
                    foreach ($calc->calculateDateList($this->start, $this->end)->get() as $rec_date) {
                        if ($this->type == self::TYPE_PD_UPCOMING &&
                            $rec_date->get(IL_CAL_UNIX) < time()) {
                            continue;
                        }

                        $this->schedule[$counter]['event'] = $event;
                        $this->schedule[$counter]['dstart'] = $rec_date->get(IL_CAL_UNIX);
                        $this->schedule[$counter]['dend'] = $this->schedule[$counter]['dstart'] + $duration;
                        $this->schedule[$counter]['fullday'] = $event->isFullday();
                        $this->schedule[$counter]['category_id'] = $cat_map[$event->getEntryId()];
                        $this->schedule[$counter]['category_type'] = $cat_types[$cat_map[$event->getEntryId()]];

                        switch ($this->type) {
                            case self::TYPE_DAY:
                            case self::TYPE_WEEK:
                                // store date info (used for calculation of overlapping events)
                                $tmp_date = new ilDateTime(
                                    $this->schedule[$counter]['dstart'],
                                    IL_CAL_UNIX,
                                    $this->timezone
                                );
                                $this->schedule[$counter]['start_info'] = $tmp_date->get(
                                    IL_CAL_FKT_GETDATE,
                                    '',
                                    $this->timezone
                                );

                                $tmp_date = new ilDateTime(
                                    $this->schedule[$counter]['dend'],
                                    IL_CAL_UNIX,
                                    $this->timezone
                                );
                                $this->schedule[$counter]['end_info'] = $tmp_date->get(
                                    IL_CAL_FKT_GETDATE,
                                    '',
                                    $this->timezone
                                );
                                break;

                            default:
                                break;
                        }
                        $counter++;
                        if ($this->type != self::TYPE_PD_UPCOMING &&
                            $this->areEventsLimited() && $counter >= $this->getEventsLimit()) {
                            break;
                        }
                    }
                }
            } else {
                $this->schedule[$counter]['event'] = $event;
                $this->schedule[$counter]['dstart'] = $event->getStart()->get(IL_CAL_UNIX);
                $this->schedule[$counter]['dend'] = $event->getEnd()->get(IL_CAL_UNIX);
                $this->schedule[$counter]['fullday'] = $event->isFullday();
                $this->schedule[$counter]['category_id'] = $cat_map[$event->getEntryId()];
                $this->schedule[$counter]['category_type'] = $cat_types[$cat_map[$event->getEntryId()]];

                if (!$event->isFullday()) {
                    switch ($this->type) {
                        case self::TYPE_DAY:
                        case self::TYPE_WEEK:
                            // store date info (used for calculation of overlapping events)
                            $tmp_date = new ilDateTime(
                                $this->schedule[$counter]['dstart'],
                                IL_CAL_UNIX,
                                $this->timezone
                            );
                            $this->schedule[$counter]['start_info'] = $tmp_date->get(
                                IL_CAL_FKT_GETDATE,
                                '',
                                $this->timezone
                            );

                            $tmp_date = new ilDateTime($this->schedule[$counter]['dend'], IL_CAL_UNIX, $this->timezone);
                            $this->schedule[$counter]['end_info'] = $tmp_date->get(
                                IL_CAL_FKT_GETDATE,
                                '',
                                $this->timezone
                            );
                            break;

                        default:
                            break;
                    }
                }
                $counter++;
                if ($this->type != self::TYPE_PD_UPCOMING &&
                    $this->areEventsLimited() && $counter >= $this->getEventsLimit()) {
                    break;
                }
            }
        }

        if ($this->type == self::TYPE_PD_UPCOMING) {
            $this->schedule = ilArrayUtil::sortArray($this->schedule, "dstart", "asc", true);
            if ($this->areEventsLimited() && sizeof($this->schedule) >= $this->getEventsLimit()) {
                $this->schedule = array_slice($this->schedule, 0, $this->getEventsLimit());
            }
        }
    }

    public function getScheduledEvents(): array
    {
        return $this->schedule;
    }

    protected function filterCategories(array $a_cats): array
    {
        if (!count($a_cats)) {
            return $a_cats;
        }
        foreach ($this->filters as $filter) {
            $a_cats = $filter->filterCategories($a_cats);
        }
        return $a_cats;
    }

    protected function modifyEventByFilters(ilCalendarEntry $event): ?ilCalendarEntry
    {
        foreach ($this->filters as $filter) {
            $res = $filter->modifyEvent($event);
            if ($res === false) {
                $this->logger->notice('filtering failed for ' . get_class($filter));
                return null;
            }
            if (is_null($res)) {    // see #35241
                return null;
            }
            $event = $res;
        }
        return $event;
    }

    protected function addCustomEvents(ilDate $start, ilDate $end, array $categories): array
    {
        $new_events = array();
        foreach ($this->filters as $filter) {
            $events_by_filter = $filter->addCustomEvents($start, $end, $categories);
            if ($events_by_filter) {
                $new_events = array_merge($new_events, $events_by_filter);
            }
        }
        return $new_events;
    }

    /**
     * get new/changed events
     * @param bool $a_include_subitem_calendars E.g include session calendars of courses.
     * @return object $events[] Array of changed events
     * @access protected
     * @return
     */
    public function getChangedEvents(bool $a_include_subitem_calendars = false): array
    {
        $cats = ilCalendarCategories::_getInstance($this->user->getId())->getCategories($a_include_subitem_calendars);
        $cats = $this->filterCategories($cats);

        if (!count($cats)) {
            return array();
        }

        $start = new ilDate(date('Y-m-d', time()), IL_CAL_DATE);
        $start->increment(IL_CAL_MONTH, -1);

        $query = "SELECT ce.cal_id cal_id FROM cal_entries ce  " .
            "JOIN cal_cat_assignments ca ON ca.cal_id = ce.cal_id " .
            "WHERE last_update > " . $this->db->quote($start->get(IL_CAL_DATETIME), 'timestamp') . " " .
            "AND " . $this->db->in('ca.cat_id', $cats, false, 'integer') . ' ' .
            "ORDER BY last_update";
        $res = $this->db->query($query);

        $events = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event = new ilCalendarEntry($row->cal_id);
            $valid_event = $this->modifyEventByFilters($event);
            if ($valid_event) {
                $events[] = $valid_event;
            }
        }

        foreach ($this->addCustomEvents($this->start, $this->end, $cats) as $event) {
            $events[] = $event;
        }

        return $events;
    }

    /**
     * Read events (will be moved to another class, since only active and/or visible calendars are shown)
     */
    public function getEvents(): array
    {
        $cats = ilCalendarCategories::_getInstance($this->user->getId())->getCategories($this->enabledSubitemCalendars());
        $cats = $this->filterCategories($cats);

        if (!count($cats)) {
            return array();
        }

        // TODO: optimize
        $query = "SELECT ce.cal_id cal_id" .
            " FROM cal_entries ce" .
            " LEFT JOIN cal_recurrence_rules crr ON (ce.cal_id = crr.cal_id)" .
            " JOIN cal_cat_assignments ca ON (ca.cal_id = ce.cal_id)";

        if ($this->type != self::TYPE_INBOX) {
            $query .= " WHERE ((starta <= " . $this->db->quote(
                $this->end->get(IL_CAL_DATETIME, '', 'UTC'),
                'timestamp'
            ) .
                " AND enda >= " . $this->db->quote($this->start->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ")" .
                " OR (starta <= " . $this->db->quote($this->end->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') .
                " AND NOT rule_id IS NULL))";
        } else {
            $date = new ilDateTime(mktime(0, 0, 0), IL_CAL_UNIX);
            $query .= " WHERE starta >= " . $this->db->quote($date->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp');
        }

        $query .= " AND " . $this->db->in('ca.cat_id', $cats, false, 'integer') .
            " ORDER BY starta";

        $res = $this->db->query($query);

        $events = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event = new ilCalendarEntry((int) $row->cal_id);
            $valid_event = $this->modifyEventByFilters($event);
            if ($valid_event) {
                $events[] = $valid_event;
            }
        }
        foreach ($this->addCustomEvents($this->start, $this->end, $cats) as $event) {
            $events[] = $event;
        }
        return $events;
    }

    protected function initPeriod(ilDate $seed): void
    {
        switch ($this->type) {
            case self::TYPE_DAY:
                $this->start = clone $seed;
                $this->end = clone $seed;
                //this strict period is just to avoid possible side effects.
                if (!$this->strict_period) {
                    $this->start->increment(IL_CAL_DAY, -2);
                    $this->end->increment(IL_CAL_DAY, 2);
                } else {
                    $this->end->increment(IL_CAL_DAY, 1);
                    $this->end->increment(IL_CAL_SECOND, -1);
                }
                break;

            case self::TYPE_WEEK:
                $this->start = clone $seed;
                $start_info = $this->start->get(IL_CAL_FKT_GETDATE, '', 'UTC');
                $day_diff = $this->weekstart - $start_info['isoday'];

                if ($day_diff == 7) {
                    $day_diff = 0;
                }

                //this strict period is just to avoid possible side effects.
                if ($this->strict_period) {
                    $this->start->increment(IL_CAL_DAY, $day_diff);
                    $this->end = clone $this->start;
                    $this->end->increment(IL_CAL_WEEK); #22173
                } else {
                    $this->start->increment(IL_CAL_DAY, $day_diff);
                    $this->start->increment(IL_CAL_DAY, -1);
                    $this->end = clone $this->start;
                    $this->end->increment(IL_CAL_DAY, 9);
                }
                break;

            case self::TYPE_MONTH:
                if ($this->strict_period) {
                    $this->start = clone $seed;
                    $this->end = clone $seed;
                    $this->end->increment(IL_CAL_MONTH, 1);
                } else {
                    $year_month = $seed->get(IL_CAL_FKT_DATE, 'Y-m', 'UTC');
                    list($year, $month) = explode('-', $year_month);
                    $year = (int) $year;
                    $month = (int) $month;

                    #21716
                    $this->start = new ilDate($year_month . '-01', IL_CAL_DATE);

                    $start_unix_time = $this->start->getUnixTime();

                    $start_day_of_week = (int) date('w', $start_unix_time);

                    $number_days_previous_month = 0;

                    if ($start_day_of_week === 0 && $this->weekstart === ilCalendarSettings::WEEK_START_MONDAY) {
                        $number_days_previous_month = 6;
                    } elseif ($start_day_of_week > 0) {
                        $number_days_previous_month = $start_day_of_week;

                        if ($this->weekstart === ilCalendarSettings::WEEK_START_MONDAY) {
                            $number_days_previous_month = $start_day_of_week - 1;
                        }
                    }

                    $this->start->increment(IL_CAL_DAY, -$number_days_previous_month);

                    #21716
                    $this->end = new ilDate(
                        $year_month . '-' . ilCalendarUtil::_getMaxDayOfMonth($year, $month),
                        IL_CAL_DATE
                    );

                    $end_unix_time = $this->end->getUnixTime();

                    $end_day_of_week = (int) date('w', $end_unix_time);

                    if ($end_day_of_week > 0) {
                        $number_days_next_month = 7 - $end_day_of_week;

                        if ($this->weekstart == ilCalendarSettings::WEEK_START_SUNDAY) {
                            $number_days_next_month = $number_days_next_month - 1;
                        }

                        $this->end->increment(IL_CAL_DAY, $number_days_next_month);
                    }
                }

                break;

            case self::TYPE_HALF_YEAR:
                $this->start = clone $seed;
                $this->end = clone $this->start;
                $this->end->increment(IL_CAL_MONTH, 6);
                break;

            case self::TYPE_PD_UPCOMING:
            case self::TYPE_INBOX:
                $this->start = $seed;
                $this->end = clone $this->start;
                $this->end->increment(IL_CAL_MONTH, 12);
                break;
        }
    }

    public function setPeriod(ilDate $a_start, ilDate $a_end): void
    {
        $this->start = $a_start;
        $this->end = $a_end;
    }
}
