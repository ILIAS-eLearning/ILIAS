<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");
include_once("./Modules/ItemGroup/classes/class.ilObjItemGroup.php");

/**
 * User Interface class for item groups
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * $Id$
 *
 * @ilCtrl_Calls ilObjItemGroupGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjItemGroupGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI
 * @ilCtrl_isCalledBy ilObjItemGroupGUI: ilRepositoryGUI
 * @ingroup ModulesItemGroup
 */
class ilObjItemGroupGUI extends ilObject2GUI
{
	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $lng;
		
		$lng->loadLanguageModule("itgr");
		
		$this->ctrl->saveParameter($this, array("ref_id"));
	}

	/**
	 * Get type
	 */
	final function getType()
	{
		return "itgr";
	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilTabs, $lng, $ilAccess, $tpl, $ilCtrl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilinfoscreengui':
				$this->prepareOutput();
				$this->addHeaderAction();
				$this->infoScreen();
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				$ilTabs->activateTab("perm_settings");
				$this->addHeaderAction();
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
				break;

			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			default:
				$this->prepareOutput();
				$this->addHeaderAction();
				$cmd = $this->ctrl->getCmd("listItems");
				$this->$cmd();
				break;
		}
	}

	/*protected function initCreationForms($a_new_type)
	{
		$forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type));

		return $forms;
	}*/

	/**
	 * save object
	 */
/*	function afterSave($newObj)
	{
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
		ilUtil::redirect("ilias.php?baseClass=ilObjItemGroupGUI&ref_id=".$newObj->getRefId()."&cmd=edit");
	}*/

	/**
	 * show material assignment
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function listMaterials()
	{
		global $tree, $objDefinition, $ilTabs, $tpl;
		
		$ilTabs->activateTab("materials");

		// add new item
		$parent_node = $tree->getNodeData($tree->getParentId($this->object->getRefId()));
		$subtypes = $objDefinition->getCreatableSubObjects($parent_node['type'], ilObjectDefinition::MODE_REPOSITORY);
		if($subtypes)
		{
			$subobj = array();
			foreach(array_keys($subtypes) as $type)
			{				
				$subobj[] = array('value' => $type,
								  'title' => $this->lng->txt('obj_'.$type),
								  'img' => ilObject::_getIcon('', 'tiny', $type),
								  'alt' => $this->lng->txt('obj_'.$type));
			}			
			$subobj = ilUtil::sortArray($subobj, 'title', 1);
			
			// add new object to parent container instead		
			$this->ctrl->setParameter($this, 'crtptrefid', $parent_node["child"]);
			// force after creation callback
			$this->ctrl->setParameter($this, 'crtcb', $this->object->getRefId());
			
			$this->lng->loadLanguageModule('cntr');
			$this->tpl->setCreationSelector($this->ctrl->getFormAction($this),
				$subobj, 'create', $this->lng->txt('add'));
			
			$this->ctrl->setParameter($this, 'crtptrefid', '');
		}

		include_once("./Modules/ItemGroup/classes/class.ilItemGroupItemsTableGUI.php");
		$tab = new ilItemGroupItemsTableGUI($this, "listMaterials");
		$tpl->setContent($tab->getHTML());
return;
		include_once 'Modules/Session/classes/class.ilEventItems.php';
		$this->event_items = new ilEventItems($this->object->getId());
		$items = $this->event_items->getItems();

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.sess_materials.html','Modules/Session');
		#$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this,'listMaterials'));
		$this->tpl->setVariable("COLL_TITLE_IMG",ilUtil::getImagePath('icon_sess.png'));
		$this->tpl->setVariable("COLL_TITLE_IMG_ALT",$this->lng->txt('events'));
		$this->tpl->setVariable("TABLE_TITLE",$this->lng->txt('event_assign_materials_table'));
		$this->tpl->setVariable("TABLE_INFO",$this->lng->txt('event_assign_materials_info'));

		$materials = array();
		$nodes = $tree->getSubTree($tree->getNodeData($parent_node["child"]));
		foreach($nodes as $node)
		{
			// No side blocks here
			if ($objDefinition->isSideBlock($node['type']) || $node['type'] == 'sess'
				|| $node['type'] == 'itgr')
			{
				continue;
			}
			
			if($node['type'] == 'rolf')
			{
				continue;
			}
			
			$node["sorthash"] = (int)(!in_array($node['ref_id'],$items)).$node["title"];
			$materials[] = $node;
		}
		
		
		$materials = ilUtil::sortArray($materials, "sorthash", "ASC");
		
		$counter = 1;
		foreach($materials as $node)
		{
			$counter++;
			
			$this->tpl->setCurrentBlock("material_row");
			
			$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$node['type'].'_s.png'));
			$this->tpl->setVariable('IMG_ALT',$this->lng->txt('obj_'.$node['type']));
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor($counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_COLL",ilUtil::formCheckbox(in_array($node['ref_id'],$items) ? 1 : 0,
																	  'items[]',$node['ref_id']));
			$this->tpl->setVariable("COLL_TITLE",$node['title']);

			if(strlen($node['description']))
			{
				$this->tpl->setVariable("COLL_DESC",$node['description']);
			}
			$this->tpl->setVariable("ASSIGNED_IMG_OK",in_array($node['ref_id'],$items) ? 
									ilUtil::getImagePath('icon_ok.png') :
									ilUtil::getImagePath('icon_not_ok.png'));
			$this->tpl->setVariable("ASSIGNED_STATUS",$this->lng->txt('event_material_assigned'));
//			$this->tpl->setVariable("COLL_PATH",$this->formatPath($node['ref_id']));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("SELECT_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$this->tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.png'));
		$this->tpl->setVariable("BTN_SAVE",$this->lng->txt('save'));
	}
	
	/**
	 * Save material assignment
	 */
	public function saveItemAssignment()
	{
		global $ilCtrl;

		include_once './Modules/ItemGroup/classes/class.ilItemGroupItems.php';

		$item_group_items = new ilItemGroupItems($this->object->getRefId());
		$items = is_array($_POST['items'])
			? $_POST['items']
			: array();
		$items = ilUtil::stripSlashesArray($items);	
		$item_group_items->setItems($items);
		$item_group_items->update();

		ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
		$ilCtrl->redirect($this, "listMaterials");
	}

	
	/**
	* Get standard template
	*/
	function getTemplate()
	{
		$this->tpl->getStandardTemplate();
	}


	/**
	 * Set tabs
	 */
	function setTabs()
	{
		global $ilAccess, $ilTabs, $ilCtrl, $ilHelp, $lng;
		
		$ilHelp->setScreenIdComponent("itgr");
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab('materials',
				$lng->txt('itgr_materials'),
				$this->ctrl->getLinkTarget($this, 'listMaterials'));

			$ilTabs->addTab('settings',
				$lng->txt('settings'),
				$this->ctrl->getLinkTarget($this, 'edit'));
		}
		
		if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("perm_settings",
				$lng->txt('perm_settings'),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
				);
		}
	}


	/**
	 * Goto item group
	 */
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;
		
		$targets = explode('_',$a_target);
return;
// todo
		if(count((array) $targets) > 1)
		{
			$ref_id = $targets[0];
			$subitem_id = $targets[1];
		}
		else
		{
			$ref_id = $targets[0];
		}

		if ($ilAccess->checkAccess("read", "", $ref_id))
		{
			$_GET["baseClass"] = "ilMediaPoolPresentationGUI";
			$_GET["ref_id"] = $ref_id;
			include("ilias.php");
			exit;
		} 
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

}
?>