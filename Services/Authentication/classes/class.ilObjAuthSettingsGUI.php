<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjAuthSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjAuthSettingsGUI: ilPermissionGUI, ilRegistrationSettingsGUI, ilLDAPSettingsGUI, ilRadiusSettingsGUI
* @ilCtrl_Calls ilObjAuthSettingsGUI: ilAuthShibbolethSettingsGUI
* 
* @extends ilObjectGUI
*/


require_once "./classes/class.ilObjectGUI.php";

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
		
		// auth mode determinitation
	 	if($this->initAuthModeDetermination())
	 	{
	 		$this->tpl->setVariable('TABLE_AUTH_DETERMINATION',$this->form->getHTML());
	 	}
		
		// roles table
		$this->tpl->setVariable("FORMACTION_ROLES",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_AUTH_ROLES", $this->lng->txt("auth_active_roles"));
		$this->tpl->setVariable("TXT_ROLE", $this->lng->txt("obj_role"));
		$this->tpl->setVariable("TXT_ROLE_AUTH_MODE", $this->lng->txt("auth_role_auth_mode"));
		$this->tpl->setVariable("CMD_SUBMIT_ROLES", "updateAuthRoles");
		
		include_once("./Services/AccessControl/classes/class.ilObjRole.php");
		$reg_roles = ilObjRole::_lookupRegisterAllowed();
		
		// auth mode selection
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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
		global $rbacsystem, $lng,$ilSetting;		
		
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
		
		if($_POST['default_auth_mode'])
		{
			$ilSetting->set('default_auth_mode',(int) $_POST['default_auth_mode']);
		}
		
		ilUtil::sendSuccess($this->lng->txt("login_information_settings_saved"));
		
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
		global $rbacsystem, $lng,$ilSetting;	
		
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
		
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		include_once('Services/Radius/classes/class.ilRadiusSettings.php');
		$rad_settings = ilRadiusSettings::_getInstance();
		if($ldap_id = ilLDAPServer::_getFirstActiveServer() or $rad_settings->isActive())
		{
			$select = new ilSelectInputGUI($this->lng->txt('default_auth_mode'),'default_auth_mode');
			$select->setValue($ilSetting->get('default_auth_mode',AUTH_LOCAL));
			$select->setInfo($this->lng->txt('default_auth_mode_info'));
			$options[AUTH_LOCAL] = $this->lng->txt('auth_local');
			if($ldap_id)
			{
				$options[AUTH_LDAP] = $this->lng->txt('auth_ldap');
			}
			if($rad_settings->isActive())
			{
				$options [AUTH_RADIUS] = $this->lng->txt('auth_radius');
			}
			$select->setOptions($options);
			$form->addItem($select);
		}
		
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
					ilUtil::sendFailure($this->lng->txt("auth_shib_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editSHIB")));
				}
				break;

			case AUTH_RADIUS:
				if ($this->object->checkAuthRADIUS() !== true)
				{
					ilUtil::sendFailure($this->lng->txt("auth_radius_not_configured"),true);
					$this->ctrl->redirect($this,'editRADIUS');
				}
				break;

			case AUTH_SCRIPT:
				if ($this->object->checkAuthScript() !== true)
				{
					ilUtil::sendFailure($this->lng->txt("auth_script_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editScript")));
				}
				break;
		}
		
		$this->ilias->setSetting("auth_mode",$_POST["auth_mode"]);
		
		ilUtil::sendSuccess($this->lng->txt("auth_default_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		$this->ctrl->redirect($this,'authSettings');
	}
	




	/**
	* Configure cas settings
	* 
	* @access	public
	*/
	function editCASObject()
	{
		global $rbacsystem, $rbacreview, $ilSetting;
		
		if (!$rbacsystem->checkAccess("read",$this->object->getRefId()))
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
         global $ilUser, $ilSetting, $rbacsystem;

 		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

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
		ilUtil::sendSuccess($this->lng->txt("auth_cas_settings_saved"),true);
		
		$this->ctrl->redirect($this,'editCAS');
	}

	/**
	* Configure soap settings
	* 
	* @access	public
	*/
	function editSOAPObject()
	{
		global $rbacsystem, $rbacreview, $ilSetting, $ilCtrl, $lng;
		
		if (!$rbacsystem->checkAccess("read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('auth_soap');
		
		// get template
		$stpl = new ilTemplate("tpl.auth_soap.html", true, true, "");
		
		//$this->getTemplateFile("soap");
		
		// get all settings
		$settings = $ilSetting->getAll();
		
		// get values in error case
		if ($_SESSION["error_post_vars"])
		{
			if ($_SESSION["error_post_vars"]["soap"]["active"] == "1")
			{
				$stpl->setVariable("CHK_SOAP_ACTIVE", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["use_https"] == "1")
			{
				$stpl->setVariable("CHK_USE_HTTPS", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["create_users"] == "1")
			{
				$stpl->setVariable("CHK_CREATE_USERS", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["allow_local"] == "1")
			{
				$stpl->setVariable("CHK_ALLOW_LOCAL", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["account_mail"] == "1")
			{
				$stpl->setVariable("CHK_ACCOUNT_MAIL", "checked=\"checked\"");
			}
			if ($_SESSION["error_post_vars"]["soap"]["use_dotnet"] == "1")
			{
				$stpl->setVariable("CHK_USEDOTNET", "checked=\"checked\"");
			}
			
			$stpl->setVariable("SOAP_SERVER", $_SESSION["error_post_vars"]["soap"]["server"]);
			$stpl->setVariable("SOAP_PORT", $_SESSION["error_post_vars"]["soap"]["port"]);
			$stpl->setVariable("SOAP_URI", $_SESSION["error_post_vars"]["soap"]["uri"]);
			$stpl->setVariable("SOAP_NAMESPACE", $_SESSION["error_post_vars"]["soap"]["namespace"]);
			$current_default_role = $_SESSION["error_post_vars"]["soap"]["user_default_role"];
		}
		else
		{
			if ($settings["soap_auth_active"] == "1")
			{
				$stpl->setVariable("CHK_SOAP_ACTIVE", "checked=\"checked\"");
			}
			if ($settings["soap_auth_use_https"] == "1")
			{
				$stpl->setVariable("CHK_USE_HTTPS", "checked=\"checked\"");
			}
			if ($settings["soap_auth_create_users"] == "1")
			{
				$stpl->setVariable("CHK_CREATE_USERS", "checked=\"checked\"");
			}
			if ($settings["soap_auth_allow_local"] == "1")
			{
				$stpl->setVariable("CHK_ALLOW_LOCAL", "checked=\"checked\"");
			}
			if ($settings["soap_auth_account_mail"] == "1")
			{
				$stpl->setVariable("CHK_ACCOUNT_MAIL", "checked=\"checked\"");
			}
			if ($settings["soap_auth_use_dotnet"] == "1")
			{
				$stpl->setVariable("CHK_USE_DOTNET", "checked=\"checked\"");
			}
			
			$stpl->setVariable("SOAP_SERVER", $settings["soap_auth_server"]);
			$stpl->setVariable("SOAP_PORT", $settings["soap_auth_port"]);
			$stpl->setVariable("SOAP_URI", $settings["soap_auth_uri"]);
			$stpl->setVariable("SOAP_NAMESPACE", $settings["soap_auth_namespace"]);
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
		
		$stpl->setVariable("SOAP_USER_DEFAULT_ROLE", $selectElement);		
		$stpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$stpl->setVariable("COLSPAN", 3);
		$stpl->setVariable("TXT_SOAP_TITLE", $this->lng->txt("auth_soap_auth"));
		$stpl->setVariable("TXT_SOAP_DESC", $this->lng->txt("auth_soap_auth_desc"));
		$stpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$stpl->setVariable("TXT_SOAP_ACTIVE", $this->lng->txt("active"));
		$stpl->setVariable("TXT_SOAP_SERVER", $this->lng->txt("server"));
		$stpl->setVariable("TXT_SOAP_SERVER_DESC", $this->lng->txt("auth_soap_server_desc"));
		$stpl->setVariable("TXT_SOAP_PORT", $this->lng->txt("port"));
		$stpl->setVariable("TXT_SOAP_PORT_DESC", $this->lng->txt("auth_soap_port_desc"));
		$stpl->setVariable("TXT_SOAP_URI", $this->lng->txt("uri"));
		$stpl->setVariable("TXT_SOAP_URI_DESC", $this->lng->txt("auth_soap_uri_desc"));
		$stpl->setVariable("TXT_SOAP_NAMESPACE", $this->lng->txt("auth_soap_namespace"));
		$stpl->setVariable("TXT_SOAP_NAMESPACE_DESC", $this->lng->txt("auth_soap_namespace_desc"));
		$stpl->setVariable("TXT_USE_DOTNET", $this->lng->txt("auth_soap_use_dotnet"));
		$stpl->setVariable("TXT_USE_HTTPS", $this->lng->txt("auth_soap_use_https"));
		$stpl->setVariable("TXT_CREATE_USERS", $this->lng->txt("auth_create_users"));
		$stpl->setVariable("TXT_CREATE_USERS_DESC", $this->lng->txt("auth_soap_create_users_desc"));
		$stpl->setVariable("TXT_ACCOUNT_MAIL", $this->lng->txt("user_send_new_account_mail"));
		$stpl->setVariable("TXT_ACCOUNT_MAIL_DESC", $this->lng->txt("auth_new_account_mail_desc"));
		$stpl->setVariable("TXT_SOAP_USER_DEFAULT_ROLE", $this->lng->txt("auth_user_default_role"));
		$stpl->setVariable("TXT_SOAP_USER_DEFAULT_ROLE_DESC",
			$this->lng->txt("auth_soap_user_default_role_desc"));
		$stpl->setVariable("TXT_ALLOW_LOCAL", $this->lng->txt("auth_allow_local"));
		$stpl->setVariable("TXT_ALLOW_LOCAL_DESC", $this->lng->txt("auth_soap_allow_local_desc"));
		$stpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$stpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$stpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$stpl->setVariable("CMD_SUBMIT", "saveSOAP");
		
		// test form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle("Test Request");
		$text_prop = new ilTextInputGUI("ext_uid", "ext_uid");
		$form->addItem($text_prop);
		$text_prop2 = new ilTextInputGUI("soap_pw", "soap_pw");
		$form->addItem($text_prop2);
		$cb = new ilCheckboxInputGUI("new_user", "new_user");
		$form->addItem($cb);
		 
		$form->addCommandButton("testSoapAuthConnection",
			"Send");
		
		if ($ilCtrl->getCmd() == "testSoapAuthConnection")
		{
			include_once("./Services/SOAPAuth/classes/class.ilSOAPAuth.php");
			$ret = "<br />".ilSOAPAuth::testConnection(
				ilUtil::stripSlashes($_POST["ext_uid"]),
				ilUtil::stripSlashes($_POST["soap_pw"]),
				(boolean) $_POST["new_user"]
				);
		}
			
		$stpl->setVariable("TEST_FORM", $form->getHtml().$ret);
		$this->tpl->setContent($stpl->get());
	}
	
	function testSoapAuthConnectionObject()
	{
		$this->editSOAPObject();
	}
	
	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveSOAPObject()
	{
         global $ilUser, $ilSetting, $rbacsystem;

 		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

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
		ilUtil::sendSuccess($this->lng->txt("auth_soap_settings_saved"),true);
		
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

		ilUtil::sendSuccess($this->lng->txt("auth_mode_changed_to")." ".$this->getAuthModeTitle(),true);
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
		
		include_once('./Services/AccessControl/classes/class.ilObjRole.php');
		ilObjRole::_updateAuthMode($_POST['Fobject']);
		
		ilUtil::sendSuccess($this->lng->txt("auth_mode_roles_changed"),true);
		$this->ctrl->redirect($this,'authSettings');
	}
	
	/**
	 * init auth mode determinitation form
	 *
	 * @access protected
	 */
	protected function initAuthModeDetermination()
	{
		if(is_object($this->form))
		{
			return true;
		}
		// Are there any authentication methods that support automatic determination ?
	
	 	include_once('Services/Authentication/classes/class.ilAuthModeDetermination.php');
	 	$det = ilAuthModeDetermination::_getInstance();
		if($det->getCountActiveAuthModes() <= 1)
		{
			return false;
		}		
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTableWidth('100%');
		$this->form->setTitle($this->lng->txt('auth_auth_mode_determination'));
		$this->form->addCommandButton('updateAuthModeDetermination',$this->lng->txt('save'));
		$this->form->addCommandButton('authSettings',$this->lng->txt('cancel'));
		
		$kind = new ilRadioGroupInputGUI($this->lng->txt('auth_kind_determination'),'kind');
		$kind->setInfo($this->lng->txt('auth_mode_determination_info'));
		$kind->setValue($det->getKind());
		$kind->setRequired(true);
		
		$option_user = new ilRadioOption($this->lng->txt('auth_by_user'),0);
		$kind->addOption($option_user);
		
		$option_determination = new ilRadioOption($this->lng->txt('auth_automatic'),1);
		
		include_once('Services/Authentication/classes/class.ilAuthUtils.php');
		
		$auth_sequenced = $det->getAuthModeSequence();
		$counter = 1;
		foreach($auth_sequenced as $auth_mode)
		{
			switch($auth_mode)
			{
				case AUTH_LDAP:
					$text = $this->lng->txt('auth_ldap');
					break;
				case AUTH_RADIUS:
					$text = $this->lng->txt('auth_radius');
					break;
				case AUTH_LOCAL:
					$text = $this->lng->txt('auth_local');
					break;
				case AUTH_SOAP:
					$text = $this->lng->txt('auth_soap');
					break;
			}
			
			
			$pos = new ilTextInputGUI($text,'position['.$auth_mode.']');
			$pos->setValue($counter++);
			$pos->setSize(1);
			$pos->setMaxLength(1);
			$option_determination->addSubItem($pos);
		}		
		$kind->addOption($option_determination);
		$this->form->addItem($kind);
		return true;
	}
	
	/**
	 * update auth mode determination
	 *
	 * @access public
	 * 
	 */
	public function updateAuthModeDeterminationObject()
	{
	 	include_once('Services/Authentication/classes/class.ilAuthModeDetermination.php');
	 	$det = ilAuthModeDetermination::_getInstance();
	 	
	 	$det->setKind((int) $_POST['kind']);
	
		$pos = $_POST['position'] ? $_POST['position'] : array();
		asort($pos,SORT_NUMERIC);
		
		$counter = 0;
	 	foreach($pos as $auth_mode => $dummy)
	 	{
	 		$position[$counter++] = $auth_mode;  
	 	}
	 	$det->setAuthModeSequence($position ? $position : array());
	 	$det->save();
	 	
	 	ilUtil::sendSuccess($this->lng->txt('settings_saved'));
	 	$this->authSettingsObject();
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
			
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
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
				
			case 'ilauthshibbolethsettingsgui':
			
				$this->tabs_gui->setTabActive('auth_shib');
				include_once('./Services/AuthShibboleth/classes/class.ilAuthShibbolethSettingsGUI.php');
				$shib_settings_gui = new ilAuthShibbolethSettingsGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($shib_settings_gui);
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
			
			$tabs_gui->addTarget('auth_shib',$this->ctrl->getLinkTargetByClass('ilauthshibbolethsettingsgui','settings'));

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
