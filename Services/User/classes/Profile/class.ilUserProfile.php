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

use ILIAS\User\Profile\ilUserProfileDefaultFields;

/**
 * Class ilUserProfile
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserProfile
{
    public const MODE_DESKTOP = 1;
    public const MODE_REGISTRATION = 2;

    private int $mode = self::MODE_DESKTOP;

    private ilSetting $settings;
    private ilLanguage $lng;
    private ilRbacReview $rbac_review;

    private array $user_fields;
    protected string $ajax_href;
    protected array $skip_fields; // Missing array type.
    protected array $skip_groups; // Missing array type.

    protected ilUserSettingsConfig $user_settings_config;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->settings = $DIC['ilSetting'];
        $this->lng = $DIC['lng'];
        $this->rbac_review = $DIC['rbacreview'];

        $this->user_fields = (new ilUserProfileDefaultFields())->getDefaultProfileFields();
        $this->user_settings_config = new ilUserSettingsConfig();

        $this->skip_groups = [];
        $this->skip_fields = [];

        $this->lng->loadLanguageModule('awrn');
        $this->lng->loadLanguageModule('buddysystem');
    }

    public function getStandardFields(): array // Missing array type.
    {
        $fields = [];
        foreach ($this->user_fields as $f => $p) {
            // skip hidden groups
            if (in_array($p['group'], $this->skip_groups) ||
                in_array($f, $this->skip_fields)) {
                continue;
            }
            $fields[$f] = $p;
        }
        return $fields;
    }

    public function getLocalUserAdministrationFields(): array // Missing array type.
    {
        $fields = [];
        foreach ($this->getStandardFields() as $field => $info) {
            if ($this->settings->get('usr_settings_visib_lua_' . $field, '1')) {
                $fields[$field] = $info;
            } elseif ($info['visib_lua_fix_value'] ?? false) {
                $fields[$field] = $info;
            }
        }
        return $fields;
    }

    public function skipGroup(string $group): void
    {
        $this->skip_groups[] = $group;
    }

    public function skipField(string $field): void
    {
        $this->skip_fields[] = $field;
    }

    public function addStandardFieldsToForm(
        ilPropertyFormGUI $form,
        ?ilObjUser $user = null,
        array $custom_fields = null
    ): void {
        $registration_settings = null;
        if ($this->mode == self::MODE_REGISTRATION) {
            $registration_settings = new ilRegistrationSettings();
            $this->addRegistrationFieldsToFieldArray();
        }

        $current_group = '';
        $custom_fields_done = false;
        foreach ($this->getStandardFields() as $field_id => $field_definition) {
            // next group? -> diplay subheader
            if (($field_definition['group'] !== $current_group) &&
                $this->userSettingVisible($field_id)) {
                list($form, $current_group, $custom_fields_done) = $this->handleSectionChange(
                    $form,
                    $current_group,
                    $field_definition['group'],
                    $custom_fields,
                    $custom_fields_done
                );
            }
            $form = $this->addFieldToForm(
                $field_id,
                $field_definition,
                $user,
                $form,
                $registration_settings
            );
        }

        // append custom fields as 'other'
        if (is_array($custom_fields) && !$custom_fields_done) {
            $form = $this->addCustomFieldsToForm($form, $custom_fields, $current_group);
        }
    }

    private function addRegistrationFieldsToFieldArray(): void
    {
        $this->user_fields['username']['group'] = 'login_data';
        $this->user_fields['password']['group'] = 'login_data';
        $this->user_fields['language']['default'] = $this->lng->lang_key;

        // different position for role
        $roles = $this->user_fields['roles'];
        unset($this->user_fields['roles']);
        $this->user_fields['roles'] = $roles;
        $this->user_fields['roles']['group'] = 'settings';
    }

    private function handleSectionChange(
        ilPropertyFormGUI $form,
        string $current_group,
        string $next_group,
        ?array $custom_fields,
        bool $custom_fields_done
    ): array {
        if ($custom_fields !== null && !$custom_fields_done
            && ($current_group === 'other' || $next_group === 'settings')) {
            // add 'other' subheader
            $form = $this->addCustomFieldsToForm(
                $form,
                $custom_fields,
                $current_group
            );
            $custom_fields_done = true;
        }

        $section_header = new ilFormSectionHeaderGUI();
        $section_header->setTitle($this->lng->txt($next_group));
        $form->addItem($section_header);
        return [
            $form,
            $next_group,
            $custom_fields_done
        ];
    }

    private function addFieldToForm(
        string $field_id,
        array $field_definition,
        ?ilObjUser $user,
        ilPropertyFormGUI $form,
        ?ilRegistrationSettings $registration_settings
    ): ilPropertyFormGUI {
        $method = $field_definition['method'] ?? '';

        $lang_var = (isset($field_definition['lang_var']) && $field_definition['lang_var'] !== '')
            ? $field_definition['lang_var']
            : $field_id;

        switch ($field_definition['input']) {
            case 'text':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getTextInput(
                        $field_id,
                        $field_definition,
                        $method,
                        $lang_var,
                        $user
                    )
                );
                break;

            case 'textarea':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getTextareaInput(
                        $field_id,
                        $field_definition,
                        $method,
                        $lang_var,
                        $user
                    )
                );
                break;

            case 'multitext':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getMultitextInput(
                        $field_id,
                        $field_definition,
                        $method,
                        $lang_var,
                        $user
                    )
                );
                break;

            case 'radio':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getRadioInput(
                        $field_id,
                        $field_definition,
                        $method,
                        $lang_var,
                        $user
                    )
                );
                break;

            case 'login':
                $form->addItem(
                    $this->getLoginInput($field_definition, $user)
                );
                break;

            case 'sel_country':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getCountryInput($field_id, $method, $lang_var, $user)
                );
                break;

            case 'birthday':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getBirthdayInput($field_id, $method, $lang_var, $user)
                );
                break;

            case 'picture':
                if (!$this->userSettingVisible($field_id) || $user === null) {
                    break;
                }

                $form->addItem(
                    $this->getImageInput($form->getFileUpload('userfile'), $user)
                );
                break;

            case 'roles':
                $roles_input = $this->getRolesInput($field_id, $registration_settings, $user);
                if ($roles_input !== null) {
                    $form->addItem($roles_input);
                }
                break;

            case 'second_email':
            case 'email':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getEmailInput($field_id, $method, $lang_var, $user)
                );
                break;

            case 'password':
                if ($this->mode !== self::MODE_REGISTRATION) {
                    break;
                }

                $form->addItem(
                    $this->getPasswordInput(
                        $field_id,
                        $lang_var,
                        $registration_settings
                    )
                );
                break;

            case 'language':
                if (!$this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getLanguageInput(
                        $field_id,
                        $method,
                        $lang_var,
                        $user
                    )
                );
                break;

            case 'noneditable':
                if ($this->mode !== self::MODE_DESKTOP || $this->userSettingVisible($field_id)) {
                    break;
                }

                $form->addItem(
                    $this->getNonEditableInput($method, $lang_var, $user)
                );
                break;
        }

        return $form;
    }

    private function getTextInput(
        string $field_id,
        array $field_definition,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $text_input = new ilTextInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        if ($user !== null) {
            $text_input->setValue($user->$method());
        }
        $text_input->setMaxLength($field_definition['maxlength']);
        $text_input->setSize($field_definition['size']);
        $text_input->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$text_input->getRequired() || $text_input->getValue()) {
            $text_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }

        return $text_input;
    }

    private function getTextareaInput(
        string $field_id,
        array $field_definition,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $text_area = new ilTextAreaInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        if ($user !== null) {
            $text_area->setValue($user->$method());
        }
        $text_area->setRows($field_definition['rows']);
        $text_area->setCols($field_definition['cols']);
        $text_area->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$text_area->getRequired() || $text_area->getValue()) {
            $text_area->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        return $text_area;
    }

    private function getMultitextInput(
        string $field_id,
        array $field_definition,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $multi_text_input = new ilTextInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        $multi_text_input->setMulti(true);
        if ($user !== null) {
            $multi_text_input->setValue($user->$method());
        }
        $multi_text_input->setMaxLength($field_definition['maxlength']);
        $multi_text_input->setSize($field_definition['size']);
        $multi_text_input->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$multi_text_input->getRequired() || $multi_text_input->getValue()) {
            $multi_text_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        if ($this->ajax_href) {
            // add field to ajax call
            $multi_text_input->setDataSource($this->ajax_href . '&f=' . $field_id);
        }
        return $multi_text_input;
    }

    private function getRadioInput(
        string $field_id,
        array $field_definition,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $radio_group = new ilRadioGroupInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        if ($user) {
            $radio_group->setValue($user->$method());
        }
        foreach ($field_definition['values'] as $k => $v) {
            $op = new ilRadioOption($this->lng->txt($v), $k);
            $radio_group->addOption($op);
        }
        $radio_group->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$radio_group->getRequired() || $radio_group->getValue()) {
            $radio_group->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        return $radio_group;
    }

    private function getLoginInput(
        array $field_definition,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $login_input = new ilNonEditableValueGUI($this->lng->txt('username'), 'ne_un');

        if ((int) $this->settings->get('allow_change_loginname') || $this->mode == self::MODE_REGISTRATION) {
            $login_input = new ilTextInputGUI($this->lng->txt('username'), 'username');
            $login_input->setMaxLength((int) $field_definition['maxlength']);
            $login_input->setSize(255);
            $login_input->setRequired(true);
        }

        if ($user !== null) {
            $login_input->setValue($user->getLogin());
        }
        return $login_input;
    }

    private function getCountryInput(
        string $field_id,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $country_input = new ilCountrySelectInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        if ($user) {
            $country_input->setValue($user->$method());
        }
        $country_input->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$country_input->getRequired() || $country_input->getValue()) {
            $country_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        return $country_input;
    }

    private function getBirthdayInput(
        string $field_id,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $birthday_input = new ilBirthdayInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        $date = null;
        if ($user && $user->$method() && strlen($user->$method())) {
            $date = new ilDateTime($user->$method(), IL_CAL_DATE);
            $birthday_input->setDate($date);
        }
        $birthday_input->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$birthday_input->getRequired() || $date !== null) {
            $birthday_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        return $birthday_input;
    }

    private function getImageInput(
        array $file_upload,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $image_input = new ilImageFileInputGUI($this->lng->txt('personal_picture'), 'userfile');
        $image_input->setAllowCapture(true);
        $image_input->setDisabled((bool) $this->settings->get('usr_settings_disable_upload'));

        if ($file_upload['name'] ?? false) {
            $image_input->setPending($file_upload['name']);
        } else {
            $picture_path = ilObjUser::_getPersonalPicturePath(
                $user->getId(),
                'small',
                true,
                true
            );
            if ($picture_path !== '') {
                $image_input->setImage($picture_path);
                $image_input->setAlt($this->lng->txt('personal_picture'));
            }
        }
        return $image_input;
    }

    private function getRolesInput(
        string $field_id,
        ?ilRegistrationSettings $registration_settings,
        ?ilObjUser $user
    ): ?ilFormPropertyGUI {
        $role_names = '';
        if ($this->mode === self::MODE_DESKTOP
            && $this->userSettingVisible('roles')) {
            $global_roles = $this->rbac_review->getGlobalRoles();
            foreach ($global_roles as $role_id) {
                if (in_array($role_id, $this->rbac_review->assignedRoles($user->getId()))) {
                    $role_obj = ilObjectFactory::getInstanceByObjId($role_id);
                    $role_names .= $role_obj->getTitle() . ', ';
                    unset($role_obj);
                }
            }
            $roles_input = new ilNonEditableValueGUI($this->lng->txt('default_roles'), 'ne_dr');
            $roles_input->setValue(substr($role_names, 0, -2));
            return $roles_input;
        }

        if ($this->mode === self::MODE_REGISTRATION
            && $registration_settings->roleSelectionEnabled()) {
            $options = [];
            foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
                $options[$role['id']] = $role['title'];
            }

            if ($options === []) {
                return null;
            }

            if (count($options) === 1) {
                $roles_input = new ilHiddenInputGUI('usr_' . $field_id);
                $keys = array_keys($options);
                $roles_input->setValue(array_shift($keys));
                return $roles_input;
            }

            $options_with_empty_value = ['' => $this->lng->txt('please_choose')] + $options;
            $roles_input = new ilSelectInputGUI($this->lng->txt('default_role'), 'usr_' . $field_id);
            $roles_input->setOptions($options_with_empty_value);
            $roles_input->setRequired(true);
            if (!$roles_input->getRequired()) {
                $roles_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
            }
            return $roles_input;
        }

        return null;
    }

    private function getEmailInput(
        string $field_id,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $email_input = new ilEMailInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        if ($user) {
            $email_input->setValue($user->$method());
        }
        $email_input->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$email_input->getRequired() || $email_input->getValue()) {
            $email_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        if (self::MODE_REGISTRATION == $this->mode) {
            $email_input->setRetype(true);
        }
        return $email_input;
    }

    private function getPasswordInput(
        string $field_id,
        string $lang_var,
        ilRegistrationSettings $registration_settings
    ): ilFormPropertyGUI {
        if ($registration_settings->passwordGenerationEnabled()) {
            $password_input = new ilNonEditableValueGUI($this->lng->txt($lang_var));
            $password_input->setValue($this->lng->txt('reg_passwd_via_mail'));
            return $password_input;
        }

        $password_input = new ilPasswordInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        $password_input->setUseStripSlashes(false);
        $password_input->setRequired(true);
        $password_input->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
        return $password_input;
    }

    private function getLanguageInput(
        string $field_id,
        string $method,
        string $lang_var,
        ?ilObjUser $user
    ): ilFormPropertyGUI {
        $language_input = new ilSelectInputGUI($this->lng->txt($lang_var), 'usr_' . $field_id);
        if ($user !== null) {
            $language_input->setValue($user->$method());
        }
        $options = [];
        $this->lng->loadLanguageModule('meta');
        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            $options[$lang_key] = $this->lng->txt('meta_l_' . $lang_key);
        }
        asort($options);
        $language_input->setOptions($options);
        $language_input->setRequired((bool) $this->settings->get('require_' . $field_id));
        if (!$language_input->getRequired() || $language_input->getValue()) {
            $language_input->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $field_id));
        }
        return $language_input;
    }

    private function getNonEditableInput(
        string $method,
        string $lang_var,
        ilObjUser $user
    ): ilFormPropertyGUI {
        $non_editable_input = new ilNonEditableValueGUI($this->lng->txt($lang_var));
        $non_editable_input->setValue($user->$method());
        return $non_editable_input;
    }

    private function addCustomFieldsToForm(ilPropertyFormGUI $form, array $custom_fields, string $current_group): ilPropertyFormGUI
    {
        if ($current_group !== 'other') {
            $section_header = new ilFormSectionHeaderGUI();
            $section_header->setTitle($this->lng->txt('other'));
            $form->addItem($section_header);
        }
        foreach ($custom_fields as $custom_field) {
            $form->addItem($custom_field);
        }
        return $form;
    }

    public function setAjaxCallback(string $href): void
    {
        $this->ajax_href = $href;
    }

    public function userSettingVisible(string $setting): bool
    {
        if ($this->mode === self::MODE_DESKTOP) {
            return ($this->user_settings_config->isVisible($setting));
        }

        if (isset($this->user_fields[$setting]['visib_reg_hide'])
            && $this->user_fields[$setting]['visib_reg_hide'] === true) {
            return true;
        }

        return ($this->settings->get('usr_settings_visib_reg_' . $setting, '1')
            || $this->settings->get('require_' . $setting, '0'));
    }

    public function setMode(int $mode): bool
    {
        if (in_array($mode, [self::MODE_DESKTOP, self::MODE_REGISTRATION])) {
            $this->mode = $mode;
            return true;
        }
        return false;
    }

    public function isProfileIncomplete(
        ilObjUser $user,
        bool $include_udf = true,
        bool $personal_data_only = true
    ): bool {
        // standard fields
        foreach ($this->user_fields as $field => $definition) {
            // only if visible in personal data
            if ($personal_data_only && !$this->user_settings_config->isVisible($field)) {
                continue;
            }

            if ($this->settings->get('require_' . $field) && $definition['method']
                && $user->{$definition['method']}() === '') {
                return true;
            }
        }

        // custom fields
        if (!$include_udf) {
            return false;
        }

        $user_defined_data = $user->getUserDefinedData();
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        foreach ($user_defined_fields->getRequiredDefinitions() as $field => $definition) {
            // only if visible in personal data
            if ($personal_data_only && !$definition['visible']) {
                continue;
            }

            if (!($user_defined_data['f_' . $field] ?? false)) {
                ilLoggerFactory::getLogger('user')->info('Profile is incomplete due to missing required udf.');
                return true;
            }
        }

        return false;
    }

    protected function isEditableByUser(string $setting): bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    public function getIgnorableRequiredSettings(): array // Missing array type.
    {
        $ignorableSettings = [];

        foreach (array_keys($this->user_fields) as $field) {
            // !!!username and password must not be ignored!!!
            if ('username' == $field ||
                'password' == $field) {
                continue;
            }

            // Field is not required -> continue
            if (!$this->settings->get('require_' . $field)) {
                continue;
            }

            if ($this->isEditableByUser($field)) {
                $ignorableSettings[] = $field;
            }
        }

        return $ignorableSettings;
    }
}
