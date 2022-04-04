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
 * @author       Stefan Meyer <meyer@leifos.de>
 * @ilCtrl_Calls ilObjPrivacySecurityGUI: ilPermissionGUI
 * @ingroup      ServicesPrivacySecurity
 */
class ilObjPrivacySecurityGUI extends ilObjectGUI
{
    private static array $ERROR_MESSAGE = [];

    /**
     * Contructor
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = 'ps';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        self::initErrorMessages();
    }

    public static function initErrorMessages() : void
    {
        global $DIC;

        $lng = $DIC->language();

        if (count(self::$ERROR_MESSAGE)) {
            return;
        }
        $lng->loadLanguageModule('ps');
        ilObjPrivacySecurityGUI::$ERROR_MESSAGE = [
            ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS => $lng->txt("ps_error_message_https_header_missing"),
            ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE => $lng->txt('https_not_possible'),
            ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE => $lng->txt('http_not_possible'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH => $lng->txt('ps_error_message_invalid_password_min_length'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH => $lng->txt('ps_error_message_invalid_password_max_length'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE => $lng->txt('ps_error_message_invalid_password_max_age'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS => $lng->txt('ps_error_message_invalid_login_max_attempts'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN1 => $lng->txt('ps_error_message_password_min1_because_chars'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2 => $lng->txt('ps_error_message_password_min2_because_chars_numbers'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3 => $lng->txt('ps_error_message_password_min3_because_chars_numbers_sc'),
            ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH => $lng->txt('ps_error_message_password_max_less_min')
        ];
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "showPrivacy";
                }

                $this->$cmd();
                break;
        }
    }

    /**
     * Get tabs
     * @access public
     */
    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "show_privacy",
                $this->ctrl->getLinkTarget($this, "showPrivacy"),
                'showPrivacy'
            );
            $this->tabs_gui->addTarget(
                "show_security",
                $this->ctrl->getLinkTarget($this, "showSecurity"),
                'showSecurity'
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    protected function initPrivacyForm() : ilPropertyFormGUI
    {
        $privacy = ilPrivacySettings::getInstance();

        $this->tabs_gui->setTabActive('show_privacy');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ps_privacy_protection'));

        if (ilMemberAgreement::_hasAgreements()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('ps_warning_modify'));
        }

        $value = [];
        if ($privacy->enabledCourseExport()) {
            $value[] = "export_course";
        }
        if ($privacy->enabledGroupExport()) {
            $value[] = "export_group";
        }
        if ($privacy->courseConfirmationRequired()) {
            $value[] = "export_confirm_course";
        }
        if ($privacy->groupConfirmationRequired()) {
            $value[] = "export_confirm_group";
        }
        if ($privacy->enabledGroupAccessTimes()) {
            $value[] = "grp_access_times";
        }
        if ($privacy->enabledCourseAccessTimes()) {
            $value[] = "crs_access_times";
        }
        if ($privacy->participantsListInCoursesEnabled()) {
            $value[] = 'participants_list_courses';
        }
        $group = new ilCheckboxGroupInputGUI($this->lng->txt('ps_profile_export'), 'profile_protection');
        $group->setValue($value);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_export_course'));
        $check->setValue('export_course');
        $group->addOption($check);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_export_groups'));
        $check->setValue('export_group');
        $group->addOption($check);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_export_confirm'));
        $check->setValue('export_confirm_course');
        $group->addOption($check);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_export_confirm_group'));
        $check->setValue('export_confirm_group');
        $group->addOption($check);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_show_grp_access'));
        $check->setValue('grp_access_times');
        $group->addOption($check);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_show_crs_access'));
        $check->setValue('crs_access_times');
        $group->addOption($check);
        $form->addItem($group);
        $check = new ilCheckboxOption();
        $check->setTitle($this->lng->txt('ps_participants_list_courses'));
        $check->setValue('participants_list_courses');
        $group->addOption($check);

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_PRIVACY,
            $form,
            $this
        );

        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('save_privacy', $this->lng->txt('save'));
        }
        return $form;
    }

    /**
     * Show Privacy settings
     * @access public
     */
    public function showPrivacy(?ilPropertyFormGUI $form = null) : void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initPrivacyForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show Privacy settings
     */
    public function showSecurity() : void
    {
        $security = ilSecuritySettings::_getInstance();

        $this->tabs_gui->setTabActive('show_security');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ps_security_protection'));

        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_SECURITY,
            $form,
            $this
        );
        $this->tpl->setContent($form->getHTML());
    }

    public function save_privacy() : void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        $form = $this->initPrivacyForm();
        $valid = true;
        if (!$form->checkInput()) {
            $valid = false;
        }

        $profile_protection = $form->getInput('profile_protection') ?? [];
        $privacy = ilPrivacySettings::getInstance();

        // to determine if agreements need to be reset - see below
        $old_settings = array(
            'export_course' => $privacy->enabledCourseExport(),
            'export_group' => $privacy->enabledGroupExport(),
            'export_confirm_course' => $privacy->courseConfirmationRequired(),
            'export_confirm_group' => $privacy->groupConfirmationRequired(),
            'crs_access_times' => $privacy->enabledCourseAccessTimes(),
            'grp_access_times' => $privacy->enabledGroupAccessTimes(),
            'participants_list_courses' => $privacy->participantsListInCoursesEnabled()
        );

        $privacy->enableCourseExport(in_array('export_course', $profile_protection));
        $privacy->enableGroupExport(in_array('export_group', $profile_protection));
        $privacy->setCourseConfirmationRequired(in_array('export_confirm_course', $profile_protection));
        $privacy->setGroupConfirmationRequired(in_array('export_confirm_group', $profile_protection));
        $privacy->showGroupAccessTimes(in_array('grp_access_times', $profile_protection));
        $privacy->showCourseAccessTimes(in_array('crs_access_times', $profile_protection));
        $privacy->enableParticipantsListInCourses(in_array('participants_list_courses', $profile_protection));

        // validate settings
        $code = $privacy->validate();
        // if error code != 0, display error and do not save
        if ($code !== 0) {
            $msg = self::getErrorMessage($code);
            $this->tpl->setOnScreenMessage('failure', $msg);
            $form->setValuesByPost();
            $this->showPrivacy($form);
            return;
        } elseif (!$valid) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $form->setValuesByPost();
            $this->showPrivacy($form);
            return;
        } else {
            $privacy->save();
            // reset agreements?
            $do_reset = false;
            if (!$old_settings['export_course'] && $privacy->enabledCourseExport()) {
                $do_reset = true;
            }
            if (!$do_reset && !$old_settings['export_group'] && $privacy->enabledGroupExport()) {
                $do_reset = true;
            }
            if (!$do_reset && !$old_settings['export_confirm_course'] && $privacy->courseConfirmationRequired()) {
                $do_reset = true;
            }
            if (!$do_reset && !$old_settings['export_confirm_group'] && $privacy->groupConfirmationRequired()) {
                $do_reset = true;
            }
            if (!$do_reset && !$old_settings['crs_access_times'] && $privacy->enabledCourseAccessTimes()) {
                $do_reset = true;
            }
            if (!$do_reset && !$old_settings['grp_access_times'] && $privacy->enabledGroupAccessTimes()) {
                $do_reset = true;
            }
            if ($do_reset) {
                ilMemberAgreement::_reset();
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        }
        $this->ctrl->redirect($this, 'showPrivacy');
    }

    /**
     * Save security settings
     */
    public function save_security() : void
    {
        if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $this->showSecurity();
    }

    /**
     * return error message for error code
     * @param int $code
     * @return string
     */

    public static function getErrorMessage(int $code) : string
    {
        self::initErrorMessages();
        if (array_key_exists($code, self::$ERROR_MESSAGE)) {
            return self::$ERROR_MESSAGE[$code];
        }
        return '';
    }

    public function addToExternalSettingsForm(int $a_form_id) : array
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_COURSE:

                $privacy = ilPrivacySettings::getInstance();

                $subitems = array(
                    'ps_export_course' => array($privacy->enabledCourseExport(),
                                                ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_export_confirm' => array($privacy->courseConfirmationRequired(),
                                                 ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_show_crs_access' => array($privacy->enabledCourseAccessTimes(),
                                                  ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_participants_list_courses' => [$privacy->participantsListInCoursesEnabled(),
                                                       ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ]
                );
                $fields = [
                    'ps_profile_export' => [null, null, $subitems]
                ];
                return array(array("showPrivacy", $fields));

            case ilAdministrationSettingsFormHandler::FORM_GROUP:

                $privacy = ilPrivacySettings::getInstance();

                $subitems = array(
                    'ps_export_groups' => array($privacy->enabledGroupExport(),
                                                ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_export_confirm_group' => array($privacy->groupConfirmationRequired(),
                                                       ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'ps_show_grp_access' => array($privacy->enabledGroupAccessTimes(),
                                                  ilAdministrationSettingsFormHandler::VALUE_BOOL
                    )
                );
                $fields = array(
                    'ps_profile_export' => array(null, null, $subitems)
                );
                return [["showPrivacy", $fields]];
        }
        return [];
    }
}
