<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjAuthSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjAuthSettingsGUI: ilPermissionGUI, ilRegistrationSettingsGUI, ilLDAPSettingsGUI, ilRadiusSettingsGUI
* @ilCtrl_Calls ilObjAuthSettingsGUI: ilAuthShibbolethSettingsGUI, ilOpenIdSettingsGUI, ilCASSettingsGUI
* 
* @extends ilObjectGUI
*/


require_once "./Services/Object/classes/class.ilObjectGUI.php";

class ilObjAuthSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjAuthSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "auth";
		$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('registration');

		define('LDAP_DEFAULT_PORT',389);
		define('RADIUS_DEFAULT_PORT',1812);

	}



	function viewObject()
	{
		return $this->authSettingsObject();		
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
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.auth_general.html",
			"Services/Authentication");
		
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

                $this->tpl->setVariable("TXT_APACHE", $this->lng->txt("auth_apache"));

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
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.svg")."\" alt=\"".$this->lng->txt("enabled")."\" title=\"".$this->lng->txt("enabled")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.svg")."\" alt=\"".$this->lng->txt("disabled")."\" title=\"".$this->lng->txt("disabled")."\" border=\"0\" vspace=\"0\"/>";

		$this->tpl->setVariable("AUTH_LOCAL_ACTIVE", $icon_ok);
		
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		$this->tpl->setVariable('AUTH_LDAP_ACTIVE',count(ilLDAPServer::_getActiveServerList()) ? $icon_ok : $icon_not_ok);
		#$this->tpl->setVariable("AUTH_LDAP_ACTIVE", $this->ilias->getSetting('ldap_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_RADIUS_ACTIVE", $this->ilias->getSetting('radius_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_SHIB_ACTIVE", $this->ilias->getSetting('shib_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_SCRIPT_ACTIVE", $this->ilias->getSetting('script_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_CAS_ACTIVE", $this->ilias->getSetting('cas_active') ? $icon_ok : $icon_not_ok);
		$this->tpl->setVariable("AUTH_APACHE_ACTIVE", $this->ilias->getSetting('apache_active') ? $icon_ok : $icon_not_ok);

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

			case AUTH_APACHE: // apache
				$this->tpl->setVariable("CHK_APACHE", $checked);
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
					|| $auth_name == "shibboleth" || $auth_name == 'ldap' 
					|| $auth_name == 'apache' || $auth_name == "ecs"
					|| $auth_name == "openid")
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
		$this->tabs_gui->setSubTabActive("auth_login_editor");
		
		$lng->loadLanguageModule("meta");
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.auth_login_messages.html",
			"Services/Authentication");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt("login_information"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("login_information_desc"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->initLoginForm();
		$this->tpl->setVariable('LOGIN_INFO',$this->form->getHTML());
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
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editLDAP", "", false, false)));
				}
				*/
				break;
				
				case AUTH_SHIB:
				if ($this->object->checkAuthSHIB() !== true)
				{
					ilUtil::sendFailure($this->lng->txt("auth_shib_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editSHIB", "", false, false)));
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
					ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editScript", "", false, false)));
				}
				break;
		}
		
		$this->ilias->setSetting("auth_mode",$_POST["auth_mode"]);
		
		ilUtil::sendSuccess($this->lng->txt("auth_default_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		$this->ctrl->redirect($this,'authSettings');
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
		
		//set Template
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.auth_soap.html','Services/Authentication');
		
		// compose role list
		$role_list = $rbacreview->getRolesByFilter(2,$this->object->getId());
		$roles = array();
		
		foreach ($role_list as $role)
		{
			$roles[$role['obj_id']] = $role['title'];
		}
		
		//set property form gui
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$soap_config = new ilPropertyFormGUI();
		$soap_config->setTitle($this->lng->txt("auth_soap_auth"));
		$soap_config->setDescription($this->lng->txt("auth_soap_auth_desc"));
		$soap_config->setFormAction($this->ctrl->getFormAction($this, "editSOAP"));
		$soap_config->addCommandButton("saveSOAP", $this->lng->txt("save"));
		$soap_config->addCommandButton("editSOAP", $this->lng->txt("cancel"));
		
		//set activ
		$active = new ilCheckboxInputGUI();
		$active->setTitle($this->lng->txt("active"));
		$active->setPostVar("soap[active]");
		
		//set server
		$server = new ilTextInputGUI();
		$server->setTitle($this->lng->txt("server"));
		$server->setInfo($this->lng->txt("auth_soap_server_desc"));
		$server->setPostVar("soap[server]");
		$server->setSize(50);
		$server->setMaxLength(256);
		$server->setRequired(true);
		
		//set port
		$port = new ilTextInputGUI();
		$port->setTitle($this->lng->txt("port"));
		$port->setInfo($this->lng->txt("auth_soap_port_desc"));
		$port->setPostVar("soap[port]");
		$port->setSize(7);
		$port->setMaxLength(5);
		
		//set https
		$https = new ilCheckboxInputGUI();
		$https->setTitle($this->lng->txt("auth_soap_use_https"));
		$https->setPostVar("soap[use_https]");
		
		//set uri
		$uri = new ilTextInputGUI();
		$uri->setTitle($this->lng->txt("uri"));
		$uri->setInfo($this->lng->txt("auth_soap_uri_desc"));
		$uri->setPostVar("soap[uri]");
		$uri->setSize(50);
		$uri->setMaxLength(256);
		
		//set namespace
		$namespace = new ilTextInputGUI();
		$namespace->setTitle($this->lng->txt("auth_soap_namespace"));
		$namespace->setInfo($this->lng->txt("auth_soap_namespace_desc"));
		$namespace->setPostVar("soap[namespace]");
		$namespace->setSize(50);
		$namespace->setMaxLength(256);
		
		//set dotnet
		$dotnet = new ilCheckboxInputGUI();
		$dotnet->setTitle($this->lng->txt("auth_soap_use_dotnet"));
		$dotnet->setPostVar("soap[use_dotnet]");
		
		//set create users
		$createuser = new ilCheckboxInputGUI();
		$createuser->setTitle($this->lng->txt("auth_create_users"));
		$createuser->setInfo($this->lng->txt("auth_soap_create_users_desc"));
		$createuser->setPostVar("soap[create_users]");
		
		//set account mail
		$sendmail = new ilCheckboxInputGUI();
		$sendmail->setTitle($this->lng->txt("user_send_new_account_mail"));
		$sendmail->setInfo($this->lng->txt("auth_new_account_mail_desc"));
		$sendmail->setPostVar("soap[account_mail]");
		
		//set user default role
		$defaultrole = new ilSelectInputGUI();
		$defaultrole->setTitle($this->lng->txt("auth_user_default_role"));
		$defaultrole->setInfo($this->lng->txt("auth_soap_user_default_role_desc"));
		$defaultrole->setPostVar("soap[user_default_role]");
		$defaultrole->setOptions($roles);
		
		//set allow local authentication
		$allowlocal = new ilCheckboxInputGUI();
		$allowlocal->setTitle($this->lng->txt("auth_allow_local"));
		$allowlocal->setInfo($this->lng->txt("auth_soap_allow_local_desc"));
		$allowlocal->setPostVar("soap[allow_local]");
		
		// get all settings
		$settings = $ilSetting->getAll();
		
		// get values in error case
		if ($_SESSION["error_post_vars"])
		{
			$active		->setChecked($_SESSION["error_post_vars"]["soap"]["active"]);
			$server		->setValue($_SESSION["error_post_vars"]["soap"]["server"]);
			$port		->setValue($_SESSION["error_post_vars"]["soap"]["port"]);
			$https		->setChecked($_SESSION["error_post_vars"]["soap"]["use_https"]);
			$uri		->setValue($_SESSION["error_post_vars"]["soap"]["uri"]);
			$namespace	->setValue($_SESSION["error_post_vars"]["soap"]["namespace"]);
			$dotnet		->setChecked($_SESSION["error_post_vars"]["soap"]["use_dotnet"]);
			$createuser	->setChecked($_SESSION["error_post_vars"]["soap"]["create_users"]);
			$allowlocal	->setChecked($_SESSION["error_post_vars"]["soap"]["allow_local"]);
			$defaultrole->setValue($_SESSION["error_post_vars"]["soap"]["user_default_role"]);
			$sendmail	->setChecked($_SESSION["error_post_vars"]["soap"]["account_mail"]);
		}
		else
		{
			$active		->setChecked($settings["soap_auth_active"]);
			$server		->setValue($settings["soap_auth_server"]);
			$port		->setValue($settings["soap_auth_port"]);
			$https		->setChecked($settings["soap_auth_use_https"]);
			$uri		->setValue($settings["soap_auth_uri"]);
			$namespace	->setValue($settings["soap_auth_namespace"]);
			$dotnet		->setChecked($settings["soap_auth_use_dotnet"]);
			$createuser	->setChecked($settings["soap_auth_create_users"]);
			$allowlocal	->setChecked($settings["soap_auth_allow_local"]);
			$defaultrole->setValue($settings["soap_auth_user_default_role"]);
			$sendmail	->setChecked($settings["soap_auth_account_mail"]);
		}
		
		if (!$defaultrole->getValue())
		{
			$defaultrole->setValue(4);
		}
		
		//add Items to property gui
		$soap_config->addItem($active);
		$soap_config->addItem($server);
		$soap_config->addItem($port);
		$soap_config->addItem($https);
		$soap_config->addItem($uri);
		$soap_config->addItem($namespace);
		$soap_config->addItem($dotnet);
		$soap_config->addItem($createuser);
		$soap_config->addItem($sendmail);
		$soap_config->addItem($defaultrole);
		$soap_config->addItem($allowlocal);
		
		$this->tpl->setVariable("CONFIG_FORM", $soap_config->getHTML());
		
		// test form
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
		$this->tpl->setVariable("TEST_FORM", $form->getHTML().$ret);
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.auth_script.html",
			"Services/Authentication");
		
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

                        case AUTH_APACHE:
				return $this->lng->txt("auth_apache");
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
		$this->form->setTitle($this->lng->txt('auth_auth_settings'));
		$this->form->addCommandButton('updateAuthModeDetermination',$this->lng->txt('save'));

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		$cap = new ilCheckboxInputGUI($this->lng->txt('adm_captcha_anonymous_short'), 'activate_captcha_anonym');
		$cap->setInfo($this->lng->txt('adm_captcha_anonymous_auth'));
		$cap->setValue(1);
		if(!ilCaptchaUtil::checkFreetype())
		{
			$cap->setAlert(ilCaptchaUtil::getPreconditionsMessage());
		}
		$cap->setChecked(ilCaptchaUtil::isActiveForLogin());
		$this->form->addItem($cap);
		
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt('auth_auth_mode_determination'));
		$this->form->addItem($header);
		
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
				case AUTH_APACHE:
					$text = $this->lng->txt('auth_apache');
					break;
				// begin-patch auth_plugin
				default:
					foreach(ilAuthUtils::getAuthPlugins() as $pl)
					{
						$option = $pl->getMultipleAuthModeOptions($auth_mode);
						$text = $option[$auth_mode]['txt'];
					}
					break;
				// end-patch auth_plugin
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

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		ilCaptchaUtil::setActiveForLogin((bool)$_POST['activate_captcha_anonym']);

	 	ilUtil::sendSuccess($this->lng->txt('settings_saved'));
	 	$this->authSettingsObject();
	}

	/**
	 * Execute command. Called from control class
	 * @global ilAccessHandler $ilAccess
	 * @global ilErrorHandling $ilErr
	 * @return void
	 */
	public function executeCommand()
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

			case 'ilcassettingsgui':

				$this->tabs_gui->setTabActive('auth_cas');
				include_once './Services/CAS/classes/class.ilCASSettingsGUI.php';
				$cas_settings = new ilCASSettingsGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($cas_settings);
				break;
				
			case 'ilradiussettingsgui':
				
				$this->tabs_gui->setTabActive('auth_radius');
				include_once './Services/Radius/classes/class.ilRadiusSettingsGUI.php';
				$radius_settings_gui = new ilRadiusSettingsGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($radius_settings_gui);
				break;
				
			case 'ilopenidsettingsgui':
				
				$this->tabs_gui->setTabActive('auth_openid');
				
				include_once './Services/OpenId/classes/class.ilOpenIdSettingsGUI.php';
				$os = new ilOpenIdSettingsGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($os);
				break;

			case 'ilauthloginpageeditorgui':
				
				$this->setSubTabs("authSettings");
				$this->tabs_gui->setTabActive('authentication_settings');
				$this->tabs_gui->setSubTabActive("auth_login_editor");

				include_once './Services/Authentication/classes/class.ilAuthLoginPageEditorGUI.php';
				$lpe = new ilAuthLoginPageEditorGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($lpe);
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
			$tabs_gui->addTarget("authentication_settings", $this->ctrl->getLinkTarget($this, "authSettings"),
										 "", "", "");
		
			$tabs_gui->addTarget('registration_settings',
									   $this->ctrl->getLinkTargetByClass('ilregistrationsettingsgui','view'));
			
			$tabs_gui->addTarget("auth_ldap", $this->ctrl->getLinkTargetByClass('illdapsettingsgui','serverList'),
								   "", "", "");

										 
			#$tabs_gui->addTarget("auth_ldap", $this->ctrl->getLinkTarget($this, "editLDAP"),
			#					   "", "", "");
			
			$tabs_gui->addTarget('auth_shib',$this->ctrl->getLinkTargetByClass('ilauthshibbolethsettingsgui','settings'));

			$tabs_gui->addTarget(
				'auth_cas',
				$this->ctrl->getLinkTargetByClass('ilcassettingsgui','settings')
			);
								   
			$tabs_gui->addTarget("auth_radius", $this->ctrl->getLinkTargetByClass('ilradiussettingsgui', "settings"),
									   "", "", "");

			$tabs_gui->addTarget("auth_soap", $this->ctrl->getLinkTarget($this, "editSOAP"),
								 "", "", "");
								 
			$tabs_gui->addTarget(
				'auth_openid',
				$this->ctrl->getLinkTargetByClass('ilopenidsettingsgui','settings'),
				'',
				'',
				''
			);

			$tabs_gui->addTarget("apache_auth_settings", $this->ctrl->getLinkTarget($this,'apacheAuthSettings'),
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

		$GLOBALS['lng']->loadLanguageModule('auth');
		
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
					$this->tabs_gui->addSubTabTarget(
						'auth_login_editor',
						$this->ctrl->getLinkTargetByClass('ilauthloginpageeditorgui',''),
						''
					);
				}				
				break;				
		}
	}


	public function apacheAuthSettingsObject($form = false)
	{
		global $ilDB, $tpl;

		$this->tabs_gui->setTabActive("apache_auth_settings");
		//$this->setSubTabs("authSettings");
		//$this->tabs_gui->setSubTabActive("apache_auth_settings");
		if (!$form)
		{
			$form = $this->getApacheAuthSettingsForm();

			$settings = new ilSetting('apache_auth');
			$settingsMap = $settings->getAll();

			$path = ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt';			
			if (file_exists($path) && is_readable($path)) {
				$settingsMap['apache_auth_domains'] = file_get_contents($path);
			}
			
			$form->setValuesByArray($settingsMap);
		}
		$tpl->setVariable('ADM_CONTENT', $form->getHtml());
	}

	public function saveApacheSettingsObject()
	{
		global $ilCtrl;
		$form = $this->getApacheAuthSettingsForm();
		$form->setValuesByPost();
		/*$items = $form->getItems();
		foreach($items as $item)
			$item->validate();*/
		if ($form->checkInput())
		{
			$settings = new ilSetting('apache_auth');
			$fields = array
			(
				'apache_auth_indicator_name', 'apache_auth_indicator_value',
				'apache_enable_auth', 'apache_enable_local', 'apache_local_autocreate',
				'apache_enable_ldap', 'apache_auth_username_config_type',
				'apache_auth_username_direct_mapping_fieldname',
				'apache_default_role', 'apache_auth_target_override_login_page',
				'apache_auth_enable_override_login_page',
				'apache_auth_authenticate_on_login_page'
//				'apache_auth_username_by_function_functionname',
			);

			foreach($fields as $field)
				$settings->set($field, $form->getInput($field));

			if ($form->getInput('apache_enable_auth'))
				$this->ilias->setSetting('apache_active', true);
			else {
				$this->ilias->setSetting('apache_active', false);
				global $ilSetting;
				if ($ilSetting->get("auth_mode") == AUTH_APACHE) {
					$ilSetting->set("auth_mode", AUTH_LOCAL);
				}
			}

			$allowedDomains = $this->validateApacheAuthAllowedDomains($form->getInput('apache_auth_domains'));
			file_put_contents(ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt', $allowedDomains);
			
			ilUtil::sendSuccess($this->lng->txt('apache_settings_changed_success'), true);
			$this->ctrl->redirect($this, 'apacheAuthSettings');
		}
		else
		{
			$this->apacheAuthSettingsObject($form);
		}
	}

	public function getApacheAuthSettingsForm()
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('apache_settings'));

		$chb_enabled = new ilCheckboxInputGUI($this->lng->txt('apache_enable_auth'), 'apache_enable_auth');
		$form->addItem($chb_enabled);

		$chb_local_create_account = new ilCheckboxInputGUI($this->lng->txt('apache_autocreate'), 'apache_local_autocreate');
		$chb_enabled->addSubitem($chb_local_create_account);

		global $rbacreview;
		$roles = $rbacreview->getGlobalRolesArray();
		$select = new ilSelectInputGUI($this->lng->txt('apache_default_role'), 'apache_default_role');
		$roleOptions = array();
		foreach($roles as $role) {
			$roleOptions[$role['obj_id']] = ilObject::_lookupTitle($role['obj_id']);
		}
		$select->setOptions($roleOptions);
		$select->setValue(4);

		$chb_local_create_account->addSubitem($select);

		$chb_local = new ilCheckboxInputGUI($this->lng->txt('apache_enable_local'), 'apache_enable_local');
		$form->addItem($chb_local);

		$chb_ldap = new ilCheckboxInputGUI($this->lng->txt('apache_enable_ldap'), 'apache_enable_ldap');
		$chb_ldap->setInfo($this->lng->txt('apache_ldap_hint_ldap_must_be_configured'));
		$form->addItem($chb_ldap);

		$txt = new ilTextInputGUI($this->lng->txt('apache_auth_indicator_name'), 'apache_auth_indicator_name');
		$txt->setRequired(true);
		$form->addItem($txt);

		$txt = new ilTextInputGUI($this->lng->txt('apache_auth_indicator_value'), 'apache_auth_indicator_value');
		$txt->setRequired(true);
		$form->addItem($txt);


		$chb = new ilCheckboxInputGUI($this->lng->txt('apache_auth_enable_override_login'), 'apache_auth_enable_override_login_page');
		$form->addItem($chb);

		$txt = new ilTextInputGUI($this->lng->txt('apache_auth_target_override_login'), 'apache_auth_target_override_login_page');
		$txt->setRequired(true);
		$chb->addSubItem($txt);

		$chb = new ilCheckboxInputGUI($this->lng->txt('apache_auth_authenticate_on_login_page'), 'apache_auth_authenticate_on_login_page');
		$form->addItem($chb);

		$sec = new ilFormSectionHeaderGUI();
		$sec->setTitle($this->lng->txt('apache_auth_username_config'));
		$form->addItem($sec);

		$rag = new ilRadioGroupInputGUI($this->lng->txt('apache_auth_username_config_type'), 'apache_auth_username_config_type');
		$form->addItem($rag);

		$rao = new ilRadioOption($this->lng->txt('apache_auth_username_direct_mapping'), 1);
		$rag->addOption($rao);

		$txt = new ilTextInputGUI($this->lng->txt('apache_auth_username_direct_mapping_fieldname'), 'apache_auth_username_direct_mapping_fieldname');
		//$txt->setRequired(true);
		$rao->addSubItem($txt);

		$rao = new ilRadioOption($this->lng->txt('apache_auth_username_extended_mapping'), 2);
		$rao->setDisabled(true);
		$rag->addOption($rao);

		$rao = new ilRadioOption($this->lng->txt('apache_auth_username_by_function'), 3);
		$rag->addOption($rao);

/*		$txt = new ilTextInputGUI($this->lng->txt('apache_auth_username_by_function_functionname'), 'apache_auth_username_by_function_functionname');
		$rao->addSubItem($txt);*/

		$sec = new ilFormSectionHeaderGUI();
		$sec->setTitle($this->lng->txt('apache_auth_security'));
		$form->addItem($sec);
		
		$txt = new ilTextAreaInputGUI($this->lng->txt('apache_auth_domains'), 'apache_auth_domains');
		$txt->setInfo($this->lng->txt('apache_auth_domains_description'));
		
		$form->addItem($txt);
		
		$form->addCommandButton('saveApacheSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		return $form;
	}
	
	private function validateApacheAuthAllowedDomains($text) {
		return join("\n",  preg_split("/[\r\n]+/", $text));
	}

	/**
	 * @param string $a_form_id
	 * @return array
	 */
	public function addToExternalSettingsForm($a_form_id)
	{
		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_ACCESSIBILITY:
				require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
				$fields = array(
					'adm_captcha_anonymous_short' => array(ilCaptchaUtil::isActiveForLogin(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
				);

				return array('authentication_settings' => array('authSettings', $fields));
		}
	}
} // END class.ilObjAuthSettingsGUI
?>
