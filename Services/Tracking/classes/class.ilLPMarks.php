<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
 * Class ilLPMarks
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @package ilias-tracking
 */
class ilLPMarks
{
    protected ?ilDBInterface $db;
    protected ilObjectDataCache $ilObjectDataCache;

    protected int $obj_id;
    protected int $usr_id;
    protected ?string $obj_type;

    protected bool $completed = false;
    protected string $comment = '';
    protected string $mark = '';
    protected string $status_changed = '';

    protected $has_entry = false;

    public function __construct(int $a_obj_id, int $a_usr_id)
    {
        global $DIC;

        $this->ilObjectDataCache = $DIC['ilObjDataCache'];
        $this->db = $DIC->database();

        $this->obj_id = $a_obj_id;
        $this->usr_id = $a_usr_id;
        $this->obj_type = $this->ilObjectDataCache->lookupType($this->obj_id);

        $this->__read();
    }

    public static function deleteObject(int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM ut_lp_marks " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function setMark(string $a_mark): void
    {
        $this->mark = $a_mark;
    }

    public function getMark(): string
    {
        return $this->mark;
    }

    public function setComment(string $a_comment): void
    {
        $this->comment = $a_comment;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setCompleted(bool $a_status): void
    {
        $this->completed = $a_status;
    }

    public function getCompleted(): bool
    {
        return $this->completed;
    }

    public function getStatusChanged(): string
    {
        return $this->status_changed;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function update(): void
    {
        if (!$this->has_entry) {
            $this->__add();
        }
        $query = "UPDATE ut_lp_marks " .
            "SET mark = " . $this->db->quote($this->getMark(), 'text') . ", " .
            "u_comment = " . $this->db->quote(
                $this->getComment(),
                'text'
            ) . ", " .
            "completed = " . $this->db->quote(
                $this->getCompleted(),
                'integer'
            ) . " " .
            "WHERE obj_id = " . $this->db->quote(
                $this->getObjId(),
                'integer'
            ) . " " .
            "AND usr_id = " . $this->db->quote($this->getUserId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    // Static
    public static function _hasCompleted(int $a_usr_id, int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->completed;
        }
        return false;
    }

    public static function getCompletionsOfUser(
        int $user_id,
        string $from,
        string $to
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($user_id, 'integer') .
            " AND status = " . $ilDB->quote(
                ilLPStatus::LP_STATUS_COMPLETED_NUM,
                'integer'
            ) .
            " AND status_changed >= " . $ilDB->quote($from, "timestamp") .
            " AND status_changed <= " . $ilDB->quote($to, "timestamp");

        $set = $ilDB->query($query);
        $completions = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $completion = [
                'obj_id' => (int) $rec['obj_id'],
                'usr_id' => (int) $rec['usr_id'],
                'completed' => (bool) $rec['completed'],
                'mark' => (string) $rec['mark'],
                'comment' => (string) $rec['u_comment'],
                'status' => (int) $rec['status'],
                'status_changed' => (string) $rec['status_changed'],
                'status_dirty' => (int) $rec['status_changed'],
                'percentage' => (int) $rec['percentage']
            ];
            $completions[] = $completion;
        }
        return $completions;
    }

    public static function _lookupMark(int $a_usr_id, int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (string) $row->mark;
        }
        return '';
    }

    public static function _lookupComment(int $a_usr_id, int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (string) $row->u_comment;
        }
        return '';
    }

    // Private
    public function __read(): bool
    {
        $res = $this->db->query(
            "SELECT * FROM ut_lp_marks " .
            "WHERE obj_id = " . $this->db->quote(
                $this->obj_id,
                'integer'
            ) . " " .
            "AND usr_id = " . $this->db->quote($this->usr_id, 'integer')
        );
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->has_entry = true;
            $this->completed = (int) $row->completed;
            $this->comment = (string) $row->u_comment;
            $this->mark = (string) $row->mark;
            $this->status_changed = (string) $row->status_changed;
            return true;
        }
        return false;
    }

    public function __add(): void
    {
        $query = "INSERT INTO ut_lp_marks (mark,u_comment, completed,obj_id,usr_id) " .
            "VALUES( " .
            $this->db->quote($this->getMark(), 'text') . ", " .
            $this->db->quote($this->getComment(), 'text') . ", " .
            $this->db->quote($this->getCompleted(), 'integer') . ", " .
            $this->db->quote($this->getObjId(), 'integer') . ", " .
            $this->db->quote($this->getUserId(), 'integer') . " " .
            ")";
        $res = $this->db->manipulate($query);
        $this->has_entry = true;
    }

    public static function _deleteForUsers(
        int $a_obj_id,
        array $a_user_ids
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilDB->manipulate(
            "DELETE FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND " . $ilDB->in("usr_id", $a_user_ids, "", "integer")
        );
    }

    public static function _getAllUserIds(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();
        $set = $ilDB->query(
            "SELECT usr_id FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer")
        );
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["usr_id"];
        }
        return $res;
    }
}
