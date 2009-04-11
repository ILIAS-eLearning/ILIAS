<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* @defgroup ServicesRadius
* 
* @author Stefan Meyer <smeyer@databay.de>
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
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_write'),$ilErr->WARNING);
		}
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

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
		
		
		$check = new ilCheckboxInputGUI($this->lng->txt('auth_radius_sync'),'sync');
		$check->setInfo($this->lng->txt('auth_radius_sync_info'));
		$check->setChecked($this->settings->enabledCreation() ? 1 : 0);
		$check->setValue(1);
		
		
		$select = new ilSelectInputGUI($this->lng->txt('auth_radius_role_select'),'role');
		$select->setOptions($this->prepareRoleSelection());
		$select->setValue($this->settings->getDefaultRole());
		$check->addSubItem($select);

		$migr = new ilCheckboxInputGUI($this->lng->txt('auth_rad_migration'),'migration');
		$migr->setInfo($this->lng->txt('auth_rad_migration_info'));
		$migr->setChecked($this->settings->isAccountMigrationEnabled() ? 1 : 0);
		$migr->setValue(1);
		$check->addSubItem($migr);
		$form->addItem($check);
		
		

		$form->addCommandButton('save',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
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
	 	$this->settings->enableCreation((int) $_POST['sync']);
	 	$this->settings->enableAccountMigration((int) $_POST['migration']);
	 	$this->settings->setCharset((int) $_POST['charset']);
	 	
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