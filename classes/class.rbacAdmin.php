<?php
// CLASS Rbac
// 
// Admin Functions for Core RBAC
//
// @author Stefan Meyer smeyer@databay.de
//

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
// 
// @access public
// @params void
// @return type String
    function getErrorMessage()
    {
        return $this->Error;
    }

// 
// @access public
// @params String (Titel der Rolle)
// @return type 1,0,-1(Fehler)
    function roleExists($Atitle)
    {
		$res = $this->db->query("SELECT obj_id FROM object_data ".
								"WHERE title ='".$Atitle.
								"' AND type = 'role'");
        if (DB::isError($res))
        {
			$this->Error = $res->getMessage();
			return -1;
		}
		return $res->fetchRow() ? 1 : 0;
    }
// 
// @access public
// @params void
// @return type String
    function addUser()
    {
    }
// 
// @access public
// @params Array(int) Array der zu löschenden obj_id
// @return type true false
    function deleteUser($Ausr_id)
    {
		foreach($Ausr_id as $id)
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
// 
// @access public
// @params Array(user_daten) Array der User Daten
// @return type true false
	function updateUser($Auserdata)
	{
		$query = "UPDATE user_data ".
			"SET ".
			"login = '".$Auserdata["Login"]."',".
//			"passwd = '".$Auserdata["Passwd"]."',".
			"firstname = '".$Auserdata["FirstName"]."',".
			"surname = '".$Auserdata["SurName"]."',".
			"title = '".$Auserdata["Title"]."',".
			"gender = '".$Auserdata["Gender"]."',".
			"email = '".$Auserdata["Email"]."'".
			" WHERE usr_id = '".$Auserdata["Id"]."'";
		$res = $this->db->query($query);
		if (DB::isError($res))
		{
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
	}
// Anlegen des Rolle in object_data, rbac_ua, rbac_pa
// @access public
// @params string,string (Titel, Beschreibung)
// @return type int (neue obj_id) sonst -1
    function addRole($Atitle,$Adescription)
    {
		$rbacreview = new RbacReview($this->db);

		if($this->roleExists($Atitle))
		{
			$this->Errno = 1;
			$this->Error = "Role Title already exists";
			return 0;
		}
		// Anlegen der Rolle in object_data
		$query = "INSERT INTO object_data (type,title,description,owner,create_date,last_update) ".
			"VALUES ('role','".$Atitle."','".$Adescription ."','-1',now(),now())";

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
 
// @access public
// @params int (Objekt ID und )
// @return type 1,-1 (Fehler)
    function deleteRole($Aobj_id)
    {
		$this->db->query("DELETE FROM object_data ".
							   "WHERE obj_id = '".$Aobj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_pa ".
							   "WHERE rol_id = '".$Aobj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_templates ".
							   "WHERE rol_id = '".$Aobj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_ua ".
							   "WHERE rol_id = '".$Aobj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$this->db->query("DELETE FROM rbac_fa ".
							   "WHERE rol_id = '".$Aobj_id ."'");
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
// @access public
// @params int (Objekt ID und )
// @return type 1,-1 (Fehler)
	function deleteLocalRole($Arol_id,$Aparent)
	{
		$query = "DELETE FROM rbac_fa ".
			"WHERE rol_id = '".$Arol_id."' ".
			"AND parent = '".$Aparent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		$query = "DELETE FROM rbac_templates ".
			"WHERE rol_id = '".$Arol_id."' ".
			"AND parent = '".$Aparent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
	}

// @access public
// @params int (Objekt ID)
// @return type 1,-1 (Fehler)
	function getParentRoles($Apath,$Achild = "")
	{
		$parentRoles = array();

		if(!$Achild)
		{
			$Achild = $this->getRoleFolder();
		}
		// CREATE IN() STATEMENT
		$in = " IN('";
		if(count($Achild) > 1)
		{
			$in .= implode("','",$Achild);
		}
		else
		{
			$in .= $Achild[0];
		}
		$in .= "')";
		foreach($Apath as $path)
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

// @access public
// @params int,int (RoleID, UsrID default sonst aktueller Benutzer) 
// @return 1,-1 (Fehler) 
    function assignUser($Arol_id,$Ausr_id = 0)
    {
        // Zuweisung des aktuellen Benutzers zu der Rolle
		if(!$Ausr_id)
		{
			global $ilias;
			$Ausr_id = $ilias->account->data["Id"];
		}
		$query = "INSERT INTO rbac_ua ".
			"VALUES ('".$Ausr_id."','".$Arol_id."')";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
// @access public
// @params int,int (RoleId und UserId)
// @return 1,-1(Fehler)
    function deassignUser($Arol_id,$Ausr_id)
    {
		$query = "DELETE FROM rbac_ua ".
			"WHERE usr_id='".$Ausr_id."' ".
			"AND rol_id='".$Arol_id."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return 1;
    }
// @access public
// @params int,array(int),int,int (RoleId,OpsID,OBjektID und SetID)
// @return 1,-1 (Fehler)
    function grantPermission($Arol_id,$Aops_id,$Aobj_id,$Asetid)
    {
		// Serialization des ops_id Arrays
		$ops_ids = addslashes(serialize($Aops_id));
		$query = "INSERT INTO rbac_pa VALUES('".$Arol_id."','".$ops_ids."','".$Aobj_id."','".$Asetid."')";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
	    }
		return 1;
    }
// @access public
// @params int,array(int,int) (OBjektID und RoleId)
// @return 1,-1 (Fehler)
    function revokePermission($Aobj_id,$Arol_id = "",$Aset_id = "")
    {
		if($Aset_id)
			$and1 = " AND set_id = '".$Aset_id."'";
		else
			$and1 = "";

		if($Arol_id)
			$and2 = " AND rol_id = '".$Arol_id."'";
		else
			$and2 = "";

		$query = "DELETE FROM rbac_pa ".
			"WHERE obj_id = '".$Aobj_id."' ".
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
// @access public
// @params int,string (RoleID und Object Type)  
// @return int[] (OperationID aus rbac_templates sonst 0)
    function getRolePermission($Arol_id,$Atype,$Aparent)
    {
		$ops_id = array();
		$query = "SELECT ops_id FROM rbac_templates ".
			"WHERE rol_id='".$Arol_id."' ".
			"AND type='".$Atype."' ".
			"AND parent='".$Aparent."'";
		
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
// @access public
// @params int int (RoleFolderId Destination Source)  
// @return 1,-1 (Fehler)
	function copyRolePermission($Arol_id,$Afrom,$Ato)
	{
		$query = "SELECT * FROM rbac_templates ".
			"WHERE rol_id = '".$Arol_id."' ".
			"AND parent = '".$Afrom."'";
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
				"VALUES('".$Arol_id."','".$row->type."','".$row->ops_id."','".$Ato."')";
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
// @access public
// @params int,string,int[] (RoleID RoleFolderId)  
// @return 1,-1 (Fehler)
	function deleteRolePermission($Arol_id,$Aparent)
	{
		$query = "DELETE FROM rbac_templates ".
			"WHERE rol_id = '".$Arol_id."' ".
			"AND parent = '".$Aparent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		} 
		return true;
	}

// @access public
// @params int,string,int[] (RoleID und Object Type und OperationID)  
// @return 1,-1 (Fehler)
    function setRolePermission($Arol_id,$Atype,$Aops_id,$Aparent)
    {
		if(!$Aops_id)
			$Aops_id = array();
		$query = "DELETE FROM rbac_templates ".
			"WHERE rol_id='".$Arol_id."' AND type='".$Atype."' AND parent='".$Aparent."'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		foreach($Aops_id as $o)
		{
			$query = "INSERT INTO rbac_templates ".
				"VALUES('".$Arol_id."','".$Atype."','".$o."','".$Aparent."')";
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
// @access public
// @params int (ObjectId eines Objektes)  
// @return array(int) (Array mit setIDs)
    function getSetIdByObject($Aobj_id)
    {
		$set_id = array();
		$query = "SELECT DISTINCT set_id FROM rbac_pa ".
			"WHERE obj_id = '".$Aobj_id."'";
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
// @access public
// @params int (ObjectId des RoleFolders)  
// @return array(int) (Array mit setIDs)
	function getRoleListByObject($Aparent)
	{
		$role_list = array();

		$query = "SELECT * FROM object_data ".
			"JOIN rbac_fa ".
			"WHERE object_data.type = 'role' ".
			"AND object_data.obj_id = rbac_fa.rol_id ".
			"AND rbac_fa.parent = '".$Aparent."'";
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
// @access public
// @params int (ObjectId des RoleFolders)  
// @return array(int) (Array mit setIDs)
	function assignRoleToFolder($Arol_id,$Aparent)
	{
		$query = "INSERT INTO rbac_fa (rol_id,parent) ".
			"VALUES ('".$Arol_id."','".$Aparent."')";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->Errno = 2;
			$this->Error = $res->getMessage();
			return -1;
		}
		return true;
	}
// @access public
// @params int (ObjectId einer Rolle)  
// @return array(int) (Array mit setIDs)
	function getRoleData($Aobj_id)
	{
		$role_list = array();

		$query = "SELECT * FROM object_data ".
			"WHERE type = 'role' ".
			"AND obj_id = '".$Aobj_id."'";
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
// @access public
// @params int (ObjectId des RoleFolders)  
// @return array(int) (Array mit setIDs)
	function getFoldersAssignedToRole($Arol_id)
	{
		$parent = array();
		
		$query = "SELECT DISTINCT parent FROM rbac_fa ".
			"WHERE rol_id = '".$Arol_id."'";
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

// @access public
// @params int (ObjectId des RoleFolders)  
// @return array(int) (Array mit setIDs)
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
// @access public
// @params int (ObjectId des Parent Objects)  
// @return int array(Id und parentID des RoleFolders) sonst false
	function getRoleFolderOfObject($Aparent)
	{
		$rol_data = array();

		$query = "SELECT * FROM tree ".
			"LEFT JOIN object_data ON tree.child=object_data.obj_id ".
			"WHERE parent = '".$Aparent."' ".
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
// @access public
// @params int (ObjectId des ROleFolders)  
// @return int (ObjectId) sonst false
	function getParentObject($Achild)
	{
		$res = $this->db->query("SELECT * FROM tree ".
			"WHERE child = '".$Achild."'");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$parent = $row->parent;
		}
		return $parent;
	}
}
?>
