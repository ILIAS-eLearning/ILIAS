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
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarRecurrenceExclusion
{
    protected $exclusion = null;
    protected $cal_id = 0;
    protected $exclusion_id = 0;

    protected $db = null;

    /**
     * Constructor
     * @return
     */
    public function __construct($a_exclusion_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->exclusion_id = $a_exclusion_id;

        if ($this->getId()) {
            $this->read();
        }
    }

    /**
     * Get exclusion id
     * @return
     */
    public function getId()
    {
        return $this->exclusion_id;
    }

    /**
     * Get calendar entry id
     * @return
     */
    public function getEntryId()
    {
        return $this->cal_id;
    }

    /**
     * Set entry id (id of calendar appointment)
     * @return
     */
    public function setEntryId($a_id)
    {
        $this->cal_id = $a_id;
    }

    /**
     * Get exclusion date
     * @return
     */
    public function getDate()
    {
        return $this->exclusion instanceof ilDate ? $this->exclusion : null;
    }

    /**
     * Set exclusion date
     * @param ilDate $dt [optional]
     * @return
     */
    public function setDate(ilDate $dt = null)
    {
        $this->exclusion = $dt;
    }

    /**
     * Exclusion date to ical format
     * @return
     */
    public function toICal()
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

    /**
     * Save exclusion date to db
     * @return
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$this->getDate()) {
            return false;
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

    /**
     * Read exclusion
     * @return
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM cal_rec_exclusion WHERE excl_id = " . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->cal_id = $row->cal_id;
            $this->setDate(new ilDate($row->excl_date, IL_CAL_DATE, 'UTC'));
        }
    }
}
