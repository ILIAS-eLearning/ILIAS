<?php
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
 * Stores exclusion dates for calendar recurrences
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarRecurrenceExclusion
{
    protected ?ilDate $exclusion = null;
    protected int $cal_id = 0;
    protected int $exclusion_id = 0;

    protected ?ilDBInterface $db;

    public function __construct(int $a_exclusion_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->exclusion_id = $a_exclusion_id;
        if ($this->getId()) {
            $this->read();
        }
    }

    public function getId(): int
    {
        return $this->exclusion_id;
    }

    public function getEntryId(): int
    {
        return $this->cal_id;
    }

    public function setEntryId(int $a_id)
    {
        $this->cal_id = $a_id;
    }

    public function getDate(): ?ilDate
    {
        return $this->exclusion instanceof ilDate ? $this->exclusion : null;
    }

    /**
     * Set exclusion date
     */
    public function setDate(?ilDate $dt = null): void
    {
        $this->exclusion = $dt;
    }

    public function toICal(): string
    {
        $entry = new ilCalendarEntry($this->getEntryId());
        $start = $entry->getStart();

        if ($entry->isFullday()) {
            return 'EXDATE;VALUE=DATE:' . $this->getDate()->get(IL_CAL_FKT_DATE, 'Ymd');
        } else {
            return 'EXDATE:' .
                $this->getDate()->get(IL_CAL_FKT_DATE, 'Ymd', ilTimeZone::UTC) .
                'T' . $start->get(IL_CAL_FKT_DATE, 'His', ilTimeZone::UTC) . 'Z';
        }
    }

    public function save(): int
    {
        if (!$this->getDate()) {
            return 0;
        }

        $query = "INSERT INTO cal_rec_exclusion (excl_id,cal_id,excl_date) " .
            "VALUES( " .
            $this->db->quote($next_id = $this->db->nextId('cal_rec_exclusion'), 'integer') . ', ' .
            $this->db->quote($this->getEntryId(), 'integer') . ', ' .
            $this->db->quote($this->getDate()->get(IL_CAL_DATE, '', 'UTC'), 'timestamp') .
            ')';
        $this->db->manipulate($query);

        $this->exclusion_id = $next_id;
        return $this->getId();
    }

    protected function read()
    {
        $query = "SELECT * FROM cal_rec_exclusion WHERE excl_id = " . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->cal_id = $row->cal_id;
            $this->setDate(new ilDate((string) $row->excl_date, IL_CAL_DATE));
        }
    }
}
