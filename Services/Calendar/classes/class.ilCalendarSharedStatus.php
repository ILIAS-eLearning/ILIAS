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
 * Stores status (accepted/declined) of shared calendars
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarSharedStatus
{
    public const STATUS_ACCEPTED = 1;
    public const STATUS_DECLINED = 2;
    public const STATUS_DELETED = 3;

    protected ?ilDBInterface $db;

    private int $usr_id = 0;

    private array $calendars = array();
    private array $writable = array();

    public function __construct(int $a_usr_id)
    {
        global $DIC;
        $this->usr_id = $a_usr_id;
        $this->db = $DIC->database();
        $this->read();
    }

    public function isAccepted(int $a_cal_id): bool
    {
        return
            isset($this->calendars[$a_cal_id]) &&
            $this->calendars[$a_cal_id] == self::STATUS_ACCEPTED;
    }

    public function isDeclined(int $a_cal_id): bool
    {
        return
            isset($this->calendars[$a_cal_id]) &&
            $this->calendars[$a_cal_id] == self::STATUS_DECLINED;
    }

    public static function getAcceptedCalendars(int $a_usr_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT cal_id FROM cal_shared_status " .
            "WHERE status = " . $ilDB->quote(self::STATUS_ACCEPTED, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $cal_ids[] = $row->cal_id;
        }
        return $cal_ids ?? [];
    }

    public static function hasStatus(int $a_usr_id, int $a_calendar_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM cal_shared_status " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND cal_id = " . $ilDB->quote($a_calendar_id, 'integer') . " ";
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    public static function deleteUser(int $a_usr_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_shared_status " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public static function deleteCalendar(int $a_calendar_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_shared_status " .
            "WHERE cal_id = " . $ilDB->quote($a_calendar_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public static function deleteStatus(int $a_id, int $a_calendar_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];

        if (ilObject::_lookupType($a_id) == 'usr') {
            $query = "DELETE FROM cal_shared_status " .
                "WHERE cal_id = " . $ilDB->quote($a_calendar_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_id, 'integer') . " ";
            $res = $ilDB->manipulate($query);
        } elseif (ilObject::_lookupType($a_id) == 'role') {
            $assigned_users = $rbacreview->assignedUsers($a_id);

            if (!count($assigned_users)) {
                return;
            }

            $query = "DELETE FROM cal_shared_status " .
                "WHERE cal_id = " . $ilDB->quote($a_calendar_id, 'integer') . " " .
                "AND " . $ilDB->in('usr_id', $assigned_users, false, 'integer');
            $res = $ilDB->manipulate($query);
        }
    }

    public function accept(int $a_calendar_id): void
    {
        self::deleteStatus($this->usr_id, $a_calendar_id);
        $query = "INSERT INTO cal_shared_status (cal_id,usr_id,status) " .
            "VALUES ( " .
            $this->db->quote($a_calendar_id, 'integer') . ", " .
            $this->db->quote($this->usr_id, 'integer') . ", " .
            $this->db->quote(self::STATUS_ACCEPTED, 'integer') . " " .
            ")";
        $res = $this->db->manipulate($query);

        $this->calendars[$a_calendar_id] = self::STATUS_ACCEPTED;
    }

    public function decline(int $a_calendar_id): void
    {
        self::deleteStatus($this->usr_id, $a_calendar_id);
        $query = "INSERT INTO cal_shared_status (cal_id,usr_id,status) " .
            "VALUES ( " .
            $this->db->quote($a_calendar_id, 'integer') . ", " .
            $this->db->quote($this->usr_id, 'integer') . ", " .
            $this->db->quote(self::STATUS_DECLINED, 'integer') . " " .
            ")";
        $res = $this->db->manipulate($query);
        $this->calendars[$a_calendar_id] = self::STATUS_DECLINED;
    }

    protected function read()
    {
        $query = "SELECT * FROM cal_shared_status " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->calendars[(int) $row->cal_id] = (int) $row->status;
        }
    }

    public function getOpenInvitations(): array
    {
        $shared = ilCalendarShared::getSharedCalendarsForUser($this->usr_id);

        $invitations = array();
        foreach ($shared as $data) {
            if ($this->isDeclined($data['cal_id']) || $this->isAccepted($data['cal_id'])) {
                continue;
            }

            $tmp_calendar = new ilCalendarCategory($data['cal_id']);

            $invitations[] = array(
                'cal_id' => (int) $data['cal_id'],
                'create_date' => $data['create_date'],
                'obj_type' => $data['obj_type'],
                'name' => $tmp_calendar->getTitle(),
                'owner' => $tmp_calendar->getObjId(),
                'apps' => count(ilCalendarCategoryAssignments::_getAssignedAppointments(array((int) $data['cal_id']))),
                'accepted' => $this->isAccepted((int) $data['cal_id']),
                'declined' => $this->isDeclined((int) $data['cal_id'])
            );
        }
        return $invitations;
    }
}
