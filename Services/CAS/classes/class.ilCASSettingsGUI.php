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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilCASSettingsGUI
{
    const SYNC_DISABLED = 0;
    const SYNC_CAS = 1;
    const SYNC_LDAP = 2;

    private ilCASSettings $settings;

    private int $ref_id;

    private \ilGlobalTemplateInterface $tpl;
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs_gui;
    private ilLanguage $lng;
    private ilRbacSystem $rbacSystem;
    private ilRbacReview $rbacReview;
    private ilErrorHandling $ilErr;
    
    /**
     * Constructor
     *
     * @param int object auth ref_id
     *
     */
    public function __construct(int $a_auth_ref_id)
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->rbacSystem = $DIC->rbac()->system();
        $this->ilErr = $DIC['ilErr'];
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('auth');
        
        $this->ref_id = $a_auth_ref_id;

        $this->settings = ilCASSettings::getInstance();
    }

    protected function getSettings() : ilCASSettings
    {
        return $this->settings;
    }
    
    /**
     * Execute command
     */
    public function executeCommand() : bool
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("settings");
        
        if (!$this->rbacSystem->checkAccess("visible,read", $this->ref_id)) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
        }

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "settings";
                }
                $this->$cmd();
                break;
        }
        return true;
    }


    /**
     * Init cas settings
     */
    protected function initFormSettings() : ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('auth');
        $this->lng->loadLanguageModule('radius');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitle($this->lng->txt('auth_cas_auth'));
        $form->setDescription($this->lng->txt("auth_cas_auth_desc"));

        // Form checkbox
        $check = new ilCheckboxInputGUI($this->lng->txt("active"), 'active');
        $check->setChecked($this->getSettings()->isActive() ? true : false);
        $check->setValue("1");
        $form->addItem($check);

        $text = new ilTextInputGUI($this->lng->txt('server'), 'server');
        $text->setValue($this->getSettings()->getServer());
        $text->setRequired(true);
        $text->setInfo($this->lng->txt('auth_cas_server_desc'));
        $text->setSize(64);
        $text->setMaxLength(255);
        $form->addItem($text);

        $port = new ilNumberInputGUI($this->lng->txt("port"), 'port');
        $port->setValue((string) $this->getSettings()->getPort());
        $port->setRequired(true);
        $port->setMinValue(0);
        $port->setMaxValue(65535);
        $port->setSize(5);
        $port->setMaxLength(5);
        $port->setInfo($this->lng->txt('auth_cas_port_desc'));
        $form->addItem($port);

        $text = new ilTextInputGUI($this->lng->txt('uri'), 'uri');
        $text->setValue($this->getSettings()->getUri());
        $text->setRequired(true);
        $text->setInfo($this->lng->txt('auth_cas_uri_desc'));
        $text->setSize(64);
        $text->setMaxLength(255);
        $form->addItem($text);

        // User synchronization
        // 0: Disabled
        // 1: CAS
        // 2: LDAP
        $sync = new ilRadioGroupInputGUI($this->lng->txt('auth_sync'), 'sync');
        $sync->setRequired(true);
        #$sync->setInfo($this->lng->txt('auth_radius_sync_info'));
        $form->addItem($sync);

        // Disabled
        $dis = new ilRadioOption(
            $this->lng->txt('disabled'),
            (string) self::SYNC_DISABLED,
            ''
        );
        #$dis->setInfo($this->lng->txt('auth_radius_sync_disabled_info'));
        $sync->addOption($dis);

        // CAS
        $rad = new ilRadioOption(
            $this->lng->txt('auth_sync_cas'),
            (string) self::SYNC_CAS,
            ''
        );
        $rad->setInfo($this->lng->txt('auth_sync_cas_info'));
        $sync->addOption($rad);

        $select = new ilSelectInputGUI($this->lng->txt('auth_user_default_role'), 'role');
        $select->setOptions($this->prepareRoleSelection());
        $select->setValue($this->getSettings()->getDefaultRole());
        $rad->addSubItem($select);



        // LDAP
        $server_ids = ilLDAPServer::getAvailableDataSources(ilAuthUtils::AUTH_CAS);

        if (count($server_ids)) {
            $ldap = new ilRadioOption(
                $this->lng->txt('auth_radius_ldap'),
                (string) ilCASSettings::SYNC_LDAP,
                ''
            );
            $ldap->setInfo($this->lng->txt('auth_radius_ldap_info'));
            $sync->addOption($ldap);

            $ldap_server_select = new ilSelectInputGUI($this->lng->txt('auth_ldap_server_ds'), 'ldap_sid');
            $options[0] = $this->lng->txt('select_one');
            foreach ($server_ids as $ldap_sid) {
                $ldap_server = new ilLDAPServer($ldap_sid);
                $options[$ldap_sid] = $ldap_server->getName();
            }
            $ldap_server_select->setOptions($options);
            $ldap_server_select->setRequired(true);
            $ds = ilLDAPServer::getDataSource(ilAuthUtils::AUTH_CAS);
            $ldap_server_select->setValue($ds);

            $ldap->addSubItem($ldap_server_select);
        }

        if (ilLDAPServer::isDataSourceActive(ilAuthUtils::AUTH_CAS)) {
            $sync->setValue((string) ilCASSettings::SYNC_LDAP);
        } else {
            $sync->setValue(
                $this->getSettings()->isUserCreationEnabled() ?
                  (string) ilCASSettings::SYNC_CAS :
                  (string) ilCASSettings::SYNC_DISABLED
            );
        }

        $instruction = new ilTextAreaInputGUI($this->lng->txt('auth_login_instructions'), 'instruction');
        $instruction->setCols(80);
        $instruction->setRows(6);
        $instruction->setValue($this->getSettings()->getLoginInstruction());
        $form->addItem($instruction);

        $create = new ilCheckboxInputGUI($this->lng->txt('auth_allow_local'), 'local');
        $create->setInfo($this->lng->txt('auth_cas_allow_local_desc'));
        $create->setChecked($this->getSettings()->isLocalAuthenticationEnabled() ? true : false);
        $create->setValue("1");
        $form->addItem($create);

        if ($this->rbacSystem->checkAccess('write', $this->ref_id)) {
            $form->addCommandButton('save', $this->lng->txt('save'));
        }

        return $form;
    }
    
    /**
     * Show settings
     */
    public function settings() : void
    {
        $form = $this->initFormSettings();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Save
     */
    public function save() : void
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getSettings()->setActive((bool) $form->getInput('active'));
            $this->getSettings()->setServer($form->getInput('server'));
            $this->getSettings()->setPort((int) $form->getInput('port'));
            $this->getSettings()->setUri($form->getInput('uri'));
            $this->getSettings()->setDefaultRole((int) $form->getInput('role'));
            $this->getSettings()->enableLocalAuthentication((bool) $form->getInput('local'));
            $this->getSettings()->setLoginInstruction($form->getInput('instruction'));
            $this->getSettings()->enableUserCreation((int) $form->getInput('sync') == ilCASSettings::SYNC_CAS ? true : false);
            $this->getSettings()->save();

            switch ((int) $form->getInput('sync')) {
                case ilCASSettings::SYNC_DISABLED:
                    ilLDAPServer::disableDataSourceForAuthMode(ilAuthUtils::AUTH_CAS);
                    break;

                case ilCASSettings::SYNC_CAS:
                    ilLDAPServer::disableDataSourceForAuthMode(ilAuthUtils::AUTH_CAS);
                    break;

                case ilCASSettings::SYNC_LDAP:
                    if (!(int) $_REQUEST['ldap_sid']) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
                        $this->settings();
                        //TODO do we need return false?
                        return;
                    }

                    ilLDAPServer::toggleDataSource((int) $_REQUEST['ldap_sid'], ilAuthUtils::AUTH_CAS, 1);
                    break;
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
        }
        
        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_ceck_input'));
        $this->tpl->setContent($form->getHTML());
    }
    
    private function prepareRoleSelection() : array
    {
        $global_roles = ilUtil::_sortIds(
            $this->rbacReview->getGlobalRoles(),
            'object_data',
            'title',
            'obj_id'
        );
        
        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle((int) $role_id);
        }
        
        return $select;
    }
}
