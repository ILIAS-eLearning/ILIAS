<?php
/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjRoleGUI.php,v 1.11 2003/04/01 16:16:54 smeyer Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjRoleGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "role";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	/**
	* save a new role object
	* @access	public
	* @return new ID
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin;

		// CHECK ACCESS 'write' to role folder
		// TODO: check for create role permission should be better
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]))
		{
			$this->ilias->raiseError("You have no permission to create new roles in this role folder",$this->ilias->error_obj->WARNING);
		}
		else
		{
			// check if role title is unique
			if ($rbacadmin->roleExists($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError("A role with the name '".$_POST["Fobject"]["title"].
										 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
			}

			// create new role object
			require_once("./classes/class.ilObjRole.php");
			$roleObj = new ilObjRole();
			$roleObj->setTitle($_POST["Fobject"]["title"]);
			$roleObj->setDescription($_POST["Fobject"]["desc"]);
			$roleObj->create();
			$parent_id = $this->tree->getParentId($_GET["ref_id"]);
			$rbacadmin->assignRoleToFolder($roleObj->getId(), $_GET["ref_id"],$parent_id,'y');
		}
		
		sendInfo($this->lng->txt("role_added"),true);
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}

	/**
	* display permissions
	*/
	function permObject()
	{
		global $tree, $tpl, $rbacadmin, $rbacreview, $rbacsystem, $lng;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to write to role folder",$this->ilias->error_obj->WARNING);
		}
		else
		{
			$obj_data = getObjectList("typ","title","ASC");

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
						$selected = $rbacadmin->getRolePermission($this->object->getId(), $data["title"], $_GET["ref_id"]);

						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = ilUtil::formCheckBox($checked,"template_perm[".$data["title"]."][]",$operations["ops_id"]);
						$output["perm"]["$operation_name"][] = $box;
					}
					else
					{
						$output["perm"]["$operation_name"][] = "";
					}
				}

				// END CHECK_PERM
				// color changing
				$css_row = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["perm"]["$operation_name"]["color"] = $css_row;
			}

			// END TABLE DATA OUTER
			$box = ilUtil::formCheckBox($checked,"recursive",1);

			$output["col_anz"] = count($obj_data);
			$output["txt_save"] = $lng->txt("save");
			$output["txt_permission"] = $lng->txt("permission");
			$output["txt_obj_type"] = $lng->txt("obj_type");
			$output["txt_stop_inheritance"] = $lng->txt("stop_inheritance");
			$output["check_bottom"] = $box;
			$output["message_table"] = $lng->txt("change_existing_objects");

			// USER ASSIGNMENT
			if ($rbacadmin->isAssignable($this->object->getId(),$_GET["ref_id"]))
			{
				$users = getObjectList("usr","title","ASC");
				$assigned_users = $rbacreview->assignedUsers($this->object->getId());

				foreach ($users as $key => $user)
				{
					$output["users"][$key]["css_row_user"] = $key % 2 ? "tblrow1" : "tblrow2";
					$checked = in_array($user["obj_id"],$assigned_users);
					$box = ilUtil::formCheckBox($checked,"user[]",$user["obj_id"]);
					$output["users"][$key]["check_user"] = $box;
					$output["users"][$key]["username"] = $user["title"];
				}

				$output["message_bottom"] = $lng->txt("assign_user_to_role");
				$output["formaction_assign"] = "adm_object.php?cmd=assignSave&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId();
			}

			// ADOPT PERMISSIONS
			$output["message_middle"] = $lng->txt("adopt_perm_from_template");
			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacadmin->getParentRoleIds($_GET["ref_id"],true);

			// sort output for correct color changing
			ksort($parent_role_ids);

			foreach ($parent_role_ids as $key => $par)
			{
				$radio = ilUtil::formRadioButton(0,"adopt",$par["obj_id"]);
				$output["adopt"][$key]["css_row_adopt"] = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["adopt"][$key]["check_adopt"] = $radio;
				$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
				$output["adopt"][$key]["role_name"] = $par["title"];
			}

			$output["formaction_adopt"] = "adm_object.php?cmd=adoptPermSave&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId();

			// END ADOPT_PERMISSIONS
			$output["formaction"] = "adm_object.php?cmd=permSave&ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId();
			$output["message_top"] = "Permission Template of Role: ".$this->object->getTitle();
		}

		$this->data = $output;

		// generate output
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_perm_role.html");

		// BEGIN BLOCK OBJECT TYPES
		$this->tpl->setCurrentBlock("OBJECT_TYPES");

		foreach ($this->data["obj_types"] as $type)
		{
			$this->tpl->setVariable("OBJ_TYPES",$type);
			$this->tpl->parseCurrentBlock();
		}
		// END BLOCK OBJECT TYPES

		// BEGIN TABLE DATA OUTER
		foreach ($this->data["perm"] as $name => $operations)
		{
			// BEGIN CHECK PERMISSION
			$this->tpl->setCurrentBlock("CHECK_PERM");

			for ($i = 0;$i < count($operations)-1;++$i)
			{
				$this->tpl->setVariable("CHECK_PERMISSION",$operations[$i]);
				$this->tpl->parseCurrentBlock();
			}
			// END CHECK PERMISSION
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$this->tpl->setVariable("CSS_ROW",$operations["color"]);
			$this->tpl->setVariable("PERMISSION",$name);
			$this->tpl->parseCurrentBlock();
		} // END TABLE DATA OUTER

		// BEGIN ADOPT PERMISSIONS
		foreach ($this->data["adopt"] as $key => $value)
		{
			$this->tpl->setCurrentBlock("ADOPT_PERMISSIONS");
			$this->tpl->setVariable("CSS_ROW_ADOPT",$value["css_row_adopt"]);
			$this->tpl->setVariable("CHECK_ADOPT",$value["check_adopt"]);
			$this->tpl->setVariable("TYPE",$value["type"]);
			$this->tpl->setVariable("ROLE_NAME",$value["role_name"]);
			$this->tpl->parseCurrentBlock();
		} // END ADOPT PERMISSIONS

		// BEGIN USER_ASSIGNMENT
		if (count($this->data["users"]))
		{
			foreach ($this->data["users"] as $key => $value)
			{
				$this->tpl->setCurrentBLock("TABLE_USER");
				$this->tpl->setVariable("CSS_ROW_USER",$value["css_row_user"]);
				$this->tpl->setVariable("CHECK_USER",$value["check_user"]);
				$this->tpl->setVariable("USERNAME",$value["username"]);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("ASSIGN");
			$this->tpl->setVariable("MESSAGE_BOTTOM",$this->data["message_bottom"]);
			$this->tpl->setVariable("FORMACTION_ASSIGN",$this->data["formaction_assign"]);
			$this->tpl->parseCurrentBlock();
		} // END USER_ASSIGNMENT

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("COL_ANZ",$this->data["col_anz"]);
		$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("TXT_OBJ_TYPE",$this->data["txt_obj_type"]);
		$this->tpl->setVariable("CHECK_BOTTOM",$this->data["check_bottom"]);
		$this->tpl->setVariable("MESSAGE_TABLE",$this->data["message_table"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->setVariable("MESSAGE_MIDDLE",$this->data["message_middle"]);
		$this->tpl->setVariable("FORMACTION_ADOPT",$this->data["formaction_adopt"]);
		$this->tpl->parseCurrentBlock("adm_content");
	}

	/**
	* save permissions
	*/
	function permSaveObject()
	{
		global $tree, $rbacsystem, $rbacadmin;

		// SET TEMPLATE PERMISSIONS
		if (!$rbacsystem->checkAccess('edit permission', $_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
		else
		{
			// delete all template entries
			$rbacadmin->deleteRolePermission($this->object->getId(), $_GET["ref_id"]);

			if (empty($_POST["template_perm"]))
			{
				$_POST["template_perm"] = array();
			}

			foreach ($_POST["template_perm"] as $key => $ops_array)
			{
				// sets new template permissions
				$rbacadmin->setRolePermission($this->object->getId(), $key, $ops_array, $_GET["ref_id"]);
			}

			// CHANGE ALL EXISTING OBJECT UNDER PARENT NODE OF ROLE FOLDER
			// BUT DON'T CHANGE PERMISSIONS OF SUBTREE OBJECTS IF INHERITANCE WAS STOPPED
			if ($_POST["recursive"])
			{
				$parent_obj = $_GET["ref_id"];
				// IF PARENT NODE IS SYSTEM FOLDER START AT ROOT FOLDER
				if ($parent_obj == SYSTEM_FOLDER_ID)
				{
					$node_id = ROOT_FOLDER_ID;
				}
				else
				{
					$node_id = $parent_obj;
				}
				// GET ALL SUBNODES
				$node_data = $tree->getNodeData($node_id);
				$subtree_nodes = $tree->getSubTree($node_data);

				// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDERS
				$all_rolf_obj = $rbacadmin->getObjectsWithStopedInheritance($this->object->getId());

				// DELETE ACTUAL ROLE FOLDER FROM ARRAY
				$key = array_keys($all_rolf_obj,$node_id);
				unset($all_rolf_obj["$key[0]"]);

				$check = false;

				foreach($subtree_nodes as $node)
				{
					if(!$check)
					{
						if(in_array($node["child"],$all_rolf_obj))
						{
							$lft = $node["lft"];
							$rgt = $node["rgt"];
							$check = true;
							continue;
						}
						$valid_nodes[] = $node;
					}
					else
					{
						if(($node["lft"] > $lft) && ($node["rgt"] < $rgt))
						{
							continue;
						}
						else
						{
							$check = false;
							$valid_nodes[] = $node;
						}
					}
				}
				// NOW SET ALL PERMISSIONS
				foreach ($_POST["template_perm"] as $type => $a_perm)
				{
					foreach ($valid_nodes as $node)
					{
						if ($type == $node["type"])
						{
							$rbacadmin->revokePermission($node["child"],$this->object->getId());
							$rbacadmin->grantPermission($this->object->getId(),$a_perm,$node["child"]);
						}
					}
				}
			}// END IF RECURSIVE
		}// END CHECK ACCESS
	
		sendinfo($this->lng->txt("saved_successfully"),true);
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		exit();
	}


	/**
	* copy permissions from role
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem;

		if ($rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"], $_GET["ref_id"]);
			$parentRoles = $rbacadmin->getParentRoleIds($_GET["ref_id"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $_GET["ref_id"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
	
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		exit();
	}


	/**
	* assign users to role
	*/
	function assignSaveObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $rbacreview;
		
		// TODO: get rid of $_GET variables
		 
		if ($rbacadmin->isAssignable($_GET["obj_id"],$_GET["ref_id"]))
		{
			if ($rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
			{
				$assigned_users = $rbacreview->assignedUsers($_GET["ref_id"]);
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

		}
		else
		{
			$this->ilias->raiseError("It's worth a try. ;-)",$this->ilias->error_obj->WARNING);
		}

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		exit();
	}
	
	/**
	* update role object
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin;

		// check write access
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError("No permission to modify role",$this->ilias->error_obj->WARNING);
		}
		else
		{
			// check if role title is unique
			if ($rbacadmin->roleExists($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError("A role with the name '".$_POST["Fobject"]["title"].
										 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
			}

			// create new role object
			require_once("./classes/class.ilObjRole.php");
			$roleObj = new ilObjRole($this->object->getId());
			$roleObj->setTitle($_POST["Fobject"]["title"]);
			$roleObj->setDescription($_POST["Fobject"]["desc"]);
			$roleObj->update();
		}
		
		sendInfo($this->lng->txt("saved_successfully"),true);
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}
} // END class.RoleObjectOut
?>
