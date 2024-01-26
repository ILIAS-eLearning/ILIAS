<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Helper class for local user accounts (in categories)
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/

class ilLocalUser
{
    public $db;

    public $parent_id;

    /**
    * Constructor
    * @access	public
    * @param	string	scriptname
    * @param    int user_id
    */
    public function __construct($a_parent_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->parent_id = $a_parent_id;
    }

    public function setParentId($a_parent_id)
    {
        $this->parent_id = $a_parent_id;
    }
    public function getParentId()
    {
        return $this->parent_id;
    }

    public static function _getUserData($a_filter)
    {
        include_once './Services/User/classes/class.ilObjUser.php';

        $users_data = ilObjUser::_getAllUserData(array("login","firstname","lastname","time_limit_owner"), -1);

        foreach ($users_data as $usr_data) {
            if (!$a_filter or $a_filter == $usr_data['time_limit_owner']) {
                $users[] = $usr_data;
            }
        }
        return $users ? $users : array();
    }

    public static function _getFolderIds($access_with_orgunit = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $access = $DIC->access();
        $rbacsystem = $DIC['rbacsystem'];

        $query = "SELECT DISTINCT(time_limit_owner) as parent_id FROM usr_data ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // Workaround for users with time limit owner "0".
            if (!$row->parent_id || (int) $row->parent_id === USER_FOLDER_ID) {
                if ($rbacsystem->checkAccess('read', USER_FOLDER_ID) ||
                    ($access_with_orgunit && $access->checkPositionAccess(\ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, USER_FOLDER_ID))) {
                    $parent[] = $row->parent_id;
                }
                continue;
            }

            if ($rbacsystem->checkAccess('read_users', $row->parent_id) ||
                ($access_with_orgunit && $access->checkPositionAccess(ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, $row->parent_id))
                or $rbacsystem->checkAccess('cat_administrate_users', $row->parent_id)) {
                if ($row->parent_id) {
                    $parent[] = $row->parent_id;
                }
            }
        }
        return $parent ? $parent : array();
    }

    public static function _getAllUserIds($a_filter = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        switch ($a_filter) {
            case 0:
                if (self::_getFolderIds()) {
                    $where = "WHERE " . $ilDB->in("time_limit_owner", self::_getFolderIds(), false, "integer") . " ";
                } else {
                    return [];
                }

                break;

            default:
                $where = "WHERE time_limit_owner = " . $ilDB->quote($a_filter, "integer") . " ";

                break;
        }

        $query = "SELECT usr_id FROM usr_data " . $where;
        $res = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($res)) {
            $users[] = $row->usr_id;
        }

        return $users ? $users : array();
    }

    public static function _getUserFolderId()
    {
        return 7;
    }
} // CLASS ilLocalUser
