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
 * Handles shared calendars
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarShared
{
    public const TYPE_USR = 1;
    public const TYPE_ROLE = 2;

    private int $calendar_id;

    private array $shared = array();
    private array $shared_users = array();
    private array $shared_roles = array();

    protected ilDBInterface $db;
    protected ilRbacReview $rbacreview;

    public function __construct(int $a_calendar_id)
    {
        global $DIC;

        $this->calendar_id = $a_calendar_id;
        $this->db = $DIC->database();
        $this->rbacreview = $DIC->rbac()->review();
        $this->read();
    }

    public static function deleteByCalendar(int $a_cal_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_shared WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * Delete all entries for a specific user
     */
    public static function deleteByUser(int $a_user_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM cal_shared WHERE obj_id = " . $ilDB->quote($a_user_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * is shared with user
     */
    public static function isSharedWithUser(int $a_usr_id, int $a_calendar_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];

        $query = 'SELECT * FROM cal_shared ' .
            "WHERE cal_id = " . $ilDB->quote($a_calendar_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[(int) $row->obj_id] = (string) $row->obj_type;
        }
        $assigned_roles = $rbacreview->assignedRoles($a_usr_id);
        foreach ($obj_ids as $id => $type) {
            switch ($type) {
                case self::TYPE_USR:
                    if ($a_usr_id == $id) {
                        return true;
                    }
                    break;
                case self::TYPE_ROLE:
                    if (in_array($id, $assigned_roles)) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    public static function getSharedCalendarsForUser(int $a_usr_id = 0): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];

        if (!$a_usr_id) {
            $a_usr_id = $ilUser->getId();
        }

        $query = "SELECT * FROM cal_shared " .
            "WHERE obj_type = " . $ilDB->quote(self::TYPE_USR, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "ORDER BY create_date";
        $res = $ilDB->query($query);
        $calendars = array();
        $shared = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $calendars[] = (int) $row->cal_id;

            $shared[(int) $row->cal_id]['cal_id'] = (int) $row->cal_id;
            $shared[(int) $row->cal_id]['create_date'] = (string) $row->create_date;
            $shared[(int) $row->cal_id]['obj_type'] = (string) $row->obj_type;
        }

        $assigned_roles = $rbacreview->assignedRoles($ilUser->getId());

        $query = "SELECT * FROM cal_shared " .
            "WHERE obj_type = " . $ilDB->quote(self::TYPE_ROLE, 'integer') . " " .
            "AND " . $ilDB->in('obj_id', $assigned_roles, false, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (in_array($row->cal_id, $calendars)) {
                continue;
            }
            if (ilCalendarCategories::_isOwner($ilUser->getId(), (int) $row->cal_id)) {
                continue;
            }

            $shared[(int) $row->cal_id]['cal_id'] = (int) $row->cal_id;
            $shared[(int) $row->cal_id]['create_date'] = (string) $row->create_date;
            $shared[(int) $row->cal_id]['obj_type'] = (string) $row->obj_type;
        }
        return $shared;
    }

    public function getCalendarId(): int
    {
        return $this->calendar_id;
    }

    public function getShared(): array
    {
        return $this->shared;
    }

    public function getUsers(): array
    {
        return $this->shared_users;
    }

    public function getRoles(): array
    {
        return $this->shared_roles;
    }

    public function isShared(int $a_obj_id): bool
    {
        return isset($this->shared[$a_obj_id]);
    }

    public function isEditableForUser(int $a_user_id): bool
    {
        foreach ($this->shared as $info) {
            if (!$info['writable']) {
                continue;
            }

            switch ($info['obj_type']) {
                case self::TYPE_USR:
                    if ($info['obj_id'] == $a_user_id) {
                        return true;
                    }
                    break;

                case self::TYPE_ROLE:
                    if ($this->rbacreview->isAssigned($a_user_id, $info['obj_id'])) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    public function share(int $a_obj_id, int $a_type, bool $a_writable = false): void
    {
        if ($this->isShared($a_obj_id)) {
            return;
        }
        $query = "INSERT INTO cal_shared (cal_id,obj_id,obj_type,create_date,writable) " .
            "VALUES ( " .
            $this->db->quote($this->getCalendarId(), 'integer') . ", " .
            $this->db->quote($a_obj_id, 'integer') . ", " .
            $this->db->quote($a_type, 'integer') . ", " .
            $this->db->now() . ", " .
            $this->db->quote((int) $a_writable, 'integer') . ' ' .
            ")";

        $res = $this->db->manipulate($query);
        $this->read();
    }

    public function stopSharing(int $a_obj_id): void
    {
        if (!$this->isShared($a_obj_id)) {
            return;
        }
        $query = "DELETE FROM cal_shared WHERE cal_id = " . $this->db->quote($this->getCalendarId(), 'integer') . " " .
            "AND obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        ilCalendarSharedStatus::deleteStatus($a_obj_id, $this->getCalendarId());
        $this->read();
    }

    protected function read(): void
    {
        $this->shared = $this->shared_users = $this->shared_roles = array();
        $query = "SELECT * FROM cal_shared WHERE cal_id = " . $this->db->quote($this->getCalendarId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($row->obj_type) {
                case self::TYPE_USR:
                    $this->shared_users[(int) $row->obj_id]['obj_id'] = (int) $row->obj_id;
                    $this->shared_users[(int) $row->obj_id]['obj_type'] = (string) $row->obj_type;
                    $this->shared_users[(int) $row->obj_id]['create_date'] = (string) $row->create_date;
                    $this->shared_users[(int) $row->obj_id]['writable'] = (bool) $row->writable;
                    break;

                case self::TYPE_ROLE:
                    $this->shared_roles[(int) $row->obj_id]['obj_id'] = (int) $row->obj_id;
                    $this->shared_roles[(int) $row->obj_id]['obj_type'] = (string) $row->obj_type;
                    $this->shared_roles[(int) $row->obj_id]['create_date'] = (string) $row->create_date;
                    $this->shared_roles[(int) $row->obj_id]['writable'] = (bool) $row->writable;
                    break;
            }

            $this->shared[(int) $row->obj_id]['obj_id'] = (int) $row->obj_id;
            $this->shared[(int) $row->obj_id]['obj_type'] = (string) $row->obj_type;
            $this->shared[(int) $row->obj_id]['create_date'] = (string) $row->create_date;
            $this->shared[(int) $row->obj_id]['writable'] = (bool) $row->writable;
        }
    }
}
