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
* Class ilObjAuthSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjAuthSettingsGUI: ilPermissionGUI, ilRegistrationSettingsGUI, ilLDAPSettingsGUI, ilRadiusSettingsGUI
* 
* @extends ilObjectGUI
*/


require_once "class.ilObjectGUI.php";

class ilObjAuthSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjAuthSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "auth";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('registration');

		define('LDAP_DEFAULT_PORT',389);
		define('RADIUS_DEFAULT_PORT',1812);
	}

	function viewObject()
	{
		// load ilRegistrationSettingsGUI

		include_once './Services/Registration/classes/class.ilRegistrationSettingsGUI.php';
		
		// Enable tabs
		$this->tabs_gui->setTabActive('registration_settings');
		
		$registration_gui =& new ilRegistrationSettingsGUI();
		$this->ctrl->setCmdClass('ilregistrationsettingsgui');
		$this->ctrl->forwardCommand($registration_gui);
	}


	/**
	* display settings menu
	* 
	* @access	public
	*/
	function authSettingsObject()
	{
		global $rbacsystem, $ilSetting;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('authentication_settings');
		$this->setSubTabs('authSettings');		
		$this->tabs_gui->setSubTabActive("auth_settings");		
		
		$this->getTemplateFile("general");
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_AUTH_TITLE", $this->lng->txt("auth_select"));
		
		$this->tpl->setVariable("TXT_AUTH_MODE", $this->lng->txt("auth_mode"));
		$this->tpl->setVariable("TXT_AUTH_DEFAULT", $this->lng->txt("default"));
		$this->tpl->setVariable("TXT_AUTH_ACTIVE", $this->lng->txt("active"));
		$this->tpl->setVariable("TXT_AUTH_NUM_USERS", $this->lng->txt("num_users"));

		$this->tpl->setVariable("TXT_LOCAL", $this->lng->txt("auth_local"));
		$this->tpl->setVariable("TXT_LDAP", $this->lng->txt("auth_ldap"));
		$this->tpl->setVariable("TXT_SHIB", $this->lng->txt("auth_shib"));
		
		$this->tpl->setVariable("TXT_CAS", $this->lng->txt("auth_cas"));

		$this->tpl->setVariable("TXT_RADIUS", $this->lng->txt("auth_radius"));
		$this->tpl->setVariable("TXT_SCRIPT", $this->lng->txt("auth_script"));

		$auth_cnt = ilObjUser::_getNumberOfUsersPerAuthMode();
		$auth_modes = ilAuthUtils::_getAllAuthModes();

		foreach($auth_modes as $mode => $mode_name)
		{
//echo "-".$ilSetting->get('auth_mode')."-".$mode."-";
			if ($ilSetting->get('auth_mode') == $mode)
			{
				$this->tpl->setVariable("NUM_".strtoupper($mode_name),
					((int) $auth_cnt[$mode_name] + $auth_cnt["default"])." (".$this->lng->txt("auth_per_default").
						": ".$auth_cnt["default"].")");
			}
			else
			{
				$this->tpl->setVariable("NUM_".strtoupper($mode_name),
					(int) $auth_cnt[$mode_name]);
			}
		}

		$this->tpl->setVariable("TXT_CONFIGURE", $this->lng->txt("auth_configure"));
		$this->tpl->setVariable("TXT_AUTH_REMARK", $this->lng->txt("auth_remark_non_local_auth"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "setAuthMode");
				
		// local vars
		$checked = "checked=\"checked\"";
		$disabled = "disabled=\"disabled\"";
		$style_disabled = "_disabled";
		
		// icon handlers
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$this->lng->txt("enabled")."\" title=\"".$this->lng->txt("enabled")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$this->lng->txt("disabled")."\" title=\"".$this->lng->txt("disabled")."\" border=\"0\" vspace=\"0\"/>";

		$this->tpl->setVariable("AUTH_LOCAL_ACTIVE", $icon_ok);
		
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		$this->tpl->setVariable('AUTH_LDAP_ACTIVE',count(ilLDAPServer::_getActiveServerList()) ? $icon_ok : $icon_not_ok);
		#$this->tpl->setVariable("AUTH_LDAP_ACTIVE", $this->ilias->getSetting('ldap_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_RADIUS_ACTIVE", $this->ilias->getSetting('radius_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_SHIB_ACTIVE", $this->ilias->getSetting('shib_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_SCRIPT_ACTIVE", $this->ilias->getSetting('script_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_CAS_ACTIVE", $this->ilias->getSetting('cas_active') ? $icon_ok : $icon_not_ok);
		
		// alter style and disable buttons depending on current selection
		switch ($this->ilias->getSetting('auth_mode'))
		{
			case AUTH_LOCAL: // default
				$this->tpl->setVariable("CHK_LOCAL", $checked);
				break;
				
			case AUTH_LDAP: // LDAP
				$this->tpl->setVariable("CHK_LDAP", $checked);
				break;
				
			case AUTH_SHIBBOLETH: // SHIB
				$this->tpl->setVariable("CHK_SHIB", $checked);
				break;
				
			case AUTH_RADIUS: // RADIUS
				$this->tpl->setVariable("CHK_RADIUS", $checked);
				break;
			
			case AUTH_CAS: // CAS
				$this->tpl->setVariable("CHK_CAS", $checked);
				break;
				
			case AUTH_SCRIPT: // script
				$this->tpl->setVariable("CHK_SCRIPT", $checked);
				break;
		}
		
		// roles table
		
		$this->tpl->setVariable("FORMACTION_ROLES",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_AUTH_ROLES", $this->lng->txt("auth_active_roles"));
		$this->tpl->setVariable("TXT_ROLE", $this->lng->txt("obj_role"));
		$this->tpl->setVariable("TXT_ROLE_AUTH_MODE", $this->lng->txt("auth_role_auth_mode"));
		$this->tpl->setVariable("CMD_SUBMIT_ROLES", "updateAuthRoles");
		
		include_once("classes/class.ilObjRole.php");
		$reg_roles = ilObjRole::_lookupRegisterAllowed();
		
		// auth mode selection
		include_once('classes/class.ilAuthUtils.php');
		$active_auth_modes = ilAuthUtils::_getActiveAuthModes();

		foreach ($reg_roles as $role)
		{
			foreach ($active_auth_modes as $auth_name => $auth_key)
			{
				// do not list auth modes with external login screen
				// even not default, because it can easily be set to
				// a non-working auth mode
				if ($auth_name == "default" || $auth_name == "cas"
					|| $auth_name == "shibboleth" || $auth_name == 'ldap')
				{
					continue;
				}

				$this->tpl->setCurrentBlock("auth_mode_selection");

				if ($auth_name == 'default')
				{
					$name = $this->lng->txt('auth_'.$auth_name)." (".$this->lng->txt('auth_'.ilAuthUtils::_getAuthModeName($auth_key)).")";
				}
				else
				{
					$name = $this->lng->txt('auth_'.$auth_name);
				}

				$this->tpl->setVariable("AUTH_MODE_NAME", $name);

				$this->tpl->setVariable("AUTH_MODE", $auth_name);

				if ($role['auth_mode'] == $auth_name)
				{
					$this->tpl->setVariable("SELECTED_AUTH_MODE", "selected=\"selected\"");
				}

				$this->tpl->parseCurrentBlock();
			} // END auth_mode selection
			
			$this->tpl->setCurrentBlock("roles");
			$this->tpl->setVariable("ROLE", $role['title']);
			$this->tpl->setVariable("ROLE_ID", $role['id']);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * saves the login information data
	 *
	 * @access public
	 * @author Michael Jansen
	 * 
	 */
	public function saveLoginInfoObject()
	{		
		global $rbacsystem, $lng;		
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}		
		if (is_array($_POST["loginMessage"]))
		{
			$this->loginSettings = new ilSetting("login_settings");
			
			foreach ($_POST["loginMessage"] as $key => $val)
			{				
				$this->loginSettings->set("login_message_".$key, $val);
			}
		}
		
		ilUtil::sendInfo($this->lng->txt("login_information_settings_saved"));
		
		$this->loginInfoObject();
	}
	
	/**
	 * displays login information of all installed languages
	 *
	 * @access public
	 * @author Michael Jansen
	 */
	public function loginInfoObject()
	{
		global $rbacsystem, $lng;	
		
		if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
		}			

		$this->tabs_gui->setTabActive("authentication_settings");
		$this->setSubTabs("authSettings");		
		$this->tabs_gui->setSubTabActive("login_information");
		
		$lng->loadLanguageModule("meta");

		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.auth_login_messages.html");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('login_information'));
		#$form->setInfo($this->lng->txt('login_information_desc'));
		
		$form->addCommandButton('saveLoginInfo',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt("login_information"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("login_information_desc"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
					
		if (!is_object($this->loginSettings))
		{
			$this->loginSettings = new ilSetting("login_settings");
		} 
		
		$login_settings = $this->loginSettings->getAll();		
		$languages = $lng->getInstalledLanguages();
		$def_language = $lng->getDefaultLanguage();		
		
		foreach ($this->setDefLangFirst($def_language, $languages) as $lang_key)
		{						
			$add = "";
			if ($lang_key == $def_language)
			{
				$add = " (".$lng->txt("default").")";
			}			
			
			$textarea = new ilTextAreaInputGUI($lng->txt("meta_l_".$lang_key).$add,
				'loginMessage['.$lang_key.']');
			$textarea->setRows(10);
			$textarea->setValue($login_settings["login_message_".$lang_key]);
			$textarea->setUseRte(true);
			$form->addItem($textarea);
			
			unset($login_settings["login_message_".$lang_key]);
		}
				
		foreach ($login_settings as $key => $message)
		{
			$lang_key = substr($key, strrpos($key, "_") + 1, strlen($key) - strrpos($key, "_"));
			
			$textarea = new ilTextAreaInputGUI($lng->txt("meta_l_".$lang_key).$add,
				'loginMessage['.$lang_key.']');
			$textarea->setRows(10);
			$textarea->setValue($message);
			$textarea->setUseRte(true);
			
			if(!in_array($lang_key,$languages))
			{
				$textarea->setAlert($lng->txt("not_installed"));
			}
			$form->addItem($textarea);
		}
		$this->tpl->setVariable('LOGIN_INFO',$form->getHTML());
	}
	
	/**
	 * 
	 * returns an array of all installed languages, default language at the first position 
	 *
	 * @param string $a_def_language Default language of the current installation
	 * @param array $a_languages Array of all installed languages
	 * @return array $languages Array of the installed languages, default language at first position
	 * @access public
	 * @author Michael Jansen
	 * 
	 */
	public function setDefLangFirst($a_def_language, $a_languages)
	{		
		if (is_array($a_languages) && $a_def_language != "")
		{
			$languages = array();
			$languages[] = $a_def_language;
			
			foreach ($a_languages as $val)
			{					
				if (!in_array($val, $languages))
				{					
					$languages[] = $val;
				}	
			}			
			
			return $languages;
		}
		else
		{		
			return array();
		}
	}
	
	function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "authSettings");
	}

	function setAuthModeObject()
	{
		global $rbacsystem,$ilSetting;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if (empty($_POST["auth_mode"]))
		{
			$this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"),$this->ilias->error_obj->MESSAGE);
		}

		if ($_POST["auth_mode"] == AUTH_DEFAULT)
		{
			ilUtil::sendInfo($this->lng->txt("auth_mode").": ".$this->getAuthModeTitle()." ".$this->lng->txt("auth_mode_not_changed"),true);
			$this->ctrl->redirect($this,'authSettings');
		}

		switch ($_POST["auth_mode"])
		{
			case AUTH_LDAP:
				// First update all users external accounts
				ilObjUser::_updateExternalAccounts('default');
				
				/*
				if ($this->object->checkAuthLDAP() !== true)
				{
					ilUtil::sendInfo($this->lng->txt("auth_ldap_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editLDAP")));
				}
				*/
				break;
				
				case AUTH_SHIB:
				if ($this->object->checkAuthSHIB() !== true)
				{
					ilUtil::sendInfo($this->lng->txt("auth_shib_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editSHIB")));
				}
				break;

			case AUTH_RADIUS:
				if ($this->object->checkAuthRADIUS() !== true)
				{
					ilUtil::sendInfo($this->lng->txt("auth_radius_not_configured"),true);
					$this->ctrl->redirect($this,'editRADIUS');
				}
				break;

			case AUTH_SCRIPT:
				if ($this->object->checkAuthScript() !== true)
				{
					ilUtil::sendInfo($this->lng->txt("auth_script_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editScript")));
				}
				break;
		}
		
		$this->ilias->setSetting("auth_mode",$_POST["auth_mode"]);
		
		ilUtil::sendInfo($this->lng->txt("auth_default_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		$this->ctrl->redirect($this,'authSettings');
	}
	
	/**
	* Configure LDAP settings
	* 
	* @access	public
	*/
	function editLDAPObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('auth_ldap');

		if ($_SESSION["error_post_vars"])
		{
			if ($_SESSION["error_post_vars"]["ldap"]["active"] == "1")
			{
				$this->tpl->setVariable("CHK_LDAP_ACTIVE", "checked=\"checked\"");
			}
			
			if ($_SESSION["error_post_vars"]["ldap"]["tls"] == "1")
			{
				$this->tpl->setVariable("LDAP_TLS_CHK", "checked=\"checked\"");
			}
			
			if ($_SESSION["error_post_vars"]["ldap"]["version"] == "3")
			{
				$this->tpl->setVariable("LDAP_VERSION3_CHK", "checked=\"checked\"");
			}
			else
			{
				$this->tpl->setVariable("LDAP_VERSION2_CHK", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("LDAP_SERVER", $_SESSION["error_post_vars"]["ldap"]["server"]);
			$this->tpl->setVariable("LDAP_BASEDN", $_SESSION["error_post_vars"]["ldap"]["basedn"]);
			$this->tpl->setVariable("LDAP_SEARCH_BASE", $_SESSION["error_post_vars"]["ldap"]["search_base"]);
			$this->tpl->setVariable("LDAP_PORT", $_SESSION["error_post_vars"]["ldap"]["port"]);
			$this->tpl->setVariable("LDAP_LOGIN_KEY", $_SESSION["error_post_vars"]["ldap"]["login_key"]);
			$this->tpl->setVariable("LDAP_OBJECTCLASS", $_SESSION["error_post_vars"]["ldap"]["objectclass"]);
		}
		else
		{
			// set already saved data or default value for port
			$settings = $this->ilias->getAllSettings();
			
			if ($settings["ldap_active"] == "1")
			{
				$this->tpl->setVariable("CHK_LDAP_ACTIVE", "checked=\"checked\"");
			}

			if ($settings["ldap_tls"] == "1")
			{
				$this->tpl->setVariable("LDAP_TLS_CHK", "checked=\"checked\"");
			}

			$this->tpl->setVariable("LDAP_SERVER", $settings["ldap_server"]);
			$this->tpl->setVariable("LDAP_BASEDN", $settings["ldap_basedn"]);
			$this->tpl->setVariable("LDAP_SEARCH_BASE", $settings["ldap_search_base"]);
			
			if (empty($settings["ldap_port"]))
			{
				$this->tpl->setVariable("LDAP_PORT", LDAP_DEFAULT_PORT);
			}
			else
			{
				$this->tpl->setVariable("LDAP_PORT", $settings["ldap_port"]);			
			}

			if (empty($settings["ldap_login_key"]))
			{
				$this->tpl->setVariable("LDAP_LOGIN_KEY", "uid");
			}
			else
			{
				$this->tpl->setVariable("LDAP_LOGIN_KEY", $settings["ldap_login_key"]);			
			}
			
			if (empty($settings["ldap_objectclass"]))
			{
				$this->tpl->setVariable("LDAP_OBJECTCLASS", "posixAccount");
			}
			else
			{
				$this->tpl->setVariable("LDAP_OBJECTCLASS", $settings["ldap_objectclass"]);
			}

			if (empty($settings["ldap_version"]) or $settings["ldap_version"] == "2")
			{
				$this->tpl->setVariable("LDAP_VERSION2_CHK", "checked=\"checked\"");
			}
			else
			{
				$this->tpl->setVariable("LDAP_VERSION3_CHK", "checked=\"checked\"");			
			}
		}

		$this->getTemplateFile("ldap");
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_LDAP_TITLE", $this->lng->txt("ldap_configure"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_LDAP_ACTIVE", $this->lng->txt("auth_ldap_enable"));
		$this->tpl->setVariable("TXT_LDAP_TLS", $this->lng->txt("ldap_tls"));
		$this->tpl->setVariable("TXT_LDAP_SERVER", $this->lng->txt("ldap_server"));
		$this->tpl->setVariable("TXT_LDAP_BASEDN", $this->lng->txt("ldap_basedn"));
		$this->tpl->setVariable("TXT_LDAP_SEARCH_BASE", $this->lng->txt("ldap_search_base"));
		$this->tpl->setVariable("TXT_LDAP_PORT", $this->lng->txt("ldap_port"));
		$this->tpl->setVariable("TXT_LDAP_TLS", $this->lng->txt("ldap_tls"));

		$this->tpl->setVariable("TXT_LDAP_VERSION", $this->lng->txt("ldap_version"));
		$this->tpl->setVariable("TXT_LDAP_VERSION2", $this->lng->txt("ldap_v2"));
		$this->tpl->setVariable("TXT_LDAP_VERSION3", $this->lng->txt("ldap_v3"));

		$this->tpl->setVariable("TXT_LDAP_LOGIN_KEY", $this->lng->txt("ldap_login_key"));
		$this->tpl->setVariable("TXT_LDAP_OBJECTCLASS", $this->lng->txt("ldap_objectclass"));
				
		$this->tpl->setVariable("TXT_LDAP_PASSWD", $this->lng->txt("ldap_passwd"));

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveLDAP");
	}


	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveLDAPObject()
	{
        global $ilUser;

        // validate required data 
		if (!$_POST["ldap"]["server"] or !$_POST["ldap"]["basedn"] or !$_POST["ldap"]["port"] or !$_POST["ldap"]["login_key"] or !$_POST["ldap"]["objectclass"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate password 
		if (!$_POST["ldap"]["passwd"])
		{
			$this->ilias->raiseError($this->lng->txt("err_enter_current_passwd"),$this->ilias->error_obj->MESSAGE);
		}

		// validate port
		if ((preg_match("/^[0-9]{0,5}$/",$_POST["ldap"]["port"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_port"),$this->ilias->error_obj->MESSAGE);
		}
		
		// TODO: implement TLS support
		if ($_POST["ldap"]["tls"] != "1")
		{
			$_POST["ldap"]["tls"] = "0";
		}	
		
		// check connection to ldap server
		//include_once('classes/class.ilLDAPAuthentication.php');
		
		$ldap_host	= $_POST["ldap"]["server"];
		$ldap_port	= $_POST["ldap"]["port"];
		$ldap_pass	= $_POST["ldap"]["passwd"];
		
		$ldap_userattr = $_POST["ldap"]["login_key"];
		$ldap_useroc = $_POST["ldap"]["objectclass"];

		$ldap_dn	= $ldap_userattr."=".$this->ilias->account->getLogin().",";

        // create base_dn
        if ($_POST["ldap"]["search_base"])
		{
			$ldap_searchbase .= $_POST["ldap"]["search_base"].",";
		}
		
		$ldap_searchbase 	.= $_POST["ldap"]["basedn"];
		
		$ldap_dn .= $ldap_searchbase;
		
		// test connection
		$ldap_conn = ldap_connect($ldap_host,$ldap_port);

		@ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $_POST["ldap"]["version"]);
		
		// bind anonymously
		if (($ldap_bind = ldap_bind($ldap_conn)) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_ldap_connect_failed"),$this->ilias->error_obj->MESSAGE);
		}

        // make user search
        $filter = sprintf('(&(objectClass=%s)(%s=%s))', $ldap_useroc, $ldap_userattr, $ilUser->getLogin());

        // make functions params array
        $func_params = array($ldap_conn, $ldap_searchbase, $filter, array($ldap_userattr));

        // search
        if (($result_id = @call_user_func_array('ldap_search', $func_params)) == false)
        {
   			$this->ilias->raiseError($this->lng->txt("err_ldap_search_failed"),$this->ilias->error_obj->MESSAGE);
        }

        if (ldap_count_entries($ldap_conn, $result_id) != 1)
        {
   			$this->ilias->raiseError($this->lng->txt("err_ldap_user_not_found"),$this->ilias->error_obj->MESSAGE);
        }

        // then get the user dn
        $entry_id = ldap_first_entry($ldap_conn, $result_id);
        $user_dn  = ldap_get_dn($ldap_conn, $entry_id);

        ldap_free_result($result_id);

        // bind with password
        if (@ldap_bind($ldap_conn, $user_dn, $ldap_pass) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_ldap_auth_failed"),$this->ilias->error_obj->MESSAGE);
		}

		// close connection
		@ldap_unbind($ldap_conn);

		// all ok. save settings
		$this->ilias->setSetting("ldap_tls", $_POST["ldap"]["tls"]);
		$this->ilias->setSetting("ldap_server", $_POST["ldap"]["server"]);
		$this->ilias->setSetting("ldap_basedn", $_POST["ldap"]["basedn"]);
		$this->ilias->setSetting("ldap_search_base", $_POST["ldap"]["search_base"]);
		$this->ilias->setSetting("ldap_port", $_POST["ldap"]["port"]);
		$this->ilias->setSetting("ldap_version", $_POST["ldap"]["version"]);
		$this->ilias->setSetting("ldap_login_key", $_POST["ldap"]["login_key"]);
		$this->ilias->setSetting("ldap_objectclass", $_POST["ldap"]["objectclass"]);
		$this->ilias->setSetting("ldap_active", $_POST["ldap"]["active"]);

		ilUtil::sendInfo($this->lng->txt("auth_ldap_settings_saved"),true);
		$this->ctrl->redirect($this,'editLDAP');;
	}

	/**
	* Configure SHIB settings
	* 
	* @access	public
	*/
	function editSHIBObject()
	{
		global $rbacsystem, $rbacreview;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('auth_shib');
		
		// set already saved data or default value for port
		$settings = $this->ilias->getAllSettings();
		
		// Compose role list
		$role_list = $rbacreview->getRolesByFilter(2,$this->object->getId());
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
		
		$this->getTemplateFile("shib");
		
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
			$this->tpl->setVariable("SHIB_LOGIN_BUTTON", "templates/default/images/shib_login_button.gif");
		}
		
		if (isset($settings["shib_active"]) && $settings["shib_active"])
		{
			$this->tpl->setVariable("chk_shib_active", 'checked="checked"');
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
		foreach ($shib_settings as $setting)
		{
			$this->tpl->setVariable("TXT_".strtoupper($setting), $this->lng->txt($setting));
		}
		
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveSHIB");
		
	}

	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveSHIBObject()
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
	
		ilUtil::sendInfo($this->lng->txt("shib_settings_saved"),true);

		$this->ctrl->redirect($this,'editSHIB');
	}

	/**
	* Configure cas settings
	* 
	* @access	public
	*/
	function editCASObject()
	{
		global $rbacsystem, $rbacreview, $ilSetting;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('auth_cas');
		
		// get template
		$this->getTemplateFile("cas");
		
		// get all settings
		$settings = $ilSetting->getAll();
		
		// get values in error case
		if ($_SESSION["error_post_vars"])
		{
			if ($_SESSION["error_post_vars"]["cas"]["active"] == "1")
			{
				$this->tpl->setVariable("CHK_CAS_ACTIVE", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["cas"]["create_users"] == "1")
			{
				$this->tpl->setVariable("CHK_CREATE_USERS", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["cas"]["allow_local"] == "1")
			{
				$this->tpl->setVariable("CHK_ALLOW_LOCAL", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("CAS_SERVER", $_SESSION["error_post_vars"]["cas"]["server"]);
			$this->tpl->setVariable("CAS_PORT", $_SESSION["error_post_vars"]["cas"]["port"]);
			$this->tpl->setVariable("CAS_URI", $_SESSION["error_post_vars"]["cas"]["uri"]);
			$this->tpl->setVariable("CAS_LOGIN_INSTRUCTIONS", $_SESSION["error_post_vars"]["cas"]["login_instructions"]);
			$current_default_role = $_SESSION["error_post_vars"]["cas"]["user_default_role"];
		}
		else
		{
			if ($settings["cas_active"] == "1")
			{
				$this->tpl->setVariable("CHK_CAS_ACTIVE", "checked=\"checked\"");
			}
			if ($settings["cas_create_users"] == "1")
			{
				$this->tpl->setVariable("CHK_CREATE_USERS", "checked=\"checked\"");
			}
			if ($settings["cas_allow_local"] == "1")
			{
				$this->tpl->setVariable("CHK_ALLOW_LOCAL", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("CAS_SERVER", $settings["cas_server"]);
			$this->tpl->setVariable("CAS_PORT", $settings["cas_port"]);
			$this->tpl->setVariable("CAS_URI", $settings["cas_uri"]);
			$this->tpl->setVariable("CAS_LOGIN_INSTRUCTIONS", $settings["cas_login_instructions"]);			
			$current_default_role = $settings["cas_user_default_role"];
		}
		
		// compose role list
		$role_list = $rbacreview->getRolesByFilter(2,$this->object->getId());
		if (!$current_default_role)
		{
			$current_default_role = 4;
		}
		$roles = array();
		foreach ($role_list as $role)
		{
			$roles[$role['obj_id']] = $role['title'];
		}
		$selectElement = ilUtil::formSelect($current_default_role,
			"cas[user_default_role]", $roles, false, true);
		
		$this->tpl->setVariable("CAS_USER_DEFAULT_ROLE", $selectElement);		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_CAS_TITLE", $this->lng->txt("auth_cas_auth"));
		$this->tpl->setVariable("TXT_CAS_DESC", $this->lng->txt("auth_cas_auth_desc"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_CAS_ACTIVE", $this->lng->txt("active"));
		$this->tpl->setVariable("TXT_CAS_SERVER", $this->lng->txt("server"));
		$this->tpl->setVariable("TXT_CAS_SERVER_DESC", $this->lng->txt("auth_cas_server_desc"));
		$this->tpl->setVariable("TXT_CAS_PORT", $this->lng->txt("port"));
		$this->tpl->setVariable("TXT_CAS_PORT_DESC", $this->lng->txt("auth_cas_port_desc"));
		$this->tpl->setVariable("TXT_CAS_URI", $this->lng->txt("uri"));
		$this->tpl->setVariable("TXT_CAS_URI_DESC", $this->lng->txt("auth_cas_uri_desc"));
		$this->tpl->setVariable("TXT_CAS_LOGIN_INSTRUCTIONS", $this->lng->txt("auth_login_instructions"));
		$this->tpl->setVariable("TXT_CREATE_USERS", $this->lng->txt("auth_create_users"));
		$this->tpl->setVariable("TXT_CREATE_USERS_DESC", $this->lng->txt("auth_cas_create_users_desc"));
		$this->tpl->setVariable("TXT_CAS_USER_DEFAULT_ROLE", $this->lng->txt("auth_user_default_role"));
		$this->tpl->setVariable("TXT_CAS_USER_DEFAULT_ROLE_DESC",
			$this->lng->txt("auth_cas_user_default_role_desc"));
		$this->tpl->setVariable("TXT_ALLOW_LOCAL", $this->lng->txt("auth_allow_local"));
		$this->tpl->setVariable("TXT_ALLOW_LOCAL_DESC", $this->lng->txt("auth_cas_allow_local_desc"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveCAS");
	}
	
	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveCASObject()
	{
         global $ilUser, $ilSetting;

        // validate required data 
		if (!$_POST["cas"]["server"] or !$_POST["cas"]["port"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate port
		if ((preg_match("/^[0-9]{0,5}$/",$_POST["cas"]["port"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_port"),$this->ilias->error_obj->MESSAGE);
		}
		
		$ilSetting->set("cas_server", $_POST["cas"]["server"]);
		$ilSetting->set("cas_port", $_POST["cas"]["port"]);
		$ilSetting->set("cas_uri", $_POST["cas"]["uri"]);
		$ilSetting->set("cas_login_instructions", $_POST["cas"]["login_instructions"]);
		$ilSetting->set("cas_active", $_POST["cas"]["active"]);
		$ilSetting->set("cas_create_users", $_POST["cas"]["create_users"]);
		$ilSetting->set("cas_allow_local", $_POST["cas"]["allow_local"]);
		$ilSetting->set("cas_active", $_POST["cas"]["active"]);
		$ilSetting->set("cas_user_default_role", $_POST["cas"]["user_default_role"]);
		ilUtil::sendInfo($this->lng->txt("auth_cas_settings_saved"),true);
		
		$this->ctrl->redirect($this,'editCAS');
	}

	/**
	* Configure soap settings
	* 
	* @access	public
	*/
	function editSOAPObject()
	{
		global $rbacsystem, $rbacreview, $ilSetting;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('auth_soap');
		
		// get template
		$this->getTemplateFile("soap");
		
		// get all settings
		$settings = $ilSetting->getAll();
		
		// get values in error case
		if ($_SESSION["error_post_vars"])
		{
			if ($_SESSION["error_post_vars"]["soap"]["active"] == "1")
			{
				$this->tpl->setVariable("CHK_SOAP_ACTIVE", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["use_https"] == "1")
			{
				$this->tpl->setVariable("CHK_USE_HTTPS", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["create_users"] == "1")
			{
				$this->tpl->setVariable("CHK_CREATE_USERS", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["allow_local"] == "1")
			{
				$this->tpl->setVariable("CHK_ALLOW_LOCAL", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["account_mail"] == "1")
			{
				$this->tpl->setVariable("CHK_ACCOUNT_MAIL", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["use_dotnet"] == "1")
			{
				$this->tpl->setVariable("CHK_USEDOTNET", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("SOAP_SERVER", $_SESSION["error_post_vars"]["soap"]["server"]);
			$this->tpl->setVariable("SOAP_PORT", $_SESSION["error_post_vars"]["soap"]["port"]);
			$this->tpl->setVariable("SOAP_URI", $_SESSION["error_post_vars"]["soap"]["uri"]);
			$this->tpl->setVariable("SOAP_NAMESPACE", $_SESSION["error_post_vars"]["soap"]["namespace"]);
			$current_default_role = $_SESSION["error_post_vars"]["soap"]["user_default_role"];
		}
		else
		{
			if ($settings["soap_auth_active"] == "1")
			{
				$this->tpl->setVariable("CHK_SOAP_ACTIVE", "checked=\"checked\"");
			}
			if ($settings["soap_auth_use_https"] == "1")
			{
				$this->tpl->setVariable("CHK_USE_HTTPS", "checked=\"checked\"");
			}
			if ($settings["soap_auth_create_users"] == "1")
			{
				$this->tpl->setVariable("CHK_CREATE_USERS", "checked=\"checked\"");
			}
			if ($settings["soap_auth_allow_local"] == "1")
			{
				$this->tpl->setVariable("CHK_ALLOW_LOCAL", "checked=\"checked\"");
			}
			if ($settings["soap_auth_account_mail"] == "1")
			{
				$this->tpl->setVariable("CHK_ACCOUNT_MAIL", "checked=\"checked\"");
			}
			if ($settings["soap_auth_use_dotnet"] == "1")
			{
				$this->tpl->setVariable("CHK_USE_DOTNET", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("SOAP_SERVER", $settings["soap_auth_server"]);
			$this->tpl->setVariable("SOAP_PORT", $settings["soap_auth_port"]);
			$this->tpl->setVariable("SOAP_URI", $settings["soap_auth_uri"]);
			$this->tpl->setVariable("SOAP_NAMESPACE", $settings["soap_auth_namespace"]);
			$current_default_role = $settings["soap_auth_user_default_role"];
		}
		
		// compose role list
		$role_list = $rbacreview->getRolesByFilter(2,$this->object->getId());
		if (!$current_default_role)
		{
			$current_default_role = 4;
		}
		$roles = array();
		foreach ($role_list as $role)
		{
			$roles[$role['obj_id']] = $role['title'];
		}
		$selectElement = ilUtil::formSelect($current_default_role,
			"soap[user_default_role]", $roles, false, true);
		
		$this->tpl->setVariable("SOAP_USER_DEFAULT_ROLE", $selectElement);		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SOAP_TITLE", $this->lng->txt("auth_soap_auth"));
		$this->tpl->setVariable("TXT_SOAP_DESC", $this->lng->txt("auth_soap_auth_desc"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_SOAP_ACTIVE", $this->lng->txt("active"));
		$this->tpl->setVariable("TXT_SOAP_SERVER", $this->lng->txt("server"));
		$this->tpl->setVariable("TXT_SOAP_SERVER_DESC", $this->lng->txt("auth_soap_server_desc"));
		$this->tpl->setVariable("TXT_SOAP_PORT", $this->lng->txt("port"));
		$this->tpl->setVariable("TXT_SOAP_PORT_DESC", $this->lng->txt("auth_soap_port_desc"));
		$this->tpl->setVariable("TXT_SOAP_URI", $this->lng->txt("uri"));
		$this->tpl->setVariable("TXT_SOAP_URI_DESC", $this->lng->txt("auth_soap_uri_desc"));
		$this->tpl->setVariable("TXT_SOAP_NAMESPACE", $this->lng->txt("auth_soap_namespace"));
		$this->tpl->setVariable("TXT_SOAP_NAMESPACE_DESC", $this->lng->txt("auth_soap_namespace_desc"));
		$this->tpl->setVariable("TXT_USE_DOTNET", $this->lng->txt("auth_soap_use_dotnet"));
		$this->tpl->setVariable("TXT_USE_HTTPS", $this->lng->txt("auth_soap_use_https"));
		$this->tpl->setVariable("TXT_CREATE_USERS", $this->lng->txt("auth_create_users"));
		$this->tpl->setVariable("TXT_CREATE_USERS_DESC", $this->lng->txt("auth_soap_create_users_desc"));
		$this->tpl->setVariable("TXT_ACCOUNT_MAIL", $this->lng->txt("user_send_new_account_mail"));
		$this->tpl->setVariable("TXT_ACCOUNT_MAIL_DESC", $this->lng->txt("auth_new_account_mail_desc"));
		$this->tpl->setVariable("TXT_SOAP_USER_DEFAULT_ROLE", $this->lng->txt("auth_user_default_role"));
		$this->tpl->setVariable("TXT_SOAP_USER_DEFAULT_ROLE_DESC",
			$this->lng->txt("auth_soap_user_default_role_desc"));
		$this->tpl->setVariable("TXT_ALLOW_LOCAL", $this->lng->txt("auth_allow_local"));
		$this->tpl->setVariable("TXT_ALLOW_LOCAL_DESC", $this->lng->txt("auth_soap_allow_local_desc"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveSOAP");
	}
	
	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveSOAPObject()
	{
         global $ilUser, $ilSetting;

        // validate required data 
		if (!$_POST["soap"]["server"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate port
		if ($_POST["soap"]["server"] != "" && (preg_match("/^[0-9]{0,5}$/",$_POST["soap"]["port"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_port"),$this->ilias->error_obj->MESSAGE);
		}
		
		$ilSetting->set("soap_auth_server", $_POST["soap"]["server"]);
		$ilSetting->set("soap_auth_port", $_POST["soap"]["port"]);
		$ilSetting->set("soap_auth_active", $_POST["soap"]["active"]);
		$ilSetting->set("soap_auth_uri", $_POST["soap"]["uri"]);
		$ilSetting->set("soap_auth_namespace", $_POST["soap"]["namespace"]);
		$ilSetting->set("soap_auth_create_users", $_POST["soap"]["create_users"]);
		$ilSetting->set("soap_auth_allow_local", $_POST["soap"]["allow_local"]);
		$ilSetting->set("soap_auth_account_mail", $_POST["soap"]["account_mail"]);
		$ilSetting->set("soap_auth_use_https", $_POST["soap"]["use_https"]);
		$ilSetting->set("soap_auth_use_dotnet", $_POST["soap"]["use_dotnet"]);
		$ilSetting->set("soap_auth_user_default_role", $_POST["soap"]["user_default_role"]);
		ilUtil::sendInfo($this->lng->txt("auth_soap_settings_saved"),true);
		
		$this->ctrl->redirect($this,'editSOAP');
	}

	/**
	* Configure Custom settings
	* 
	* @access	public
	*/
	function editScriptObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($_SESSION["error_post_vars"])
		{
			$this->tpl->setVariable("AUTH_SCRIPT_NAME", $_SESSION["error_post_vars"]["auth_script"]["name"]);
		}
		else
		{
			// set already saved data
			$settings = $this->ilias->getAllSettings();

			$this->tpl->setVariable("AUTH_SCRIPT_NAME", $settings["auth_script_name"]);
		}

		$this->tabs_gui->setTabActive('auth_script');

		$this->getTemplateFile("script");
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_AUTH_SCRIPT_TITLE", $this->lng->txt("auth_script_configure"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_AUTH_SCRIPT_NAME", $this->lng->txt("auth_script_name"));
		
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveScript");
	}

	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveScriptObject()
	{
		// validate required data 
		if (!$_POST["auth_script"]["name"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// validate script url
		/*
		if (( TODO ,$_POST["ldap"]["server"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_server"),$this->ilias->error_obj->MESSAGE);
		}*/
		
		// TODO: check connection to server
		
		// all ok. save settings and activate auth by external script
		$this->ilias->setSetting("auth_script_name", $_POST["auth_script"]["name"]);
		$this->ilias->setSetting("auth_mode", AUTH_SCRIPT);

		ilUtil::sendInfo($this->lng->txt("auth_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		$this->ctrl->redirect($this,'editScript');
	}
	
	
	/**
	* get the title of auth mode
	* 
	* @access	public
	* @return	string	language dependent title of auth mode
	*/
	function getAuthModeTitle()
	{
		switch ($this->ilias->getSetting("auth_mode"))
		{
			case AUTH_LOCAL:
				return $this->lng->txt("auth_local");
				break;
			
			case AUTH_LDAP:
				return $this->lng->txt("auth_ldap");
				break;
			
			case AUTH_SHIBBOLETH:
				return $this->lng->txt("auth_shib");
				break;

			case AUTH_RADIUS:
				return $this->lng->txt("auth_radius");
				break;
		
			case AUTH_SCRIPT:
				return $this->lng->txt("auth_script");
				break;

			default:
				return $this->lng->txt("unknown");
				break;
		}
	}
	
	function updateAuthRolesObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once('classes/class.ilObjRole.php');
		ilObjRole::_updateAuthMode($_POST['Fobject']);
		
		ilUtil::sendInfo($this->lng->txt("auth_mode_roles_changed"),true);
		$this->ctrl->redirect($this,'authSettings');
	}

	function &executeCommand()
	{
		global $ilAccess,$ilErr;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();


		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->WARNING);
		}
			
		switch($next_class)
		{
			case 'ilregistrationsettingsgui':

				include_once './Services/Registration/classes/class.ilRegistrationSettingsGUI.php';

				// Enable tabs
				$this->tabs_gui->setTabActive('registration_settings');
				$registration_gui =& new ilRegistrationSettingsGUI();
				$this->ctrl->forwardCommand($registration_gui);
				break;

			case 'ilpermissiongui':
			
				// Enable tabs
				$this->tabs_gui->setTabActive('perm_settings');
			
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'illdapsettingsgui':
			
				// Enable Tabs
				$this->tabs_gui->setTabActive('auth_ldap');
				
				include_once './Services/LDAP/classes/class.ilLDAPSettingsGUI.php';
				$ldap_settings_gui = new ilLDAPSettingsGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($ldap_settings_gui);
				break;
				
			case 'ilradiussettingsgui':
				
				$this->tabs_gui->setTabActive('auth_radius');
				include_once './Services/Radius/classes/class.ilRadiusSettingsGUI.php';
				$radius_settings_gui = new ilRadiusSettingsGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($radius_settings_gui);
				break;
				

			default:
				if(!$cmd)
				{
					$cmd = "authSettings";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{

			$tabs_gui->addTarget('registration_settings',
									   $this->ctrl->getLinkTargetByClass('ilregistrationsettingsgui','view'));

			$tabs_gui->addTarget("authentication_settings", $this->ctrl->getLinkTarget($this, "authSettings"),
										 "", "", "");
										 
			$tabs_gui->addTarget("auth_ldap", $this->ctrl->getLinkTargetByClass('illdapsettingsgui','serverList'),
								   "", "", "");

										 
			#$tabs_gui->addTarget("auth_ldap", $this->ctrl->getLinkTarget($this, "editLDAP"),
			#					   "", "", "");
			
			$tabs_gui->addTarget("auth_shib", $this->ctrl->getLinkTarget($this, "editSHIB"),
								 "", "", "");

			$tabs_gui->addTarget("auth_cas", $this->ctrl->getLinkTarget($this, "editCAS"),
								   "", "", "");
								   
			$tabs_gui->addTarget("auth_radius", $this->ctrl->getLinkTargetByClass('ilradiussettingsgui', "settings"),
									   "", "", "");

			$tabs_gui->addTarget("auth_soap", $this->ctrl->getLinkTarget($this, "editSOAP"),
								 "", "", "");
			
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
								 array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	* set sub tabs
	*/
	function setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser,$ilAccess;
		
		switch ($a_tab)
		{			
			case 'authSettings':				
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("auth_settings",
													 $this->ctrl->getLinkTarget($this,'authSettings'),
													 "");
				}
				
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("login_information",
													 $this->ctrl->getLinkTarget($this,'loginInfo'),
													 "");
				}				
				break;				
		}
	}
} // END class.ilObjAuthSettingsGUI
?>
