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
* $Id$Id: class.ilObjRoleGUI.php,v 1.71 2004/01/21 16:56:38 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjRoleGUI extends ilObjectGUI
{
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* rolefolder ref_id where role is assigned to
	* @var		string
	* @access	public
	*/
	var $rolf_ref_id;
 
	/**
	* Constructor
	* @access public
	*/
	function ilObjRoleGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "role";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
		$this->rolf_ref_id =& $this->ref_id;
	}

	/**
	* display role create form
	*/
	function createObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess('create_role', $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->getTemplateFile("edit","role");

		// fill in saved values in case of error
		$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"]),true);
		$this->tpl->setVariable("DESC",ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]));

		$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";
		$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
		$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);

		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".$this->rolf_ref_id."&new_type=".$this->type));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($this->type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* save a new role object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// check for create role permission
		if (!$rbacsystem->checkAccess("create_role",$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_role"),$this->ilias->error_obj->MESSAGE);
		}

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// check if role title is unique
		if ($rbacreview->roleExists($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".ilUtil::stripSlashes($_POST["Fobject"]["title"])."' ".
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
		//$roleObj->assignData($_POST["Fobject"]);
		$roleObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$roleObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$roleObj->setAllowRegister($_POST["Fobject"]["allow_register"]);
		$roleObj->create();
		$rbacadmin->assignRoleToFolder($roleObj->getId(), $this->rolf_ref_id,'y');
		
		sendInfo($this->lng->txt("role_added"),true);

		header("Location: adm_object.php?ref_id=".$this->rolf_ref_id);
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

		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
			exit();
		}

		// build array with all rbac object types
		$q = "SELECT ta.typ_id,obj.title,ops.ops_id,ops.operation FROM rbac_ta AS ta ".
			 "LEFT JOIN object_data AS obj ON obj.obj_id=ta.typ_id ".
			 "LEFT JOIN rbac_operations AS ops ON ops.ops_id=ta.ops_id";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rbac_objects[$row->typ_id] = array("obj_id"	=> $row->typ_id,
											    "type"		=> $row->title
												);

			$rbac_operations[$row->typ_id][$row->ops_id] = array(
									   							"ops_id"	=> $row->ops_id,
									  							"title"		=> $row->operation,
																"name"		=> $this->lng->txt($row->title."_".$row->operation)
															   );
		}
			
		foreach ($rbac_objects as $key => $obj_data)
		{
			$rbac_objects[$key]["name"] = $this->lng->txt("obj_".$obj_data["type"]);
			$rbac_objects[$key]["ops"] = $rbac_operations[$key];
		}


		// for local roles display only the permissions settings for allowed subobjects 
		if ($this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			// first get object in question (parent of role folder object)
			$parent_data = $this->tree->getParentNodeData($this->rolf_ref_id);
			// get allowed subobject of object
			$subobj_data = $this->objDefinition->getSubObjects($parent_data["type"]);
			
			// remove not allowed object types from array but keep the type definition of object itself
			foreach ($rbac_objects as $key => $obj_data)
			{
				if (!$subobj_data[$obj_data["type"]] and $parent_data["type"] != $obj_data["type"])
				{
					unset($rbac_objects[$key]);
				}
			}
		} // end if local roles
		
		// now sort computed result
		sort($rbac_objects);
			
		foreach ($rbac_objects as $key => $obj_data)
		{
			sort($rbac_objects[$key]["ops"]);
		}
		
		// sort by (translated) name of object type
		$rbac_objects = ilUtil::sortArray($rbac_objects,"name","asc");

		// BEGIN CHECK_PERM
		$global_roles_all = $rbacreview->getGlobalRoles();
		$global_roles_user = array_intersect($_SESSION["RoleId"],$global_roles_all);
		
		// is this role a global role?
		if (in_array($this->object->getId(),$global_roles_all))
		{
			$global_role = true;
		}
		else
		{
			$global_role = false;
		}

		foreach ($rbac_objects as $key => $obj_data)
		{
			$allowed_ops_on_type = array();

			foreach ($global_roles_user as $role_id)
			{
				$allowed_ops_on_type = array_merge($allowed_ops_on_type,$rbacreview->getOperationsOfRole($role_id,$obj_data["type"]));
			}
				
			$allowed_ops_on_type = array_unique($allowed_ops_on_type);
				
			$arr_selected = $rbacreview->getOperationsOfRole($this->object->getId(), $obj_data["type"], $this->rolf_ref_id);
			$arr_checked = array_intersect($arr_selected,array_keys($rbac_operations[$obj_data["obj_id"]]));

			foreach ($rbac_operations[$obj_data["obj_id"]] as $operation)
			{
				// check all boxes for system role
				if ($this->object->getId() == SYSTEM_ROLE_ID)
				{
					$checked = true;
					$disabled = true;
				}
				else
				{
					$checked = in_array($operation["ops_id"],$arr_checked);

					// for global roles only allow to set those permission the current user is granted himself except SYSTEM_ROLE_ID !!
					if (!in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]) and $global_role == true and !in_array($operation["ops_id"],$allowed_ops_on_type))
					{
						$disabled = true;
					}
					else
					{
						$disabled = false;
					}
				}

				// Es wird eine 2-dim Post Variable übergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"template_perm[".$obj_data["type"]."][]",$operation["ops_id"],$disabled);
				$output["perm"][$obj_data["obj_id"]][$operation["ops_id"]] = $box;
			}
		}
		// END CHECK_PERM

		$output["col_anz"] = count($rbac_objects);
		$output["txt_save"] = $this->lng->txt("save");
		$output["check_bottom"] = ilUtil::formCheckBox(0,"recursive",1);
		$output["message_table"] = $this->lng->txt("change_existing_objects");


