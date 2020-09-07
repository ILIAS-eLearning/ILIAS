<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* $Id$
*
*
*
* @extends ilObjectGUI
*/


class ilRoleDesktopItem
{
    public $db;
    public $role_id;
 
    /**
    * Constructor
    * @access public
    */
    public function __construct($a_role_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->role_id = $a_role_id;
    }

    public function getRoleId()
    {
        return $this->role_id;
    }
    public function setRoleId($a_role_id)
    {
        $this->role_id = $a_role_id;
    }

    public function add($a_item_id, $a_item_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($a_item_type and $a_item_id) {
            $next_id = $ilDB->nextId('role_desktop_items');
            $query = "INSERT INTO role_desktop_items (role_item_id,role_id,item_id,item_type) " .
                "VALUES (" .
                $ilDB->quote($next_id, 'integer') . ',' .
                $ilDB->quote($this->getRoleId(), 'integer') . ", " .
                $ilDB->quote($a_item_id, 'integer') . ", " .
                $ilDB->quote($a_item_type, 'text') . " " .
                ")";
            $res = $ilDB->manipulate($query);
            $this->__assign($a_item_id, $a_item_type);
            
            return true;
        }
        return false;
    }
    public function delete($a_role_item_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM role_desktop_items " .
            "WHERE role_item_id = " . $ilDB->quote($a_role_item_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function deleteAll()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM role_desktop_items " .
            "WHERE role_id = " . $ilDB->quote($this->getRoleId(), 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    public function isAssigned($a_item_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM role_desktop_items " .
            "WHERE role_id = " . $ilDB->quote($this->getRoleId(), 'integer') . " " .
            "AND item_id = " . $ilDB->quote($a_item_ref_id, 'integer') . " ";
        $res = $ilDB->query($query);

        return $res->numRows() ? true : false;
    }

    public function getItem($a_role_item_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM role_desktop_items " .
            "WHERE role_id = " . $ilDB->quote($this->getRoleId(), 'integer') . " " .
            "AND role_item_id = " . $ilDB->quote($a_role_item_id, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['item_id'] = $row->item_id;
            $item['item_type'] = $row->item_type;
        }

        return $item ? $item : array();
    }



    public function getAll()
    {
        global $DIC;

        $tree = $DIC['tree'];
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM role_desktop_items " .
            "WHERE role_id = " . $this->db->quote($this->getRoleId(), 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // TODO this check must be modified for non tree objects
            if (!$tree->isInTree($row->item_id)) {
                $this->delete($row->role_item_id);
                continue;
            }
            $items[$row->role_item_id]['item_id'] = $row->item_id;
            $items[$row->role_item_id]['item_type'] = $row->item_type;
        }

        return $items ? $items : array();
    }

    // PRIVATE
    public function __assign($a_item_id, $a_item_type)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        foreach ($rbacreview->assignedUsers($this->getRoleId()) as $user_id) {
            if (is_object($tmp_user = ilObjectFactory::getInstanceByObjId($user_id, false))) {
                if (!$tmp_user->isDesktopItem($a_item_id, $a_item_type)) {
                    $tmp_user->addDesktopItem($a_item_id, $a_item_type);
                }
            }
        }
        return true;
    }
}
