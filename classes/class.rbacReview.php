<?php
/**
* class RbacReview
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends PEAR
* @package rbac
*/
class RbacReview extends PEAR
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
	function RbacReview()
	{
		global $ilias;

		$this->ilias =& $ilias;
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer
	* @return	array		2-dim array: role_id <-> user_id
	*/
	function assignedUsers($a_rol_id)
	{
		$usr = array();

		$query = "SELECT usr_id FROM rbac_ua WHERE rol_id='".$a_rol_id."'";
		$res = $this->ilias->db->query($query);

		while($row = $res->fetchRow())
		{
			array_push($usr,$row[0]);
		}

		return $usr;
	}
	
	/**
	* get user data
	* @access	public
	* @param	integer
	* @return	array		user data
	*/
	function getUserData($a_usr_id)
	{
		$query = "SELECT * FROM user_data WHERE usr_id='".$a_usr_id."'";
		$res = $this->ilias->db->query($query);	

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{	
			$arr = array(
						"usr_id"		=>	$row->usr_id,
						"login"			=>	$row->login,
						"firstname"		=>	$row->firstname,
						"surname"		=>	$row->surname,
						"title"			=>	$row->title,
						"gender"		=>	$row->gender,	
						"email"			=>	$row->email,
						"last_login"	=>	$row->last_login,
						"last_update"	=>	$row->last_update,
						"create_date"	=>	$row->create_date
						);
		}
		
		return $arr;
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer		usr_id
	* @return	integer		RoleID des Users
	*/
	function assignedRoles($a_usr_id)
	{
		$rol = array();
		
		$query = "SELECT rol_id FROM rbac_ua WHERE usr_id = '".$a_usr_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol[] = $row->rol_id;
		}

		if (!count($rol))
		{
			$this->ilias->raiseError("No such user",$this->ilias->error_obj->WARNING);
		}

		return $rol;
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer		usr_id
	* @return	string		Role Title des Users
	*/
	function assignedRoleTitles($a_usr_id)
	{
		$query = "SELECT title FROM object_data ".
				 "JOIN rbac_ua ".
				 "WHERE object_data.obj_id = rbac_ua.rol_id ".
				 "AND rbac_ua.usr_id = '".$a_usr_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow())
		{
			$role_title[] = $row[0];
		}

		if (!count($rol))
		{
			$this->ilias->raiseError("No such role",$this->ilias->error_obj->WARNING);
		}

		return $role_title;
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer
	* @param	integer
	* @return	array		2-dim. Array: Objekt-Permissions,Object-ID zu einer Rolle
	*/
	function rolePermissons($a_rol_id,$a_obj_id = 0)
	{
		$ops = array();
		   
		$query = "SELECT ops_id,obj_id FROM rbac_pa WHERE rol_id='".$a_rol_id."'";

		if ($a_obj_id)
		{
			$query .= " AND obj_id='".$a_obj_id."'";
		}
		
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow())
		{
			array_push($ops,$row[0],$row[1]);
		}

		if (!count($ops))
		{
			$this->ilias->raiseError("No such Role or Object!",$this->ilias->error_obj->WARNING);
		}

		return $ops;
	}
	
	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer		user_id
	* @return	array		object permissions of a user
	*/
	function userPermissions($a_usr_id)
	{
		$ops = array();

		$query = "SELECT ops_id,obj_id FROM rbac_pa ".
				 "JOIN rbac_ua WHERE rbac_ua.usr_id='".$a_usr_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow())
		{
			array_push($ops,$row[0]);
		}

		if (!count($ops))
		{
			$this->ilias->raiseError("No such user",$this->ilias->error_obj->WARNING);
		}

		return $ops;
	}

	/**
	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function getErrorMessage()
	{
		return $this->Error;
	}
	
	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function createSession()
	{

	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function deleteSession()
	{

	}
	
	/**
	* adds an active role in $_SESSION["RoleId"]
	* @access	public
	*/
	function addActiveRole()
	{

	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function dropActiveRole()
	{

	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function sessionRoles()
	{

	}
	
	/**
	* DESCRIPTION MISSING
	* @access	public
	*/
	function sessionPermissions()
	{
	}
	
	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer 	role_id
	* @param	integer 	object_id
	* @return	array		2-dim array: permissions for role/object 
	*/
	function roleOperationsOnObject($a_rol_id,$a_obj_id)
	{
		$query = "SELECT ops_id FROM rbac_pa ".
				 "WHERE rol_id='".$a_rol_id."' ".
				 "AND obj_id='".$a_obj_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = unserialize(stripslashes($row->ops_id));
		}

		if (!count($ops))
		{
			$this->ilias->raiseError("No such role or object",$this->ilias->error_obj->WARNING);
		}

		return $ops ? $ops : array();
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer
	* @param	string
	* @param	integer
	* @return	array	Array of operation_id
	*/
	function getOperations($a_rol_id,$a_type,$a_parent = 0)
	{
		$ops = array();

		// TODO: what happens if $a_parent is empty???????
		
		$query = "SELECT ops_id FROM rbac_templates ".
				 "WHERE type ='".$a_type."' ".
				 "AND rol_id = '".$a_rol_id."' ".
				 "AND parent = '".$a_parent."'";
		$res  = $this->ilias->db->query($query);

//		if($res->numRows == 0)
//		{
//			return $this->raiseError("No such type or template entry",$this->error_class->WARNING);
//		}

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops[] = $row->ops_id;
		}

		return $ops;
	}

	/**
	* DESCRIPTION MISSING
	* @access	public
	* @param	integer
	* @param	integer
	* @return	array		permissions of user/object
	*/
	function userOperationsOnObject($a_usr_id,$a_obj_id)
	{
		$ops = array();

		$query = "SELECT ops_id FROM rbac_pa ".
				 "JOIN rbac_ua WHERE rbac_ua.usr_id='".$a_usr_id."'";
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow())
		{
			array_push($ops,$row[0]);
		}

		if (!count($ops))
		{
			$this->ilias->raiseError("No such user",$this->ilias->error_obj->WARNING);
		}

		return $ops;
	}

	/**
	* Assign an existing permission to an object 
	* @access	public
	* @param	integer
	* @param	integer
	* @return	boolean
	*/
	function assignPermissionToObject($a_type_id,$a_ops_id)
	{
		$query = "INSERT INTO rbac_ta ".
				 "VALUES('".$a_type_id."','".$a_ops_id."')";
		$res = $this->ilias->db->query($query);

		return true;
	}
} // END class.RbacReview
?>