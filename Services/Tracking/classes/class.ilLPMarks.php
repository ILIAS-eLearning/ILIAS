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
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @package ilias-tracking
*
*/


class ilLPMarks
{
    public $db = null;

    public $obj_id = null;
    public $usr_id = null;
    public $obj_type = null;

    public $completed = false;
    public $comment = '';
    public $mark = '';
    public $status_changed = '';

    public $has_entry = false;



    public function __construct($a_obj_id, $a_usr_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;

        $this->obj_id = $a_obj_id;
        $this->usr_id = $a_usr_id;
        $this->obj_type = $ilObjDataCache->lookupType($this->obj_id);

        $this->__read();
    }
    
    /**
     * Delete object
     *
     * @static
     */
    public static function deleteObject($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM ut_lp_marks " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    public function getUserId()
    {
        return $this->usr_id;
    }

    public function setMark($a_mark)
    {
        $this->mark = $a_mark;
    }
    public function getMark()
    {
        return $this->mark;
    }
    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    public function getComment()
    {
        return $this->comment;
    }
    public function setCompleted($a_status)
    {
        $this->completed = (bool) $a_status;
    }
    public function getCompleted()
    {
        return $this->completed;
    }
    public function getStatusChanged()
    {
        return $this->status_changed;
    }

    public function getObjId()
    {
        return (int) $this->obj_id;
    }
    
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->has_entry) {
            $this->__add();
        }
        $query = "UPDATE ut_lp_marks " .
            "SET mark = " . $ilDB->quote($this->getMark(), 'text') . ", " .
            "u_comment = " . $ilDB->quote($this->getComment(), 'text') . ", " .
            "completed = " . $ilDB->quote($this->getCompleted(), 'integer') . " " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($this->getUserId(), 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    // Static
    public static function _hasCompleted($a_usr_id, $a_obj_id)
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

    /**
     * Get completions of user
     * @param $user_id
     * @param $from
     * @param $to
     * @return array
     */
    public static function getCompletionsOfUser($user_id, $from, $to)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($user_id, 'integer') .
            " AND status = " . $ilDB->quote(ilLPStatus::LP_STATUS_COMPLETED_NUM, 'integer') .
            " AND status_changed >= " . $ilDB->quote($from, "timestamp") .
            " AND status_changed <= " . $ilDB->quote($to, "timestamp");

        $set = $ilDB->query($query);
        $completions = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $completions[] = $rec;
        }

        return $completions;
    }


    public static function _lookupMark($a_usr_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->mark;
        }
        return '';
    }

        
    public static function _lookupComment($a_usr_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM ut_lp_marks " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->u_comment;
        }
        return '';
    }

    // Private
    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = $this->db->query("SELECT * FROM ut_lp_marks " .
                                "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
                                "AND usr_id = " . $ilDB->quote($this->usr_id, 'integer'));
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->has_entry = true;
            $this->completed = (int) $row->completed;
            $this->comment = $row->u_comment;
            $this->mark = $row->mark;
            $this->status_changed = $row->status_changed;

            return true;
        }

        return false;
    }

    public function __add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "INSERT INTO ut_lp_marks (mark,u_comment, completed,obj_id,usr_id) " .
            "VALUES( " .
            $ilDB->quote($this->getMark(), 'text') . ", " .
            $ilDB->quote($this->getComment(), 'text') . ", " .
            $ilDB->quote($this->getCompleted(), 'integer') . ", " .
            $ilDB->quote($this->getObjId(), 'integer') . ", " .
            $ilDB->quote($this->getUserId(), 'integer') . " " .
            ")";
        $res = $ilDB->manipulate($query);
        $this->has_entry = true;

        return true;
    }
    
    public static function _deleteForUsers($a_obj_id, array $a_user_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $ilDB->manipulate("DELETE FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND " . $ilDB->in("usr_id", $a_user_ids, "", "integer"));
    }
    
    public static function _getAllUserIds($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = array();
        
        $set = $ilDB->query("SELECT usr_id FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row["usr_id"];
        }

        return $res;
    }
}
