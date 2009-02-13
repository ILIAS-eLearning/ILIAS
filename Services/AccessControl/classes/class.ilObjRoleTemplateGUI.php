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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjRoleTemplateGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjRoleTemplateGUI:
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleTemplateGUI extends ilObjectGUI
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
	*
	* @access	public
	*/
	function ilObjRoleTemplateGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $lng;
		
		$lng->loadLanguageModule('rbac');
		
		$this->type = "rolt";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		$this->rolf_ref_id =& $this->ref_id;
		$this->ctrl->saveParameter($this, "obj_id");
	}
	
	function &executeCommand()
	{
		global $rbacsystem;

		if($this->ctrl->getTargetScript() == 'role.php')
		{
			$this->__prepareOutput();
		}
		else
		{
			$this->prepareOutput();
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
	* create new role definition template
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("create_rolt", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);

			$this->getTemplateFile("edit","role");

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);

				if ($this->prepare_output)
				{
					$this->tpl->parseCurrentBlock();
				}
			}
			
			$this->tpl->setCurrentBlock("protect_permissions");
			$protect_permissions = $_SESSION["error_post_vars"]["Fobject"]["protect_permissions"] ? "checked=\"checked\"" : "";
			$this->tpl->setVariable("TXT_PROTECT_PERMISSIONS",$this->lng->txt("role_protect_permissions"));
			$this->tpl->setVariable("PROTECT_PERMISSIONS",$protect_permissions);
			$this->tpl->parseCurrentBlock();

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
	}


	/**
	* save a new role template object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem,$rbacadmin, $rbacreview;

		// CHECK ACCESS 'write' to role folder
		// TODO: check for create role permission should be better
		if (!$rbacsystem->checkAccess("create_rolt",$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolt"),$this->ilias->error_obj->WARNING);
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

		// create new rolt object
		include_once("./Services/AccessControl/classes/class.ilObjRoleTemplate.php");
		$roltObj = new ilObjRoleTemplate();
		$roltObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$roltObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$roltObj->create();
		$rbacadmin->assignRoleToFolder($roltObj->getId(), $this->rolf_ref_id,'n');
		$rbacadmin->setProtected($this->rolf_ref_id,$roltObj->getId(),ilUtil::tf2yn($_POST["Fobject"]["protect_permissions"]));	
		
		ilUtil::sendInfo($this->lng->txt("rolt_added"),true);
		$this->ctrl->returnToParent($this);
	}

	/**
	* display permissions
	* 
	* @access	public
	*/
	function permObject()
	{
		global $rbacadmin, $rbacreview, $rbacsystem,$objDefinition;

		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
			exit();
		}

		$to_filter = $objDefinition->getSubobjectsToFilter();
		
		$tpl_filter = array();
		$internal_tpl = false;

		if (($internal_tpl = $this->object->isInternalTemplate()))
		{
			$tpl_filter = $this->object->getFilterOfInternalTemplate();
		}

		$operation_info = $rbacreview->getOperationAssignment();
		foreach($operation_info as $info)
		{
			if($objDefinition->getDevMode($info['type']))
			{
				continue;
			}
			// FILTER SUBOJECTS OF adm OBJECT
			if(in_array($info['type'],$to_filter))
			{
				continue;
			}
			if ($internal_tpl and !in_array($info['type'],$tpl_filter))
			{
				continue;
			}
			$rbac_objects[$info['typ_id']] = array("obj_id"	=> $info['typ_id'],
											    "type"		=> $info['type']);
			$rbac_operations[$info['typ_id']][$info['ops_id']] = array(
									   							"ops_id"	=> $info['ops_id'],
									  							"title"		=> $info['operation'],
																"name"		=> $this->lng->txt($info['type']."_".$info['operation']));		
		}
		

		foreach ($rbac_objects as $key => $obj_data)
		{
			$rbac_objects[$key]["name"] = $this->lng->txt("obj_".$obj_data["type"]);
			$rbac_objects[$key]["ops"] = $rbac_operations[$key];
		}

		sort($rbac_objects);
			
		foreach ($rbac_objects as $key => $obj_data)
		{
			sort($rbac_objects[$key]["ops"]);
		}
		
		// sort by (translated) name of object type
		$rbac_objects = ilUtil::sortArray($rbac_objects,"name","asc");

		// BEGIN CHECK_PERM
		foreach ($rbac_objects as $key => $obj_data)
		{
			$arr_selected = $rbacreview->getOperationsOfRole($this->object->getId(), $obj_data["type"], $this->rolf_ref_id);
			$arr_checked = array_intersect($arr_selected,array_keys($rbac_operations[$obj_data["obj_id"]]));

			foreach ($rbac_operations[$obj_data["obj_id"]] as $operation)
			{
				$checked = in_array($operation["ops_id"],$arr_checked);
				$disabled = false;

				// Es wird eine 2-dim Post Variable �bergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"template_perm[".$obj_data["type"]."][]",$operation["ops_id"],$disabled);
				$output["perm"][$obj_data["obj_id"]][$operation["ops_id"]] = $box;
			}
		}
		// END CHECK_PERM

		$output["col_anz"] = count($rbac_objects);
		$output["txt_save"] = $this->lng->txt("save");
		$output["check_protected"] = ilUtil::formCheckBox($rbacreview->isProtected($this->rolf_ref_id,$this->object->getId()),"protected",1);
		$output["text_protected"] = $this->lng->txt("role_protect_permissions");

