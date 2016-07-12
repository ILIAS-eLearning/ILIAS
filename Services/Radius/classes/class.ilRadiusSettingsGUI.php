<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* @defgroup ServicesRadius
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesRadius
*/
class ilRadiusSettingsGUI
{
	private $ref_id;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int object auth ref_id
	 * 
	 */
	public function __construct($a_auth_ref_id)
	{
		global $lng,$ilCtrl,$tpl,$ilTabs;
		
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('registration');
		$this->lng->loadLanguageModule('auth');
		
		$this->tpl = $tpl;
		$this->ref_id = $a_auth_ref_id;

		$this->initSettings();
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function executeCommand()
	{
		global $ilAccess,$ilErr,$ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("settings");
		
		if(!$ilAccess->checkAccess('read','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->WARNING);
		}
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id) && $cmd != "settings")
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_write'), true);
			$ilCtrl->redirect($this, "settings");
		}


		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "settings";
				}
				$this->$cmd();
				break;
		}
		return true;
	 	
	}
	
	/**
	 * Show settings
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function settings()
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.settings.html','Services/Radius');

		$this->lng->loadLanguageModule('auth');
	 	
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('auth_radius_configure'));
		
		// Form checkbox
		$check = new ilCheckboxInputGUI($this->lng->txt('auth_radius_enable'),'active');
		$check->setChecked($this->settings->isActive() ? 1 : 0);
		$check->setValue(1);
		$form->addItem($check);
		
		$text = new ilTextInputGUI($this->lng->txt('auth_radius_name'),'name');
		$text->setRequired(true);
		$text->setInfo($this->lng->txt('auth_radius_name_desc'));
		$text->setValue($this->settings->getName());
		$text->setSize(32);
		$text->setMaxLength(64);
		$form->addItem($text);
		
		$text = new ilTextInputGUI($this->lng->txt('auth_radius_server'),'servers');
		$text->setRequired(true);
		$text->setInfo($this->lng->txt('auth_radius_server_desc'));
		$text->setValue($this->settings->getServersAsString());
		$text->setSize(64);
		$text->setMaxLength(255);
		$form->addItem($text);
		
			
		$text = new ilTextInputGUI($this->lng->txt('auth_radius_port'),'port');
		$text->setRequired(true);
		$text->setValue($this->settings->getPort());
		$text->setSize(5);
		$text->setMaxLength(5);
		$form->addItem($text);

		$text = new ilTextInputGUI($this->lng->txt('auth_radius_shared_secret'),'secret');
		$text->setRequired(true);
		$text->setValue($this->settings->getSecret());
		$text->setSize(16);
		$text->setMaxLength(32);
		$form->addItem($text);
		
		$encoding = new ilSelectInputGUI($this->lng->txt('auth_radius_charset'),'charset');
		$encoding->setRequired(true);
		$encoding->setOptions($this->prepareCharsetSelection());
		$encoding->setValue($this->settings->getCharset());
		$encoding->setInfo($this->lng->txt('auth_radius_charset_info'));
		$form->addItem($encoding);
		
		// User synchronization
		// 0: Disabled
		// 1: Radius
		// 2: LDAP
		$sync = new ilRadioGroupInputGUI($this->lng->txt('auth_radius_sync'), 'sync');
		$sync->setRequired(true);
		#$sync->setInfo($this->lng->txt('auth_radius_sync_info'));
		$form->addItem($sync);

		// Disabled
		$dis = new ilRadioOption(
			$this->lng->txt('disabled'),
			ilRadiusSettings::SYNC_DISABLED,
			''
		);
		#$dis->setInfo($this->lng->txt('auth_radius_sync_disabled_info'));
		$sync->addOption($dis);

		// Radius
		$rad = new ilRadioOption(
			$this->lng->txt('auth_radius_sync_rad'),
			ilRadiusSettings::SYNC_RADIUS,
			''
		);
		$rad->setInfo($this->lng->txt('auth_radius_sync_rad_info'));
		$sync->addOption($rad);

		$select = new ilSelectInputGUI($this->lng->txt('auth_radius_role_select'),'role');
		$select->setOptions($this->prepareRoleSelection());
		$select->setValue($this->settings->getDefaultRole());
		$rad->addSubItem($select);

		$migr = new ilCheckboxInputGUI($this->lng->txt('auth_rad_migration'),'migration');
		$migr->setInfo($this->lng->txt('auth_rad_migration_info'));
		$migr->setChecked($this->settings->isAccountMigrationEnabled() ? 1 : 0);
		$migr->setValue(1);
		$rad->addSubItem($migr);

		// LDAP
		include_once './Services/LDAP/classes/class.ilLDAPServer.php';
		$server_ids = ilLDAPServer::getAvailableDataSources(AUTH_RADIUS);
		
		if(count($server_ids))
		{
			$ldap = new ilRadioOption(
				$this->lng->txt('auth_radius_ldap'),
				ilRadiusSettings::SYNC_LDAP,
				''
			);
			$ldap->setInfo($this->lng->txt('auth_radius_ldap_info'));
			$sync->addOption($ldap);

			// TODO Handle more than one LDAP configuration
		}

		if(ilLDAPServer::isDataSourceActive(AUTH_RADIUS))
		{
			$sync->setValue(ilRadiusSettings::SYNC_LDAP);
		}
		else
		{
			$sync->setValue(
				$this->settings->enabledCreation() ?
					ilRadiusSettings::SYNC_RADIUS :
					ilRadiusSettings::SYNC_DISABLED);
		}

		$form->addCommandButton('save',$this->lng->txt('save'));
		$this->tpl->setVariable('SETTINGS_TABLE',$form->getHTML());
	}
	
	/**
	 * Save
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		$this->settings->setActive((int) $_POST['active']);
		$this->settings->setName(ilUtil::stripSlashes($_POST['name']));
	 	$this->settings->setPort(ilUtil::stripSlashes($_POST['port']));
	 	$this->settings->setSecret(ilUtil::stripSlashes($_POST['secret']));
	 	$this->settings->setServerString(ilUtil::stripSlashes($_POST['servers']));
	 	$this->settings->setDefaultRole((int) $_POST['role']);
	 	$this->settings->enableAccountMigration((int) $_POST['migration']);
	 	$this->settings->setCharset((int) $_POST['charset']);
		$this->settings->enableCreation(((int) $_POST['sync'] == ilRadiusSettings::SYNC_RADIUS) ? true : false);

		if(!$this->settings->validateRequired())
	 	{
	 		ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields"));
	 		$this->settings();
			return false;
	 	}
	 	if(!$this->settings->validatePort())
	 	{
	 		ilUtil::sendFailure($this->lng->txt("err_invalid_port"));
	 		$this->settings();
	 		return false;
	 	}
	 	if(!$this->settings->validateServers())
	 	{
	 		ilUtil::sendFailure($this->lng->txt("err_invalid_server"));
	 		$this->settings();
	 		return false;
	 	}

		include_once './Services/LDAP/classes/class.ilLDAPServer.php';
		switch((int) $_POST['sync'])
		{
			case ilRadiusSettings::SYNC_DISABLED:
				ilLDAPServer::toggleDataSource(AUTH_RADIUS,false);
				break;

			case ilRadiusSettings::SYNC_RADIUS:
				ilLDAPServer::toggleDataSource(AUTH_RADIUS,false);
				break;

			case ilRadiusSettings::SYNC_LDAP:
				// TODO: handle multiple ldap configurations
				ilLDAPServer::toggleDataSource(AUTH_RADIUS,true);
				break;
		}

	 	$this->settings->save();
	 	ilUtil::sendSuccess($this->lng->txt('settings_saved'));
	 	$this->settings();
	 	return true;
	}
	
	
	/**
	 * Init Server settings
	 *
	 * @access private
	 * 
	 */
	private function initSettings()
	{
	 	include_once('Services/Radius/classes/class.ilRadiusSettings.php');
	 	$this->settings = ilRadiusSettings::_getInstance();
	 	
	 	
	}
	
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
	
	/**
	 * Get charset options
	 *
	 * @access private
	 * 
	 */
	private function prepareCharsetSelection()
	{
	 	return $select = array(ilRadiusSettings::RADIUS_CHARSET_UTF8 => 'UTF-8',
	 			ilRadiusSettings::RADIUS_CHARSET_LATIN1 => 'ISO-8859-1');
	}
	
}
?>