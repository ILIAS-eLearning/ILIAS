<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once './Services/Registration/classes/class.ilRegistrationSettings.php';

/**
* Class ilRegistrationSettingsGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilRegistrationSettingsGUI:
*
* @ingroup ServicesRegistration
*/
class ilRegistrationSettingsGUI
{
    const CODE_TYPE_REGISTRATION = 1;
    const CODE_TYPE_EXTENSION = 2;
    
    public $ctrl;
    public $tpl;
    public $ref_id;
    public $rbacsystem;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac();
        
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('user');

        $this->ref_id = (int) $_GET['ref_id'];

        $this->registration_settings = new ilRegistrationSettings();
    }

    public function executeCommand()
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
        return true;
    }

    /**
     * @param string $a_permission
     */
    protected function checkAccess($a_permission)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if (!$this->checkAccessBool($a_permission)) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
        }
    }

    /**
     * @param string $a_permission
     * @return bool
     */
    protected function checkAccessBool($a_permission)
    {
        global $DIC;

        $access = $DIC->access();

        return $access->checkAccess($a_permission, '', $this->ref_id);
    }
    
    /**
    * set sub tabs
    * @param	string	$activeTab
    */
    public function setSubTabs($activeTab = 'registration_settings')
    {
        global $DIC;

        $ilTabs = $DIC->tabs();

        $ilTabs->addSubTab(
            "registration_settings",
            $this->lng->txt("registration_tab_settings"),
            $this->ctrl->getLinkTarget($this, 'view')
        );

        $ilTabs->addSubTab(
            "registration_codes",
            $this->lng->txt("registration_tab_codes"),
            $this->ctrl->getLinkTarget($this, 'listCodes')
        );
            
        $ilTabs->activateSubTab($activeTab);
    }
    
    public function initForm()
    {
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $this->form_gui->setTitle($this->lng->txt('reg_settings_header'));

        $reg_type = new ilRadioGroupInputGUI($this->lng->txt('reg_type'), 'reg_type');
        $reg_type->addOption(new ilRadioOption($this->lng->txt('reg_disabled'), IL_REG_DISABLED));
        $option = new ilRadioOption($this->lng->txt('reg_direct'), IL_REG_DIRECT);
        $option->setInfo($this->lng->txt('reg_direct_info'));
        $cd = new ilCheckboxInputGUI($this->lng->txt('reg_allow_codes'), 'reg_codes_' . IL_REG_DIRECT);
        $cd->setInfo($this->lng->txt('reg_allow_codes_info'));
        $option->addSubItem($cd);
        $reg_type->addOption($option);
        $option = new ilRadioOption($this->lng->txt('reg_approve'), IL_REG_APPROVE);
        $option->setInfo($this->lng->txt('reg_approve_info'));
        $cd = new ilCheckboxInputGUI($this->lng->txt('reg_allow_codes'), 'reg_codes_' . IL_REG_APPROVE);
        $cd->setInfo($this->lng->txt('reg_allow_codes_info'));
        $option->addSubItem($cd);
        $reg_type->addOption($option);
        $option = new ilRadioOption($this->lng->txt('reg_type_confirmation'), IL_REG_ACTIVATION);
        $option->setInfo($this->lng->txt('reg_type_confirmation_info'));
        $lt = new ilNumberInputGUI($this->lng->txt('reg_confirmation_hash_life_time'), 'reg_hash_life_time');
        $lt->setSize(6); // #8511
        $lt->setMaxLength(6);
        $lt->setMinValue(ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE);
        $lt->setRequired(true);
        $lt->setInfo($this->lng->txt('reg_confirmation_hash_life_time_info'));
        $lt->setSuffix($this->lng->txt('seconds'));
        $option->addSubItem($lt);
        $cd = new ilCheckboxInputGUI($this->lng->txt('reg_allow_codes'), 'reg_codes_' . IL_REG_ACTIVATION);
        $cd->setInfo($this->lng->txt('reg_allow_codes_info'));
        $option->addSubItem($cd);
        $reg_type->addOption($option);
        $option = new ilRadioOption($this->lng->txt('registration_reg_type_codes'), IL_REG_CODES);
        $option->setInfo($this->lng->txt('registration_reg_type_codes_info'));
        $reg_type->addOption($option);
        $this->form_gui->addItem($reg_type);

        $pwd_gen = new ilCheckboxInputGUI($this->lng->txt('passwd_generation'), 'reg_pwd');
        $pwd_gen->setValue(1);
        $pwd_gen->setInfo($this->lng->txt('reg_info_pwd'));
        $this->form_gui->addItem($pwd_gen);

        require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
        $cap = new ilCheckboxInputGUI($this->lng->txt('adm_captcha_anonymous_short'), 'activate_captcha_anonym');
        $cap->setInfo($this->lng->txt('adm_captcha_anonymous_reg'));
        $cap->setValue(1);
        if (!ilCaptchaUtil::checkFreetype()) {
            $cap->setAlert(ilCaptchaUtil::getPreconditionsMessage());
        }
        $this->form_gui->addItem($cap);
        
        $approver = new ilTextInputGUI($this->lng->txt('reg_notification'), 'reg_approver');
        $approver->setSize(32);
        $approver->setMaxLength(50);
        $approver->setInfo($this->lng->txt('reg_notification_info'));
        $this->form_gui->addItem($approver);

        $roles = new ilRadioGroupInputGUI($this->lng->txt('reg_role_assignment'), 'reg_role_type');
        $option = new ilRadioOption($this->lng->txt('reg_fixed'), IL_REG_ROLES_FIXED);
        $list = new ilCustomInputGUI($this->lng->txt('reg_available_roles'));
        $edit = $this->ctrl->getLinkTarget($this, 'editRoles');
        $list->setHtml($this->__parseRoleList($this->__prepareRoleList(), $edit));
        $option->addSubItem($list);
        $roles->addOption($option);
        $option = new ilRadioOption($this->lng->txt('reg_email'), IL_REG_ROLES_EMAIL);
        $list = new ilCustomInputGUI($this->lng->txt('reg_available_roles'));
        $edit = $this->ctrl->getLinkTarget($this, 'editEmailAssignments');
        $list->setHtml($this->__parseRoleList($this->__prepareAutomaticRoleList(), $edit));
        $option->addSubItem($list);
        $roles->addOption($option);
        $roles->setInfo($this->lng->txt('registration_codes_override_global_info'));
        $this->form_gui->addItem($roles);

        $limit = new ilCheckboxInputGUI($this->lng->txt('reg_access_limitations'), 'reg_access_limitation');
        $limit->setValue(1);
        $list = new ilCustomInputGUI($this->lng->txt('reg_available_roles'));
        $edit = $this->ctrl->getLinkTarget($this, 'editRoleAccessLimitations');
        $list->setHtml($this->__parseRoleList($this->__prepareAccessLimitationRoleList(), $edit));
        $list->setInfo($this->lng->txt('registration_codes_override_global_info'));
        $limit->addSubItem($list);
        $this->form_gui->addItem($limit);
        
        $domains = new ilTextInputGUI($this->lng->txt('reg_allowed_domains'), 'reg_allowed_domains');
        $domains->setInfo($this->lng->txt('reg_allowed_domains_info'));
        $this->form_gui->addItem($domains);

        if ($this->rbacsystem->system()->checkAccess("write", $this->ref_id)) {
            $this->form_gui->addCommandButton('save', $this->lng->txt('save'));
        }
    }
    
    public function initFormValues()
    {
        if ($this->registration_settings->roleSelectionEnabled()) {
            $role_type = IL_REG_ROLES_FIXED;
        } elseif ($this->registration_settings->automaticRoleAssignmentEnabled()) {
            $role_type = IL_REG_ROLES_EMAIL;
        }

        require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
        $values = array(
            'reg_type' => $this->registration_settings->getRegistrationType(),
            'reg_hash_life_time' => (int) $this->registration_settings->getRegistrationHashLifetime(),
            'reg_pwd' => $this->registration_settings->passwordGenerationEnabled(),
            'reg_approver' => $this->registration_settings->getApproveRecipientLogins(),
            'reg_role_type' => $role_type,
            'reg_access_limitation' => $this->registration_settings->getAccessLimitation(),
            'reg_allowed_domains' => implode(';', $this->registration_settings->getAllowedDomains()),
            'activate_captcha_anonym' => ilCaptchaUtil::isActiveForRegistration()
            );

        $allow_codes = $this->registration_settings->getAllowCodes();
        $reg_type = $this->registration_settings->getRegistrationType();
        if ($allow_codes && in_array($reg_type, array(IL_REG_DIRECT, IL_REG_APPROVE, IL_REG_ACTIVATION))) {
            $values['reg_codes_' . $reg_type] = true;
        }

        $this->form_gui->setValuesByArray($values);
    }
    
    public function view()
    {
        global $DIC;
        if (!$DIC->rbac()->system()->checkAccess("visible,read", $this->ref_id)) {
            $DIC['ilErr']->raiseError($this->lng->txt("msg_no_perm_read"), $DIC['ilErr']->MESSAGE);
        }
        
        $this->setSubTabs();
        
        // edit new accout mail
        $this->ctrl->setParameterByClass("ilobjuserfoldergui", "ref_id", USER_FOLDER_ID);
        if ($DIC->rbac()->system()->checkAccess("write", $this->ref_id)) {
            $DIC->toolbar()->addButton($this->lng->txt('registration_user_new_account_mail'), $this->ctrl->getLinkTargetByClass(array(
                "iladministrationgui",
                "ilobjuserfoldergui"
            ), "newAccountMail"));
            $this->ctrl->setParameterByClass("ilobjuserfoldergui", "ref_id", $_GET["ref_id"]);
        }

        $this->initForm();
        $this->initFormValues();
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    public function save()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $this->registration_settings->setRegistrationType((int) $_POST['reg_type']);
        $this->registration_settings->setPasswordGenerationStatus((int) $_POST['reg_pwd']);
        $this->registration_settings->setApproveRecipientLogins(ilUtil::stripSlashes($_POST['reg_approver']));
        $this->registration_settings->setRoleType((int) $_POST['reg_role_type']);
        $this->registration_settings->setAccessLimitation((int) $_POST['reg_access_limitation']);
        $this->registration_settings->setAllowedDomains($_POST['reg_allowed_domains']);
        
        $allow_codes = false;
        if (in_array((int) $_POST['reg_type'], array(IL_REG_DIRECT, IL_REG_APPROVE, IL_REG_ACTIVATION))) {
            $allow_codes = (bool) $_POST['reg_codes_' . (int) $_POST['reg_type']];
        }
        $this->registration_settings->setAllowCodes($allow_codes);
        
        if (!preg_match('/^([0]|([1-9][0-9]*))([\.,][0-9][0-9]*)?$/', (int) $_POST['reg_hash_life_time'])) {
            $this->registration_settings->setRegistrationHashLifetime(ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE);
        } else {
            $this->registration_settings->setRegistrationHashLifetime(max((int) $_POST['reg_hash_life_time'], ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE));
        }

        if ($error_code = $this->registration_settings->validate()) {
            switch ($error_code) {
                case ilRegistrationSettings::ERR_UNKNOWN_RCP:
            
                    ilUtil::sendFailure($this->lng->txt('reg_unknown_recipients') . ' ' . $this->registration_settings->getUnknown());
                    $this->view();
                    return false;

                case ilRegistrationSettings::ERR_MISSING_RCP:
                    
                    ilUtil::sendFailure($this->lng->txt('reg_approve_needs_recipient') . ' ' . $this->registration_settings->getUnknown());
                    $this->view();
                    return false;
                                    
            }
        }

        require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
        ilCaptchaUtil::setActiveForRegistration((bool) $_POST['activate_captcha_anonym']);
        
        $this->registration_settings->save();
        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $this->view();

        return true;
    }

    public function editRoles()
    {

        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();
        $rbacreview = $DIC->rbac()->review();
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("registration_settings"),
            $ilCtrl->getLinkTarget($this, "view")
        );

        $role_form = new ilPropertyFormGUI();
        $role_form->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $role_form->setTitle($this->lng->txt('reg_selectable_roles'));

        $roles = new \ilCheckboxGroupInputGUI($this->lng->txt('reg_available_roles'), 'roles');
        $allowed_roles = array();
        foreach ($rbacreview->getGlobalRoles() as $role) {
            if ($role == SYSTEM_ROLE_ID or $role == ANONYMOUS_ROLE_ID) {
                continue;
            }
            $role_option = new \ilCheckboxOption(ilObjRole::_lookupTitle($role));
            $role_option->setValue($role);
            $roles->addOption($role_option);

            $allowed_roles[$role] = ilObjRole::_lookupAllowRegister($role);
        }

        $roles->setUseValuesAsKeys(true);
        $roles->setValue($allowed_roles);
        $role_form->addItem($roles);


        if ($this->rbacsystem->system()->checkAccess("write", $this->ref_id)) {
            $role_form->addCommandButton("updateRoles", $this->lng->txt("save"));
        }
        $role_form->addCommandButton("view", $this->lng->txt("cancel"));


        $this->tpl->setContent($role_form->getHTML());
    }

    public function updateRoles()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $rbacreview = $DIC->rbac()->review();
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }
        // Minimum one role
        if (count($_POST['roles']) < 1) {
            ilUtil::sendFailure($this->lng->txt('msg_last_role_for_registration'));
            $this->editRoles();
            return false;
        }
        // update allow register
        foreach ($rbacreview->getGlobalRoles() as $role) {
            if ($role_obj = ilObjectFactory::getInstanceByObjId($role, false)) {
                $role_obj->setAllowRegister($_POST['roles'][$role] ? 1 : 0);
                $role_obj->update();
            }
        }
        
        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $this->view();

        return true;
    }

    public function editEmailAssignments(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("registration_settings"),
            $ilCtrl->getLinkTarget($this, "view")
        );

        $this->__initRoleAssignments();


        $form = (empty($form)) ? $this->initEmailAssignmentForm() : $form;
        $this->tpl->setContent($form->getHTML());
    }

    public function initEmailAssignmentForm() : ilPropertyFormGUI
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();

        $role_assignment_form = new ilPropertyFormGUI();
        $role_assignment_form->setFormAction($this->ctrl->getFormAction($this));
        $role_assignment_form->setTitle($this->lng->txt('reg_email_role_assignment'));

        $global_roles = ["" => $this->lng->txt("links_select_one")];
        foreach ($rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id == ANONYMOUS_ROLE_ID) {
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
    
    public function editRoleAccessLimitations(ilPropertyFormGUI $form = null)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC->tabs();
        $ilCtrl = $DIC->ctrl();
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->lng->txt("registration_settings"),
            $ilCtrl->getLinkTarget($this, "view")
        );

        $this->__initRoleAccessLimitations();

        $form = $this->initRoleAccessForm();

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
            
            $op_absolute   = new ilRadioOption($this->lng->txt('reg_access_limitation_mode_absolute'), "absolute");
            $absolute_date = new ilDateTime(date("d.m.Y",$this->access_limitations_obj->getAbsolute($role['id'])), IL_CAL_DATE);
            $date          = new ilDateTimeInputGUI("", "absolute_date_" . $role['id']);
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

    public function saveAssignment()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $rbacreview = $DIC->rbac()->review();
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $this->__initRoleAssignments();

        $form = $this->initEmailAssignmentForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editEmailAssignments($form);
            return false;
        }

        $this->assignments_obj->deleteAll();

        $counter = 0;
        foreach ($rbacreview->getGlobalRoles() as $role_id) {
            if ($role_id == ANONYMOUS_ROLE_ID) {
                continue;
            }

            $domain_input = $form->getInput("domain_$role_id");
            $role_assigned_input = $form->getInput("role_assigned_$role_id");


            if (!empty($role_assigned_input)) {
                foreach ($domain_input as $domain) {
                    if (!empty($domain)) {
                        $this->assignments_obj->setDomain($counter, ilUtil::stripSlashes($domain));
                        $this->assignments_obj->setRole($counter, ilUtil::stripSlashes($role_id));
                        $counter++;
                    }
                }
            }
        }

        $default_role = $form->getInput("default_role");
        $this->assignments_obj->setDefaultRole((int) $default_role);

        $this->assignments_obj->save();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->view();
        return true;
    }
    
    public function saveRoleAccessLimitations()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        
        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $this->__initRoleAccessLimitations();
        
        $form = $this->initRoleAccessForm();
        if(!$form->checkInput()) {
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
                case IL_REG_ACCESS_LIMITATION_MISSING_MODE:
                    ilUtil::sendFailure($this->lng->txt('reg_access_limitation_missing_mode'));
                    break;
                    
                case IL_REG_ACCESS_LIMITATION_OUT_OF_DATE:
                    ilUtil::sendFailure($this->lng->txt('reg_access_limitation_out_of_date'));
                    break;
            }
            $this->editRoleAccessLimitations();
            return false;
        }


        $this->access_limitations_obj->save();
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->view();
        return true;
    }

    public function __parseRoleList($roles, $url)
    {
        $tpl = new ilTemplate('tpl.registration_roles.html', true, true, 'Services/Registration');
        
        $tpl->setVariable("EDIT", $this->lng->txt("edit"));
        $tpl->setVariable("LINK_EDIT", $url);
        
        if (is_array($roles) && sizeof($roles)) {
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

    public function __prepareRoleList()
    {
        
        $all = array();
        foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
            $all[] = $role['title'];
        }
        return $all;
    }

    public function __prepareAutomaticRoleList()
    {
        $this->__initRoleAssignments();
        
        $all = array();
        foreach ($this->assignments_obj->getAssignments() as $assignment) {
            if (strlen($assignment['domain']) and $assignment['role']) {
                $all[] = $assignment['domain'] . ' -> ' . ilObjRole::_lookupTitle($assignment['role']);
            }
        }

        if (strlen($this->assignments_obj->getDefaultRole())) {
            $all[] = $this->lng->txt('reg_default') . ' -> ' . ilObjRole::_lookupTitle($this->assignments_obj->getDefaultRole());
        }

        return $all;
    }
    
    public function __prepareAccessLimitationRoleList()
    {
        global $DIC;

        $this->__initRoleAccessLimitations();
        

        $all = array();
        foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
            switch ($this->access_limitations_obj->getMode($role['id'])) {
                case 'absolute':
                    $txt_access_value = $this->lng->txt('reg_access_limitation_limited_until');
                    $txt_access_value .= " " . ilDatePresentation::formatDate(new ilDateTime($this->access_limitations_obj->getAbsolute($role['id']), IL_CAL_UNIX));
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
                        $txt_access_value .= ($months == 1) ? $this->lng->txt('month') : $this->lng->txt('months');
                        
                        if ($days) {
                            $txt_access_value .= " " . $this->lng->txt('and') . " ";
                        }
                    }
                    
                    if ($days) {
                        $txt_access_value .= $days . " ";
                        $txt_access_value .= ($days == 1) ? $this->lng->txt('day') : $this->lng->txt('days');
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

    public function __initRoleAssignments()
    {
        if (is_object($this->assignments_obj)) {
            return true;
        }

        $this->assignments_obj = new ilRegistrationRoleAssignments();
    }
    
    public function __initRoleAccessLimitations()
    {
        if (is_object($this->access_limitations_obj)) {
            return true;
        }

        $this->access_limitations_obj = new ilRegistrationRoleAccessLimitations();
    }
    
    public function listCodes()
    {
        global $DIC;
        $this->checkAccess("visible,read");

        $this->setSubTabs('registration_codes');

        if ($this->checkAccessBool("write")) {
            $DIC->toolbar()->addButton(
                $this->lng->txt("registration_codes_add"),
                $this->ctrl->getLinkTarget($this, "addCodes")
            );
        }

        $ctab = new ilRegistrationCodesTableGUI($this, "listCodes");
        $this->tpl->setContent($ctab->getHTML());
    }
    
    public function initAddCodesForm()
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $ilObjDataCache = $DIC['ilObjDataCache'];

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
                self::CODE_TYPE_REGISTRATION,
                $this->lng->txt('registration_codes_type_reg_info')
                )
        );
        $code_type->addOption(
            new ilCheckboxOption(
                $this->lng->txt('registration_codes_type_ext'),
                self::CODE_TYPE_EXTENSION,
                $this->lng->txt('registration_codes_type_ext_info')
                )
        );
        $this->form_gui->addItem($code_type);

        
        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('registration_codes_roles_title'));
        $this->form_gui->addItem($sec);

        $options = array("" => $this->lng->txt('registration_codes_no_assigned_role'));
        foreach ($rbacreview->getGlobalRoles() as $role_id) {
            if (!in_array($role_id, array(SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID))) {
                $options[$role_id] = $ilObjDataCache->lookupTitle($role_id);
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
    }
    
    // see ilRoleAutoCompleteInputGUI
    public function getLocalRoleAutoComplete()
    {
        $q = $_REQUEST["term"];
        $list = ilRoleAutoComplete::getList($q);
        echo $list;
        exit;
    }
    
    public function addCodes()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }
    
        $this->setSubTabs('registration_codes');
        
        $this->initAddCodesForm();
    
        // default
        $limit = $this->form_gui->getItemByPostVar("reg_limit");
        $limit->setValue("none");
        
        $this->tpl->setContent($this->form_gui->getHTML());
    }
    
    public function createCodes()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];
        $rbacreview = $DIC->rbac()->review();

        if (!$ilAccess->checkAccess('write', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }
        
        $this->setSubTabs('registration_codes');

        $this->initAddCodesForm();
        $valid = $this->form_gui->checkInput();
        if ($valid) {
            $number = $this->form_gui->getInput('reg_codes_number');
            $role = $this->form_gui->getInput('reg_codes_role');
            $local = $this->form_gui->getInput("reg_codes_local");
            
            if (is_array($local)) {
                $role_ids = array();
                foreach (array_unique($local) as $item) {
                    if (trim($item)) {
                        $role_id = $rbacreview->roleExists($item);
                        if ($role_id) {
                            $role_ids[] = $role_id;
                        }
                    }
                }
                if (sizeof($role_ids)) {
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
                        $date = array(
                            "d" => $date["dd"],
                            "m" => $date["MM"] % 12,
                            "y" => floor($date["MM"] / 12)
                        );
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
            
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, "listCodes");
        } else {
            $this->form_gui->setValuesByPost();
            $this->tpl->setContent($this->form_gui->getHtml());
        }
    }
    
    public function deleteCodes()
    {
        $this->checkAccess("write");

        include_once './Services/Registration/classes/class.ilRegistrationCode.php';
        ilRegistrationCode::deleteCodes($_POST["id"]);
        
        ilUtil::sendSuccess($this->lng->txt('info_deleted'), true);
        $this->ctrl->redirect($this, "listCodes");
    }

    public function deleteConfirmation()
    {
        $this->checkAccess("write");

        global $DIC;

        $ilErr = $DIC['ilErr'];

        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }
        
        $this->setSubTabs('registration_codes');

        $gui = new ilConfirmationGUI();
        $gui->setHeaderText($this->lng->txt("info_delete_sure"));
        $gui->setCancel($this->lng->txt("cancel"), "listCodes");
        $gui->setConfirm($this->lng->txt("confirm"), "deleteCodes");
        $gui->setFormAction($this->ctrl->getFormAction($this, "deleteCodes"));
        
        $data = ilRegistrationCode::loadCodesByIds($_POST["id"]);
        foreach ($data as $code) {
            $gui->addItem("id[]", $code["code_id"], $code["code"]);
        }

        $this->tpl->setContent($gui->getHTML());
    }
    
    public function resetCodesFilter()
    {
        $utab = new ilRegistrationCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->resetFilter();
        
        $this->listCodes();
    }
    
    public function applyCodesFilter()
    {
        $utab = new ilRegistrationCodesTableGUI($this, "listCodes");
        $utab->resetOffset();
        $utab->writeFilterToSession();
        
        $this->listCodes();
    }
    
    public function exportCodes()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC['ilErr'];

        if (!$ilAccess->checkAccess('read', '', $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
        }
        
        $utab = new ilRegistrationCodesTableGUI($this, "listCodes");
        
        $codes = ilRegistrationCode::getCodesForExport($utab->filter["code"], $utab->filter["role"], $utab->filter["generated"], $utab->filter["alimit"]);

        if (sizeof($codes)) {
            ilUtil::deliverData(implode("\r\n", $codes), "ilias_registration_codes_" . date("d-m-Y") . ".txt", "text/plain");
        } else {
            ilUtil::sendFailure($this->lng->txt("registration_export_codes_no_data"));
            $this->listCodes();
        }
    }
}
