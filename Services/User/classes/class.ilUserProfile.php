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

// mjansen@databay.de essential for mail constants, do not remove this include

use ILIAS\Services\Mail\ilMailUserFieldChangeListener;

/**
 * Class ilUserProfile
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserProfile
{
    public const MODE_DESKTOP = 1;
    public const MODE_REGISTRATION = 2;

    private static int $mode = self::MODE_DESKTOP;

    // this array should be used in all places where user data is tackled
    // in the future: registration, personal profile, user administration
    // public profile, user import/export
    // for now this is not implemented yet. Please list places, that already use it:
    //
    // - personal profile
    // - (global) standard user profile fields settings
    //
    // the following attributes are defined (can be extended if needed):
    // - input: input type
    //			standard inputs: text, radio, selection, textarea
    //			special inputs: login
    // - input dependend attributes
    //		- maxlength, sizte for text
    //		- values array for radio
    //		- cols/rows for text areas
    //		- options array for selections
    // - method: ilObjUser get-method, e.g. getFirstname
    // - group: group id (id is also used as lang_var for sub headers in forms
    // - lang_var: if key should not be used as lang var, this overwrites the usage in forms
    // - settings property related attributes, settingsproperties are ("visible", "changeable",
    //   "searchable", "required", "export", "course_export" and "registration")
    // 		- <settingsproperty>_hide: hide this property in settings (not implemented)
    // 		- <settingsproperty>_fix_value: property has a fix value (cannot be changed)
    private static array $user_field = array(
        "username" => array(
                        "input" => "login",
                        "maxlength" => 190,
                        "size" => 190,
                        "method" => "getLogin",
                        "course_export_fix_value" => 1,
                        "group_export_fix_value" => 1,
                        "changeable_hide" => true,
                        "required_hide" => true,
                        "group" => "personal_data"),
        "password" => array(
                        "input" => "password",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        'visib_lua_fix_value' => 0,
                        "course_export_hide" => true,
                        "export_hide" => false,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "personal_data"),
        "firstname" => array(
                        "input" => "text",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getFirstname",
                        "required_fix_value" => 1,
                        "visib_reg_fix_value" => 1,
                        'visib_lua_fix_value' => 1,
                        "course_export_fix_value" => 1,
                        "group_export_fix_value" => 1,
                        "group" => "personal_data"),
        "lastname" => array(
                        "input" => "text",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getLastname",
                        "required_fix_value" => 1,
                        "visib_reg_fix_value" => 1,
                        'visib_lua_fix_value' => 1,
                        "course_export_fix_value" => 1,
                        "group_export_fix_value" => 1,
                        "group" => "personal_data"),
        "title" => array(
                        "input" => "text",
                        "lang_var" => "person_title",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getUTitle",
                        "group" => "personal_data"),
        "birthday" => array(
                        "input" => "birthday",
                        "lang_var" => "birthday",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getBirthday",
                        "group" => "personal_data"),
        "gender" => array(
                        "input" => "radio",
                        "values" => array("n" => "salutation_n", "f" => "salutation_f", "m" => "salutation_m"),
                        "method" => "getGender",
                        "group" => "personal_data"),
        "upload" => array(
                        "input" => "picture",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "lang_var" => "personal_picture",
                        "group" => "personal_data"),
        "roles" => array(
                        "input" => "roles",
                        "changeable_hide" => true,
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "export_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "personal_data"),
        "interests_general" => array(
                        "input" => "multitext",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getGeneralInterests",
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "interests"),
        "interests_help_offered" => array(
                        "input" => "multitext",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getOfferingHelp",
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "interests"),
        "interests_help_looking" => array(
                        "input" => "multitext",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getLookingForHelp",
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "interests"),
        "org_units" => array(
                        "input" => "noneditable",
                        "lang_var" => "objs_orgu",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => false,
                        "group_export_hide" => false,
                        "export_hide" => true,
                        "changeable_hide" => true,
                        "changeable_fix_value" => 0,
                        "changeable_lua_hide" => true,
                        "changeable_lua_fix_value" => 0,
                        "method" => "getOrgUnitsRepresentation",
                        "group" => "contact_data"),
        "institution" => array(
                        "input" => "text",
                        "maxlength" => 80,
                        "size" => 40,
                        "method" => "getInstitution",
                        "group" => "contact_data"),
        "department" => array(
                        "input" => "text",
                        "maxlength" => 80,
                        "size" => 40,
                        "method" => "getDepartment",
                        "group" => "contact_data"),
        "street" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getStreet",
                        "group" => "contact_data"),
        "zipcode" => array(
                        "input" => "text",
                        "maxlength" => 10,
                        "size" => 10,
                        "method" => "getZipcode",
                        "group" => "contact_data"),
        "city" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getCity",
                        "group" => "contact_data"),
        "country" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getCountry",
                        "group" => "contact_data"),
        "sel_country" => array(
                        "input" => "sel_country",
                        "method" => "getSelectedCountry",
                        "group" => "contact_data"),
        "phone_office" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getPhoneOffice",
                        "group" => "contact_data"),
        "phone_home" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getPhoneHome",
                        "group" => "contact_data"),
        "phone_mobile" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getPhoneMobile",
                        "group" => "contact_data"),
        "fax" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getFax",
                        "group" => "contact_data"),
        "email" => array(
                        "input" => "email",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getEmail",
                        "group" => "contact_data"),
        "second_email" => array(
                        "input" => "second_email",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getSecondEmail",
                        "group" => "contact_data",
                        "change_listeners" => [
                            ilMailUserFieldChangeListener::class,
                        ]),
        "hobby" => array(
                        "input" => "textarea",
                        "rows" => 3,
                        "cols" => 45,
                        "method" => "getHobby",
                        "lists_hide" => true,
                        "group" => "contact_data"),
        "referral_comment" => array(
                        "input" => "textarea",
                        "rows" => 3,
                        "cols" => 45,
                        "method" => "getComment",
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "contact_data"),
        "matriculation" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getMatriculation",
                        "group" => "other"),
        "language" => array(
                        "input" => "language",
                        "method" => "getLanguage",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "settings"),
        "skin_style" => array(
                        "input" => "skinstyle",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "settings"),
        "hits_per_page" => array(
                        "input" => "hitsperpage",
                        "default" => 10,
                        "options" => array(
                            10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40,
                            50 => 50, 100 => 100, 9999 => 9999),
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "settings"),
        /*"show_users_online" => array(
                        "input" => "selection",
                        "default" => "y",
                        "options" => array(
                            "y" => "users_online_show_short_y",
                            "associated" => "users_online_show_short_associated",
                            "n" => "users_online_show_short_n"),
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "settings"),*/
        "hide_own_online_status" => array(
                        "input" => "selection",
                        "lang_var" => "awrn_user_show",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "settings",
                        "default" => "y",
                        "options" => array(
                            "y" => "user_awrn_hide",
                            "n" => "user_awrn_show"
                        )),
        "bs_allow_to_contact_me" => array(
            "input" => "selection",
            "lang_var" => "buddy_allow_to_contact_me",
            "required_hide" => true,
            "visib_reg_hide" => true,
            "course_export_hide" => true,
            "group_export_hide" => true,
            "group" => "settings",
            "default" => "y",
            "options" => array(
                "n" => "buddy_allow_to_contact_me_no",
                "y" => "buddy_allow_to_contact_me_yes"
            )
        ),
        "chat_osc_accept_msg" => array(
            "input" => "selection",
            "lang_var" => "chat_osc_accept_msg",
            "required_hide" => true,
            "visib_reg_hide" => true,
            "course_export_hide" => true,
            "group_export_hide" => true,
            "group" => "settings",
            "default" => "y",
            "options" => array(
                "n" => "chat_osc_accepts_messages_no",
                "y" => "chat_osc_accepts_messages_yes"
            )
        ),
        "chat_broadcast_typing" => array(
            "input" => "selection",
            "lang_var" => "chat_broadcast_typing",
            "required_hide" => true,
            "visib_reg_hide" => true,
            "course_export_hide" => true,
            "group_export_hide" => true,
            "group" => "settings",
            "default" => "y",
            "options" => array(
                "n" => "chat_broadcast_typing_no",
                "y" => "chat_broadcast_typing_yes"
            )
        ),
        "preferences" => array(
                        "visible_fix_value" => 1,
                        "changeable_fix_value" => 1,
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "preferences"),
        "mail_incoming_mail" => array(
                        "input" => "selection",
                        "default" => "y",
                        "options" => array(
                            ilMailOptions::INCOMING_LOCAL => "mail_incoming_local",
                            ilMailOptions::INCOMING_EMAIL => "mail_incoming_smtp",
                            ilMailOptions::INCOMING_BOTH => "mail_incoming_both"),
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "export_hide" => true,
                        "search_hide" => true,
                        "group" => "settings")

        );
    protected string $ajax_href;
    protected array $skip_fields; // Missing array type.
    protected array $skip_groups; // Missing array type.

    protected ilUserSettingsConfig $user_settings_config;

    public function __construct()
    {
        global $DIC;

        $lng = $DIC->language();

        $this->skip_groups = array();
        $this->skip_fields = array();

        // for hide me from awareness tool text
        // not nicest workaround, but better than using common block
        $lng->loadLanguageModule("awrn");
        $lng->loadLanguageModule("buddysystem");

        $this->user_settings_config = new ilUserSettingsConfig();
    }

    /**
     * Get standard user fields array
     */
    public function getStandardFields(): array // Missing array type.
    {
        $fields = array();
        foreach (self::$user_field as $f => $p) {
            // skip hidden groups
            if (in_array($p["group"], $this->skip_groups) ||
                in_array($f, $this->skip_fields)) {
                continue;
            }
            $fields[$f] = $p;
        }
        return $fields;
    }

    /**
     * Get visible fields in local user administration
     */
    public function getLocalUserAdministrationFields(): array // Missing array type.
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $fields = array();
        foreach ($this->getStandardFields() as $field => $info) {
            if ($ilSetting->get('usr_settings_visib_lua_' . $field, '1')) {
                $fields[$field] = $info;
            } elseif ($info['visib_lua_fix_value'] ?? false) {
                $fields[$field] = $info;
            }
        }
        return $fields;
    }


    /**
     * Skip a group
     */
    public function skipGroup(string $a_group): void
    {
        $this->skip_groups[] = $a_group;
    }

    /**
     * Skip a field
     */
    public function skipField(string $a_field): void
    {
        $this->skip_fields[] = $a_field;
    }

    /**
     * Add standard fields to form
     */
    public function addStandardFieldsToForm(
        ilPropertyFormGUI $a_form,
        ?ilObjUser $a_user = null,
        array $custom_fields = null
    ): void {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $lng = $DIC['lng'];
        $rbacreview = $DIC['rbacreview'];

        $registration_settings = null;

        // custom registration settings
        if (self::$mode == self::MODE_REGISTRATION) {
            $registration_settings = new ilRegistrationSettings();

            self::$user_field["username"]["group"] = "login_data";
            self::$user_field["password"]["group"] = "login_data";
            self::$user_field["language"]["default"] = $lng->lang_key;

            // different position for role
            $roles = self::$user_field["roles"];
            unset(self::$user_field["roles"]);
            self::$user_field["roles"] = $roles;
            self::$user_field["roles"]["group"] = "settings";
        }

        $fields = $this->getStandardFields();
        $current_group = "";
        $custom_fields_done = false;
        foreach ($fields as $f => $p) {
            // next group? -> diplay subheader
            if (($p["group"] != $current_group) &&
                self::userSettingVisible($f)) {
                if (is_array($custom_fields) && !$custom_fields_done) {
                    // should be appended to "other" or at least before "settings"
                    if ($current_group == "other" || $p["group"] == "settings") {
                        // add "other" subheader
                        if ($current_group != "other") {
                            $sh = new ilFormSectionHeaderGUI();
                            $sh->setTitle($lng->txt("other"));
                            $a_form->addItem($sh);
                        }
                        foreach ($custom_fields as $custom_field) {
                            $a_form->addItem($custom_field);
                        }
                        $custom_fields_done = true;
                    }
                }

                $sh = new ilFormSectionHeaderGUI();
                $sh->setTitle($lng->txt($p["group"]));
                $a_form->addItem($sh);
                $current_group = $p["group"];
            }

            $m = $p["method"] ?? "";

            $lv = (isset($p["lang_var"]) && $p["lang_var"] != "")
                ? $p["lang_var"]
                : $f;

            switch ($p["input"]) {
                case "login":
                    if ((int) $ilSetting->get('allow_change_loginname') || self::$mode == self::MODE_REGISTRATION) {
                        $val = new ilTextInputGUI($lng->txt('username'), 'username');
                        if ($a_user) {
                            $val->setValue($a_user->getLogin());
                        }
                        $val->setMaxLength((int) $p['maxlength']);
                        $val->setSize(255);
                        $val->setRequired(true);
                    } else {
                        // user account name
                        $val = new ilNonEditableValueGUI($lng->txt("username"), 'ne_un');
                        if ($a_user) {
                            $val->setValue($a_user->getLogin());
                        }
                    }
                    $a_form->addItem($val);
                    break;

                case "text":
                    if (self::userSettingVisible($f)) {
                        $ti = new ilTextInputGUI($lng->txt($lv), "usr_" . $f);
                        if ($a_user) {
                            $ti->setValue($a_user->$m());
                        }
                        $ti->setMaxLength($p["maxlength"]);
                        $ti->setSize($p["size"]);
                        $ti->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$ti->getRequired() || $ti->getValue()) {
                            $ti->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        $a_form->addItem($ti);
                    }
                    break;

                case "sel_country":
                    if (self::userSettingVisible($f)) {
                        $ci = new ilCountrySelectInputGUI($lng->txt($lv), "usr_" . $f);
                        if ($a_user) {
                            $ci->setValue($a_user->$m());
                        }
                        $ci->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$ci->getRequired() || $ci->getValue()) {
                            $ci->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        $a_form->addItem($ci);
                    }
                    break;

                case "birthday":
                    if (self::userSettingVisible($f)) {
                        $bi = new ilBirthdayInputGUI($lng->txt($lv), "usr_" . $f);
                        $date = null;
                        if ($a_user && strlen($a_user->$m())) {
                            $date = new ilDateTime($a_user->$m(), IL_CAL_DATE);
                            $bi->setDate($date);
                        }
                        $bi->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$bi->getRequired() || $date) {
                            $bi->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        $a_form->addItem($bi);
                    }
                    break;

                case "radio":
                    if (self::userSettingVisible($f)) {
                        $rg = new ilRadioGroupInputGUI($lng->txt($lv), "usr_" . $f);
                        if ($a_user) {
                            $rg->setValue($a_user->$m());
                        }
                        foreach ($p["values"] as $k => $v) {
                            $op = new ilRadioOption($lng->txt($v), $k);
                            $rg->addOption($op);
                        }
                        $rg->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$rg->getRequired() || $rg->getValue()) {
                            $rg->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        $a_form->addItem($rg);
                    }
                    break;

                case "picture":
                    if (self::userSettingVisible("upload") && $a_user) {
                        $ii = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
                        $ii->setAllowCapture(true);
                        $ii->setDisabled((bool) $ilSetting->get("usr_settings_disable_upload"));

                        $upload = $a_form->getFileUpload("userfile");
                        if ($upload["name"] ?? false) {
                            $ii->setPending($upload["name"]);
                        } else {
                            $im = ilObjUser::_getPersonalPicturePath(
                                $a_user->getId(),
                                "small",
                                true,
                                true
                            );
                            if ($im != "") {
                                $ii->setImage($im);
                                $ii->setAlt($lng->txt("personal_picture"));
                            }
                        }

                        $a_form->addItem($ii);
                    }
                    break;

                case "roles":
                    $role_names = "";
                    if (self::$mode == self::MODE_DESKTOP) {
                        if (self::userSettingVisible("roles")) {
                            $global_roles = $rbacreview->getGlobalRoles();
                            foreach ($global_roles as $role_id) {
                                if (in_array($role_id, $rbacreview->assignedRoles($a_user->getId()))) {
                                    $roleObj = ilObjectFactory::getInstanceByObjId($role_id);
                                    $role_names .= $roleObj->getTitle() . ", ";
                                    unset($roleObj);
                                }
                            }
                            $dr = new ilNonEditableValueGUI($lng->txt("default_roles"), "ne_dr");
                            $dr->setValue(substr($role_names, 0, -2));
                            $a_form->addItem($dr);
                        }
                    } elseif (self::$mode == self::MODE_REGISTRATION) {
                        if ($registration_settings->roleSelectionEnabled()) {
                            $options = array();
                            foreach (ilObjRole::_lookupRegisterAllowed() as $role) {
                                $options[$role["id"]] = $role["title"];
                            }
                            // registration form validation will take care of missing field / value
                            if ($options) {
                                if (count($options) > 1) {
                                    $ta = new ilSelectInputGUI($lng->txt('default_role'), "usr_" . $f);
                                    $ta->setOptions($options);
                                    $ta->setRequired((bool) $ilSetting->get("require_" . $f));
                                    if (!$ta->getRequired()) {
                                        $ta->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                                    }
                                }
                                // no need for select if only 1 option
                                else {
                                    $ta = new ilHiddenInputGUI("usr_" . $f);
                                    $keys = array_keys($options);
                                    $ta->setValue(array_shift($keys));
                                }
                                $a_form->addItem($ta);
                            }
                        }
                    }
                    break;

                case "second_email":
                case "email":
                    if (self::userSettingVisible($f)) {
                        $em = new ilEMailInputGUI($lng->txt($lv), "usr_" . $f);
                        if ($a_user) {
                            $em->setValue($a_user->$m());
                        }
                        $em->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$em->getRequired() || $em->getValue()) {
                            $em->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        if (self::MODE_REGISTRATION == self::$mode) {
                            $em->setRetype(true);
                        }
                        $a_form->addItem($em);
                    }
                    break;
                case "textarea":
                    if (self::userSettingVisible($f)) {
                        $ta = new ilTextAreaInputGUI($lng->txt($lv), "usr_" . $f);
                        if ($a_user) {
                            $ta->setValue($a_user->$m());
                        }
                        $ta->setRows($p["rows"]);
                        $ta->setCols($p["cols"]);
                        $ta->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$ta->getRequired() || $ta->getValue()) {
                            $ta->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        $a_form->addItem($ta);
                    }
                    break;

                case "password":
                    if (self::$mode == self::MODE_REGISTRATION) {
                        if (!$registration_settings->passwordGenerationEnabled()) {
                            $ta = new ilPasswordInputGUI($lng->txt($lv), "usr_" . $f);
                            $ta->setUseStripSlashes(false);
                            $ta->setRequired(true);
                            $ta->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
                        // $ta->setDisabled($ilSetting->get("usr_settings_disable_".$f));
                        } else {
                            $ta = new ilNonEditableValueGUI($lng->txt($lv));
                            $ta->setValue($lng->txt("reg_passwd_via_mail"));
                        }
                        $a_form->addItem($ta);
                    }
                    break;

                case "language":
                    if (self::userSettingVisible($f)) {
                        $ta = new ilSelectInputGUI($lng->txt($lv), "usr_" . $f);
                        if ($a_user) {
                            $ta->setValue($a_user->$m());
                        }
                        $options = array();
                        $lng->loadLanguageModule("meta");
                        foreach ($lng->getInstalledLanguages() as $lang_key) {
                            $options[$lang_key] = $lng->txt("meta_l_" . $lang_key);
                        }
                        asort($options); // #9728
                        $ta->setOptions($options);
                        $ta->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$ta->getRequired() || $ta->getValue()) {
                            $ta->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        $a_form->addItem($ta);
                    }
                    break;

                case "multitext":
                    if (self::userSettingVisible($f)) {
                        $ti = new ilTextInputGUI($lng->txt($lv), "usr_" . $f);
                        $ti->setMulti(true);
                        if ($a_user) {
                            $ti->setValue($a_user->$m());
                        }
                        $ti->setMaxLength($p["maxlength"]);
                        $ti->setSize($p["size"]);
                        $ti->setRequired((bool) $ilSetting->get("require_" . $f));
                        if (!$ti->getRequired() || $ti->getValue()) {
                            $ti->setDisabled((bool) $ilSetting->get("usr_settings_disable_" . $f));
                        }
                        if ($this->ajax_href) {
                            // add field to ajax call
                            $ti->setDataSource($this->ajax_href . "&f=" . $f);
                        }
                        $a_form->addItem($ti);
                    }
                    break;
                case "noneditable":
                    if (self::$mode == self::MODE_DESKTOP && self::userSettingVisible($f)) {
                        $ne = new ilNonEditableValueGUI($lng->txt($lv));
                        $ne->setValue($a_user->$m());
                        $a_form->addItem($ne);
                    }
                    break;
            }
        }

        // append custom fields as "other"
        if (is_array($custom_fields) && !$custom_fields_done) {
            // add "other" subheader
            if ($current_group != "other") {
                $sh = new ilFormSectionHeaderGUI();
                $sh->setTitle($lng->txt("other"));
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

    /**
     * Checks whether user setting is visible
     */
    public static function userSettingVisible(string $a_setting): bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];


        $user_settings_config = new ilUserSettingsConfig();

        if (self::$mode == self::MODE_DESKTOP) {
            return ($user_settings_config->isVisible($a_setting));
        } else {
            if (isset(self::$user_field[$a_setting]["visib_reg_hide"]) && self::$user_field[$a_setting]["visib_reg_hide"] === true) {
                return true;
            }
            return ($ilSetting->get("usr_settings_visib_reg_" . $a_setting, "1") || $ilSetting->get("require_" . $a_setting, "0"));
        }
    }

    public static function setMode(int $mode): bool
    {
        if (in_array($mode, array(self::MODE_DESKTOP, self::MODE_REGISTRATION))) {
            self::$mode = $mode;
            return true;
        }
        return false;
    }

    /**
     * Check if all required personal data fields are set
     *
     * @param ilObjUser $a_user
     * @param bool $a_include_udf check custom fields, too
     * @param bool $a_personal_data_only only check fields which are visible in personal data
     * @return bool
     */
    public static function isProfileIncomplete(
        ilObjUser $a_user,
        bool $a_include_udf = true,
        bool $a_personal_data_only = true
    ): bool {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $user_settings_config = new ilUserSettingsConfig();

        // standard fields
        foreach (self::$user_field as $field => $definition) {
            // only if visible in personal data
            if ($a_personal_data_only && !$user_settings_config->isVisible($field)) {
                continue;
            }

            if ($ilSetting->get("require_" . $field) && $definition["method"]) {
                $value = $a_user->{$definition["method"]}();
                if ($value == "") {
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
                if ($a_personal_data_only && !$definition["visible"]) {
                    continue;
                }

                if (!($user_defined_data["f_" . $field] ?? false)) {
                    ilLoggerFactory::getLogger('user')->info('Profile is incomplete due to missing required udf.');
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns whether a profile setting is editable by an user in the profile gui
     */
    protected static function isEditableByUser(string $setting): bool
    {
        $user_settings_config = new ilUserSettingsConfig();
        return $user_settings_config->isVisibleAndChangeable($setting);
    }

    /**
     * Returns an array of all ignorable profiel fields
     */
    public static function getIgnorableRequiredSettings(): array // Missing array type.
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $ignorableSettings = array();

        foreach (self::$user_field as $field => $definition) {
            // !!!username and password must not be ignored!!!
            if ('username' == $field ||
                'password' == $field) {
                continue;
            }

            // Field is not required -> continue
            if (!$ilSetting->get('require_' . $field)) {
                continue;
            }

            if (self::isEditableByUser($field)) {
                $ignorableSettings[] = $field;
            }
        }

        return $ignorableSettings;
    }
}
