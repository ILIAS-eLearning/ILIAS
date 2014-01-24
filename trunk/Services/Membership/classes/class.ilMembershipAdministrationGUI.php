<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php" ;
include_once "./Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php" ;

/**
 * Membership Administration Settings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesMembership
 */
abstract class ilMembershipAdministrationGUI extends ilObjectGUI
{	
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = $this->getType();
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule("grp");
	}
	
	public function executeCommand()
	{		
		global $ilAccess, $ilErr;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("no_permission"), $ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive("perm_settings");
				include_once "Services/AccessControl/classes/class.ilPermissionGUI.php";
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd || $cmd == "view")
				{
					$cmd = "editSettings";
				}
				$this->$cmd();
				break;
		}
		return true;
	}

	public function getAdminTabs()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));
		}

		if ($rbacsystem->checkAccess("edit_permission", $this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"),
				array(), "ilpermissiongui");
		}
	}
	
	public function editSettings(ilObjPropertyFormGUI $a_form = null)
	{		
		$this->tabs_gui->setTabActive('settings');	
				
		if(!$a_form)
		{
			$a_form = $this->initFormSettings();
		}		
		$this->tpl->setContent($a_form->getHTML());
		return true;
	}

	public function saveSettings()
	{
		global $ilSetting;
		
		$this->checkPermission("write");
		
		$form = $this->initFormSettings();
		if($form->checkInput())
		{			
			if($this->save($form))
			{			
				$ilSetting->set('mail_'.$this->getParentObjType().'_member_notification', 
					(int)$form->getInput('mail_member_notification'));
				
				ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
				$this->ctrl->redirect($this, "editSettings");
			}
		}
		
		$form->setValuesByPost();
		$this->editSettings($form);
	}

	protected function initFormSettings()
	{	    
		global $ilSetting;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
		$form->setTitle($this->lng->txt("settings"));
				
		$this->addFieldsToForm($form);		
				
		$this->lng->loadLanguageModule("mail");
	
		// member notification
		$cn = new ilCheckboxInputGUI($this->lng->txt('mail_enable_'.$this->getParentObjType().'_member_notification'), 'mail_member_notification');
		$cn->setInfo($this->lng->txt('mail_enable_'.$this->getParentObjType().'_member_notification_info'));
		$cn->setChecked($ilSetting->get('mail_'.$this->getParentObjType().'_member_notification', true));
		$form->addItem($cn);
		
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			$this->getAdministrationFormId(), 
			$form,
			$this
		);
		
		$form->addCommandButton("saveSettings", $this->lng->txt("save"));
		$form->addCommandButton("view", $this->lng->txt("cancel"));

		return $form;
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{				
		global $ilSetting;
		
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_MAIL:
				
				$this->lng->loadLanguageModule("mail");
				
				$fields = array('mail_enable_'.$this->getParentObjType().'_member_notification' => array($ilSetting->get('mail_'.$this->getParentObjType().'_member_notification', true), ilAdministrationSettingsFormHandler::VALUE_BOOL));
				
				return array(array("editSettings", $fields));			
		}
	}
		
	protected function addFieldsToForm(ilPropertyFormGUI $a_form)
	{
		
	}
			
	protected function save(ilPropertyFormGUI $a_form)
	{
		return true;	
	}
	
	abstract protected function getType();
	
	abstract protected function getParentObjType();
	
	abstract protected function getAdministrationFormId();
}

?>