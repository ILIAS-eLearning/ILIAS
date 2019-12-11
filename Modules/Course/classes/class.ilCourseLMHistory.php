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
* class ilCourseLMHistory
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends Object
*/

class ilCourseLMHistory
{
    public $db;

    public $course_id;
    public $user_id;

    /**
     * Constructor
     * @param int $crs_id
     * @param int $user_id
     */
    public function __construct($crs_id, $user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db =&$ilDB;

        $this->course_id = $crs_id;
        $this->user_id = $user_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }
    public function getCourseRefId()
    {
        return $this->course_id;
    }

    public static function _updateLastAccess($a_user_id, $a_lm_ref_id, $a_page_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];

        if (!$crs_ref_id = $tree->checkForParentType($a_lm_ref_id, 'crs')) {
            return true;
        }

        // Delete old entries
        $query = "DELETE FROM crs_lm_history " .
            "WHERE lm_ref_id = " . $ilDB->quote($a_lm_ref_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_user_id, 'integer') . "";
        $res = $ilDB->manipulate($query);

        // Add new entry
        $fields = array("usr_id" => array("integer", $a_user_id),
            "crs_ref_id" => array("integer", $crs_ref_id),
            "lm_ref_id" => array("integer", $a_lm_ref_id),
            "lm_page_id" => array("integer", $a_page_id),
            "last_access" => array("integer", time()));
        $ilDB->insert("crs_lm_history", $fields);
        return true;
    }

    public function getLastLM()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_lm_history " .
            "WHERE usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . " " .
            "AND crs_ref_id = " . $ilDB->quote($this->getCourseRefId(), 'integer') . " " .
            "ORDER BY last_access ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->lm_ref_id;
        }
        return false;
    }

    public function getLMHistory()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_lm_history " .
            "WHERE usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . " " .
            "AND crs_ref_id = " . $ilDB->quote($this->getCourseRefId(), 'integer') . "";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lm[$row->lm_ref_id]['lm_ref_id'] = $row->lm_ref_id;
            $lm[$row->lm_ref_id]['lm_page_id'] = $row->lm_page_id;
            $lm[$row->lm_ref_id]['last_access'] = $row->last_access;
        }
        return $lm ? $lm : array();
    }

    /**
     * Delete user
     * @global type $ilDB
     * @param type $a_usr_id
     * @return boolean
     */
    public static function _deleteUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_lm_history WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }
}
