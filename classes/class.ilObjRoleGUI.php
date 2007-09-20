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

require_once "class.ilObjectGUI.php";

/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilObjRoleGUI:
*
* @ingroup	ServicesAccessControl
*/
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


	var $ctrl;
 
	/**
	* Constructor
	* @access public
	*/
	function ilObjRoleGUI($a_data,$a_id,$a_call_by_reference = false,$a_prepare_output = true)
	{
		global $tree;
		
		//TODO: move this to class.ilias.php
		define("USER_FOLDER_ID",7);

		// copy ref_id for later use.
		if ($_GET['rolf_ref_id'] != "")
		{
			$this->rolf_ref_id = $_GET['rolf_ref_id'];
		}
		else
		{
			$this->rolf_ref_id = $_GET['ref_id'];
		}
		
		// Add ref_id of object that contains this role folder
		$this->obj_ref_id = $tree->getParentId($this->rolf_ref_id);

		$this->type = "role";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		$this->ctrl->saveParameter($this, array("obj_id", "rolf_ref_id"));
	}


	function &executeCommand()
	{
		global $rbacsystem;

		// todo: clean this mess up, but note that there are several
		// points where roles can be edited:
		// - repository categories, courses, groups, learning modules
		// glossaries (see object.xml)
		// - administration -> repository trash and permissions ->
		//   item ->edit role
		// - administration -> repository trash and permissions ->
		//   role folder -> role
		// - administration -> roles -> role
		if($this->ctrl->getTargetScript() == 'repository.php' ||
			$this->ctrl->getTargetScript() == 'role.php' ||
			$this->ctrl->getTargetScript() == 'fblm_edit.php' ||
			$this->ctrl->getTargetScript() == 'chat.php' ||
			strtolower($_GET["baseClass"]) == 'illmeditorgui' ||
			strtolower($_GET["baseClass"]) == 'ilexercisehandlergui' ||
			strtolower($_GET["baseClass"]) == 'illinkresourcehandlergui' ||
			strtolower($_GET["baseClass"]) == 'ilsahseditgui' ||
			strtolower($_GET["baseClass"]) == 'ilobjsurveygui' ||
			strtolower($_GET["baseClass"]) == 'ilmediapoolpresentation' ||
			strtolower($_GET["baseClass"]) == 'ilobjsurveyquestionpoolgui' ||
			strtolower($_GET["baseClass"]) == 'ilobjtestgui' ||
			strtolower($_GET["baseClass"]) == 'ilobjquestionpoolgui' ||
			strtolower($_GET["baseClass"]) == 'ilglossaryeditorgui' ||
			$_GET["admin_mode"] == "repository")
		{
			$this->__prepareOutput();
		}
		else
		{
			if ($_GET["ref_id"] != SYSTEM_FOLDER_ID)
			{
				$this->prepareOutput();
			}
			else
			{
				$this->setAdminTabs();
				//$this->addAdminLocatorItems();
				//$tpl->setLocator();
			}
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "perm";
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}

		return true;
	}
	
	/**
	* set back tab target
	*/
	function setBackTarget($a_text, $a_link)
	{
		$this->back_target = array("text" => $a_text,
			"link" => $a_link);
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}


	function listDesktopItemsObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		#if(!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if(!$rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id) &&
			$this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			ilUtil::sendInfo($this->lng->txt('role_no_users_no_desk_items'));
			return true;
		}


		include_once './classes/class.ilRoleDesktopItem.php';
		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		if($rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->__showButton('selectDesktopItem',$this->lng->txt('role_desk_add'));
		}
		if(!count($items = $role_desk_item_obj->getAll()))
		{
			ilUtil::sendInfo($this->lng->txt('role_desk_none_created'));
			return true;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_desktop_item_list.html");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_role.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_role'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('role_assigned_desk_items').' ('.$this->object->getTitle().')');
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));

		$counter = 0;

		foreach($items as $role_item_id => $item)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_id']);
			
			if(strlen($desc = $tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_DESK",$desc);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("desk_row");
			$this->tpl->setVariable("DESK_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_DESK",ilUtil::formCheckBox(0,'del_desk_item[]',$role_item_id));
			$this->tpl->setVariable("TXT_PATH",$this->lng->txt('path').':');
			$this->tpl->setVariable("PATH",$this->__formatPath($tree->getPathFull($item['item_id'])));
			$this->tpl->parseCurrentBlock();
		}

		return true;
	}

	function askDeleteDesktopItemObject()
	{
		global $rbacsystem;
		
		
		#if(!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['del_desk_item']))
		{
			ilUtil::sendInfo($this->lng->txt('role_select_one_item'));

			$this->listDesktopItemsObject();

			return true;
		}
		ilUtil::sendInfo($this->lng->txt('role_sure_delete_desk_items'));
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_ask_delete_desktop_item.html");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_role.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_role'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('role_assigned_desk_items').' ('.$this->object->getTitle().')');
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		include_once './classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		$counter = 0;

		foreach($_POST['del_desk_item'] as $role_item_id)
		{
			$item_data = $role_desk_item_obj->getItem($role_item_id);
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($item_data['item_id']);

			if(strlen($desc = $tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_DESK",$desc);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("desk_row");
			$this->tpl->setVariable("DESK_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}

		$_SESSION['role_del_desk_items'] = $_POST['del_desk_item'];

		return true;
	}

	function deleteDesktopItemsObject()
	{
		global $rbacsystem;
		
		#if (!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!count($_SESSION['role_del_desk_items']))
		{
			ilUtil::sendInfo($this->lng->txt('role_select_one_item'));

			$this->listDesktopItemsObject();

			return true;
		}

		include_once './classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		foreach ($_SESSION['role_del_desk_items'] as $role_item_id)
		{
			$role_desk_item_obj->delete($role_item_id);
		}

		ilUtil::sendInfo($this->lng->txt('role_deleted_desktop_items'));
		$this->listDesktopItemsObject();

		return true;
	}


	function selectDesktopItemObject()
	{
		global $rbacsystem,$tree;

		include_once './classes/class.ilRoleDesktopItemSelector.php';
		include_once './classes/class.ilRoleDesktopItem.php';

		if(!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			#$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendInfo($this->lng->txt('permission_denied'));
			$this->listDesktopItemsObject();
			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_desktop_item_selector.html");
		$this->__showButton('listDesktopItems',$this->lng->txt('back'));

		ilUtil::sendInfo($this->lng->txt("role_select_desktop_item"));
		
		$exp = new ilRoleDesktopItemSelector($this->ctrl->getLinkTarget($this,'selectDesktopItem'),
											 new ilRoleDesktopItem($this->object->getId()));
		$exp->setExpand($_GET["role_desk_item_link_expand"] ? $_GET["role_desk_item_link_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'selectDesktopItem'));
		
		$exp->setOutput(0);
		
		$this->tpl->setVariable("EXPLORER",$exp->getOutput());

		return true;
	}

	function assignDesktopItemObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			return false;
		}
	
		#if (!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			return false;
		}

		if (!isset($_GET['item_id']))
		{
			ilUtil::sendInfo($this->lng->txt('role_no_item_selected'));
			$this->selectDesktopItemObject();

			return false;
		}

		include_once './classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());
		$role_desk_item_obj->add((int) $_GET['item_id'],ilObject::_lookupType((int) $_GET['item_id'],true));

		ilUtil::sendInfo($this->lng->txt('role_assigned_desktop_item'));

		$this->ctrl->redirect($this,'listDesktopItems');
		return true;
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

		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$this->tpl->setCurrentBlock("allow_register");
			$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";
			$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
			$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("assign_users");
			$assign_users = $_SESSION["error_post_vars"]["Fobject"]["assign_users"] ? "checked=\"checked\"" : "";
			$this->tpl->setVariable("TXT_ASSIGN_USERS",$this->lng->txt("allow_assign_users"));
			$this->tpl->setVariable("ASSIGN_USERS",$assign_users);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("protect_permissions");
			$protect_permissions = $_SESSION["error_post_vars"]["Fobject"]["protect_permissions"] ? "checked=\"checked\"" : "";
			$this->tpl->setVariable("TXT_PROTECT_PERMISSIONS",$this->lng->txt("role_protect_permissions"));
			$this->tpl->setVariable("PROTECT_PERMISSIONS",$protect_permissions);
			$this->tpl->parseCurrentBlock();
		}

		// fill in saved values in case of error
		$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"]),true);
		$this->tpl->setVariable("DESC",ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]));

		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
		$this->ctrl->setParameter($this, "new_type", $this->type);
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
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

		// check if role title has il_ prefix
		if (substr($_POST["Fobject"]["title"],0,3) == "il_")
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_reserved_prefix"),$this->ilias->error_obj->MESSAGE);
		}

		// save
		include_once("class.ilObjRole.php");
		$roleObj = new ilObjRole();
		$roleObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$roleObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$roleObj->setAllowRegister($_POST["Fobject"]["allow_register"]);
		$roleObj->toggleAssignUsersStatus($_POST["Fobject"]["assign_users"]);
		$roleObj->create();
		$rbacadmin->assignRoleToFolder($roleObj->getId(), $this->rolf_ref_id,'y');
		$rbacadmin->setProtected($this->rolf_ref_id,$roleObj->getId(),ilUtil::tf2yn($_POST["Fobject"]["protect_permissions"]));	
		ilUtil::sendInfo($this->lng->txt("role_added"),true);

		$this->ctrl->returnToParent($this);
	}

	/**
	* display permission settings template
	*
	* @access	public
	*/
	function permObject()
	{
		global $rbacadmin, $rbacreview, $rbacsystem, $objDefinition, $tree;

		// for role administration check visible,write of global role folder
		/*
		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$access = $rbacsystem->checkAccess('visible,write',$this->rolf_ref_id);
		}
		else	// for local roles check 'edit permission' of parent object of the local role folder
		{
			$access = $rbacsystem->checkAccess('edit_permission',$tree->getParentId($this->rolf_ref_id));
		}
		*/		
		$access = $this->checkAccess('visible,write','edit_permission');
			
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}
		
		$perm_def = $this->object->__getPermissionDefinitions();

		$rbac_objects =& $perm_def[0];
		$rbac_operations =& $perm_def[1];

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
			// get allowed subobjects of object recursively
			$subobj_data = $this->objDefinition->getSubObjectsRecursively($parent_data["type"]);

			// remove not allowed object types from array but keep the type definition of object itself
			foreach ($rbac_objects as $key => $obj_data)
			{
				if ($obj_data["type"] == "rolf")
				{
					unset($rbac_objects[$key]);
					continue;
				}
				
				if (!$subobj_data[$obj_data["type"]] and $parent_data["type"] != $obj_data["type"])
				{
					unset($rbac_objects[$key]);
				}
			}
		} // end if local roles
		
		// now sort computed result
		//sort($rbac_objects);
			
		/*foreach ($rbac_objects as $key => $obj_data)
		{
			sort($rbac_objects[$key]["ops"]);
		}*/
		
		// sort by (translated) name of object type
		$rbac_objects = ilUtil::sortArray($rbac_objects,"name","asc");

		// BEGIN CHECK_PERM
		foreach ($rbac_objects as $key => $obj_data)
		{
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
					$disabled = false;
				}

				// Es wird eine 2-dim Post Variable �bergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"template_perm[".$obj_data["type"]."][]",$operation["ops_id"],$disabled);
				$output["perm"][$obj_data["obj_id"]][$operation["ops_id"]] = $box;
			}
		}
		// END CHECK_PERM

		$output["col_anz"] = count($rbac_objects);
		$output["txt_save"] = $this->lng->txt("save");
		$output["check_recursive"] = ilUtil::formCheckBox(0,"recursive",1);
		$output["text_recursive"] = $this->lng->txt("change_existing_objects");
		$output["text_recursive_desc"] = $this->lng->txt("change_existing_objects_desc");
		
		$protected_disabled = true;
		
		if ($this->rolf_ref_id == ROLE_FOLDER_ID or $rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id))
		{
			$protected_disabled = false;
		}
		
		$output["check_protected"] = ilUtil::formCheckBox($rbacreview->isProtected($this->rolf_ref_id,$this->object->getId()),
															"protected",
															1,
															$protected_disabled);
		
		$output["text_protected"] = $this->lng->txt("role_protect_permissions");
		$output["text_protected_desc"] = $this->lng->txt("role_protect_permissions_desc");


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

			// Sort roles by title
			$sorted_roles = ilUtil::sortArray(array_values($parent_role_ids), 'title', ASC);
			$key = 0;
			foreach ($sorted_roles as $par)
			{
				if ($par["obj_id"] != SYSTEM_ROLE_ID)
				{
					$radio = ilUtil::formRadioButton(0,"adopt",$par["obj_id"]);
					$output["adopt"][$key]["css_row_adopt"] = ($key % 2 == 0) ? "tblrow1" : "tblrow2";
					$output["adopt"][$key]["check_adopt"] = $radio;
					$output["adopt"][$key]["role_id"] = $par["obj_id"];
					$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
					$output["adopt"][$key]["role_name"] = $par["title"];
				}
				$key++;
			}

			$output["formaction_adopt"] = $this->ctrl->getFormAction($this);
			// END ADOPT_PERMISSIONS
		}

		$output["formaction"] = $this->ctrl->getFormAction($this);

		$this->data = $output;


