<?php
/**
* Class RoleObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class RoleObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function RoleObject()
	{
		$this->Object();
	}

	/**
	* create a role object 
	* @access	public
	*/
	function createObject()
	{
		// Creates a child object
		global $rbacsystem;

		if ($rbacsystem->checkAccess("write",$_GET["obj_id"],$_GET["parent"]))
		{
			$data = array();
			$data["fields"] = array();
			
			$data["fields"]["title"] = "";
			$data["fields"]["desc"] = "";
			return $data;
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* save a new role object
	* @access	public
	**/
	function saveObject($a_obj_id = '', $a_parent = '' , $a_data = '')
	{
		global $rbacsystem, $rbacadmin;

		$a_obj_id = $a_obj_id ? $a_obj_id : $_GET["obj_id"];
		$a_parent = $a_parent ? $a_parent : $_GET["parent"];
		$a_data = $a_data ? $a_data : $_POST["Fobject"];
	
		// CHECK ACCESS 'write' to role folder
		if ($rbacsystem->checkAccess('write',$a_obj_id,$a_parent))
		{
			if ($rbacadmin->roleExists($a_data["title"]))
			{
				$this->ilias->raiseError("A role with the name '".$a_data["title"].
										 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
			}

			$new_obj_id = createNewObject('role',$a_data);
			$rbacadmin->assignRoleToFolder($new_obj_id,$a_obj_id,$a_parent,'y');
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		
		//return true;
		
		return $new_obj_id;
	}

	/**
	* delete a role object
	* @access	public
	**/
	function deleteObject($a_obj_id, $a_parent)
	{
		global $tree, $rbacadmin;
		
		if($rbacadmin->isAssignable($a_obj_id,$a_parent))
		{
			// IT'S THE BASE ROLE
			$rbacadmin->deleteRole($a_obj_id,$a_parent);
		}
		else
		{
			// INHERITANCE WAS STOPED, SO DELETE ONLY THIS LOCAL ROLE
			$rbacadmin->deleteLocalRole($a_obj_id,$a_parent);
		}
		return true;
	}

	/**
	* edit a role object
	* @access	public
	* 
	**/
	function editObject()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]))
		{
			$obj = getObject($this->id);
			
			$data = array();
			$data["fields"] = array();
			
			$data["fields"]["title"] = $obj["title"];
			$data["fields"]["desc"] = $obj["desc"];
			return $data;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}			
	}

	/**
	* update a role object
	* @access	public
	**/
	function updateObject()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]))
		{
			updateObject($_GET["obj_id"],$_GET["type"],$_POST["Fobject"]);
			return true;
		}
		else
		{
			$this->ilias->raiseError("No permission to edit the object",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* show permission templates of role object
	* @access	public
	**/
	function permObject()
	{
		global $tree, $tpl, $rbacadmin, $rbacreview, $rbacsystem, $lng;

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			$obj_data = getTypeList();
			// BEGIN OBJECT_TYPES

			foreach ($obj_data as $data)
			{
				$output["obj_types"][] = $data["title"];
			}

			// END OBJECT TYPES
			$all_ops = getOperationList();

			// BEGIN TABLE_DATA_OUTER
			foreach ($all_ops as $key => $operations)
			{
				$operation_name = $operations["operation"];
				// BEGIN CHECK_PERM

				foreach ($obj_data as $data)
				{
					if (in_array($operations["ops_id"],$rbacadmin->getOperationsOnType($data["obj_id"])))
					{
						$selected = $rbacadmin->getRolePermission($_GET["obj_id"],$data["title"],$_GET["parent"]);

						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = TUtil::formCheckBox($checked,"template_perm[".$data["title"]."][]",$operations["ops_id"]);
						$output["perm"]["$operation_name"][] = $box;
					}
					else
					{
						$output["perm"]["$operation_name"][] = "";
					}
				}

				// END CHECK_PERM
				// color changing
				$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["perm"]["$operation_name"]["color"] = $css_row;
			}

			// END TABLE DATA OUTER
			$box = TUtil::formCheckBox($checked,"recursive",1);
		
			$output["col_anz"] = count($obj_data);
			$output["check_bottom"] = $box;
			$output["message_table"] = "Change existing objects";

			// USER ASSIGNMENT
			if ($rbacadmin->isAssignable($_GET["obj_id"],$_GET["parent"]))
			{
				$users = getUserList();
				$assigned_users = $rbacreview->assignedUsers($_GET["obj_id"]);

				foreach ($users as $key => $user)
				{
					$output["users"][$key]["css_row_user"] = $key % 2 ? "tblrow1" : "tblrow2";
					$checked = in_array($user["obj_id"],$assigned_users);
					$box = TUtil::formCheckBox($checked,"user[]",$user["obj_id"]);
					$output["users"][$key]["check_user"] = $box;
					$output["users"][$key]["username"] = $user["title"];
				}
				
				$output["message_bottom"] = "Assign User To Role";
				$output["formaction_assign"] = "adm_object.php?cmd=assignSave&obj_id=".
								  $this->id."&parent_parent=".$this->parent_parent."&parent=".$this->parent;
			}

			// ADOPT PERMISSIONS
			$output["message_middle"] = "Adopt Permissions from Role Template";
			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);

			// sort output for correct color changing
			ksort($parent_role_ids);

			foreach ($parent_role_ids as $key => $par)
			{
				$radio = TUtil::formRadioButton(0,"adopt",$par["obj_id"]);
				$output["adopt"][$key]["css_row_adopt"] = TUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["adopt"][$key]["check_adopt"] = $radio;
				$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
				$output["adopt"][$key]["role_name"] = $par["title"];
			}
			$output["formaction_adopt"] = "adm_object.php?cmd=adoptPermSave&obj_id="
				.$this->id."&parent_parent=".$this->parent_parent."&parent=".$this->parent;

			// END ADOPT_PERMISSIONS
			$output["formaction"] = "adm_object.php?cmd=permSave&obj_id=".
				$this->id."&parent_parent=".$this->parent_parent."&parent=".$this->parent;
			$role_data = $rbacadmin->getRoleData($_GET["obj_id"]);
			$output["message_top"] = "Permission Template of Role: ".$role_data["title"];
		}
		else
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}

		return $output;
	}
	/**
	* save permission templates of a role object
	* @access	public
	**/
	function permSaveObject()
	{
		global $tree, $rbacsystem, $rbacadmin;

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			// delete all template entries
			$rbacadmin->deleteRolePermission($_GET["obj_id"],$_GET["parent"]);

			if (empty($_POST["template_perm"]))
			{
			    $_POST["template_perm"] = array();
			}
			
			foreach ($_POST["template_perm"] as $key => $ops_array)
			{
				// sets new template permissions
				$rbacadmin->setRolePermission($_GET["obj_id"],$key,$ops_array,$_GET["parent"]);
			}
			// Existierende Objekte anpassen
			if ($_POST["recursive"])
			{
				$parent_obj = $_GET["parent_parent"];
				if ($parent_obj == SYSTEM_FOLDER_ID)
				{
					$object_id = ROOT_FOLDER_ID;
					$parent = 0;
				}
				else
				{
					$object_id = $_GET["parent"];
					$parent = $_GET["parent_parent"];
				}
				// revoke all permissions where no permissions are set 
				$types = getTypeList();

				foreach ($types as $type)
				{
					$typ = $type["title"];

					if (!is_array($_POST["template_perm"][$typ]))
					{
						$objects = $tree->getAllChildsByType($object_id,$parent,$typ);

						foreach ($objects as $object)
						{
							$rbacadmin->revokePermission($object["obj_id"],$object["parent"],$_GET["obj_id"]);
						}
					}
				}

				foreach ($_POST["template_perm"] as $key => $ops_array)
				{
					$objects = $tree->getAllChildsByType($object_id,$parent,$key);

					foreach ($objects as $object)
					{
						$rbacadmin->revokePermission($object["obj_id"],$object["parent"],$_GET["obj_id"]);
						$rbacadmin->grantPermission($_GET["obj_id"],$ops_array,$object["obj_id"],$object["parent"]);
					}
				}
			}
		}
		else
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
		}

		return true;
	}

	/**
	* copy permissions from role
	* @access	public
	**/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem;

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"],$_GET["parent"]);
			$parentRoles = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $_GET["parent"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
		return true;
	}

	/**
	* assign user to role
	* @access	public
	**/
	function assignSaveObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $rbacreview;
		 
		if ($rbacadmin->isAssignable($_GET["obj_id"],$_GET["parent"]))
		{
			if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
			{
				$assigned_users = $rbacreview->assignedUsers($_GET["obj_id"]);
				$_POST["user"] = $_POST["user"] ? $_POST["user"] : array();

				foreach (array_diff($assigned_users,$_POST["user"]) as $user)
				{
					$rbacadmin->deassignUser($_GET["obj_id"],$user);
				}

				foreach (array_diff($_POST["user"],$assigned_users) as $user)
				{
					$rbacadmin->assignUser($_GET["obj_id"],$user,false);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
			}

			return true;
		}
		else
		{
			$this->ilias->raiseError("It's worth a try. ;-)",$this->ilias->error_obj->WARNING);
		}
	}
} // END class.RoleObject
?>