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
* Class ilObjRole
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRole extends ilObject
{
	/**
	* reference id of parent object
	* this is _only_ neccessary for non RBAC protected objects
	* TODO: maybe move this to basic Object class
	* @var		integer
	* @access	private
	*/
	var $parent;
	
	var $allow_register;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRole($a_id = 0,$a_call_by_reference = false)
	{
		$this->type = "role";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* loads "role" from database
	* @access private
	*/
	function read ()
	{
		$q = "SELECT * FROM role_data WHERE role_id='".$this->id."'";
		$r = $this->ilias->db->query($q);

		if ($r->numRows() > 0)
		{
			$data = $r->fetchRow(DB_FETCHMODE_ASSOC);

			// fill member vars in one shot
			$this->assignData($data);
		}
		else
		{
			 $this->ilias->raiseError("<b>Error: There is no dataset with id ".$this->id."!</b><br />class: ".get_class($this)."<br />Script: ".__FILE__."<br />Line: ".__LINE__, $this->ilias->FATAL);
		}

		parent::read();
	}

	/**
	* loads a record "role" from array
	* @access	public
	* @param	array		roledata
	*/
	function assignData($a_data)
	{
		$this->setTitle(ilUtil::stripSlashes($a_data["title"]));
		$this->setDescription(ilUtil::stripslashes($a_data["desc"]));
		$this->setAllowRegister($a_data["allow_register"]);
	}

	/**
	* updates a record "role" and write it into database
	* @access	public
	*/
	function update ()
	{
		$q = "UPDATE role_data SET ".
			 "allow_register='".$this->allow_register."' ".
			 "WHERE role_id='".$this->id."'";

		$this->ilias->db->query($q);

		parent::update();

		$this->read();

		return true;
	}
	
	/**
	* create
	*
	*
	* @access	public
	* @return	integer		object id
	*/
	function create()
	{
		$this->id = parent::create();

		$q = "INSERT INTO role_data ".
			 "(role_id,allow_register) ".
			 "VALUES ".
			 "('".$this->id."','".$this->getAllowRegister()."')";
		$this->ilias->db->query($q);

		return $this->id;
	}

	/**
	* set allow_register of role
	* 
	* @access	public
	* @param	integer
	*/
	function setAllowRegister($a_allow_register)
	{
		if (empty($a_allow_register))
		{
			$a_allow_register == 0;
		}
		
		$this->allow_register = (int) $a_allow_register;
	}
	
	/**
	* get allow_register
	* 
	* @access	public
	* @return	integer
	*/
	function getAllowRegister()
	{
		return $this->allow_register;
	}

	/**
	* set reference id of parent object
	* this is neccessary for non RBAC protected objects!!!
	* 
	* @access	public
	* @param	integer	ref_id of parent object
	*/
	function setParent($a_parent_ref)
	{
		$this->parent = $a_parent_ref;
	}
	
	/**
	* get reference id of parent object
	* 
	* @access	public
	* @return	integer	ref_id of parent object
	*/
	function getParent()
	{
		return $this->parent;
	}

	/**
	* copy all properties and subobjects of a role.
	* DISABLED
	* @access	public
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{		
		// DISABLED
		return false;

		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);
		
		// put here role specific stuff

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete role and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		global $rbacadmin, $rbacreview;
		
		// TODO: unassign users from deleted role
		
		// first get all rolefolders where role is assigned to (by linking operation)
		// before the role is deleted. $role_folders is used later on
		$role_folders = $rbacreview->getFoldersAssignedToRole($this->getId());

		// check if role is a linked local role or not
		if ($rbacreview->isAssignable($this->getId(),$this->getParent()))
		{
			// do not delete role if this role is the last role a user is assigned to
			
			// first fetch all users assigned to role
			$user_ids = $rbacreview->assignedUsers($this->getId());
			
			$last_role_user_ids = array();

			foreach ($user_ids as $user_id)
			{
				// get all roles each user has
				$role_ids = $rbacreview->assignedRoles($user_id);
				
				// is last role?
				if (count($role_ids) == 1)
				{
					$last_role_user_ids[] = $user_id;
				}			
			}
			
			// users with last role found?
			if (count($last_role_user_ids) > 0)
			{
				foreach ($last_role_user_ids as $user_id)
				{
					// GET OBJECT TITLE
					$tmp_obj = $this->ilias->obj_factory->getInstanceByObjId($user_id);
					$user_names[] = $tmp_obj->getFullname();
					unset($tmp_obj);
				}
				
				// TODO: This check must be done in rolefolder object because if multiple
				// roles were selected the other roles are still deleted and the system does not
				// give any feedback about this.
				$users = implode(', ',$user_names);
				$this->ilias->raiseError($this->lng->txt("msg_user_last_role1")." ".
									 $users."<br/>".$this->lng->txt("msg_user_last_role2"),$this->ilias->error_obj->WARNING);				
			}
			else
			{
				// IT'S A BASE ROLE
				$rbacadmin->deleteRole($this->getId(),$this->getParent());

				// delete object_data entry
				parent::delete();
					
				// delete role_data entry
				$q = "DELETE FROM role_data WHERE role_id = '".$this->getId()."'";
				$this->ilias->db->query($q);
			}
		}
		else
		{
			// linked local role: INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
			$rbacadmin->deleteLocalRole($this->getId(),$this->getParent());
		}

		//  purge empty rolefolders
		foreach ($role_folders as $rolf)
		{
			if (ilObject::_exists($rolf,true))
			{
				$rolfObj = $this->ilias->obj_factory->getInstanceByRefId($rolf);
				$rolfObj->purge();
				unset($roleObj);
			}
		}
		
		return true;
	}
} // END class.ilObjRole
?>
