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
 * Model of calendar entry recurrcences
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarRecurrence implements ilCalendarRecurrenceCalculation
{
    protected const REC_RECURRENCE = 0;
    protected const REC_EXCLUSION = 1;

    public const FREQ_NONE = 'NONE';
    public const FREQ_DAILY = 'DAILY';
    public const FREQ_WEEKLY = 'WEEKLY';
    public const FREQ_MONTHLY = 'MONTHLY';
    public const FREQ_YEARLY = 'YEARLY';

    protected ilDBInterface $db;

    private int $recurrence_id = 0;
    private int $cal_id = 0;
    private int $recurrence_type = 0;

    private string $freq_type = '';
    private string $freq_until_type = '';
    private ?ilDate $freq_until_date = null;
    private int $freq_until_count = 0;

    private int $interval = 1;
    private string $byday = '';
    private string $byweekno = '';
    private string $bymonth = '';
    private string $bymonthday = '';
    private string $byyearday = '';
    private string $bysetpos = '';
    private string $weekstart = '';

    private array $exclusion_dates = array();

    private string $timezone = 'Europe/Berlin';

    public function __construct(int $a_rec_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->recurrence_id = $a_rec_id;
        if ($a_rec_id) {
            $this->read();
        }
    }

    public static function _delete(int $a_cal_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_recurrence_rules " .
            "WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        ilCalendarRecurrenceExclusions::delete($a_cal_id);
    }

    /**
     * Get ical presentation for calendar recurrence
     */
    public function toICal(int $a_user_id): string
    {
        $entry = new ilCalendarEntry($this->getEntryId());

        if (!$this->getFrequenceType()) {
            return '';
        }

        $ical = 'RRULE:';
        $ical .= ('FREQ=' . $this->getFrequenceType());

        if ($this->getInterval()) {
            $ical .= (';INTERVAL=' . $this->getInterval());
        }
        if ($this->getFrequenceUntilCount()) {
            $ical .= (';COUNT=' . $this->getFrequenceUntilCount());
        } elseif ($this->getFrequenceUntilDate()) {
            if ($entry->isFullday()) {
                $ical .= (';UNTIL=' . $this->getFrequenceUntilDate()->get(IL_CAL_FKT_DATE, 'Ymd'));
            } else {
                $his = $entry->getStart()->get(IL_CAL_FKT_DATE, 'His');
                $ical .= (';UNTIL=' . $this->getFrequenceUntilDate()->get(IL_CAL_FKT_DATE, 'Ymd') . 'T' . $his);
            }
        }
        if ($this->getBYMONTH()) {
            $ical .= (';BYMONTH=' . $this->getBYMONTH());
        }
        if ($this->getBYWEEKNO()) {
            $ical .= (';BYWEEKNO=' . $this->getBYWEEKNO());
        }
        if ($this->getBYYEARDAY()) {
            $ical .= (';BYYEARDAY=' . $this->getBYYEARDAY());
        }
        if ($this->getBYMONTHDAY()) {
            $ical .= (';BYMONTHDAY=' . $this->getBYMONTHDAY());
        }
        if ($this->getBYDAY()) {
            $ical .= (';BYDAY=' . $this->getBYDAY());
        }
        if ($this->getBYSETPOS()) {
            $ical .= (';BYSETPOS=' . $this->getBYSETPOS());
        }

        // Required in outlook
        if ($this->getBYDAY()) {
            $us = ilCalendarUserSettings::_getInstanceByUserId($a_user_id);
            if ($us->getWeekStart() == ilCalendarSettings::WEEK_START_MONDAY) {
                $ical .= (';WKST=MO');
            } else {
                $ical .= (';WKST=SU');
            }
        }

        return $ical;
    }

    /**
     * reset all settings
     */
    public function reset(): void
    {
        $this->setBYDAY('');
        $this->setBYMONTHDAY('');
        $this->setBYMONTH('');
        $this->setBYSETPOS('');
        $this->setBYWEEKNO('');
        $this->setBYYEARDAY('');
        $this->setFrequenceType('');
        $this->setInterval(1);
        $this->setFrequenceUntilCount(0);
    }

    public function getRecurrenceId(): int
    {
        return $this->recurrence_id;
    }

    public function setEntryId(int $a_id): void
    {
        $this->cal_id = $a_id;
    }

    public function getEntryId(): int
    {
        return $this->cal_id;
    }

    /**
     * set type of recurrence
     * @access public
     * @param int REC_RECURRENCE or REC_EXLUSION defines whther the current object is a recurrence an exclusion pattern
     */
    public function setRecurrence(int $a_type): void
    {
        $this->recurrence_type = $a_type;
    }

    public function isRecurrence(): bool
    {
        return $this->recurrence_type == self::REC_RECURRENCE;
    }

    public function setFrequenceType(string $a_type): void
    {
        $this->freq_type = $a_type;
    }

    public function getFrequenceType(): string
    {
        return $this->freq_type;
    }

    public function getFrequenceUntilDate(): ?ilDate
    {
        return is_object($this->freq_until_date) ? $this->freq_until_date : null;
    }

    public function setFrequenceUntilDate(ilDateTime $a_date = null): void
    {
        $this->freq_until_date = $a_date;
    }

    public function setFrequenceUntilCount(int $a_count): void
    {
        $this->freq_until_count = $a_count;
    }

    public function getFrequenceUntilCount(): int
    {
        return $this->freq_until_count;
    }

    public function setInterval(int $a_interval): void
    {
        $this->interval = $a_interval;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setBYDAY(string $a_byday): void
    {
        $this->byday = $a_byday;
    }

    public function getBYDAY(): string
    {
        return $this->byday;
    }

    /**
     * @inheritDoc
     */
    public function getBYDAYList(): array
    {
        if (!trim($this->getBYDAY())) {
            return array();
        }
        $bydays = [];
        foreach (explode(',', $this->getBYDAY()) as $byday) {
            $bydays[] = trim($byday);
        }
        return $bydays;
    }

    public function setBYWEEKNO(string $a_byweekno): void
    {
        $this->byweekno = $a_byweekno;
    }

    public function getBYWEEKNOList(): array
    {
        if (!trim($this->getBYWEEKNO())) {
            return array();
        }
        $weeks = [];
        foreach (explode(',', $this->getBYWEEKNO()) as $week_num) {
            $weeks[] = (int) $week_num;
        }
        return $weeks;
    }

    public function getBYWEEKNO(): string
    {
        return $this->byweekno;
    }

    public function setBYMONTH(string $a_by): void
    {
        $this->bymonth = $a_by;
    }

    public function getBYMONTH(): string
    {
        return $this->bymonth;
    }

    public function getBYMONTHList(): array
    {
        if (!trim($this->getBYMONTH())) {
            return array();
        }
        $months = [];
        foreach (explode(',', $this->getBYMONTH()) as $month_num) {
            $months[] = (int) $month_num;
        }
        return $months;
    }

    public function setBYMONTHDAY(string $a_by): void
    {
        $this->bymonthday = $a_by;
    }

    public function getBYMONTHDAY(): string
    {
        return $this->bymonthday;
    }

    public function getBYMONTHDAYList(): array
    {
        if (!trim($this->getBYMONTHDAY())) {
            return array();
        }
        $month = [];
        foreach (explode(',', $this->getBYMONTHDAY()) as $month_num) {
            $month[] = (int) $month_num;
        }
        return $month;
    }

    public function setBYYEARDAY(string $a_by): void
    {
        $this->byyearday = $a_by;
    }

    public function getBYYEARDAY(): string
    {
        return $this->byyearday;
    }

    public function getBYYEARDAYList(): array
    {
        if (!trim($this->getBYYEARDAY())) {
            return array();
        }
        $days = [];
        foreach (explode(',', $this->getBYYEARDAY()) as $year_day) {
            $days[] = (int) $year_day;
        }
        return $days;
    }

    public function setBYSETPOS(string $a_by): void
    {
        $this->bysetpos = $a_by;
    }

    public function getBYSETPOS(): string
    {
        return $this->bysetpos;
    }

    public function getBYSETPOSList(): array
    {
        if (!trim($this->getBYSETPOS())) {
            return array();
        }
        $positions = [];
        foreach (explode(',', $this->getBYSETPOS()) as $pos) {
            $positions[] = (int) $pos;
        }
        return $positions;
    }

    public function setWeekstart(string $a_start): void
    {
        $this->weekstart = $a_start;
    }

    public function getWeekstart(): string
    {
        return $this->weekstart;
    }

    public function getTimeZone(): string
    {
        return $this->timezone;
    }

    public function setTimeZone(string $a_tz): void
    {
        $this->timezone = $a_tz;
    }

    /**
     * @return ilCalendarRecurrenceExclusion[]
     */
    public function getExclusionDates(): array
    {
        return $this->exclusion_dates;
    }

    /**
     * validate
     */
    public function validate(): bool
    {
        $valid_frequences = array(self::FREQ_DAILY,
                                  self::FREQ_WEEKLY,
                                  self::FREQ_MONTHLY,
                                  self::FREQ_YEARLY
        );
        if (!in_array($this->getFrequenceType(), $valid_frequences)) {
            return false;
        }
        if ($this->getFrequenceUntilCount() < 0) {
            return false;
        }
        if ($this->getInterval() <= 0) {
            return false;
        }
        return true;
    }

    public function save(): void
    {
        $until_date = is_null($this->getFrequenceUntilDate()) ?
            null :
            $this->getFrequenceUntilDate()->get(IL_CAL_DATETIME, '', 'UTC');
        $next_id = $this->db->nextId('cal_recurrence_rules');

        $query = "INSERT INTO cal_recurrence_rules (rule_id,cal_id,cal_recurrence,freq_type,freq_until_date,freq_until_count,intervall, " .
            "byday,byweekno,bymonth,bymonthday,byyearday,bysetpos,weekstart) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->cal_id, 'integer') . ", " .
            $this->db->quote(1, 'integer') . ", " .
            $this->db->quote($this->getFrequenceType(), 'text') . ", " .
            $this->db->quote($until_date, 'timestamp') . ", " .
            $this->db->quote($this->getFrequenceUntilCount(), 'integer') . ", " .
            $this->db->quote($this->getInterval(), 'integer') . ", " .
            $this->db->quote($this->getBYDAY(), 'text') . ", " .
            $this->db->quote($this->getBYWEEKNO(), 'text') . ", " .
            $this->db->quote($this->getBYMONTH(), 'text') . ", " .
            $this->db->quote($this->getBYMONTHDAY(), 'text') . ", " .
            $this->db->quote($this->getBYYEARDAY(), 'text') . ", " .
            $this->db->quote($this->getBYSETPOS(), 'text') . ", " .
            $this->db->quote($this->getWeekstart(), 'text') . " " .
            ")";
        $res = $this->db->manipulate($query);
        $this->recurrence_id = $next_id;
    }

    public function update(): void
    {
        $until_date = is_null($this->getFrequenceUntilDate()) ?
            null :
            $this->getFrequenceUntilDate()->get(IL_CAL_DATETIME, '', 'UTC');

        $query = "UPDATE cal_recurrence_rules SET " .
            "cal_id = " . $this->db->quote($this->cal_id, 'integer') . ", " .
            "cal_recurrence = 1," .
            "freq_type = " . $this->db->quote($this->getFrequenceType(), 'text') . ", " .
            "freq_until_date = " . $this->db->quote($until_date, 'timestamp') . ", " .
            "freq_until_count = " . $this->db->quote($this->getFrequenceUntilCount(), 'integer') . ", " .
            "intervall = " . $this->db->quote($this->getInterval(), 'integer') . ", " .
            "byday = " . $this->db->quote($this->getBYDAY(), 'text') . ", " .
            "byweekno = " . $this->db->quote($this->getBYWEEKNO(), 'text') . ", " .
            "bymonth = " . $this->db->quote($this->getBYMONTH(), 'text') . ", " .
            "bymonthday = " . $this->db->quote($this->getBYMONTHDAY(), 'text') . ", " .
            "byyearday = " . $this->db->quote($this->getBYYEARDAY(), 'text') . ", " .
            "bysetpos = " . $this->db->quote($this->getBYSETPOS(), 'text') . ", " .
            "weekstart = " . $this->db->quote($this->getWeekstart(), 'text') . " " .
            "WHERE rule_id = " . $this->db->quote($this->recurrence_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function delete(): void
    {
        $query = "DELETE FROM cal_recurrence_rules " .
            "WHERE rule_id = " . $this->db->quote($this->recurrence_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    private function read(): void
    {
        $query = "SELECT * FROM cal_recurrence_rules " .
            "WHERE rule_id = " . $this->db->quote($this->recurrence_id, 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->cal_id = (int) $row->cal_id;
            $this->recurrence_type = (int) $row->cal_recurrence;
            $this->freq_type = (string) $row->freq_type;

            if ($row->freq_until_date != null) {
                $this->freq_until_date = new ilDate($row->freq_until_date, IL_CAL_DATETIME);
            }
            $this->freq_until_count = (int) $row->freq_until_count;
            $this->interval = (int) $row->intervall;
            $this->byday = (string) $row->byday;
            $this->byweekno = (string) $row->byweekno;
            $this->bymonth = (string) $row->bymonth;
            $this->bymonthday = (string) $row->bymonthday;
            $this->byyearday = (string) $row->byyearday;
            $this->bysetpos = (string) $row->bysetpos;
            $this->weekstart = (string) $row->weekstart;
        }

        $this->exclusion_dates = ilCalendarRecurrenceExclusions::getExclusionDates($this->cal_id);
    }
}
