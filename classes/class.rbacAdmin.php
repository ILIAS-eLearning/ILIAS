<?php

include_once "classes/class.Object.php";

/**
 * Class RbacAdmin 
 * core functions for role based access control
 * @author Stefan Meyer <smeyer@databay.de>
 * @version $Id$
 * @package rbac
 */
class RbacAdmin
{
    var $db;			//  Database Handle

    var $Errno; 
    var $Error;

    function RbacAdmin(&$dbhandle)
    {
        $this->db =& $dbhandle;
		$this->Errno = 0; 
		$this->Error = "";
    }
/**
 * @access public
 * @params void
 * @return type String
 */
    function getErrorMessage()
    {
        return $this->Error;
    }
/**
 * @access public
 * @params String (Titel der Rolle)
 * @return type 1,0,-1(Fehler)
 */
    function roleExists($a_title)
    {
		$res = $this->db->query("SELECT obj_id FROM object_data ".
								"WHERE title ='".$a_title.
								"' AND type = 'role'");
        if (DB::isError($res))
        {
			$this->Error = $res->getMessage();
			return -1;
		}
		return $res->fetchRow() ? 1 : 0;
    }
/**
 * Inserts userdata in user_data table
 * @access public
 * @params array user data set
 * @return bool true/false
 */
	function addUser($a_data)
    {
		$passwd = md5($a_data["Passwd"]);
		$query = "INSERT INTO user_data ".
			"(usr_id,login,passwd,firstname,surname,title,gender,email,last_login,last_update,create_date) ".
			"VALUES('".$a_data["Id"]."','".$a_data["Login"]."','".$passwd."','".$a_data["FirstName"].
			"','".$a_data["SurName"]."','".$a_data["Title"]."','".$a_data["Gender"]."','".$a_data["Email"].
			"',0,now(),now())";

		$res = $this->db->query($query);
        if (DB::isError($res))
        {
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
    }
/**
 * @access public
 * @params Array(int) Array der zu löschenden obj_id
 * @return type true false
 */
    function deleteUser($a_usr_id)
    {
		foreach($a_usr_id as $id)
		{
			// Einträge in object_data
			$res = $this->db->query("DELETE FROM object_data ".
									"WHERE obj_id='".$id."'");
			if (DB::isError($res))
			{
				$this->Error = $res->getMessage();
				return -1;
			}
			$res = $this->db->query("DELETE FROM rbac_pa ".
									"WHERE obj_id='".$id."'");
			if (DB::isError($res))
			{
				$this->Error = $res->getMessage();
				return -1;
			}
			$res = $this->db->query("DELETE FROM rbac_ua ".
									"WHERE usr_id='".$id."'");
			if (DB::isError($res))
			{
				$this->Error = $res->getMessage();
				return -1;
			}
			$res = $this->db->query("DELETE FROM user_data ".
									"WHERE usr_id='".$id."'");
			if (DB::isError($res))
			{
				$this->Error = $res->getMessage();
				return -1;
			}
		}
		return true;
    }
/** 
 * @access public
 * @params Array(user_daten) Array der User Daten
 * @return type true false
 */
	function updateUser($a_userdata)
	{
		$query = "UPDATE user_data ".
			"SET ".
			"login = '".$a_userdata["Login"]."',".
//			"passwd = '".$a_userdata["Passwd"]."',".
			"firstname = '".$a_userdata["FirstName"]."',".
			"surname = '".$a_userdata["SurName"]."',".
			"title = '".$a_userdata["Title"]."',".
			"gender = '".$a_userdata["Gender"]."',".
			"email = '".$a_userdata["Email"]."'".
			" WHERE usr_id = '".$a_userdata["Id"]."'";
		$res = $this->db->query($query);
		if (DB::isError($res))
		{
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
	}
/**
 * Anlegen des Rolle in object_data, rbac_ua, rbac_pa
 * @access public
 * @params string,string (Titel, Beschreibung)
 * @return type int (neue obj_id) sonst -1
 */
    function addRole($a_title,$a_description)
    {
		$rbacreview = new RbacReview($this->db);

		if($this->roleExists($a_title))
		{
			$this->Errno = 1;
			$this->Error = "Role Title already exists";
			return 0;
		}
		// Anlegen der Rolle in object_data
		$query = "INSERT INTO object_data (type,title,description,owner,create_date,last_update) ".
			"VALUES ('role','".$a_title."','".$a_description ."','-1',now(),now())";

		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}

		// Eintrag in rbac_ua
 		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$row = $res->fetchRow();
		if(!$this->assignUser($row[0]))
		{
			$this->Error = "Fehler bei User Assignment";
			return -1;
		}

		// Eintrag der Permissions in rbac_pa
		$rolops = $rbacreview->getRolesOperations("role");
		foreach($rolops as $r)
		{
			// TODO: set_id muss den Wert des aktuellen Container
			// erhalten, in dem die Rolle angelegt wurde
			$this->grantPermission($r["rol_id"],$r["ops_id"],$row[0],1);
		}
		return $row[0];
	}
	/**
	 * @access public
	 * @params int (Objekt ID und )
	 * @return type 1,-1 (Fehler)
	 */
    function deleteRole($a_obj_id)
    {
		$this->db->query("DELETE FROM object_data ".
						 "WHERE obj_id = '".$a_obj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_pa ".
						 "WHERE rol_id = '".$a_obj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_templates ".
						 "WHERE rol_id = '".$a_obj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_ua ".
						 "WHERE rol_id = '".$a_obj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_fa ".
						 "WHERE rol_id = '".$a_obj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
/**
 * @access public
 * @params int (Objekt ID und )
 * @return type 1,-1 (Fehler)
 */
	function deleteLocalRole($a_rol_id,$a_parent)
	{
		$query = "DELETE FROM rbac_fa ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_parent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$query = "DELETE FROM rbac_templates ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_parent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
	}
/**
 * @access public
 * @params int (Objekt ID)
 * @return type 1,-1 (Fehler)
 */
	function getParentRoles($a_path,$a_child = "")
	{
		$parentRoles = array();

		if(!$a_child)
		{
			$a_child = $this->getRoleFolder();
		}
		// CREATE IN() STATEMENT
		$in = " IN('";
		if(count($a_child) > 1)
		{
			$in .= implode("','",$a_child);
		}
		else
		{
			$in .= $a_child[0];
		}
		$in .= "')";
		foreach($a_path as $path)
		{
			$query = "SELECT * FROM tree ".
				" WHERE child ".$in.
				" AND parent = '".$path."'";
			$res = $this->db->query($query);
			if(DB::isError($res))
			{
				$this->Errno = 2;
				$this->Error = $res->getMessage();
				return -1;
			}
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$roles = $this->getRoleListByObject($row->child);
				foreach($roles as $role)
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
 * @access public
 * @params int,int (RoleID, UsrID default sonst aktueller Benutzer) 
 * @return 1,-1 (Fehler) 
 */
    function assignUser($a_rol_id,$a_usr_id = 0)
    {
        // Zuweisung des aktuellen Benutzers zu der Rolle
		if(!$a_usr_id)
		{
			global $ilias;
			$a_usr_id = $ilias->account->data["Id"];
		}
		$query = "INSERT INTO rbac_ua ".
			"VALUES ('".$a_usr_id."','".$a_rol_id."')";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
/**
 * @access public
 * @params int,int (RoleId und UserId)
 * @return 1,-1(Fehler)
 */
    function deassignUser($a_rol_id,$a_usr_id)
    {
		$query = "DELETE FROM rbac_ua ".
			"WHERE usr_id='".$a_usr_id."' ".
			"AND rol_id='".$a_rol_id."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
/**
 * @access public
 * @params int,array(int),int,int (RoleId,OpsID,OBjektID und SetID)
 * @return 1,-1 (Fehler)
 */
    function grantPermission($a_rol_id,$a_ops_id,$a_obj_id,$a_setid)
    {
		// Serialization des ops_id Arrays
		$ops_ids = addslashes(serialize($a_ops_id));
		$query = "INSERT INTO rbac_pa VALUES('".$a_rol_id."','".$ops_ids."','".$a_obj_id."','".$a_setid."')";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
	    }
		return 1;
    }
/**
 * @access public
 * @params int,array(int,int) (OBjektID und RoleId)
 * @return 1,-1 (Fehler)
 */
    function revokePermission($a_obj_id,$a_rol_id = "",$a_set_id = "")
    {
		if($a_set_id)
			$and1 = " AND set_id = '".$a_set_id."'";
		else
			$and1 = "";

		if($a_rol_id)
			$and2 = " AND rol_id = '".$a_rol_id."'";
		else
			$and2 = "";

		$query = "DELETE FROM rbac_pa ".
			"WHERE obj_id = '".$a_obj_id."' ".
			$and1.
			$and2;
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
/**
 * @access public
 * @params int,string (RoleID und Object Type)  
 * @return int[] (OperationID aus rbac_templates sonst 0)
 */
    function getRolePermission($a_rol_id,$a_type,$a_parent)
    {
		$ops_id = array();
		$query = "SELECT ops_id FROM rbac_templates ".
			"WHERE rol_id='".$a_rol_id."' ".
			"AND type='".$a_type."' ".
			"AND parent='".$a_parent."'";
		
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		} 
		if(!$res->numRows())
			return $ops_id;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id[] = $row->ops_id;
		}
		return $ops_id;
    }
/**
 * @access public
 * @params int int (RoleFolderId Destination Source)  
 * @return 1,-1 (Fehler)
 */
	function copyRolePermission($a_rol_id,$a_from,$a_to)
	{
		$query = "SELECT * FROM rbac_templates ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_from."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		} 
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = "INSERT INTO rbac_templates ".
				"VALUES('".$a_rol_id."','".$row->type."','".$row->ops_id."','".$a_to."')";
			$result = $this->db->query($query);
			if(DB::isError($result))
			{
				$this->Errno = 2;
				$this->Error = $res->getMessage();
				return -1;
			} 
		}
		return true;
	}
/**
 * @access public
 * @params int,string,int[] (RoleID RoleFolderId)  
 * @return 1,-1 (Fehler)
 */
	function deleteRolePermission($a_rol_id,$a_parent)
	{
		$query = "DELETE FROM rbac_templates ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_parent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		} 
		return true;
	}
/**
 * @access public
 * @params int,string,int[] (RoleID und Object Type und OperationID)  
 * @return 1,-1 (Fehler)
 */
    function setRolePermission($a_rol_id,$a_type,$a_ops_id,$a_parent)
    {
		if(!$a_ops_id)
			$a_ops_id = array();

		foreach($a_ops_id as $o)
		{
			$query = "INSERT INTO rbac_templates ".
				"VALUES('".$a_rol_id."','".$a_type."','".$o."','".$a_parent."')";
			$res = $this->db->query($query);
			if(DB::isError($res))
			{
				$this->Errno = 2;
				$this->Error = $res->getMessage();
				return -1;
			}
		}
		return 1;
    }
/**
 * @access public
 * @params int (ObjectId eines Objektes)  
 * @return array(int) (Array mit setIDs)
 */
    function getSetIdByObject($a_obj_id)
    {
		$set_id = array();
		$query = "SELECT DISTINCT set_id FROM rbac_pa ".
			"WHERE obj_id = '".$a_obj_id."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return $set_id;
		}
        while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$set_id[] = $row->set_id;
        }
		return $set_id;
    }
/**
 * @access public
 * @params int (ObjectId des RoleFolders)  
 * @return array(int) (Array mit setIDs)
 */
	function getRoleListByObject($a_parent)
	{
		$role_list = array();

		$query = "SELECT * FROM object_data ".
			"JOIN rbac_fa ".
			"WHERE object_data.type = 'role' ".
			"AND object_data.obj_id = rbac_fa.rol_id ".
			"AND rbac_fa.parent = '".$a_parent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list[] = array( 
				"obj_id"            => $row->obj_id,
				"type"              => $row->type,
				"title"             => $row->title,
				"description"       => $row->description,
				"owner"             => $row->owner,
				"create_date"       => $row->create_date,
				"last_update"       => $row->last_update);
		}
		return $role_list;
	}
/**
 * @access public
 * @params int (ObjectId des RoleFolders)  
 * @return array(int) (Array mit setIDs)
 */
	function assignRoleToFolder($a_rol_id,$a_parent,$a_assign = 'y')
	{
		$query = "INSERT INTO rbac_fa (rol_id,parent,assign) ".
			"VALUES ('".$a_rol_id."','".$a_parent."','".$a_assign."')";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
	}
/**
 * Check if its possible to assign users
 * @access public
 * @params int,int  (ObjectId of RoleFolder and objectId of role)  
 * @return bool 
 */
	function isAssignable($a_rol_id,$a_parent)
	{
		$query = "SELECT * FROM rbac_fa ".
			"WHERE rol_id = '".$a_rol_id."' ".
			"AND parent = '".$a_parent."'";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->assign == 'y' ? true : false;
		}
	}
