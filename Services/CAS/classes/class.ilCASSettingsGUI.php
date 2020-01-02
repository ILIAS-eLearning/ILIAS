<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/CAS/classes/class.ilCASSettings.php';

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilCASSettingsGUI:
*
* @ingroup ServicesCAS
*/
class ilCASSettingsGUI
{
    const SYNC_DISABLED = 0;
    const SYNC_CAS = 1;
    const SYNC_LDAP = 2;

    private $settings;

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

        $this->settings = ilCASSettings::getInstance();
    }

    /**
     *
     * @return ilCASSettings
     */
    protected function getSettings()
    {
        return $this->settings;
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
        
        if (!$ilAccess->checkAccess('read', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_write'), $ilErr->WARNING);
        }
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id) && $cmd != "settings") {
            ilUtil::sendFailure($this->lng->txt('msg_no_perm_write'), true);
            $ilCtrl->redirect($this, "settings");
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
    protected function initFormSettings()
    {
        $this->lng->loadLanguageModule('auth');
        $this->lng->loadLanguageModule('radius');

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitle($this->lng->txt('auth_cas_auth'));
        $form->setDescription($this->lng->txt("auth_cas_auth_desc"));

        // Form checkbox
        $check = new ilCheckboxInputGUI($this->lng->txt("active"), 'active');
        $check->setChecked($this->getSettings()->isActive() ? true : false);
        $check->setValue(1);
        $form->addItem($check);

        $text = new ilTextInputGUI($this->lng->txt('server'), 'server');
        $text->setValue($this->getSettings()->getServer());
        $text->setRequired(true);
        $text->setInfo($this->lng->txt('auth_cas_server_desc'));
        $text->setSize(64);
        $text->setMaxLength(255);
        $form->addItem($text);

        $port = new ilNumberInputGUI($this->lng->txt("port"), 'port');
        $port->setValue($this->getSettings()->getPort());
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
            self::SYNC_DISABLED,
            ''
        );
        #$dis->setInfo($this->lng->txt('auth_radius_sync_disabled_info'));
        $sync->addOption($dis);

        // CAS
        $rad = new ilRadioOption(
            $this->lng->txt('auth_sync_cas'),
            self::SYNC_CAS,
            ''
        );
        $rad->setInfo($this->lng->txt('auth_sync_cas_info'));
        $sync->addOption($rad);

        $select = new ilSelectInputGUI($this->lng->txt('auth_user_default_role'), 'role');
        $select->setOptions($this->prepareRoleSelection());
        $select->setValue($this->getSettings()->getDefaultRole());
        $rad->addSubItem($select);



        // LDAP
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        $server_ids = ilLDAPServer::getAvailableDataSources(AUTH_CAS);

        if (count($server_ids)) {
            $ldap = new ilRadioOption(
                $this->lng->txt('auth_radius_ldap'),
                ilCASSettings::SYNC_LDAP,
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
            $ds = ilLDAPServer::getDataSource(AUTH_CAS);
            $ldap_server_select->setValue($ds);

            $ldap->addSubItem($ldap_server_select);
        }

        if (ilLDAPServer::isDataSourceActive(AUTH_CAS)) {
            $sync->setValue(ilCASSettings::SYNC_LDAP);
        } else {
            $sync->setValue(
                $this->getSettings()->isUserCreationEnabled() ?
                    ilCASSettings::SYNC_CAS :
                    ilCASSettings::SYNC_DISABLED
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
        $create->setValue(1);
        $form->addItem($create);

        $form->addCommandButton('save', $this->lng->txt('save'));

        return $form;
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
        $form = $this->initFormSettings();
        $this->tpl->setContent($form->getHTML());
        return;
    }
    
    /**
     * Save
     *
     * @access public
     *
     */
    public function save()
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getSettings()->setActive($form->getInput('active'));
            $this->getSettings()->setServer($form->getInput('server'));
            $this->getSettings()->setPort($form->getInput('port'));
            $this->getSettings()->setUri($form->getInput('uri'));
            $this->getSettings()->setDefaultRole($form->getInput('role'));
            $this->getSettings()->enableLocalAuthentication($form->getInput('local'));
            $this->getSettings()->setLoginInstruction($form->getInput('instruction'));
            $this->getSettings()->enableUserCreation($form->getInput('sync') == ilCASSettings::SYNC_CAS ? true : false);
            $this->getSettings()->save();

            include_once './Services/LDAP/classes/class.ilLDAPServer.php';
            switch ((int) $form->getInput('sync')) {
                case ilCASSettings::SYNC_DISABLED:
                    ilLDAPServer::disableDataSourceForAuthMode(AUTH_CAS);
                    break;

                case ilCASSettings::SYNC_CAS:
                    ilLDAPServer::disableDataSourceForAuthMode(AUTH_CAS);
                    break;

                case ilCASSettings::SYNC_LDAP:
                    if (!(int) $_REQUEST['ldap_sid']) {
                        ilUtil::sendFailure($this->lng->txt('err_check_input'));
                        $this->settings();
                        return false;
                    }

                    ilLDAPServer::toggleDataSource((int) $_REQUEST['ldap_sid'], AUTH_CAS, true);
                    break;
            }

            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
        }
        
        $form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_ceck_input'));
        $this->tpl->setContent($form->getHTML());
    }
    
    
    
    private function prepareRoleSelection()
    {
        global $rbacreview,$ilObjDataCache;
        
        $global_roles = ilUtil::_sortIds(
            $rbacreview->getGlobalRoles(),
            'object_data',
            'title',
            'obj_id'
        );
        
        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle($role_id);
        }
        
        return $select;
    }
}
