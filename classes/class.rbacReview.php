<?php
/**
* class RbacReview
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @package rbac
*/
class RbacReview
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
	function RbacReview()
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
	* @param	integer	???
	* @return	array	array of operation_id
	*/
	function getOperations($a_rol_id,$a_type,$a_parent = 0)
	{
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