<?php
/**
* class ilRbacReview
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @package rbac
*/
class ilRbacReview
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
	function ilRbacReview()
	{
	    global $ilias;
		
		$this->ilias =& $ilias;
	}

	/**
	* get all assigned users to a given role
	* @access	public
	* @param	integer	role_id
	* @return	array	all users (id) assigned to role
	*/
	function assignedUsers($a_rol_id)
	{
		global $log;

		if (!isset($a_rol_id))
		{
			$message = get_class($this)."::assignedUsers(): No role_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

	    $usr_arr = array();
	   
		$q = "SELECT usr_id FROM rbac_ua WHERE rol_id='".$a_rol_id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($usr_arr,$row["usr_id"]);
		}
		
		return $usr_arr;
	}
	
	/**
	* get all assigned roles to a given user
	* @access	public
	* @param	integer		usr_id
	* @return	array		all roles (id) the user have
	*/
	function assignedRoles($a_usr_id)
	{
		global $log;

		if (!isset($a_usr_id))
		{
			$message = get_class($this)."::assignedRoles(): No user_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$role_arr = array();
		
		$q = "SELECT rol_id FROM rbac_ua WHERE usr_id = '".$a_usr_id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_arr[] = $row->rol_id;
		}

		if (!count($role_arr))
		{
			$this->ilias->raiseError("No assigned roles found or user doesn't exists!",$this->ilias->error_obj->WARNING);
		}

		return $role_arr;
	}

	/**
	* DESCRIPTION MISSING
	* TODO: function is very similar to perm:getOperationList ????
	* @access	public
	* @param	integer	role_id
	* @param	string	object type
	* @param	integer	??? i think ist a ref_id
	* @return	array	array of operation_id
	*/
	function getOperations($a_rol_id,$a_type,$a_parent = 0)
	{
		global $log;

		if (!isset($a_rol_id) or !isset($a_type) or func_num_args() != 3)
		{
			$message = get_class($this)."::getOperations(): Missing Parameter!".
					   "role_id: ".$a_rol_id.
					   "type: ".$a_type.
					   "parent_id: ".$a_parent;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$ops_arr = array();

		// TODO: what happens if $a_parent is empty???????
		
		$q = "SELECT ops_id FROM rbac_templates ".
			 "WHERE type ='".$a_type."' ".
			 "AND rol_id = '".$a_rol_id."' ".
			 "AND parent = '".$a_parent."'";
		$r  = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_arr[] = $row->ops_id;
		}

		return $ops_arr;
	}

	function userOperationsOnObject($a_usr_id,$a_obj_id)
	{

	}

	/**
	* Assign an existing permission to an object 
	* @access	public
	* @param	integer	object type
	* @param	integer	operation_id
	* @return	boolean
	*/
	function assignPermissionToObject($a_type_id,$a_ops_id)
	{
		global $log;

		if (!isset($a_type_id) or !isset($a_ops_id))
		{
			$message = get_class($this)."::assignPermissionToObject(): Missing parameter!".
					   "type_id: ".$a_type_id.
					   "ops_id: ".$a_ops_id;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "INSERT INTO rbac_ta ".
			 "VALUES('".$a_type_id."','".$a_ops_id."')";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* Deassign an existing permission from an object 
	* @access	public
	* @param	integer	object type
	* @param	integer	operation_id
	* @return	boolean
	*/
	function deassignPermissionFromObject($a_type_id,$a_ops_id)
	{
		global $log;

		if (!isset($a_type_id) or !isset($a_ops_id))
		{
			$message = get_class($this)."::deassignPermissionFromObject(): Missing parameter!".
					   "type_id: ".$a_type_id.
					   "ops_id: ".$a_ops_id;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$q = "DELETE FROM rbac_ta ".
			 "WHERE typ_id = '".$a_type_id."' ".
			 "AND ops_id = '".$a_ops_id."'";
		$this->ilias->db->query($q);
	
		return true;
	}
	
	function rolePermissons($a_rol_id,$a_obj_id = 0)
	{

	}
	
	function userPermissions($a_usr_id)
	{

	}

	function getErrorMessage()
	{
		return $this->Error;
	}
	
	function createSession()
	{

	}

	function deleteSession()
	{

	}
	
	function addActiveRole()
	{

	}

	function dropActiveRole()
	{

	}

	function sessionRoles()
	{

	}
	
	function sessionPermissions()
	{
	
	}
	
	function roleOperationsOnObject($a_rol_id,$a_obj_id)
	{

	}
} // END class.RbacReview
?>
