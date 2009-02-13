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

require_once "./classes/class.ilObject.php";

/**
* Class ilObjRole
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @ingroup	ServicesAccessControl
*/
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
	var $assign_users;

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

	function toggleAssignUsersStatus($a_assign_users)
	{
		$this->assign_users = (int) $a_assign_users;
	}
	function getAssignUsersStatus()
	{
		return $this->assign_users;
	}
	// Same method (static)
	function _getAssignUsersStatus($a_role_id)
	{
		global $ilDB;

		$query = "SELECT assign_users FROM role_data WHERE role_id = ".$ilDB->quote($a_role_id)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->assign_users ? true : false;
		}
		return false;
	}

	/**
	* loads "role" from database
	* @access private
	*/
	function read ()
	{
		global $ilDB;
		
		$q = "SELECT * FROM role_data WHERE role_id= ".$ilDB->quote($this->id)." ";
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
		$this->toggleAssignUsersStatus($a_data['assign_users']);
	}

	/**
	* updates a record "role" and write it into database
	* @access	public
	*/
	function update ()
	{
		global $ilDB;
		
		$q = "UPDATE role_data SET ".
			"allow_register= ".$ilDB->quote($this->allow_register).", ".
			"assign_users = ".$ilDB->quote($this->getAssignUsersStatus())." ".
			"WHERE role_id= ".$ilDB->quote($this->id)." ";

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
		global $ilDB;
		
		$this->id = parent::create();

		$q = "INSERT INTO role_data ".
			"(role_id,allow_register,assign_users) ".
			"VALUES ".
			"(".$ilDB->quote($this->id).",".$ilDB->quote($this->getAllowRegister()).",".$ilDB->quote($this->getAssignUsersStatus()).")";
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
	* get all roles that are activated in user registration
	*
	* @access	public
	* @return	array		array of int: role ids
	*/
	function _lookupRegisterAllowed()
	{
		global $ilDB;
		
		$q = "SELECT * FROM role_data ".
			"LEFT JOIN object_data ON object_data.obj_id = role_data.role_id ".
			"WHERE allow_register = 1";
			
		$r = $ilDB->query($q);
	
		$roles = array();
		while ($role = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$roles[] = array("id" => $role["obj_id"],
							 "title" => $role["title"],
							 "auth_mode" => $role['auth_mode']);
		}
		
		return $roles;
	}

	/**
	* check whether role is allowed in user registration or not
	*
	* @param	int			$a_role_id		role id
	* @return	boolean		true if role is allowed in user registration
	*/
	function _lookupAllowRegister($a_role_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM role_data ".
			" WHERE role_id =".$ilDB->quote($a_role_id);
			
		$role_set = $ilDB->query($q);
		
		if ($role_rec = $role_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($role_rec["allow_register"])
			{
				return true;
			}
		}
		return false;
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
	* delete role and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		global $rbacadmin, $rbacreview,$ilDB;
		
		$role_folders = $rbacreview->getFoldersAssignedToRole($this->getId());
		
		if ($rbacreview->isAssignable($this->getId(),$this->getParent()))
		{
			// do not delete a global role, if the role is the last 
			// role a user is assigned to.
			//
			// Performance improvement: In the code section below, we
			// only need to consider _global_ roles. We don't need
			// to check for _local_ roles, because a user who has
			// a local role _always_ has a global role too.
			$last_role_user_ids = array();
			if ($this->getParent() == ROLE_FOLDER_ID)
			{
				// The role is a global role: check if 
				// we find users who aren't assigned to any
				// other global role than this one.
				$user_ids = $rbacreview->assignedUsers($this->getId());

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
			}
			
			// users with last role found?
			if (count($last_role_user_ids) > 0)
			{
				foreach ($last_role_user_ids as $user_id)
				{
//echo "<br>last role for user id:".$user_id.":";
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

				// Delete ldap role group mappings
				include_once('./Services/LDAP/classes/class.ilLDAPRoleGroupMappingSettings.php');
				ilLDAPRoleGroupMappingSettings::_deleteByRole($this->getId());

				// delete object_data entry
				parent::delete();
					
				// delete role_data entry
				$q = "DELETE FROM role_data WHERE role_id = ".$ilDB->quote($this->getId())." ";
				$this->ilias->db->query($q);

				include_once './classes/class.ilRoleDesktopItem.php';
				$role_desk_item_obj =& new ilRoleDesktopItem($this->getId());
				$role_desk_item_obj->deleteAll();

			}
		}
		else
		{
			// linked local role: INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
			$rbacadmin->deleteLocalRole($this->getId(),$this->getParent());
		}

		//  purge empty rolefolders
		//
		// Performance improvement: We filter out all role folders
		// which still contain roles, _before_ we attempt to purge them.
        // This is faster than attempting to purge all role folders,
        // and let function purge() of the role folder find out, if
        // purging is possible.
        
        $non_empty_role_folders = $rbacreview->filterEmptyRoleFolders($role_folders);
		$role_folders = array_diff($role_folders,$non_empty_role_folders);
        
		// Attempt to purge the role folders
		foreach ($role_folders as $rolf)
		{
			if (ilObject::_exists($rolf,true))
			{
				$rolfObj = $this->ilias->obj_factory->getInstanceByRefId($rolf);
				$rolfObj->purge();
				unset($rolfObj);
			}
		}
		
		return true;
	}
	
	function getCountMembers()
	{
		global $rbacreview;
		
		return count($rbacreview->assignedUsers($this->getId()));
	}

	/**
	 * STATIC METHOD
	 * search for role data. This method is called from class.ilSearch
	 * This method used by class.ilSearchGUI.php to a link to the results
	 * @param	object object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$a_search_obj)
	{
		global $ilBench;

		// NO CLASS VARIABLES IN STATIC METHODS

		$where_condition = $a_search_obj->getWhereCondition("like",array("title","description"));
		//$in = $a_search_obj->getInStatement("ore.ref_id");

		$query = "SELECT obj_id FROM object_data AS od ".
			$where_condition." ".
			"AND od.type = 'role' ";

		$ilBench->start("Search", "ilObjRole_search");
		$res = $a_search_obj->ilias->db->query($query);
		$ilBench->stop("Search", "ilObjRole_search");

		$counter = 0;

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_data[$counter++]["id"]				=  $row->obj_id;
		}

		return $result_data ? $result_data : array();
	}
	
	
	function _getTranslation($a_role_title)
	{
		global $lng;
		
		$test_str = explode('_',$a_role_title);

		if ($test_str[0] == 'il') 
		{
			$test2 = (int) $test_str[3];
			if ($test2 > 0)
			{
				unset($test_str[3]);
			}

			return $lng->txt(implode('_',$test_str));
		}
		
		return $a_role_title;
	}
	
	
	
	function _updateAuthMode($a_roles)
	{
		global $ilDB;

		foreach ($a_roles as $role_id => $auth_mode)
		{
			$q = "UPDATE role_data SET ".
				 "auth_mode= ".$ilDB->quote($auth_mode)." ".
				 "WHERE role_id= ".$ilDB->quote($role_id)." ";
			$ilDB->query($q);
		}
	}

	function _getAuthMode($a_role_id)
	{
		global $ilDB;

		$q = "SELECT auth_mode FROM role_data ".
			 "WHERE role_id= ".$ilDB->quote($a_role_id)." ";
		$r = $ilDB->query($q);
		$row = $r->fetchRow();
		
		return $row[0];
	}
	
	/**
	 * Get roles by auth mode
	 *
	 * @access public
	 * @param string auth mode
	 * 
	 */
	public static function _getRolesByAuthMode($a_auth_mode)
	{
		global $ilDB;
		
	 	$query = "SELECT * FROM role_data ".
	 		"WHERE auth_mode = ".$ilDB->quote($a_auth_mode);
	 	$res = $ilDB->query($query);
	 	while($row  = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$roles[] = $row->role_id;
	 	}
	 	return $roles ? $roles : array();
	}
	
	/**
	 * Reset auth mode to default
	 *
	 * @access public
	 * @static
	 *
	 * @param string auth mode
	 */
	public static function _resetAuthMode($a_auth_mode)
	{
		global $ilDB;
		
		$query = "UPDATE role_data SET auth_mode = 'default' WHERE auth_mode = ".$ilDB->quote($a_auth_mode);
		$ilDB->query($query);
	}
	
	// returns array of operation/objecttype definitions
	// private
	function __getPermissionDefinitions()
	{
		global $ilDB, $lng, $objDefinition,$rbacreview;		

		$operation_info = $rbacreview->getOperationAssignment();
		foreach($operation_info as $info)
		{
			if($objDefinition->getDevMode($info['type']))
			{
				continue;
			}
			$rbac_objects[$info['typ_id']] = array("obj_id"	=> $info['typ_id'],
											   	   "type"	=> $info['type']);
			$rbac_operations[$info['typ_id']][$info['ops_id']] = array(
									   							"ops_id"	=> $info['ops_id'],
									  							"title"		=> $info['operation'],
																"name"		=> $lng->txt($info['type']."_".$info['operation']));
			
		}
		return array($rbac_objects,$rbac_operations);
	}
} // END class.ilObjRole
?>
