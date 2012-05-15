<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjDataCollectionGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjDataCollectionGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjDataCollectionGUI: ilPermissionGUI, ilObjectCopyGUI
*
* @extends ilObject2GUI
*/
class ilObjDataCollectionGUI extends ilObject2GUI
{	
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $lng;
		
	    parent::__construct($a_id, $a_id_type, $a_parent_node_id);		
		
		$lng->loadLanguageModule("dcl");
	}

	function getType()
	{
		return "dcl";
	}
	
	function executeCommand()
	{
		global $ilCtrl, $ilTabs;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->infoScreenForward();	
				break;
			
			case "ilnotegui":
				$this->render();
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilpermissiongui":
				$this->prepareOutput();
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			
			case "ilobjectcopygui":
				include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
				$cp = new ilObjectCopyGUI($this);
				$cp->setType("dcl");
				$this->ctrl->forwardCommand($cp);
				break;

			default:								
				$this->addHeaderAction($cmd);			
				return parent::executeCommand();			
		}
		
		return true;
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreen()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreenForward();
	}
	
	/**
	* show information screen
	*/
	function infoScreenForward()
	{
		global $ilTabs, $ilErr;
		
		$ilTabs->activateTab("id_info");

		if (!$this->checkPermissionBool("visible"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();		
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		
		$this->ctrl->forwardCommand($info);
	}
	
	
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
		}
	}
	
	/**
	 * Deep link
	 * 
	 * @param string $a_target 
	 */
	function _goto($a_target)
	{									
		$id = explode("_", $a_target);		

		$_GET["baseClass"] = "ilRepositoryGUI";	
		$_GET["ref_id"] = $id[0];		
		$_GET["cmd"] = "render";
		
		include("ilias.php");
		exit;
	}
		
	protected function render()
	{
		global $ilNavigationHistory;
		
		// $ilNavigationHistory->addItem();
		
		// ...
	} 
	
	/*
	protected function initCreationForms($a_new_type)
	{
		
	}
	
	protected function afterSave(ilObject $a_new_object)
	{
		
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
				
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		
	}	 
	
	function setTabs()
	{		
		// will add permissions if needed
		parent::setTabs();
	}	 

	protected function initHeaderAction($sub_type = null, $sub_id = null)
	{
		global $ilUser, $ilCtrl;		

		$lg = parent::initHeaderAction($a_sub_type, $a_sub_id);		
		if($lg)
		{
			include_once "./Services/Notification/classes/class.ilNotification.php";
			if(ilNotification::hasNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->node_id))
			{
				$ilCtrl->setParameter($this, "ntf", 1);
				$link = $ilCtrl->getLinkTarget($this, "setNotification");
				$lg->addCustomCommand($link, "dcl_notification_toggle_off");
				
				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_on.png"),
					$this->lng->txt("dcl_notification_activated"));
			}
			else
			{
				$ilCtrl->setParameter($this, "ntf", 2);
				$link = $ilCtrl->getLinkTarget($this, "setNotification");
				$lg->addCustomCommand($link, "dcl_notification_toggle_on");
				
				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_off.png"),
					$this->lng->txt("dcl_notification_deactivated"));
			}
		}
		
		return $lg;
	}
	
	protected function setNotification()
	{
		global $ilUser, $ilCtrl;
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		switch($_GET["ntf"])
		{
			case 1:
				ilNotification::setNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->node_id, false);
				break;
			
			case 2:
				ilNotification::setNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->node_id, true);
				break;
		}
		
		$ilCtrl->redirect($this, "render");
	}	 	
	*/	
}

?>