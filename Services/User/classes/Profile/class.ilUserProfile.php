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

    public function skipGroup(string $a_group): void
    {
        $this->skip_groups[] = $a_group;
    }

    public function skipField(string $a_field): void
    {
        $this->skip_fields[] = $a_field;
    }

    public function addStandardFieldsToForm(
        ilPropertyFormGUI $a_form,
        ?ilObjUser $a_user = null,
        array $custom_fields = null
    ): void {
        $registration_settings = null;

        // custom registration settings
        if ($this->mode == self::MODE_REGISTRATION) {
            $registration_settings = new ilRegistrationSettings();

            $this->user_fields['username']['group'] = 'login_data';
            $this->user_fields['password']['group'] = 'login_data';
            $this->user_fields['language']['default'] = $this->lng->lang_key;

            // different position for role
            $roles = $this->user_fields['roles'];
            unset($this->user_fields['roles']);
            $this->user_fields['roles'] = $roles;
            $this->user_fields['roles']['group'] = 'settings';
        }

        $fields = $this->getStandardFields();
        $current_group = '';
        $custom_fields_done = false;
        foreach ($fields as $f => $p) {
            // next group? -> diplay subheader
            if (($p['group'] != $current_group) &&
                $this->userSettingVisible($f)) {
                if (is_array($custom_fields) && !$custom_fields_done) {
                    // should be appended to 'other' or at least before 'settings'
                    if ($current_group == 'other' || $p['group'] == 'settings') {
                        // add 'other' subheader
                        if ($current_group != 'other') {
                            $sh = new ilFormSectionHeaderGUI();
                            $sh->setTitle($this->lng->txt('other'));
                            $a_form->addItem($sh);
                        }
                        foreach ($custom_fields as $custom_field) {
                            $a_form->addItem($custom_field);
                        }
                        $custom_fields_done = true;
                    }
                }

                $sh = new ilFormSectionHeaderGUI();
                $sh->setTitle($this->lng->txt($p['group']));
                $a_form->addItem($sh);
                $current_group = $p['group'];
            }

            $m = $p['method'] ?? '';

            $lv = (isset($p['lang_var']) && $p['lang_var'] != '')
                ? $p['lang_var']
                : $f;

            switch ($p['input']) {
                case 'login':
                    if ((int) $this->settings->get('allow_change_loginname') || $this->mode == self::MODE_REGISTRATION) {
                        $val = new ilTextInputGUI($this->lng->txt('username'), 'username');
                        if ($a_user) {
                            $val->setValue($a_user->getLogin());
                        }
                        $val->setMaxLength((int) $p['maxlength']);
                        $val->setSize(255);
                        $val->setRequired(true);
                    } else {
                        // user account name
                        $val = new ilNonEditableValueGUI($this->lng->txt('username'), 'ne_un');
                        if ($a_user) {
                            $val->setValue($a_user->getLogin());
                        }
                    }
                    $a_form->addItem($val);
                    break;

                case 'text':
                    if ($this->userSettingVisible($f)) {
                        $ti = new ilTextInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        if ($a_user) {
                            $ti->setValue($a_user->$m());
                        }
                        $ti->setMaxLength($p['maxlength']);
                        $ti->setSize($p['size']);
                        $ti->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$ti->getRequired() || $ti->getValue()) {
                            $ti->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        $a_form->addItem($ti);
                    }
                    break;

                case 'sel_country':
                    if ($this->userSettingVisible($f)) {
                        $ci = new ilCountrySelectInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        if ($a_user) {
                            $ci->setValue($a_user->$m());
                        }
                        $ci->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$ci->getRequired() || $ci->getValue()) {
                            $ci->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        $a_form->addItem($ci);
                    }
                    break;

                case 'birthday':
                    if ($this->userSettingVisible($f)) {
                        $bi = new ilBirthdayInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        $date = null;
                        if ($a_user && $a_user->$m() && strlen($a_user->$m())) {
                            $date = new ilDateTime($a_user->$m(), IL_CAL_DATE);
                            $bi->setDate($date);
                        }
                        $bi->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$bi->getRequired() || $date) {
                            $bi->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        $a_form->addItem($bi);
                    }
                    break;

                case 'radio':
                    if ($this->userSettingVisible($f)) {
                        $rg = new ilRadioGroupInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        if ($a_user) {
                            $rg->setValue($a_user->$m());
                        }
                        foreach ($p['values'] as $k => $v) {
                            $op = new ilRadioOption($this->lng->txt($v), $k);
                            $rg->addOption($op);
                        }
                        $rg->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$rg->getRequired() || $rg->getValue()) {
                            $rg->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        $a_form->addItem($rg);
                    }
                    break;

                case 'picture':
                    if ($this->userSettingVisible('upload') && $a_user) {
                        $ii = new ilImageFileInputGUI($this->lng->txt('personal_picture'), 'userfile');
                        $ii->setAllowCapture(true);
                        $ii->setDisabled((bool) $this->settings->get('usr_settings_disable_upload'));

                        $upload = $a_form->getFileUpload('userfile');
                        if ($upload['name'] ?? false) {
                            $ii->setPending($upload['name']);
                        } else {
                            $im = ilObjUser::_getPersonalPicturePath(
                                $a_user->getId(),
                                'small',
                                true,
                                true
                            );
                            if ($im != '') {
                                $ii->setImage($im);
                                $ii->setAlt($this->lng->txt('personal_picture'));
                            }
                        }

                        $a_form->addItem($ii);
                    }
                    break;

                case 'roles':
                    $role_names = '';
                    if ($this->mode == self::MODE_DESKTOP) {
                        if ($this->userSettingVisible('roles')) {
                            $global_roles = $this->rbac_review->getGlobalRoles();
                            foreach ($global_roles as $role_id) {
                                if (in_array($role_id, $this->rbac_review->assignedRoles($a_user->getId()))) {
                                    $roleObj = ilObjectFactory::getInstanceByObjId($role_id);
                                    $role_names .= $roleObj->getTitle() . ', ';
                                    unset($roleObj);
                                }
                            }
                            $dr = new ilNonEditableValueGUI($this->lng->txt('default_roles'), 'ne_dr');
                            $dr->setValue(substr($role_names, 0, -2));
                            $a_form->addItem($dr);
                        }
                    } elseif ($this->mode == self::MODE_REGISTRATION) {
                        if ($registration_settings->roleSelectionEnabled()) {
                            $options = [];
                            foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
                                $options[$role['id']] = $role['title'];
                            }
                            // registration form validation will take care of missing field / value
                            if ($options) {
                                if (count($options) > 1) {
                                    $options = ['' => $this->lng->txt('please_choose')] + $options;
                                    $ta = new ilSelectInputGUI($this->lng->txt('default_role'), 'usr_' . $f);
                                    $ta->setOptions($options);
                                    $ta->setRequired(true);
                                    if (!$ta->getRequired()) {
                                        $ta->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                                    }
                                }
                                // no need for select if only 1 option
                                else {
                                    $ta = new ilHiddenInputGUI('usr_' . $f);
                                    $keys = array_keys($options);
                                    $ta->setValue(array_shift($keys));
                                }
                                $a_form->addItem($ta);
                            }
                        }
                    }
                    break;

                case 'second_email':
                case 'email':
                    if ($this->userSettingVisible($f)) {
                        $em = new ilEMailInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        if ($a_user) {
                            $em->setValue($a_user->$m());
                        }
                        $em->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$em->getRequired() || $em->getValue()) {
                            $em->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        if (self::MODE_REGISTRATION == $this->mode) {
                            $em->setRetype(true);
                        }
                        $a_form->addItem($em);
                    }
                    break;
                case 'textarea':
                    if ($this->userSettingVisible($f)) {
                        $ta = new ilTextAreaInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        if ($a_user) {
                            $ta->setValue($a_user->$m());
                        }
                        $ta->setRows($p['rows']);
                        $ta->setCols($p['cols']);
                        $ta->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$ta->getRequired() || $ta->getValue()) {
                            $ta->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        $a_form->addItem($ta);
                    }
                    break;

                case 'password':
                    if ($this->mode == self::MODE_REGISTRATION) {
                        if (!$registration_settings->passwordGenerationEnabled()) {
                            $ta = new ilPasswordInputGUI($this->lng->txt($lv), 'usr_' . $f);
                            $ta->setUseStripSlashes(false);
                            $ta->setRequired(true);
                            $ta->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
                        } else {
                            $ta = new ilNonEditableValueGUI($this->lng->txt($lv));
                            $ta->setValue($this->lng->txt('reg_passwd_via_mail'));
                        }
                        $a_form->addItem($ta);
                    }
                    break;

                case 'language':
                    if ($this->userSettingVisible($f)) {
                        $ta = new ilSelectInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        if ($a_user) {
                            $ta->setValue($a_user->$m());
                        }
                        $options = [];
                        $this->lng->loadLanguageModule('meta');
                        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
                            $options[$lang_key] = $this->lng->txt('meta_l_' . $lang_key);
                        }
                        asort($options); // #9728
                        $ta->setOptions($options);
                        $ta->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$ta->getRequired() || $ta->getValue()) {
                            $ta->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        $a_form->addItem($ta);
                    }
                    break;

                case 'multitext':
                    if ($this->userSettingVisible($f)) {
                        $ti = new ilTextInputGUI($this->lng->txt($lv), 'usr_' . $f);
                        $ti->setMulti(true);
                        if ($a_user) {
                            $ti->setValue($a_user->$m());
                        }
                        $ti->setMaxLength($p['maxlength']);
                        $ti->setSize($p['size']);
                        $ti->setRequired((bool) $this->settings->get('require_' . $f));
                        if (!$ti->getRequired() || $ti->getValue()) {
                            $ti->setDisabled((bool) $this->settings->get('usr_settings_disable_' . $f));
                        }
                        if ($this->ajax_href) {
                            // add field to ajax call
                            $ti->setDataSource($this->ajax_href . '&f=' . $f);
                        }
                        $a_form->addItem($ti);
                    }
                    break;
                case 'noneditable':
                    if ($this->mode == self::MODE_DESKTOP && $this->userSettingVisible($f)) {
                        $ne = new ilNonEditableValueGUI($this->lng->txt($lv));
                        $ne->setValue($a_user->$m());
                        $a_form->addItem($ne);
                    }
                    break;
            }
        }

        // append custom fields as 'other'
        if (is_array($custom_fields) && !$custom_fields_done) {
            // add 'other' subheader
            if ($current_group != 'other') {
                $sh = new ilFormSectionHeaderGUI();
                $sh->setTitle($this->lng->txt('other'));
                $a_form->addItem($sh);
            }
            foreach ($custom_fields as $custom_field) {
                $a_form->addItem($custom_field);
            }
        }
    }

    public function setAjaxCallback(string $a_href): void
    {
        $this->ajax_href = $a_href;
    }

    public function userSettingVisible(string $a_setting): bool
    {
        $user_settings_config = new ilUserSettingsConfig();

        if ($this->mode == self::MODE_DESKTOP) {
            return ($user_settings_config->isVisible($a_setting));
        } else {
            if (isset($this->user_fields[$a_setting]['visib_reg_hide']) && $this->user_fields[$a_setting]['visib_reg_hide'] === true) {
                return true;
            }
            return ($this->settings->get('usr_settings_visib_reg_' . $a_setting, '1') || $this->settings->get('require_' . $a_setting, '0'));
        }
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
        ilObjUser $a_user,
        bool $a_include_udf = true,
        bool $a_personal_data_only = true
    ): bool {
        $user_settings_config = new ilUserSettingsConfig();

        // standard fields
        foreach ($this->user_fields as $field => $definition) {
            // only if visible in personal data
            if ($a_personal_data_only && !$user_settings_config->isVisible($field)) {
                continue;
            }

            if ($this->settings->get('require_' . $field) && $definition['method']) {
                $value = $a_user->{$definition['method']}();
                if ($value == '') {
                    return true;
                }
            }
        }

        // custom fields
        if ($a_include_udf) {
            $user_defined_data = $a_user->getUserDefinedData();

            $user_defined_fields = ilUserDefinedFields::_getInstance();
            foreach ($user_defined_fields->getRequiredDefinitions() as $field => $definition) {
                // only if visible in personal data
                if ($a_personal_data_only && !$definition['visible']) {
                    continue;
                }

                if (!($user_defined_data['f_' . $field] ?? false)) {
                    ilLoggerFactory::getLogger('user')->info('Profile is incomplete due to missing required udf.');
                    return true;
                }
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
