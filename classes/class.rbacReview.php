<?php
/**
 * class RbacReview
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package rbac
 * 
 */
class RbacReview extends PEAR
{
    var $db; // Database Handle

	var $error_class;

    function RbacReview(&$dbhandle)
    {
		$this->PEAR();
		$this->error_class = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK,array($this->error_class,'errorHandler'));

        $this->db =& $dbhandle;
    }
/**
 * @access public
 * @params int (rol_id)
 * @return type int array (Uid der Rolle)
 */
    function assignedUsers($Arol_id)
    {
        $usr = array();

        $res = $this->db->query("SELECT usr_id FROM rbac_ua WHERE rol_id = $Arol_id");
        while($row = $res->fetchRow())
        {
		    array_push($usr,$row[0]);
        }
        return $usr;
    }
    function getUserData($Ausr_id)
    {
		$res = $this->db->query("SELECT * FROM user_data WHERE usr_id='".$Ausr_id."'");	
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{	
			$arr = array(
				"usr_id"	=>	$row->usr_id,
				"login"		=>	$row->login,
				"firstname"	=>	$row->firstname,
				"surname"	=>	$row->surname,
				"title"		=>	$row->title,
				"gender"	=>	$row->gender,	
				"email"		=>	$row->email,
				"last_login"	=>	$row->last_login,
				"last_update"	=>	$row->last_update,
				"create_date"	=>	$row->create_date);
		}		
		return $arr;
    }
/**
 * @access public
 * @params int (usr_id)
 * @return type int array (RoleID des Users)
 */
    function assignedRoles($Ausr_id)
    {
        $rol = array();
        $res = $this->db->query("SELECT rol_id FROM rbac_ua WHERE usr_id = '".$Ausr_id . "'");
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
        {
		    $rol[] = $row->rol_id;
        }
		if(!count($rol))
		{
			return $this->raiseError("No such user",$this->error_class->WARNING);
		}
        return $rol;
    }
/**
 * @access public
 * @params int (usr_id)
 * @return type string array (Role Title des Users)
 */
    function assignedRoleTitles($Ausr_id)
    {
        $res = $this->db->query("SELECT title FROM object_data JOIN rbac_ua WHERE object_data.obj_id = rbac_ua.rol_id AND rbac_ua.usr_id = '".$Ausr_id . "'");
        while($row = $res->fetchRow())
        {
		    $role_title[] = $row[0];
        }
        if(!count($rol))
        {
			return $this->raiseError("No such role",$this->error_class->WARNING);
        }
        return $role_title;
    }
/**
 * @access public
 * @params int,int (RoleID und optional ID eines Objektes)
 * @return type 2-dim Array (Objekt-Permissions,Object-ID zu einer Rolle)
 */
    function rolePermissons($Arol_id,$Aobj_id = 0)
    {
        $ops = array();
	       
        $query = "SELECT ops_id,obj_id FROM rbac_pa WHERE rol_id = $Arol_id";
        if($Aobj_id)
		    $query .= " AND obj_id = $Aobj_id";
	    
        $res = $this->db->query($query);
        while($row = $res->fetchRow())
        {
		    array_push($ops,$row[0],$row[1]);
        }
        if(!count($ops))
        {
			return $this->raiseError("No such Role or Object!",$this->error_class->WARNING);
        }
        return $ops;

    }
/**
 * @access public
 * @params int (UserID)
 * @return type int array (Objekt-Permissions eines Users)
 */
    function userPermissions($Ausr_id)
    {
        $ops = array();

        $query = "SELECT ops_id,obj_id FROM rbac_pa JOIN rbac_ua WHERE rbac_ua.usr_id=$Ausr_id";
        $res = $this->db->query($query);
        while($row = $res->fetchRow())
        {
		    array_push($ops,$row[0]);
        }
        if(!count($ops))
        {
			return $this->raiseError("No such user",$this->error_class->WARNING);
        }
        return $ops;
    }
/**
 * @access public
 * @params void
 * @return type String
 */
    function sessionRoles()
    {
    }
/**
 * @access public
 * @params void
 * @return type String
 */
    function sessionPermissions()
    {
    }
/**
 * @access public
 * @params int,int (RoleID und ObjektID)
 * @return type int array (Permissions fr Rolle/Objekt) 
 */
    function roleOperationsOnObject($Arol_id,$Aobj_id)
    {
        $query = "SELECT ops_id FROM rbac_pa WHERE rol_id = '".$Arol_id."' AND obj_id = '".$Aobj_id."'";
        $res = $this->db->query($query);
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
        {
			$ops = unserialize(stripslashes($row->ops_id));
        }
        if(!count($ops))
        {
			return $this->raiseError("No such role or object",$this->error_class->WARNING);
        }
        return $ops ? $ops : array();
    }
/**
 * @access public
 * @params int int int ROlID Type und RoleFolderId
 * @return type array(int) Array der Operations
 */
    function getOperations($Arol_id,$Atype,$Aparent = "")
    {
		$ops = array();

		$query = "SELECT ops_id FROM rbac_templates ".
			"WHERE type ='".$Atype."' ".
			"AND rol_id = '".$Arol_id."' ".
			"AND parent = '".$Aparent."'";
		$res  = $this->db->query($query);
//		if($res->numRows == 0)
//		{
//			return $this->raiseError("No such type or template entry",$this->error_class->WARNING);
//		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops[] = $row->ops_id;
		}
		return $ops;
    }
/**
 * @access public
 * @params int,int (UserID und ObjektID)
 * @return type int array (Permisions fr User/Objekt)
 */
    function userOperationsOnObject($Ausr_id,$Aobj_id)
    {
        $ops = array();

        $query = "SELECT ops_id FROM rbac_pa JOIN rbac_ua WHERE rbac_ua.usr_id = $Ausr_id";

        $res = $this->db->query($query);
        while($row = $res->fetchRow())
        {
		    array_push($ops,$row[0]);
        }
        if(!count($ops))
        {
			return $this->raiseError("No such user",$this->error_class->WARNING);
        }
        return $ops;
    }
/**
 * Assign an existing permission to an object 
 * @access public
 * @params int,int (type_id,ops_id)
 * @return bool true/false
 */
	function assignPermissionToObject($a_type_id,$a_ops_id)
	{
		$query = "INSERT INTO rbac_ta ".
			"VALUES('".$a_type_id."','".$a_ops_id."')";

		$res = $this->db->query($query);
		return true;
	}
} // END class.RBac
?>