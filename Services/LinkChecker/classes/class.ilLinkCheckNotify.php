<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class for checking external links in page objects. All user who want to get messages about invalid links of a page_object
* are stored here
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/
class ilLinkCheckNotify
{
    public $db = null;


    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function setUserId($a_usr_id)
    {
        $this->usr_id = $a_usr_id;
    }
    public function getUserId()
    {
        return $this->usr_id;
    }
    public function setObjId($a_obj_id)
    {
        $this->obj_id = $a_obj_id;
    }
    public function getObjId()
    {
        return $this->obj_id;
    }

    public function addNotifier()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->deleteNotifier();

        $query = "INSERT INTO link_check_report (obj_id,usr_id) " .
            "VALUES ( " .
            $ilDB->quote($this->getObjId(), 'integer') . ", " .
            $ilDB->quote($this->getUserId(), 'integer') .
            ")";
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function deleteNotifier()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM link_check_report " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }

    /* Static */
    public static function _getNotifyStatus($a_usr_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM link_check_report " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);

        return $res->numRows() ? true : false;
    }

    public static function _deleteUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM link_check_report " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    public static function _deleteObject($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM link_check_report " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
                
        return true;
    }

    public static function _getNotifiers($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM link_check_report " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $usr_ids[] = $row->usr_id;
        }

        return $usr_ids ? $usr_ids : array();
    }

    public static function _getAllNotifiers(&$db)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM link_check_report ";

        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $usr_ids[$row->usr_id][] = $row->obj_id;
        }

        return $usr_ids ? $usr_ids : array();
    }
}
