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
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilObjSurveyAdministrationGUI: ilPermissionGUI
 */
class ilObjSurveyAdministrationGUI extends ilObjectGUI
{
    protected ilTabsGUI $tabs;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
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
    
    public function executeCommand() : void
    {
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $ilTabs->activateTab("perm_settings");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd === null || $cmd === "" || $cmd === "view") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    /**
     * Display survey settings form
     */
    public function settingsObject(
        ilPropertyFormGUI $a_form = null
    ) : void {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("settings");
        
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $surveySetting = new ilSetting("survey");
        $use_anonymous_id = (bool) $surveySetting->get("use_anonymous_id");
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("survey_defaults"));

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
        $anon_part->setChecked((bool) $surveySetting->get("anonymous_participants", '0'));
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
    
    public function saveSettingsObject() : void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilCtrl->redirect($this, "settings");
        }
        
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $surveySetting = new ilSetting("survey");
            $surveySetting->set("use_anonymous_id", $form->getInput("use_anonymous_id") ? "1" : "0");
            $surveySetting->set("anonymous_participants", $form->getInput("anon_part") ? "1" : "0");
            $surveySetting->set(
                "anonymous_participants_min",
                (trim($form->getInput("anon_part_min")))
                    ? (string) (int) $form->getInput("anon_part_min")
                    : ""
            );

            if ($form->getInput("skcust") === "lng") {
                $surveySetting->set("skipped_is_custom", false);
            } else {
                $surveySetting->set("skipped_is_custom", true);
                $surveySetting->set("skipped_custom_value", trim($form->getInput("cust_value")));
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "settings");
        }
        
        $form->setValuesByPost();
        $this->settingsObject($form);
    }
    
    public function getAdminTabs() : void
    {
        $this->getTabs();
    }

    protected function getTabs() : void
    {
        $lng = $this->lng;

        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "settings")
            );
        }
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }
}
