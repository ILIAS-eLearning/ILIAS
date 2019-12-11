<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjSurveyAdministrationGUI
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjSurveyAdministrationGUI: ilPermissionGUI, ilSettingsTemplateGUI
*
* @extends ilObjectGUI
* @ingroup ModulesSurvey
*
*/
class ilObjSurveyAdministrationGUI extends ilObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
    * Constructor
    * @access public
    */
    public $conditions;

    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->type = "svyf";
        $lng->loadLanguageModule("survey");
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }
    
    public function executeCommand()
    {
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $ilTabs->activateTab("perm_settings");
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret =&$this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilsettingstemplategui':
                $ilTabs->activateTab("templates");
                include_once("./Services/Administration/classes/class.ilSettingsTemplateGUI.php");
                $set_tpl_gui = new ilSettingsTemplateGUI($this->getSettingsTemplateConfig());
                $this->ctrl->forwardCommand($set_tpl_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }


    /**
    * display survey settings form
    *
    * Default settings tab for Survey settings
    *
    * @access	public
    */
    public function settingsObject(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("settings");
        
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function initSettingsForm()
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $surveySetting = new ilSetting("survey");
        $unlimited_invitation = array_key_exists("unlimited_invitation", $_GET) ? $_GET["unlimited_invitation"] : $surveySetting->get("unlimited_invitation");
        $use_anonymous_id = array_key_exists("use_anonymous_id", $_GET) ? $_GET["use_anonymous_id"] : $surveySetting->get("use_anonymous_id");
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("survey_defaults"));
        
        // unlimited invitation
        $enable = new ilCheckboxInputGUI($lng->txt("survey_unlimited_invitation"), "unlimited_invitation");
        $enable->setChecked($unlimited_invitation);
        $enable->setInfo($lng->txt("survey_unlimited_invitation_desc"));
        $form->addItem($enable);
        
        // Survey Code
        $code = new ilCheckboxInputGUI($lng->txt("use_anonymous_id"), "use_anonymous_id");
        $code->setChecked($use_anonymous_id);
        $code->setInfo($lng->txt("use_anonymous_id_desc"));
        $form->addItem($code);
        
        // Skipped
        $eval_skipped = new ilRadioGroupInputGUI($lng->txt("svy_eval_skipped_value"), "skcust");
        $eval_skipped->setRequired(true);
        $form->addItem($eval_skipped);
        
        $eval_skipped->setValue($surveySetting->get("skipped_is_custom", false)
            ? "cust"
            : "lng");
        
        $skipped_lng = new ilRadioOption($lng->txt("svy_eval_skipped_value_lng"), "lng");
        $skipped_lng->setInfo(sprintf($lng->txt("svy_eval_skipped_value_lng_info"), $lng->txt("skipped")));
        $eval_skipped->addOption($skipped_lng);
        $skipped_cust = new ilRadioOption($lng->txt("svy_eval_skipped_value_custom"), "cust");
        $skipped_cust->setInfo($lng->txt("svy_eval_skipped_value_custom_info"));
        $eval_skipped->addOption($skipped_cust);
        
        $skipped_cust_value = new ilTextInputGUI($lng->txt("svy_eval_skipped_value_custom_value"), "cust_value");
        $skipped_cust_value->setSize(15);
        $skipped_cust_value->setValue($surveySetting->get("skipped_custom_value", ""));
        $skipped_cust->addSubItem($skipped_cust_value);
        
        $anon_part = new ilCheckboxInputGUI($lng->txt("svy_anonymous_participants"), "anon_part");
        $anon_part->setInfo($lng->txt("svy_anonymous_participants_info"));
        $anon_part->setChecked($surveySetting->get("anonymous_participants", false));
        $form->addItem($anon_part);
        
        $anon_part_min = new ilNumberInputGUI($lng->txt("svy_anonymous_participants_min"), "anon_part_min");
        $anon_part_min->setInfo($lng->txt("svy_anonymous_participants_min_info"));
        $anon_part_min->setSize(4);
        $anon_part_min->setMinValue(1);
        $anon_part_min->setValue($surveySetting->get("anonymous_participants_min", null));
        $anon_part->addSubItem($anon_part_min);

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $lng->txt("save"));
        }
        
        return $form;
    }
    
    /**
    * Save survey settings
    */
    public function saveSettingsObject()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilCtrl->redirect($this, "settings");
        }
        
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $surveySetting = new ilSetting("survey");
            $surveySetting->set("unlimited_invitation", ($_POST["unlimited_invitation"]) ? "1" : "0");
            $surveySetting->set("use_anonymous_id", ($_POST["use_anonymous_id"]) ? "1" : "0");
            $surveySetting->set("anonymous_participants", ($_POST["anon_part"]) ? "1" : "0");
            $surveySetting->set("anonymous_participants_min", (trim($_POST["anon_part_min"])) ? (int) $_POST["anon_part_min"] : null);

            if ($_POST["skcust"] == "lng") {
                $surveySetting->set("skipped_is_custom", false);
            } else {
                $surveySetting->set("skipped_is_custom", true);
                $surveySetting->set("skipped_custom_value", trim($_POST["cust_value"]));
            }

            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "settings");
        }
        
        $form->setValuesByPost();
        $this->settingsObject($form);
    }
    
    public function getAdminTabs()
    {
        $this->getTabs();
    }

    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        $lng = $this->lng;

        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "settings")
            );

            // #7927: special users are deprecated
            /*
            $tabs_gui->addTab("specialusers",
                $lng->txt("specialusers"),
                $this->ctrl->getLinkTarget($this, "specialusers"));
            */

            if ($this->checkPermissionBool("write")) {
                $this->tabs_gui->addTab(
                    "templates",
                    $lng->txt("adm_settings_templates"),
                    $this->ctrl->getLinkTargetByClass("ilsettingstemplategui", "")
                );
            }
        }
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    /**
     * Get settings template configuration object
     *
     * @return object settings template configuration object
     */
    private function getSettingsTemplateConfig()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("survey");
        include_once "Modules/Survey/classes/class.ilObjSurvey.php";

        include_once("./Services/Administration/classes/class.ilSettingsTemplateConfig.php");
        $config = new ilSettingsTemplateConfig("svy");

        $config->addHidableTab("survey_question_editor", $lng->txt("survey_question_editor_settings_template"));
        $config->addHidableTab("constraints", $lng->txt("constraints"));
        $config->addHidableTab("invitation", $lng->txt("invitation"));
        $config->addHidableTab("meta_data", $lng->txt("meta_data"));
        $config->addHidableTab("export", $lng->txt("export"));

        $config->addSetting(
            "use_pool",
            ilSettingsTemplateConfig::SELECT,
            $lng->txt("survey_question_pool_usage"),
            true,
            0,
            array(1 => $this->lng->txt("survey_question_pool_usage_active"),
                0 => $this->lng->txt("survey_question_pool_usage_inactive"))
        );
        
        
        $config->addSetting(
            "enabled_start_date",
            ilSettingsTemplateConfig::BOOL,
            $lng->txt("start_date"),
            true
        );

        $config->addSetting(
            "enabled_end_date",
            ilSettingsTemplateConfig::BOOL,
            $lng->txt("end_date"),
            true
        );

        $config->addSetting(
            "show_question_titles",
            ilSettingsTemplateConfig::BOOL,
            $lng->txt("svy_show_questiontitles"),
            true
        );

        
        // #17585
        
        $config->addSetting(
            "acc_codes",
            ilSettingsTemplateConfig::BOOL,
            $lng->txt("survey_access_codes"),
            true
        );
        
        $config->addSetting(
            "evaluation_access",
            ilSettingsTemplateConfig::SELECT,
            $lng->txt("evaluation_access"),
            true,
            0,
            array(ilObjSurvey::EVALUATION_ACCESS_OFF => $this->lng->txt("evaluation_access_off"),
                ilObjSurvey::EVALUATION_ACCESS_ALL => $this->lng->txt("evaluation_access_all"),
                ilObjSurvey::EVALUATION_ACCESS_PARTICIPANTS => $this->lng->txt("evaluation_access_participants"))
        );
        
        $config->addSetting(
            "anonymization_options",
            ilSettingsTemplateConfig::SELECT,
            $lng->txt("survey_results_anonymization"),
            true,
            0,
            array("statpers" => $this->lng->txt("survey_results_personalized"),
                "statanon" => $this->lng->txt("survey_results_anonymized"))
        );
        
        /*
        $config->addSetting(
            "rte_switch",
            ilSettingsTemplateConfig::SELECT,
            $lng->txt("set_edit_mode"),
            true,
            0,
            array(0 => $this->lng->txt("rte_editor_disabled"),
                1 => $this->lng->txt("rte_editor_enabled"))
            );
        */
    
        return $config;
    }
} // END class.ilObjSurveyAdministrationGUI
