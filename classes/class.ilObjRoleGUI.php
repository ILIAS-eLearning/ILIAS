<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjRoleGUI.php,v 1.46 2003/08/18 12:42:14 shofmann Exp $
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
	* display role create form
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		
		if (!$rbacsystem->checkAccess('create', $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->getTemplateFile("edit","role");

			// fill in saved values in case of error
			$this->tpl->setVariable("TITLE",$_SESSION["error_post_vars"]["Fobject"]["title"]);
			$this->tpl->setVariable("DESC",$_SESSION["error_post_vars"]["Fobject"]["desc"]);
			$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";

			$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
			$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
			$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
			$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);
			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"]."&new_type=".$new_type);
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	
		}
	}

	/**
	* save a new role object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// CHECK ACCESS 'write' to role folder
		// TODO: check for create role permission should be better. Need 'new type'->role
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_role"),$this->ilias->error_obj->WARNING);
		}

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// check if role title is unique
		if ($rbacreview->roleExists($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".$_POST["Fobject"]["title"]."' ".
									 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
		}
		
		// check if role title has il_ prefix
		if (substr($_POST["Fobject"]["title"],0,3) == "il_")
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_reserved_prefix"),$this->ilias->error_obj->MESSAGE);
		}		

		// save
		include_once("./classes/class.ilObjRole.php");
		$roleObj = new ilObjRole();
		$roleObj->assignData($_POST["Fobject"]);
		//$roleObj->setTitle($_POST["Fobject"]["title"]);
		//$roleObj->setDescription($_POST["Fobject"]["desc"]);
		$roleObj->create();
		$rbacadmin->assignRoleToFolder($roleObj->getId(), $_GET["ref_id"],'y');
		
		sendInfo($this->lng->txt("role_added"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}

	/**
	* display permission settings template
	*
	* @access	public
	*/
	function permObject()
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// BEGIN OBJECT_TYPES
			// get all object type definitions
			$obj_data = getObjectList("typ","title","ASC");

			// remove object types that are 'deactivated' (have no operation enabled)
			foreach ($obj_data as $key => $type)
			{
				$ops_arr = $rbacreview->getOperationsOnType($type["obj_id"]);

				if (empty($ops_arr))
				{
					unset($obj_data[$key]);				
				}
			}
			
			// for local roles display only the permissions settings for allowed subobjects 
			if ($_GET["ref_id"] != ROLE_FOLDER_ID)
			{
				// first get object in question (parent of role folder object)
				$parent_data = $this->tree->getParentNodeData($_GET["ref_id"]);
				// get allowed subobject of object
				$obj_data2 = $this->objDefinition->getSubObjects($parent_data["type"]);
			
				// remove not allowed object types from array but keep the type definition of object itself
				foreach ($obj_data as $key => $type)
				{
					if (!$obj_data2[$type["title"]] and $parent_data["type"] != $type["title"])
					{
						unset($obj_data[$key]);
					}
				}
			} // end if local roles

			// normal processing
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

				$num = 0;
				
				// BEGIN CHECK_PERM
				foreach ($obj_data as $data)
				{
					if (in_array($operations["ops_id"],$rbacreview->getOperationsOnType($data["obj_id"])))
					{
						$selected = $rbacreview->getOperationsOfRole($this->object->getId(), $data["title"], $_GET["ref_id"]);

						$checked = in_array($operations["ops_id"],$selected);
						// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
						$box = ilUtil::formCheckBox($checked,"template_perm[".$data["title"]."][]",$operations["ops_id"]);
						$output["perm"]["$operation_name"][$num] = $box;
					}
					else
					{
						$output["perm"]["$operation_name"][$num] = "";
					}
					
					$num++;
				}
				// END CHECK_PERM

				// color changing
				$css_row = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$output["perm"]["$operation_name"]["color"] = $css_row;
			}

			// END TABLE DATA OUTER
			$box = ilUtil::formCheckBox(0,"recursive",1);

			$output["col_anz"] = count($obj_data);
			$output["txt_save"] = $this->lng->txt("save");
			$output["txt_permission"] = $this->lng->txt("permission");
			$output["txt_obj_type"] = $this->lng->txt("obj_type");
			$output["txt_stop_inheritance"] = $this->lng->txt("stop_inheritance");
			$output["check_bottom"] = $box;
			$output["message_table"] = $this->lng->txt("change_existing_objects");

			// ADOPT PERMISSIONS
			$output["message_middle"] = $this->lng->txt("adopt_perm_from_template");

			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacreview->getParentRoleIds($_GET["ref_id"],true);

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
			$display_row = false;
						
			// BEGIN CHECK PERMISSION
			$this->tpl->setCurrentBlock("CHECK_PERM");

			//var_dump("<pre>",$operations,"</pre>");

			for ($i = 0;$i < count($operations)-1;++$i)
			{
				if (!empty($operations[$i]))
				{
					$display_row = true;
				}
			}
			
			if ($display_row)
			{
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
			}
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

		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));
		$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath("icon_help.gif"));
		$this->tpl->setVariable("TBL_HELP_LINK","tbl_help.php");
		$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->lng->txt("help"));
		$this->tpl->setVariable("TBL_TITLE",$this->object->getTitle());
			
		$this->tpl->setVariable("COL_ANZ",$this->data["col_anz"]);
		$this->tpl->setVariable("COL_ANZ_PLUS",$this->data["col_anz"]+1);
		$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("TXT_OBJ_TYPE",$this->data["txt_obj_type"]);
		$this->tpl->setVariable("CHECK_BOTTOM",$this->data["check_bottom"]);
		$this->tpl->setVariable("MESSAGE_TABLE",$this->data["message_table"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->setVariable("MESSAGE_MIDDLE",$this->data["message_middle"]);
		$this->tpl->setVariable("FORMACTION_ADOPT",$this->data["formaction_adopt"]);
		$this->tpl->parseCurrentBlock();
	}

	/**
	* save permissions
	* 
	* @access	public
	*/
	function permSaveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// SET TEMPLATE PERMISSIONS
		if (!$rbacsystem->checkAccess('edit permission', $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
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

			// update object data entry (to update last modification date)
			$this->object->update();

			// CHANGE ALL EXISTING OBJECT UNDER PARENT NODE OF ROLE FOLDER
			// BUT DON'T CHANGE PERMISSIONS OF SUBTREE OBJECTS IF INHERITANCE WAS STOPPED
			if ($_POST["recursive"])
			{
				// IF PARENT NODE IS MAIN ROLE FOLDER START AT ROOT FOLDER
				if ($_GET["ref_id"] == ROLE_FOLDER_ID)
				{
					$node_id = ROOT_FOLDER_ID;
				}
				else
				{
					$node_id = $this->tree->getParentId($_GET["ref_id"]);
				}

				// GET ALL SUBNODES
				$node_data = $this->tree->getNodeData($node_id);
				$subtree_nodes = $this->tree->getSubTree($node_data);

				// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDERS
				$all_rolf_obj = $rbacreview->getObjectsWithStopedInheritance($this->object->getId());
				
				//var_dump("<pre>",$all_rolf_obj,"</pre>");exit;
				// DELETE ACTUAL ROLE FOLDER FROM ARRAY
				$key = array_keys($all_rolf_obj,$node_id);
				unset($all_rolf_obj["$key[0]"]);

				$check = false;

				foreach ($subtree_nodes as $node)
				{
					if (!$check)
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
						if (($node["lft"] > $lft) && ($node["rgt"] < $rgt))
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
				
				// revoke all permissions for valid nodes if nothing was posted
				if (empty($_POST["template_perm"]))
				{
					foreach ($valid_nodes as $node)
					{
						$rbacadmin->revokePermission($node["child"],$this->object->getId());
					}				
				}
				else
				{
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
				}
			}// END IF RECURSIVE
		}// END CHECK ACCESS
	
		sendinfo($this->lng->txt("saved_successfully"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&cmd=perm");
		exit();
	}


	/**
	* copy permissions from role
	* 
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		elseif ($this->object->getId() == $_POST["adopt"])
		{
			sendInfo($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->object->getId(), $_GET["ref_id"]);
			$parentRoles = $rbacreview->getParentRoleIds($_GET["ref_id"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $_GET["ref_id"],$this->object->getId());		

			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			sendInfo($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".$this->lng->txt("msg_perm_adopted_from2"),true);
		}

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&cmd=perm");
		exit();
	}


	/**
	* assign users to role
	*
	* @access	public
	*/
	function assignSaveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		if (!$rbacreview->isAssignable($this->object->getId(),$_GET["ref_id"]))
		{
			$this->ilias->raiseError("It's worth a try. ;-)",$this->ilias->error_obj->WARNING);
		}
		else
		{
			if (!$rbacsystem->checkAccess('edit permission',$_GET["ref_id"]))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
			}
			else
			{
				$_POST["id"] = $_POST["id"] ? $_POST["id"] : array();
			
				$online_users_all = ilUtil::getUsersOnline();
				$assigned_users_all = $rbacreview->assignedUsers($this->object->getId());
				$assigned_users = array_intersect($assigned_users_all,$_SESSION["user_list"]);
				$online_users_keys = array_intersect(array_keys($online_users_all),$_SESSION["user_list"]);
				$affected_users = array();
				
				// check for each user if the current role is his last role before deassigning him
				$last_role = array();
				
				foreach ($assigned_users as $user_id)
				{
					if (!in_array($user_id,$_POST["id"]))
					{
						$assigned_roles = $rbacreview->assignedRoles($user_id);
						
						if (count($assigned_roles) == 1)
						{
							$userObj = $this->ilias->obj_factory->getInstanceByObjId($user_id);
							$last_role[$user_id] = $userObj->getFullName();
							unset($userObj);
						}
					}
				}
				
				// raise error if last role was taken from a user...
				if (count($last_role) > 0)
				{
					$user_list = implode(", ",$last_role);
					$this->ilias->raiseError($this->lng->txt("msg_is_last_role").": ".$user_list."<br/>".$this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
				}

				// ...otherwise continue assignment
				foreach ($online_users_all as $user_id => $user_data)
				{
					if (in_array($user_id,$online_users_keys))
					{
						$online_users[$user_id] = $user_data;
					}
				}

				foreach (array_diff($assigned_users,$_POST["id"]) as $user)
				{
					$rbacadmin->deassignUser($this->object->getId(),$user);
					
					if (array_key_exists($user,$online_users))
					{
						$affected_users[$user] = $online_users[$user];
					}
				}

				foreach (array_diff($_POST["id"],$assigned_users) as $user)
				{
					$rbacadmin->assignUser($this->object->getId(),$user,false);

					if (array_key_exists($user,$online_users))
					{
						$affected_users[$user] = $online_users[$user];
					}
				}
				
				foreach ($affected_users as $affected_user)
				{
					$role_arr = $rbacreview->assignedRoles($affected_user["user_id"]);
		
					if ($affected_user["user_id"] == $_SESSION["AccountId"])
					{
						$_SESSION["RoleId"] = $role_arr;
					}
					else
					{
						$roles = "RoleId|".serialize($role_arr);
						$modified_data = preg_replace("/RoleId.*?;\}/",$roles,$affected_user["data"]);
			
						$q = "UPDATE usr_session SET data='".$modified_data."' WHERE user_id = '".$affected_user["user_id"]."'";
						$this->ilias->db->query($q);
					}
				}
				
				// update object data entry (to update last modification date)
				$this->object->update();			
			}
		}

		sendInfo($this->lng->txt("msg_userassignment_changed"),true);
		
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$this->object->getId()."&cmd=userassignment&sort_by=".$_GET["sort_by"]."&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);
		exit();
	}
	
	/**
	* update role object
	* 
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacreview;

		// check write access
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_role"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// check if role title is unique
			if ($rbacreview->roleExists($_POST["Fobject"]["title"],$this->object->getId()))
			{
				$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".$_POST["Fobject"]["title"]."' ".
										 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
			}

			// update
			$this->object->setTitle($_POST["Fobject"]["title"]);
			$this->object->setDescription($_POST["Fobject"]["desc"]);
			$this->object->setAllowRegister($_POST["Fobject"]["allow_register"]);
			$this->object->update();
		}
		
		sendInfo($this->lng->txt("saved_successfully"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit();
	}
	
	/**
	* edit object
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->getTemplateFile("edit");

			if ($_SESSION["error_post_vars"])
			{
				// fill in saved values in case of error
				$this->tpl->setVariable("TITLE",$_SESSION["error_post_vars"]["Fobject"]["title"]);
				$this->tpl->setVariable("DESC",$_SESSION["error_post_vars"]["Fobject"]["desc"]);
				
				$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";
			}
			else
			{
				$this->tpl->setVariable("TITLE",$this->object->getTitle());
				$this->tpl->setVariable("DESC",$this->object->getDescription());
				
				$allow_register = ($this->object->getAllowRegister()) ? "checked=\"checked\"" : "";
			}

			$obj_str = "&obj_id=".$this->obj_id;

			$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
			$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
			$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
			$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);
			$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=update");
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}
	
	/**
	* display userassignment panel
	* 
	* @access	public
	*/
	function userassignmentObject ()
	{
		global $rbacreview;
		
		$obj_str = "&obj_id=".$this->obj_id;
				
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=searchUserForm");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_user"));
		$this->tpl->parseCurrentBlock();

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "type", "name", "email", "last_change");

		if ($usr_data = getObjectList("usr",$_GET["order"], $_GET["direction"]))
		{
			foreach ($usr_data as $key => $val)
			{
				if ($key != ANONYMOUS_USER_ID)
				{
					//visible data part
					$this->data["data"][] = array(
								"type"			=> $val["type"],
								"name"			=> $val["title"],
								"email"			=> $val["desc"],
								"last_change"	=> $val["last_update"],
								"obj_id"		=> $val["obj_id"]
							);
				}
			}
		} //if userdata

		$this->maxcount = count($this->data["data"]);

		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "title")
		{
			$_GET["sort_by"] = "name";
		}

		// sorting array
		include_once "./include/inc.sort.php";
		$this->data["data"] = sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		$assigned_users = $rbacreview->assignedUsers($this->object->getId());

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$checked = in_array($this->data["data"][$key]["obj_id"],$assigned_users);

			$this->data["ctrl"][$key] = array(
											"ref_id"	=> $this->id,
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"],
											"assigned"	=> $checked
											);
			$tmp[] = $val["obj_id"];

			unset($this->data["data"][$key]["obj_id"]);

			$this->data["data"][$key]["last_change"] = ilFormat::formatDate($this->data["data"][$key]["last_change"]);
		}

		// remember filtered users
		$_SESSION["user_list"] = $tmp;		
	
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=assignSave&sort_by=".$_GET["sort_by"]."&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

		include_once "./classes/class.ilTableGUI.php";

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("user_assignment"),"icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);
		
		$header_params = array(
								"ref_id"	=> $this->ref_id,
								"obj_id"	=> $this->obj_id,
								"cmd"		=> "userassignment"
							  );

		$tbl->setHeaderVars($this->data["cols"],$header_params);
		//$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));	

		// display action button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "assignSave");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("change_assignment"));
		$this->tpl->parseCurrentBlock();

		$this->showActions(true);
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				//var_dump("<pre>",$ctrl,"</pre>");
				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				($ctrl["assigned"]) ? $checked = "checked=\"checked\"" : $checked = "";
				
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
	

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					$this->tpl->setCurrentBlock("text");

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);						
					}

					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for

		} //if is_array
	}
	
	/**
	* displays user search form 
	* 
	*
	*/
	function searchUserFormObject ()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.usr_search_form.html");

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&obj_id=".$this->obj_id."&cmd=gateway");
		$this->tpl->setVariable("TXT_SEARCH_USER",$this->lng->txt("search_user"));
		$this->tpl->setVariable("TXT_SEARCH_IN",$this->lng->txt("search_in"));
		$this->tpl->setVariable("TXT_SEARCH_USERNAME",$this->lng->txt("username"));
		$this->tpl->setVariable("TXT_SEARCH_FIRSTNAME",$this->lng->txt("firstname"));
		$this->tpl->setVariable("TXT_SEARCH_LASTNAME",$this->lng->txt("lastname"));
		$this->tpl->setVariable("TXT_SEARCH_EMAIL",$this->lng->txt("email"));
		$this->tpl->setVariable("BUTTON_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));	
	}
	
	function searchCancelledObject ()
	{
		sendInfo($this->lng->txt("action_aborted"),true);

		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=userassignment");
		exit();
	}

	function searchUserObject ()
	{
		global $rbacreview;
		
		$obj_str = "&obj_id=".$this->obj_id;

		$_POST["search_string"] = $_POST["search_string"] ? $_POST["search_string"] : urldecode($_GET["search_string"]);

		if (empty($_POST["search_string"]))
		{
			sendInfo($this->lng->txt("msg_no_search_string"),true);

			header("Location: adm_object.php?ref_id=".$_GET["ref_id"].$obj_str."&cmd=searchUserForm");
			exit();
		}

		if (count($search_result = ilObjUser::searchUsers($_POST["search_string"])) == 0)
		{
			sendInfo($this->lng->txt("msg_no_search_result")." ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'",true);

			header("Location: adm_object.php?ref_id=".$_GET["ref_id"].$obj_str."&cmd=searchUserForm");
			exit();		
		}

		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=searchUserForm");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_new"));
		$this->tpl->parseCurrentBlock();

		$this->data["cols"] = array("", "login", "firstname", "lastname", "email");

		foreach ($search_result as $key => $val)
		{
			//visible data part
			$this->data["data"][] = array(
							"login"			=> $val["login"],
							"firstname"		=> $val["firstname"],
							"lastname"		=> $val["lastname"],
							"email"			=> $val["email"],
							"obj_id"		=> $val["usr_id"]
						);
		}

		$this->maxcount = count($this->data["data"]);

		// TODO: correct this in objectGUI
		if ($_GET["sort_by"] == "title")
		{
			$_GET["sort_by"] = "login";
		}

		// sorting array
		include_once "./include/inc.sort.php";
		$this->data["data"] = sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		$assigned_users = $rbacreview->assignedUsers($this->object->getId());

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$checked = in_array($this->data["data"][$key]["obj_id"],$assigned_users);

			$this->data["ctrl"][$key] = array(
											"ref_id"	=> $this->id,
											"obj_id"	=> $val["obj_id"],
											"assigned"	=> $checked
										);
			$tmp[] = $val["obj_id"];
			unset($this->data["data"][$key]["obj_id"]);
		}

		// remember filtered users
		$_SESSION["user_list"] = $tmp;		
	
		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id.$obj_str."&cmd=assignSave&sort_by=name&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

		// create table
		include_once "./classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("user_assignment")." (".$this->lng->txt("search_result").")","icon_".$this->object->getType()."_b.gif",$this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = array(
							"ref_id"		=> $this->ref_id,
							"obj_id"		=> $this->obj_id,
							"cmd"			=> "searchUser",
							"search_string" => urlencode($_POST["search_string"])
					  		);

		$tbl->setHeaderVars($this->data["cols"],$header_params);
		//$tbl->setColumnWidth(array("7%","7%","15%","31%","6%","17%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));	

		// display action button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "assignSave");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("change_assignment"));
		$this->tpl->parseCurrentBlock();

		$this->showActions(true);
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");
		
				($ctrl["assigned"]) ? $checked = "checked=\"checked\"" : $checked = "";
				
				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID", $ctrl["obj_id"]);
				$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			
				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();
		
				foreach ($data as $key => $val)
				{
					$this->tpl->setCurrentBlock("text");

					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();
				} //foreach
		
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		}
	}
} // END class.ilObjRoleGUI
?>