/************************************/
/*			generate output			*/
/************************************/

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.adm_perm_role.html');

		foreach ($rbac_objects as $obj_data)
		{
			// BEGIN object_operations
			$this->tpl->setCurrentBlock("object_operations");

			$ops_ids = "";

			foreach ($obj_data["ops"] as $operation)
			{
				$ops_ids[] = $operation["ops_id"];
				
				//$css_row = ilUtil::switchColor($j++, "tblrow1", "tblrow2");
				$css_row = "tblrow1";
				$this->tpl->setVariable("CSS_ROW",$css_row);
				$this->tpl->setVariable("PERMISSION",$operation["name"]);
				if (substr($operation["title"], 0, 7) == "create_")
				{
					if ($this->objDefinition->getDevMode(substr($operation["title"], 7, strlen($operation["title"]) -7)))
					{
						$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_implemented_yet").")");
					}
				}
				$this->tpl->setVariable("CHECK_PERMISSION",$this->data["perm"][$obj_data["obj_id"]][$operation["ops_id"]]);
				$this->tpl->setVariable("LABEL_ID","template_perm_".$obj_data["type"]."_".$operation["ops_id"]);
				$this->tpl->parseCurrentBlock();
			} // END object_operations

			// BEGIN object_type
			$this->tpl->setCurrentBlock("object_type");
			$this->tpl->setVariable("TXT_OBJ_TYPE",$obj_data["name"]);

