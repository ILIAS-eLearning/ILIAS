<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");


/**
* Media Objects/Pools Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjMediaObjectsSettingsGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjMediaObjectsSettingsGUI: ilAdministrationGUI
*
* @ingroup ServicesMediaObject
*/
class ilObjMediaObjectsSettingsGUI extends ilObjectGUI
{
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'mobs';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('mob');
		$this->lng->loadLanguageModule('mep');
		$this->lng->loadLanguageModule('content');
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem,$ilErr,$ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSettings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess, $ilTabs;

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));
		}

		if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId()))
		{
			$ilTabs->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit settings
	*/
	function editSettings($a_omit_init = false)
	{
		global $tpl;
		
		$this->checkPermission("write");
		
		if (!$a_omit_init)
		{
			$this->initMediaObjectsSettingsForm();
			$this->getSettingsValues();
		}
		$tpl->setContent($this->form->getHTML());
	}
		
	/**
	 * Save settings
	 */	
	public function saveSettings()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initMediaObjectsSettingsForm();
		if ($this->form->checkInput())
		{
			// perform save
			$mset = new ilSetting("mobs");		
			$mset->set("mep_activate_pages", $_POST["activate_pages"]);
			$mset->set("file_manager_always", $_POST["file_manager_always"]);
			$mset->set("restricted_file_types", $_POST["restricted_file_types"]);
			$mset->set("upload_dir", $_POST["mob_upload_dir"]);
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editSettings");
		}
		
		$this->form->setValuesByPost();
		$this->editSettings(true);
	}
	
	/**
	 * Init media objects settings form.
	 */
	public function initMediaObjectsSettingsForm()
	{
		global $lng, $ilCtrl;
		
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// activate page in media pool 
		$cb = new ilCheckboxInputGUI($lng->txt("mobs_activate_pages"), "activate_pages");
		$cb->setInfo($lng->txt("mobs_activate_pages_info"));
		$this->form->addItem($cb);
	
		// activate page in media pool 
		$cb = new ilCheckboxInputGUI($lng->txt("mobs_always_show_file_manager"), "file_manager_always");
		$cb->setInfo($lng->txt("mobs_always_show_file_manager_info"));
		$this->form->addItem($cb);
		
		// allowed file types
		$ta = new ilTextAreaInputGUI($this->lng->txt("mobs_restrict_file_types"), "restricted_file_types");
		//$ta->setCols();
		//$ta->setRows();
		$ta->setInfo($this->lng->txt("mobs_restrict_file_types_info"));
		$this->form->addItem($ta);
		

		// Upload dir for learning resources
		$tx_prop = new ilTextInputGUI($lng->txt("mob_upload_dir"),
			"mob_upload_dir");
		$tx_prop->setInfo($lng->txt("mob_upload_dir_info"));
		$this->form->addItem($tx_prop);

		$this->form->addCommandButton("saveSettings", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("settings"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Get current values for form from 
	 */
	public function getSettingsValues()
	{
		$values = array();
	
		$mset = new ilSetting("mobs");
		$values["activate_pages"] = $mset->get("mep_activate_pages");
		$values["file_manager_always"] = $mset->get("file_manager_always");
		$values["restricted_file_types"] = $mset->get("restricted_file_types");
		$values["mob_upload_dir"] = $mset->get("upload_dir");
	
		$this->form->setValuesByArray($values);
	}

}
?>