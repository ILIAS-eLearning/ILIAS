<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilAuthShibbolethSettingsGUI: 
* @ingroup AuthShibboleth 
*/
class ilAuthShibbolethSettingsGUI
{
	private $ctrl;
	private $ilias;
	private $tabs_gui;
	private $lng;
	private $tpl;
	private $ref_id;


	/**
	 * 
	 * @param
	 * @return
	 */
	public function __construct($a_auth_ref_id)
	{
		global $lng,$ilCtrl,$tpl,$ilTabs,$ilias;
		
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->ilias = $ilias;
		
		$this->tpl = $tpl;

		$this->ref_id = $a_auth_ref_id;
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
	}
	
	/**
	 * Execute Command
	 * @return void
	 */
	public function executeCommand()
	{
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('read','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->WARNING);
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
	
	public function settings()
	{
		global $rbacsystem, $rbacreview;
		
		$this->tabs_gui->setTabActive('auth_shib');
		
		// set already saved data or default value for port
		$settings = $this->ilias->getAllSettings();
		
		// Compose role list
		$role_list = $rbacreview->getRolesByFilter(2);
		$selectElement = '<select name="shib[user_default_role]">';
		
		if (!isset($settings["shib_user_default_role"]))
		{
			$settings["shib_user_default_role"] = 4;
		}
			
		foreach ($role_list as $role)
		{
			$selectElement .= '<option value="'.$role['obj_id'].'"';
			if ($settings["shib_user_default_role"] == $role['obj_id'])
				$selectElement .= 'selected="selected"';
			
			$selectElement .= '>'.$role['title'].'</option>';
		}
		$selectElement .= '</select>';
		
		
		// Set text field content
		$shib_settings = array(
								'shib_login',
								'shib_title',
								'shib_firstname',
								'shib_lastname',
								'shib_email',
								'shib_gender',
								'shib_institution',
								'shib_department',
								'shib_zipcode',
								'shib_city',
								'shib_country',
								'shib_street',
								'shib_phone_office',
								'shib_phone_home',
								'shib_phone_mobile',
								'shib_language'
								);
		
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.auth_shib.html');
		
		foreach ($shib_settings as $setting)
		{
			$field = ereg_replace('shib_','',$setting);
			$this->tpl->setVariable(strtoupper($setting), $settings[$setting]);
			$this->tpl->setVariable('SHIB_UPDATE_'.strtoupper($field), $settings["shib_update_".$field]);
			
			if ($settings["shib_update_".$field])
				$this->tpl->setVariable('CHK_SHIB_UPDATE_'.strtoupper($field), 'checked="checked"');
		}
		
		// Set some default values
		
		if (!isset($settings["shib_login_button"]) || $settings["shib_login_button"] == ''){
			$this->tpl->setVariable("SHIB_LOGIN_BUTTON", "templates/default/images/shib_login_button.png");
		}
		
		if (isset($settings["shib_active"]) && $settings["shib_active"])
		{
			$this->tpl->setVariable("chk_shib_active", 'checked="checked"');
		}
		if ($settings['shib_auth_allow_local'] == '1')
		{
			$this->tpl->setVariable('CHK_SHIB_AUTH_ALLOW_LOCAL', 'checked="checked"');
		}
		
		if (
			!isset($settings["shib_hos_type"])
			|| $settings["shib_hos_type"] == ''
			|| $settings["shib_hos_type"] != 'external_wayf'
			)
		{
			$this->tpl->setVariable("CHK_SHIB_LOGIN_INTERNAL_WAYF", 'checked="checked"');
			$this->tpl->setVariable("CHK_SHIB_LOGIN_EXTERNAL_WAYF", '');
		} else {
			$this->tpl->setVariable("CHK_SHIB_LOGIN_INTERNAL_WAYF", '');
			$this->tpl->setVariable("CHK_SHIB_LOGIN_EXTERNAL_WAYF", 'checked="checked"');
		}
		
		if (!isset($settings["shib_idp_list"]) || $settings["shib_idp_list"] == '')
		{
			$this->tpl->setVariable("SHIB_IDP_LIST", "urn:mace:organization1:providerID, Example Organization 1\nurn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai");
		} else {
			$this->tpl->setVariable("SHIB_IDP_LIST", stripslashes($settings["shib_idp_list"]));
		}
		
		$this->tpl->setVariable("SHIB_USER_DEFAULT_ROLE", $selectElement);
		$this->tpl->setVariable("SHIB_LOGIN_BUTTON", $settings["shib_login_button"]);
		$this->tpl->setVariable("SHIB_LOGIN_INSTRUCTIONS", stripslashes($settings["shib_login_instructions"]));
		$this->tpl->setVariable("SHIB_FEDERATION_NAME", stripslashes($settings["shib_federation_name"]));
		$this->tpl->setVariable("SHIB_DATA_CONV", $settings["shib_data_conv"]);
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SHIB_INSTRUCTIONS",
			$this->lng->txt("auth_shib_instructions"));
		$this->tpl->setVariable("LINK_SHIB_INSTRUCTIONS",
			"./Services/AuthShibboleth/README.SHIBBOLETH.txt");
		$this->tpl->setVariable("TXT_SHIB", $this->lng->txt("shib"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_SHIB_UPDATE", $this->lng->txt("shib_update"));
		$this->tpl->setVariable("TXT_SHIB_ACTIVE", $this->lng->txt("shib_active"));
		$this->tpl->setVariable("TXT_SHIB_USER_DEFAULT_ROLE", $this->lng->txt("shib_user_default_role"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $this->lng->txt("shib_login_button"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_TYPE", $this->lng->txt("shib_login_type"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_INTERNAL_WAYF", $this->lng->txt("shib_login_internal_wayf"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_EXTERNAL_WAYF", $this->lng->txt("shib_login_external_wayf"));
		$this->tpl->setVariable("TXT_SHIB_IDP_LIST", $this->lng->txt("shib_idp_list"));
		$this->tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $this->lng->txt("shib_federation_name"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", $this->lng->txt("auth_login_instructions"));
		$this->tpl->setVariable("TXT_SHIB_DATA_CONV", $this->lng->txt("shib_data_conv"));
		$this->tpl->setVariable("TXT_SHIB_AUTH_ALLOW_LOCAL", $this->lng->txt("auth_allow_local"));
		foreach ($shib_settings as $setting)
		{
			$this->tpl->setVariable("TXT_".strtoupper($setting), $this->lng->txt($setting));
		}
		
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
	}

	public function save() 
	{
        global $ilUser;

        // validate required data 
		if (
			!$_POST["shib"]["login"] 
			or !$_POST["shib"]["hos_type"] 
			or !$_POST["shib"]["firstname"] 
			or !$_POST["shib"]["lastname"] 
			or !$_POST["shib"]["email"] 
			or !$_POST["shib"]["user_default_role"]
			or !$_POST["shib"]["federation_name"]
			)
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate api
		if (
			$_POST["shib"]["data_conv"] 
			and $_POST["shib"]["data_conv"] != '' 
			and !is_readable($_POST["shib"]["data_conv"]) )
		{
			$this->ilias->raiseError($this->lng->txt("shib_data_conv_warning"),$this->ilias->error_obj->MESSAGE);
		}
		
		// all ok. save settings
		$shib_settings = array(
								'shib_login',
								'shib_title',
								'shib_firstname',
								'shib_lastname',
								'shib_email',
								'shib_gender',
								'shib_institution',
								'shib_department',
								'shib_zipcode',
								'shib_city',
								'shib_country',
								'shib_street',
								'shib_phone_office',
								'shib_phone_home',
								'shib_phone_mobile',
								'shib_language'
								);
		
		foreach ($shib_settings as $setting)
		{
			$field = ereg_replace('shib_','',$setting);
			if ($_POST["shib"]["update_".$field] != "1")
				$_POST["shib"]["update_".$field] = "0";
			$this->ilias->setSetting($setting, trim($_POST["shib"][$field]));
			$this->ilias->setSetting("shib_update_".$field, $_POST["shib"]["update_".$field]);
		}
		
		if ($_POST["shib"]["active"] != "1")
		{
			$this->ilias->setSetting("shib_active", "0");
		}
		else
		{
			$this->ilias->setSetting("shib_active", "1");
		}
		
		$this->ilias->setSetting("shib_user_default_role", $_POST["shib"]["user_default_role"]);
		$this->ilias->setSetting("shib_hos_type", $_POST["shib"]["hos_type"]);
		$this->ilias->setSetting("shib_federation_name", $_POST["shib"]["federation_name"]);
		$this->ilias->setSetting("shib_idp_list", $_POST["shib"]["idp_list"]);
		$this->ilias->setSetting("shib_login_instructions", $_POST["shib"]["login_instructions"]);
		$this->ilias->setSetting("shib_login_button", $_POST["shib"]["login_button"]);
		$this->ilias->setSetting("shib_data_conv", $_POST["shib"]["data_conv"]);
		$this->ilias->setSetting("shib_auth_allow_local", ($_POST['shib']['auth_allow_local']=='1') ? '1' : '0');
	
		ilUtil::sendInfo($this->lng->txt("shib_settings_saved"),true);

		$this->ctrl->redirect($this,'settings');
	}

}
?>