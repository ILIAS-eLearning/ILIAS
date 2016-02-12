<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Badge Administration Settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjBadgeAdministrationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjBadgeAdministrationGUI: ilAdministrationGUI
 *
 * @ingroup ServicesBadge
 */
class ilObjBadgeAdministrationGUI extends ilObjectGUI
{	
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "bdga";
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule("badge");
	}

	public function executeCommand()
	{		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
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

	public function getAdminTabs()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "editSettings"));
			
			if($this->isActive())
			{			
				$this->tabs_gui->addTab("types",
					$this->lng->txt("badge_types"),
					$this->ctrl->getLinkTarget($this, "listTypes"));

				$this->tabs_gui->addTab("imgtmpl",
					$this->lng->txt("badge_image_templates"),
					$this->ctrl->getLinkTarget($this, "listImageTemplates"));

				$this->tabs_gui->addTab("activity",
					$this->lng->txt("badge_activity_badges"),
					$this->ctrl->getLinkTarget($this, "listActivityBadges"));
			}
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}
	}
	
	protected function isActive()
	{
		$bdga_set = new ilSetting("bdga");
		return (bool)$bdga_set->get("active", false);		
	}
	
	protected function assertActive()
	{
		if(!$this->isActive())
		{
			$this->ctrl->redirect($this, "editSettings");
		}		
	}
	
	
	//
	// settings
	//

	protected function editSettings($a_form = null)
	{		
		$this->tabs_gui->setTabActive("settings");	
		
		if(!$a_form)
		{
			$a_form = $this->initFormSettings();
		}		
		
		$this->tpl->setContent($a_form->getHTML());
	}

	protected function saveSettings()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		$form = $this->initFormSettings();
		if($form->checkInput())
		{			
			$bdga_set = new ilSetting("bdga");
			$bdga_set->set("active", (bool)$form->getInput("act"));			
			$bdga_set->set("obi_active", (bool)$form->getInput("obi"));			
			$bdga_set->set("obi_organisation", trim($form->getInput("obi_org")));			
			$bdga_set->set("obi_contact", trim($form->getInput("obi_cont")));			
			$bdga_set->set("obi_salt", trim($form->getInput("obi_salt")));			
			
			ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
			$ilCtrl->redirect($this, "editSettings");
		}
		
		$form->setValuesByPost();
		$this->editSettings($form);
	}

	protected function initFormSettings()
	{
	    global $ilAccess;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("badge_settings"));
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$form->addCommandButton("saveSettings", $this->lng->txt("save"));
			$form->addCommandButton("editSettings", $this->lng->txt("cancel"));
		}

		$act = new ilCheckboxInputGUI($this->lng->txt("badge_service_activate"), "act");
		$act->setInfo($this->lng->txt("badge_service_activate_info"));
		$form->addItem($act);				
		
		$obi = new ilCheckboxInputGUI($this->lng->txt("badge_obi_activate"), "obi");		
		$obi->setInfo($this->lng->txt("badge_obi_activate_info"));
		$form->addItem($obi);
		
			$obi_org = new ilTextInputGUI($this->lng->txt("badge_obi_organisation"), "obi_org");
			$obi_org->setRequired(true);
			$obi_org->setInfo($this->lng->txt("badge_obi_organisation_info"));
			$obi->addSubItem($obi_org);
			
			$obi_contact = new ilEmailInputGUI($this->lng->txt("badge_obi_contact"), "obi_cont");
			$obi_contact->setRequired(true);
			$obi_contact->setInfo($this->lng->txt("badge_obi_contact_info"));
			$obi->addSubItem($obi_contact);
			
			$obi_salt = new ilTextInputGUI($this->lng->txt("badge_obi_salt"), "obi_salt");
			$obi_salt->setRequired(true);
			$obi_salt->setInfo($this->lng->txt("badge_obi_salt_info"));
			$obi->addSubItem($obi_salt);
		
		$bdga_set = new ilSetting("bdga");
		$act->setChecked($bdga_set->get("active", false));				
		$obi->setChecked($bdga_set->get("obi_active", false));				
		$obi_org->setValue($bdga_set->get("obi_organisation", null));				
		$obi_contact->setValue($bdga_set->get("obi_contact", null));				
		$obi_salt->setValue($bdga_set->get("obi_salt", null));				
		
		return $form;
	}
	
	
	//
	// types
	//
	
	protected function listTypes()
	{
		$this->assertActive();
		$this->tabs_gui->setTabActive("types");	
		
		// $this->tpl->setContent($tbl->getHTML());
	}
	
	
	//
	// images templates
	//
	
	protected function listImageTemplates()
	{
		$this->assertActive();
		$this->tabs_gui->setTabActive("imgtmpl");	
		
		// $this->tpl->setContent($tbl->getHTML());
	}
	
	
	//
	// activity badges
	//
	
	protected function listActivityBadges()
	{
		$this->assertActive();
		$this->tabs_gui->setTabActive("activity");	
		
		// $this->tpl->setContent($tbl->getHTML());
	}
}