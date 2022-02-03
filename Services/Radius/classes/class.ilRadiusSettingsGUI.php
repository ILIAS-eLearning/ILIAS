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
* @defgroup ServicesRadius
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesRadius
*/
class ilRadiusSettingsGUI
{
    private ilCtrl $ctrl;
    private ilTabsGUI $tabs_gui;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilRbacReview $rbacreview;
    private ilRbacSystem $rbacsystem;
    private ilErrorHandling $ilErr;
    
    private $ref_id;
    
    /**
     * @param int object auth ref_id
     *
     */
    public function __construct(int $a_auth_ref_id)
    {
        global $DIC;
        
        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->lng = $DIC->language();
        $this->ilErr = $DIC['ilErr'];
        
        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('auth');
        $this->ref_id = $a_auth_ref_id;

        $this->initSettings();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("settings");
        
        if (!$this->rbacsystem->checkAccess("visible,read", $this->ref_id)) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
            $this->ctrl()->redirect($this, "settings");
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
     * Show settings
     */
    public function settings()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.settings.html', 'Services/Radius');

        $this->lng->loadLanguageModule('auth');
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('auth_radius_configure'));
        
        $check = new ilCheckboxInputGUI($this->lng->txt('auth_radius_enable'), 'active');
        $check->setChecked($this->settings->isActive());
        $check->setValue("1");
        $form->addItem($check);
        
        $text = new ilTextInputGUI($this->lng->txt('auth_radius_name'), 'name');
        $text->setRequired(true);
        $text->setInfo($this->lng->txt('auth_radius_name_desc'));
        $text->setValue($this->settings->getName());
        $text->setSize(32);
        $text->setMaxLength(64);
        $form->addItem($text);
        
        $text = new ilTextInputGUI($this->lng->txt('auth_radius_server'), 'servers');
        $text->setRequired(true);
        $text->setInfo($this->lng->txt('auth_radius_server_desc'));
        $text->setValue($this->settings->getServersAsString());
        $text->setSize(64);
        $text->setMaxLength(255);
        $form->addItem($text);
        
            
        $text = new ilTextInputGUI($this->lng->txt('auth_radius_port'), 'port');
        $text->setRequired(true);
        $text->setValue($this->settings->getPort());
        $text->setSize(5);
        $text->setMaxLength(5);
        $form->addItem($text);

        $text = new ilTextInputGUI($this->lng->txt('auth_radius_shared_secret'), 'secret');
        $text->setRequired(true);
        $text->setValue($this->settings->getSecret());
        $text->setSize(16);
        $text->setMaxLength(32);
        $form->addItem($text);
        
        $encoding = new ilSelectInputGUI($this->lng->txt('auth_radius_charset'), 'charset');
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
            (string) ilRadiusSettings::SYNC_DISABLED,
            ''
        );
        #$dis->setInfo($this->lng->txt('auth_radius_sync_disabled_info'));
        $sync->addOption($dis);

        // Radius
        $rad = new ilRadioOption(
            $this->lng->txt('auth_radius_sync_rad'),
            (string) ilRadiusSettings::SYNC_RADIUS,
            ''
        );
        $rad->setInfo($this->lng->txt('auth_radius_sync_rad_info'));
        $sync->addOption($rad);

        $select = new ilSelectInputGUI($this->lng->txt('auth_radius_role_select'), 'role');
        $select->setOptions($this->prepareRoleSelection());
        $select->setValue($this->settings->getDefaultRole());
        $rad->addSubItem($select);

        $migr = new ilCheckboxInputGUI($this->lng->txt('auth_rad_migration'), 'migration');
        $migr->setInfo($this->lng->txt('auth_rad_migration_info'));
        $migr->setChecked($this->settings->isAccountMigrationEnabled());
        $migr->setValue("1");
        $rad->addSubItem($migr);

        // LDAP
        $server_ids = ilLDAPServer::getAvailableDataSources(ilAuthUtils::AUTH_RADIUS);
        
        if (count($server_ids)) {
            $ldap = new ilRadioOption(
                $this->lng->txt('auth_radius_ldap'),
                ilRadiusSettings::SYNC_LDAP,
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
            $ds = ilLDAPServer::getDataSource(ilAuthUtils::AUTH_RADIUS);
            $ldap_server_select->setValue($ds);
            
            $ldap->addSubItem($ldap_server_select);
        }

        if (ilLDAPServer::isDataSourceActive(ilAuthUtils::AUTH_RADIUS)) {
            $sync->setValue(ilRadiusSettings::SYNC_LDAP);
        } else {
            $sync->setValue(
                $this->settings->enabledCreation() ?
                    (string) ilRadiusSettings::SYNC_RADIUS :
                    (string) ilRadiusSettings::SYNC_DISABLED
            );
        }

        if ($this->rbacsystem->checkAccess('write', $this->ref_id)) {
            $form->addCommandButton('save', $this->lng->txt('save'));
        }
        $this->tpl->setVariable('SETTINGS_TABLE', $form->getHTML());
    }
    
    /**
     * Save
     */
    public function save()
    {
        $this->settings->setActive((bool) $_POST['active']);
        $this->settings->setName(ilUtil::stripSlashes($_POST['name']));
        $this->settings->setPort((int) ilUtil::stripSlashes($_POST['port']));
        $this->settings->setSecret(ilUtil::stripSlashes($_POST['secret']));
        $this->settings->setServerString(ilUtil::stripSlashes($_POST['servers']));
        $this->settings->setDefaultRole((int) $_POST['role']);
        $this->settings->enableAccountMigration((bool) $_POST['migration']);
        $this->settings->setCharset((int) $_POST['charset']);
        $this->settings->enableCreation($_POST['sync'] == ilRadiusSettings::SYNC_RADIUS ? true : false);

        if (!$this->settings->validateRequired()) {
            ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields"));
            $this->settings();
            return false;
        }
        if (!$this->settings->validatePort()) {
            ilUtil::sendFailure($this->lng->txt("err_invalid_port"));
            $this->settings();
            return false;
        }
        if (!$this->settings->validateServers()) {
            ilUtil::sendFailure($this->lng->txt("err_invalid_server"));
            $this->settings();
            return false;
        }

        switch ((int) $_POST['sync']) {
            case ilRadiusSettings::SYNC_DISABLED:
                ilLDAPServer::disableDataSourceForAuthMode(ilAuthUtils::AUTH_RADIUS);
                break;

            case ilRadiusSettings::SYNC_RADIUS:
                ilLDAPServer::disableDataSourceForAuthMode(ilAuthUtils::AUTH_RADIUS);
                break;

            case ilRadiusSettings::SYNC_LDAP:
                if (!(int) $_REQUEST['ldap_sid']) {
                    ilUtil::sendFailure($this->lng->txt('err_check_input'));
                    $this->settings();
                    return false;
                }
                
                ilLDAPServer::toggleDataSource((int) $_REQUEST['ldap_sid'], ilAuthUtils::AUTH_RADIUS, true);
                break;
        }

        $this->settings->save();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->settings();
        return true;
    }
    
    
    /**
     * Init Server settings
     */
    private function initSettings()
    {
        $this->settings = ilRadiusSettings::_getInstance();
    }
    
    private function prepareRoleSelection()
    {
        $global_roles = ilUtil::_sortIds(
            $this->rbacreview->getGlobalRoles(),
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
    
    /**
     * Get charset options
     */
    private function prepareCharsetSelection()
    {
        return array(ilRadiusSettings::RADIUS_CHARSET_UTF8 => 'UTF-8',
                ilRadiusSettings::RADIUS_CHARSET_LATIN1 => 'ISO-8859-1');
    }
}