/**
 * @access public
 * @params int (ObjectId einer Rolle)  
 * @return array(int) (Array mit setIDs)
 */
	function getRoleData($a_obj_id)
	{
		$role_list = array();

		$query = "SELECT * FROM object_data ".
			"WHERE type = 'role' ".
			"AND obj_id = '".$a_obj_id."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list = array( 
				"obj_id"            => $row->obj_id,
				"type"              => $row->type,
				"title"             => $row->title,
				"description"       => $row->description,
				"owner"             => $row->owner,
				"create_date"       => $row->create_date,
				"last_update"       => $row->last_update);
		}
		return $role_list;
	}
/**
 * @access public
 * @params int (ObjectId des RoleFolders)  
 * @return array(int) (Array mit setIDs)
 */
	function getFoldersAssignedToRole($a_rol_id)
	{
		$parent = array();
		
		$query = "SELECT DISTINCT parent FROM rbac_fa ".
			"WHERE rol_id = '".$a_rol_id."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent[] = $row->parent;
		}
		return $parent;
	}
/**
 * @access public
 * @params int (ObjectId of RoleFolder)  
 * @return array(int) (Array with rol_ids)
 */
	function getRolesAssignedToFolder($a_parent)
	{
		$query = "SELECT rol_id FROM rbac_fa ".
			"WHERE parent = '".$a_parent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol_id[] = $row->rol_id;
		}
		return $rol_id ? $rol_id : array();
	}
