<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilUserFieldSettingsTableGUI extends ilTable2GUI
{
    private $confirm_change = false;
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        
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

        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $up->skipField("username");
        $fds = $up->getStandardFields();
        // vd($fds);
        foreach ($fds as $k => $f) {
            $fds[$k]["key"] = $k;
        }
        $this->setData($fds);
        $this->addCommandButton("saveGlobalUserSettings", $lng->txt("save"));
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilSetting = $DIC['ilSetting'];
        
        $field = $a_set["key"];
        
        $props = array(
            "visible" => "user_visible_in_profile",
            "changeable" => "changeable",
            "searchable" => "header_searchable",
            "required"  => "required_field",
            "export" => "export",
            "course_export" => "course_export",
            'group_export' => 'group_export',
            "visib_reg" => "header_visible_registration",
            'visib_lua' => 'usr_settings_visib_lua',
            'changeable_lua' => 'usr_settings_changeable_lua'
        );
        
        foreach ($props as $prop => $lv) {
            $up_prop = strtoupper($prop);
            
            if (($prop != "searchable" && $a_set[$prop . "_hide"] != true) ||
                ($prop == "searchable" && ilUserSearchOptions::_isSearchable($field))) {
                $this->tpl->setCurrentBlock($prop);
                $this->tpl->setVariable(
                    "HEADER_" . $up_prop,
                    $lng->txt($lv)
                );
                $this->tpl->setVariable("PROFILE_OPTION_" . $up_prop, $prop . "_" . $field);
                
                // determine checked status
                $checked = false;
                if ($prop == "visible" && $ilSetting->get("usr_settings_hide_" . $field) != "1") {
                    $checked = true;
                }
                if ($prop == "changeable" && $ilSetting->get("usr_settings_disable_" . $field) != "1") {
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
                    $checked = $_POST["chb"][$prop . "_" . $field];
                }
                if (isset($a_set[$prop . "_fix_value"])) {	// fix values overwrite everything
                    $checked = $a_set[$prop . "_fix_value"];
                }
    
                if ($checked) {
                    $this->tpl->setVariable("CHECKED_" . $up_prop, " checked=\"checked\"");
                }
                if (isset($a_set[$prop . "_fix_value"])) {
                    $this->tpl->setVariable("DISABLE_" . $up_prop, " disabled=\"disabled\"");
                }
                $this->tpl->parseCurrentBlock();
            }
        }
        
        // default
        if ($a_set["default"] != "") {
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
        $lv = ($a_set["lang_var"] == "")
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
    
    public function setConfirmChange()
    {
        $this->confirm_change = true;
    }
}
