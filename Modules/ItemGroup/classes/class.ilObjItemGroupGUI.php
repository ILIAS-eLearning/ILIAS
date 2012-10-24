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
		global $ilTabs, $lng, $ilAccess, $tpl, $ilCtrl, $ilLocator;
		
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
				$cmd = $this->ctrl->getCmd("listMaterials");
				$this->prepareOutput();
				$this->addHeaderAction();
				$this->$cmd();
				break;
		}
	}

	/**
	 * Add session locator
	 *
	 * @access public
	 * 
	 */
	public function addLocatorItems()
	{
		global $ilLocator, $ilAccess;
		
		if (is_object($this->object) && $ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "listMaterials"), "", $_GET["ref_id"]);
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
		
		$this->checkPermission("write");
		
		$ilTabs->activateTab("materials");

		// add new item
		$parent_node = $tree->getNodeData($tree->getParentId($this->object->getRefId()));
		$subtypes = $objDefinition->getCreatableSubObjects($parent_node['type'], ilObjectDefinition::MODE_REPOSITORY);
		if($subtypes)
		{
			$subobj = array();
			foreach(array_keys($subtypes) as $type)
			{
				if (!in_array($type, array("itgr", "sess")))
				{
					$subobj[] = array('value' => $type,
									  'title' => $this->lng->txt('obj_'.$type),
									  'img' => ilObject::_getIcon('', 'tiny', $type),
									  'alt' => $this->lng->txt('obj_'.$type));
				}
			}			
//			$subobj = ilUtil::sortArray($subobj, 'title', 1);
			
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
	}
	
	/**
	 * Save material assignment
	 */
	public function saveItemAssignment()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");

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

	/**
	 * Custom callback after object is created (in parent containert
	 * 
	 * @param ilObject $a_obj 
	 */	
	public function afterSaveCallback(ilObject $a_obj)
	{		
		// add new object to materials
		include_once './Modules/ItemGroup/classes/class.ilItemGroupItems.php';
		$items = new ilItemGroupItems($this->object->getRefId());
		$items->addItem($a_obj->getRefId());
		$items->update();
	}	

}
?>