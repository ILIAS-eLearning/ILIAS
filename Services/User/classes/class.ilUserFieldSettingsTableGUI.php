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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserFieldSettingsTableGUI extends ilTable2GUI
{
    private bool $confirm_change = false;
    protected \ILIAS\User\StandardGUIRequest $user_request;

    protected ilUserSettingsConfig $user_settings_config;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->user_settings_config = new ilUserSettingsConfig();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("usr_settings_header_profile"));
        $this->setDescription($lng->txt("usr_settings_explanation_profile"));
        $this->setLimit(9999);

        //$this->addColumn($this->lng->txt("usrs_group"), "");
        //$this->addColumn("", "");
        $this->addColumn($this->lng->txt("user_field"), "");
        $this->addColumn($this->lng->txt("access"), "");
        $this->addColumn($this->lng->txt("export") . " / " . $this->lng->txt("search"), "");
        $this->addColumn($this->lng->txt("default"), "");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.std_fields_settings_row.html", "Services/User");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $up = new ilUserProfile();
        $up->skipField("username");
        $fds = $up->getStandardFields();
        foreach ($fds as $k => $f) {
            $fds[$k]["key"] = $k;
        }
        $this->setData($fds);
        $this->addCommandButton("saveGlobalUserSettings", $lng->txt("save"));

        $this->user_request = new \ILIAS\User\StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    /**
     * @param array<string,mixed> $a_set
     * @throws ilTemplateException
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];
        $user_settings_config = $this->user_settings_config;
        $req_checked = $this->user_request->getChecked();

        $field = $a_set["key"];

        foreach (ilObjUserFolderGUI::USER_FIELD_TRANSLATION_MAPPING as $prop => $lv) {
            $up_prop = strtoupper($prop);

            if (($prop != "searchable" && ($a_set[$prop . "_hide"] ?? false) != true) ||
                ($prop == "searchable" && ilUserSearchOptions::_isSearchable($field))) {
                $this->tpl->setCurrentBlock($prop);
                $this->tpl->setVariable(
                    "HEADER_" . $up_prop,
                    $lng->txt($lv)
                );
                $this->tpl->setVariable("PROFILE_OPTION_" . $up_prop, $prop . "_" . $field);

                // determine checked status
                $checked = false;
                if ($prop == "visible" && $user_settings_config->isVisible($field)) {
                    $checked = true;
                }
                if ($prop == "changeable" && $user_settings_config->isChangeable($field)) {
                    $checked = true;
                }
                if ($prop == "searchable" && ilUserSearchOptions::_isEnabled($field)) {
                    $checked = true;
                }
                if ($prop == "required" && $ilSetting->get("require_" . $field) == "1") {
                    $checked = true;
                }
                if ($prop == "export" && $ilSetting->get("usr_settings_export_" . $field) == "1") {
                    $checked = true;
                }
                if ($prop == "course_export" && $ilSetting->get("usr_settings_course_export_" . $field) == "1") {
                    $checked = true;
                }
                if ($prop == "group_export" && $ilSetting->get("usr_settings_group_export_" . $field) == "1") {
                    $checked = true;
                }
                if ($prop == "visib_reg" && (int) $ilSetting->get('usr_settings_visib_reg_' . $field, '1')) {
                    $checked = true;
                }
                if ($prop == "visib_lua" && (int) $ilSetting->get('usr_settings_visib_lua_' . $field, '1')) {
                    $checked = true;
                }

                if ($prop == "changeable_lua" && (int) $ilSetting->get('usr_settings_changeable_lua_' . $field, '1')) {
                    $checked = true;
                }


                if ($this->confirm_change == 1) {	// confirm value
                    $checked = $req_checked[$prop . "_" . $field];
                }
                if (isset($a_set[$prop . "_fix_value"])) {	// fix values overwrite everything
                    $checked = $a_set[$prop . "_fix_value"];
                }

                if ($checked) {
                    $this->tpl->setVariable("CHECKED_" . $up_prop, " checked=\"checked\"");
                    if (!isset($a_set["{$prop}_fix_value"])) {
                        $this->tpl->setVariable("CURRENT_OPTION_VISIBLE", "1");
                    }
                } else {
                    $this->tpl->setVariable("CURRENT_OPTION_VISIBLE", "0");
                }

                if (isset($a_set[$prop . "_fix_value"])) {
                    $this->tpl->setVariable("DISABLE_" . $up_prop, " disabled=\"disabled\"");
                }
                $this->tpl->parseCurrentBlock();
            }
        }

        // default
        if (($a_set["default"] ?? "") != "") {
            switch ($a_set["input"]) {
                case "selection":
                case "hitsperpage":
                    $selected_option = $ilSetting->get($field);
                    if ($selected_option == "") {
                        $selected_option = $a_set["default"];
                    }
                    foreach ($a_set["options"] as $k => $v) {
                        $this->tpl->setCurrentBlock("def_sel_option");
                        $this->tpl->setVariable("OPTION_VALUE", $k);
                        $text = ($a_set["input"] == "selection")
                            ? $lng->txt($v)
                            : $v;
                        if ($a_set["input"] == "hitsperpage" && $k == 9999) {
                            $text = $lng->txt("no_limit");
                        }
                        if ($selected_option == $k) {
                            $this->tpl->setVariable(
                                "OPTION_SELECTED",
                                ' selected="selected" '
                            );
                        }
                        $this->tpl->setVariable("OPTION_TEXT", $text);
                        $this->tpl->parseCurrentBlock();
                    }
                    $this->tpl->setCurrentBlock("def_selection");
                    $this->tpl->setVariable("PROFILE_OPTION_DEFAULT_VALUE", "default_" . $field);
                    $this->tpl->parseCurrentBlock();
                    break;
            }
            $this->tpl->setCurrentBlock("default");
            $this->tpl->parseCurrentBlock();
        }

        // group name
        $this->tpl->setVariable("TXT_GROUP", $lng->txt($a_set["group"]));

        // field name
        $lv = (($a_set["lang_var"] ?? "") == "")
            ? $a_set["key"]
            : $a_set["lang_var"];
        if ($a_set["key"] == "country") {
            $lv = "country_free_text";
        }
        if ($a_set["key"] == "sel_country") {
            $lv = "country_selection";
        }

        $this->tpl->setVariable("TXT_FIELD", $lng->txt($lv));
    }

    public function setConfirmChange() : void
    {
        $this->confirm_change = true;
    }
}
