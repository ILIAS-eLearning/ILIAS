<?php
include_once("classes/class.Object.php");
class RoleObject extends Object
{
	function RoleObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
	//
	// Überschriebene Methoden:
	//
	// PUBLIC METHODEN
	function saveObject()
	{
		$rbacadmin = new RbacAdminH($this->ilias->db); 

		$new_obj_id = createNewObject($_POST["type"],$_POST["Fobject"]);
		$rbacadmin->assignRoleToFolder($new_obj_id,$_GET["obj_id"]);
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}

	function deleteObject()
	{
		global $tree;

		// Erst muss das Recht zum Löschen im RoleFolder überprüft werden
		// Auslesen aller RoleFolderId's aus rbac_fa
		// => alle Id's sind Kinder oder es gibt keine anderen RoleFolder
		//    deleteRole()
		// => sonst deleteLocalRole() für alle Kinder und den zu löschenden RoleFolder

		$rbacsystem = new RbacSystemH($this->ilias->db);
		if($rbacsystem->checkAccess($_GET["obj_id"],"delete",$_GET["parent"]) && $_POST["id"])
		{
			$rbacadmin = new RbacAdminH($this->ilias->db);
			foreach($_POST["id"] as $id)
			{
				$folders = $rbacadmin->getFoldersAssignedToRole($id);
				if(count($folders) == 1)
				{
					$rbacadmin->deleteRole($id);
				}
				else
				{
					foreach($folders as $folder)
					{
						$path_cmp = $tree->showPathId($folder,1);
						if(in_array($_GET["parent"],$path_cmp))
						{
							$to_delete[] = $folder;
						}
					}
					// Sind alle Kinder?
					if(count($to_delete) == count($folders))
					{
						$rbacadmin->deleteRole($id);
					}
					else
					{
						foreach($to_delete as $delete)
						{
							$rbacadmin->deleteLocalRole($id,$_GET["obj_id"]);
						}
					}
				}
			}
		}
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}

	function permObject()
	{
		global $ilias;
		global $tree;
		global $tplContent;

		$rbacadmin = new RbacAdminH($ilias->db);
		$rbacreview = new RbacReviewH($ilias->db);

		$tplContent = new Template("role_perm.html",true,true);
		$tplContent->setVariable("TPOS",$_GET["parent"]);
		$tplContent->setVariable("SHOW",$_GET["show"]);
		$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
		$tplContent->setVariable($ilias->ini["layout"]);

		// set Path
		$tree = new Tree($_GET["parent"],1,1);
		$tree->getPath();
		$path = showPath($tree->Path,"content.php");
		$tplContent->setVariable("TREEPATH",$path);

		$role_data = $rbacadmin->getRoleData($_GET["obj_id"]);
		$tplContent->setVariable("MESSAGE_TOP","Permission Template of Role: ".$role_data["title"]);

		// Abfrage der Objekte
		$query = "SELECT title,description FROM object_data WHERE type='".$type."'"; // WHERE class = 'y'"; 
		$res = $ilias->db->query($query);
		$anz_title = $res->numRows();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$obj_type[] = $row->title;
			$title[$row->title] = $row->description; 	
		}          	

		// Abfrage der Operationen und OperationID
		foreach($obj_type as $o)
		{ 		
			$query = "SELECT * FROM rbac_operations
					  LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id
					  LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj.id
					  WHERE object_data.title='".$o."' AND object_data.type='type'"; 
				
	//		$query = "SELECT operation,ops_id FROM rbac_operations WHERE obj_type = '".$o."'";
	
			$res = $ilias->db->query($query);
			$operation["$o"] = array();
			while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$operation[$o][] = $row['operation'];
				$ops_id[$o][$row['operation']] = $row['ops_id'];
			}
		}
		// Begin HEADER
		$tplContent->setCurrentBlock("TABLE_HEADER");
       	foreach($obj_type as $o)
		{
			$tplContent->setVariable("OBJECT",$_GET["obj_id"]);
			$tplContent->setVariable("COLUMN",$o);
			$tplContent->setVariable("PARENT",$_GET["parent"]);
			$tplContent->setVariable("HEADER",$title["$o"]);
			$tplContent->parseCurrentBlock();
		}
		// END HEADER

		//Abfrage der Permissions
		$selected = $rbacadmin->getRolePermission($_GET["obj_id"],$_GET["show"],$_GET["parent"]);
		foreach($operation["$_GET[show]"] as $ope)
			// BEGIN TABLE_OUTER
		{
			$tplContent->setCurrentBlock("TABLE_INNER");
			foreach($obj_type as $obj)
			{
				// TABLE INNER
				if($obj == $_GET["show"])
				{
					$checked = 0;
					foreach($selected as $s)
					{
						if($s == $ops_id["$_GET[show]"]["$ope"])
							$checked = 1;
					}
					$box = TUtil::formCheckBox($checked,$ope,1);
					$tplContent->setVariable("CHECK_TOP",$box);
					$tplContent->setVariable("PERM",$ope);
				}
				else
				{
					$tplContent->setVariable("CHECK","");
					$tplContent->setVariable("PERM","");
				}
				$tplContent->parseCurrentBlock();
				// END TABLE_INNER
			}
			$tplContent->setCurrentBlock("TABLE_OUTER");
			$tplContent->parseCurrentBlock();
		}
		// END TABLE OUTER
		$tplContent->setVariable("COL_ANZ",2*$anz_title);
		$tplContent->parseCurrentBlock();
		// END TABLE 

