<?php
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
 * Class ilAuthShibbolethSettingsGUI
 *
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version      $Id$
 *
 * @ingroup      AuthShibboleth
 */
class ilAuthShibbolethSettingsGUI
{
    const PARAM_RULE_ID = 'rule_id';
    private ?\ilPropertyFormGUI $form = null;
    private ?ilShibbolethRoleAssignmentRule $rule = null;
    private \ilCtrl $ctrl;
    private \ilTabsGUI $tabs_gui;
    private \ilLanguage $lng;
    private \ilGlobalTemplateInterface $tpl;
    private int $ref_id;
    protected ilComponentRepository $component_repository;
    private \ILIAS\DI\RBACServices $rbac;
    private ilAccessHandler $access;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    private \ILIAS\Refinery\Factory $refinery;
    protected ilShibbolethSettings $shib_settings;

    /**
     *
     * @param
     *
     * @return \ilAuthShibbolethSettingsGUI
     */
    public function __construct(int $a_auth_ref_id)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->rbac = $DIC->rbac();
        $this->access = $DIC->access();
        $this->tabs_gui = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('shib');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ref_id = $a_auth_ref_id;
        $this->component_repository = $DIC["component.repository"];

        $this->shib_settings = new ilShibbolethSettings();
    }

    /**
     * Execute Command
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        if (!$this->access->checkAccess('read', '', $this->ref_id)) {
            throw new ilException('Permission denied');
        }
        if (!$this->access->checkAccess('write', '', $this->ref_id) && $cmd != "settings") {
            ilUtil::sendFailure($this->lng->txt('msg_no_perm_write'), true);
            $this->ctrl->redirect($this, "settings");
        }
        $this->setSubTabs();
        if (!$cmd) {
            $cmd = "settings";
        }
        $this->$cmd();
    }

    public function settings() : void
    {
        $this->tabs_gui->setSubTabActive('shib_settings');
        $form = new ilShibbolethSettingsForm(
            $this->shib_settings,
            $this->ctrl->getLinkTarget($this, 'save')
        );

        $this->tpl->setContent($form->getHTML());
    }

    public function save() : void
    {
        $form = new ilShibbolethSettingsForm($this->shib_settings, 'save');
        $form->setValuesByPost();
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt("shib_settings_saved"), true);
            $this->ctrl->redirect($this, 'settings');
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function roleAssignment() : bool
    {
        $this->tabs_gui->setSubTabActive('shib_role_assignment');
        $this->initFormRoleAssignment('default');
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.shib_role_assignment.html',
            'Services/AuthShibboleth'
        );
        $this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());
        if (strlen($html = $this->parseRulesTable()) !== 0) {
            $this->tpl->setVariable('RULE_TABLE', $html);
        }

        return true;
    }

    protected function parseRulesTable() : string
    {
        if (ilShibbolethRoleAssignmentRules::getCountRules() == 0) {
            return '';
        }
        $rules_table = new ilShibbolethRoleAssignmentTableGUI($this, 'roleAssignment');
        $rules_table->setTitle($this->lng->txt('shib_rules_tables'));
        $rules_table->parse(ilShibbolethRoleAssignmentRules::getAllRules());
        $rules_table->addMultiCommand("confirmDeleteRules", $this->lng->txt("delete"));
        $rules_table->setSelectAllCheckbox(self::PARAM_RULE_ID);

        return $rules_table->getHTML();
    }

    /**
     * Confirm delete rules
     *
     * @access public
     *
     * @param
     *
     */
    protected function confirmDeleteRules()
    {
        if (!is_array($_POST['rule_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->roleAssignment();

            return false;
        }
        $this->tabs_gui->setSubTabActive('shib_role_assignment');
        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteRules"));
        $c_gui->setHeaderText($this->lng->txt("shib_confirm_del_role_ass"));
        $c_gui->setCancel($this->lng->txt("cancel"), "roleAssignment");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteRules");
        foreach ($_POST["rule_ids"] as $rule_id) {
            $rule = new ilShibbolethRoleAssignmentRule($rule_id);
            $info = ilObject::_lookupTitle($rule->getRoleId());
            $info .= " (";
            $info .= $rule->conditionToString();
            $info .= ')';
            $c_gui->addItem('rule_ids[]', $rule_id, $info);
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * delete role assignment rule
     *
     * @access public
     *
     */
    protected function deleteRules() : bool
    {
        if (!is_array($_POST['rule_ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_once'));
            $this->roleAssignment();

            return false;
        }
        foreach ($_POST["rule_ids"] as $rule_id) {
            $rule = new ilShibbolethRoleAssignmentRule($rule_id);
            $rule->delete();
        }
        ilUtil::sendSuccess($this->lng->txt('shib_deleted_rule'));
        $this->roleAssignment();

        return true;
    }

    protected function initFormRoleAssignment($a_mode = 'default') : void
    {
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'cancel'));
        $this->form->setTitle($this->lng->txt('shib_role_ass_table'));
        if ($a_mode == 'default') {
            $this->form->setTitle($this->lng->txt('shib_role_ass_table'));
            $this->form->addCommandButton('addRoleAssignmentRule', $this->lng->txt('shib_new_rule'));
            $this->form->addCommandButton('settings', $this->lng->txt('cancel'));
        } else {
            $this->form->setTitle($this->lng->txt('shib_update_role_ass_table'));
            $this->form->addCommandButton('updateRoleAssignmentRule', $this->lng->txt('save'));
            $this->form->addCommandButton('roleAssignment', $this->lng->txt('cancel'));
        }
        // Role selection
        $role = new ilRadioGroupInputGUI($this->lng->txt('shib_role_name'), 'role_name');
        $role->setRequired(true);
        $global = new ilRadioOption($this->lng->txt('shib_global_role'), 0);
        $role->addOption($global);
        $role_select = new ilSelectInputGUI('', 'role_id');
        $role_select->setOptions($this->prepareRoleSelect());
        $global->addSubItem($role_select);
        $local = new ilRadioOption($this->lng->txt('shib_local_role'), 1);
        $role->addOption($local);
        $role_search = new ilRoleAutoCompleteInputGUI('', 'role_search', self::class, 'addRoleAutoCompleteObject');
        $role_search->setSize(40);
        $local->addSubItem($role_search);
        $role->setInfo($this->lng->txt('shib_role_name_info'));
        $this->form->addItem($role);
        // Update options
        $update = new ilNonEditableValueGUI($this->lng->txt('shib_update_roles'), 'update_roles');
        $update->setValue($this->lng->txt('shib_check_role_assignment'));
        $add = new ilCheckboxInputGUI('', 'add_missing');
        $add->setOptionTitle($this->lng->txt('shib_add_missing'));
        $add->setValue(1);
        $update->addSubItem($add);
        $remove = new ilCheckboxInputGUI('', 'remove_deprecated');
        $remove->setOptionTitle($this->lng->txt('shib_remove_deprecated'));
        $remove->setValue(1);
        $update->addSubItem($remove);
        $this->form->addItem($update);
        // Assignment type
        $kind = new ilRadioGroupInputGUI($this->lng->txt('shib_assignment_type'), 'kind');
        $kind->setValue(1);
        $kind->setRequired(true);
        $attr = new ilRadioOption($this->lng->txt('shib_attribute'), 1);
        $attr->setInfo($this->lng->txt('shib_attr_info'));
        $name = new ilTextInputGUI($this->lng->txt('shib_attribute_name'), 'attr_name');
        $name->setSize(32);
        $attr->addSubItem($name);
        $value = new ilTextInputGUI($this->lng->txt('shib_attribute_value'), 'attr_value');
        $value->setSize(32);
        $attr->addSubItem($value);
        $kind->addOption($attr);
        $pl_active = $this->component_repository->getPluginSlotById('shibhk')->hasActivePlugins();
        $pl = new ilRadioOption($this->lng->txt('shib_plugin'), 2);
        $pl->setInfo($this->lng->txt('shib_plugin_info'));
        $pl->setDisabled(!$pl_active);
        $id = new ilNumberInputGUI($this->lng->txt('shib_plugin_id'), 'plugin_id');
        $id->setDisabled(!$pl_active);
        $id->setSize(3);
        $id->setMaxLength(3);
        $id->setMaxValue(999);
        $id->setMinValue(1);
        $pl->addSubItem($id);
        $kind->addOption($pl);
        $this->form->addItem($kind);
    }

    /**
     * Add Member for autoComplete
     */
    public function addRoleAutoCompleteObject() : void
    {
        ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
    }

    protected function addRoleAssignmentRule() : bool
    {
        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->roleAssignment();

            return false;
        }
        $this->initFormRoleAssignment();
        if (!$this->form->checkInput() || ($err = $this->checkInput())) {
            if (isset($err)) {
                ilUtil::sendFailure($this->lng->txt($err));
            }
            $this->tabs_gui->setSubTabActive('shib_role_assignment');
            $this->form->setValuesByPost();
            $this->tpl->addBlockFile(
                'ADM_CONTENT',
                'adm_content',
                'tpl.shib_role_assignment.html',
                'Services/AuthShibboleth'
            );
            $this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());
            if (strlen($html = $this->parseRulesTable()) !== 0) {
                $this->tpl->setVariable('RULE_TABLE', $html);
            }

            return true;
        }
        // Redirects if required
        $this->showLocalRoleSelection();
        $this->rule->add();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->roleAssignment();

        return true;
    }

    /**
     * Edit Role Assignment
     *
     * @return
     */
    protected function editRoleAssignment() : bool
    {
        $this->ctrl->saveParameter($this, self::PARAM_RULE_ID);
        $this->tabs_gui->setSubTabActive('shib_role_assignment');
        $this->initFormRoleAssignment('update');
        $this->getRuleValues();
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.shib_role_assignment.html',
            'Services/AuthShibboleth'
        );
        $this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());

        return true;
    }

    protected function updateRoleAssignmentRule() : bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->roleAssignment();

            return false;
        }
        $this->initFormRoleAssignment();
        $err = false;
        if (!$this->form->checkInput() || ($err = $this->checkInput((int) $_REQUEST[self::PARAM_RULE_ID]))) {
            if ($err) {
                ilUtil::sendFailure($this->lng->txt($err));
            }
            $this->tabs_gui->setSubTabActive('shib_role_assignment');
            $this->form->setValuesByPost();
            $this->tpl->addBlockFile(
                'ADM_CONTENT',
                'adm_content',
                'tpl.shib_role_assignment.html',
                'Services/AuthShibboleth'
            );
            $this->tpl->setVariable('NEW_RULE_TABLE', $this->form->getHTML());

            return true;
        }
        $this->showLocalRoleSelection();
        $this->rule->update();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->roleAssignment();

        return true;
    }

    private function loadRule($a_rule_id = 0) : \ilShibbolethRoleAssignmentRule
    {
        $this->rule = new ilShibbolethRoleAssignmentRule($a_rule_id);
        if ($this->form->getInput('role_name') == 0) {
            $this->rule->setRoleId($this->form->getInput('role_id'));
        } elseif ($this->form->getInput('role_search')) {
            $parser = new ilQueryParser($this->form->getInput('role_search'));
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
        $this->rule->setName($this->form->getInput('attr_name'));
        $this->rule->setValue($this->form->getInput('attr_value'));
        $this->rule->enableAddOnUpdate($this->form->getInput('add_missing'));
        $this->rule->enableRemoveOnUpdate($this->form->getInput('remove_deprecated'));
        $this->rule->enablePlugin($this->form->getInput('kind') == 2);
        $this->rule->setPluginId($this->form->getInput('plugin_id'));

        return $this->rule;
    }

    private function getRuleValues() : void
    {
        $rule_id = $this->wrapper->query()->has(self::PARAM_RULE_ID)
            ? $this->wrapper->query()->retrieve(self::PARAM_RULE_ID, $this->refinery->kindlyTo()->int())
            : 0;

        $rule = new ilShibbolethRoleAssignmentRule($rule_id);
        $role = $rule->getRoleId();
        if ($this->rbac->review()->isGlobalRole($role)) {
            $values['role_name'] = 0;
            $values['role_id'] = $role;
        } else {
            $values['role_name'] = 1;
            $values['role_search'] = ilObject::_lookupTitle($role);
        }
        $values['add_missing'] = (int) $rule->isAddOnUpdateEnabled();
        $values['remove_deprecated'] = (int) $rule->isRemoveOnUpdateEnabled();
        $values['attr_name'] = $rule->getName();
        $values['attr_value'] = $rule->getValue();
        if (!$rule->isPluginActive()) {
            $values['kind'] = 1;
        } else {
            $values['kind'] = 2;
            $values['plugin_id'] = $rule->getPluginId();
        }
        $this->form->setValuesByArray($values);
    }

    private function checkInput($a_rule_id = 0) : string
    {
        $this->loadRule($a_rule_id);

        return $this->rule->validate();
    }

    private function showLocalRoleSelection()
    {
        if ($this->rule->getRoleId() > 0) {
            return false;
        }
        $_SESSION['shib_role_ass'][self::PARAM_RULE_ID] = $_REQUEST[self::PARAM_RULE_ID] ? $_REQUEST[self::PARAM_RULE_ID] : 0;
        $_SESSION['shib_role_ass']['search'] = $this->form->getInput('role_search');
        $_SESSION['shib_role_ass']['add_on_update'] = $this->rule->isAddOnUpdateEnabled();
        $_SESSION['shib_role_ass']['remove_on_update'] = $this->rule->isRemoveOnUpdateEnabled();
        $_SESSION['shib_role_ass']['name'] = $this->rule->getName();
        $_SESSION['shib_role_ass']['value'] = $this->rule->getValue();
        $_SESSION['shib_role_ass']['plugin'] = $this->rule->isPluginActive();
        $_SESSION['shib_role_ass']['plugin_id'] = $this->rule->getPluginId();
        $this->ctrl->redirect($this, 'chooseRole');
    }

    protected function chooseRole() : bool
    {
        $this->tabs_gui->setSubTabActive('shib_role_assignment');
        $parser = new ilQueryParser($_SESSION['shib_role_ass']['search']);
        $parser->setMinWordLength(1);
        $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $parser->parse();
        $object_search = new ilLikeObjectSearch($parser);
        $object_search->setFilter(array('role'));
        $res = $object_search->performSearch();
        $entries = $res->getEntries();
        $table = new ilRoleSelectionTableGUI($this, 'chooseRole');
        $table->setTitle($this->lng->txt('shib_role_selection'));
        $table->addMultiCommand('saveRoleSelection', $this->lng->txt('shib_choose_role'));
        $table->addCommandButton('roleAssignment', $this->lng->txt('cancel'));
        $table->parse($entries);
        $this->tpl->setContent($table->getHTML());

        return true;
    }

    protected function saveRoleSelection() : void
    {
        $rule = new ilShibbolethRoleAssignmentRule($_SESSION['shib_role_ass'][self::PARAM_RULE_ID]);
        $rule->setRoleId((int) $_POST['role_id']);
        $rule->setName($_SESSION['shib_role_ass']['name']);
        $rule->setValue($_SESSION['shib_role_ass']['value']);
        $rule->enablePlugin($_SESSION['shib_role_ass']['plugin']);
        $rule->setPluginId($_SESSION['shib_role_ass']['plugin_id']);
        $rule->enableAddOnUpdate($_SESSION['shib_role_ass']['add_on_update']);
        $rule->enableRemoveOnUpdate($_SESSION['shib_role_ass']['remove_on_update']);
        if ($rule->getRuleId() !== 0) {
            $rule->update();
        } else {
            $rule->add();
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        unset($_SESSION['shib_role_ass']);
        $this->roleAssignment();
    }

    /**
     * @return array<int|string, string>
     */
    private function prepareRoleSelect() : array
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $global_roles = ilUtil::_sortIds($rbacreview->getGlobalRoles(), 'object_data', 'title', 'obj_id');
        $select[0] = $this->lng->txt('links_select_one');
        foreach ($global_roles as $role_id) {
            $select[$role_id] = ilObject::_lookupTitle($role_id);
        }
        return $select;
    }

    protected function setSubTabs() : bool
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        if ($ilSetting->get('shib_active') == 0 && ilShibbolethRoleAssignmentRules::getCountRules() == 0) {
            return false;
        }
        // DONE: show sub tabs if there is any role assignment rule
        $this->tabs_gui->addSubTabTarget('shib_settings', $this->ctrl->getLinkTarget($this, 'settings'));
        $this->tabs_gui->addSubTabTarget('shib_role_assignment', $this->ctrl->getLinkTarget($this, 'roleAssignment'));

        return true;
    }
}
