<?php
/**
* Class ilRbacAdmin 
* core functions for role based access control
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package rbac
*/
class ilRbacAdmin
{
	/**
	* ilias object
	* @var		object	ilias
	* @access	public
	*/
	var $ilias;

	/**
	* Constructor
	* @access	public
	*/
	function ilRbacAdmin()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}

	/**
	* deletes a user from rbac_ua
	* @access	public
	* @param	integer	user_id
	* @return	boolean	true on success
	*/
	function removeUser($a_usr_id)
	{
		global $log;

		if (!isset($a_usr_id))
		{
			$message = get_class($this)."::removeUser(): No usr_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "DELETE FROM rbac_ua WHERE usr_id='".$a_usr_id."'";
		$this->ilias->db->query($q);
		
		return true;
	}

	/**
	* TODO: use DISTINCT and return true/false !
	* Checks if a role already exists. Role title should be unique
	* @access	public
	* @param	string	role title
	* @return	boolean	true if exists
	*/
	function roleExists($a_title)
	{
		if (empty($a_title))
		{
			$message = get_class($this)."::roleExists(): No title given!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT DISTINCT obj_id FROM object_data ".
			 "WHERE title ='".$a_title."' ".
			 "AND type IN('role','rolt')";
		$r = $this->ilias->db->query($q);

		if ($r->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* add Role
	* @access	public
	*/
	function addRole()
	{

	}

	/**
	* Deletes a role and deletes entries in object_data, rbac_pa, rbac_templates, rbac_ua, rbac_fa
	* @access	public
	* @param	integer		obj_id of role (role_id)
	* @param	integer		ref_id of role folder (ref_id)
	* @return	boolean
	*/
	function deleteRole($a_rol_id,$a_ref_id)
	{
		if (!isset($a_rol_id) or !isset($a_ref_id))
		{
			$message = get_class($this)."::deleteRole(): Missing parameter! role_id: ".$a_rol_id." ref_id of role folder: ".$a_ref_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		// TODO: check assigned users before deletion
		
		// delete user assignements
		$q = "DELETE FROM rbac_ua ".
			 "WHERE rol_id = '".$a_rol_id ."'";
		$this->ilias->db->query($q);
		
		// delete permission assignments
		$q = "DELETE FROM rbac_pa ".
			 "WHERE rol_id = '".$a_rol_id."'";
		$this->ilias->db->query($q);
		
		//TODO: delete rbac_templates and rbac_fa

		$this->deleteLocalRole($a_rol_id,$a_ref_id);
		
		return true;
	}

	/**
	* Deletes a template from role folder and deletes all entries in rbac_templates, rbac_fa
	* TODO: function could be merged with rbacAdmin::deleteLocalRole
 	* @access	public
	* @param	integer		object_id of role template
	* @return	boolean
	*/
	function deleteTemplate($a_obj_id)
	{
		if (!isset($a_obj_id))
		{
			$message = get_class($this)."::deleteTemplate(): No obj_id given!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "DELETE FROM rbac_templates ".
			 "WHERE rol_id = '".$a_obj_id ."'";
		$this->ilias->db->query($q);

		$q = "DELETE FROM rbac_fa ".
			 "WHERE rol_id = '".$a_obj_id ."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Deletes a local role and entries in rbac_fa and rbac_templates
	* @access	public
	* @param	integer	object_id of role
	* @param	integer	ref_id of role folder
	* @return	boolean ture on success
	*/
	function deleteLocalRole($a_rol_id,$a_ref_id)
	{
		if (!isset($a_rol_id) or !isset($a_ref_id))
		{
			$message = get_class($this)."::deleteLocalRole(): Missing parameter! role_id: ".$a_rol_id." ref_id of role folder: ".$a_ref_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "DELETE FROM rbac_fa ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_ref_id."'";
		$this->ilias->db->query($q);

		$q = "DELETE FROM rbac_templates ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_ref_id."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Get parent roles in a path. If last parameter is set 'true'
	* it delivers also all templates in the path
	* @access	public
	* @param	array	array with path_ids
	* @param	boolean	true for role templates (default: false)
	* @return	array	array with all parent roles (obj_ids)
	*/
	function getParentRoles($a_path,$a_templates = false)
	{
		if (!isset($a_path) or !is_array($a_path))
		{
			$message = get_class($this)."::getParentRoles(): No path given or wrong datatype!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$parentRoles = array();

		$child = $this->getRoleFolder();
		
		// CREATE IN() STATEMENT
		$in = " IN('";
		$in .= implode("','",$child);
		$in .= "') ";
		
		foreach ($a_path as $path)
		{
			//TODO: move this to tree class !!!!
			$q = "SELECT * FROM tree ".
				 "WHERE child ".$in.
				 "AND parent = '".$path."'";
			$r = $this->ilias->db->query($q);

			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$roles = $this->getRoleListByObject($row->child,$a_templates);

				foreach ($roles as $role)
				{
					$id = $role["obj_id"];
					// TODO: need a parent here?
					$role["parent"] = $row->child;
					$parentRoles[$id] = $role;
				}
			}
		}

		return $parentRoles;
	}

	/**
	* Assigns an user to a role
	* @access	public
	* @param	integer	object_id of role
	* @param	integer	object_id of user
	* @param	boolean	true means default role (optional
	* @return	boolean
	*/
	function assignUser($a_rol_id,$a_usr_id,$a_default = false)
	{
		if (!isset($a_rol_id) or !isset($a_usr_id))
		{
			$message = get_class($this)."::assignUser(): Missing parameter! role_id: ".$a_rol_id." usr_id: ".$a_usr_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}
		
		if ($a_default)
		{
			$a_default = "y";
		}
		else
		{
			$a_default = "n";
		}

		$q = "INSERT INTO rbac_ua ".
			 "VALUES ('".$a_usr_id."','".$a_rol_id."','".$a_default."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Deassigns a user from a role
	* @access	public
	* @param	integer	object id of role
	* @param	integer	object id of user
	* @return	boolean	true on success
	*/
	function deassignUser($a_rol_id,$a_usr_id)
	{
		if (!isset($a_rol_id) or !isset($a_usr_id))
		{
			$message = get_class($this)."::deassignUser(): Missing parameter! role_id: ".$a_rol_id." usr_id: ".$a_usr_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "DELETE FROM rbac_ua ".
			 "WHERE usr_id='".$a_usr_id."' ".
			 "AND rol_id='".$a_rol_id."'";
		$this->ilias->db->query($q);
		
		return true;
	}

	/**
	* Update of the default role from a user
	* @access	public
	* @param	integer	object id of role
	* @param	integer	user id
	* @return	boolean true if role was changed
	*/
	function updateDefaultRole($a_rol_id,$a_usr_id)
	{
		global $log;

		if (!isset($a_rol_id) or !isset($a_usr_id))
		{
			$message = get_class($this)."::updateDefaultRole(): Missing parameter! role_id: ".$a_rol_id." usr_id: ".$a_usr_id;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$current_default_role = $this->getDefaultRole($a_usr_id);
		
		if ($current_default_role != $a_rol_id)
		{
			$this->deassignUser($current_default_role,$a_usr_id);	
			return $this->assignUser($a_rol_id,$a_usr_id,true);
		}
		
		return false;
	}

	/**
	* get Default role
	* @access	public
	* @param	integer		object id of role
	* @param	integer		user id
	* @return	boolean
	*/
	function getDefaultRole($a_usr_id)
	{
		global $log;

		if (!isset($a_usr_id))
		{
			$message = get_class($this)."::getDefaultRole(): No usr_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT * FROM rbac_ua ".
			 "WHERE usr_id = '".$a_usr_id."' ".
			 "AND default_role = 'y'";
		$row = $this->ilias->db->getRow($q);

		return $row->rol_id;
	}

	/**
	* Grants permissions to an object and a specific role
	* @access	public
	* @param	integer	object id of role
	* @param	array	array of operation ids
	* @param	integer	reference id of that object which is granted the permissions
	* @return	boolean
	*/
	function grantPermission($a_rol_id,$a_ops,$a_ref_id)
	{
		if (!isset($a_rol_id) or !isset($a_ops) or !isset($a_ref_id))
		{
			$this->ilias->raiseError(get_class($this)."::grantPermission(): Missing parameter! ".
							"role_id: ".$a_rol_id." ref_id: ".$a_ref_id." operations: ",$this->ilias->error_obj->WARNING);
		}

		if (!is_array($a_ops))
		{
			$this->ilias->raiseError(get_class($this)."::grantPermission(): Wrong datatype for operations!",$this->ilias->error_obj->WARNING);
		}

		// Serialization des ops_id Arrays
		$ops_ids = addslashes(serialize($a_ops));

		$q = "INSERT INTO rbac_pa (rol_id,ops_id,obj_id,set_id) ".
			 "VALUES ".
			 "('".$a_rol_id."','".$ops_ids."','".$a_ref_id."','".$a_ref_id."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Revokes permissions of object
	* Revokes all permission for all roles for that object (with this reference).
	* When a role_id is given this applies only to that role
	* @access	public
	* @param	integer	reference id of object where permissions should be revoked
	* @param	integer	role_id (optional: if you want to revoke permissions of object only for a specific role)
	* @return	boolean
	*/
	function revokePermission($a_ref_id,$a_rol_id = 0)
	{
		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::revokePermission(): Missing parameter! ref_id: ".$a_ref_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		if ($a_rol_id)
		{
			$and1 = " AND rol_id = '".$a_rol_id."'";
		}
		else
		{
			$and1 = "";
		}

		// TODO: rename db_field from obj_id to ref_id and remove db-field set_id
		$q = "DELETE FROM rbac_pa ".
			 "WHERE obj_id = '".$a_ref_id."' ".
			 $and1;
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Return template permissions of an role
	* The ref_id of the role folder (parent object) is necessary to distinguish local roles
	* settings that are derived from another role. Roles using an internal referencing system
	* @access	public
	* @param	integer	object id of role
	* @param	string	object type
	* @param	integer	ref_id of role folder
	* @return	array	operation_ids or empty
	*/
	function getRolePermission($a_rol_id,$a_type,$a_ref_id)
	{
		if (!isset($a_rol_id) or !isset($a_type) or !isset($a_ref_id))
		{
			$message = get_class($this)."::getRolePermission(): Missing parameter! ".
					   "role_id: ".$a_rol_id." type_id: ".$a_type." ref_id: ".$a_ref_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$ops_arr = array();

		$q = "SELECT ops_id FROM rbac_templates ".
			 "WHERE rol_id='".$a_rol_id."' ".
			 "AND type='".$a_type."' ".
			 "AND parent='".$a_ref_id."'";
		$r = $this->ilias->db->query($q);

		if ($r->numRows() > 0)
		{
			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$ops_arr[] = $row->ops_id;
			}
		}

		return $ops_arr;
	}

	/**
	* TODO: we can't get rid off the parents if roles are referenced in this way!
	* Copies template permissions
	* @access	public
	* @param	integer		role_id source
	* @param	integer		parent_id source
	* @param	integer		role_id destination
	* @param	integer		parent_id destination
	* @return	boolean 
	*/
	function copyRolePermission($a_source_id,$a_source_parent,$a_dest_parent,$a_dest_id)
	{
		if (!isset($a_source_id) or !isset($a_source_parent) or !isset($a_dest_id) or !isset($a_dest_parent))
		{
			$message = get_class($this)."::copyRolePermission(): Missing parameter! source_id: ".$a_source_id.
					   " source_parent_id: ".$a_source_parent.
					   " dest_id : ".$a_dest_id.
					   " dest_parent_id: ".$a_dest_parent;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		//$a_dest_id = $a_dest_id ? $a_dest_id : $a_source_id;

		$q = "SELECT * FROM rbac_templates ".
			 "WHERE rol_id = '".$a_source_id."' ".
			 "AND parent = '".$a_source_parent."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$q = "INSERT INTO rbac_templates ".
				 "VALUES ".
				 "('".$a_dest_id."','".$row->type."','".$row->ops_id."','".$a_dest_parent."')";
			$this->ilias->db->query($q);
		}

		return true;
	}
	
	/**
	* Deletes a template
	* @access	public
	* @param	integer		object id of role
	* @param	integer		ref_id of role folder
	* @return	boolean
	*/
	function deleteRolePermission($a_rol_id,$a_ref_id)
	{
		if (!isset($a_rol_id) or !isset($a_ref_id))
		{
			$message = get_class($this)."::deleteRolePermission(): Missing parameter! role_id: ".$a_rol_id." ref_id: ".$a_ref_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "DELETE FROM rbac_templates ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_ref_id."'";
		$this->ilias->db->query($q);

		return true;
	}
	
	/**
	* Inserts template permissions in rbac_templates
	* @access	public
	* @param	integer		role_id
	* @param	string		object type
	* @param	array		operation_ids
	* @param	integer		ref_id of role folder object
	* @return	boolean
	*/
	function setRolePermission($a_rol_id,$a_type,$a_ops,$a_ref_id)
	{
		if (!isset($a_rol_id) or !isset($a_type) or !isset($a_ops) or !isset($a_ref_id))
		{
			$message = get_class($this)."::setRolePermission(): Missing parameter!".
					   " role_id: ".$a_rol_id.
					   " type: ".$a_type.
					   " operations: ".$a_ops.
					   " ref_id: ".$a_ref_id;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		if (!is_string($a_type) or empty($a_type))
		{
			$message = get_class($this)."::setRolePermission(): a_type is no string or empty!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		if (!is_array($a_ops) or empty($a_ops))
		{
			$message = get_class($this)."::setRolePermission(): a_ops is no array or empty!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}
		
		foreach ($a_ops as $op)
		{
			$q = "INSERT INTO rbac_templates ".
				 "VALUES ".
				 "('".$a_rol_id."','".$a_type."','".$op."','".$a_ref_id."')";
			$this->ilias->db->query($q);
		}

		return true;
	}

	/**
	* TODO: maybe deprecated
	* Returns a list of roles in an container
	* @access	public
	* @param	integer	ref_id
	* @param	boolean	if true fetch template roles too
	* @param	string	order by type,title,desc or last_update
	* @param	string	order ASC or DESC (default: ASC)
	* @return	array	set ids
	*/
	function getRoleListByObject($a_ref_id,$a_templates,$a_order = "",$a_direction = "ASC")
	{
		if (!isset($a_ref_id) or !isset($a_templates))
		{
			$message = get_class($this)."::getRoleListByObject(): Missing parameter!".
					   "ref_id: ".$a_ref_id.
					   "tpl_flag: ".$a_templates;
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$role_list = array();

		if (!$a_order)
		{
			$a_order = "title";
		}

		if ($a_templates)
		{
			 $where = "WHERE object_data.type IN ('role','rolt') ";		
		}
		else
		{
			$where = "WHERE object_data.type = 'role' ";
		}
	
		$q = "SELECT * FROM object_data ".
			 "JOIN rbac_fa ".$where.
			 "AND object_data.obj_id = rbac_fa.rol_id ".
			 "AND rbac_fa.parent = '".$a_ref_id."' ".
			 "ORDER BY ".$a_order." ".$a_direction;
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list[] = fetchObjectData($row);
		}

		return $role_list;
	}

	/**
	* Assigns a role to an role folder
	* @access	public
	* @param	integer		object id of role
	* @param	integer		ref_id of role folder
	* @param	string		assignable('y','n'); default: 'y'
	* @return	boolean
	*/
	function assignRoleToFolder($a_rol_id,$a_parent,$a_parent_obj,$a_assign = "y")
	{
		global $log;

		if (!isset($a_rol_id) or !isset($a_parent) or !isset($a_parent_obj) or func_num_args() != 4)
		{
			$message = get_class($this)."::assignRoleToFolder(): Missing Parameter!".
					   " role_id: ".$a_rol_id.
					   " parent_id: ".$a_parent.
					   " parent_obj: ".$a_parent_obj.
					   " assign: ".$a_assign;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "INSERT INTO rbac_fa (rol_id,parent,assign,parent_obj) ".
			 "VALUES ('".$a_rol_id."','".$a_parent."','".$a_assign."','".$a_parent_obj."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Check if its possible to assign users
	* @access	public
	* @param	integer	object id of role
	* @param	integer	ref_id of object in question
	* @return	boolean 
	*/
	function isAssignable($a_rol_id, $a_ref_id)
	{
		global $log;
		
		if (!isset($a_rol_id) or !isset($a_ref_id))
		{
			$message = get_class($this)."::isAssignable(): Missing parameter!".
					   " role_id: ".$a_rol_id." ,ref_id: ".$a_ref_id;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT * FROM rbac_fa ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_ref_id."'";
		$row = $this->ilias->db->getRow($q);

		return $row->assign == 'y' ? true : false;
	}

	/**
	* TODO: maybe DEPRECATED
	* returns an array with role ids assigned to a role folder
	* @access	public
	* @param	integer		role id
	* @return	array		object ids of role folders
	*/
	function getFoldersAssignedToRole($a_rol_id)
	{
		global $log;

		if (!isset($a_rol_id))
		{
			$message = get_class($this)."::getFoldersAssignedToRole(): No role_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT DISTINCT parent,parent_obj FROM rbac_fa ".
			 "WHERE rol_id = '".$a_rol_id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$folders[] = array(
				"parent"     => $row->parent,
				"parent_obj" => $row->parent_obj);
		}

		return $folders ? $folders : array();
	}

	/**
	* TODO: function should be renamed
	* return an array with role ids
	* @access	public
	* @param	integer		ref_id of object  
	* @return	array		Array with rol_ids
	*/
	function getRolesAssignedToFolder($a_ref_id)
	{
		global $log;

		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::getRolesAssignedToFolder(): No ref_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT rol_id FROM rbac_fa ".
			 "WHERE parent = '".$a_ref_id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol_id[] = $row->rol_id;
		}

		return $rol_id ? $rol_id : array();
	}
	
	/**
	* get all role folder ids
	* @access	public
	* @return	array
	*/
	function getRoleFolder()
	{
		$parent = array();
		
		$q = "SELECT DISTINCT parent FROM rbac_fa";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent[] = $row->parent;
		}

		return $parent;
	}


	/**
	* returns the data of a role folder assigned to an object
	* @access	public
	* @param	integer		ref_id of object with a rolefolder object under it
	* @return	array
	*/
	function getRoleFolderOfObject($a_ref_id)
	{
		global $tree, $log;
		
		if (!isset($a_ref_id))
		{
			$message = get_class($this)."::getRoleFolderOfObject(): No ref_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$childs = $tree->getChildsByType($a_ref_id,"rolf");

		return $childs[0] ? $childs[0] : array();
	}

	/**
	* get role ids of all parent roles, if last parameter is set true
	* you get also all parent templates
	* @access	private
	* @param	integer		ref_id of an object which is end node
	* @param	boolean		true for role templates (default: false)
	* @return	string 
	*/
	function getParentRoleIds($a_endnode_id,$a_templates = false)
	{
		global $tree, $log;

		if (!isset($a_endnode_id))
		{
			$message = get_class($this)."::getParentRoleIds(): No node_id (ref_id) given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}
	
		$pathIds  = $tree->getPathId($a_endnode_id);

		// add system folder since it may not in the path
		$pathIds[0] = SYSTEM_FOLDER_ID;

		return $this->getParentRoles($pathIds,$a_templates);
	}

	/**
	* all possible operations of a type
	* @access	public
	* @param	integer 
	* @return	array
	*/
	function getOperationsOnType($a_typ_id)
	{
		global $log;

		if (!isset($a_typ_id))
		{
			$message = get_class($this)."::getOperationsOnType(): No type_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT * FROM rbac_ta WHERE typ_id = '".$a_typ_id."'";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id[] = $row->ops_id;
		}

		return $ops_id ? $ops_id : array();
	}
	
	/**
	* TODO: function needs better explanation and should be renamed
	* TODO: using var $a_obj_id which is not known in function
	* Fetch loaded modules or possible modules in context
	* @access	public
	* @param	string
	*/
	function getModules ($a_type,$a_ref_id)
	{
		global $objDefinition;
		
		if (!isset($a_type) or !isset($a_ref_id))
		{
			$message = get_class($this)."::getModules(): Missing parameter!".
					   "type: ".$a_type." ref_id: ".$a_ref_id;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$arr = array();
		
		$type_list = $objDefinition->getSubObjectsAsString($a_type);
		
		if (empty($type_list))
		{
			$q = "SELECT * FROM object_data ".
				 "WHERE type = 'typ' ORDER BY type";
		}
		else
		{
			$q = "SELECT * FROM object_data ".
				 "WHERE title IN (".$type_list.") AND type='typ'";
		}

		$r = $this->ilias->db->query($q);
		
		$rolf_exist = false;
		
		if (count($this->getRoleFolderOfObject($a_ref_id)) > 0)
		{
			$rolf_exist = true;
		}
		
		if ($r->numRows() > 0)
		{
			while ($data = $r->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (!$rolf_exist || ($data["title"] != "rolf"))
				{
					$arr[$data["title"]] = $data["description"];
				}
			}
		}

		return $arr;
	}
} // END class.ilRbacAdmin
?>
