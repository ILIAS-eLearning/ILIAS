<?php
/**
* Class RbacAdmin 
* core functions for role based access control
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package rbac
*/
class RbacAdmin
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
	function RbacAdmin()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}

	/**
	* deletes a user from rbac_ua
	* @access	public
	* @param	integer	user_id
	* @return	boolean
	*/
	function removeUser($a_usr_id)
	{
		$q = "DELETE FROM rbac_ua WHERE usr_id='".$a_usr_id."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* TODO: use DISTINCT and return true/false !
	* Checks if a role already exists. Role title should be unique
	* @access	public
	* @param	string
	* @return	integer
	*/
	function roleExists($a_title)
	{
		$q = "SELECT obj_id FROM object_data ".
			 "WHERE title ='".$a_title."' ".
			 "AND type IN('role','rolt')";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$id[] = $row->obj_id;
		}

		return count($id);
	}

	function addRole()
	{

	}

	/**
	* Deletes a role and deletes entries in object_data, rbac_pa, rbac_templates, rbac_ua, rbac_fa
	* @access	public
	* @param	integer		obj_id of role (role_id)
	* @param	integer		obj_id of role folder (parent_id)
	* @return	boolean
	*/
	function deleteRole($a_rol_id,$a_parent_id)
	{
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

		$this->deleteLocalRole($a_rol_id,$a_parent_id);
		
		return true;
	}

	/**
	* Deletes a template from role folder and deletes all entries in rbac_templates, rbac_fa
	* TODO: function could be merged with rbacAdmin::deleteLocalRole
 	* @access	public
	* @param	integer		object_id
	* @return	boolean
	*/
	function deleteTemplate($a_obj_id)
	{
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
	* @param	integer	object_id of parent object
	* @param	integer	THIS PARAM IS SENSELESS
	* @return	boolean
	*/
	function deleteLocalRole($a_rol_id,$a_parent_id,$a_parent_obj = 0)
	{
		$q = "DELETE FROM rbac_fa ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_parent_id."'";
		$this->ilias->db->query($q);

		$q = "DELETE FROM rbac_templates ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_parent_id."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Get parent roles in a path. If last parameter is set 'true'
	* it delivers also all templates in the path
	* @access	public
	* @param	array		path_id
	* @param	boolean		true for role templates (default: false)
	* @return	boolean
	*/
	function getParentRoles($a_path,$a_templates = false)
	{
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
	* @param	boolean	true means default role
	* @return	boolean
	*/
	function assignUser($a_rol_id,$a_usr_id,$a_default )
	{
		$a_default = $a_default ? 'y' : 'n';

		$q = "INSERT INTO rbac_ua ".
			 "VALUES ('".$a_usr_id."','".$a_rol_id."','".$a_default."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Deassigns a user from a role
	* @access	public
	* @param	integer		object id of role
	* @param	integer		user id
	* @return	boolean
	*/
	function deassignUser($a_rol_id,$a_usr_id)
	{
		$q = "DELETE FROM rbac_ua ".
			 "WHERE usr_id='".$a_usr_id."' ".
			 "AND rol_id='".$a_rol_id."'";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Update of default role
	* @access	public
	* @param	integer		object id of role
	* @param	integer		user id
	* @return	boolean
	*/
	function updateDefaultRole($a_rol_id,$a_usr_id)
	{
		$this->deassignUser($this->getDefaultRole($a_usr_id),$a_usr_id);
		$this->deassignUser($a_rol_id,$a_usr_id);

		return $this->assignUser($a_rol_id,$a_usr_id,true);
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
		$q = "SELECT * FROM rbac_ua ".
			 "WHERE usr_id = '".$a_usr_id."' ".
			 "AND default_role = 'y'";
		$row = $this->ilias->db->getRow($q);

		return $row->rol_id;
	}

	/**
	* Grants permissions to an object
	* @access	public
	* @param	integer		object id of role
	* @param	array		array of operation ids
	* @param	integer		object id
	* @param	integer		obj id of parent object
	* @return	boolean
	*/
	function grantPermission($a_rol_id,$a_ops,$a_obj_id,$a_parent_id)
	{
		// Serialization des ops_id Arrays
		$ops_ids = addslashes(serialize($a_ops));

		$q = "INSERT INTO rbac_pa ".
			 "VALUES ".
			 "('".$a_rol_id."','".$ops_ids."','".$a_obj_id."','".$a_parent_id."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Revokes permissions of object
	* @access	public
	* @param	integer		object_id
	* @param	integer		object_id of parent object
	* @param	integer		role_id (optional: if you want to revoke permissions of object only for a specific role)
	* @return	boolean
	*/
	function revokePermission($a_obj_id, $a_parent_id, $a_rol_id = 0)
	{
		if ($a_rol_id)
		{
			$and1 = " AND rol_id = '".$a_rol_id."'";
		}
		else
		{
			$and1 = "";
		}

		$q = "DELETE FROM rbac_pa ".
			 "WHERE obj_id = '".$a_obj_id."' ".
			 "AND set_id = '".$a_parent_id."' ".
			 $and1;
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Return template permissions of an role
	* @access	public
	* @param	integer	role_id
	* @param	string	object type
	* @param	integer	parent_id
	* @return	array	operation_ids
	*/
	function getRolePermission($a_rol_id,$a_type,$a_parent_id)
	{
		$ops_arr = array();

		$q = "SELECT ops_id FROM rbac_templates ".
			 "WHERE rol_id='".$a_rol_id."' ".
			 "AND type='".$a_type."' ".
			 "AND parent='".$a_parent_id."'";
		$r = $this->ilias->db->query($q);

		if (!$r->numRows())
		{
			return $ops_arr;
		}
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_arr[] = $row->ops_id;
		}

		return $ops_arr;
	}

	/**
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
		$a_dest_id = $a_dest_id ? $a_dest_id : $a_source_id;

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
	* @param	integer		role_id
	* @param	integer		object_id of parent object
	* @return	boolean
	*/
	function deleteRolePermission($a_rol_id,$a_parent_id)
	{
		$q = "DELETE FROM rbac_templates ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_parent_id."'";
		$this->ilias->db->query($q);

		return true;
	}
	
	/**
	* Inserts template permissions in rbac_templates
	* @access	public
	* @param	integer		role_id
	* @param	string		object type
	* @param	array		operation_ids
	* @param	integer		object_id of parent object
	* @return	boolean
	*/
	function setRolePermission($a_rol_id,$a_type,$a_ops,$a_parent_id)
	{
		if (!$a_ops)
		{
			$a_ops = array();
		}

		foreach ($a_ops as $op)
		{
			$q = "INSERT INTO rbac_templates ".
				 "VALUES ".
				 "('".$a_rol_id."','".$a_type."','".$op."','".$a_parent_id."')";
			$this->ilias->db->query($q);
		}

		return true;
	}

	/**
	* TODO: maybe deprecated
	* Returns a list of roles in an container
	* @access	public
	* @param	integer	object id
	* @param	boolean	if true fetch template roles too
	* @param	string	order by type,title,desc or last_update
	* @param	string	order ASC or DESC (default: ASC)
	* @return	array	set ids
	*/
	function getRoleListByObject($a_parent_id,$a_templates,$a_order = "",$a_direction = "ASC")
	{
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
			 "AND rbac_fa.parent = '".$a_parent_id."' ".
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
	* @param	integer		role folder id
	* @param    integer     parent object id
	* @param	string		assignable('y','n'); default: 'y'
	* @return	boolean
	*/
	function assignRoleToFolder($a_rol_id, $a_parent,$a_parent_obj, $a_assign = "y")
	{
		$q = "INSERT INTO rbac_fa (rol_id,parent,assign,parent_obj) ".
			 "VALUES ('".$a_rol_id."','".$a_parent."','".$a_assign."','".$a_parent_obj."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Check if its possible to assign users
	* @access	public
	* @param	integer		object id
	* @param	integer		parent id
	* @return	boolean 
	*/
	function isAssignable($a_rol_id, $a_parent_id)
	{
		$q = "SELECT * FROM rbac_fa ".
			 "WHERE rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_parent_id."'";
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
	* @param	integer		parent id  
	* @return	array		Array with rol_ids
	*/
	function getRolesAssignedToFolder($a_parent)
	{
		$q = "SELECT rol_id FROM rbac_fa ".
			 "WHERE parent = '".$a_parent."'";
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
	* TODO: function should be renamed
	* get all objects in which the inheritance was stopped
	* @access	public
	* @param	integer	role_id
	* @return	array
	*/
	function getObjectsWithStopedInheritance($a_rol_id)
	{
		$parent_obj = array();
		
		$q = "SELECT DISTINCT parent_obj FROM rbac_fa ".
			 "WHERE rol_id = '".$a_rol_id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent_obj[] = $row->parent_obj;
		}

		return $parent_obj;
	}
	/**
	* returns the data of a role folder assigned to an object
	* @access	public
	* @param	integer		parent id
	* @return	array
	*/
	function getRoleFolderOfObject($a_parent)
	{
		$rolf_data = array();

		// parent obj_id is enough for information since linked objects(they have duplicate object_ids) are not
		// allowed to contain role folders
		$q = "SELECT * from tree ,object_data ".
			 "WHERE tree.parent = '".$a_parent."' ".
			 "AND tree.child = object_data.obj_id ".
			 "AND object_data.type = 'rolf' ".
			 "AND tree.tree = '1'";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			//$rolf_data["child"] = $row->child;
			//$rolf_data["parent"] = $row->parent;
			$rolf_data = $row;
		}

		return $rolf_data;
	}

	/**
	* get role ids of all parent roles, if last parameter is set true
	* you get also all parent templates
	* @access	private
	* @param	integer		object id of start node
	* @param	integer		object id of start parent
	* @param	boolean		true for role templates (default: false)
	* @return	string 
	*/
	function getParentRoleIds($a_end_node,$a_templates = false)
	{
		global $tree;
		
		$pathIds  = $tree->getPathId($a_end_node);
		
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
	* Fetch loaded modules or possible modules in context
	* @access	public
	* @param	string
	*/
	function getModules ($a_objname)
	{
		global $objDefinition;
		
		$arr = array();
		
		$type_list = $objDefinition->getSubObjectsAsString($a_objname);
		
		if (empty($ATypeList))
		{
			$q = "SELECT * FROM object_data ".
				 "WHERE type = 'typ' ORDER BY type";
		}
		else
		{
			$q = "SELECT * FROM object_data ".
				 "WHERE title IN ($type_list) AND type='typ'";
		}

		$r = $this->ilias->db->query($q);
		
		$rolf_exist = false;
		
		if (count($this->getRoleFolderOfObject($a_obj_id)) > 0)
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
} // END class.rbacAdmin
?>