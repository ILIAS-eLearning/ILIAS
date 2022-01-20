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
    private ?\ilPropertyFormGUI $form = null;
    private ?ilShibbolethRoleAssignmentRule $rule = null;
    private \ilCtrl $ctrl;
    private \ilTabsGUI $tabs_gui;
    private \ilLanguage $lng;
    private \ilGlobalTemplateInterface $tpl;
    private int $ref_id;
    protected ilComponentRepository $component_repository;
    private \ILIAS\DI\RBACServices $rbac;
    private ilSetting $settings;
    private ilAccessHandler $access;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    private \ILIAS\Refinery\Factory $refinery;

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
        $this->settings = $DIC->settings();
        $this->tabs_gui = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('shib');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ref_id = $a_auth_ref_id;
        $this->component_repository = $DIC["component.repository"];
    }

    /**
     * Execute Command
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
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
        // set already saved data or default value for port
        $settings = $this->settings->getAll();
        // Compose role list
        $role_list = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);
        $role = [];
        if (!isset($settings["shib_user_default_role"])) {
            $settings["shib_user_default_role"] = 4;
        }
        if (!isset($settings["shib_idp_list"]) || $settings["shib_idp_list"] == '') {
            $settings["shib_idp_list"] = "urn:mace:organization1:providerID, Example Organization 1\nurn:mace:organization2:providerID, Example Organization 2, /Shibboleth.sso/WAYF/SWITCHaai";
        }
        if (!isset($settings["shib_login_button"]) || $settings["shib_login_button"] == '') {
            $settings["shib_login_button"] = "templates/default/images/shib_login_button.png";
        }
        if (!isset($settings["shib_hos_type"]) || $settings["shib_hos_type"] == '') {
            $settings["shib_hos_type"] = 'internal_wayf';
        }
        foreach ($role_list as $data) {
            $role[$data["obj_id"]] = $data["title"];
        }
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
            'shib_language',
            'shib_matriculation',
        );
        $propertys = new ilPropertyFormGUI();
        $propertys->setTitle($this->lng->txt("shib"));
        $propertys->setFormAction($this->ctrl->getFormAction($this, "save"));

        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $propertys->addCommandButton("save", $this->lng->txt("save"));
        }

        $propertys->addCommandButton("settings", $this->lng->txt("cancel"));
        //set enable shibboleth support
        $enable = new ilCheckboxInputGUI();
        $enable->setTitle($this->lng->txt("shib_active"));
        $read_me_link = "./Services/AuthShibboleth/README.SHIBBOLETH.txt";
        $info = "<a href='" . $read_me_link . "' target='_blank'>" . $this->lng->txt("auth_shib_instructions") . "</a>";
        $enable->setInfo($info);
        $enable->setPostVar("shib[active]");
        $enable->setChecked($settings["shib_active"] ?? false);
        //set allow local authentication
        $local = new ilCheckboxInputGUI();
        $local->setTitle($this->lng->txt("auth_allow_local"));
        $local->setPostVar("shib[auth_allow_local]");
        $local->setChecked($settings['shib_auth_allow_local'] ?? false);
        //set user default role
        $defaultrole = new ilSelectInputGUI();
        $defaultrole->setTitle($this->lng->txt("shib_user_default_role"));
        $defaultrole->setPostVar("shib[user_default_role]");
        $defaultrole->setOptions($role);
        $defaultrole->setRequired(true);
        $defaultrole->setValue($settings["shib_user_default_role"]);
        // Administrator must activate new user accounts
        $activate_new = new ilCheckboxInputGUI($this->lng->txt("shib_activate_new"), "shib[activate_new]");
        $activate_new->setInfo($this->lng->txt("shib_activate_new_info"));
        $activate_new->setChecked($settings["shib_activate_new"] ?? false);
        //set name of federation
        $name = new ilTextInputGUI();
        $name->setTitle($this->lng->txt("shib_federation_name"));
        $name->setPostVar("shib[federation_name]");
        $name->setSize(40);
        $name->setMaxLength(50);
        $name->setRequired(true);
        $name->setValue(stripslashes($settings["shib_federation_name"]));
        $organize = new ilRadioGroupInputGUI();
        $organize->setTitle($this->lng->txt("shib_login_type"));
        $organize->setPostVar("shib[hos_type]");
        $organize->setRequired(true);
        $organize->setValue($settings["shib_hos_type"]);
        //set 1. option internalwayf
        $internalwayf = new ilRadioOption();
        $internalwayf->setTitle($this->lng->txt("shib_login_internal_wayf"));
        $internalwayf->setValue("internal_wayf");
        //set 1. option internalwayf textbox idplist
        $idplist = new ilTextAreaInputGUI();
        $idplist->setInfo($this->lng->txt("shib_idp_list"));
        $idplist->setPostVar("shib[idp_list]");
        $idplist->setRows(3);
        $idplist->setCols(50);
        $idplist->setValue($settings["shib_idp_list"]);
        //set 2. Option externalwayf
        $externalwayf = new ilRadioOption();
        $externalwayf->setTitle($this->lng->txt("shib_login_external_wayf"));
        $externalwayf->setValue("external_wayf");
        //set 2. Option externalwayf textfield path to login button image
        $loginbutton = new ilTextInputGUI();
        $loginbutton->setInfo($this->lng->txt("shib_login_button"));
        $loginbutton->setPostVar("shib[login_button]");
        $loginbutton->setSize(50);
        $loginbutton->setMaxLength(255);
        $loginbutton->setValue($settings["shib_login_button"]);
        //set 3. Option embeddedwayf
        $embeddedwayf = new ilRadioOption();
        $embeddedwayf->setTitle($this->lng->txt("shib_login_embedded_wayf"));
        $embeddedwayf->setInfo($this->lng->txt("shib_login_embedded_wayf_description"));
        $embeddedwayf->setValue("embedded_wayf");
        //set login instructions
        $logininstruction = new ilTextAreaInputGUI();
        $logininstruction->setTitle($this->lng->txt("auth_login_instructions"));
        $logininstruction->setPostVar("shib[login_instructions]");
        $logininstruction->setRows(3);
        $logininstruction->setCols(50);
        $logininstruction->setValue(stripslashes($settings["shib_login_instructions"]));
        //set path to data manipulation API
        $dataconv = new ilTextInputGUI();
        $dataconv->setTitle($this->lng->txt("shib_data_conv"));
        $dataconv->setPostVar("shib[data_conv]");
        $dataconv->setSize(80);
        $dataconv->setMaxLength(512);
        $dataconv->setValue($settings["shib_data_conv"]);
        //field mappings
        $fields = array();
        foreach ($shib_settings as $setting) {
            $field = str_replace('shib_', '', $setting);
            $textinput = new ilTextInputGUI();
            $textinput->setTitle($this->lng->txt($setting));
            $textinput->setPostVar("shib[" . $field . "]");
            $textinput->setValue($settings[$setting]);
            $textinput->setSize(40);
            $textinput->setMaxLength(50);
            $checkinput = new ilCheckboxInputGUI("");
            $checkinput->setOptionTitle($this->lng->txt("shib_update"));
            $checkinput->setPostVar("shib[update_" . $field . "]");
            $checkinput->setChecked($settings["shib_update_" . $field] ?? false);
            if ($setting == 'shib_login' || $setting == 'shib_firstname'
                || $setting == 'shib_lastname'
                || $setting == 'shib_email'
            ) {
                $textinput->setRequired(true);
            }
            $fields[$setting] = array("text" => $textinput, "check" => $checkinput);
        }
        $propertys->addItem($enable);
        $propertys->addItem($local);
        $propertys->addItem($activate_new);
        $propertys->addItem($defaultrole);
        $propertys->addItem($name);
        $internalwayf->addSubItem($idplist);
        $organize->addOption($internalwayf);
        $externalwayf->addSubItem($loginbutton);
        $organize->addOption($externalwayf);
        $organize->addOption($embeddedwayf);
        $propertys->addItem($organize);
        $propertys->addItem($logininstruction);
        $propertys->addItem($dataconv);
        foreach ($shib_settings as $setting) {
            $propertys->addItem($fields[$setting]["text"]);
            if ($setting != "shib_login") {
                $propertys->addItem($fields[$setting]["check"]);
            }
        }
        $this->tpl->setContent($propertys->getHTML());
    }

    public function save() : void
    {
        $post = $this->wrapper->post()->has('shib')
            ? $this->wrapper->post()->retrieve('shib',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string()))
            : [];

        $retriever = function (string $key) use ($post) : string {
            return $post[$key] ?? '';
        };

        $required = [
            "login",
            "hos_type",
            "firstname",
            "lastname",
            "email",
            "user_default_role",
            "federation_name"
        ];
        array_walk($required, function (&$item) use($retriever) : void {
            if (!$retriever($item)) {
                ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields"), true);
                $this->ctrl->redirect($this, 'settings');
            }
        });

        // validate api
        $data_conv = $retriever("data_conv");
        if ($data_conv !== '' && !is_readable($data_conv)) {
            ilUtil::sendFailure($this->lng->txt("shib_data_conv_warning"), true);
            $this->ctrl->redirect($this, 'settings');
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
            'shib_language',
            'shib_matriculation'
        );
        foreach ($shib_settings as $setting) {
            $field = str_replace('shib_', '', $setting);
            $this->settings->set($setting, trim($retriever($field)));
            $this->settings->set("shib_update_" . $field, $retriever("update_" . $field));
        }
        if ($retriever("active") != "1") {
            $this->settings->set("shib_active", "0");
            $this->settings->set("shibboleth_active", "0");
        } else {
            $this->settings->set("shib_active", "1");
            $this->settings->set("shibboleth_active", "1");
        }
        $this->settings->set("shib_user_default_role", $retriever("user_default_role"));
        $this->settings->set("shib_hos_type", $retriever("hos_type"));
        $this->settings->set("shib_federation_name", $retriever("federation_name"));
        $this->settings->set("shib_idp_list", $retriever("idp_list"));
        $this->settings->set("shib_login_instructions", $retriever("login_instructions"));
        $this->settings->set("shib_login_button", $retriever("login_button"));
        $this->settings->set("shib_data_conv", $retriever("data_conv"));
        $this->settings->set("shib_auth_allow_local", $retriever('auth_allow_local'));
        $this->settings->set("shib_activate_new", $retriever('activate_new'));

        ilUtil::sendSuccess($this->lng->txt("shib_settings_saved"), true);
        $this->ctrl->redirect($this, 'settings');
    }

    protected function roleAssignment() : bool
    {
        $this->tabs_gui->setSubTabActive('shib_role_assignment');
        $this->initFormRoleAssignment('default');
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html',
            'Services/AuthShibboleth');
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
        $rules_table->setSelectAllCheckbox("rule_id");

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
            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html',
                'Services/AuthShibboleth');
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
        $this->ctrl->setParameter($this, 'rule_id', (int) $_GET['rule_id']);
        $this->tabs_gui->setSubTabActive('shib_role_assignment');
        $this->initFormRoleAssignment('update');
        $this->getRuleValues();
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html',
            'Services/AuthShibboleth');
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
        if (!$this->form->checkInput() || ($err = $this->checkInput((int) $_REQUEST['rule_id']))) {
            if ($err) {
                ilUtil::sendFailure($this->lng->txt($err));
            }
            $this->tabs_gui->setSubTabActive('shib_role_assignment');
            $this->form->setValuesByPost();
            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.shib_role_assignment.html',
                'Services/AuthShibboleth');
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
        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $rule = new ilShibbolethRoleAssignmentRule((int) $_GET['rule_id']);
        $role = $rule->getRoleId();
        if ($rbacreview->isGlobalRole($role)) {
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

    private function checkInput($a_rule_id = 0)
    {
        $this->loadRule($a_rule_id);

        return $this->rule->validate();
    }

    private function showLocalRoleSelection()
    {
        if ($this->rule->getRoleId() > 0) {
            return false;
        }
        $_SESSION['shib_role_ass']['rule_id'] = $_REQUEST['rule_id'] ? $_REQUEST['rule_id'] : 0;
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
        $rule = new ilShibbolethRoleAssignmentRule($_SESSION['shib_role_ass']['rule_id']);
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
