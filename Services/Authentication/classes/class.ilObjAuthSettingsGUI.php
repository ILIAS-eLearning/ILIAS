<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class ilObjAuthSettingsGUI
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @ilCtrl_Calls ilObjAuthSettingsGUI: ilPermissionGUI, ilRegistrationSettingsGUI, ilLDAPSettingsGUI, ilRadiusSettingsGUI
 * @ilCtrl_Calls ilObjAuthSettingsGUI: ilAuthShibbolethSettingsGUI, ilCASSettingsGUI
 * @ilCtrl_Calls ilObjAuthSettingsGUI: ilSamlSettingsGUI, ilOpenIdConnectSettingsGUI
 *
 * @extends ilObjectGUI
 */
class ilObjAuthSettingsGUI extends ilObjectGUI
{
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        $this->type = "auth";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('auth');
    }

    public function viewObject() : void
    {
        $this->authSettingsObject();
    }

    /**
    * display settings menu
    */
    public function authSettingsObject() : void
    {
        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->tabs_gui->setTabActive('authentication_settings');
        $this->setSubTabs('authSettings');
        $this->tabs_gui->setSubTabActive("auth_settings");

        $generalSettingsTpl = new ilTemplate('tpl.auth_general.html', true, true, 'Services/Authentication');

        $generalSettingsTpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $generalSettingsTpl->setVariable("TXT_AUTH_TITLE", $this->lng->txt("auth_select"));

        $generalSettingsTpl->setVariable("TXT_AUTH_MODE", $this->lng->txt("auth_mode"));
        $generalSettingsTpl->setVariable("TXT_AUTH_DEFAULT", $this->lng->txt("default"));
        $generalSettingsTpl->setVariable("TXT_AUTH_ACTIVE", $this->lng->txt("active"));
        $generalSettingsTpl->setVariable("TXT_AUTH_NUM_USERS", $this->lng->txt("num_users"));

        $generalSettingsTpl->setVariable("TXT_LOCAL", $this->lng->txt("auth_local"));
        $generalSettingsTpl->setVariable("TXT_LDAP", $this->lng->txt("auth_ldap"));
        $generalSettingsTpl->setVariable("TXT_SHIB", $this->lng->txt("auth_shib"));

        $generalSettingsTpl->setVariable("TXT_CAS", $this->lng->txt("auth_cas"));

        $generalSettingsTpl->setVariable("TXT_RADIUS", $this->lng->txt("auth_radius"));
        $generalSettingsTpl->setVariable("TXT_SCRIPT", $this->lng->txt("auth_script"));

        $generalSettingsTpl->setVariable("TXT_APACHE", $this->lng->txt("auth_apache"));

        $auth_cnt = ilObjUser::_getNumberOfUsersPerAuthMode();
        $auth_modes = ilAuthUtils::_getAllAuthModes();
        $valid_modes = array(AUTH_LOCAL,AUTH_LDAP,AUTH_SHIBBOLETH,AUTH_SAML,AUTH_CAS,ilAuthUtils::AUTH_RADIUS,AUTH_APACHE,AUTH_OPENID_CONNECT);
        // icon handlers
        $icon_ok = "<img src=\"" . ilUtil::getImagePath("icon_ok.svg") . "\" alt=\"" . $this->lng->txt("enabled") . "\" title=\"" . $this->lng->txt("enabled") . "\" border=\"0\" vspace=\"0\"/>";
        $icon_not_ok = "<img src=\"" . ilUtil::getImagePath("icon_not_ok.svg") . "\" alt=\"" . $this->lng->txt("disabled") . "\" title=\"" . $this->lng->txt("disabled") . "\" border=\"0\" vspace=\"0\"/>";


        foreach ($auth_modes as $mode => $mode_name) {
            if (!in_array($mode, $valid_modes) && !ilLDAPServer::isAuthModeLDAP($mode) && !ilSamlIdp::isAuthModeSaml((string) $mode)) {
                continue;
            }

            $generalSettingsTpl->setCurrentBlock('auth_mode');

            if (ilLDAPServer::isAuthModeLDAP($mode)) {
                $server = ilLDAPServer::getInstanceByServerId(ilLDAPServer::getServerIdByAuthMode($mode));
                $generalSettingsTpl->setVariable("AUTH_NAME", $server->getName());
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', $server->isActive() ? $icon_ok : $icon_not_ok);
            } elseif (ilSamlIdp::isAuthModeSaml((string) $mode)) {
                $idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode($mode));
                $generalSettingsTpl->setVariable('AUTH_NAME', $idp->getEntityId());
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', $idp->isActive() ? $icon_ok : $icon_not_ok);
            } else {
                $generalSettingsTpl->setVariable("AUTH_NAME", $this->lng->txt("auth_" . $mode_name));
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', $this->ilias->getSetting($mode_name . '_active') || $mode == AUTH_LOCAL ? $icon_ok : $icon_not_ok);
            }

            $auth_cnt_mode = isset($auth_cnt[$mode_name]) ? $auth_cnt[$mode_name] : 0;
            if ($this->settings->get('auth_mode') == $mode) {
                $generalSettingsTpl->setVariable("AUTH_CHECKED", "checked=\"checked\"");
                $auth_cnt_default = isset($auth_cnt["default"]) ? $auth_cnt["default"] : 0;
                $generalSettingsTpl->setVariable(
                    "AUTH_USER_NUM",
                    ((int) $auth_cnt_mode + $auth_cnt_default) . " (" . $this->lng->txt("auth_per_default") .
                    ": " . $auth_cnt_default . ")"
                );
            } else {
                $generalSettingsTpl->setVariable(
                    "AUTH_USER_NUM",
                    (int) $auth_cnt_mode
                );
            }
            $generalSettingsTpl->setVariable("AUTH_ID", $mode_name);
            $generalSettingsTpl->setVariable("AUTH_VAL", $mode);
            $generalSettingsTpl->parseCurrentBlock();
        }

        $generalSettingsTpl->setVariable("TXT_CONFIGURE", $this->lng->txt("auth_configure"));

        if ($this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $generalSettingsTpl->setVariable("TXT_AUTH_REMARK", $this->lng->txt("auth_remark_non_local_auth"));
            $generalSettingsTpl->setCurrentBlock('auth_mode_submit');
            $generalSettingsTpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
            $generalSettingsTpl->setVariable("CMD_SUBMIT", "setAuthMode");
            $generalSettingsTpl->parseCurrentBlock();
        }
        
        // auth mode determinitation
        if ($this->initAuthModeDetermination()) {
            $generalSettingsTpl->setVariable('TABLE_AUTH_DETERMINATION', $this->form->getHTML());
        }
        
        // roles table
        $generalSettingsTpl->setVariable(
            "FORMACTION_ROLES",
            $this->ctrl->getFormAction($this)
        );
        $generalSettingsTpl->setVariable("TXT_AUTH_ROLES", $this->lng->txt("auth_active_roles"));
        $generalSettingsTpl->setVariable("TXT_ROLE", $this->lng->txt("obj_role"));
        $generalSettingsTpl->setVariable("TXT_ROLE_AUTH_MODE", $this->lng->txt("auth_role_auth_mode"));
        if ($this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $generalSettingsTpl->setVariable("CMD_SUBMIT_ROLES", "updateAuthRoles");
            $generalSettingsTpl->setVariable('BTN_SUBMIT_ROLES', $this->lng->txt('save'));
        }
        
        $reg_roles = ilObjRole::_lookupRegisterAllowed();
        
        // auth mode selection
        $active_auth_modes = ilAuthUtils::_getActiveAuthModes();

        foreach ($reg_roles as $role) {
            foreach ($active_auth_modes as $auth_name => $auth_key) {
                // do not list auth modes with external login screen
                // even not default, because it can easily be set to
                // a non-working auth mode
                if ($auth_name == "default" || $auth_name == "cas"
                    || $auth_name == 'saml'
                    || $auth_name == "shibboleth" || $auth_name == 'ldap'
                    || $auth_name == 'apache' || $auth_name == "ecs"
                    || $auth_name == "openid") {
                    continue;
                }

                $generalSettingsTpl->setCurrentBlock("auth_mode_selection");

                if ($auth_name == 'default') {
                    $name = $this->lng->txt('auth_' . $auth_name) . " (" . $this->lng->txt('auth_' . ilAuthUtils::_getAuthModeName($auth_key)) . ")";
                } elseif ($id = ilLDAPServer::getServerIdByAuthMode($auth_key)) {
                    $server = ilLDAPServer::getInstanceByServerId($id);
                    $name = $server->getName();
                } elseif ($id = ilSamlIdp::getIdpIdByAuthMode((string) $auth_key)) {
                    $idp = ilSamlIdp::getInstanceByIdpId($id);
                    $name = $idp->getEntityId();
                } else {
                    $name = $this->lng->txt('auth_' . $auth_name);
                }

                $generalSettingsTpl->setVariable("AUTH_MODE_NAME", $name);

                $generalSettingsTpl->setVariable("AUTH_MODE", $auth_name);

                if ($role['auth_mode'] == $auth_name) {
                    $generalSettingsTpl->setVariable("SELECTED_AUTH_MODE", "selected=\"selected\"");
                }

                $generalSettingsTpl->parseCurrentBlock();
            }

            $generalSettingsTpl->setCurrentBlock("roles");
            $generalSettingsTpl->setVariable("ROLE", $role['title']);
            $generalSettingsTpl->setVariable("ROLE_ID", $role['id']);
            $generalSettingsTpl->parseCurrentBlock();
        }

        $this->tpl->setContent($generalSettingsTpl->get());
    }
    
    
    /**
     * displays login information of all installed languages
     *
     * @access public
     * @author Michael Jansen
     */
    public function loginInfoObject() : void
    {
        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->tabs_gui->setTabActive("authentication_settings");
        $this->setSubTabs("authSettings");
        $this->tabs_gui->setSubTabActive("auth_login_editor");
        
        $this->lng->loadLanguageModule("meta");
        
        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.auth_login_messages.html",
            "Services/Authentication"
        );
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt("login_information"));
        $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("login_information_desc"));
        $this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
        $this->initLoginForm();
        $this->tpl->setVariable('LOGIN_INFO', $this->form->getHTML());
    }


    public function cancelObject() : void
    {
        $this->ctrl->redirect($this, "authSettings");
    }

    public function setAuthModeObject() : void
    {
        if (!$this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        
        if (empty($_POST["auth_mode"])) {
            $this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"), $this->ilias->error_obj->MESSAGE);
        }

        $current_auth_mode = $this->settings->get('auth_mode', '');
        if ($_POST["auth_mode"] == $current_auth_mode) {
            ilUtil::sendInfo($this->lng->txt("auth_mode") . ": " . $this->getAuthModeTitle() . " " . $this->lng->txt("auth_mode_not_changed"), true);
            $this->ctrl->redirect($this, 'authSettings');
        }

        switch ($_POST["auth_mode"]) {
            case AUTH_SAML:
                break;

            case AUTH_LDAP:
        
                /*
                if ($this->object->checkAuthLDAP() !== true)
                {
                    ilUtil::sendInfo($this->lng->txt("auth_ldap_not_configured"),true);
                    ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editLDAP", "", false, false)));
                }
                */
                break;
                
                // @fix changed from AUTH_SHIB > is not defined
                case AUTH_SHIBBOLETH:
                if ($this->object->checkAuthSHIB() !== true) {
                    ilUtil::sendFailure($this->lng->txt("auth_shib_not_configured"), true);
                    ilUtil::redirect(
                        $this->getReturnLocation(
                            'authSettings',
                            $this->ctrl->getLinkTargetByClass(
                                ilAuthShibbolethSettingsGUI::class,
                                'settings',
                                '',
                                false,
                                false
                            )
                        )
                    );
                }
                break;

            case ilAuthUtils::AUTH_RADIUS:
                if ($this->object->checkAuthRADIUS() !== true) {
                    ilUtil::sendFailure($this->lng->txt("auth_radius_not_configured"), true);
                    $this->ctrl->redirect($this, 'editRADIUS');
                }
                break;

            case AUTH_SCRIPT:
                if ($this->object->checkAuthScript() !== true) {
                    ilUtil::sendFailure($this->lng->txt("auth_script_not_configured"), true);
                    ilUtil::redirect($this->getReturnLocation("authSettings", $this->ctrl->getLinkTarget($this, "editScript", "", false, false)));
                }
                break;
        }
        
        $this->ilias->setSetting("auth_mode", $_POST["auth_mode"]);
        
        ilUtil::sendSuccess($this->lng->txt("auth_default_mode_changed_to") . " " . $this->getAuthModeTitle(), true);
        $this->ctrl->redirect($this, 'authSettings');
    }
    
    /**
    * Configure soap settings
    */
    public function editSOAPObject() : void
    {
        if (!$this->rbacsystem->checkAccess("read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->tabs_gui->setTabActive('auth_soap');
        
        //set Template
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.auth_soap.html', 'Services/Authentication');
        
        // compose role list
        $role_list = $this->rbacreview->getRolesByFilter(2, $this->object->getId());
        $roles = array();
        
        foreach ($role_list as $role) {
            $roles[$role['obj_id']] = $role['title'];
        }
        
        //set property form gui
        
        $soap_config = new ilPropertyFormGUI();
        $soap_config->setTitle($this->lng->txt("auth_soap_auth"));
        $soap_config->setDescription($this->lng->txt("auth_soap_auth_desc"));
        $soap_config->setFormAction($this->ctrl->getFormAction($this, "editSOAP"));
        if ($this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $soap_config->addCommandButton("saveSOAP", $this->lng->txt("save"));
            $soap_config->addCommandButton("editSOAP", $this->lng->txt("cancel"));
        }
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
        
        // get values in error case
        if (isset($_SESSION["error_post_vars"])) {
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
        } else {
            $active		->setChecked((bool) $this->settings->get("soap_auth_active", (string) false));
            $server		->setValue($this->settings->get("soap_auth_server", ""));
            $port		->setValue((int) $this->settings->get("soap_auth_port", (string) 0));
            $https		->setChecked((bool) $this->settings->get("soap_auth_use_https", (string) false));
            $uri		->setValue($this->settings->get("soap_auth_uri", ""));
            $namespace	->setValue($this->settings->get("soap_auth_namespace", ""));
            $dotnet		->setChecked((bool) $this->settings->get("soap_auth_use_dotnet", (string) false));
            $createuser	->setChecked((bool) $this->settings->get("soap_auth_create_users", (string) false));
            $allowlocal	->setChecked((bool) $this->settings->get("soap_auth_allow_local", (string) false));
            $defaultrole->setValue($this->settings->get("soap_auth_user_default_role", ""));
            $sendmail	->setChecked((bool) $this->settings->get("soap_auth_account_mail", (string) false));
        }
        
        if (!$defaultrole->getValue()) {
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
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle("Test Request");
        $text_prop = new ilTextInputGUI("ext_uid", "ext_uid");
        $form->addItem($text_prop);
        $text_prop2 = new ilTextInputGUI("soap_pw", "soap_pw");
        $form->addItem($text_prop2);
        $cb = new ilCheckboxInputGUI("new_user", "new_user");
        $form->addItem($cb);
         
        $form->addCommandButton(
            "testSoapAuthConnection",
            "Send"
        );
        
        $ret = "";
        if ($this->ctrl->getCmd() == "testSoapAuthConnection") {
            $ret .= "<br />" . ilSOAPAuth::testConnection(
                ilUtil::stripSlashes($_POST["ext_uid"]),
                ilUtil::stripSlashes($_POST["soap_pw"]),
                (boolean) $_POST["new_user"]
            );
        }
        $this->tpl->setVariable("TEST_FORM", $form->getHTML() . $ret);
    }
    
    public function testSoapAuthConnectionObject() : void
    {
        $this->editSOAPObject();
    }
    
    /**
    * validates all input data, save them to database if correct and active chosen auth mode
    *
    * @access	public
    */
    public function saveSOAPObject() : void
    {
        if (!$this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        // validate required data
        if (!$_POST["soap"]["server"]) {
            $this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"), $this->ilias->error_obj->MESSAGE);
        }
        
        // validate port
        if ($_POST["soap"]["server"] != "" && (preg_match("/^[0-9]{0,5}$/", $_POST["soap"]["port"])) == false) {
            $this->ilias->raiseError($this->lng->txt("err_invalid_port"), $this->ilias->error_obj->MESSAGE);
        }
        
        $this->ilSetting->set("soap_auth_server", $_POST["soap"]["server"]);
        $this->ilSetting->set("soap_auth_port", $_POST["soap"]["port"]);
        $this->ilSetting->set("soap_auth_active", $_POST["soap"]["active"]);
        $this->ilSetting->set("soap_auth_uri", $_POST["soap"]["uri"]);
        $this->ilSetting->set("soap_auth_namespace", $_POST["soap"]["namespace"]);
        $this->ilSetting->set("soap_auth_create_users", $_POST["soap"]["create_users"]);
        $this->ilSetting->set("soap_auth_allow_local", $_POST["soap"]["allow_local"]);
        $this->ilSetting->set("soap_auth_account_mail", $_POST["soap"]["account_mail"]);
        $this->ilSetting->set("soap_auth_use_https", $_POST["soap"]["use_https"]);
        $this->ilSetting->set("soap_auth_use_dotnet", $_POST["soap"]["use_dotnet"]);
        $this->ilSetting->set("soap_auth_user_default_role", $_POST["soap"]["user_default_role"]);
        ilUtil::sendSuccess($this->lng->txt("auth_soap_settings_saved"), true);
        
        $this->ctrl->redirect($this, 'editSOAP');
    }

    /**
    * Configure Custom settings
    */
    public function editScriptObject() : void
    {
        if (!$this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        
        if ($_SESSION["error_post_vars"]) {
            $this->tpl->setVariable("AUTH_SCRIPT_NAME", $_SESSION["error_post_vars"]["auth_script"]["name"]);
        } else {
            // set already saved data
            $settings = $this->ilias->getAllSettings();

            $this->tpl->setVariable("AUTH_SCRIPT_NAME", $settings["auth_script_name"]);
        }

        $this->tabs_gui->setTabActive('auth_script');

        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.auth_script.html",
            "Services/Authentication"
        );
        
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
    */
    public function saveScriptObject() : void
    {
        // validate required data
        if (!$_POST["auth_script"]["name"]) {
            $this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"), $this->ilias->error_obj->MESSAGE);
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

        ilUtil::sendSuccess($this->lng->txt("auth_mode_changed_to") . " " . $this->getAuthModeTitle(), true);
        $this->ctrl->redirect($this, 'editScript');
    }
    
    
    /**
    * get the title of auth mode
    *
    * @return language dependent title of auth mode
    */
    public function getAuthModeTitle() : string
    {
        switch ($this->ilias->getSetting("auth_mode")) {
            case AUTH_LOCAL:
                return $this->lng->txt("auth_local");
                break;
            
            case AUTH_LDAP:
                return $this->lng->txt("auth_ldap");
                break;
            
            case AUTH_SHIBBOLETH:
                return $this->lng->txt("auth_shib");
                break;

            case AUTH_SAML:
                return $this->lng->txt("auth_saml");
                break;

            case ilAuthUtils::AUTH_RADIUS:
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
    
    public function updateAuthRolesObject() : void
    {
        if (!$this->rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        
        ilObjRole::_updateAuthMode($_POST['Fobject']);
        
        ilUtil::sendSuccess($this->lng->txt("auth_mode_roles_changed"), true);
        $this->ctrl->redirect($this, 'authSettings');
    }
    
    /**
     * init auth mode determinitation form
     */
    protected function initAuthModeDetermination() : bool
    {
        if (isset($this->form) && is_object($this->form)) {
            return true;
        }
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTableWidth('100%');
        $this->form->setTitle($this->lng->txt('auth_auth_settings'));

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton('updateAuthModeDetermination', $this->lng->txt('save'));
        }

        $det = ilAuthModeDetermination::_getInstance();
        if ($det->getCountActiveAuthModes() <= 1) {
            return true;
        }

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt('auth_auth_mode_determination'));
        $this->form->addItem($header);
        
        $kind = new ilRadioGroupInputGUI($this->lng->txt('auth_kind_determination'), 'kind');
        $kind->setInfo($this->lng->txt('auth_mode_determination_info'));
        $kind->setValue((string) $det->getKind());
        $kind->setRequired(true);
        
        $option_user = new ilRadioOption($this->lng->txt('auth_by_user'), "0");
        $kind->addOption($option_user);
        
        $option_determination = new ilRadioOption($this->lng->txt('auth_automatic'), "1");
                
        $auth_sequenced = $det->getAuthModeSequence();
        $counter = 1;
        foreach ($auth_sequenced as $auth_mode) {
            switch ($auth_mode) {
                // begin-patch ldap_multiple
                case ilLDAPServer::isAuthModeLDAP($auth_mode):
                    $auth_id = ilLDAPServer::getServerIdByAuthMode($auth_mode);
                    $server = ilLDAPServer::getInstanceByServerId($auth_id);
                    $text = $server->getName();
                // end-patch ldap_multiple
                    break;
                case ilAuthUtils::AUTH_RADIUS:
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
                    foreach (ilAuthUtils::getAuthPlugins() as $pl) {
                        $option = $pl->getMultipleAuthModeOptions($auth_mode);
                        $text = $option[$auth_mode]['txt'];
                    }
                    break;
                // end-patch auth_plugin
            }
            
            $pos = new ilTextInputGUI($text, 'position[' . $auth_mode . ']');
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
     */
    public function updateAuthModeDeterminationObject() : void
    {
        $det = ilAuthModeDetermination::_getInstance();
        
        $det->setKind((int) $_POST['kind']);
    
        $pos = $_POST['position'] ? $_POST['position'] : array();
        asort($pos, SORT_NUMERIC);
        
        $counter = 0;
        foreach ($pos as $auth_mode => $dummy) {
            $position[$counter++] = $auth_mode;
        }
        $det->setAuthModeSequence($position ? $position : array());
        $det->save();

        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->authSettingsObject();
    }

    /**
     * Execute command. Called from control class
     */
    public function executeCommand() : bool
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
        }
        
        switch ($next_class) {
            case 'ilopenidconnectsettingsgui':

                $this->tabs_gui->activateTab('auth_oidconnect');

                $oid = new ilOpenIdConnectSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($oid);
                break;

            case 'ilsamlsettingsgui':
                $this->tabs_gui->setTabActive('auth_saml');

                $os = new ilSamlSettingsGUI((int) $this->object->getRefId());
                $this->ctrl->forwardCommand($os);
                break;

            case 'ilregistrationsettingsgui':

                // Enable tabs
                $this->tabs_gui->setTabActive('registration_settings');
                $registration_gui = new ilRegistrationSettingsGUI();
                $this->ctrl->forwardCommand($registration_gui);
                break;

            case 'ilpermissiongui':
            
                // Enable tabs
                $this->tabs_gui->setTabActive('perm_settings');
            
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
                
            case 'illdapsettingsgui':
            
                // Enable Tabs
                $this->tabs_gui->setTabActive('auth_ldap');
                
                $ldap_settings_gui = new ilLDAPSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($ldap_settings_gui);
                break;
                
            case 'ilauthshibbolethsettingsgui':
            
                $this->tabs_gui->setTabActive('auth_shib');
                $shib_settings_gui = new ilAuthShibbolethSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($shib_settings_gui);
                break;

            case 'ilcassettingsgui':

                $this->tabs_gui->setTabActive('auth_cas');
                $cas_settings = new ilCASSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($cas_settings);
                break;
                
            case 'ilradiussettingsgui':
                
                $this->tabs_gui->setTabActive('auth_radius');
                $radius_settings_gui = new ilRadiusSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($radius_settings_gui);
                break;
                

            case 'ilauthloginpageeditorgui':
                
                $this->setSubTabs("authSettings");
                $this->tabs_gui->setTabActive('authentication_settings');
                $this->tabs_gui->setSubTabActive("auth_login_editor");

                $lpe = new ilAuthLoginPageEditorGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($lpe);
                break;

            default:
                if (!$cmd) {
                    $cmd = "authSettings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }
    
    public function getAdminTabs() : void
    {
        $this->getTabs();
    }

    /**
    * get tabs
    */
    public function getTabs() : void
    {
        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        if ($this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "authentication_settings",
                $this->ctrl->getLinkTarget($this, "authSettings"),
                "",
                "",
                ""
            );
        
            $this->tabs_gui->addTarget(
                'registration_settings',
                $this->ctrl->getLinkTargetByClass('ilregistrationsettingsgui', 'view')
            );
            
            $this->tabs_gui->addTarget(
                "auth_ldap",
                $this->ctrl->getLinkTargetByClass('illdapsettingsgui', 'serverList'),
                "",
                "",
                ""
            );

                                         
            #$this->tabs_gui->addTarget("auth_ldap", $this->ctrl->getLinkTarget($this, "editLDAP"),
            #					   "", "", "");
            
            $this->tabs_gui->addTarget('auth_shib', $this->ctrl->getLinkTargetByClass('ilauthshibbolethsettingsgui', 'settings'));

            $this->tabs_gui->addTarget(
                'auth_cas',
                $this->ctrl->getLinkTargetByClass('ilcassettingsgui', 'settings')
            );
                                   
            $this->tabs_gui->addTarget(
                "auth_radius",
                $this->ctrl->getLinkTargetByClass('ilradiussettingsgui', "settings"),
                "",
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                "auth_soap",
                $this->ctrl->getLinkTarget($this, "editSOAP"),
                "",
                "",
                ""
            );
                                 
            $this->tabs_gui->addTarget(
                "apache_auth_settings",
                $this->ctrl->getLinkTarget($this, 'apacheAuthSettings'),
                "",
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                'auth_saml',
                $this->ctrl->getLinkTargetByClass('ilsamlsettingsgui', ilSamlSettingsGUI::DEFAULT_CMD),
                '',
                '',
                ''
            );

            $this->tabs_gui->addTab(
                'auth_oidconnect',
                $this->lng->txt('auth_oidconnect'),
                $this->ctrl->getLinkTargetByClass('ilopenidconnectsettingsgui')
            );
        }

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }
    
    /**
    * set sub tabs
    */
    public function setSubTabs(string $a_tab) : void
    {
        $this->lng->loadLanguageModule('auth');
        
        switch ($a_tab) {
            case 'authSettings':
                if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->tabs_gui->addSubTabTarget(
                        "auth_settings",
                        $this->ctrl->getLinkTarget($this, 'authSettings'),
                        ""
                    );
                }
                if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->tabs_gui->addSubTabTarget(
                        'auth_login_editor',
                        $this->ctrl->getLinkTargetByClass('ilauthloginpageeditorgui', ''),
                        ''
                    );
                }
                break;
        }
    }


    public function apacheAuthSettingsObject(?ilPropertyFormGUI $form = null) : void
    {
        $this->tabs_gui->setTabActive("apache_auth_settings");

        if (null === $form) {
            $form = $this->getApacheAuthSettingsForm();

            $settings = new ilSetting('apache_auth');
            $settingsMap = $settings->getAll();

            $path = ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt';
            if (file_exists($path) && is_readable($path)) {
                $settingsMap['apache_auth_domains'] = file_get_contents($path);
            }
            
            $form->setValuesByArray($settingsMap);
        }
        $this->tpl->setVariable('ADM_CONTENT', $form->getHtml());
    }

    public function saveApacheSettingsObject() : void
    {
        $form = $this->getApacheAuthSettingsForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $settings = new ilSetting('apache_auth');
            $fields = [
                'apache_auth_indicator_name', 'apache_auth_indicator_value',
                'apache_enable_auth', 'apache_enable_local', 'apache_local_autocreate',
                'apache_enable_ldap', 'apache_auth_username_config_type',
                'apache_auth_username_direct_mapping_fieldname',
                'apache_default_role', 'apache_auth_target_override_login_page',
                'apache_auth_enable_override_login_page',
                'apache_auth_authenticate_on_login_page',
                'apache_ldap_sid'
            ];

            foreach ($fields as $field) {
                $settings->set($field, (string) $form->getInput($field));
            }

            if ($form->getInput('apache_enable_auth')) {
                $this->ilias->setSetting('apache_active', '1');
            } else {
                $this->ilias->setSetting('apache_active', '0');
                global $DIC;

                $ilSetting = $DIC['ilSetting'];
                if ((int) $ilSetting->get("auth_mode", '0') === AUTH_APACHE) {
                    $ilSetting->set("auth_mode", (string) AUTH_LOCAL);
                }
            }

            $allowedDomains = $this->validateApacheAuthAllowedDomains((string) $form->getInput('apache_auth_domains'));
            file_put_contents(ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt', $allowedDomains);
            
            ilUtil::sendSuccess($this->lng->txt('apache_settings_changed_success'), true);
            $this->ctrl->redirect($this, 'apacheAuthSettings');
        } else {
            $this->apacheAuthSettingsObject($form);
        }
    }

    public function getApacheAuthSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('apache_settings'));

        $chb_enabled = new ilCheckboxInputGUI($this->lng->txt('apache_enable_auth'), 'apache_enable_auth');
        $chb_enabled->setValue('1');
        $form->addItem($chb_enabled);

        $chb_local_create_account = new ilCheckboxInputGUI($this->lng->txt('apache_autocreate'), 'apache_local_autocreate');
        $chb_local_create_account->setValue('1');
        $chb_enabled->addSubitem($chb_local_create_account);

        $roles = $this->rbacreview->getGlobalRolesArray();
        $select = new ilSelectInputGUI($this->lng->txt('apache_default_role'), 'apache_default_role');
        $roleOptions = [];
        foreach ($roles as $role) {
            $roleOptions[$role['obj_id']] = ilObject::_lookupTitle($role['obj_id']);
        }
        $select->setOptions($roleOptions);
        $select->setValue(4);

        $chb_local_create_account->addSubitem($select);

        $chb_local = new ilCheckboxInputGUI($this->lng->txt('apache_enable_local'), 'apache_enable_local');
        $chb_local->setValue('1');
        $form->addItem($chb_local);

        $chb_ldap = new ilCheckboxInputGUI($this->lng->txt('apache_enable_ldap'), 'apache_enable_ldap');
        $chb_local->setValue('1');

        $chb_ldap->setInfo($this->lng->txt('apache_ldap_hint_ldap_must_be_configured'));
        
        $this->lng->loadLanguageModule('auth');

        $servers = ilLDAPServer::getServerIds();
        if (count($servers)) {
            $ldap_server_select = new ilSelectInputGUI($this->lng->txt('auth_ldap_server_ds'), 'apache_ldap_sid');
            $options[0] = $this->lng->txt('select_one');
            foreach ($servers as $server_id) {
                $ldap_server = new ilLDAPServer($server_id);
                $options[$server_id] = $ldap_server->getName();
            }
            $ldap_server_select->setOptions($options);
            $ldap_server_select->setRequired(true);

            $ds = ilLDAPServer::getDataSource(AUTH_APACHE);
            $ldap_server_select->setValue($ds);

            $chb_ldap->addSubItem($ldap_server_select);
        }
        $form->addItem($chb_ldap);

        $txt = new ilTextInputGUI($this->lng->txt('apache_auth_indicator_name'), 'apache_auth_indicator_name');
        $txt->setRequired(true);
        $form->addItem($txt);

        $txt = new ilTextInputGUI($this->lng->txt('apache_auth_indicator_value'), 'apache_auth_indicator_value');
        $txt->setRequired(true);
        $form->addItem($txt);

        $chb = new ilCheckboxInputGUI($this->lng->txt('apache_auth_enable_override_login'), 'apache_auth_enable_override_login_page');
        $chb->setValue('1');
        $form->addItem($chb);

        $txt = new ilTextInputGUI($this->lng->txt('apache_auth_target_override_login'), 'apache_auth_target_override_login_page');
        $txt->setRequired(true);
        $chb->addSubItem($txt);

        $chb = new ilCheckboxInputGUI($this->lng->txt('apache_auth_authenticate_on_login_page'), 'apache_auth_authenticate_on_login_page');
        $chb->setValue('1');
        $form->addItem($chb);

        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('apache_auth_username_config'));
        $form->addItem($sec);

        $rag = new ilRadioGroupInputGUI($this->lng->txt('apache_auth_username_config_type'), 'apache_auth_username_config_type');
        $form->addItem($rag);

        $rao = new ilRadioOption($this->lng->txt('apache_auth_username_direct_mapping'), "1");
        $rag->addOption($rao);

        $txt = new ilTextInputGUI($this->lng->txt('apache_auth_username_direct_mapping_fieldname'), 'apache_auth_username_direct_mapping_fieldname');
        //$txt->setRequired(true);
        $rao->addSubItem($txt);

        $rao = new ilRadioOption($this->lng->txt('apache_auth_username_extended_mapping'), "2");
        $rao->setDisabled(true);
        $rag->addOption($rao);

        $rao = new ilRadioOption($this->lng->txt('apache_auth_username_by_function'), "3");
        $rag->addOption($rao);

        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('apache_auth_security'));
        $form->addItem($sec);
        
        $txt = new ilTextAreaInputGUI($this->lng->txt('apache_auth_domains'), 'apache_auth_domains');
        $txt->setInfo($this->lng->txt('apache_auth_domains_description'));
        
        $form->addItem($txt);

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $form->addCommandButton('saveApacheSettings', $this->lng->txt('save'));
        }
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }
    
    private function validateApacheAuthAllowedDomains(string $text) : string
    {
        return implode("\n", preg_split("/[\r\n]+/", $text));
    }

    public function registrationSettingsObject()
    {
        $registration_gui = new ilRegistrationSettingsGUI();
        $this->ctrl->redirect($registration_gui);
    }
}