//
// FORMULAR "USER ASSIGNMENT"
//
		$tplContent->setVariable("MESSAGE_MIDDLE","User Assignment To Role: ".$role_data["title"]); 
	
		$query = "SELECT usr_id FROM user_data";
		$res = $ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$id[] = $row->usr_id;
		}
		foreach($id as $i)
		{
			$udata[] = $rbacreview->getUserData($i);
		}	    
		$assignedUsers = $rbacreview->assignedUsers($_GET["obj_id"]);

		foreach($udata as $u)
		{
			// TABLE DATA
			$tplContent->setCurrentBlock("TABLE_DATA");
			$checked = 0;
			foreach($assignedUsers as $a)
			{
				if($a == $u["usr_id"])
					$checked = 1;
			}
			$box = TUtil::formCheckBox($checked,'user[]',$u["usr_id"]);
			$tplContent->setVariable("CHECK_BOTTOM",$box);
			$tplContent->setVariable("USER",$u["login"]);
			$tplContent->parseCurrentBlock();
			// END TABLE_DATA
			$tplContent->setCurrentBlock("OUTER");
			$tplContent->parseCurrentBlock();
		}             
	}
	function perms_saveObject()
	{

		global $ilias;
		global $tree;
		global $tplContent;

		$ope_list = getOperationList($_GET["show"]);
		// Liest die neuen Operationen aus
		foreach($ope_list as $o)
		{
			if($_POST[$o["operation"]] == 1)
				$new_ops[] = $o["ops_id"];
		}
		$rbacadmin = new RbacAdminH($ilias->db);
		$rbacadmin->setRolePermission($_GET["obj_id"],$_GET["show"],$new_ops,$_GET["parent"]);
       	if($_POST["recursive"] == "on")
		{
			// Alle Objecte des aktuellen Typs unterhalb des aktuellen Knotens
			$parent_obj = $rbacadmin->getParentObject($_GET["parent"]);
			$obj_list = $tree->getAllChildsByType($parent_obj,$_GET["show"]);
			// Alle set_id speichern
			foreach($obj_list as $o)
			{
				$set_id[$o["obj_id"]] = $rbacadmin->getSetIdByObject($o["obj_id"]);
			}
			foreach($obj_list as $obj)
			{
				foreach($set_id[$obj["obj_id"]] as $set)
				{
					$rbacadmin->revokePermission($obj["obj_id"],$_GET["obj_id"],$set);
					$rbacadmin->grantPermission($_GET["obj_id"],$new_ops,$obj["obj_id"],$set);
				}
			}
		}
		header("location:object.php?cmd=perm&obj_id=$_GET[obj_id]&parent=$_GET[parent]&show=$_GET[show]");
		break;
	}
	function assign_saveObject()
	{
		global $ilias;
		global $tplContent;
		global $tree;

		if(!$_POST["user"])
		{
			$_POST["user"] = array();
		}
		$rbacreview = new RbacReviewH($ilias->db);
		$rbacadmin = new RbacAdminH($ilias->db);
		$assignedUser = $rbacreview->assignedUsers($_GET["obj_id"]);
		foreach($_POST["user"] as $u)
		{
			$assign = true;
			foreach($assignedUser as $a)
			{
				if($u == $a)
					$assign = false;
			}
			if($assign)
				$rbacadmin->assignUser($_GET["obj_id"],$u);
		}
		foreach ($assignedUser as $a)
		{
			$deassign = true;
			foreach($_POST["user"] as $u)
			{
				if($u == $a)
					$deassign = false;
			}
			if($deassign)
				$rbacadmin->deassignUser($_GET["obj_id"],$a);
		}
       	header("location:object.php?cmd=perm&obj_id=$_GET[obj_id]&parent=$_GET[parent]&show=$_GET[show]");
	}
}
?>