/************************************/
/*		adopt permissions form		*/
/************************************/

		$output["message_middle"] = $this->lng->txt("adopt_perm_from_template");

		// send message for system role
		if ($this->object->getId() == SYSTEM_ROLE_ID)
		{
			$output["adopt"] = array();
			ilUtil::sendInfo($this->lng->txt("msg_sysrole_not_editable"));
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

			$output["formaction_adopt"] = $this->ctrl->getFormAction($this);
			// END ADOPT_PERMISSIONS
		}

		$output["formaction"] =
			$this->ctrl->getFormAction($this);

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
				$ops_ids[] = $operation["ops_id"];
				
				$css_row = ilUtil::switchColor($key, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW",$css_row);
				$this->tpl->setVariable("PERMISSION",$operation["name"]);
				$this->tpl->setVariable("CHECK_PERMISSION",$this->data["perm"][$obj_data["obj_id"]][$operation["ops_id"]]);
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
			
			// js checkbox toggles
			$this->tpl->setVariable("JS_VARNAME","template_perm_".$obj_data["type"]);
			$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($ops_ids));
			$this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));	


			$this->tpl->parseCurrentBlock();
			// END object_type
		}

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
		$this->tpl->setVariable("ADOPT",$this->lng->txt('copy'));
		$this->tpl->parseCurrentBlock();
		// END ADOPT PERMISSIONS
		
		$this->tpl->setCurrentBlock("tblfooter_protected");
		$this->tpl->setVariable("COL_ANZ",3);
		$this->tpl->setVariable("CHECK_BOTTOM",$this->data["check_protected"]);
		$this->tpl->setVariable("MESSAGE_TABLE",$this->data["text_protected"]);
		$this->tpl->parseCurrentBlock();
	
		$this->tpl->setCurrentBlock("tblfooter_standard");
		$this->tpl->setVariable("COL_ANZ_PLUS",4);
		$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
		$this->tpl->parseCurrentBlock();

		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_".$this->object->getType().".gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));
		$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath("icon_help.gif"));
		$this->tpl->setVariable("TBL_HELP_LINK","tbl_help.php");
		$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->lng->txt("help"));

		// compute additional information in title
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			$desc = $this->lng->txt("predefined_template");//$this->lng->txt("obj_".$parent_node['type'])." (".$parent_node['obj_id'].") : ".$parent_node['title'];
		}
		
		$description = "<br/>&nbsp;<span class=\"small\">".$desc."</span>";

		// translation for autogenerated roles
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			include_once('./Services/AccessControl/classes/class.ilObjRole.php');

			$title = ilObjRole::_getTranslation($this->object->getTitle())." (".$this->object->getTitle().")";
		}
		else
		{
			$title = $this->object->getTitle();
		}

		$this->tpl->setVariable("TBL_TITLE",$title.$description);
			
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->parseCurrentBlock();
	}


	/**
	* save permission templates of role
	*
	* @access	public
	*/
	function permSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview,$objDefinition;

		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		else
		{
			// Alle Template Eintraege loeschen
			$rbacadmin->deleteRolePermission($this->object->getId(), $this->rolf_ref_id);

			foreach ($_POST["template_perm"] as $key => $ops_array)
			{
				// Setzen der neuen template permissions
				$rbacadmin->setRolePermission($this->object->getId(), $key,$ops_array,$this->rolf_ref_id);
			}
		}
		
		// update object data entry (to update last modification date)
		$this->object->update();
		
		// set protected flag
		$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST['protected']));

		ilUtil::sendInfo($this->lng->txt("saved_successfully"),true);

		$this->ctrl->redirect($this, "perm");
	}

	/**
	* adopting permission setting from other roles/role templates
	*
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview;

		if (!$rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->WARNING);
		}
		elseif ($this->obj_id == $_POST["adopt"])
		{
			ilUtil::sendInfo($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->obj_id, $this->rolf_ref_id);
			$parentRoles = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);
			$rbacadmin->copyRoleTemplatePermissions($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $this->rolf_ref_id,$this->obj_id);		
			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			ilUtil::sendInfo($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".$this->lng->txt("msg_perm_adopted_from2"),true);
		}

		$this->ctrl->redirect($this, "perm");
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

		$this->getTemplateFile("edit","role");

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			if (substr($this->object->getTitle(),0,3) != "il_")
			{
				$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"]),true);
			}
		
			$this->tpl->setVariable("DESC",ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]));
			$protect_permissions = ($_SESSION["error_post_vars"]["Fobject"]["protect_permissions"]) ? "checked=\"checked\"" : "";
		}
		else
		{
			if (substr($this->object->getTitle(),0,3) != "il_")
			{
				$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($this->object->getTitle()));
			}

			$this->tpl->setVariable("DESC",ilUtil::stripSlashes($this->object->getDescription()));
			$protect_permissions = $rbacreview->isProtected($this->rolf_ref_id,$this->object->getId()) ? "checked=\"checked\"" : "";
		}

		$obj_str = "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
		
		$this->tpl->setCurrentBlock("protect_permissions");
		$this->tpl->setVariable("TXT_PROTECT_PERMISSIONS",$this->lng->txt('role_protect_permissions'));
		$this->tpl->setVariable("PROTECT_PERMISSIONS",$protect_permissions);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			$this->tpl->setVariable("SHOW_TITLE",$this->object->getTitle());
		}
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		if ($rbacsystem->checkAccess('write',$this->rolf_ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"),
				array("edit","update"), get_class($this));
				
			$tabs_gui->addTarget("default_perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"),
				array("perm"), get_class($this));
		}
	}

	
	/**
	* cancelObject is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		
		$this->ctrl->redirectByClass("ilobjrolefoldergui","view");
	}



	/**
	* update role template object
	*
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// check write access
		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_rolt"),$this->ilias->error_obj->WARNING);
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
		}

		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST["Fobject"]["protect_permissions"]));	
		$this->object->update();
		
		ilUtil::sendInfo($this->lng->txt("saved_successfully"),true);

		$this->ctrl->returnToParent($this);
	}
	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;

		if ($_GET["admin_mode"] == "settings")	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
				
			$ilLocator->addItem(ilObject::_lookupTitle(
				ilObject::_lookupObjId($_GET["ref_id"])),
				$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));

			$ilLocator->addItem($this->object->getTitle(),
				$this->ctrl->getLinkTarget($this, "perm"));
		}
		else							// repository administration
		{
			//?
		}

	}
	
	function showUpperIcon()
	{
		global $tree, $tpl, $objDefinition;
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
				$tpl->setUpperIcon(
					$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
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


} // END class.ilObjRoleTemplateGUI
?>
