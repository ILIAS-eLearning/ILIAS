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
	* @var object ilias
	* @access public
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
	* Inserts userdata in usr_data table
	* @access	public
	* @param	array 
	* @return	boolean
	*/
	function addUser($a_data)
	{

		$passwd = md5($a_data["Passwd"]);
		$query = "INSERT INTO usr_data ".
			"(usr_id,login,passwd,firstname,surname,title,gender,email,last_login,last_update,create_date) ".
			"VALUES('".$a_data["Id"]."','".$a_data["Login"]."','".$passwd."','".$a_data["FirstName"].
			"','".$a_data["SurName"]."','".$a_data["Title"]."','".$a_data["Gender"]."','".$a_data["Email"].
			"',0,now(),now())";

		$res = $this->ilias->db->query($query);
		return true;
	}

	/**
	* Deletes a user from object_data, rbac_pa, rbac_ua and usr_data
	* @access	public
	* @param	array
	* @return	boolean
	*/
	function deleteUserData($a_usr_id)
	{
			// Einträge in object_data
		$res = $this->ilias->db->query("DELETE FROM rbac_ua ".
									   "WHERE usr_id='".$id."'");
		$res = $this->ilias->db->query("DELETE FROM usr_data ".
									   "WHERE usr_id='".$id."'");
		return true;
	}

	/**
	* updates user data in table usr_data & object_data
	* @access	public
	* @param	array		with user data
	* @return	boolean
	*/
	function updateUser($a_userdata)
	{
		$query = "UPDATE usr_data ".
				 "SET ".
				 "login = '".$a_userdata["Login"]."',".
				 "firstname = '".$a_userdata["FirstName"]."',".
				 "surname = '".$a_userdata["SurName"]."',".
				 "title = '".$a_userdata["Title"]."',".
				 "gender = '".$a_userdata["Gender"]."',".
				 "email = '".$a_userdata["Email"]."'".
				 " WHERE usr_id = '".$a_userdata["Id"]."'";
		$res = $this->ilias->db->query($query);

		$fullname = User::buildFullName($a_userdata["Title"],$a_userdata["FirstName"],$a_userdata["SurName"]);

		$query = "UPDATE object_data ".
				 "SET ".
				 "title = '".$fullname."', ".
				 "description = '', ".
				 "last_update = now() ".
				 "WHERE obj_id = '".$a_userdata["Id"]."'";
		$res = $this->ilias->db->query($query);
		
		return true;
	}
	
	/**
	* Checks if a role already exists. Role title should be unique
	* @access	public
	* @param	string
	* @return	integer
	*/
	function roleExists($a_title)
	{
		$res = $this->ilias->db->query("SELECT obj_id FROM object_data ".
								"WHERE title ='".$a_title.
								"' AND type IN('role','rolt')");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$id[] = $row->obj_id;
		}
		return count($id);
	}

	/**
	* Creates a role, inserts data in object_data, rbac_ua, rbac_pa
	* @access	public
	* @param	string		title
	* @param	string		description
	* @return	integer		new obj_id
	*/
	function addRole($a_title,$a_description)
	{
		global $rbacreview;
		
		//$rbacreview = new RbacReview($this->db);

		if ($this->roleExists($a_title))
		{
			$this->ilias->raiseError("Role Title already exists",$this->ilias->error_obj->WARNING);
		}
		// Anlegen der Rolle in object_data
		$query = "INSERT INTO object_data ".
				 "(type,title,description,owner,create_date,last_update) ".
				 "VALUES ('role','".$a_title."','".$a_description ."','-1',now(),now())";

		$res = $this->ilias->db->query($query);

		// Eintrag in rbac_ua
 		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		$row = $res->fetchRow();
	
		if (!$this->assignUser($row[0]))
		{
			$this->ilias->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->ilias->error_obj->WARNING);
		}

		// Eintrag der Permissions in rbac_pa
		$rolops = $rbacreview->getRolesOperations("role");
	
		foreach ($rolops as $r)
		{
			// TODO: set_id muss den Wert des aktuellen Container
			// erhalten, in dem die Rolle angelegt wurde
			$this->grantPermission($r["rol_id"],$r["ops_id"],$row[0],1);
		}
	
		return $row[0];
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
		$this->ilias->db->query("DELETE FROM rbac_ua ".
						 "WHERE rol_id = '".$a_rol_id ."'");
						 
		$this->ilias->db->query("DELETE FROM rbac_pa ".
								"WHERE rol_id = '".$a_rol_id."'");

		$this->deleteLocalRole($a_rol_id,$a_parent_id);
		
		// at last: remove role entry in object_data
		deleteObject($a_rol_id);						 
	
		return true;
	}

	/**
	* Deletes a template from role folder and deletes all entries in object_data, rbac_templates, rbac_fa
	* @access	public
	* @param	integer		object_id
	* @param	integer		parent_id
	* @return	boolean
	*/
	function deleteTemplate($a_obj_id,$a_parent_id)
	{
		$this->ilias->db->query("DELETE FROM object_data ".
						 "WHERE obj_id = '".$a_obj_id ."'");
		$this->ilias->db->query("DELETE FROM rbac_templates ".
						 "WHERE rol_id = '".$a_obj_id ."' ");
		$this->ilias->db->query("DELETE FROM rbac_fa ".
						 "WHERE rol_id = '".$a_obj_id ."' ");
		return true;
	}

	/**
	* Deletes a local role and entries in rbac_fa and rbac_templates
	* @access	public
	* @param	integer		object_id of role
	* @param	integer		object_id of parent object
	* @param	integer	
	* @return	boolean
	*/
	function deleteLocalRole($a_rol_id,$a_parent_id,$a_parent_obj = 0)
	{
		$query = "DELETE FROM rbac_fa ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_parent_id."'";
		$res = $this->ilias->db->query($query);

		$query = "DELETE FROM rbac_templates ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_parent_id."'";
		$res = $this->ilias->db->query($query);

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

		$a_child = $this->getRoleFolder();
		
		// CREATE IN() STATEMENT
		$in = " IN('";
		$in .= implode("','",$a_child);
		$in .= "') ";
		
		foreach ($a_path as $path)
		{
			$query = "SELECT * FROM tree ".
					 "WHERE child ".$in.
					 "AND parent = '".$path."'";
			$res = $this->ilias->db->query($query);

			while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if ($a_templates)
				{
					$roles = $this->getRoleAndTemplateListByObject($row->child);
				}
				else
				{					
					$roles = $this->getRoleListByObject($row->child);
				}

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
	* Assigns a user to a role
	* @access	public
	* @param	integer		object_id of role
	* @param	integer		object_id of user
	* @return	boolean
	*/
	function assignUser($a_rol_id,$a_usr_id = 0)
	{
		// Zuweisung des aktuellen Benutzers zu der Rolle
		if (!$a_usr_id)
		{
			$a_usr_id = $this->ilias->account->data["Id"];
		}

		$query = "INSERT INTO rbac_ua ".
				 "VALUES ('".$a_usr_id."','".$a_rol_id."')";
		$res = $this->ilias->db->query($query);

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
		$query = "DELETE FROM rbac_ua ".
				 "WHERE usr_id='".$a_usr_id."' ".
				 "AND rol_id='".$a_rol_id."'";
		$res = $this->ilias->db->query($query);

		return true;
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
		$query = "INSERT INTO rbac_pa ".
				 "VALUES ".
				 "('".$a_rol_id."','".$ops_ids."','".$a_obj_id."','".$a_parent_id."')";
		$res = $this->ilias->db->query($query);

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

		$query = "DELETE FROM rbac_pa ".
				 "WHERE obj_id = '".$a_obj_id."' ".
				 "AND set_id = '".$a_parent_id."' ".
				 $and1;
		$res = $this->ilias->db->query($query);

		return true;
	}

	/**
	* Return template permissions of an role
	* @access	public
	* @param	integer		role_id
	* @param	string		object type
	* @param	integer		parent_id
	* @return	array		operation_ids
	*/
	function getRolePermission($a_rol_id,$a_type,$a_parent_id)
	{
		$ops_id = array();

		$query = "SELECT ops_id FROM rbac_templates ".
				 "WHERE rol_id='".$a_rol_id."' ".
				 "AND type='".$a_type."' ".
				 "AND parent='".$a_parent_id."'";
		$res = $this->ilias->db->query($query);

		if (!$res->numRows())
		{
			return $ops_id;
		}
		
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id[] = $row->ops_id;
		}

		return $ops_id;
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

		$query = "SELECT * FROM rbac_templates ".
				 "WHERE rol_id = '".$a_source_id."' ".
				 "AND parent = '".$a_source_parent."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = "INSERT INTO rbac_templates ".
					 "VALUES ".
					 "('".$a_dest_id."','".$row->type."','".$row->ops_id."','".$a_dest_parent."')";
			$this->ilias->db->query($query);
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
		$query = "DELETE FROM rbac_templates ".
				 "WHERE rol_id = '".$a_rol_id."' ".
				 "AND parent = '".$a_parent_id."'";
		$res = $this->ilias->db->query($query);

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
			$query = "INSERT INTO rbac_templates ".
					 "VALUES ".
					 "('".$a_rol_id."','".$a_type."','".$op."','".$a_parent_id."')";
			$res = $this->ilias->db->query($query);
		}

		return true;
	}

	/**
	* Returns parent id of an object (obsolete)
	* @access	public
	* @param	integer		object id 
	* @return	array		parent ids
	*/
	function getSetIdByObject($a_obj_id)
	{
		$set_id = array();

		$query = "SELECT DISTINCT set_id FROM rbac_pa ".
				 "WHERE obj_id = '".$a_obj_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$set_id[] = $row->set_id;
		}

		return $set_id;
	}
	
	/**
	* Returns a list of roles in an container
	* @access	public
	* @param	integer		object id
	* @param	string		order by type,title,desc or last_update
	* @param	string		order ASC or DESC (default: ASC)
	* @return	array		set ids
	*/
	function getRoleListByObject($a_parent_id, $a_order = "", $a_direction = "ASC")
	{
		$role_list = array();

		if (!$a_order)
		{
			$a_order = "title";
		}

		$query = "SELECT * FROM object_data ".
				 "JOIN rbac_fa ".
				 "WHERE object_data.type = 'role' ".
				 "AND object_data.obj_id = rbac_fa.rol_id ".
				 "AND rbac_fa.parent = '".$a_parent_id."' ".
				 "ORDER BY ".$a_order." ".$a_direction;
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list[] = $this->fetchObject($row);
		}

		return $role_list;
	}

	/**
	* Returns a list of roles and templates of an container
	* @access	public
	* @param	integer		object id
	* @param	string		order by type,title,desc or last_update
	* @param	string		order ASC or DESC (defalut: ASC)
	* @return	array		set ids
	*/
	function getRoleAndTemplateListByObject($a_parent_id, $a_order = "", $a_direction= "ASC")
	{
		$role_list = array();

		if (!$a_order)
		{
			$a_order = "title";
		}

		$query = "SELECT * FROM object_data ".
				 "JOIN rbac_fa ".
				 "WHERE object_data.type IN ('role','rolt')".
				 "AND object_data.obj_id = rbac_fa.rol_id ".
				 "AND rbac_fa.parent = '".$a_parent_id."' ".
				 "ORDER BY ".$a_order." ".$a_direction;
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list[] = $this->fetchObject($row);
		}

		return $role_list;
	}

	/**
	* Assigns a role to an role folder
	* @access	public
	* @param	integer		object id of role
	* @param	integer		role folder id
	* @param    integer     parent object id
	* @param	string		asignable('y','n'); default: 'y'
	* @return	boolean
	*/
	function assignRoleToFolder($a_rol_id, $a_parent,$a_parent_obj, $a_assign = "y")
	{
		$query = "INSERT INTO rbac_fa (rol_id,parent,assign,parent_obj) ".
				 "VALUES ('".$a_rol_id."','".$a_parent."','".$a_assign."','".$a_parent_obj."')";
		$res = $this->ilias->db->query($query);

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
		$query = "SELECT * FROM rbac_fa ".
				 "WHERE rol_id = '".$a_rol_id."' ".
				 "AND parent = '".$a_parent_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->assign == 'y' ? true : false;
		}
	}

	/**
	* gets data of an role
	* @access	public
	* @param	integer		object id  
	* @return	array		array of set ids
	*/
	function getRoleData($a_obj_id)
	{
		$role_list = array();

		$query = "SELECT * FROM object_data ".
				 "WHERE type = 'role' ".
				 "AND obj_id = '".$a_obj_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list[] = $this->fetchObject($row);
		}

		return $role_list;
	}

	/**
	* returns an array with role ids assigned to a role folder
	* @access	public
	* @param	integer		role id
	* @return	array		object ids of role folders
	*/
	function getFoldersAssignedToRole($a_rol_id)
	{
		$query = "SELECT DISTINCT parent,parent_obj FROM rbac_fa ".
				 "WHERE rol_id = '".$a_rol_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$folders[] = array(
				"parent"     => $row->parent,
				"parent_obj" => $row->parent_obj);
		}

		return $folders ? $folders : array();
	}

	/**
	* return an array with role ids
	* @access	public
	* @param	integer		parent id  
	* @return	array		Array with rol_ids
	*/
	function getRolesAssignedToFolder($a_parent)
	{
		$query = "SELECT rol_id FROM rbac_fa ".
				 "WHERE parent = '".$a_parent."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
		
		$query = "SELECT DISTINCT parent FROM rbac_fa";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent[] = $row->parent;
		}

		return $parent;
	}

	/**
	* returns the data of a role folder assigned to an object
	* @access	public
	* @param	integer		parent id
	* @return	array
	*/
	function getRoleFolderOfObject($a_parent)
	{
		$rol_data = array();

		$query = "SELECT * FROM tree ".
				 "LEFT JOIN object_data ON tree.child=object_data.obj_id ".
				 "WHERE parent = '".$a_parent."' ".
				 "AND type = 'rolf'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol_data["child"] = $row->obj_id;
			$rol_data["parent"] = $row->parent;
		}

		return $rol_data;
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
	function getParentRoleIds($a_start_node = 0,$a_start_parent = 0,$a_templates = false)
	{
		global $tree;
		
		$a_start_node = $a_start_node ? $a_start_node : $_GET["obj_id"];
		$a_start_parent = $a_start_parent ? $a_start_parent : $_GET["parent"];

		$pathIds  = $tree->getPathId($a_start_node,$a_start_parent);
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
		$query = "SELECT * FROM rbac_ta WHERE typ_id = '".$a_typ_id."'";
		$res = $this->ilias->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id[] = $row->ops_id;
		}

		return $ops_id ? $ops_id : array();
	}
	
	/**
	* get data of object
	* @access	private
 	* @param	object	db	db result object containing object_data
	* @return	array		2-dim (int/str) object_data
	*/
	function fetchObject($a_row)
	{
		$data = array(
					"obj_id"		=> $a_row->obj_id,
					"type"			=> $a_row->type,
					"title"			=> $a_row->title,
					"description"	=> $a_row->description,
					"owner"			=> $a_row->owner,
					"create_date"	=> $a_row->create_date,
					"last_update"	=> $a_row->last_update
					);
		return $data ? $data : array();
	}
} // END class.rbacAdmin

?>