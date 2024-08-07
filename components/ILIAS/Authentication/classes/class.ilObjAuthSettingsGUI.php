<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @ilCtrl_Calls ilObjAuthSettingsGUI: ilPermissionGUI, ilRegistrationSettingsGUI, ilLDAPSettingsGUI
 * @ilCtrl_Calls ilObjAuthSettingsGUI: ilAuthShibbolethSettingsGUI, ilCASSettingsGUI
 * @ilCtrl_Calls ilObjAuthSettingsGUI: ilSamlSettingsGUI, ilOpenIdConnectSettingsGUI
 */
class ilObjAuthSettingsGUI extends ilObjectGUI
{
    private ilLogger $logger;
    private ILIAS\UI\Factory $ui;
    private ILIAS\UI\Renderer $renderer;
    private ILIAS\Http\Services $http;

    private ?ilPropertyFormGUI $form;

    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        $this->type = "auth";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        global $DIC;
        $this->logger = $DIC->logger()->auth();

        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();

        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('auth');
    }

    public function viewObject(): void
    {
        $this->authSettingsObject();
    }

    /**
    * display settings menu
    */
    public function authSettingsObject(): void
    {
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->tabs_gui->setTabActive('authentication_settings');
        $this->setSubTabs('authSettings');
        $this->tabs_gui->setSubTabActive("auth_settings");

        $generalSettingsTpl = new ilTemplate('tpl.auth_general.html', true, true, 'components/ILIAS/Authentication');

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

        $generalSettingsTpl->setVariable("TXT_SCRIPT", $this->lng->txt("auth_script"));

        $generalSettingsTpl->setVariable("TXT_APACHE", $this->lng->txt("auth_apache"));

        $auth_cnt = ilObjUser::_getNumberOfUsersPerAuthMode();
        $auth_modes = ilAuthUtils::_getAllAuthModes();
        $valid_modes = [
            ilAuthUtils::AUTH_LOCAL,
            ilAuthUtils::AUTH_LDAP,
            ilAuthUtils::AUTH_SHIBBOLETH,
            ilAuthUtils::AUTH_SAML,
            ilAuthUtils::AUTH_CAS,
            ilAuthUtils::AUTH_APACHE,
            ilAuthUtils::AUTH_OPENID_CONNECT
        ];
        // icon handlers

        $icon_ok = $this->renderer->render(
            $this->ui->symbol()->icon()->custom(ilUtil::getImagePath("standard/icon_ok.svg"), $this->lng->txt("enabled"))
        );
        $icon_not_ok = $this->renderer->render(
            $this->ui->symbol()->icon()->custom(ilUtil::getImagePath("standard/icon_not_ok.svg"), $this->lng->txt("disabled"))
        );

        $this->logger->debug(print_r($auth_modes, true));
        foreach ($auth_modes as $mode => $mode_name) {
            if (!in_array($mode, $valid_modes, true) && !ilLDAPServer::isAuthModeLDAP((string) $mode) && !ilSamlIdp::isAuthModeSaml((string) $mode)) {
                continue;
            }

            $generalSettingsTpl->setCurrentBlock('auth_mode');

            if (ilLDAPServer::isAuthModeLDAP((string) $mode)) {
                $server = ilLDAPServer::getInstanceByServerId(ilLDAPServer::getServerIdByAuthMode($mode));
                $generalSettingsTpl->setVariable("AUTH_NAME", $server->getName());
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', $server->isActive() ? $icon_ok : $icon_not_ok);
            } elseif (ilSamlIdp::isAuthModeSaml((string) $mode)) {
                $idp = ilSamlIdp::getInstanceByIdpId(ilSamlIdp::getIdpIdByAuthMode($mode));
                $generalSettingsTpl->setVariable('AUTH_NAME', $idp->getEntityId());
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', $idp->isActive() ? $icon_ok : $icon_not_ok);
            } elseif ($mode === ilAuthUtils::AUTH_OPENID_CONNECT) {
                $generalSettingsTpl->setVariable("AUTH_NAME", $this->lng->txt("auth_" . $mode_name));
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', ilOpenIdConnectSettings::getInstance()->getActive() ? $icon_ok : $icon_not_ok);
            } else {
                $generalSettingsTpl->setVariable("AUTH_NAME", $this->lng->txt("auth_" . $mode_name));
                $generalSettingsTpl->setVariable('AUTH_ACTIVE', $this->ilias->getSetting($mode_name . '_active') || (int) $mode === ilAuthUtils::AUTH_LOCAL ? $icon_ok : $icon_not_ok);
            }

            $auth_cnt_mode = $auth_cnt[$mode_name] ?? 0;
            if ($this->settings->get('auth_mode') === (string) $mode) {
                $generalSettingsTpl->setVariable("AUTH_CHECKED", "checked=\"checked\"");
                $auth_cnt_default = $auth_cnt["default"] ?? 0;
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

        if ($this->rbac_system->checkAccess("write", $this->object->getRefId())) {
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
        if ($this->rbac_system->checkAccess("write", $this->object->getRefId())) {
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
                if ($auth_name === "default" || $auth_name === "cas"
                    || $auth_name === 'saml'
                    || $auth_name === "shibboleth" || $auth_name === 'ldap'
                    || $auth_name === 'apache' || $auth_name === "ecs"
                    || $auth_name === "openid") {
                    continue;
                }

                $generalSettingsTpl->setCurrentBlock("auth_mode_selection");

                if ($auth_name === 'default') {
                    $name = $this->lng->txt('auth_' . $auth_name) . " (" . $this->lng->txt('auth_' . ilAuthUtils::_getAuthModeName($auth_key)) . ")";
                } elseif ($id = ilLDAPServer::getServerIdByAuthMode((string) $auth_key)) {
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

                if ($role['auth_mode'] === $auth_name) {
                    $generalSettingsTpl->setVariable("SELECTED_AUTH_MODE", "selected=\"selected\"");
                }

                $generalSettingsTpl->parseCurrentBlock();
            }

            $generalSettingsTpl->setCurrentBlock("roles");
            $generalSettingsTpl->setVariable("ROLE", $role['title']);
            // r_ is add to  the role id only for the dictOf transformation used later to parse the input
            $generalSettingsTpl->setVariable("ROLE_ID", "r_" . $role['id']);
            $generalSettingsTpl->parseCurrentBlock();
        }

        $this->tpl->setContent($generalSettingsTpl->get());
    }


    /**
     * displays login information of all installed languages
     *
     * @author Michael Jansen
     */
    public function loginInfoObject(): void
    {
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
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
            "components/ILIAS/Authentication"
        );
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt("login_information"));
        $this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("login_information_desc"));
        $this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
        $this->tpl->setVariable('LOGIN_INFO', $this->form->getHTML());
    }


    public function cancelObject(): void
    {
        $this->ctrl->redirect($this, "authSettings");
    }

    public function setAuthModeObject(): void
    {
        if (!$this->rbac_system->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        $this->logger->debug('auth mode available:' . $this->request_wrapper->has("auth_mode"));

        if (!$this->http->wrapper()->post()->has("auth_mode")) {
            $this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"), $this->ilias->error_obj->MESSAGE);
        }
        $new_auth_mode = $this->http->wrapper()->post()->retrieve("auth_mode", $this->refinery->to()->string());
        $this->logger->debug('auth mode:' . $new_auth_mode);
        $current_auth_mode = $this->settings->get('auth_mode', '');
        if ($new_auth_mode === $current_auth_mode) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("auth_mode") . ": " . $this->getAuthModeTitle() . " " . $this->lng->txt("auth_mode_not_changed"), true);
            $this->ctrl->redirect($this, 'authSettings');
        }

        switch ((int) $new_auth_mode) {
            case ilAuthUtils::AUTH_SAML:
                break;

            case ilAuthUtils::AUTH_LDAP:

                /*
                if ($this->object->checkAuthLDAP() !== true)
                {
                    ilUtil::sendInfo($this->lng->txt("auth_ldap_not_configured"),true);
                    ilUtil::redirect($this->getReturnLocation("authSettings",$this->ctrl->getLinkTarget($this,"editLDAP", "", false, false)));
                }
                */
                break;

                // @fix changed from AUTH_SHIB > is not defined
            case ilAuthUtils::AUTH_SHIBBOLETH:
                if ($this->object->checkAuthSHIB() !== true) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("auth_shib_not_configured"), true);
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

            case ilAuthUtils::AUTH_SCRIPT:
                if ($this->object->checkAuthScript() !== true) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("auth_script_not_configured"), true);
                    ilUtil::redirect($this->getReturnLocation("authSettings", $this->ctrl->getLinkTarget($this, "editScript", "", false, false)));
                }
                break;
        }

        $this->ilias->setSetting("auth_mode", $new_auth_mode);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("auth_default_mode_changed_to") . " " . $this->getAuthModeTitle(), true);
        $this->ctrl->redirect($this, 'authSettings');
    }

    private function buildSOAPForm(
        string $submit_action
    ): \ILIAS\UI\Component\Input\Container\Form\Form {
        // compose role list
        $role_list = $this->rbac_review->getRolesByFilter(2, $this->object->getId());
        $roles = [];

        foreach ($role_list as $role) {
            $roles[$role['obj_id']] = $role['title'];
        }

        $active = $this->ui->input()->field()
                                    ->checkbox($this->lng->txt("active"))
                                    ->withValue((bool) $this->settings->get("soap_auth_active", ""));

        $server = $this->ui->input()->field()->text(
            $this->lng->txt("server"),
            $this->lng->txt("auth_soap_server_desc")
        )->withRequired(true)
         ->withMaxLength(256)
         ->withValue($this->settings->get("soap_auth_server", ""));

        $port = $this->ui->input()->field()->numeric(
            $this->lng->txt("port"),
            $this->lng->txt("auth_soap_port_desc")
        )->withAdditionalTransformation($this->refinery->int()->isGreaterThan(0))
        ->withAdditionalTransformation(
            $this->refinery->int()->isLessThan(65536)
        )->withValue((int) $this->settings->get("soap_auth_port", "0"));

        $use_https = $this->ui->input()->field()->checkbox($this->lng->txt("auth_soap_use_https"))
        ->withValue((bool) $this->settings->get("soap_auth_use_https", ""));

        $uri = $this->ui->input()->field()->text(
            $this->lng->txt("uri"),
            $this->lng->txt("auth_soap_uri_desc")
        )->withMaxLength(256)
        ->withValue($this->settings->get("soap_auth_uri", ""));

        $namespace = $this->ui->input()->field()->text(
            $this->lng->txt("auth_soap_namespace"),
            $this->lng->txt("auth_soap_namespace_desc")
        )->withMaxLength(256)
        ->withValue($this->settings->get("soap_auth_namespace", ""));

        $dotnet = $this->ui->input()->field()->checkbox($this->lng->txt("auth_soap_use_dotnet"))
        ->withValue((bool) $this->settings->get("soap_auth_use_dotnet", ""));

        $createuser = $this->ui->input()->field()->checkbox(
            $this->lng->txt("auth_create_users"),
            $this->lng->txt("auth_soap_create_users_desc")
        )->withValue((bool) $this->settings->get("soap_auth_create_users", ""));

        $sendmail = $this->ui->input()->field()->checkbox(
            $this->lng->txt("user_send_new_account_mail"),
            $this->lng->txt("auth_new_account_mail_desc")
        )->withValue((bool) $this->settings->get("soap_auth_account_mail", ""));

        $defaultrole = $this->ui->input()->field()->select(
            $this->lng->txt("auth_user_default_role"),
            $roles,
            $this->lng->txt("auth_soap_user_default_role_desc")
        )->withValue($this->settings->get("soap_auth_user_default_role", "4"))
        ->withAdditionalTransformation($this->refinery->int()->isGreaterThan(0));

        $allowlocal = $this->ui->input()->field()->checkbox(
            $this->lng->txt("auth_allow_local"),
            $this->lng->txt("auth_soap_allow_local_desc")
        )->withValue((bool) $this->settings->get("soap_auth_user_default_role", ""));

        $form = $this->ui->input()->container()->form()->standard(
            $submit_action,
            [ "active" => $active,
              "server" => $server,
              "port" => $port,
              "use_https" => $use_https,
              "uri" => $uri,
              "namespace" => $namespace,
              "dotnet" => $dotnet,
              "createuser" => $createuser,
              "sendmail" => $sendmail,
              "defaultrole" => $defaultrole,
              "allowlocal" => $allowlocal
            ]
        );
        return $form;
    }

    private function buildSOAPTestForm(
        string $submit_action
    ): \ILIAS\UI\Component\Input\Container\Form\Form {
        $ext_uid = $this->ui->input()->field()->text(
            "ext_uid"
        );
        $soap_pw = $this->ui->input()->field()->text(
            "soap_pw"
        );
        $new_user = $this->ui->input()->field()
                           ->checkbox("new_user");
        $form = $this->ui->input()->container()->form()->standard(
            $submit_action,
            [ "ext_uid" => $ext_uid,
              "soap_pw" => $soap_pw,
              "new_user" => $new_user
            ]
        )->withSubmitLabel("Send");
        return $form;
    }


    /**
    * Configure soap settings
    */
    public function editSOAPObject(): void
    {
        if (!$this->rbac_system->checkAccess("read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $soap_form = $this->buildSOAPForm($this->ctrl->getFormAction($this, "saveSOAP"));
        $test_form = $this->buildSOAPTestForm($this->ctrl->getFormAction($this, "testSoapAuthConnection"));

        $this->tabs_gui->setTabActive('auth_soap');
        $panel = $this->ui->panel()->standard("SOAP", [$soap_form, $test_form]);
        $this->tpl->setContent($this->renderer->render($panel));
    }

    public function testSoapAuthConnectionObject(): void
    {
        if (!$this->rbac_system->checkAccess("read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }


        $soap_form = $this->buildSOAPForm($this->ctrl->getFormAction($this, "saveSOAP"));
        $test_form = $this->buildSOAPTestForm($this->ctrl->getFormAction($this, "testSoapAuthConnection"));
        $panel_content = [$soap_form, $test_form];
        if ($this->request->getMethod() == "POST") {
            $test_form = $test_form->withRequest($this->request);
            $result = $test_form->getData();
            if (!is_null($result)) {
                $panel_content[] = $this->ui->legacy(
                    ilSOAPAuth::testConnection($result["ext_uid"], $result["soap_pw"], $result["new_user"])
                );
            }
        }
        $this->tabs_gui->setTabActive('auth_soap');
        $panel = $this->ui->panel()->standard("SOAP", $panel_content);
        $this->tpl->setContent($this->renderer->render($panel));
    }

    /**
    * validates all input data, save them to database if correct and active chosen auth mode
    */
    public function saveSOAPObject(): void
    {
        if (!$this->rbac_system->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $soap_form = $this->buildSOAPForm($this->ctrl->getFormAction($this, "saveSOAP"));
        $test_form = $this->buildSOAPTestForm($this->ctrl->getFormAction($this, "testSoapAuthConnection"));
        if ($this->request->getMethod() === "POST") {
            $soap_form = $soap_form->withRequest($this->request);
            $result = $soap_form->getData();
            if (!is_null($result)) {
                $this->settings->set("soap_auth_active", (string) $result["active"]);
                $this->settings->set("soap_auth_server", $result["server"]);
                $this->settings->set("soap_auth_port", (string) $result["port"]);
                $this->settings->set("soap_auth_use_https", (string) $result["use_https"]);
                $this->settings->set("soap_auth_uri", $result["uri"]);
                $this->settings->set("soap_auth_namespace", $result["namespace"]);
                $this->settings->set("soap_auth_use_dotnet", (string) $result["dotnet"]);
                $this->settings->set("soap_auth_create_users", (string) $result["createuser"]);
                $this->settings->set("soap_auth_account_mail", (string) $result["sendmail"]);
                $this->settings->set("soap_auth_user_default_role", (string) $result["defaultrole"]);
                $this->settings->set("soap_auth_allow_local", (string) $result["allowlocal"]);

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("auth_soap_settings_saved"), true);
                $this->logger->info("data" . print_r($result, true));
                $this->ctrl->redirect($this, 'editSOAP');
            }
        }

        $this->tabs_gui->setTabActive('auth_soap');
        $panel = $this->ui->panel()->standard("SOAP", [$soap_form, $test_form]);
        $this->tpl->setContent($this->renderer->render($panel));
    }

    /**
    * Configure Custom settings
    */
    public function editScriptObject(): void
    {
        if (!$this->rbac_system->checkAccess("write", $this->object->getRefId())) {
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
            "components/ILIAS/Authentication"
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
    public function saveScriptObject(): void
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
        $this->ilias->setSetting("auth_mode", (string) ilAuthUtils::AUTH_SCRIPT);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("auth_mode_changed_to") . " " . $this->getAuthModeTitle(), true);
        $this->ctrl->redirect($this, 'editScript');
    }


    /**
    * get the title of auth mode
    *
    * @return string language dependent title of auth mode
    */
    public function getAuthModeTitle(): string
    {
        switch ($this->ilias->getSetting("auth_mode")) {
            case ilAuthUtils::AUTH_LOCAL:
                return $this->lng->txt("auth_local");
                break;

            case ilAuthUtils::AUTH_LDAP:
                return $this->lng->txt("auth_ldap");
                break;

            case ilAuthUtils::AUTH_SHIBBOLETH:
                return $this->lng->txt("auth_shib");
                break;

            case ilAuthUtils::AUTH_SAML:
                return $this->lng->txt("auth_saml");
                break;


            case ilAuthUtils::AUTH_SCRIPT:
                return $this->lng->txt("auth_script");
                break;

            case ilAuthUtils::AUTH_APACHE:
                return $this->lng->txt("auth_apache");
                break;

            default:
                return $this->lng->txt("unknown");
                break;
        }
    }

    public function updateAuthRolesObject(): void
    {
        if (!$this->rbac_system->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        if (!$this->http->wrapper()->post()->has("Fobject")) {
            $this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"), $this->ilias->error_obj->MESSAGE);
        }
        $f_object_unconverted = $this->http->wrapper()->post()->retrieve(
            "Fobject",
            $this->refinery->to()->dictOf($this->refinery->to()->string())
        );
        // remove the r_ from the role id. It is only added for the dictOf transformation
        $f_object = [];
        foreach ($f_object_unconverted as $role_id => $auth_mode) {
            $f_object[substr($role_id, 2)] = $auth_mode;
        }
        ilObjRole::_updateAuthMode($f_object);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("auth_mode_roles_changed"), true);
        $this->ctrl->redirect($this, 'authSettings');
    }

    /**
     * init auth mode determinitation form
     */
    protected function initAuthModeDetermination(): bool
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
            return false;
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
        $text = "";
        foreach ($auth_sequenced as $auth_mode) {
            switch ($auth_mode) {
                case ilLDAPServer::isAuthModeLDAP((string) $auth_mode):
                    $auth_id = ilLDAPServer::getServerIdByAuthMode($auth_mode);
                    $server = ilLDAPServer::getInstanceByServerId($auth_id);
                    $text = $server->getName();
                    break;
                case ilAuthUtils::AUTH_LOCAL:
                    $text = $this->lng->txt('auth_local');
                    break;
                case ilAuthUtils::AUTH_SOAP:
                    $text = $this->lng->txt('auth_soap');
                    break;
                case ilAuthUtils::AUTH_APACHE:
                    $text = $this->lng->txt('auth_apache');
                    break;
                default:
                    foreach (ilAuthUtils::getAuthPlugins() as $pl) {
                        $option = $pl->getMultipleAuthModeOptions($auth_mode);
                        $text = $option[$auth_mode]['txt'];
                    }
                    break;
            }

            $pos = new ilTextInputGUI($text, 'position[m' . $auth_mode . ']');
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
    public function updateAuthModeDeterminationObject(): void
    {
        if (!$this->rbac_system->checkAccess("write", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        if (!$this->http->wrapper()->post()->has("kind")) {
            $this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"), $this->ilias->error_obj->MESSAGE);
        }
        $kind = $this->http->wrapper()->post()->retrieve("kind", $this->refinery->kindlyTo()->int());
        if ($kind === ilAuthModeDetermination::TYPE_AUTOMATIC && !$this->http->wrapper()->post()->has("position")) {
            $this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"), $this->ilias->error_obj->MESSAGE);
        }

        $det = ilAuthModeDetermination::_getInstance();

        $det->setKind($kind);
        if ($kind === ilAuthModeDetermination::TYPE_AUTOMATIC) {
            $pos = $this->http->wrapper()->post()->retrieve(
                "position",
                $this->refinery->to()->dictOf($this->refinery->kindlyTo()->int())
            );
            $this->logger->debug('pos mode:' . print_r($pos, true));
            asort($pos, SORT_NUMERIC);
            $this->logger->debug('pos mode:' . print_r($pos, true));
            $counter = 0;
            $position = [];
            foreach (array_keys($pos) as $auth_mode) {
                $position[$counter++] = substr($auth_mode, 1);
            }
            $this->logger->debug('position mode:' . print_r($position, true));
            $det->setAuthModeSequence($position);
        }
        $det->save();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'authSettings');
    }

    /**
     * Execute command. Called from control class
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilopenidconnectsettingsgui':

                $this->tabs_gui->activateTab('auth_oidconnect');

                $oid = new ilOpenIdConnectSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($oid);
                break;

            case 'ilsamlsettingsgui':
                $this->tabs_gui->setTabActive('auth_saml');

                $os = new ilSamlSettingsGUI($this->object->getRefId());
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
                $this->ctrl->forwardCommand($perm_gui);
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

            case 'ilauthloginpageeditorgui':

                $this->setSubTabs("authSettings");
                $this->tabs_gui->setTabActive('authentication_settings');
                $this->tabs_gui->setSubTabActive("auth_login_editor");

                $lpe = new ilAuthLoginPageEditorGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($lpe);
                break;

            case 'ilauthlogoutpageeditorgui':

                $this->setSubTabs("authSettings");
                $this->tabs_gui->setTabActive('authentication_settings');
                $this->tabs_gui->setSubTabActive("logout_editor");

                $lpe = new ilAuthLogoutPageEditorGUI($this->object->getRefId());
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
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    /**
    * get tabs
    */
    protected function getTabs(): void
    {
        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
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

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
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
    public function setSubTabs(string $a_tab): void
    {
        $this->lng->loadLanguageModule('auth');

        if ($a_tab === 'authSettings') {
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
            if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
                $this->tabs_gui->addSubTabTarget(
                    'logout_editor',
                    $this->ctrl->getLinkTargetByClass(ilAuthLogoutPageEditorGUI::class, ''),
                    ''
                );
            }
        }
    }


    public function apacheAuthSettingsObject(?ilPropertyFormGUI $form = null): void
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

    public function saveApacheSettingsObject(): void
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
                if ((int) $ilSetting->get("auth_mode", '0') === ilAuthUtils::AUTH_APACHE) {
                    $ilSetting->set("auth_mode", (string) ilAuthUtils::AUTH_LOCAL);
                }
            }

            $allowedDomains = $this->validateApacheAuthAllowedDomains((string) $form->getInput('apache_auth_domains'));
            file_put_contents(ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt', $allowedDomains);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('apache_settings_changed_success'), true);
            $this->ctrl->redirect($this, 'apacheAuthSettings');
        } else {
            $this->apacheAuthSettingsObject($form);
        }
    }

    public function getApacheAuthSettingsForm(): ilPropertyFormGUI
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

        $roles = $this->rbac_review->getGlobalRolesArray();
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

            $ds = ilLDAPServer::getDataSource(ilAuthUtils::AUTH_APACHE);
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

    private function validateApacheAuthAllowedDomains(string $text): string
    {
        return implode("\n", preg_split("/[\r\n]+/", $text));
    }

    public function registrationSettingsObject(): void
    {
        $registration_gui = new ilRegistrationSettingsGUI();
        $this->ctrl->redirect($registration_gui);
    }
}
