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
class ilLDAPSettingsGUI
{
    private int $ref_id;
    private ilLDAPServer $server;
    private ?ilPropertyFormGUI $form_gui = null;
    private ?ilPropertyFormGUI $form = null;

    private ilCtrl $ctrl;
    private ilTabsGUI $tabs_gui;
    private ilLanguage $lng;
    private ilGlobalTemplateInterface $tpl;
    private ilErrorHandling $ilErr;
    private ilAccessHandler $ilAccess;
    private ilRbacReview $rbacReview;
    private ilRbacSystem $rbacSystem;
    private ilToolbarGUI $toolbar;
    private \ilGlobalTemplateInterface $main_tpl;
    private ilComponentRepository $component_repository;

    public function __construct(int $a_auth_ref_id)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ldap');
        $this->ilErr = $DIC['ilErr'];
        $this->ilAccess = $DIC->access();
        $this->component_repository = $DIC["component.repository"];
        $this->rbacReview = $DIC->rbac()->review();
        $this->rbacSystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        
        $this->tpl = $DIC->ui()->mainTemplate();

        if ($this->ctrl->getCmd() != "addServerSettings") {
            $this->ctrl->saveParameter($this, 'ldap_server_id');
        }
        
//         $this->http_wrapper = $DIC->http()->wrapper();
//         $this->refinery = $DIC->refinery();
//         if ($this->http_wrapper->query()->has('ref_id')) {
//             $this->ref_id = (int) $this->http_wrapper->query()->retrieve(
//                 'ref_id',
//                 $this->refinery->kindlyTo()->int()
//                 );
//         }
        
        $this->ref_id = $a_auth_ref_id;


