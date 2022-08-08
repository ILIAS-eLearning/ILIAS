<?php declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilRegistrationSettingsGUI
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilRegistrationSettingsGUI:
 * @ingroup      ServicesRegistration
 */
class ilRegistrationSettingsGUI
{
    public const CODE_TYPE_REGISTRATION = 1;
    public const CODE_TYPE_EXTENSION = 2;

    /**
     * @todo make private
     */
    public int $ref_id;

    protected ilCtrlInterface $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilAccessHandler $access;
    protected ilLanguage $lng;
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\Refinery\Factory $refinery;

    protected ilRegistrationSettings $registration_settings;
    protected ?ilRegistrationRoleAssignments $assignments_obj = null;
    protected ?ilRegistrationRoleAccessLimitations $access_limitations_obj = null;

    protected ?ilPropertyFormGUI $form_gui = null;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->error = $DIC['ilErr'];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('user');
        $this->registration_settings = new ilRegistrationSettings();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ref_id = $this->initRefIdFromQuery();
    }

    protected function initRefIdFromQuery() : int
    {
        if ($this->http->wrapper()->query()->has('ref_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = 'view';
                }
                $this->$cmd();
                break;
        }
    }

    protected function checkAccess(string $a_permission) : void
    {
        if (!$this->checkAccessBool($a_permission)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->WARNING);
        }
    }

    protected function checkAccessBool(string $a_permission) : bool
    {
        return $this->access->checkAccess($a_permission, '', $this->ref_id);
    }

    public function setSubTabs(string $activeTab = 'registration_settings') : void
    {
        $this->tabs->addSubTab(
            "registration_settings",
            $this->lng->txt("registration_tab_settings"),
            $this->ctrl->getLinkTarget($this, 'view')
        );
        $this->tabs->addSubTab(
            "registration_codes",
            $this->lng->txt("registration_tab_codes"),
            $this->ctrl->getLinkTarget($this, 'listCodes')
        );
        $this->tabs->activateSubTab($activeTab);
    }

    public function initForm() : ilPropertyFormGUI
    {
        $form_gui = new ilPropertyFormGUI();
        $form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $form_gui->setTitle($this->lng->txt('reg_settings_header'));

        $reg_type = new ilRadioGroupInputGUI($this->lng->txt('reg_type'), 'reg_type');
        $reg_type->addOption(new ilRadioOption(
            $this->lng->txt('reg_disabled'),
            (string) ilRegistrationSettings::IL_REG_DISABLED
        ));
        $option = new ilRadioOption($this->lng->txt('reg_direct'), (string) ilRegistrationSettings::IL_REG_DIRECT);
        $option->setInfo($this->lng->txt('reg_direct_info'));
        $cd = new ilCheckboxInputGUI(
            $this->lng->txt('reg_allow_codes'),
            'reg_codes_' . ilRegistrationSettings::IL_REG_DIRECT
        );
        $cd->setInfo($this->lng->txt('reg_allow_codes_info'));
        $option->addSubItem($cd);
        $reg_type->addOption($option);
        $option = new ilRadioOption($this->lng->txt('reg_approve'), (string) ilRegistrationSettings::IL_REG_APPROVE);
        $option->setInfo($this->lng->txt('reg_approve_info'));
        $cd = new ilCheckboxInputGUI(
            $this->lng->txt('reg_allow_codes'),
            'reg_codes_' . ilRegistrationSettings::IL_REG_APPROVE
        );
        $cd->setInfo($this->lng->txt('reg_allow_codes_info'));
        $option->addSubItem($cd);
        $reg_type->addOption($option);
        $option = new ilRadioOption(
            $this->lng->txt('reg_type_confirmation'),
            (string) ilRegistrationSettings::IL_REG_ACTIVATION
        );
        $option->setInfo($this->lng->txt('reg_type_confirmation_info'));
        $lt = new ilNumberInputGUI($this->lng->txt('reg_confirmation_hash_life_time'), 'reg_hash_life_time');
        $lt->setSize(6); // #8511
        $lt->setMaxLength(6);
        $lt->setMinValue(ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE);
        $lt->setRequired(true);
        $lt->setInfo($this->lng->txt('reg_confirmation_hash_life_time_info'));
        $lt->setSuffix($this->lng->txt('seconds'));
        $option->addSubItem($lt);
        $cd = new ilCheckboxInputGUI(
            $this->lng->txt('reg_allow_codes'),
            'reg_codes_' . ilRegistrationSettings::IL_REG_ACTIVATION
        );
        $cd->setInfo($this->lng->txt('reg_allow_codes_info'));
        $option->addSubItem($cd);
        $reg_type->addOption($option);
        $option = new ilRadioOption(
            $this->lng->txt('registration_reg_type_codes'),
            (string) ilRegistrationSettings::IL_REG_CODES
        );
        $option->setInfo($this->lng->txt('registration_reg_type_codes_info'));
        $reg_type->addOption($option);
        $form_gui->addItem($reg_type);

        $pwd_gen = new ilCheckboxInputGUI($this->lng->txt('passwd_generation'), 'reg_pwd');
        $pwd_gen->setValue('1');
        $pwd_gen->setInfo($this->lng->txt('reg_info_pwd'));
        $form_gui->addItem($pwd_gen);

        $approver = new ilTextInputGUI($this->lng->txt('reg_notification'), 'reg_approver');
        $approver->setSize(32);
        $approver->setMaxLength(50);
        $approver->setInfo($this->lng->txt('reg_notification_info'));
        $form_gui->addItem($approver);

        $roles = new ilRadioGroupInputGUI($this->lng->txt('reg_role_assignment'), 'reg_role_type');
        $option = new ilRadioOption($this->lng->txt('reg_fixed'), (string) ilRegistrationSettings::IL_REG_ROLES_FIXED);
        $list = new ilCustomInputGUI($this->lng->txt('reg_available_roles'));
        $edit = $this->ctrl->getLinkTarget($this, 'editRoles');
        $list->setHtml($this->parseRoleList($this->prepareRoleList(), $edit));
        $option->addSubItem($list);
        $roles->addOption($option);
        $option = new ilRadioOption($this->lng->txt('reg_email'), (string) ilRegistrationSettings::IL_REG_ROLES_EMAIL);
        $list = new ilCustomInputGUI($this->lng->txt('reg_available_roles'));
        $edit = $this->ctrl->getLinkTarget($this, 'editEmailAssignments');
        $list->setHtml($this->parseRoleList($this->prepareAutomaticRoleList(), $edit));
        $option->addSubItem($list);
        $roles->addOption($option);
        $roles->setInfo($this->lng->txt('registration_codes_override_global_info'));
        $form_gui->addItem($roles);

        $limit = new ilCheckboxInputGUI($this->lng->txt('reg_access_limitations'), 'reg_access_limitation');
        $limit->setValue('1');
        $list = new ilCustomInputGUI($this->lng->txt('reg_available_roles'));
        $edit = $this->ctrl->getLinkTarget($this, 'editRoleAccessLimitations');
        $list->setHtml($this->parseRoleList($this->prepareAccessLimitationRoleList(), $edit));
        $list->setInfo($this->lng->txt('registration_codes_override_global_info'));
        $limit->addSubItem($list);
        $form_gui->addItem($limit);

        $domains = new ilTextInputGUI($this->lng->txt('reg_allowed_domains'), 'reg_allowed_domains');
        $domains->setInfo($this->lng->txt('reg_allowed_domains_info'));
        $form_gui->addItem($domains);

        if ($this->rbacsystem->checkAccess("write", $this->ref_id)) {
            $form_gui->addCommandButton('save', $this->lng->txt('save'));
        }
        return $form_gui;
    }

    public function initFormValues(ilPropertyFormGUI $formGUI) : void
    {
        $role_type = ilRegistrationSettings::IL_REG_ROLE_UNDEFINED;
        if ($this->registration_settings->roleSelectionEnabled()) {
            $role_type = ilRegistrationSettings::IL_REG_ROLES_FIXED;
        } elseif ($this->registration_settings->automaticRoleAssignmentEnabled()) {
            $role_type = ilRegistrationSettings::IL_REG_ROLES_EMAIL;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $values = [
            'reg_type' => $this->registration_settings->getRegistrationType(),
            'reg_hash_life_time' => $this->registration_settings->getRegistrationHashLifetime(),
            'reg_pwd' => $this->registration_settings->passwordGenerationEnabled(),
            'reg_approver' => $this->registration_settings->getApproveRecipientLogins(),
            'reg_role_type' => $role_type,
            'reg_access_limitation' => $this->registration_settings->getAccessLimitation(),
            'reg_allowed_domains' => implode(';', $this->registration_settings->getAllowedDomains())
        ];

        $allow_codes = $this->registration_settings->getAllowCodes();
        $reg_type = $this->registration_settings->getRegistrationType();
        if ($allow_codes && in_array($reg_type, [
                ilRegistrationSettings::IL_REG_DIRECT,
                ilRegistrationSettings::IL_REG_APPROVE,
                ilRegistrationSettings::IL_REG_ACTIVATION
            ], true)) {
            $values['reg_codes_' . $reg_type] = true;
        }

        $formGUI->setValuesByArray($values);
    }

    public function view() : void
    {
        $this->checkAccess('visible,read');
        $this->setSubTabs();

        // edit new accout mail
        $this->ctrl->setParameterByClass("ilobjuserfoldergui", "ref_id", USER_FOLDER_ID);
        if ($this->checkAccessBool('write')) {
            $this->toolbar->addButton(
                $this->lng->txt('registration_user_new_account_mail'),
                $this->ctrl->getLinkTargetByClass(
                    [
                        ilAdministrationGUI::class,
                        ilObjUserFolderGUI::class
                    ],
                    'newAccountMail'
                )
            );
            $this->ctrl->setParameterByClass(ilObjUserFolderGUI::class, 'ref_id', $_GET['ref_id']);
        }

        $form = $this->initForm();
        $this->initFormValues($form);
        $this->tpl->setContent($form->getHTML());
    }

    public function save() : bool
    {
        $this->checkAccess('write');

        $form = $this->initForm();
        $res = $form->checkInput();
        $this->registration_settings->setRegistrationType((int) $form->getInput('reg_type'));
        $this->registration_settings->setPasswordGenerationStatus((bool) $form->getInput('reg_pwd'));
        $this->registration_settings->setApproveRecipientLogins($form->getInput('reg_approver'));
        $this->registration_settings->setRoleType((int) $form->getInput('reg_role_type'));
        $this->registration_settings->setAccessLimitation((bool) $form->getInput('reg_access_limitation'));
        $this->registration_settings->setAllowedDomains((string) $form->getInput('reg_allowed_domains'));

        $allow_codes = false;
        $reg_type = (int) $form->getInput('reg_type');
        if (in_array($reg_type, [
            ilRegistrationSettings::IL_REG_DIRECT,
            ilRegistrationSettings::IL_REG_APPROVE,
            ilRegistrationSettings::IL_REG_ACTIVATION
        ], true)) {
            $allow_codes = (bool) $form->getInput('reg_codes_' . $reg_type);
        }
        $this->registration_settings->setAllowCodes($allow_codes);

        $hash_life_time = $form->getInput('reg_hash_life_time');
        if (!preg_match('/^([0]|([1-9][0-9]*))([\.,][0-9]+)?$/', (string) $hash_life_time)) {
            $this->registration_settings->setRegistrationHashLifetime(ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE);
        } else {
            $this->registration_settings->setRegistrationHashLifetime(max(
                (int) $hash_life_time,
                ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE
            ));
        }

        if ($error_code = $this->registration_settings->validate()) {
            switch ($error_code) {
                case ilRegistrationSettings::ERR_UNKNOWN_RCP:
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('reg_unknown_recipients') . ' ' . $this->registration_settings->getUnknown());
                    $this->view();
                    return false;

                case ilRegistrationSettings::ERR_MISSING_RCP:
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('reg_approve_needs_recipient') . ' ' . $this->registration_settings->getUnknown());
                    $this->view();
                    return false;

            }
        }

        $this->registration_settings->save();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->view();
        return true;
    }

    protected function initRolesForm() : ilPropertyFormGUI
    {
        $role_form = new ilPropertyFormGUI();
        $role_form->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $role_form->setTitle($this->lng->txt('reg_selectable_roles'));

        $roles = new ilCheckboxGroupInputGUI($this->lng->txt('reg_available_roles'), 'roles');
        $allowed_roles = [];
        foreach ($this->rbacreview->getGlobalRoles() as $role) {
            if ($role === SYSTEM_ROLE_ID || $role === ANONYMOUS_ROLE_ID) {
                continue;
            }
            $role_option = new ilCheckboxOption(ilObjRole::_lookupTitle($role));
            $role_option->setValue((string) $role);
            $roles->addOption($role_option);
            $allowed_roles[$role] = ilObjRole::_lookupAllowRegister($role);
        }

        $roles->setUseValuesAsKeys(true);
        $roles->setValue($allowed_roles);
        $role_form->addItem($roles);

        if ($this->checkAccessBool('write')) {
            $role_form->addCommandButton("updateRoles", $this->lng->txt("save"));
        }
        $role_form->addCommandButton("view", $this->lng->txt("cancel"));
        return $role_form;
    }

    public function editRoles(?ilPropertyFormGUI $form = null) : void
    {
        $this->checkAccess('write');
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("registration_settings"),
            $this->ctrl->getLinkTarget($this, "view")
        );
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initRolesForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function updateRoles() : bool
    {
        $this->checkAccess('write');
        $form = $this->initRolesForm();
        if ($form->checkInput()) {
            $roles = (array) $form->getInput('roles');
            if (count($roles) < 1) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_last_role_for_registration'));
                $this->editRoles();
                return false;
            }
            foreach ($this->rbacreview->getGlobalRoles() as $role) {
                if ($role_obj = ilObjectFactory::getInstanceByObjId($role, false)) {
                    $role_obj->setAllowRegister(
                        (int) $roles[$role] === 1
                    );
                    $role_obj->update();
                }
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->view();
        return true;
    }

    public function editEmailAssignments(ilPropertyFormGUI $form = null) : void
    {
        $this->checkAccess('write');
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("registration_settings"),
            $this->ctrl->getLinkTarget($this, "view")
        );

        $this->initRoleAssignments();
        $form = $form ?? $this->initEmailAssignmentForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function initEmailAssignmentForm() : ilPropertyFormGUI
    {
        $role_assignment_form = new ilPropertyFormGUI();
        $role_assignment_form->setFormAction($this->ctrl->getFormAction($this));
        $role_assignment_form->setTitle($this->lng->txt('reg_email_role_assignment'));

        $global_roles = ["" => $this->lng->txt("links_select_one")];
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id === ANONYMOUS_ROLE_ID) {
                continue;
            }

            $global_roles[$role_id] = ilObjRole::_lookupTitle($role_id);
            $role_assignments = new ilCheckboxInputGUI(ilObjRole::_lookupTitle($role_id), "role_assigned_$role_id");

            $domains = $this->assignments_obj->getDomainsByRole($role_id);

            $domain = new ilTextInputGUI($this->lng->txt('reg_domain'), "domain_$role_id");
            $domain->setMulti(true);
            $domain->setValidationRegexp("/^@.*\.[a-zA-Z]{1,4}$/");
            if (!empty($domains)) {
                $domain->setValue($domains[0]);
                $domain->setMultiValues($domains);
                $role_assignments->setChecked(true);
            }

            $role_assignments->addSubItem($domain);
            $role_assignment_form->addItem($role_assignments);
        }

        $default_role = new ilSelectInputGUI($this->lng->txt('reg_default'));
        $default_role->setPostVar("default_role");
        $default_role->setOptions($global_roles);
        $default_role->setValue($this->assignments_obj->getDefaultRole());
        $default_role->setRequired(true);
        $role_assignment_form->addItem($default_role);

        $role_assignment_form->addCommandButton("saveAssignment", $this->lng->txt("save"));
        $role_assignment_form->addCommandButton("view", $this->lng->txt("cancel"));

        return $role_assignment_form;
    }

    public function editRoleAccessLimitations(ilPropertyFormGUI $form = null) : void
    {
        global $DIC;

        $this->checkAccess('write');
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("registration_settings"),
            $this->ctrl->getLinkTarget($this, "view")
        );
        $this->initRoleAccessLimitations();
        if (null === $form) {
            $form = $this->initRoleAccessForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initRoleAccessForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('reg_role_access_limitations'));
        foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
            $role_access = new ilRadioGroupInputGUI($role['title'], "role_access_" . $role['id']);

            $op_unlimited = new ilRadioOption($this->lng->txt('reg_access_limitation_mode_unlimited'), "unlimited");

            $op_absolute = new ilRadioOption($this->lng->txt('reg_access_limitation_mode_absolute'), "absolute");
            $absolute_date = new ilDateTime(
                date("d.m.Y", $this->access_limitations_obj->getAbsolute($role['id'])),
                IL_CAL_DATE
            );
            $date = new ilDateTimeInputGUI("", "absolute_date_" . $role['id']);
            $date->setDate($absolute_date);
            $op_absolute->addSubItem($date);

            $op_relative = new ilRadioOption($this->lng->txt('reg_access_limitation_mode_relative'), "relative");
            $duration = new ilDurationInputGUI("", "duration_" . $role['id']);
            $duration->setShowMinutes(false);
            $duration->setShowHours(false);
            $duration->setShowDays(true);
            $duration->setShowMonths(true);
            $duration->setDays($this->access_limitations_obj->getRelative($role['id'], 'd'));
            $duration->setMonths($this->access_limitations_obj->getRelative($role['id'], 'm'));
            $op_relative->addSubItem($duration);

            $role_access->addOption($op_unlimited);
            $role_access->addOption($op_absolute);
            $role_access->addOption($op_relative);
            $role_access->setValue($this->access_limitations_obj->getMode($role['id']));
            $form->addItem($role_access);
        }

        $form->addCommandButton("saveRoleAccessLimitations", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));

        return $form;
    }

    public function saveAssignment() : bool
    {
        $this->checkAccess('write');
        $this->initRoleAssignments();
        $form = $this->initEmailAssignmentForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editEmailAssignments($form);
            return false;
        }
        $this->assignments_obj->deleteAll();

        $counter = 0;
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id === ANONYMOUS_ROLE_ID) {
                continue;
            }
            $domain_input = $form->getInput("domain_$role_id");
            $role_assigned_input = $form->getInput("role_assigned_$role_id");
            if (!empty($role_assigned_input)) {
                foreach ($domain_input as $domain) {
                    if (!empty($domain)) {
                        $this->assignments_obj->setDomain($counter, $domain);
                        $this->assignments_obj->setRole($counter, $role_id);
                        $counter++;
                    }
                }
            }
        }
        $default_role = $form->getInput("default_role");
        $this->assignments_obj->setDefaultRole((int) $default_role);
        $this->assignments_obj->save();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->view();
        return true;
    }

    public function saveRoleAccessLimitations() : bool
    {
        $this->checkAccess('write');
        $this->initRoleAccessLimitations();

        $form = $this->initRoleAccessForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editRoleAccessLimitations($form);
            return false;
        }

        $this->access_limitations_obj->resetAccessLimitations();
        foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
            $this->access_limitations_obj->setMode($form->getInput("role_access_" . $role['id']), $role['id']);
            $this->access_limitations_obj->setAbsolute($form->getInput("absolute_date_" . $role['id']), $role['id']);
            $this->access_limitations_obj->setRelative($form->getInput("duration_" . $role['id']), $role['id']);
        }

        if ($err = $this->access_limitations_obj->validate()) {
            switch ($err) {
                case ilRegistrationRoleAccessLimitations::IL_REG_ACCESS_LIMITATION_MISSING_MODE:
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('reg_access_limitation_missing_mode'));
                    break;

                case ilRegistrationRoleAccessLimitations::IL_REG_ACCESS_LIMITATION_OUT_OF_DATE:
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('reg_access_limitation_out_of_date'));
                    break;
            }
            $this->editRoleAccessLimitations();
            return false;
        }

        $this->access_limitations_obj->save();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->view();
        return true;
    }

    /**
     * @param string[] $roles
     * @param string $url
     * @return string
     */
    private function parseRoleList(array $roles, string $url) : string
    {
        $tpl = new ilTemplate('tpl.registration_roles.html', true, true, 'Services/Registration');

        $tpl->setVariable("EDIT", $this->lng->txt("edit"));
        $tpl->setVariable("LINK_EDIT", $url);

        if (is_array($roles) && count($roles)) {
            foreach ($roles as $role) {
                $tpl->setCurrentBlock("list_item");
                $tpl->setVariable("LIST_ITEM_ITEM", $role);
                $tpl->parseCurrentBlock();
            }
        } else {
            $tpl->setVariable("NONE", $this->lng->txt('none'));
        }
        return $tpl->get();
    }

    /***
     * @return string[]
     */
    private function prepareRoleList() : array
    {
        $all = [];
        foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
            $all[] = $role['title'];
        }

        return $all;
    }

    /**
     * @return string[]
     */
    private function prepareAutomaticRoleList() : array
    {
        $this->initRoleAssignments();
        $all = [];
        foreach ($this->assignments_obj->getAssignments() as $assignment) {
            if ($assignment['domain'] !== '' && $assignment['role']) {
                $all[] = $assignment['domain'] . ' -> ' . ilObjRole::_lookupTitle((int) $assignment['role']);
            }
        }

        if ((string) $this->assignments_obj->getDefaultRole() !== '') {
            $all[] = $this->lng->txt('reg_default') . ' -> ' . ilObjRole::_lookupTitle($this->assignments_obj->getDefaultRole());
        }

        return $all;
    }

    private function prepareAccessLimitationRoleList() : array
    {
        $this->initRoleAccessLimitations();
        $all = [];
        foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
            switch ($this->access_limitations_obj->getMode((int) $role['id'])) {
                case 'absolute':
                    $txt_access_value = $this->lng->txt('reg_access_limitation_limited_until');
                    $txt_access_value .= " " . ilDatePresentation::formatDate(
                        new ilDateTime($this->access_limitations_obj->getAbsolute((int) $role['id']), IL_CAL_UNIX)
                    );
                    break;

                case 'relative':
                    $months = $this->access_limitations_obj->getRelative($role['id'], 'm');
                    $days = $this->access_limitations_obj->getRelative($role['id'], 'd');

                    $txt_access_value = $this->lng->txt('reg_access_limitation_limited_time') . " ";

                    if ($months) {
                        if ($days) {
                            $txt_access_value .= ", ";
                        } else {
                            $txt_access_value .= " " . $this->lng->txt('and') . " ";
                        }
                    } elseif ($days) {
                        $txt_access_value .= " " . $this->lng->txt('and') . " ";
                    }

                    if ($months) {
                        $txt_access_value .= $months . " ";
                        $txt_access_value .= ($months === 1) ? $this->lng->txt('month') : $this->lng->txt('months');

                        if ($days) {
                            $txt_access_value .= " " . $this->lng->txt('and') . " ";
                        }
                    }

                    if ($days) {
                        $txt_access_value .= $days . " ";
                        $txt_access_value .= ($days === 1) ? $this->lng->txt('day') : $this->lng->txt('days');
                    }
                    break;

                default:
                    $txt_access_value = $this->lng->txt('reg_access_limitation_none');
                    break;
            }

            $all[] = $role['title'] . ' (' . $txt_access_value . ')';
        }

        return $all;
    }

    private function initRoleAssignments() : void
    {
        if (!$this->assignments_obj instanceof ilRegistrationRoleAssignments) {
            $this->assignments_obj = new ilRegistrationRoleAssignments();
        }
    }

    private function initRoleAccessLimitations() : void
    {
        if (!$this->access_limitations_obj instanceof ilRegistrationRoleAccessLimitations) {
            $this->access_limitations_obj = new ilRegistrationRoleAccessLimitations();
        }
    }

    public function listCodes() : void
    {
        $this->checkAccess("visible,read");
        $this->setSubTabs('registration_codes');
        if ($this->checkAccessBool("write")) {
            $this->toolbar->addButton(
                $this->lng->txt("registration_codes_add"),
                $this->ctrl->getLinkTarget($this, "addCodes")
            );
        }
        $ctab = new ilRegistrationCodesTableGUI($this, "listCodes");
        $this->tpl->setContent($ctab->getHTML());
    }

    public function initAddCodesForm() : ilPropertyFormGUI
    {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'createCodes'));
        $this->form_gui->setTitle($this->lng->txt('registration_codes_edit_header'));

        $count = new ilNumberInputGUI($this->lng->txt('registration_codes_number'), 'reg_codes_number');
        $count->setSize(4);
        $count->setMaxLength(4);
        $count->setMinValue(1);
        $count->setMaxValue(1000);
        $count->setRequired(true);
        $this->form_gui->addItem($count);

        // type
        $code_type = new ilCheckboxGroupInputGUI($this->lng->txt('registration_codes_type'), 'code_type');
        $code_type->setRequired(true);

        $code_type->addOption(
            new ilCheckboxOption(
                $this->lng->txt('registration_codes_type_reg'),
                (string) self::CODE_TYPE_REGISTRATION,
                $this->lng->txt('registration_codes_type_reg_info')
            )
        );
        $code_type->addOption(
            new ilCheckboxOption(
                $this->lng->txt('registration_codes_type_ext'),
                (string) self::CODE_TYPE_EXTENSION,
                $this->lng->txt('registration_codes_type_ext_info')
            )
        );
        $this->form_gui->addItem($code_type);

        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('registration_codes_roles_title'));
        $this->form_gui->addItem($sec);

        $options = ["" => $this->lng->txt('registration_codes_no_assigned_role')];
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            if (!in_array($role_id, [SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID], true)) {
                $options[$role_id] = ilObject::_lookupTitle($role_id);
            }
        }
        $roles = new ilSelectInputGUI($this->lng->txt("registration_codes_roles"), "reg_codes_role");
        $roles->setInfo($this->lng->txt("registration_codes_override_info"));
        $roles->setOptions($options);
        $this->form_gui->addItem($roles);

        $local = new ilTextInputGUI($this->lng->txt("registration_codes_roles_local"), "reg_codes_local");
        $local->setMulti(true);
        $local->setDataSource($this->ctrl->getLinkTarget($this, "getLocalRoleAutoComplete", "", true));
        $this->form_gui->addItem($local);

        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('reg_access_limitations'));
        $this->form_gui->addItem($sec);

        $limit = new ilRadioGroupInputGUI($this->lng->txt("reg_access_limitation_mode"), "reg_limit");
        $limit->setInfo($this->lng->txt("registration_codes_override_info"));
        $this->form_gui->addItem($limit);

        $opt = new ilRadioOption($this->lng->txt("registration_codes_roles_limitation_none"), "none");
        $limit->addOption($opt);

        $opt = new ilRadioOption($this->lng->txt("reg_access_limitation_none"), "unlimited");
        $limit->addOption($opt);

        $opt = new ilRadioOption($this->lng->txt("reg_access_limitation_mode_absolute"), "absolute");
        $limit->addOption($opt);

        $dt = new ilDateTimeInputGUI($this->lng->txt("reg_access_limitation_mode_absolute_target"), "abs_date");
        $dt->setRequired(true);
        $opt->addSubItem($dt);

        $opt = new ilRadioOption($this->lng->txt("reg_access_limitation_mode_relative"), "relative");
        $limit->addOption($opt);

        $dur = new ilDurationInputGUI($this->lng->txt("reg_access_limitation_mode_relative_target"), "rel_date");
        $dur->setRequired(true);
        $dur->setShowMonths(true);
        $dur->setShowDays(true);
        $dur->setShowHours(false);
        $dur->setShowMinutes(false);
        $opt->addSubItem($dur);

        $this->form_gui->addCommandButton('createCodes', $this->lng->txt('create'));
        $this->form_gui->addCommandButton('listCodes', $this->lng->txt('cancel'));
        return $this->form_gui;
    }

    // see ilRoleAutoCompleteInputGUI
    public function getLocalRoleAutoComplete() : void
    {
        $q = $_REQUEST["term"];
        $list = ilRoleAutoComplete::getList($q);
        echo $list;
        exit;
    }

    public function addCodes() : void
    {
        $this->checkAccess('write');
        $this->setSubTabs('registration_codes');
        $this->initAddCodesForm();

        // default
        $limit = $this->form_gui->getItemByPostVar("reg_limit");
        $limit->setValue("none");
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    public function createCodes() : void
    {
        $this->checkAccess('write');
        $this->setSubTabs('registration_codes');

        $this->initAddCodesForm();
        $valid = $this->form_gui->checkInput();
        if ($valid) {
            $number = $this->form_gui->getInput('reg_codes_number');
            $role = $this->form_gui->getInput('reg_codes_role');
            $local = $this->form_gui->getInput("reg_codes_local");

            if (is_array($local)) {
                $role_ids = [];
                foreach (array_unique($local) as $item) {
                    if (trim($item)) {
                        $role_id = $this->rbacreview->roleExists($item);
                        if ($role_id) {
                            $role_ids[] = $role_id;
                        }
                    }
                }
                if (count($role_ids)) {
                    $local = $role_ids;
                }
            }

            $date = null;
            $limit = $this->form_gui->getInput("reg_limit");
            switch ($limit) {
                case "absolute":
                    $date_input = $this->form_gui->getItemByPostVar("abs_date");
                    $date = $date_input->getDate()->get(IL_CAL_DATE);
                    if ($date < date("Y-m-d")) {
                        $date_input->setAlert($this->lng->txt("form_msg_wrong_date"));
                        $valid = false;
                    }
                    break;

                case "relative":
                    $date = $this->form_gui->getInput("rel_date");
                    if (!array_sum($date)) {
                        $valid = false;
                    } else {
                        $date = [
                            "d" => $date["dd"],
                            "m" => $date["MM"] % 12,
                            "y" => floor($date["MM"] / 12)
                        ];
                    }
                    break;

                case "none":
                    $limit = null;
                    break;
            }
        }

        if ($valid) {
            $stamp = time();
            for ($loop = 1; $loop <= $number; $loop++) {
                $code_types = (array) $this->form_gui->getInput('code_type');

                ilRegistrationCode::create(
                    $role,
                    $stamp,
                    $local,
                    $limit,
                    $date,
                    in_array(self::CODE_TYPE_REGISTRATION, $code_types) ? true : false,
                    in_array(self::CODE_TYPE_EXTENSION, $code_types) ? true : false
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, "listCodes");
        } else {
            $this->form_gui->setValuesByPost();
            $this->tpl->setContent($this->form_gui->getHTML());
        }
    }

    public function deleteCodes() : void
    {
        $this->checkAccess("write");

        $ids = [];
        if ($this->http->wrapper()->post()->has('id')) {
            $ids = $this->http->wrapper()->post()->retrieve(
                'id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        ilRegistrationCode::deleteCodes($ids);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('info_deleted'), true);
        $this->ctrl->redirect($this, "listCodes");
    }

    public function deleteConfirmation() : void
    {
        $this->checkAccess("write");

        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ids = [];
        if ($this->http->wrapper()->post()->has('id')) {
            $ids = $this->http->wrapper()->post()->retrieve(
                'id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (!count($ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_select_one'), true);
            $this->ctrl->redirect($this, 'listCodes');
        }
        $this->setSubTabs('registration_codes');

        $gui = new ilConfirmationGUI();
        $gui->setHeaderText($this->lng->txt("info_delete_sure"));
        $gui->setCancel($this->lng->txt("cancel"), "listCodes");
        $gui->setConfirm($this->lng->txt("confirm"), "deleteCodes");
        $gui->setFormAction($this->ctrl->getFormAction($this, "deleteCodes"));

        $data = ilRegistrationCode::loadCodesByIds($ids);
        foreach ($data as $code) {
            $gui->addItem("id[]", $code["code_id"], $code["code"]);
        }
        $this->tpl->setContent($gui->getHTML());
    }

    public function resetCodesFilter() : void
    {
        $utab = new ilRegistrationCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->resetFilter();

        $this->listCodes();
    }

    public function applyCodesFilter() : void
    {
        $utab = new ilRegistrationCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->writeFilterToSession();

        $this->listCodes();
    }

    public function exportCodes() : void
    {
        $this->checkAccess('read');
        $utab = new ilRegistrationCodesTableGUI($this, "listCodes");

        $codes = ilRegistrationCode::getCodesForExport(
            $utab->filter["code"],
            $utab->filter["role"],
            $utab->filter["generated"],
            $utab->filter["alimit"]
        );

        if (count($codes)) {
            ilUtil::deliverData(
                implode("\r\n", $codes),
                "ilias_registration_codes_" . date("d-m-Y") . ".txt",
                "text/plain"
            );
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("registration_export_codes_no_data"));
            $this->listCodes();
        }
    }
}
