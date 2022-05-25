<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * registration for calendar appointments
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarRegistration
{
    private int $appointment_id = 0;
    private array $registered = array();

    protected ilDBInterface $db;

    public function __construct(int $a_appointment_id)
    {
        global $DIC;

        $this->appointment_id = $a_appointment_id;

        $this->db = $DIC->database();
        $this->read();
    }

    public static function deleteByUser(int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_registrations " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $ilDB->manipulate($query);
    }

    public static function deleteByAppointment(int $a_cal_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_registrations " .
            "WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer');
        $ilDB->manipulate($query);
    }

    public function getAppointmentId() : int
    {
        return $this->appointment_id;
    }

    public function getRegisteredUsers(\ilDateTime $start, \ilDateTime $end) : array
    {
        $users = [];
        foreach ($this->registered as $reg_data) {
            if (
                $reg_data['dstart'] == $start->get(IL_CAL_UNIX) &&
                $reg_data['dend'] == $end->get(IL_CAL_UNIX)
            ) {
                $users[] = (int) $reg_data['usr_id'];
            }
        }
        return $users;
    }

    public function isRegistered($a_usr_id, ilDateTime $start, ilDateTime $end) : bool
    {
        foreach ($this->registered as $reg_data) {
            if ($reg_data['usr_id'] == $a_usr_id) {
                if ($reg_data['dstart'] == $start->get(IL_CAL_UNIX) and $reg_data['dend'] == $end->get(IL_CAL_UNIX)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function register(int $a_usr_id, ilDateTime $start, ilDateTime $end) : void
    {
        $this->unregister($a_usr_id, $start, $end);

        $query = "INSERT INTO cal_registrations (cal_id,usr_id,dstart,dend) " .
            "VALUES ( " .
            $this->db->quote($this->getAppointmentId(), 'integer') . ", " .
            $this->db->quote($a_usr_id, 'integer') . ", " .
            $this->db->quote($start->get(IL_CAL_UNIX), 'integer') . ", " .
            $this->db->quote($end->get(IL_CAL_UNIX), 'integer') .
            ")";
        $this->db->manipulate($query);
        $this->registered[] = [
            'usr_id' => $a_usr_id,
            'dstart' => $start->get(IL_CAL_UNIX),
            'dend' => $end->get(IL_CAL_UNIX)
        ];
    }

    /**
     * unregister one user
     */
    public function unregister(int $a_usr_id, ilDateTime $start, ilDateTime $end) : void
    {
        $query = "DELETE FROM cal_registrations " .
            "WHERE cal_id = " . $this->db->quote($this->getAppointmentId(), 'integer') . ' ' .
            "AND usr_id = " . $this->db->quote($a_usr_id, 'integer') . ' ' .
            "AND dstart = " . $this->db->quote($start->get(IL_CAL_UNIX), 'integer') . ' ' .
            "AND dend = " . $this->db->quote($end->get(IL_CAL_UNIX), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Read registration
     */
    protected function read() : void
    {
        if (!$this->getAppointmentId()) {
            return;
        }

        $query = "SELECT * FROM cal_registrations WHERE cal_id = " . $this->db->quote(
            $this->getAppointmentId(),
            'integer'
        );
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->registered[] = array(
                'usr_id' => (int) $row->usr_id,
                'dstart' => (int) $row->dstart,
                'dend' => (int) $row->dend
            );
        }
    }
}
