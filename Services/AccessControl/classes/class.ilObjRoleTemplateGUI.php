<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjRoleTemplateGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjRoleTemplateGUI:
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleTemplateGUI extends ilObjectGUI
{

	const FORM_MODE_EDIT = 1;
	const FORM_MODE_CREATE = 2;
	
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
	
	function executeCommand()
	{
		global $rbacsystem;

		$this->prepareOutput();

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
	 * Init create form
	 * @param bool creation mode
	 * @return ilPropertyFormGUI $form
	 */
	protected function initFormRoleTemplate($a_mode = self::FORM_MODE_CREATE)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		if($this->creation_mode)
		{
			$this->ctrl->setParameter($this, "new_type", 'rolt');
		}
		
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		if($a_mode == self::FORM_MODE_CREATE)
		{
			$form->setTitle($this->lng->txt('rolt_new'));
			$form->addCommandButton('save', $this->lng->txt('rolt_new'));
		}
		else
		{
			$form->setTitle($this->lng->txt('rolt_edit'));
			$form->addCommandButton('update', $this->lng->txt('save'));
			
		}
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		if($a_mode != self::FORM_MODE_CREATE)
		{
			if($this->object->isInternalTemplate())
			{
				$title->setDisabled(true);
			}
			$title->setValue($this->object->getTitle());
		}
		$title->setSize(40);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		
		if($a_mode != self::FORM_MODE_CREATE)
		{
			$desc->setValue($this->object->getDescription());
		}
		$desc->setCols(40);
		$desc->setRows(3);
		$form->addItem($desc);

		if($a_mode != self::FORM_MODE_CREATE)
		{
			$ilias_id = new ilNonEditableValueGUI($this->lng->txt("ilias_id"), "ilias_id");
			$ilias_id->setValue('il_'.IL_INST_ID.'_'.ilObject::_lookupType($this->object->getId()).'_'.$this->object->getId());
			$form->addItem($ilias_id);
		}

		$pro = new ilCheckboxInputGUI($this->lng->txt('role_protect_permissions'),'protected');
		$pro->setChecked($GLOBALS['rbacreview']->isProtected(
				$this->rolf_ref_id,
				$this->object->getId()
		));
		$pro->setValue(1);
		$form->addItem($pro);

		return $form;
	}

	
	/**
	* create new role definition template
	*
	* @access	public
	*/
	function createObject(ilPropertyFormGUI $form = null)
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("create_rolt", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$form)
		{
			$form = $this->initFormRoleTemplate(self::FORM_MODE_CREATE);
		}
		$this->tpl->setContent($form->getHTML());
		return true;
	}
	
	/**
	 * Create new object
	 */
	public function editObject(ilPropertyFormGUI $form = null)
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		if(!$form)
		{
			$form = $this->initFormRoleTemplate(self::FORM_MODE_EDIT);	
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}

	/**
	* update role template object
	*
	* @access	public
	*/
	public function updateObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// check write access
		if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_rolt"),$this->ilias->error_obj->WARNING);
		}
		
		$form = $this->initFormRoleTemplate(self::FORM_MODE_EDIT);
		if($form->checkInput())
		{
			$this->object->setTitle($form->getInput('title'));
			$this->object->setDescription($form->getInput('desc'));
			$rbacadmin->setProtected(
					$this->rolf_ref_id,
					$this->object->getId(),
					$form->getInput('protected') ? 'y' : 'n'
			);
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			$this->ctrl->returnToParent($this);
		}
		
		$form->setValuesByPost();
		$this->editObject($form);
	}
	


	/**
	* save a new role template object
	*
	* @access	public
	*/
	public function saveObject()
	{
		global $rbacsystem,$rbacadmin, $rbacreview;

		if (!$rbacsystem->checkAccess("create_rolt",$this->rolf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolt"),$this->ilias->error_obj->WARNING);
		}
		$form = $this->initFormRoleTemplate();
		if($form->checkInput())
		{
			include_once("./Services/AccessControl/classes/class.ilObjRoleTemplate.php");
			$roltObj = new ilObjRoleTemplate();
			$roltObj->setTitle($form->getInput('title'));
			$roltObj->setDescription($form->getInput('desc'));
			$roltObj->create();
			$rbacadmin->assignRoleToFolder($roltObj->getId(), $this->rolf_ref_id,'n');
			$rbacadmin->setProtected(
					$this->rolf_ref_id,
					$roltObj->getId(),
					$form->getInput('protected') ? 'y' : 'n'
			);
			
			ilUtil::sendSuccess($this->lng->txt("rolt_added"),true);
			// redirect to permission screen
			$this->ctrl->setParameter($this,'obj_id',$roltObj->getId());
			$this->ctrl->redirect($this,'perm');
		}
		$form->setValuesByPost();
		$this->createObject($form);
	}

	/**
	* display permissions
	* 
	* @access	public
	*/
	function permObject()
	{
		global $rbacadmin, $rbacreview, $rbacsystem,$objDefinition,$ilSetting;

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
		$op_order = array();

		foreach(ilRbacReview::_getOperationList() as $op)
		{
			$op_order[$op["ops_id"]] = $op["order"];
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
			if ($internal_tpl and $tpl_filter and !in_array($info['type'],$tpl_filter))
			{
				continue;
			}
			$rbac_objects[$info['typ_id']] = array("obj_id"	=> $info['typ_id'],
											    "type"		=> $info['type']);
			
			$txt = $objDefinition->isPlugin($info['type'])
				? ilPlugin::lookupTxt("rep_robj", $info['type'], $info['type']."_".$info['operation'])
				: $this->lng->txt($info['type']."_".$info['operation']);
			if (substr($info['operation'], 0, 7) == "create_" &&
				$objDefinition->isPlugin(substr($info['operation'], 7)))
			{
				$txt = ilPlugin::lookupTxt("rep_robj", substr($info['operation'], 7), $info['type']."_".$info['operation']);
			}
			elseif(substr($info['operation'],0,6) == 'create')
			{
				$txt = $this->lng->txt('rbac_'.$info['operation']);
			}

			$order = $op_order[$info['ops_id']];
			if(substr($info['operation'],0,6) == 'create')
			{
				$order = $objDefinition->getPositionByType($info['type']);
			}

			$rbac_operations[$info['typ_id']][$info['ops_id']] = array(
									   							"ops_id"	=> $info['ops_id'],
									  							"title"		=> $info['operation'],
																"name"		=> $txt,
																"order"		=> $order);
		}
		
		foreach ($rbac_objects as $key => $obj_data)
		{
			if ($objDefinition->isPlugin($obj_data["type"]))
			{
				$rbac_objects[$key]["name"] = ilPlugin::lookupTxt("rep_robj", $obj_data["type"],
						"obj_".$obj_data["type"]);
			}
			else
			{
				$rbac_objects[$key]["name"] = $this->lng->txt("obj_".$obj_data["type"]);
			}

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
			ilUtil::sendFailure($this->lng->txt("msg_sysrole_not_editable"));
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_perm_role.html",
			"Services/AccessControl");

		foreach ($rbac_objects as $obj_data)
		{
			// BEGIN object_operations
			$this->tpl->setCurrentBlock("object_operations");

			$obj_data["ops"] = ilUtil::sortArray($obj_data["ops"], 'order','asc',true,true);

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

		/* 
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
		*/
		
		$this->tpl->setCurrentBlock("tblfooter_protected");
		$this->tpl->setVariable("COL_ANZ",3);
		$this->tpl->setVariable("CHECK_BOTTOM",$this->data["check_protected"]);
		$this->tpl->setVariable("MESSAGE_TABLE",$this->data["text_protected"]);
		$this->tpl->parseCurrentBlock();
	
		$this->tpl->setVariable("COL_ANZ_PLUS",4);
		$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_".$this->object->getType().".svg"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));

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
		// not applicable for role templates
		#$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST['protected']));

		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);

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
			ilUtil::sendFailure($this->lng->txt("msg_perm_adopted_from_itself"),true);
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
			ilUtil::sendSuccess($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".$this->lng->txt("msg_perm_adopted_from2"),true);
		}

		$this->ctrl->redirect($this, "perm");
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
			$tabs_gui->addTarget("settings",
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
		$this->ctrl->redirectByClass("ilobjrolefoldergui","view");
	}



	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;
		
		parent::addAdminLocatorItems(true);
				
		$ilLocator->addItem(ilObject::_lookupTitle(
			ilObject::_lookupObjId($_GET["ref_id"])),
			$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
	}
	
} // END class.ilObjRoleTemplateGUI
?>
