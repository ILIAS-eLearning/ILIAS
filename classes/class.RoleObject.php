<?php
/**
 * Class RoleObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
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
	function createObject()
	{
		// Creates a child object
		global $tplContent;

		$rbacsystem = new RbacSystemH($this->ilias->db);
		if($rbacsystem->checkAccess("write",$_GET["obj_id"],$_GET["parent"]))
		{
			$tplContent = new Template("object_form.html",true,true);
			$tplContent->setVariable($this->ilias->ini["layout"]);

			// Zur Ausgabe des 'Path' wird die Private-Methode createPath() aufgerufen 
			$tplContent->setVariable("TREEPATH",$this->getPath());
			$tplContent->setVariable("CMD","save");
			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable("TPOS",$_GET["parent"]);
			$tplContent->setVariable("TYPE",$_POST["type"]);
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_class->WARNING);
		}
	}
	function saveObject()
	{
		$rbacadmin = new RbacAdminH($this->ilias->db); 
		$rbacsystem = new RbacSystemH($this->ilias->db);
		// CHECK ACCESS 'write' to role folder
		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			if($rbacadmin->roleExists($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError("Role Exists",$this->ilias->error_class->WARNING);
			}
			$new_obj_id = createNewObject($_POST["type"],$_POST["Fobject"]);
			$rbacadmin->assignRoleToFolder($new_obj_id,$_GET["obj_id"],'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_class->WARNING);
		}
		header("Location: content.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}

	function deleteObject()
	{

		// Erst muss das Recht zum Löschen im RoleFolder überprüft werden
		// Auslesen aller RoleFolderId's aus rbac_fa
		// => alle Id's sind Kinder oder es gibt keine anderen RoleFolder
		//    deleteRole()
		// => sonst deleteLocalRole() für alle Kinder und den zu löschenden RoleFolder

		$tree = new Tree($_GET["obj_id"],$_GET["parent"]);
		$rbacsystem = new RbacSystemH($this->ilias->db);
		if($rbacsystem->checkAccess('write',$_GET["obj_id"],$_GET["parent"]))
		{
			if($_POST["id"])
			{
				$rbacadmin = new RbacAdminH($this->ilias->db);
				$parent = $_GET["parent"] == $this->SYSTEM_FOLDER_ID ? $this->ROOT_FOLDER_ID : $_GET["parent"];
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
							$path_cmp = $tree->getPathId($folder,1);
							if(in_array($parent,$path_cmp))
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
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_class->WARNING);
		}
		header("Location: content_role.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	function permObject() 
	{
		global $tree;
		global $tplContent;

		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		$parent_obj_id = $this->getParentObjectId();
		
		if($rbacsystem->checkAccess('write',$_GET["parent"],$parent_obj_id))
		{
			$tplContent = new Template("role_perm.html",true,true);
			$tplContent->setVariable("TPOS",$_GET["parent"]);
			$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
			$tplContent->setVariable($this->ilias->ini["layout"]);
			$tplContent->setVariable("TREEPATH",$this->getPath($_GET["parent"]));

			$role_data = $rbacadmin->getRoleData($_GET["obj_id"]);
			$tplContent->setVariable("MESSAGE_TOP","Permission Template of Role: ".$role_data["title"]);

			$obj_data = getTypeList();
			// BEGIN OBJECT_TYPES
			$tplContent->setCurrentBlock("OBJECT_TYPES");
			foreach($obj_data as $data)
			{
				$tplContent->setVariable("OBJ_TYPES",$data["type"]);
				$tplContent->parseCurrentBlock();
			}
			// END OBJECT TYPES
			$all_ops = getOperationList();
			// BEGIN TABLE_DATA_OUTER
			foreach($all_ops as $key => $operations)
			{
				// BEGIN CHECK_PERM
				$tplContent->setCurrentBlock("CHECK_PERM");
				foreach($obj_data as $data)
				{
					if(in_array($operations["ops_id"],$rbacadmin->getOperationsOnType($data["obj_id"])))
					{
						$selected = $rbacadmin->getRolePermission($_GET["obj_id"],$data["type"],$_GET["parent"]);
						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = TUtil::formCheckBox($checked,"template_perm[".$data["type"]."][]",$operations["ops_id"]);
						$tplContent->setVariable("CHECK_PERMISSION",$box);
					}
					else
					{
						$tplContent->setVariable("CHECK_PERMISSION","");
					}
					$tplContent->parseCurrentBlock();
				}
				// END CHECK_PERM
				$tplContent->setCurrentBlock("TABLE_DATA_OUTER");
				$css_row = $key % 2 ? "row_low" : "row_high";
				$tplContent->setVariable("CSS_ROW",$css_row);
				$tplContent->setVariable("PERMISSION",$operations["operation"]);
				$tplContent->parseCurrentBlock();
			}
			$box = TUtil::formCheckBox($checked,"recursive",1);
			$tplContent->setVariable("COL_ANZ",count($obj_data));
			$tplContent->setVariable("CHECK_BOTTOM",$box);
		
			// USER ASSIGNMENT
			$users = getUserList();
			$assigned_users = $rbacreview->assignedUsers($_GET["obj_id"]);

			$tplContent->setVariable("MESSAGE_BOTTOM","Assign User To Role");
			$tplContent->setCurrentBLock("TABLE_USER");
			foreach($users as $key => $user)
			{
				$tplContent->setVariable("CSS_ROW_USER",$key % 2 ? "row_low" : "row_high");
				$checked = in_array($user["obj_id"],$assigned_users);
				$box = TUtil::formCheckBox($checked,"user[]",$user["obj_id"]);
				$tplContent->setVariable("CHECK_USER",$box);
				$tplContent->setVariable("USERNAME",$user["title"]);
				$tplContent->parseCurrentBlock();
			}
			// ADOPT PERMISSIONS
			$tplContent->setVariable("MESSAGE_MIDDLE","Adopt Permissions from Role Template");
			
			// BEGIN ADOPT_PERMISSIONS
			$tplContent->setCurrentBlock("ADOPT_PERMISSIONS");
			$parent_role_ids = $this->getParentRoleIds($_GET["parent"]);
			foreach($parent_role_ids as $key => $par)
			{
				$radio = TUtil::formRadioButton(0,"adopt",$par["obj_id"]);
				$tplContent->setVariable("CSS_ROW_ADOPT",$key % 2 ? "row_low" : "row_high");
				$tplContent->setVariable("CHECK_ADOPT",$radio);
				$tplContent->setVariable("ROLE_NAME",$par["title"]);
				$tplContent->parseCurrentBlock();
			}
			// END ADOPT_PERMISSIONS
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_class->WARNING);
		}
	}
	function permSaveObject()
	{
		$tree = new Tree($_GET["obj_id"],$_GET["parent"]);
		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		$parent_obj_id = $this->getParentObjectId();
		if($rbacsystem->checkAccess('edit permission',$_GET["parent"],$parent_obj_id))
		{
			// Alle Template Eintraege loeschen
			$rbacadmin->deleteRolePermission($_GET["obj_id"],$_GET["parent"]);

			foreach($_POST["template_perm"] as $key => $ops_array)
			{
				// Setzen der neuen template permissions
				$rbacadmin->setRolePermission($_GET["obj_id"],$key,$ops_array,$_GET["parent"]);
			}
			// Existierende Objekte anpassen
			if($_POST["recursive"])
			{
				$parent_obj = $this->getParentObjectId();
				if($parent_obj == $this->SYSTEM_FOLDER_ID)
				{
					$object_id = $this->ROOT_FOLDER_ID;
					$parent = 0;
				}
				else
				{
					$object_id = $_GET["parent"];
					$parent = $parent_obj;
				}
				// revoke all permissions where no permissions are set 
				$types = getTypeList();
				foreach($types as $type)
				{
					$typ = $type["type"];
					if(!is_array($_POST["template_perm"][$typ]))
					{
						$objects = $tree->getAllChildsByType($object_id,$parent,$typ);
						foreach($objects as $object)
						{
							$rbacadmin->revokePermission($object["obj_id"],$_GET["obj_id"],$object["parent"]);
						}
					}
				}
				foreach($_POST["template_perm"] as $key => $ops_array)
				{
					$objects = $tree->getAllChildsByType($object_id,$parent,$key);
					foreach($objects as $object)
					{
						$rbacadmin->revokePermission($object["obj_id"],$_GET["obj_id"],$object["parent"]);
						$rbacadmin->grantPermission($_GET["obj_id"],$ops_array,$object["obj_id"],$object["parent"]);
					}
				}
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_class->WARNING);
		}
		header("location:object.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]&cmd=perm");

	}
	function adoptPermSaveObject()
	{
		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		$parent_obj_id = $this->getParentObjectId();

		if($rbacsystem->checkAccess('edit permission',$_GET["parent"],$parent_obj_id))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"],$_GET["parent"]);
			$parentRoles = $this->getParentRoleIds($_GET["parent"]);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles["$_POST[adopt]"]["parent"],$_GET["parent"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_class->WARNING);
		}
		header("Location: object.php?obj_id=$_GET[obj_id]&parent=$_GET[parent]&cmd=perm");
		exit();
	}
	function assignSaveObject()
	{
		global $tree;
		 
		$rbacreview = new RbacReviewH($this->ilias->db);
		$rbacadmin = new RbacAdminH($this->ilias->db);
		$rbacsystem = new RbacSystemH($this->ilias->db);

		$parent_obj_id = $this->getParentObjectId();

		if($rbacsystem->checkAccess('edit permission',$_GET["parent"],$parent_obj_id))
		{
		 
			$assigned_users = $rbacreview->assignedUsers($_GET["obj_id"]);
			$_POST["user"] = $_POST["user"] ? $_POST["user"] : array();
			foreach( array_diff($assigned_users,$_POST["user"]) as $user)
			{
				$rbacadmin->deassignUser($_GET["obj_id"],$user);
			}
			foreach( array_diff($_POST["user"],$assigned_users) as $user)
			{

				if($rbacadmin->isAssignable($_GET["obj_id"],$_GET["parent"]))
				{
					$rbacadmin->assignUser($_GET["obj_id"],$user);
				}
				else
				{
					$this->ilias->raiseError("It's only possible to assign users to local roles",$this->ilias->error_class->WARNING);
				}
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_class->WARNING);
		}
		header("location:object.php?cmd=perm&obj_id=$_GET[obj_id]&parent=$_GET[parent]");
	}
	// PRIVATE
}
?>