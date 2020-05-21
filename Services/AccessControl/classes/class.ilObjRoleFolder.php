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


require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjRoleFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* $Id$
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleFolder extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "rolf";
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    public function read()
    {
        parent::read();
        
        if ($this->getId() != ROLE_FOLDER_ID) {
            $this->setDescription($this->lng->txt("obj_" . $this->getType() . "_local_desc") . $this->getTitle() . $this->getDescription());
            $this->setTitle($this->lng->txt("obj_" . $this->getType() . "_local"));
        }
    }


    /**
    * delete rolefolder and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here rolefolder specific stuff
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        $roles = $rbacreview->getRolesOfRoleFolder($this->getRefId());
        
        // FIRST DELETE ALL LOCAL/BASE ROLES OF FOLDER
        foreach ($roles as $role_id) {
            $roleObj = &$this->ilias->obj_factory->getInstanceByObjId($role_id);
            $roleObj->setParent($this->getRefId());
            $roleObj->delete();
            unset($roleObj);
        }
        
        // always call parent delete function at the end!!
        return true;
    }

    /**
    * creates a local role in current rolefolder (this object)
    *
    * @access	public
    * @param	string	title
    * @param	string	description
    * @return	object	role object
    */
    public function createRole($a_title, $a_desc, $a_import_id = 0)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        
        include_once("./Services/AccessControl/classes/class.ilObjRole.php");
        $roleObj = new ilObjRole();
        $roleObj->setTitle($a_title);
        $roleObj->setDescription($a_desc);
        //echo "aaa-1-";
        if ($a_import_id != "") {
            //echo "aaa-2-".$a_import_id."-";
            $roleObj->setImportId($a_import_id);
        }
        $roleObj->create();
            
        // ...and put the role into local role folder...
        $rbacadmin->assignRoleToFolder($roleObj->getId(), $this->getRefId(), "y");

        return $roleObj;
    }
    
    /**
    * checks if rolefolder contains any roles. if not the rolefolder is deleted
    * @access	public
    * @return	boolean	true if rolefolder is deleted
    */
    public function purge()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacadmin = $DIC['rbacadmin'];
        $tree = $DIC['tree'];

        $local_roles = $rbacreview->getRolesOfRoleFolder($this->getRefId());
            
        if (count($local_roles) == 0) {
            $rbacadmin->revokePermission($this->getRefId());
            
            if ($tree_id = $this->isDeleted()) {
                $deleted_tree = new ilTree($tree_id, -(int) $tree_id);
                $deleted_tree->deleteTree($deleted_tree->getNodeData($this->getRefId()));
            } else {
                $tree->deleteTree($tree->getNodeData($this->getRefId()));
            }

            $this->delete();
            
            return true;
        }
        
        return false;
    }
    
    /**
    * checks if role folder is in trash
    * @access	private
    * @return	integer	return negative tree if in trash, otherwise false
    */
    public function isDeleted()
    {
        $q = "SELECT tree FROM tree WHERE child= " . $this->ilias->db->quote($this->getRefId()) . " ";
        $row = $this->ilias->db->getRow($q);
        
        if ($row->tree < 0) {
            return $row->tree;
        }
        
        return false;
    }
} // END class.ilObjRoleFolder