/**
 * @access public
 * @params int (ObjectId des RoleFolders)  
 * @return array(int) (Array mit setIDs)
 */
	function getRoleFolder()
	{
		$parent = array();
		
		$query = "SELECT DISTINCT parent FROM rbac_fa";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent[] = $row->parent;
		}
		return $parent;
	}
/**
 * @access public
 * @params int (ObjectId des Parent Objects)  
 * @return int array(Id und parentID des RoleFolders) sonst false
 */
	function getRoleFolderOfObject($a_parent)
	{
		$rol_data = array();

		$query = "SELECT * FROM tree ".
			"LEFT JOIN object_data ON tree.child=object_data.obj_id ".
			"WHERE parent = '".$a_parent."' ".
			"AND type = 'rolf'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rol_data["child"] = $row->obj_id;
			$rol_data["parent"] = $row->parent;
		}
		return $rol_data;
	}
/**
 * @access public
 * @params int (ObjectId des ROleFolders)  
 * @return int (ObjectId) sonst false
 */
	function getParentObject($a_child)
	{
		$res = $this->db->query("SELECT * FROM tree ".
								"WHERE child = '".$a_child."'");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent = $row->parent;
		}
		return $parent;
	}
/**
 * @access public
 * @params int (TypeId des Objektes)  
 * @return array(int) (OperationId) sonst false
 */
	function getOperationsOnType($a_typ_id)
	{
		$res = $this->db->query("SELECT * FROM rbac_ta ".
								"WHERE typ_id = '".$a_typ_id."'");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops_id[] = $row->ops_id;
		}
		return $ops_id ? $ops_id : array();
	}
}
?>