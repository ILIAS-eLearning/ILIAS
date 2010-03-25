<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/OpenId/classes/class.ilOpenIdSettings.php';

/**
 * @classDescription Open ID Settings GUI
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @ilCtrl_isCalledBy ilOpenIdSettingsGUI: ilObjAuthSettingsGUI
 */
class ilOpenIdSettingsGUI
{
	private $ctrl;
	private $lng;
	
	private $ref_id;
	private $settings;
	
	
	/**
	 * Constructor
	 */
	public function __construct($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
		$this->ctrl = $GLOBALS['ilCtrl'];
		$this->lng = $GLOBALS['lng'];
		$this->lng->loadLanguageModule('auth');
		$this->settings = ilOpenIdSettings::getInstance();
	}
	
	/**
	 * Execute control command
	 * @return 
	 */
	public function executeCommand()
	{
		global $ilAccess, $ilErr, $lng;
		
		if(!$ilAccess->checkAccess('read','',$this->getRefId()))
		{
			$ilErr->raiseError($lng->txt('msg_no_perm_read'),$ilErr->WARNING);
		}
		switch($this->ctrl->getNextClass($this))
		{
			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'settings';
				}
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Set sub tabs for open id section
	 *
	 * @access private
	 */
	private function setSubTabs()
	{
		global $ilTabs;
		
		$ilTabs->addSubTabTarget(
			"auth_openid_settings",
			$this->ctrl->getLinkTarget($this,'settings')
		);
		/*
		$ilTabs->addSubTabTarget(
			'auth_openid_provider',
			$this->ctrl->getLinkTarget($this,'provider')
		);
		*/
	}
	
	
	/**
	 * Get ref id of settings object
	 * @return int
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * Show general settings
	 * @return 
	 */
	protected function settings()
	{
		global $tpl,$lng,$ilTabs;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
	 	$tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.openid_settings.html','Services/OpenId');
		
		$this->setSubTabs();
		$ilTabs->setSubTabActive('auth_openid_settings');

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'save'));
		
		$this->form->setTitle($this->lng->txt('auth_openid_configure'));
		
		// Activation
		$check = new ilCheckboxInputGUI($this->lng->txt('auth_openid_enable'),'active');
		$check->setChecked($this->settings->isActive() ? 1 : 0);
		$check->setValue(1);
		$this->form->addItem($check);
		
		// Creation
		$check = new ilCheckboxInputGUI($this->lng->txt('auth_openid_sync'),'sync');
		$check->setInfo($this->lng->txt('auth_openid_sync_info'));
		$check->setChecked($this->settings->isCreationEnabled() ? 1 : 0);
		$check->setValue(1);
		
		// Role selection
		$select = new ilSelectInputGUI($this->lng->txt('auth_openid_role_select'),'role');
		$select->setOptions($this->prepareRoleSelection());
		$select->setValue($this->settings->getDefaultRole());
		$check->addSubItem($select);

		$migr = new ilCheckboxInputGUI($this->lng->txt('auth_openid_migration'),'migration');
		$migr->setInfo($this->lng->txt('auth_openid_migration_info'));
		$migr->setChecked($this->settings->isAccountMigrationEnabled() ? 1 : 0);
		$migr->setValue(1);
		$check->addSubItem($migr);
		$this->form->addItem($check);

		$this->form->addCommandButton('save',$this->lng->txt('save'));
		
		$tpl->setVariable('SETTINGS_FORM',$this->form->getHTML());

		return true;
	}
	
	/**
	 * Administrate openid provider
	 * @return 
	 */
	protected function provider()
	{
		global $ilTabs;
		
		$this->setSubTabs();
		$ilTabs->setSubTabActive('auth_openid_provider');
	}
	
	/**
	 * Save settings
	 * @return 
	 */
	protected function save()
	{
		$this->settings->setActive((int) $_POST['active']);
		$this->settings->enableCreation((int) $_POST['sync']);
		$this->settings->enableAccountMigration((int) $_POST['migration']);
		$this->settings->setDefaultRole((int) $_POST['role']);
		$this->settings->update();
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'settings');
	}

	/**
	 * Prepare default role selection
	 * @return 
	 */	
	private function prepareRoleSelection()
	{
		global $rbacreview,$ilObjDataCache;
		
		$global_roles = ilUtil::_sortIds($rbacreview->getGlobalRoles(),
			'object_data',
			'title',
			'obj_id');
		
		$select[0] = $this->lng->txt('links_select_one');
		foreach($global_roles as $role_id)
		{
			$select[$role_id] = ilObject::_lookupTitle($role_id);
		}
		
		return $select;
	}
}
?>