// TODO: move this if in a function and query all objects that may be disabled or inactive
			if ($this->objDefinition->getDevMode($obj_data["type"]))
			{
				$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_implemented_yet").")");
			}
			else if ($obj_data["type"] == "icrs" and !$this->ilias->getSetting("ilinc_active"))
			{
				$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_enabled_or_configured").")");
			}

			// option: change permissions of exisiting objects of that type
			$this->tpl->setVariable("OBJ_TYPE",$obj_data["type"]);
			$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE_DESC",$this->lng->txt("change_existing_object_type_desc"));

			// use different Text for system objects		
			if ($objDefinition->isSystemObject($obj_data["type"]))
			{
				$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE",$this->lng->txt("change_existing_prefix_single")." ".$this->lng->txt("obj_".$obj_data["type"])." ".$this->lng->txt("change_existing_suffix_single"));

			}
			else
			{
				$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE",$this->lng->txt("change_existing_prefix")." ".$this->lng->txt("objs_".$obj_data["type"])." ".$this->lng->txt("change_existing_suffix"));
			}

			// js checkbox toggles
			$this->tpl->setVariable("JS_VARNAME","template_perm_".$obj_data["type"]);
			$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($ops_ids));
			$this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));			
			
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
				$this->tpl->setVariable("LABEL_ID",$value["role_id"]);
				$this->tpl->setVariable("TYPE",$value["type"]);
				$this->tpl->setVariable("ROLE_NAME",$value["role_name"]);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("ADOPT_PERM_FORM");
			$this->tpl->setVariable("MESSAGE_MIDDLE",$this->data["message_middle"]);
			$this->tpl->setVariable("FORMACTION_ADOPT",$this->data["formaction_adopt"]);
			$this->tpl->setVariable("ADOPT",$this->lng->txt('copy'));
			$this->tpl->parseCurrentBlock();
			// END ADOPT PERMISSIONS
			
			$this->tpl->setCurrentBlock("tblfooter_special_options");
			$this->tpl->setVariable("TXT_PERM_SPECIAL_OPTIONS",$this->lng->txt("perm_special_options"));
			$this->tpl->parseCurrentBlock();
		
			$this->tpl->setCurrentBlock("tblfooter_recursive");
			$this->tpl->setVariable("COL_ANZ",3);
			$this->tpl->setVariable("CHECK_RECURSIVE",$this->data["check_recursive"]);
			$this->tpl->setVariable("TXT_RECURSIVE",$this->data["text_recursive"]);
			$this->tpl->setVariable("TXT_RECURSIVE_DESC",$this->data["text_recursive_desc"]);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("tblfooter_protected");
			$this->tpl->setVariable("COL_ANZ",3);
			$this->tpl->setVariable("CHECK_PROTECTED",$this->data["check_protected"]);
			$this->tpl->setVariable("TXT_PROTECTED",$this->data["text_protected"]);
			$this->tpl->setVariable("TXT_PROTECTED_DESC",$this->data["text_protected_desc"]);
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
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_".$this->object->getType().".gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));
		$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath("icon_help.gif"));
		$this->tpl->setVariable("TBL_HELP_LINK","tbl_help.php");
		$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->lng->txt("help"));
		
		// compute additional information in title
		$global_roles = $rbacreview->getGlobalRoles();
		
		if (in_array($this->object->getId(),$global_roles))
		{
			$desc = "global";
		}
		else
		{
			// description for autogenerated roles
			$rolf = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true);
			$parent_node = $this->tree->getParentNodeData($rolf[0]);

			$desc = $this->lng->txt("obj_".$parent_node['type'])." (#".$parent_node['obj_id'].") : ".$parent_node['title'];
		}
		
		$description = "&nbsp;<span class=\"small\">(".$desc.")</span>";

		// translation for autogenerated roles
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			$title = ilObjRole::_getTranslation($this->object->getTitle())." (".$this->object->getTitle().")";
		}
		else
		{
			$title = $this->object->getTitle();
		}

		$this->tpl->setVariable("TBL_TITLE",$title.$description);

		// info text
		$pid = $tree->getParentId($this->rolf_ref_id);
		$ptitle = ilObject::_lookupTitle(ilObject::_lookupObjId($pid));
		if ($this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			$info = sprintf($this->lng->txt("perm_role_info_1"),
				$this->object->getTitle(), $ptitle)." ".
				sprintf($this->lng->txt("perm_role_info_2"),
				$this->object->getTitle(), $ptitle);
		}
		else
		{
			$info = sprintf($this->lng->txt("perm_role_info_glob_1"),
				$this->object->getTitle(), $ptitle)." ".
				sprintf($this->lng->txt("perm_role_info_glob_2"),
				$this->object->getTitle(), $ptitle);
		}
		$this->tpl->setVariable("TXT_TITLE_INFO", $info);
		
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->parseCurrentBlock();
		
		//var_dump($this->data["formaction"]);
	}

	/**
	* save permissions
	* 
	* @access	public
	*/
	function permSaveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview, $objDefinition, $tree;

		// for role administration check write of global role folder
		/*
		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$access = $rbacsystem->checkAccess('write',$this->rolf_ref_id);
		}
		else	// for local roles check 'edit permission' of parent object of the local role folder
		{
			$access = $rbacsystem->checkAccess('edit_permission',$tree->getParentId($this->rolf_ref_id));
		}
		*/
		$access = $this->checkAccess('visible,write','edit_permission');
			
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

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
		if ($_POST["recursive"] or is_array($_POST["recursive_list"]))
		{
			// IF ROLE IS A GLOBAL ROLE START AT ROOT
			if ($this->rolf_ref_id == ROLE_FOLDER_ID)
			{
				$node_id = ROOT_FOLDER_ID;
			}
			else
			{
				$node_id = $this->tree->getParentId($this->rolf_ref_id);
			}

			// GET ALL SUBNODES
			$node_data = $this->tree->getNodeData($node_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			// GET ALL OBJECTS THAT CONTAIN A ROLE FOLDER
			$all_parent_obj_of_rolf = $rbacreview->getObjectsWithStopedInheritance($this->object->getId());

			// DELETE ACTUAL ROLE FOLDER FROM ARRAY
			if ($this->rolf_ref_id == ROLE_FOLDER_ID)
			{
				$key = array_keys($all_parent_obj_of_rolf,SYSTEM_FOLDER_ID);
			}
			else
			{
				$key = array_keys($all_parent_obj_of_rolf,$node_id);
			}

			unset($all_parent_obj_of_rolf[$key[0]]);

			$check = false;

			foreach ($subtree_nodes as $node)
			{
				if (!$check)
				{
					if (in_array($node["child"],$all_parent_obj_of_rolf))
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
						
						if (in_array($node["child"],$all_parent_obj_of_rolf))
						{
							$lft = $node["lft"];
							$rgt = $node["rgt"];
							$check = true;
							continue;
						}
						
						$valid_nodes[] = $node;
					}
				}
			}

			// Prepare arrays for permission settings below	
			foreach ($valid_nodes as $key => $node)
			{
				// To change only selected object types filter selected object types
				if (is_array($_POST["recursive_list"]) and !in_array($node["type"],$_POST["recursive_list"]))
				{
					unset($valid_nodes[$key]);
					continue;
				}

				$node_ids[] = $node["child"];
				$valid_nodes[$key]["perms"] = $_POST["template_perm"][$node["type"]];
			}
			
			// prepare arrays for permission settings below
			/*foreach ($valid_nodes as $key => $node)
			{
				#if(!in_array($node["type"],$to_filter))
				{
					$node_ids[] = $node["child"];
					$valid_nodes[$key]["perms"] = $_POST["template_perm"][$node["type"]];
				}
			}*/
			
			if (!empty($node_ids))
			{
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
			}
		}// END IF RECURSIVE
		
		// set protected flag
		if ($this->rolf_ref_id == ROLE_FOLDER_ID or $rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id))
		{
			$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST['protected']));
		}

		ilUtil::sendInfo($this->lng->txt("saved_successfully"),true); 
		$this->ctrl->redirect($this, "perm");
	}


	/**
	* copy permissions from role
	* 
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview, $tree;

		if(!$_POST['adopt'])
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->permObject();
			return false;
		}
		
		// for role administration check write of global role folder
		/*
		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$access = $rbacsystem->checkAccess('write',$this->rolf_ref_id);
		}
		else	// for local roles check 'edit permission' of parent object of the local role folder
		{
			$access = $rbacsystem->checkAccess('edit_permission',$tree->getParentId($this->rolf_ref_id));
		}
		*/	
	
		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		if ($this->object->getId() == $_POST["adopt"])
		{
			ilUtil::sendInfo($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->object->getId(), $this->rolf_ref_id);
			$parentRoles = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);
			$rbacadmin->copyRoleTemplatePermissions($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $this->rolf_ref_id,$this->object->getId());		

			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			ilUtil::sendInfo($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".
					 $this->lng->txt("msg_perm_adopted_from2"),true);
		}

		$this->ctrl->redirect($this, "perm");
	}

	/**
	* wrapper for renamed function
	*
	* @access	public
	*/
	function assignSaveObject()
	{
        $this->assignUserObject();
    }

	/**
	* assign users to role
	*
	* @access	public
	*/
	function assignUserObject()
	{
    	global $rbacsystem, $rbacadmin, $rbacreview;

		#if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id) &&
			$this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			$this->ilias->raiseError($this->lng->txt("err_role_not_assignable"),$this->ilias->error_obj->MESSAGE);
		}

		if (!isset($_POST["user"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"));
			$this->searchObject();

			return false;
		}
		
		$selected_users = $_POST["user"];
		$assigned_users_all = $rbacreview->assignedUsers($this->object->getId());
				
		// users to assign
		$assigned_users_new = array_diff($selected_users,array_intersect($selected_users,$assigned_users_all));
		
		// selected users all already assigned. stop
        if (count($assigned_users_new) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("msg_selected_users_already_assigned"));
			$this->searchObject();
			
			return false;
		}

		// assign new users
        foreach ($assigned_users_new as $user)
		{
			$rbacadmin->assignUser($this->object->getId(),$user,false);
        }
        
    	// update object data entry (to update last modification date)
		$this->object->update();

		ilUtil::sendInfo($this->lng->txt("msg_userassignment_changed"),true);
		
		$this->ctrl->redirect($this,'userassignment');
	}
	
	/**
	* de-assign users from role
	*
	* @access	public
	*/
	function deassignUserObject()
	{
    	global $rbacsystem, $rbacadmin, $rbacreview;

		#if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		/*
		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}
		*/
    	$selected_users = ($_POST["user_id"]) ? $_POST["user_id"] : array($_GET["user_id"]);

		if ($selected_users[0]=== NULL)
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// prevent unassignment of system user from system role
		if ($this->object->getId() == SYSTEM_ROLE_ID)
		{
            if ($admin = array_search(SYSTEM_USER_ID,$selected_users) !== false)
			    unset($selected_users[$admin]);
		}

		// check for each user if the current role is his last global role before deassigning him
		$last_role = array();
		$global_roles = $rbacreview->getGlobalRoles();
		
		foreach ($selected_users as $user)
		{
			$assigned_roles = $rbacreview->assignedRoles($user);
			$assigned_global_roles = array_intersect($assigned_roles,$global_roles);

			if (count($assigned_roles) == 1 or (count($assigned_global_roles) == 1 and in_array($this->object->getId(),$assigned_global_roles)))
			{
				$userObj = $this->ilias->obj_factory->getInstanceByObjId($user);
				$last_role[$user] = $userObj->getFullName();
				unset($userObj);
			}
		}

		// raise error if last role was taken from a user...
		if (count($last_role) > 0)
		{
			$user_list = implode(", ",$last_role);
			$this->ilias->raiseError($this->lng->txt("msg_is_last_role").": ".$user_list."<br/>".$this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}
		
		// ... else perform deassignment
		foreach ($selected_users as $user)
        {
			$rbacadmin->deassignUser($this->object->getId(),$user);
		}

    	// update object data entry (to update last modification date)
		$this->object->update();

		ilUtil::sendInfo($this->lng->txt("msg_userassignment_changed"),true);

		$this->ctrl->redirect($this,'userassignment');
	}
	
	/**
	* update role object
	* 
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree;

		// for role administration check write of global role folder
		/*
		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$access = $rbacsystem->checkAccess('write',$this->rolf_ref_id);
		}
		else	// for local roles check 'edit permission' of parent object of the local role folder
		{
			$access = $rbacsystem->checkAccess('edit_permission',$tree->getParentId($this->rolf_ref_id));
		}
		*/
		$access = $this->checkAccess('write','edit_permission');	
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_role"),$this->ilias->error_obj->MESSAGE);
		}

		if (substr($this->object->getTitle(),0,3) != "il_")
		{
			// check required fields
			if (empty($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
			}
	
			// check if role title has il_ prefix
			if (substr($_POST["Fobject"]["title"],0,3) == "il_")
			{
				$this->ilias->raiseError($this->lng->txt("msg_role_reserved_prefix"),$this->ilias->error_obj->MESSAGE);
			}
	
			// update
			$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		}


		
		// ensure that at least one role is available in the new user register form if registration is enabled
		if ($_POST["Fobject"]["allow_register"] == "")
		{
			$roles_allowed = $this->object->_lookupRegisterAllowed();

			if (count($roles_allowed) == 1 and $roles_allowed[0]['id'] == $this->object->getId())
			{
				$this->ilias->raiseError($this->lng->txt("msg_last_role_for_registration"),$this->ilias->error_obj->MESSAGE);
			}	
		}

		$this->object->setAllowRegister($_POST["Fobject"]["allow_register"]);
		$this->object->toggleAssignUsersStatus($_POST["Fobject"]["assign_users"]);
		$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST["Fobject"]["protect_permissions"]));	
		$this->object->update();
		
		ilUtil::sendInfo($this->lng->txt("saved_successfully"),true);

		$this->ctrl->redirect($this,'edit');
	}
	
	/**
	* edit object
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $rbacreview;

		#if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		if(!$this->checkAccess('write','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->getTemplateFile("edit");

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			if (substr($this->object->getTitle(false),0,3) != "il_")
			{
				$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"]),true);
				$this->tpl->setVariable("DESC",ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]));
			}
		
			$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";
			$assign_users = ($_SESSION["error_post_vars"]["Fobject"]["assign_users"]) ? "checked=\"checked\"" : "";
			$protect_permissions = ($_SESSION["error_post_vars"]["Fobject"]["protect_permissions"]) ? "checked=\"checked\"" : "";
		}
		else
		{
			if (substr($this->object->getTitle(),0,3) != "il_")
			{
				$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($this->object->getTitle()));
				$this->tpl->setVariable("DESC",ilUtil::stripSlashes($this->object->getDescription()));
			}

			$allow_register = ($this->object->getAllowRegister()) ? "checked=\"checked\"" : "";
			$assign_users = $this->object->getAssignUsersStatus() ? "checked=\"checked\"" : "";
			$protect_permissions = $rbacreview->isProtected($this->rolf_ref_id,$this->object->getId()) ? "checked=\"checked\"" : "";

		}

		$obj_str = "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
		
		// exclude allow register option for anonymous role, system role and all local roles
		$global_roles = $rbacreview->getGlobalRoles();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			$this->tpl->setVariable("SHOW_TITLE",ilObjRole::_getTranslation($this->object->getTitle())." (".$this->object->getTitle().")");
			
			$rolf = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true);
			$parent_node = $this->tree->getParentNodeData($rolf[0]);

			$this->tpl->setVariable("SHOW_DESC",$this->lng->txt("obj_".$parent_node['type'])." (".$parent_node['obj_id'].") <br/>".$parent_node['title']);

			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("back"));
			$this->tpl->setVariable("CMD_SUBMIT", "cancel");
		}

		if ($this->object->getId() != ANONYMOUS_ROLE_ID and 
			$this->object->getId() != SYSTEM_ROLE_ID and 
			in_array($this->object->getId(),$global_roles))
		{
			$this->tpl->setCurrentBlock("allow_register");
			$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
			$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("assign_users");
			$this->tpl->setVariable("TXT_ASSIGN_USERS",$this->lng->txt('allow_assign_users'));
			$this->tpl->setVariable("ASSIGN_USERS",$assign_users);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("protect_permissions");
			$this->tpl->setVariable("TXT_PROTECT_PERMISSIONS",$this->lng->txt('role_protect_permissions'));
			$this->tpl->setVariable("PROTECT_PERMISSIONS",$protect_permissions);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* display user assignment panel
	*/
	function userassignmentObject()
	{
		global $rbacreview, $rbacsystem;
		
		//if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}
		$assigned_users = $rbacreview->assignedUsers($this->object->getId(),array("login","firstname","lastname","usr_id"));

		//if current user is admin he is able to add new members to group
		$val_contact = $this->lng->txt("message");
		$val_change = $this->lng->txt("edit");
		$val_leave = $this->lng->txt("remove");
		$val_contact_desc = $this->lng->txt("role_user_send_mail");
		$val_change_desc = $this->lng->txt("role_user_edit");
		$val_leave_desc = $this->lng->txt("role_user_deassign");
		$counter = 0;

		foreach ($assigned_users as $user)
		{
			$link_contact = "ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$user["login"];
			
			if ($_GET["admin_mode"] == "settings"
				&& $_GET["ref_id"] != SYSTEM_FOLDER_ID)
			{
				$link_change = $this->ctrl->getLinkTargetByClass("ilobjusergui", "edit");
				$link_change = "ilias.php?ref_id=7&admin_mode=settings&obj_id=".$user["usr_id"]."&cmd=edit&cmdClass=ilobjusergui&cmdNode=1396&baseClass=ilAdministrationGUI";
			}

			$this->ctrl->setParameter($this, "user_id", $user["usr_id"]);
			$link_leave = $this->ctrl->getLinkTarget($this,"deassignUser");

            $member_functions = "";

            // exclude root/admin role and anon/anon
            if ($this->object->getId() != ANONYMOUS_ROLE_ID or $user["usr_id"] != ANONYMOUS_USER_ID)
			{
                //build function
                $member_functions = "<a class=\"il_ContainerItemCommand\" href=\"".$link_contact."\" title=\"".$val_contact_desc."\">".$val_contact."</a>";

				if (strtolower($_GET["baseClass"]) == 'iladministrationgui' && $_GET["admin_mode"] == "settings")
				{
					$member_functions .= "&nbsp;<a class=\"il_ContainerItemCommand\" href=\"".$link_change."\" title=\"".$val_change_desc."\">".$val_change."</a>";
				}

                if ($this->object->getId() != SYSTEM_ROLE_ID or $user["usr_id"] != SYSTEM_USER_ID)
                {
                    $member_functions .= "&nbsp;<a class=\"il_ContainerItemCommand\" href=\"".$link_leave."\" title=\"".$val_leave_desc."\">".$val_leave."</a>";
                }
            }

			// no check box for root/admin role and anon/anon
			if (($this->object->getId() == SYSTEM_ROLE_ID and $user["usr_id"] == SYSTEM_USER_ID)
                or ($this->object->getId() == ANONYMOUS_ROLE_ID and $user["usr_id"] == ANONYMOUS_USER_ID))
			{
                $result_set[$counter][] = "";
            }
            else
            {
                $result_set[$counter][] = ilUtil::formCheckBox(0,"user_id[]",$user["usr_id"]);
            }

            $user_ids[$counter] = $user["usr_id"];

            $result_set[$counter][] = $user["login"];
			$result_set[$counter][] = $user["firstname"];
			$result_set[$counter][] = $user["lastname"];
			$result_set[$counter][] = $member_functions;

			++$counter;

			unset($member_functions);
		}

		return $this->__showAssignedUsersTable($result_set,$user_ids);
    }
	
	function __showAssignedUsersTable($a_result_set,$a_user_ids = NULL)
	{
        global $rbacsystem;

		$actions = array("deassignUser"  => $this->lng->txt("remove"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		
		$this->__showButton('mailToRole',$this->lng->txt('role_mailto'),'target=\'_blank\'');

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

        $tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","searchUserForm");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("role_add_user"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		foreach ($actions as $name => $value)
		{
			$tpl->setCurrentBlock("tbl_action_btn");
			$tpl->setVariable("BTN_NAME",$name);
			$tpl->setVariable("BTN_VALUE",$value);
			$tpl->parseCurrentBlock();
		}
			
		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user_id");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

        $tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->ctrl->setParameter($this,"cmd","userassignment");

		// title & header columns
		$tbl->setTitle($this->lng->txt("assigned_users"),"icon_usr.gif",$this->lng->txt("users"));

		//user must be administrator
		$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("firstname"),
			$this->lng->txt("lastname"),$this->lng->txt("grp_options")));
		$tbl->setHeaderVars(array("","login","firstname","lastname","functions"),
			$this->ctrl->getParameterArray($this,"",false));
		$tbl->setColumnWidth(array("","20%","25%","25%","30%"));
		
		$this->__setTableGUIBasicData($tbl,$a_result_set,"userassignment");
		$tbl->render();
		$this->tpl->setVariable("ADM_CONTENT",$tbl->tpl->get());

		return true;
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "group":
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				break;

			case "role":
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				break;

			default:
				// init sort_by (unfortunatly sort_by is preset with 'title')
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function searchUserFormObject()
	{
		global $rbacsystem;

		//if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule('search');

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.role_users_search.html");

		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("role_search_users"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["role_search_str"] ? $_SESSION["role_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("exc_roles"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));

        $usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($usr,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($role,"search_for","role"));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($grp,"search_for","grp"));

		$this->__unsetSessionVariables();
	}

	function __unsetSessionVariables()
	{
		unset($_SESSION["role_delete_member_ids"]);
		unset($_SESSION["role_delete_subscriber_ids"]);
		unset($_SESSION["role_search_str"]);
		unset($_SESSION["role_search_for"]);
		unset($_SESSION["role_role"]);
		unset($_SESSION["role_group"]);
		unset($_SESSION["role_archives"]);
	}

	/**
	* cancelObject is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		
		if ($_GET["new_type"] != "role")
		{
			$this->ctrl->redirect($this, "userassignment");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjrolefoldergui","view");
		}
	}

	function searchObject()
	{
		global $rbacsystem, $tree;

		#if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["role_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["role_search_str"];
		$_SESSION["role_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["role_search_for"];

		if (!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			ilUtil::sendInfo($this->lng->txt("role_search_enter_search_string"));
			$this->searchUserFormObject();

			return false;
		}

		if (!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"])))
		{
			ilUtil::sendInfo($this->lng->txt("role_no_results_found"));
			$this->searchUserFormObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		$counter = 0;
		$f_result = array();

		switch($_POST["search_for"])
		{
        	case "usr":
				foreach($result as $user)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
					{
						continue;
					}
					
					$user_ids[$counter] = $user["id"];
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$user_ids);

				return true;

			case "role":
				foreach($result as $role)
				{
                    // exclude anonymous role
                    if ($role["id"] == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }

                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role["id"],false))
					{
						continue;
					}

				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }

					$role_ids[$counter] = $role["id"];

					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}

				$this->__showSearchRoleTable($f_result,$role_ids);

				return true;

			case "grp":
				foreach($result as $group)
				{
					if(!$tree->isInTree($group["id"]))
					{
						continue;
					}

					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"],false))
					{
						continue;
					}

                    // exclude myself :-)
                    if ($tmp_obj->getId() == $this->object->getId())
                    {
                        continue;
                    }

					$grp_ids[$counter] = $group["id"];

					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchGroupTable($f_result,$grp_ids);

				return true;
		}
	}

	function __search($a_search_string,$a_search_for)
	{
		include_once("class.ilSearch.php");

		$this->lng->loadLanguageModule("content");
		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		if ($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUserForm");
		}

		return $search->getResultByType($a_search_for);
	}

	function __showSearchUserTable($a_result_set,$a_user_ids = NULL,$a_cmd = "search")
	{
        $return_to  = "searchUserForm";

    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",$return_to);
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","assignUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		if (!empty($a_user_ids))
		{		
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("role_header_edit_users"),"icon_usr.gif",$this->lng->txt("role_header_edit_users"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"),
							$this->ctrl->getParameterArray($this,$a_cmd,false));
			//array("ref_id" => $this->rolf_ref_id,
			//  "obj_id" => $this->object->getId(),
			// "cmd" => $a_cmd,
			//"cmdClass" => "ilobjrolegui",
			// "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchRoleTable($a_result_set,$a_role_ids = NULL)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("role_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","role");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("role_header_edit_users"),"icon_usr.gif",$this->lng->txt("role_header_edit_users"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_role"),
								   $this->lng->txt("role_count_users")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							$this->ctrl->getParameterArray($this,"search",false));
			//array("ref_id" => $this->rolf_ref_id,
			//"obj_id" => $this->object->getId(),
			//"cmd" => "search",
			//"cmdClass" => "ilobjrolegui",
			//"cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
    	$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","group");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->rolf_ref_id,
                                  "obj_id" => $this->object->getId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjrolegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview;

		$_SESSION["role_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["role_role"];

		if (!is_array($_POST["role"]))
		{
			ilUtil::sendInfo($this->lng->txt("role_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		// GET ALL MEMBERS
		$members = array();

		foreach ($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();

		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
			
			// TODO: exclude anonymous user
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}

		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}

	function listUsersGroupObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["role_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["role_group"];

		if (!is_array($_POST["group"]))
		{
			ilUtil::sendInfo($this->lng->txt("role_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		// GET ALL MEMBERS
		$members = array();

		foreach ($_POST["group"] as $group_id)
		{
			if (!$tree->isInTree($group_id))
			{
				continue;
			}
			if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}

			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();

		foreach($members as $user)
		{
			if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;			

			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}

		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
	}


	function __formatPath($a_path_arr)
	{
		$counter = 0;

		foreach ($a_path_arr as $data)
		{
			if ($counter++)
			{
				$path .= " -> ";
			}

			$path .= $data['title'];
		}

		if (strlen($path) > 50)
		{
			return '...'.substr($path,-50);
		}

		return $path;
	}

	function __prepareOutput()
	{
		// output objects
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.role.html");
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		//$this->__setLocator();

		// output message
		if ($this->message)
		{
			ilUtil::sendInfo($this->message);
		}

		// display infopanel if something happened
		ilUtil::infoPanel();

		// set header
		$this->__setHeader();
	}

	function __setHeader()
	{
		include_once './classes/class.ilTabsGUI.php';

		$this->tpl->setTitle($this->lng->txt('role'));
		$this->tpl->setDescription($this->object->getTitle());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role.gif"));

		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($this->tabs_gui);

		// output tabs
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}

	function __setLocator()
	{
		global $tree, $ilias_locator;
		
		return;
		
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$counter = 0;

		foreach ($tree->getPathFull($this->rolf_ref_id) as $key => $row)
		{
			if ($counter++)
			{
				$this->tpl->touchBlock('locator_separator_prefix');
			}

			$this->tpl->setCurrentBlock("locator_item");

			if ($row["type"] == 'rolf')
			{
				$this->tpl->setVariable("ITEM",$this->object->getTitle());
				$this->tpl->setVariable("LINK_ITEM",$this->ctrl->getLinkTarget($this));
			}
			elseif ($row["child"] != $tree->getRootId())
			{
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM","repository.php?ref_id=".$row["child"]);
			}
			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				$this->tpl->setVariable("LINK_ITEM","repository.php?ref_id=".$row["child"]);
			}

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;

		if ($_GET["admin_mode"] == "settings"
			&& $_GET["ref_id"] != SYSTEM_FOLDER_ID)	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));

			$ilLocator->addItem($this->lng->txt("obj_".ilObject::_lookupType(
				ilObject::_lookupObjId($_GET["ref_id"]))),
				$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
			
			if ($_GET["obj_id"] > 0)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "view"));
			}
		}
		else							// repository administration
		{
			// ?
		}
	}
	
	function showUpperIcon()
	{
		global $tree, $tpl, $objDefinition;
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			if ($_GET["admin_mode"] == "settings"
				&& $_GET["ref_id"] != SYSTEM_FOLDER_ID)
			{
				$tpl->setUpperIcon(
					$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
			}
		}
		else
		{		
			if ($this->object->getRefId() != ROOT_FOLDER_ID &&
				$this->object->getRefId() != SYSTEM_FOLDER_ID)
			{
				$par_id = $tree->getParentId($this->object->getRefId());
				$tpl->setUpperIcon("repository.php?ref_id=".$par_id);
			}
		}
	}



	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		$base_role_folder = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true);
		
//var_dump($base_role_folder);
//echo "-".$this->rolf_ref_id."-";

		$activate_role_edit = false;
		
		// todo: activate the following (allow editing of local roles in
		// roles administration)
		//if (in_array($this->rolf_ref_id,$base_role_folder))
		if (in_array($this->rolf_ref_id,$base_role_folder) ||
			(strtolower($_GET["baseClass"]) == "iladministrationgui" &&
			$_GET["admin_mode"] == "settings"))
		{
			$activate_role_edit = true;
		}

		// not so nice (workaround for using tabs in repository)
		$tabs_gui->clearTargets();

		if ($this->back_target != "")
		{
			$tabs_gui->setBackTarget(
				$this->back_target["text"],$this->back_target["link"]);
		}

		#if ($rbacsystem->checkAccess('write',$this->rolf_ref_id) && $activate_role_edit)
		if($this->checkAccess('write','edit_permission') && $activate_role_edit)
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit","update"), get_class($this));
		}

		#if ($rbacsystem->checkAccess('write',$this->rolf_ref_id))
		if($this->checkAccess('write','edit_permission'))
		{
			$force_active = ($_GET["cmd"] == "perm" || $_GET["cmd"] == "")
				? true
				: false;
			$tabs_gui->addTarget("default_perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), array("perm", "adoptPermSave", "permSave"),
				get_class($this),
				"", $force_active);
		}

		#if ($rbacsystem->checkAccess('write',$this->rolf_ref_id) && $activate_role_edit)
		if($this->checkAccess('write','edit_permission') && $activate_role_edit)
		{
			$tabs_gui->addTarget("user_assignment",
				$this->ctrl->getLinkTarget($this, "userassignment"),
				array("deassignUser", "userassignment", "assignUser", "searchUserForm", "search"),
				get_class($this));
		}

		#if ($rbacsystem->checkAccess('write',$this->rolf_ref_id) && $activate_role_edit)
		if($this->checkAccess('write','edit_permission') && $activate_role_edit)
		{
			$tabs_gui->addTarget("desktop_items",
				$this->ctrl->getLinkTarget($this, "listDesktopItems"),
				array("listDesktopItems", "deleteDesktopItems", "selectDesktopItem", "askDeleteDesktopItem"),
				get_class($this));
		}
	}
	
	function mailToRoleObject()
	{
		global $rbacreview;
		$_SESSION['mail_roles'][] = $rbacreview->getRoleMailboxAddress($this->object->getId());
		$script = 'ilias.php?baseClass=ilMailGUI&type=role';
		ilUtil::redirect($script);
	}
	
	function checkAccess($a_perm_global,$a_perm_obj = '')
	{
		global $rbacsystem,$ilAccess;
		
		$a_perm_obj = $a_perm_obj ? $a_perm_obj : $a_perm_global;
		
		if($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			return $rbacsystem->checkAccess($a_perm_global,$this->rolf_ref_id);
		}
		else
		{
			return $ilAccess->checkAccess($a_perm_obj,'',$this->obj_ref_id);
		}
	}
	
} // END class.ilObjRoleGUI
?>
