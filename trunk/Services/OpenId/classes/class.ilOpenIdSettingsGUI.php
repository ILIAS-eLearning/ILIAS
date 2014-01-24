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
	private $tpl;
	
	private $ref_id;
	private $settings;
	
	private $form = null;
	private $provider = null;
	
	
	/**
	 * Constructor
	 */
	public function __construct($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
		$this->ctrl = $GLOBALS['ilCtrl'];
		$this->lng = $GLOBALS['lng'];
		$this->tpl = $GLOBALS['tpl'];
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
		$ilTabs->addSubTabTarget(
			'auth_openid_provider',
			$this->ctrl->getLinkTarget($this,'provider')
		);
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
		$this->form->setTableWidth('75%');
		$this->form->setFormAction($this->ctrl->getFormAction($this,'save'));
		
		$this->form->setTitle($this->lng->txt('auth_openid_configure'));
		
		// Activation
		$check = new ilCheckboxInputGUI($this->lng->txt('auth_openid_enable'),'active');
		$check->setChecked($this->settings->isActive() ? 1 : 0);
		$check->setValue(1);
		$this->form->addItem($check);
		
		// Selection
		$sel = new ilCheckboxInputGUI($this->lng->txt('auth_openid_uncontrolled_selection'),'free');
		$sel->setChecked(!$this->settings->forcedProviderSelection() ? 1 : 0);
		$sel->setValue(1);
		$sel->setInfo($this->lng->txt('auth_openid_uncontrolled_selection_info'));
		$this->form->addItem($sel);
		
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
	 * Save settings
	 * @return 
	 */
	protected function save()
	{
		$this->settings->setActive((int) $_POST['active']);
		$this->settings->forceProviderSelection((int) !$_POST['free']);
		$this->settings->enableCreation((int) $_POST['sync']);
		$this->settings->enableAccountMigration((int) $_POST['migration']);
		$this->settings->setDefaultRole((int) $_POST['role']);
		$this->settings->update();
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'settings');
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
		
		include_once './Services/OpenId/classes/class.ilOpenIdProviderTableGUI.php';
		$table = new ilOpenIdProviderTableGUI($this,'provider');
		$table->parse();
		
		$GLOBALS['tpl']->setContent($table->getHTML());
		return true;
	}
	
	/**
	 * Delete selected provider
	 * @return 
	 */
	protected function deleteProvider()
	{
		if(!isset($_POST['provider_ids']) or !$_POST['provider_ids'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->provider();
			return true;
		}
	
		foreach($_POST['provider_ids'] as $provider)
		{
			$this->initProvider($provider);
			$this->provider->delete();
		}
		ilUtil::sendSuccess($this->lng->txt('auth_openid_deleted_provider'),TRUE);
		$this->ctrl->redirect($this,'provider');
	}
	
	/**
	 * Create new provider
	 * @return 
	 */
	protected function addProvider()
	{
		global $ilTabs;
	
		$this->setSubTabs();
		$ilTabs->setSubTabActive('auth_openid_provider');
		
		$this->initProvider(0);
		$this->initFormProvider('add');
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Create new provider
	 * @return 
	 */
	protected function editProvider()
	{
		global $ilTabs;
	
		$this->setSubTabs();
		$ilTabs->setSubTabActive('auth_openid_provider');

		$this->ctrl->setParameter($this,'provider_id',$_GET['provider_id']);

		$this->initProvider((int) $_GET['provider_id']);
		$this->initFormProvider('edit');
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Create new provider
	 * @return 
	 */
	protected function createProvider()
	{
		global $tpl;
		
		$this->initProvider(0);
		$this->initFormProvider('add');
		
		if(!$this->form->checkInput())
		{
			$this->form->setValuesByPost();
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
			$tpl->setContent($this->form->getHTML());
			return false;
		}
		$this->loadProviderFromPost();
		$this->provider->add();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'provider');
	}

	/**
	 * Update provider settings
	 * @return 
	 */	
	protected function updateProvider()
	{
		global $tpl;
		
		$this->initProvider((int) $_GET['provider_id']);
		$this->initFormProvider('edit');
		
		if(!$this->form->checkInput())
		{
			$this->form->setValuesByPost();
			ilUtil::sendFailure($this->lng->txt('err_check_input'));
			$tpl->setContent($this->form->getHTML());
			return false;
		}
		$this->loadProviderFromPost();
		$this->provider->update();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'provider');
	}
	
	/**
	 * Show provider form
	 * @param string $a_mode [optional] add | edit
	 * @return 
	 */
	protected function initFormProvider($a_mode = 'edit')
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'provider'));
		$this->form->setTableWidth('75%');
		
		switch($a_mode)
		{
			case 'edit':
				$this->form->setTitle($this->lng->txt('auth_openid_provider_edit'));
				$this->form->addCommandButton('updateProvider', $this->lng->txt('save'));
				$this->form->addCommandButton('provider', $this->lng->txt('cancel'));
				break;
							
			case 'add':
				$this->form->setTitle($this->lng->txt('auth_openid_provider_add'));
				$this->form->addCommandButton('createProvider', $this->lng->txt('btn_add'));
				$this->form->addCommandButton('provider', $this->lng->txt('cancel'));
				break;
		}

		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setRequired(true);
		$title->setMaxLength(128);
		$title->setSize(32);
		$title->setValue($this->provider->getName());
		$this->form->addItem($title);
		
		$url = new ilTextInputGUI($this->lng->txt('url'),'url');
		$url->setValidationRegexp('/http.*%s.*/');
		$url->setRequired(true);
		$url->setMaxLength(255);
		$url->setSize(32);
		$url->setInfo($this->lng->txt('auth_openid_url_info'));
		$url->setValue($this->provider->getURL());
		$this->form->addItem($url);
	}
	
	/**
	 * Init provider
	 * @param object $a_provider_id
	 * @return 
	 */
	protected function initProvider($a_provider_id)
	{
		include_once './Services/OpenId/classes/class.ilOpenIdProvider.php';
		
		$this->provider = new ilOpenIdProvider($a_provider_id);
		return $this->provider;
	}
	
	/**
	 * load provider from post
	 */
	protected function loadProviderFromPost()
	{
		$this->provider->setName($this->form->getInput('title'));
		$this->provider->setURL($this->form->getInput('url'));
		return $this->provider();
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