/************************************/
/*		adopt permissions form		*/
/************************************/

		$output["message_middle"] = $this->lng->txt("adopt_perm_from_template");

		// send message for system role
		if ($this->object->getId() == SYSTEM_ROLE_ID)
		{
			$output["adopt"] = array();
			$output["sysrole_msg"] = $this->lng->txt("msg_sysrole_not_editable");
		}
		else
		{
			// BEGIN ADOPT_PERMISSIONS
			$parent_role_ids = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);

			// sort output for correct color changing
			ksort($parent_role_ids);

			foreach ($parent_role_ids as $key => $par)
			{
				if ($par["obj_id"] != SYSTEM_ROLE_ID)
				{
					$radio = ilUtil::formRadioButton(0,"adopt",$par["obj_id"]);
					$output["adopt"][$key]["css_row_adopt"] = ilUtil::switchColor($key, "tblrow1", "tblrow2");
					$output["adopt"][$key]["check_adopt"] = $radio;
					$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
					$output["adopt"][$key]["role_name"] = $par["title"];
				}
			}
	
			$output["formaction_adopt"] = "adm_object.php?cmd=adoptPermSave&ref_id=".$this->rolf_ref_id."&obj_id=".$this->object->getId();
			// END ADOPT_PERMISSIONS
		}

		$output["formaction"] = "adm_object.php?cmd=permSave&ref_id=".$this->rolf_ref_id."&obj_id=".$this->object->getId();

		$this->data = $output;