        $this->initServer();
    }
    
    public function executeCommand() : bool
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        if (!$this->dic->rbac()->system()->checkAccess("visible,read", $this->ref_id) && $cmd != "serverList") {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_write'), true);
            $this->ctrl->redirect($this, "serverList");
        }
        
        //TODO check if needed
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "serverList";
                }
                $this->$cmd();
                break;
        }
        return true;
    }

    protected function checkAccess(string $a_permission) : void
    {
        if (!$this->checkAccessBool($a_permission)) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
        }
    }

    protected function checkAccessBool(string $a_permission) : bool
    {
        return $this->ilAccess->checkAccess($a_permission, '', $this->ref_id);
    }
    
    /**
     * Get server settings
     */
    public function getServer() : ilLDAPServer
    {
        return $this->server;
    }
    
    /**
     * Edit role assignments
     */
    public function roleAssignments() : void
    {
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_assignments');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_assignments.html', 'Services/LDAP');

        $this->initFormRoleAssignments('create', $this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId(0));
        $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $this->form->getHTML());
        

        if (count($rules = ilLDAPRoleAssignmentRule::_getRules($this->server->getServerId()))) {
            $table_gui = new ilLDAPRoleAssignmentTableGUI($this, 'roleAssignments');
            $table_gui->setTitle($this->lng->txt("ldap_tbl_role_ass"));
            $table_gui->parse($rules);
            $table_gui->addMultiCommand("confirmDeleteRules", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("rule_id");
            $this->tpl->setVariable('RULES_TBL', $table_gui->getHTML());
        }
    }

    /**
     * Edit role assignment
     */
    public function editRoleAssignment() : void
    {
        if (!(int) $_GET['rule_id']) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->roleAssignments();
            //TODO check if return false is needed
            return;
        }
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_assignments');

        $this->ctrl->saveParameter($this, 'rule_id', (int) $_GET['rule_id']);
        $this->initFormRoleAssignments(
            'edit',
            $this->role_mapping_rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId((int) $_GET['rule_id'])
        );
        $this->setValuesByArray();
        $this->tpl->setContent($this->form->getHTML());
    }
    
    
    /**
     * set values of form array
     */
    protected function setValuesByArray() : void
    {
        $role_id = $this->role_mapping_rule->getRoleId();
        if ($this->rbacReview->isGlobalRole($role_id)) {
            $val['role_name'] = 0;
            $val['role_id'] = $role_id;
        } else {
            $val['role_name'] = 1;
            $val['role_search'] = ilObject::_lookupTitle($role_id);
        }
        $val['add_missing'] = (int) $this->role_mapping_rule->isAddOnUpdateEnabled();
        $val['remove_deprecated'] = (int) $this->role_mapping_rule->isRemoveOnUpdateEnabled();
        $val['type'] = (int) $this->role_mapping_rule->getType();
        $val['dn'] = $this->role_mapping_rule->getDN();
        $val['at'] = $this->role_mapping_rule->getMemberAttribute();
        $val['isdn'] = $this->role_mapping_rule->isMemberAttributeDN();
        $val['name'] = $this->role_mapping_rule->getAttributeName();
        $val['value'] = $this->role_mapping_rule->getAttributeValue();
        $val['plugin_id'] = $this->role_mapping_rule->getPluginId();
        
        $this->form->setValuesByArray($val);
    }
    
    /**
     * update role assignment
     */
    public function updateRoleAssignment() : bool
    {
        if (!$this->ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->roleAssignment();
            return false;
        }

        $this->initFormRoleAssignments('edit');
        if (!$this->form->checkInput() or ($err = $this->checkRoleAssignmentInput((int) $_REQUEST['rule_id']))) {
            if ($err) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt($err));
            }

            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_assignments.html', 'Services/LDAP');

            // DONE: wrap this
            $this->form->setValuesByPost();
            $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $this->form->getHTML());
            #$this->tpl->setVariable('RULES_TBL',$this->getRoleAssignmentTable());
            $this->tabs_gui->setSubTabActive('shib_role_assignment');
            return true;
        }
        
        // Might redirect
        $this->roleSelection();
        
        $this->rule->update();
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->roleAssignments();
        return true;
    }
    
    /**
     * Confirm delete rules
     */
    public function confirmDeleteRules() : void
    {
        $this->checkAccess("write");

        if (!is_array($_POST['rule_ids'])) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->roleAssignments();
            //TODO check why return false
            return;
        }
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_assignments');
        
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRules"));
        $c_gui->setHeaderText($this->lng->txt("ldap_confirm_del_role_ass"));
        $c_gui->setCancel($this->lng->txt("cancel"), "roleAssignments");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteRules");

        // add items to delete
        foreach ($_POST["rule_ids"] as $rule_id) {
            $rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
            $c_gui->addItem('rule_ids[]', $rule_id, $rule->conditionToString());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }
    
    /**
     * delete role assignment rule
     */
    public function deleteRules() : bool
    {
        if (!is_array($_POST['rule_ids'])) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_once'));
            $this->roleAssignments();
            return false;
        }
        foreach ($_POST["rule_ids"] as $rule_id) {
            $rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($rule_id);
            $rule->delete();
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('ldap_deleted_rule'));
        $this->roleAssignments();
        return true;
    }
    
    /**
     * add new role assignment
     */
    public function addRoleAssignment() : bool
    {
        if (!$this->ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->roleAssignment();
            return false;
        }

        $this->initFormRoleAssignments('create');
        if (!$this->form->checkInput() or ($err = $this->checkRoleAssignmentInput())) {
            if ($err) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt($err));
            }

            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_assignments.html', 'Services/LDAP');

            // DONE: wrap this
            $this->form->setValuesByPost();
            $this->tpl->setVariable('NEW_ASSIGNMENT_TBL', $this->form->getHTML());
            $this->tpl->setVariable('RULES_TBL', $this->getRoleAssignmentTable());
            $this->tabs_gui->setSubTabActive('shib_role_assignment');
            return true;
        }
        
        // Might redirect
        $this->roleSelection();

        $this->rule->create();
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        unset($_POST);
        $this->roleAssignments();
        return true;
    }
    
    protected function roleSelection() : void
    {
        if ($this->rule->getRoleId() > 0) {
            //TODO check why return false
            return;
        }
        $_SESSION['ldap_role_ass']['server_id'] = $this->getServer()->getServerId();
        $_SESSION['ldap_role_ass']['rule_id'] = $_REQUEST['rule_id'] ? $_REQUEST['rule_id'] : 0;
        $_SESSION['ldap_role_ass']['role_search'] = $this->form->getInput('role_search');
        $_SESSION['ldap_role_ass']['add_on_update'] = $this->form->getInput('add_missing');
        $_SESSION['ldap_role_ass']['remove_on_update'] = $this->form->getInput('remove_deprecated');
        $_SESSION['ldap_role_ass']['type'] = $this->form->getInput('type');
        $_SESSION['ldap_role_ass']['dn'] = $this->form->getInput('dn');
        $_SESSION['ldap_role_ass']['at'] = $this->form->getInput('at');
        $_SESSION['ldap_role_ass']['isdn'] = $this->form->getInput('isdn');
        $_SESSION['ldap_role_ass']['name'] = $this->form->getInput('name');
        $_SESSION['ldap_role_ass']['value'] = $this->form->getInput('value');
        $_SESSION['ldap_role_ass']['plugin'] = $this->form->getInput('plugin_id');
        
        $this->ctrl->saveParameter($this, 'rule_id');
        $this->ctrl->redirect($this, 'showRoleSelection');
    }
    
    
    
    /**
     * show role selection
     */
    protected function showRoleSelection() : bool
    {
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_assignment');
        $this->ctrl->saveParameter($this, 'rule_id');
        
        $parser = new ilQueryParser($_SESSION['ldap_role_ass']['role_search']);
        $parser->setMinWordLength(1);
        $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $parser->parse();
        
        $object_search = new ilLikeObjectSearch($parser);
        $object_search->setFilter(array('role'));
        $res = $object_search->performSearch();
        
        $entries = $res->getEntries();

        $table = new ilRoleSelectionTableGUI($this, 'showRoleSelection');
        $table->setTitle($this->lng->txt('ldap_role_selection'));
        $table->addMultiCommand('saveRoleSelection', $this->lng->txt('ldap_choose_role'));
        #$table->addCommandButton('roleAssignment',$this->lng->txt('cancel'));
        $table->parse($entries);
        
        $this->tpl->setContent($table->getHTML());
        return true;
    }

    /**
     * Save role selection
     * @return
     */
    protected function saveRoleSelection() : bool
    {
        if (!$this->ilAccess->checkAccess('write', '', $this->ref_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->roleAssignment();
            return false;
        }

        if (!(int) $_REQUEST['role_id']) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->showRoleSelection();
            return false;
        }

        $this->loadRoleAssignmentRule((int) $_REQUEST['rule_id'], false);
        $this->rule->setRoleId((int) $_REQUEST['role_id']);
        
        if ((int) $_REQUEST['rule_id']) {
            $this->rule->update();
        } else {
            $this->rule->create();
        }
        
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->roleAssignments();
        return true;
    }
    
    
    /**
     * Check role assignment input
     */
    protected function checkRoleAssignmentInput(int $a_rule_id = 0) : string
    {
        $this->loadRoleAssignmentRule($a_rule_id);
        $this->rule->validate();
        return $this->ilErr->getMessage();
    }
    
    /**
     * Show active role assignments
     */
    protected function getRoleAssignmentTable() : string
    {
        if (count($rules = ilLDAPRoleAssignmentRule::_getRules($this->server->getServerId()))) {
            $table_gui = new ilLDAPRoleAssignmentTableGUI($this, 'roleAssignments');
            $table_gui->setTitle($this->lng->txt("ldap_tbl_role_ass"));
            $table_gui->parse($rules);
            $table_gui->addMultiCommand("confirmDeleteRules", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("rule_id");
            return $table_gui->getHTML();
        }
        return '';
    }
    
    
    /**
     * Load input from form
     */
    protected function loadRoleAssignmentRule(int $a_rule_id, bool $a_from_form = true) : bool
    {
        if (is_object($this->rule)) {
            return true;
        }
        
        $this->rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($a_rule_id);


        if ($a_from_form) {
            if ($this->form->getInput('role_name') == 0) {
                $this->rule->setRoleId($this->form->getInput('role_id'));
            } elseif ($this->form->getInput('role_search')) {
                // Search role
                
                $parser = new ilQueryParser('"' . $this->form->getInput('role_search') . '"');
                
                // TODO: Handle minWordLength
                $parser->setMinWordLength(1);
                $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
                $parser->parse();
                
                $object_search = new ilLikeObjectSearch($parser);
                $object_search->setFilter(array('role'));
                $res = $object_search->performSearch();
                
                $entries = $res->getEntries();
                if (count($entries) == 1) {
                    $role = current($entries);
                    $this->rule->setRoleId($role['obj_id']);
                } elseif (count($entries) > 1) {
                    $this->rule->setRoleId(-1);
                }
            }
            
            $this->rule->setAttributeName($this->form->getInput('name'));
            $this->rule->setAttributeValue($this->form->getInput('value'));
            $this->rule->setDN($this->form->getInput('dn'));
            $this->rule->setMemberAttribute($this->form->getInput('at'));
            $this->rule->setMemberIsDN($this->form->getInput('isdn'));
            $this->rule->enableAddOnUpdate($this->form->getInput('add_missing'));
            $this->rule->enableRemoveOnUpdate($this->form->getInput('remove_deprecated'));
            $this->rule->setPluginId($this->form->getInput('plugin_id'));
            $this->rule->setType($this->form->getInput('type'));
            $this->rule->setServerId($this->getServer()->getServerId());
            return true;
        }
        
        // LOAD from session
        $this->rule = ilLDAPRoleAssignmentRule::_getInstanceByRuleId($a_rule_id);
        $this->rule->setServerId($this->getServer()->getServerId());
        $this->rule->enableAddOnUpdate((int) $_SESSION['ldap_role_ass']['add_on_update']);
        $this->rule->enableRemoveOnUpdate((int) $_SESSION['ldap_role_ass']['remove_on_update']);
        $this->rule->setType(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['type']));
        $this->rule->setDN(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['dn']));
        $this->rule->setMemberAttribute(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['at']));
        $this->rule->setMemberIsDN(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['isdn']));
        $this->rule->setAttributeName(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['name']));
        $this->rule->setAttributeValue(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['value']));
        $this->rule->setPluginId(ilUtil::stripSlashes($_SESSION['ldap_role_ass']['plugin']));
        return true;
    }
    
    public function deleteRoleMapping() : bool
    {
        if (!count($_POST['mappings'])) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->roleMapping();
            return false;
        }
        
        $this->initRoleMapping();
        
        foreach ($_POST['mappings'] as $mapping_id) {
            $this->role_mapping->delete($mapping_id);
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('ldap_deleted_role_mapping'));
        $this->roleMapping();
        return true;
    }
    
    public function chooseMapping() : void
    {
        if (!$_POST['mapping_template']) {
            $this->userMapping();
            return;
        }
        
        $this->initAttributeMapping();
        $this->mapping->clearRules();
        
        foreach (ilLDAPAttributeMappingUtils::_getMappingRulesByClass($_POST['mapping_template']) as $key => $value) {
            $this->mapping->setRule($key, $value, 0);
        }
        $this->userMapping();
        return;
    }
    
    public function saveMapping() : void
    {
        $this->initAttributeMapping();
        $this->tabs_gui->setTabActive('role_mapping');
        
        foreach ($this->getMappingFields() as $key) {
            $this->mapping->setRule($key, ilUtil::stripSlashes($_POST[$key . '_value']), (int) $_POST[$key . '_update']);
        }
        $this->initUserDefinedFields();
        foreach ($this->udf->getDefinitions() as $definition) {
            $key = 'udf_' . $definition['field_id'];
            $this->mapping->setRule($key, ilUtil::stripSlashes($_POST[$key . '_value']), (int) $_POST[$key . '_update']);
        }
        
        $this->mapping->save();
        $this->userMapping();
        
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        unset($_POST['mapping_template']);
        return;
    }
    
    public function serverList() : void
    {
        if (!$this->rbacSystem->checkAccess("visible,read", $this->ref_id)) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->WARNING);
        }
        
        if (!ilLDAPServer::checkLDAPLib() and $this->server->isActive()) {
            $this->main_tpl->setOnScreenMessage('failure', 'Missing LDAP libraries. Please ensure that the PHP LDAP module is installed on your server.');
        }

        if ($this->rbacSystem->checkAccess("write", $this->ref_id)) {
            $this->toolbar->addButton(
                $this->lng->txt("add_ldap_server"),
                $this->ctrl->getLinkTarget($this, "addServerSettings")
            );
        }
        
        $table = new ilLDAPServerTableGUI($this, "serverList");
        
        $this->tpl->setContent($table->getHTML());
    }
    
    public function setServerFormValues() : void
    {
        $this->form_gui->setValuesByArray(array(
            'active' => $this->server->isActive(),
            'ds' => !$this->server->isAuthenticationEnabled(),
            'server_name' => $this->server->getName(),
            'server_url' => $this->server->getUrlString(),
            'version' => $this->server->getVersion(),
            'base_dn' => $this->server->getBaseDN(),
            'referrals' => $this->server->isActiveReferrer(),
            'tls' => $this->server->isActiveTLS(),
            'binding_type' => $this->server->getBindingType(),
            'bind_dn' => $this->server->getBindUser(),
            'bind_pass' => $this->server->getBindPassword(),
            'bind_pass_retype' => $this->server->getBindPassword(),
            'search_base' => $this->server->getSearchBase(),
            'user_scope' => $this->server->getUserScope(),
            'user_attribute' => $this->server->getUserAttribute(),
            'filter' => $this->server->getFilter(),
            'group_dn' => $this->server->getGroupDN(),
            'group_scope' => $this->server->getGroupScope(),
            'group_filter' => $this->server->getGroupFilter(),
            'group_member' => $this->server->getGroupMember(),
            'memberisdn' => $this->server->enabledGroupMemberIsDN(),
            'group' => $this->server->getGroupName(),
            'group_attribute' => $this->server->getGroupAttribute(),
            'group_optional' => $this->server->isMembershipOptional(),
            'group_user_filter' => $this->server->getGroupUserFilter(),
            'sync_on_login' => $this->server->enabledSyncOnLogin(),
            'sync_per_cron' => $this->server->enabledSyncPerCron(),
            'global_role' => ilLDAPAttributeMapping::_lookupGlobalRole($this->server->getServerId()),
            'migration' => (int) $this->server->isAccountMigrationEnabled(),
            "name_filter" => $this->server->getUsernameFilter(),
            'escape_dn' => $this->server->enabledEscapeDN()
        ));
    }
    
    private function initForm() : void
    {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $this->form_gui->setTitle($this->lng->txt('ldap_configure'));
        
        $active = new ilCheckboxInputGUI($this->lng->txt('auth_ldap_enable'), 'active');
        $active->setValue("1");
        $this->form_gui->addItem($active);

        $ds = new ilCheckboxInputGUI($this->lng->txt('ldap_as_ds'), 'ds');
        $ds->setValue("1");
        $ds->setInfo($this->lng->txt('ldap_as_ds_info'));
        $this->form_gui->addItem($ds);

        $servername = new ilTextInputGUI($this->lng->txt('ldap_server_name'), 'server_name');
        $servername->setRequired(true);
        $servername->setInfo($this->lng->txt('ldap_server_name_info'));
        $servername->setSize(32);
        $servername->setMaxLength(32);
        $this->form_gui->addItem($servername);
        
        // start Patch Name Filter
        $namefilter = new ilTextInputGUI($this->lng->txt('ldap_username_filter'), "name_filter");//ADD LANG VAR
        $namefilter->setInfo($this->lng->txt("ldap_username_filter_info"));
        $namefilter->setSize(64);
        $namefilter->setMaxLength(255);
        $this->form_gui->addItem($namefilter);
        // end Patch Name Filter
        
        $serverurl = new ilTextInputGUI($this->lng->txt('ldap_server'), 'server_url');
        $serverurl->setRequired(true);
        $serverurl->setInfo($this->lng->txt('ldap_server_url_info'));
        $serverurl->setSize(64);
        $serverurl->setMaxLength(255);
        $this->form_gui->addItem($serverurl);
        
        $version = new ilSelectInputGUI($this->lng->txt('ldap_version'), 'version');
        $version->setOptions(array(2 => 2, 3 => 3));
        $version->setInfo($this->lng->txt('ldap_server_version_info'));
        $this->form_gui->addItem($version);
        
        $basedsn = new ilTextInputGUI($this->lng->txt('basedn'), 'base_dn');
        $basedsn->setRequired(true);
        $basedsn->setSize(64);
        $basedsn->setMaxLength(255);
        $this->form_gui->addItem($basedsn);
        
        $referrals = new ilCheckboxInputGUI($this->lng->txt('ldap_referrals'), 'referrals');
        $referrals->setValue("1");
        $referrals->setInfo($this->lng->txt('ldap_referrals_info'));
        $this->form_gui->addItem($referrals);
        
        $section_security = new ilFormSectionHeaderGUI();
        $section_security->setTitle($this->lng->txt('ldap_server_security_settings'));
        $this->form_gui->addItem($section_security);
        
        $tls = new ilCheckboxInputGUI($this->lng->txt('ldap_tls'), 'tls');
        $tls->setValue("1");
        $this->form_gui->addItem($tls);
        
        $binding = new ilRadioGroupInputGUI($this->lng->txt('ldap_server_binding'), 'binding_type');
        $anonymous = new ilRadioOption($this->lng->txt('ldap_bind_anonymous'), (string) ilLDAPServer::LDAP_BIND_ANONYMOUS);
        $binding->addOption($anonymous);
        $user = new ilRadioOption($this->lng->txt('ldap_bind_user'), (string) ilLDAPServer::LDAP_BIND_USER);
        $dn = new ilTextInputGUI($this->lng->txt('ldap_server_bind_dn'), 'bind_dn');
        $dn->setSize(64);
        $dn->setMaxLength(255);
        $user->addSubItem($dn);
        $pass = new ilPasswordInputGUI($this->lng->txt('ldap_server_bind_pass'), 'bind_pass');
        $pass->setSkipSyntaxCheck(true);
        $pass->setSize(12);
        $pass->setMaxLength(36);
        $user->addSubItem($pass);
        $binding->addOption($user);
        $this->form_gui->addItem($binding);
        
        $section_auth = new ilFormSectionHeaderGUI();
        $section_auth->setTitle($this->lng->txt('ldap_authentication_settings'));
        $this->form_gui->addItem($section_auth);
        
        $search_base = new ilTextInputGUI($this->lng->txt('ldap_user_dn'), 'search_base');
        $search_base->setInfo($this->lng->txt('ldap_search_base_info'));
        $search_base->setSize(64);
        $search_base->setMaxLength(255);
        $this->form_gui->addItem($search_base);
        
        $user_scope = new ilSelectInputGUI($this->lng->txt('ldap_user_scope'), 'user_scope');
        $user_scope->setOptions(array(ilLDAPServer::LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
            ilLDAPServer::LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')));
        $user_scope->setInfo($this->lng->txt('ldap_user_scope_info'));
        $this->form_gui->addItem($user_scope);
        
        $user_attribute = new ilTextInputGUI($this->lng->txt('ldap_user_attribute'), 'user_attribute');
        $user_attribute->setSize(16);
        $user_attribute->setMaxLength(64);
        $user_attribute->setRequired(true);
        $this->form_gui->addItem($user_attribute);
        
        $filter = new ilTextInputGUI($this->lng->txt('ldap_search_filter'), 'filter');
        $filter->setInfo($this->lng->txt('ldap_filter_info'));
        $filter->setSize(64);
        $filter->setMaxLength(512);
        $this->form_gui->addItem($filter);
        
        $section_restrictions = new ilFormSectionHeaderGUI();
        $section_restrictions->setTitle($this->lng->txt('ldap_group_restrictions'));
        $this->form_gui->addItem($section_restrictions);
        
        $group_dn = new ilTextInputGUI($this->lng->txt('ldap_group_search_base'), 'group_dn');
        $group_dn->setInfo($this->lng->txt('ldap_group_dn_info'));
        $group_dn->setSize(64);
        $group_dn->setMaxLength(255);
        $this->form_gui->addItem($group_dn);
        
        $group_scope = new ilSelectInputGUI($this->lng->txt('ldap_group_scope'), 'group_scope');
        $group_scope->setOptions(array(ilLDAPServer::LDAP_SCOPE_ONE => $this->lng->txt('ldap_scope_one'),
            ilLDAPServer::LDAP_SCOPE_SUB => $this->lng->txt('ldap_scope_sub')));
        $group_scope->setInfo($this->lng->txt('ldap_group_scope_info'));
        $this->form_gui->addItem($group_scope);
        
        $group_filter = new ilTextInputGUI($this->lng->txt('ldap_group_filter'), 'group_filter');
        $group_filter->setInfo($this->lng->txt('ldap_group_filter_info'));
        $group_filter->setSize(64);
        $group_filter->setMaxLength(255);
        $this->form_gui->addItem($group_filter);
        
        $group_member = new ilTextInputGUI($this->lng->txt('ldap_group_member'), 'group_member');
        $group_member->setInfo($this->lng->txt('ldap_group_member_info'));
        $group_member->setSize(32);
        $group_member->setMaxLength(255);
        $this->form_gui->addItem($group_member);


        $group_member_isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_memberisdn'), 'memberisdn');
        #$group_member_isdn->setInfo($this->lng->txt('ldap_group_member_info'));
        $this->form_gui->addItem($group_member_isdn);
        #$group_member->addSubItem($group_member_isdn);
        
        $escapedn = new ilCheckboxInputGUI($this->lng->txt('ldap_escapedn'), 'escape_dn');
        $escapedn->setValue("1");
        $escapedn->setInfo($this->lng->txt('ldap_escapedn_info'));
        $this->form_gui->addItem($escapedn);

        $group = new ilTextInputGUI($this->lng->txt('ldap_group_name'), 'group');
        $group->setInfo($this->lng->txt('ldap_group_name_info'));
        $group->setSize(32);
        $group->setMaxLength(255);
        $this->form_gui->addItem($group);
        
        $group_atrr = new ilTextInputGUI($this->lng->txt('ldap_group_attribute'), 'group_attribute');
        $group_atrr->setInfo($this->lng->txt('ldap_group_attribute_info'));
        $group_atrr->setSize(16);
        $group_atrr->setMaxLength(64);
        $this->form_gui->addItem($group_atrr);
        
        $group_optional = new ilCheckboxInputGUI($this->lng->txt('ldap_group_membership'), 'group_optional');
        $group_optional->setOptionTitle($this->lng->txt('ldap_group_member_optional'));
        $group_optional->setInfo($this->lng->txt('ldap_group_optional_info'));
        $group_optional->setValue("1");
        $group_user_filter = new ilTextInputGUI($this->lng->txt('ldap_group_user_filter'), 'group_user_filter');
        $group_user_filter->setSize(64);
        $group_user_filter->setMaxLength(255);
        $group_optional->addSubItem($group_user_filter);
        $this->form_gui->addItem($group_optional);
    
        $section_sync = new ilFormSectionHeaderGUI();
        $section_sync->setTitle($this->lng->txt('ldap_user_sync'));
        $this->form_gui->addItem($section_sync);
        
        $ci_gui = new ilCustomInputGUI($this->lng->txt('ldap_moment_sync'));
        $sync_on_login = new ilCheckboxInputGUI($this->lng->txt('ldap_sync_login'), 'sync_on_login');
        $sync_on_login->setValue("1");
        $ci_gui->addSubItem($sync_on_login);
        $sync_per_cron = new ilCheckboxInputGUI($this->lng->txt('ldap_sync_cron'), 'sync_per_cron');
        $sync_per_cron->setValue("1");
        $ci_gui->addSubItem($sync_per_cron);
        $ci_gui->setInfo($this->lng->txt('ldap_user_sync_info'));
        $this->form_gui->addItem($ci_gui);
        
        $global_role = new ilSelectInputGUI($this->lng->txt('ldap_global_role_assignment'), 'global_role');
        $global_role->setOptions($this->prepareGlobalRoleSelection(false));
        $global_role->setInfo($this->lng->txt('ldap_global_role_info'));
        $this->form_gui->addItem($global_role);
        
        $migr = new ilCheckboxInputGUI($this->lng->txt('auth_ldap_migration'), 'migration');
        $migr->setInfo($this->lng->txt('auth_ldap_migration_info'));
        $migr->setValue("1");
        $this->form_gui->addItem($migr);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_LDAP,
            $this->form_gui,
            ilAdministrationSettingsFormHandler::getSettingsGUIInstance("auth")
        );

        $this->form_gui->addCommandButton('save', $this->lng->txt('save'));
        if ($_GET["cmd"] == "addServerSettings") {
            $this->form_gui->addCommandButton('serverList', $this->lng->txt('cancel'));
        }
    }
    
    /*
     * Update Settings
     */
    public function save() : bool
    {
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('settings');
        
        $this->initForm();
        if ($this->form_gui->checkInput()) {
            $this->server->toggleActive((bool) $this->form_gui->getInput('active'));
            $this->server->enableAuthentication(!(bool) $this->form_gui->getInput('ds'));
            $this->server->setName($this->form_gui->getInput('server_name'));
            $this->server->setUrl($this->form_gui->getInput('server_url'));
            $this->server->setVersion($this->form_gui->getInput('version'));
            $this->server->setBaseDN($this->form_gui->getInput('base_dn'));
            $this->server->toggleReferrer($this->form_gui->getInput('referrals'));
            $this->server->toggleTLS($this->form_gui->getInput('tls'));
            $this->server->setBindingType((int) $this->form_gui->getInput('binding_type'));
            $this->server->setBindUser($this->form_gui->getInput('bind_dn'));
            $this->server->setBindPassword($this->form_gui->getInput('bind_pass'));
            $this->server->setSearchBase($this->form_gui->getInput('search_base'));
            $this->server->setUserScope($this->form_gui->getInput('user_scope'));
            $this->server->setUserAttribute($this->form_gui->getInput('user_attribute'));
            $this->server->setFilter($this->form_gui->getInput('filter'));
            $this->server->setGroupDN($this->form_gui->getInput('group_dn'));
            $this->server->setGroupScope((int) $this->form_gui->getInput('group_scope'));
            $this->server->setGroupFilter($this->form_gui->getInput('group_filter'));
            $this->server->setGroupMember($this->form_gui->getInput('group_member'));
            $this->server->enableGroupMemberIsDN((int) $this->form_gui->getInput('memberisdn'));
            $this->server->setGroupName($this->form_gui->getInput('group'));
            $this->server->setGroupAttribute($this->form_gui->getInput('group_attribute'));
            $this->server->setGroupUserFilter($this->form_gui->getInput('group_user_filter'));
            $this->server->toggleMembershipOptional((int) $this->form_gui->getInput('group_optional'));
            $this->server->enableSyncOnLogin((int) $this->form_gui->getInput('sync_on_login'));
            $this->server->enableSyncPerCron((int) $this->form_gui->getInput('sync_per_cron'));
            $this->server->setGlobalRole((int) $this->form_gui->getInput('global_role'));
            $this->server->enableAccountMigration((int) $this->form_gui->getInput('migration'));
            $this->server->setUsernameFilter($this->form_gui->getInput("name_filter"));
            $this->server->enableEscapeDN((bool) $this->form_gui->getInput('escape_dn'));
            if (!$this->server->validate()) {
                $this->main_tpl->setOnScreenMessage('failure', $this->ilErr->getMessage());
                $this->form_gui->setValuesByPost();
                $this->tpl->setContent($this->form_gui->getHtml());
                return false;
            }
            
            // Update or create
            if ($this->server->getServerId()) {
                $this->server->update();
            } else {
                $this->server->create();
            }
            
            // Now server_id exists => update LDAP attribute mapping
            $this->initAttributeMapping();
            $this->mapping->setRule('global_role', (string) $this->form_gui->getInput('global_role'), false);
            $this->mapping->save();
    
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'serverList');
            return true;
        }
        
        $this->form_gui->setValuesByPost();
        return $this->tpl->setContent($this->form_gui->getHtml());
    }
    
    
    
    /**
     * Set sub tabs for ldap section
     */
    private function setSubTabs() : void
    {
        $this->tabs_gui->clearTargets();
        
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, 'serverList')
        );
        
        /*$this->tabs_gui->addSubTabTarget("ldap_settings",
            $this->ctrl->getLinkTarget($this,'serverList'),
            "serverList",get_class($this));*/
        
        $this->tabs_gui->addTab(
            "settings",
            $this->lng->txt("ldap_settings"),
            $this->ctrl->getLinkTarget($this, 'editServerSettings')
        );
        
        // Disable all other tabs, if server hasn't been configured.
        if (!count(ilLDAPServer::_getServerList())) {
            //TODO check why return true;
            return;
        }

        /*$this->tabs_gui->addSubTabTarget("ldap_user_mapping",
            $this->ctrl->getLinkTarget($this,'userMapping'),
            "userMapping",get_class($this));*/
        
        $this->tabs_gui->addTab(
            "user_mapping",
            $this->lng->txt("ldap_user_mapping"),
            $this->ctrl->getLinkTarget($this, 'userMapping')
        );
        
        /*$this->tabs_gui->addSubTabTarget('ldap_role_assignments',
            $this->ctrl->getLinkTarget($this,'roleAssignments'),
            "roleAssignments",get_class($this));*/
            
        $this->tabs_gui->addTab(
            "role_assignments",
            $this->lng->txt('ldap_role_assignments'),
            $this->ctrl->getLinkTarget($this, 'roleAssignments')
        );
        
        /*$this->tabs_gui->addSubTabTarget("ldap_role_mapping",
            $this->ctrl->getLinkTarget($this,'roleMapping'),
            "roleMapping",get_class($this));
            "roleMapping",get_class($this));*/
        $this->tabs_gui->addTab(
            "role_mapping",
            $this->lng->txt("ldap_role_mapping"),
            $this->ctrl->getLinkTarget($this, 'roleMapping')
        );
    }
    
    
    private function initServer() : void
    {
        if (!$_REQUEST['ldap_server_id']) {
            $_REQUEST['ldap_server_id'] = 0;
        }
        $this->server = new ilLDAPServer((int) $_REQUEST['ldap_server_id']);
    }
    
    private function initAttributeMapping() : void
    {
        $this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->getServer()->getServerId());
    }
    
    private function initRoleMapping() : void
    {
        $this->role_mapping = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->getServer()->getServerId());
    }
    
    /**
     * New implementation for InputForm
     */
    private function prepareGlobalRoleSelection()
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
    
    private function getMappingFields() : array
    {
        return array('gender' => $this->lng->txt('gender'),
                'firstname' => $this->lng->txt('firstname'),
                'lastname' => $this->lng->txt('lastname'),
                'title' => $this->lng->txt('person_title'),
                'institution' => $this->lng->txt('institution'),
                'department' => $this->lng->txt('department'),
                'street' => $this->lng->txt('street'),
                'city' => $this->lng->txt('city'),
                'zipcode' => $this->lng->txt('zipcode'),
                'country' => $this->lng->txt('country'),
                'phone_office' => $this->lng->txt('phone_office'),
                'phone_home' => $this->lng->txt('phone_home'),
                'phone_mobile' => $this->lng->txt('phone_mobile'),
                'fax' => $this->lng->txt('fax'),
                'email' => $this->lng->txt('email'),
                'second_email' => $this->lng->txt('second_email'),
                'hobby' => $this->lng->txt('hobby'),
                'matriculation' => $this->lng->txt('matriculation'));
        #'photo'			=> $this->lng->txt('photo'));
    }
    
    private function initUserDefinedFields() : void
    {
        $this->udf = ilUserDefinedFields::_getInstance();
    }
    
    private function prepareMappingSelect()
    {
        return ilLegacyFormElementsUtil::formSelect(
            $_POST['mapping_template'],
            'mapping_template',
            [0 => $this->lng->txt('ldap_mapping_template'),
             "inetOrgPerson" => 'inetOrgPerson',
             "organizationalPerson" => 'organizationalPerson',
             "person" => 'person',
             "ad_2003" => 'Active Directory (Win 2003)'
            ],
            false,
            true
        );
    }
    
    /**
     * Load info about hide/show details
     */
    private function loadMappingDetails() : void
    {
        if (!isset($_SESSION['ldap_mapping_details'])) {
            $_SESSION['ldap_mapping_details'] = array();
        }
        if (isset($_GET['details_show'])) {
            $_SESSION['ldap_mapping_details'][$_GET['details_show']] = $_GET['details_show'];
        }
        if (isset($_GET['details_hide'])) {
            unset($_SESSION['ldap_mapping_details'][$_GET['details_hide']]);
        }
    }
    
    /**
     * Init form table for new role assignments
     *
     * @param string mode edit | create
     */
    protected function initFormRoleAssignments(string $a_mode) : void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
     
        switch ($a_mode) {
            case 'edit':
                $this->form->setTitle($this->lng->txt('ldap_edit_role_ass_rule'));
                $this->form->addCommandButton('updateRoleAssignment', $this->lng->txt('save'));
                //$this->form->addCommandButton('roleAssignments',$this->lng->txt('cancel'));
                break;
            case 'create':
                $this->form->setTitle($this->lng->txt('ldap_add_role_ass_rule'));
                $this->form->addCommandButton('addRoleAssignment', $this->lng->txt('ldap_btn_add_role_ass'));
                //$this->form->addCommandButton('roleAssignments',$this->lng->txt('cancel'));
                break;
        }

        // Role Selection
        $role = new ilRadioGroupInputGUI($this->lng->txt('ldap_ilias_role'), 'role_name');
        $role->setRequired(true);
        
        $global = new ilRadioOption($this->lng->txt('ldap_global_role'), 0);
        $role->addOption($global);
        
        $role_select = new ilSelectInputGUI('', 'role_id');
        $role_select->setOptions($this->prepareGlobalRoleSelection());
        $global->addSubItem($role_select);
        
        $local = new ilRadioOption($this->lng->txt('ldap_local_role'), 1);
        $role->addOption($local);
        
        $role_search = new ilRoleAutoCompleteInputGUI('', 'role_search', $this, 'addRoleAutoCompleteObject');
        $role_search->setSize(40);
        $local->addSubItem($role_search);

        $role->setInfo($this->lng->txt('ldap_role_name_info'));
        $this->form->addItem($role);
        
        // Update options
        $update = new ilNonEditableValueGUI($this->lng->txt('ldap_update_roles'), 'update_roles');
        $update->setValue($this->lng->txt('ldap_check_role_assignment'));
        
        $add = new ilCheckboxInputGUI('', 'add_missing');
        $add->setOptionTitle($this->lng->txt('ldap_add_missing'));
        $update->addSubItem($add);
        
        $remove = new ilCheckboxInputGUI('', 'remove_deprecated');
        $remove->setOptionTitle($this->lng->txt('ldap_remove_deprecated'));
        $update->addSubItem($remove);
        
        $this->form->addItem($update);
        
        
        
        // Assignment Type
        $group = new ilRadioGroupInputGUI($this->lng->txt('ldap_assignment_type'), 'type');
        #$group->setValue($current_rule->getType());
        $group->setRequired(true);
        
        // Option by group
        $radio_group = new ilRadioOption($this->lng->txt('ldap_role_by_group'), ilLDAPRoleAssignmentRule::TYPE_GROUP);
        
        $dn = new ilTextInputGUI($this->lng->txt('ldap_group_dn'), 'dn');
        #$dn->setValue($current_rule->getDN());
        $dn->setSize(32);
        $dn->setMaxLength(512);
        $dn->setInfo($this->lng->txt('ldap_role_grp_dn_info'));
        $radio_group->addSubItem($dn);
        $at = new ilTextInputGUI($this->lng->txt('ldap_role_grp_at'), 'at');
        #$at->setValue($current_rule->getMemberAttribute());
        $at->setSize(16);
        $at->setMaxLength(128);
        $radio_group->addSubItem($at);
        $isdn = new ilCheckboxInputGUI($this->lng->txt('ldap_role_grp_isdn'), 'isdn');
        #$isdn->setChecked($current_rule->isMemberAttributeDN());
        $isdn->setInfo($this->lng->txt('ldap_group_member_info'));
        $radio_group->addSubItem($isdn);
        $radio_group->setInfo($this->lng->txt('ldap_role_grp_info'));
        
        $group->addOption($radio_group);
        
        // Option by Attribute
        $radio_attribute = new ilRadioOption($this->lng->txt('ldap_role_by_attribute'), ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE);
        $name = new ilTextInputGUI($this->lng->txt('ldap_role_at_name'), 'name');
        #$name->setValue($current_rule->getAttributeName());
        $name->setSize(32);
        $name->setMaxLength(128);
        #$name->setInfo($this->lng->txt('ldap_role_at_name_info'));
        $radio_attribute->addSubItem($name);
        
        // Radio Attribute
        $val = new ilTextInputGUI($this->lng->txt('ldap_role_at_value'), 'value');
        #$val->setValue($current_rule->getAttributeValue());
        $val->setSize(32);
        $val->setMaxLength(128);
        #$val->setInfo($this->lng->txt('ldap_role_at_value_info'));
        $radio_attribute->addSubItem($val);
        $radio_attribute->setInfo($this->lng->txt('ldap_role_at_info'));

        $group->addOption($radio_attribute);
        
        // Option by Plugin
        $pl_active = $this->component_repository->getPluginSlotById("ldaphk")->hasActivePlugins();
        $pl = new ilRadioOption($this->lng->txt('ldap_plugin'), 3);
        $pl->setInfo($this->lng->txt('ldap_plugin_info'));
        $pl->setDisabled(!$pl_active);
        
        $id = new ilNumberInputGUI($this->lng->txt('ldap_plugin_id'), 'plugin_id');
        $id->setDisabled(!$pl_active);
        $id->setSize(3);
        $id->setMaxLength(3);
        $id->setMaxValue(999);
        $id->setMinValue(1);
        $pl->addSubItem($id);

        $group->addOption($pl);
        $this->form->addItem($group);
    }
    
    
    /**
    * Add Member for autoComplete
    */
    public function addRoleAutoCompleteObject() : void
    {
        ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
    }
    
    /**
     * Create Toolbar
     */
    private function userMappingToolbar()
    {
        $select_form = new ilSelectInputGUI("mapping_template");
        $select_form->setPostVar("mapping_template");
        $options = array(
                        "" => $this->lng->txt('ldap_mapping_template'),
                        "inetOrgPerson" => 'inetOrgPerson',
                        "organizationalPerson" => 'organizationalPerson',
                        "person" => 'person',
                        "ad_2003" => 'Active Directory (Win 2003)');
        $select_form->setOptions($options);
        $select_form->setValue($_POST['mapping_template']);
        
        $this->toolbar->addInputItem($select_form);
        $this->toolbar->addFormButton($this->lng->txt('show'), "chooseMapping");
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "chooseMapping"));
    }
    
    /**
     * Create Property Form GUI for User Mapping
     */
    private function initUserMappingForm() : ilPropertyFormGUI
    {
        $propertie_form = new ilPropertyFormGUI();
        $propertie_form->setTitle($this->lng->txt('ldap_mapping_table'));
        $propertie_form->setFormAction($this->ctrl->getFormAction($this, 'saveMapping'));
        $propertie_form->addCommandButton('saveMapping', $this->lng->txt('save'));
        
        foreach ($this->getMappingFields() as $mapping => $lang) {
            $text_form = new ilTextInputGUI($lang);
            $text_form->setPostVar($mapping . "_value");
            $text_form->setValue($this->mapping->getValue($mapping));
            $text_form->setSize(32);
            $text_form->setMaxLength(255);
            $propertie_form->addItem($text_form);
            
            $checkbox_form = new ilCheckboxInputGUI("");
            $checkbox_form->setPostVar($mapping . "_update");
            $checkbox_form->setChecked($this->mapping->enabledUpdate($mapping));
            $checkbox_form->setOptionTitle($this->lng->txt('ldap_update_field_info'));
            $propertie_form->addItem($checkbox_form);
        }
        
        $this->initUserDefinedFields();
        foreach ($this->udf->getDefinitions() as $definition) {
            $text_form = new ilTextInputGUI($definition['field_name']);
            $text_form->setPostVar('udf_' . $definition['field_id'] . '_value');
            $text_form->setValue($this->mapping->getValue('udf_' . $definition['field_id']));
            $text_form->setSize(32);
            $text_form->setMaxLength(255);
            $propertie_form->addItem($text_form);
            
            $checkbox_form = new ilCheckboxInputGUI("");
            $checkbox_form->setPostVar('udf_' . $definition['field_id'] . '_update');
            $checkbox_form->setChecked($this->mapping->enabledUpdate('udf_' . $definition['field_id']));
            $checkbox_form->setOptionTitle($this->lng->txt('ldap_update_field_info'));
            $propertie_form->addItem($checkbox_form);
        }
        
        return $propertie_form;
    }
    
    /**
     * Role Mapping Tab
     */
    public function roleMapping() : void
    {
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_mapping');
        $this->toolbar->addButton(
            $this->lng->txt("ldap_new_role_assignment"),
            $this->ctrl->getLinkTarget($this, 'addRoleMapping')
        );
        
        //Set propertyform for synchronization settings
        $propertie_form = new ilPropertyFormGUI();
        $propertie_form->setTitle($this->lng->txt('ldap_role_settings'));
        $propertie_form->setFormAction($this->ctrl->getFormAction($this, "saveSyncronizationSettings"));
        $propertie_form->addCommandButton("saveSyncronizationSettings", $this->lng->txt('save'));
        $role_active = new ilCheckboxInputGUI($this->lng->txt('ldap_role_active'));
        $role_active->setPostVar('role_sync_active');
        $role_active->setChecked($this->server->enabledRoleSynchronization() ? true : false);
        $propertie_form->addItem($role_active);
        $binding = new ilCombinationInputGUI($this->lng->txt('ldap_server_binding'));
        $binding->setInfo($this->lng->txt('ldap_role_bind_user_info'));
        $user = new ilTextInputGUI("");
        $user->setPostVar("role_bind_user");
        $user->setValue($this->server->getRoleBindDN());
        $user->setSize(50);
        $user->setMaxLength(255);
        $binding->addCombinationItem(0, $user, $this->lng->txt('ldap_role_bind_user'));
        $pass = new ilPasswordInputGUI("");
        $pass->setPostVar("role_bind_pass");
        $pass->setValue($this->server->getRoleBindPassword());
        $pass->setSize(12);
        $pass->setMaxLength(36);
        $pass->setRetype(false);
        $binding->addCombinationItem(1, $pass, $this->lng->txt('ldap_role_bind_pass'));
        $propertie_form->addItem($binding);
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.ldap_role_mappings.html', 'Services/LDAP');
        $this->tpl->setVariable("NEW_ASSIGNMENT_TBL", $propertie_form->getHTML());
        
        //Set Group Assignments Table if mappings exist
        $mapping_instance = ilLDAPRoleGroupMappingSettings::_getInstanceByServerId($this->server->getServerId());
        $mappings = $mapping_instance->getMappings();
        if (count($mappings)) {
            $table_gui = new ilLDAPRoleMappingTableGUI($this, $this->server->getServerId());
            $table_gui->setTitle($this->lng->txt('ldap_role_group_assignments'));
            $table_gui->setData($mappings);
            $this->tpl->setVariable("RULES_TBL", $table_gui->getHTML());
        }
    }
    
    /**
     * Edit Assigments for role mapping
     */
    public function editRoleMapping() : void
    {
        $mapping = new ilLDAPRoleGroupMappingSetting($_GET["mapping_id"]);
        $mapping->read();
        
        $propertie_form = $this->initRoleMappingForm("updateRoleMapping");
        $propertie_form->setTitle($this->lng->txt('ldap_edit_role_assignment'));
        $propertie_form->getItemByPostVar("url")->setValue($mapping->getURL());
        $propertie_form->getItemByPostVar("dn")->setValue($mapping->getDN());
        $propertie_form->getItemByPostVar("member")->setValue($mapping->getMemberAttribute());
        $propertie_form->getItemByPostVar("memberisdn")->setChecked($mapping->getMemberISDN());
        $propertie_form->getItemByPostVar("role")->setValue($mapping->getRoleName());
        $propertie_form->getItemByPostVar("info")->setValue($mapping->getMappingInfo());
        $propertie_form->getItemByPostVar("info_type")->setChecked($mapping->getMappingInfoType());
        
        $this->tpl->setContent($propertie_form->getHTML());
    }
    
    
    
    /**
     * Check add screen input and save to db
     */
    public function createRoleMapping() : void
    {
        $propertie_form = $this->initRoleMappingForm("createRoleMapping");
        
        if ($propertie_form->checkInput() && $this->rbacReview->roleExists($propertie_form->getInput("role"))) {
            $mapping = new ilLDAPRoleGroupMappingSetting(0);
            $mapping->setServerId($this->server->getServerId());
            $mapping->setURL($propertie_form->getInput("url"));
            $mapping->setDN($propertie_form->getInput("dn"));
            $mapping->setMemberAttribute($propertie_form->getInput("member"));
            $mapping->setMemberISDN($propertie_form->getInput("memberisdn"));
            $mapping->setRoleByName($propertie_form->getInput("role"));
            $mapping->setMappingInfo($propertie_form->getInput("info"));
            $mapping->setMappingInfoType($propertie_form->getInput("info_type"));
            $mapping->save();
            
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "roleMapping");
        } else {
            if (!$this->rbacReview->roleExists($propertie_form->getInput("role"))) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("ldap_role_not_exists") . " " .
                        $propertie_form->getInput("role"));
            }
            $propertie_form->setValuesByPost();
            $this->tpl->setContent($propertie_form->getHTML());
        }
    }
    
    /**
     * confirm delete role mappings
     */
    public function confirmDeleteRoleMapping() : void
    {
        if (!is_array($_POST['mappings'])) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, "roleMapping");
            return;
        }
        
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRoleMapping"));
        $c_gui->setHeaderText($this->lng->txt("ldap_confirm_del_role_ass"));
        $c_gui->setCancel($this->lng->txt("cancel"), "roleMapping");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteRoleMapping");
        
        foreach ($_POST['mappings'] as $id) {
            $mapping = new ilLDAPRoleGroupMappingSetting($id);
            $mapping->read();
            $txt = $this->lng->txt('obj_role') . ": " . $mapping->getRoleName() . ", ";
            $txt .= $this->lng->txt('ldap_group_dn') . ": " . $mapping->getDN() . ", ";
            $txt .= $this->lng->txt('ldap_server_short') . " " . $mapping->getURL() . ", ";
            $txt .= $this->lng->txt('ldap_group_member_short') . " " . $mapping->getMemberAttribute();
            
            $c_gui->addItem("mappings[]", $id, $txt);
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function addServerSettings() : void
    {
        $this->checkAccess("write");

        $this->ctrl->clearParameters($this);

        $this->initForm();
        $this->tpl->setContent($this->form_gui->getHtml());
    }
    
    public function editServerSettings() : void
    {
        $this->checkAccess("write");

        $this->setSubTabs();
        $this->tabs_gui->setTabActive('settings');
        
        $this->initForm();
        $this->setServerFormValues();
        $this->tpl->setContent($this->form_gui->getHtml());
    }
    
    
    /**
     * Confirm delete rules
     */
    public function confirmDeleteServerSettings() : void
    {
        $this->checkAccess("write");

        if (!isset($_GET["ldap_server_id"])) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->serverList();
            //TODO check why return false
            return;
        }
        
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteServerSettings"));
        $c_gui->setHeaderText($this->lng->txt("ldap_confirm_del_server_settings"));
        $c_gui->setCancel($this->lng->txt("cancel"), "serverList");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteServerSettings");

        // add items to delete
        foreach ((array) $_GET["ldap_server_id"] as $server_id) {
            $setting = new ilLDAPServer((int) $server_id);
            $c_gui->addItem('server_ids[]', $server_id, $setting->getName());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }
    
    /**
     *
     */
    public function deleteServerSettings() : void
    {
        if (!is_array($_POST["server_ids"])) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->serverList();
            return;
        }
        
        foreach ((array) $_POST["server_ids"] as $server_id) {
            $setting = new ilLDAPServer((int) $server_id);
            $setting->delete();
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('deleted'));
        
        $this->serverList();
    }
    
    /**
     * Ldap User Mapping
     */
    public function userMapping() : void
    {
        $this->initAttributeMapping();
        
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('user_mapping');
        $this->userMappingToolbar();
        
        $propertie_form = $this->initUserMappingForm();
        
        $this->tpl->setContent($propertie_form->getHTML());
    }
    
    
    
    public function activateServer() : void
    {
        $this->checkAccess("write");

        $this->server->toggleActive(true);
        $this->server->update();
        $this->serverList();
    }
    
    public function deactivateServer() : void
    {
        $this->checkAccess("write");

        $this->server->toggleActive(false);
        $this->server->update();
        $this->serverList();
    }
    
    
    
    
    /**
     * init propertyformgui for Assignment of LDAP Attributes to ILIAS User Profile
     * @param string $command command methode
     */
    private function initRoleMappingForm(string $command) : ilPropertyFormGUI
    {
        $this->setSubTabs();
        $this->tabs_gui->setTabActive('role_mapping');
        
        if (isset($_GET["mapping_id"])) {
            $this->ctrl->setParameter($this, 'mapping_id', $_GET["mapping_id"]);
        }
        
        $propertie_form = new ilPropertyFormGUI();
        $propertie_form->setTitle($this->lng->txt('ldap_mapping_table'));
        $propertie_form->setFormAction($this->ctrl->getFormAction($this, $command));
        $propertie_form->addCommandButton($command, $this->lng->txt('save'));
        $propertie_form->addCommandButton("roleMapping", $this->lng->txt('cancel'));
        
        $url = new ilTextInputGUI($this->lng->txt('ldap_server'));
        $url->setPostVar("url");
        $url->setSize(50);
        $url->setMaxLength(255);
        $url->setRequired(true);
        
        $group_dn = new ilTextInputGUI($this->lng->txt('ldap_group_dn'));
        $group_dn->setPostVar("dn");
        $group_dn->setSize(50);
        $group_dn->setMaxLength(255);
        $group_dn->setInfo($this->lng->txt('ldap_dn_info'));
        $group_dn->setRequired(true);
        
        $member = new ilTextInputGUI($this->lng->txt('ldap_group_member'));
        $member->setPostVar("member");
        $member->setSize(32);
        $member->setMaxLength(255);
        $member->setInfo($this->lng->txt('ldap_member_info'));
        $member->setRequired(true);

        $member_isdn = new ilCheckboxInputGUI("");
        $member_isdn->setPostVar("memberisdn");
        $member_isdn->setOptionTitle($this->lng->txt('ldap_memberisdn'));
        
        $role = new ilTextInputGUI($this->lng->txt('ldap_ilias_role'));
        $role->setPostVar("role");
        $role->setSize(32);
        $role->setMaxLength(255);
        $role->setInfo($this->lng->txt('ldap_role_info'));
        $role->setRequired(true);
        
        $info = new ilTextAreaInputGUI($this->lng->txt('ldap_info_text'));
        $info->setPostVar("info");
        $info->setCols(50);
        $info->setRows(3);
        $info->setInfo($this->lng->txt('ldap_info_text_info'));
        
        $info_type = new ilCheckboxInputGUI("");
        $info_type->setPostVar("info_type");
        $info_type->setOptionTitle($this->lng->txt('ldap_mapping_info_type'));
        
        $propertie_form->addItem($url);
        $propertie_form->addItem($group_dn);
        $propertie_form->addItem($member);
        $propertie_form->addItem($member_isdn);
        $propertie_form->addItem($role);
        $propertie_form->addItem($info);
        $propertie_form->addItem($info_type);
        
        return $propertie_form;
    }
    

    
    /**
     * Add Assigments for role mapping
     */
    public function addRoleMapping() : void
    {
        $propertie_form = $this->initRoleMappingForm("createRoleMapping");
        $propertie_form->getItemByPostVar("url")->setValue($this->server->getUrl());
        
        if (isset($_GET["mapping_id"])) {
            $mapping = new ilLDAPRoleGroupMappingSetting($_GET["mapping_id"]);
            $mapping->read();
            
            $propertie_form->getItemByPostVar("url")->setValue($mapping->getURL());
            $propertie_form->getItemByPostVar("dn")->setValue($mapping->getDN());
            $propertie_form->getItemByPostVar("member")->setValue($mapping->getMemberAttribute());
            $propertie_form->getItemByPostVar("memberisdn")->setChecked($mapping->getMemberISDN());
            $propertie_form->getItemByPostVar("role")->setValue($mapping->getRoleName());
            $propertie_form->getItemByPostVar("info")->setValue($mapping->getMappingInfo());
            $propertie_form->getItemByPostVar("info_type")->setChecked($mapping->getMappingInfoType());
        }
        
        $this->tpl->setContent($propertie_form->getHTML());
    }
    
    
    /**
     * Check edit screen input and save to db
     */
    public function updateRoleMapping()
    {
        $propertie_form = $this->initRoleMappingForm("updateRoleMapping");
        
        if ($propertie_form->checkInput() && $this->rbacReview->roleExists($propertie_form->getInput("role"))) {
            $mapping = new ilLDAPRoleGroupMappingSetting($_GET["mapping_id"]);
            $mapping->setServerId($this->server->getServerId());
            $mapping->setURL($propertie_form->getInput("url"));
            $mapping->setDN($propertie_form->getInput("dn"));
            $mapping->setMemberAttribute($propertie_form->getInput("member"));
            $mapping->setMemberISDN($propertie_form->getInput("memberisdn"));
            $mapping->setRoleByName($propertie_form->getInput("role"));
            $mapping->setMappingInfo($propertie_form->getInput("info"));
            $mapping->setMappingInfoType($propertie_form->getInput("info_type"));
            $mapping->update();
            
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "roleMapping");
        } else {
            if (!$this->rbacReview->roleExists($propertie_form->getInput("role"))) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("ldap_role_not_exists") . " " .
                        $propertie_form->getInput("role"));
            }
            $propertie_form->setValuesByPost();
            $this->tpl->setContent($propertie_form->getHTML());
        }
    }
    
    /**
     * save Syncronization Settings on Role Mapping screen
     */
    public function saveSyncronizationSettings() : void
    {
        $this->server->setRoleBindDN(ilUtil::stripSlashes($_POST['role_bind_user']));
        $this->server->setRoleBindPassword(ilUtil::stripSlashes($_POST['role_bind_pass']));
        $this->server->enableRoleSynchronization((int) $_POST['role_sync_active']);
        
        // Update or create
        if ($this->server->getServerId()) {
            $this->server->update();
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, "roleMapping");
    }
}
