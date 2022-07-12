<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
* class ilSessionAppointment
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilSessionAppointment implements ilDatePeriod
{
    protected ilErrorHandling $ilErr;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilLanguage $lng;
    protected ?ilDateTime $start = null;
    protected ?ilDateTime $end = null;
    protected int $starting_time = 0;
    protected int $ending_time = 0;
    protected bool $fulltime = false;
    protected int $appointment_id = 0;
    protected int $session_id = 0;

    public function __construct(int $a_appointment_id = 0)
    {
        global $DIC;

        $this->ilErr = $DIC['ilErr'];
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();

        $this->appointment_id = $a_appointment_id;
        $this->__read();
    }

    public static function _lookupAppointment(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM event_appointment " .
            "WHERE event_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $info['fullday'] = $row->fulltime;
            
            $date = new ilDateTime($row->e_start, IL_CAL_DATETIME, 'UTC');
            $info['start'] = $date->getUnixTime();
            $date = new ilDateTime($row->e_end, IL_CAL_DATETIME, 'UTC');
            $info['end'] = $date->getUnixTime();
            
            return $info;
        }
        return [];
    }

    /**
     * @return array|bool
     */
    public static function lookupNextSessionByCourse(int $a_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        
        
        $sessions = $tree->getChildsByType($a_ref_id, 'sess');
        $obj_ids = [];
        foreach ($sessions as $tree_data) {
            $obj_ids[] = $tree_data['obj_id'];
        }
        if (!count($obj_ids)) {
            return false;
        }

        // Try to read the next sessions within the next 24 hours
        $now = new ilDate(time(), IL_CAL_UNIX);
        $tomorrow = clone $now;
        $tomorrow->increment(IL_CAL_DAY, 2);
        
        $query = "SELECT event_id FROM event_appointment " .
            "WHERE e_start > " . $ilDB->quote($now->get(IL_CAL_DATE), 'timestamp') . ' ' .
            "AND e_start < " . $ilDB->quote($tomorrow->get(IL_CAL_DATE), 'timestamp') . ' ' .
            "AND " . $ilDB->in('event_id', $obj_ids, false, 'integer') . ' ' .
            "ORDER BY e_start ";
            
        $event_ids = [];
            
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event_ids[] = $row->event_id;
        }
        
        if (count($event_ids)) {
            return $event_ids;
        }
        
        // Alternativ: get next event.
        $query = "SELECT event_id FROM event_appointment " .
            "WHERE e_start > " . $ilDB->now() . " " .
            "AND " . $ilDB->in('event_id', $obj_ids, false, 'integer') . " " .
            "ORDER BY e_start ";
        $ilDB->setLimit(1, 0);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event_id = $row->event_id;
        }
        return isset($event_id) ? [$event_id] : [];
    }

    /**
     * @return bool|int
     */
    public static function lookupLastSessionByCourse(int $a_ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilDB = $DIC->database();
        
        $sessions = $tree->getChildsByType($a_ref_id, 'sess');
        $obj_ids = [];
        foreach ($sessions as $tree_data) {
            $obj_ids[] = $tree_data['obj_id'];
        }
        if (!count($obj_ids)) {
            return false;
        }
        $query = "SELECT event_id FROM event_appointment " .
            "WHERE e_start < " . $ilDB->now() . " " .
            "AND " . $ilDB->in('event_id', $obj_ids, false, 'integer') . " " .
            "ORDER BY e_start DESC ";
        $ilDB->setLimit(1, 0);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $event_id = (int) $row->event_id;
        }
        return $event_id ?? 0;
    }

    public function isFullday() : bool
    {
        return $this->enabledFullTime();
    }

    public function getStart() : ?ilDateTime
    {
        return $this->start ?: $this->start = new ilDateTime(date('Y-m-d') . ' 08:00:00', IL_CAL_DATETIME);
    }

    public function setStart(ilDateTime $a_start) : void
    {
        $this->start = $a_start;
    }

    public function getEnd() : ?ilDateTime
    {
        return $this->end ?: $this->end = new ilDateTime(date('Y-m-d') . ' 16:00:00', IL_CAL_DATETIME);
    }

    public function setEnd(ilDateTime $a_end) : void
    {
        $this->end = $a_end;
    }

    public function setAppointmentId(int $a_appointment_id) : void
    {
        $this->appointment_id = $a_appointment_id;
    }

    public function getAppointmentId() : int
    {
        return $this->appointment_id;
    }

    public function setSessionId(int $a_session_id) : void
    {
        $this->session_id = $a_session_id;
    }
    public function getSessionId() : int
    {
        return $this->session_id;
    }

    public function setStartingTime(int $a_starting_time) : void
    {
        $this->starting_time = $a_starting_time;
        $this->start = new ilDateTime($this->starting_time, IL_CAL_UNIX);
    }

    public function getStartingTime() : int
    {
        return $this->starting_time ?? mktime(8, 0, 0, (int) date('n', time()), (int) date('j', time()), (int) date('Y', time()));
    }
    
    public function setEndingTime(int $a_ending_time) : void
    {
        $this->ending_time = $a_ending_time;
        $this->end = new ilDateTime($this->ending_time, IL_CAL_UNIX);
    }
    public function getEndingTime() : int
    {
        return $this->ending_time ?? mktime(16, 0, 0, (int) date('n', time()), (int) date('j', time()), (int) date('Y', time()));
    }

    public function toggleFullTime(bool $a_status) : void
    {
        $this->fulltime = $a_status;
    }
    public function enabledFullTime() : bool
    {
        return $this->fulltime;
    }

    public function formatTime() : string
    {
        return $this->timeToString($this->getStartingTime(), $this->getEndingTime());
    }

    public function timeToString(int $start, int $end) : string
    {
        $lng = $this->lng;

        $start = date($lng->txt('lang_timeformat_no_sec'), $start);
        $end = date($lng->txt('lang_timeformat_no_sec'), $end);
        
        return $start . ' - ' . $end;
    }

    public static function _appointmentToString(int $start, int $end, bool $fulltime) : string
    {
        global $DIC;

        $lng = $DIC->language();

        if ($fulltime) {
            return ilDatePresentation::formatPeriod(
                new ilDate($start, IL_CAL_UNIX),
                #new ilDate($end,IL_CAL_UNIX)).' ('.$lng->txt('event_full_time_info').')';
                new ilDate($end, IL_CAL_UNIX)
            );
        } else {
            return ilDatePresentation::formatPeriod(
                new ilDateTime($start, IL_CAL_UNIX),
                new ilDateTime($end, IL_CAL_UNIX)
            );
        }
    }

    public function appointmentToString() : string
    {
        return self::_appointmentToString($this->getStartingTime(), $this->getEndingTime(), $this->isFullday());
    }

    public function cloneObject(int $new_id) : self
    {
        $new_app = new ilSessionAppointment();
        $new_app->setSessionId($new_id);
        $new_app->setStartingTime($this->getStartingTime());
        $new_app->setEndingTime($this->getEndingTime());
        $new_app->toggleFullTime($this->enabledFullTime());
        $new_app->create();

        return $new_app;
    }

    public function create() : bool
    {
        $ilDB = $this->db;
        
        if (!$this->getSessionId()) {
            return false;
        }
        $next_id = $ilDB->nextId('event_appointment');
        $query = "INSERT INTO event_appointment (appointment_id,event_id,e_start,e_end,fulltime) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getSessionId(), 'integer') . ", " .
            $ilDB->quote($this->getStart()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            $ilDB->quote($this->getEnd()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            $ilDB->quote((int) $this->enabledFullTime(), 'integer') . " " .
            ")";
        $this->appointment_id = $next_id;
        $res = $ilDB->manipulate($query);
        
        return true;
    }

    public function update() : bool
    {
        $ilDB = $this->db;
        
        if (!$this->getSessionId()) {
            return false;
        }
        $query = "UPDATE event_appointment " .
            "SET event_id = " . $ilDB->quote($this->getSessionId(), 'integer') . ", " .
            "e_start = " . $ilDB->quote($this->getStart()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            "e_end = " . $ilDB->quote($this->getEnd()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            "fulltime = " . $ilDB->quote((int) $this->enabledFullTime(), 'integer') . " " .
            "WHERE appointment_id = " . $ilDB->quote($this->getAppointmentId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function delete() : bool
    {
        return self::_delete($this->getAppointmentId());
    }

    public static function _delete(int $a_appointment_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM event_appointment " .
            "WHERE appointment_id = " . $ilDB->quote($a_appointment_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public static function _deleteBySession(int $a_event_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM event_appointment " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public static function _readAppointmentsBySession(int $a_event_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_appointment " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer') . " " .
            "ORDER BY starting_time";

        $res = $ilDB->query($query);
        $appointments = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $appointments[] = new ilSessionAppointment((int) $row->appointment_id);
        }
        return $appointments;
    }
            
    public function validate() : bool
    {
        if ($this->starting_time > $this->ending_time) {
            $this->ilErr->appendMessage($this->lng->txt('event_etime_smaller_stime'));
            return false;
        }
        return true;
    }

    protected function __read() : ?bool
    {
        $ilDB = $this->db;
        
        if (!$this->getAppointmentId()) {
            return null;
        }

        $query = "SELECT * FROM event_appointment " .
            "WHERE appointment_id = " . $ilDB->quote($this->getAppointmentId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setSessionId((int) $row->event_id);
            $this->toggleFullTime((bool) $row->fulltime);
            
            if ($this->isFullday()) {
                $this->start = new ilDate($row->e_start, IL_CAL_DATETIME);
                $this->end = new ilDate($row->e_end, IL_CAL_DATETIME);
            } else {
                $this->start = new ilDateTime($row->e_start, IL_CAL_DATETIME, 'UTC');
                $this->end = new ilDateTime($row->e_end, IL_CAL_DATETIME, 'UTC');
            }
            $this->starting_time = (int) $this->start->getUnixTime();
            $this->ending_time = (int) $this->end->getUnixTime();
        }
        return true;
    }
}