/************************************/
/*			generate output			*/
/************************************/

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_perm_role.html");

		foreach ($rbac_objects as $obj_data)
		{
			// BEGIN object_operations
			$this->tpl->setCurrentBlock("object_operations");
	
			foreach ($obj_data["ops"] as $operation)
			{
				$css_row = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW",$css_row);
				$this->tpl->setVariable("PERMISSION",$operation["name"]);
				$this->tpl->setVariable("CHECK_PERMISSION",$this->data["perm"][$obj_data["obj_id"]][$operation["ops_id"]]);
				$this->tpl->parseCurrentBlock();
			} // END object_operations
			
			// BEGIN object_type
			$this->tpl->setCurrentBlock("object_type");
			$this->tpl->setVariable("TXT_OBJ_TYPE",$obj_data["name"]);
			$this->tpl->parseCurrentBlock();
			// END object_type
		}

		// don't display adopt permissions form for system role
		if ($this->object->getId() != SYSTEM_ROLE_ID)
		{
			// BEGIN ADOPT PERMISSIONS
			foreach ($this->data["adopt"] as $key => $value)
			{
				$this->tpl->setCurrentBlock("ADOPT_PERM_ROW");
				$this->tpl->setVariable("CSS_ROW_ADOPT",$value["css_row_adopt"]);
				$this->tpl->setVariable("CHECK_ADOPT",$value["check_adopt"]);
				$this->tpl->setVariable("TYPE",$value["type"]);
				$this->tpl->setVariable("ROLE_NAME",$value["role_name"]);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("ADOPT_PERM_FORM");
			$this->tpl->setVariable("MESSAGE_MIDDLE",$this->data["message_middle"]);
			$this->tpl->setVariable("FORMACTION_ADOPT",$this->data["formaction_adopt"]);
			$this->tpl->parseCurrentBlock();
			// END ADOPT PERMISSIONS
		
			$this->tpl->setCurrentBlock("tblfooter_recursive");
			$this->tpl->setVariable("COL_ANZ",3);
			$this->tpl->setVariable("CHECK_BOTTOM",$this->data["check_bottom"]);
			$this->tpl->setVariable("MESSAGE_TABLE",$this->data["message_table"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tblfooter_standard");
			$this->tpl->setVariable("COL_ANZ_PLUS",4);
			$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			// display form buttons not for system role
			$this->tpl->setCurrentBlock("tblfooter_sysrole");
			$this->tpl->setVariable("COL_ANZ_SYS",3);
			$this->tpl->parseCurrentBlock();

			// display sysrole_msg
			$this->tpl->setCurrentBlock("sysrole_msg");
			$this->tpl->setVariable("TXT_SYSROLE_MSG",$this->data["sysrole_msg"]);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));
		$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath("icon_help.gif"));
		$this->tpl->setVariable("TBL_HELP_LINK","tbl_help.php");
		$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->lng->txt("help"));
		$this->tpl->setVariable("TBL_TITLE",$this->object->getTitle());
			
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
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
		if (!$rbacsystem->checkAccess('write', $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		// first safe permissions that were disabled in HTML form due to missing lack of permissions of user who changed it
		// TODO: move this following if-code into an extra function. this part is also used in $this->permObject !!
		if (!in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]))
		{
			// build array with all rbac object types
			$q = "SELECT ta.typ_id,obj.title,ops.ops_id,ops.operation FROM rbac_ta AS ta ".
				 "LEFT JOIN object_data AS obj ON obj.obj_id=ta.typ_id ".
				 "LEFT JOIN rbac_operations AS ops ON ops.ops_id=ta.ops_id";
			$r = $this->ilias->db->query($q);
	
			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$rbac_objects[$row->typ_id] = array("obj_id"	=> $row->typ_id,
												    "type"		=> $row->title
													);
	
				$rbac_operations[$row->typ_id][$row->ops_id] = array(
										   							"ops_id"	=> $row->ops_id,
										  							"title"		=> $row->operation,
																	"name"		=> $this->lng->txt($row->title."_".$row->operation)
																   );
			}
				
			foreach ($rbac_objects as $key => $obj_data)
			{
				$rbac_objects[$key]["name"] = $this->lng->txt("obj_".$obj_data["type"]);
				$rbac_objects[$key]["ops"] = $rbac_operations[$key];
			}
	
			$global_roles_all = $rbacreview->getGlobalRoles();
			$global_roles_user = array_intersect($_SESSION["RoleId"],$global_roles_all);
			
			foreach ($rbac_objects as $key => $obj_data)
			{
				$allowed_ops_on_type = array();
	
				foreach ($global_roles_user as $role_id)
				{
					$allowed_ops_on_type = array_merge($allowed_ops_on_type,$rbacreview->getOperationsOfRole($role_id,$obj_data["type"]));
				}
					
				$allowed_ops_on_type = array_unique($allowed_ops_on_type);
					
				$arr_previous = $rbacreview->getOperationsOfRole($this->object->getId(), $obj_data["type"], $this->rolf_ref_id);
				$arr_missing = array_diff($arr_previous,$allowed_ops_on_type);
				
				$_POST["template_perm"][$obj_data["type"]] = array_merge($_POST["template_perm"][$obj_data["type"]],$arr_missing);
				
				// remove empty types
				if (empty($_POST["template_perm"][$obj_data["type"]]))
				{
					unset($_POST["template_perm"][$obj_data["type"]]);
				}
			}
		} // END TODO: move!!!

		// delete all template entries
		$rbacadmin->deleteRolePermission($this->object->getId(), $this->rolf_ref_id);

		if (empty($_POST["template_perm"]))
		{
			$_POST["template_perm"] = array();
		}

		foreach ($_POST["template_perm"] as $key => $ops_array)
		{
			// sets new template permissions
			$rbacadmin->setRolePermission($this->object->getId(), $key, $ops_array, $this->rolf_ref_id);
		}

		// update object data entry (to update last modification date)
		$this->object->update();

		// CHANGE ALL EXISTING OBJECT UNDER PARENT NODE OF ROLE FOLDER
		// BUT DON'T CHANGE PERMISSIONS OF SUBTREE OBJECTS IF INHERITANCE WAS STOPPED
		if ($_POST["recursive"])
		{
			// IF PARENT NODE IS MAIN ROLE FOLDER START AT ROOT FOLDER
			if ($this->rolf_ref_id == ROLE_FOLDER_ID)
			{
				$node_id = ROOT_FOLDER_ID;
			}
			else
			{
				$node_id = $this->rolf_ref_id;
			}

			// GET ALL SUBNODES
			$node_data = $this->tree->getNodeData($node_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDER
			$all_rolf_obj = $rbacreview->getObjectsWithStopedInheritance($this->object->getId());

			// DELETE ACTUAL ROLE FOLDER FROM ARRAY
			$key = array_keys($all_rolf_obj,$node_id);
			unset($all_rolf_obj[$key[0]]);

			$check = false;

			foreach ($subtree_nodes as $node)
			{
				if (!$check)
				{
					if (in_array($node["child"],$all_rolf_obj))
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

			// prepare arrays for permission settings below
			foreach ($valid_nodes as $key => $node)
			{
				$node_ids[] = $node["child"];
				
				$valid_nodes[$key]["perms"] = $_POST["template_perm"][$node["type"]];
			}
			
			// FIRST REVOKE PERMISSIONS FROM ALL VALID OBJECTS
			$rbacadmin->revokePermissionList($node_ids,$this->object->getId());

			// NOW SET ALL PERMISSIONS
			foreach ($valid_nodes as $node)
			{
				if (is_array($node["perms"]))
				{
					$rbacadmin->grantPermission($this->object->getId(),$node["perms"],$node["child"]);
				}
			}
		}// END IF RECURSIVE

		sendinfo($this->lng->txt("saved_successfully"),true);

		header("Location: adm_object.php?ref_id=".$this->rolf_ref_id."&obj_id=".$this->object->getId()."&cmd=perm");
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

		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}
		elseif ($this->object->getId() == $_POST["adopt"])
		{
			sendInfo($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->object->getId(), $this->rolf_ref_id);
			$parentRoles = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $this->rolf_ref_id,$this->object->getId());		

			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			sendInfo($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".$this->lng->txt("msg_perm_adopted_from2"),true);
		}

		header("Location: adm_object.php?ref_id=".$this->rolf_ref_id."&obj_id=".$this->object->getId()."&cmd=perm");
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

		if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("err_role_not_assignable"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
			}
			else
			{
				$_POST["id"] = $_POST["id"] ? $_POST["id"] : array();
				
				// prevent unassignment of system user from system role
				if ($this->object->getId() == SYSTEM_ROLE_ID and in_array(SYSTEM_USER_ID, $_SESSION["user_list"]))
				{
					array_push($_POST["id"],SYSTEM_USER_ID);
				}
				
				$global_roles = $rbacreview->getGlobalRoles();
				$online_users_all = ilUtil::getUsersOnline();
				$assigned_users_all = $rbacreview->assignedUsers($this->object->getId());
				$assigned_users = array_intersect($assigned_users_all,$_SESSION["user_list"]);
				$online_users_keys = array_intersect(array_keys($online_users_all),$_SESSION["user_list"]);
				$affected_users = array();
				
				// check for each user if the current role is his last global role before deassigning him
				$last_role = array();
				
				foreach ($assigned_users as $user_id)
				{
					if (!in_array($user_id,$_POST["id"]))
					{
						$assigned_roles = $rbacreview->assignedRoles($user_id);
						
						$assigned_global_roles = array_intersect($assigned_roles,$global_roles);
				
						if (count($assigned_roles) == 1 or (count($assigned_global_roles) == 1 and in_array($this->object->getId(),$assigned_global_roles)))
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
		
		header("Location: adm_object.php?ref_id=".$this->rolf_ref_id."&obj_id=".$this->object->getId()."&cmd=userassignment&sort_by=".$_GET["sort_by"]."&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);
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
		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_role"),$this->ilias->error_obj->MESSAGE);
		}

		// check required fields
		if (empty($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// check if role title is unique
		if ($rbacreview->roleExists($_POST["Fobject"]["title"],$this->object->getId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".ilUtil::stripSlashes($_POST["Fobject"]["title"])."' ".
									 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
		}

		// update
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->object->setAllowRegister($_POST["Fobject"]["allow_register"]);
		$this->object->update();
		
		sendInfo($this->lng->txt("saved_successfully"),true);

		header("Location: adm_object.php?ref_id=".$this->rolf_ref_id);
		exit();
	}
	
	/**
	* edit object
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->getTemplateFile("edit");

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"]),true);
			$this->tpl->setVariable("DESC",ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]));
			$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";
		}
		else
		{
			$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($this->object->getTitle()));
			$this->tpl->setVariable("DESC",ilUtil::stripSlashes($this->object->getDescription()));
			$allow_register = ($this->object->getAllowRegister()) ? "checked=\"checked\"" : "";
		}

		$obj_str = "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
		
		// exclude allow register option for anonymous role, system role and all local roles
		$global_roles = $rbacreview->getGlobalRoles();

		if ($this->object->getId() != ANONYMOUS_ROLE_ID and $this->object->getId() != SYSTEM_ROLE_ID and in_array($this->object->getId(),$global_roles))
		{
			$this->tpl->setCurrentBlock("allow_register");
			$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
			$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update","adm_object.php?cmd=gateway&ref_id=".$this->rolf_ref_id.$obj_str));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}
	
	/**
	* display userassignment panel
	* 
	* @access	public
	*/
	function userassignmentObject ()
	{
		global $rbacreview,$rbacsystem;
		
		if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}
		
		// do not allow to assign admin users roles to administrator role if current user does not has SYSTEM_ROLE_ID
		if ($this->object->getId() == SYSTEM_ROLE_ID and !in_array(SYSTEM_ROLE_ID,$_SESSION["RoleId"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_sysadmin_sysrole_not_assignable"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("err_role_not_assignable"),$this->ilias->error_obj->MESSAGE);
		}
		
		$obj_str = "&obj_id=".$this->obj_id;
				
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","adm_object.php?ref_id=".$this->rolf_ref_id.$obj_str."&cmd=searchUserForm");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("search_user"));
		$this->tpl->parseCurrentBlock();

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "", "name", "email", "last_change");

		if ($usr_data = getObjectList("usr",$_GET["order"], $_GET["direction"]))
		{
			foreach ($usr_data as $key => $val)
			{
				// exclude anonymous user account from list
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
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
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

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->rolf_ref_id.$obj_str."&cmd=assignSave&sort_by=".$_GET["sort_by"]."&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

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
								"ref_id"	=> $this->rolf_ref_id,
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

		$this->showActions();
		
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
				
				// disable checkbox for system user id for the system role id
				if ($this->object->getId() == SYSTEM_ROLE_ID and $ctrl["obj_id"] == SYSTEM_USER_ID)
				{
					$this->tpl->setVariable("CHECKED", $checked." disabled=\"disabled\"");
				}
				else
				{
					$this->tpl->setVariable("CHECKED", $checked);
				}

				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$link = "adm_object.php?ref_id=7&obj_id=".$ctrl["obj_id"];
	
					if ($key == "name" || $key == "type")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);
						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
						}

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

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->rolf_ref_id."&obj_id=".$this->obj_id."&cmd=gateway");
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

		header("Location: adm_object.php?ref_id=".$this->rolf_ref_id."&obj_id=".$_GET["obj_id"]."&cmd=userassignment");
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

			header("Location: adm_object.php?ref_id=".$this->rolf_ref_id.$obj_str."&cmd=searchUserForm");
			exit();
		}

		if (count($search_result = ilObjUser::searchUsers($_POST["search_string"])) == 0)
		{
			sendInfo($this->lng->txt("msg_no_search_result")." ".$this->lng->txt("with")." '".htmlspecialchars($_POST["search_string"])."'",true);

			header("Location: adm_object.php?ref_id=".$this->rolf_ref_id.$obj_str."&cmd=searchUserForm");
			exit();		
		}

		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","adm_object.php?ref_id=".$this->rolf_ref_id.$obj_str."&cmd=searchUserForm");
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
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
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

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->rolf_ref_id.$obj_str."&cmd=assignSave&sort_by=name&sort_order=".$_GET["sort_order"]."&offset=".$_GET["offset"]);

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
							"ref_id"		=> $this->rolf_ref_id,
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
					//build link
					$link = "adm_object.php?ref_id=7&obj_id=".$ctrl["obj_id"];

					if ($key == "login")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);
						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

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
