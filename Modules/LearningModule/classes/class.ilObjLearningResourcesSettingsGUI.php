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
 * Learning Resources Settings.
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjLearningResourcesSettingsGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjLearningResourcesSettingsGUI: ilAdministrationGUI
 */
class ilObjLearningResourcesSettingsGUI extends ilObjectGUI
{
    /**
     * @param mixed $a_data
     * @throws ilCtrlException
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = 'lrss';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('lm');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs(): void
    {
        $rbac_system = $this->rbac_system;

        if ($rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "cont_edit_lrs_settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(?ilPropertyFormGUI $form = null): void
    {
        if (is_null($form)) {
            $form = $this->getSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function getSettingsForm(): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $lm_set = new ilSetting("lm");
        $lng->loadLanguageModule("scormdebug");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("cont_lrs_settings") . " - " . $lng->txt("obj_lm"));

        // Page History
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("cont_enable_page_history"),
            "page_history"
        );
        $cb_prop->setInfo($lng->txt("cont_enable_page_history_info"));
        $cb_prop->setChecked((bool) $lm_set->get("page_history", '1'));
        $form->addItem($cb_prop);

        // Time scheduled page activation
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("cont_enable_time_scheduled_page_activation"),
            "time_scheduled_page_activation"
        );
        $cb_prop->setInfo($lng->txt("cont_enable_time_scheduled_page_activation_info"));
        $cb_prop->setChecked((bool) $lm_set->get("time_scheduled_page_activation"));
        $form->addItem($cb_prop);

        // lm starting point
        $options = array(
            "" => $this->lng->txt("cont_last_visited_page"),
            "first" => $this->lng->txt("cont_first_page")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_lm_starting_point"), "lm_starting_point");
        $si->setOptions($options);
        $si->setValue($lm_set->get("lm_starting_point"));
        $si->setInfo($this->lng->txt("cont_lm_starting_point_info"));
        $form->addItem($si);

        // Activate replace media object function
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("cont_replace_mob_feature"),
            "replace_mob_feature"
        );
        $cb_prop->setInfo($lng->txt("cont_replace_mob_feature_info"));
        $cb_prop->setChecked((bool) $lm_set->get("replace_mob_feature"));
        $form->addItem($cb_prop);

        // Activate HTML export IDs
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("cont_html_export_ids"),
            "html_export_ids"
        );
        $cb_prop->setInfo($lng->txt("cont_html_export_ids_info"));
        $cb_prop->setChecked((bool) $lm_set->get("html_export_ids"));
        $form->addItem($cb_prop);

        // Activate replace media object function
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("lm_est_reading_time"),
            "lm_est_reading_time"
        );
        $cb_prop->setInfo($lng->txt("lm_est_reading_time_info"));
        $cb_prop->setChecked((bool) $lm_set->get("est_reading_time"));
        $form->addItem($cb_prop);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("cont_lrs_settings") . " - " . $lng->txt("obj_sahs"));
        $form->addItem($sh);

        // scormDebugger activation
        $cb_prop = new ilCheckboxInputGUI($lng->txt("scormdebug_global_activate"), "scormdebug_global_activate");
        $cb_prop->setInfo($lng->txt("scormdebug_global_activate_info"));
        $cb_prop->setChecked((bool) $lm_set->get("scormdebug_global_activate"));
        $form->addItem($cb_prop);

        // scorm2004 disableRTECaching
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("scormdebug_disable_cache"),
            "scormdebug_disable_cache"
        );
        $cb_prop->setInfo($lng->txt("scormdebug_disable_cache_info"));
        $cb_prop->setChecked((bool) $lm_set->get("scormdebug_disable_cache"));
        $form->addItem($cb_prop);

        // scorm2004 without session
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("scorm_without_session"),
            "scorm_without_session"
        );
        $cb_prop->setInfo($lng->txt("scorm_without_session_info"));
        $cb_prop->setChecked((bool) $lm_set->get("scorm_without_session"));
        $form->addItem($cb_prop);

        $privacy = ilPrivacySettings::getInstance();
        $check = new ilCheckboxInputGui($lng->txt('enable_sahs_protocol_data'), 'enable_sahs_pd');
        $check->setInfo($this->lng->txt('enable_sahs_protocol_data_desc'));
        $check->setChecked((bool) $privacy->enabledSahsProtocolData());
        $form->addItem($check);

        // show and export protocol data with name
        $check = new ilCheckboxInputGui($this->lng->txt('ps_export_scorm'), 'export_scorm');
        $check->setInfo($this->lng->txt('enable_export_scorm_desc'));
        $check->setChecked($privacy->enabledExportSCORM());
        $form->addItem($check);

        // scorm auto-setting for learning progress
        $cb_prop = new ilCheckboxInputGUI($lng->txt("scorm_lp_auto_activate"), "scorm_lp_auto_activate");
        $cb_prop->setInfo($lng->txt("scorm_lp_auto_activate_info"));
        $cb_prop->setChecked((bool) $lm_set->get("scorm_lp_auto_activate"));
        $form->addItem($cb_prop);

        // command buttons
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton("saveSettings", $lng->txt("save"));
            $form->addCommandButton("view", $lng->txt("cancel"));
        }
        return $form;
    }

    public function saveSettings(): void
    {
        $ilCtrl = $this->ctrl;

        $form = $this->getSettingsForm();

        $this->checkPermission("write");

        if ($form->checkInput()) {
            $lm_set = new ilSetting("lm");
            $lm_set->set(
                "time_scheduled_page_activation",
                $form->getInput("time_scheduled_page_activation")
            );
            $lm_set->set(
                "lm_starting_point",
                $form->getInput("lm_starting_point")
            );
            $lm_set->set(
                "page_history",
                $form->getInput("page_history")
            );
            $lm_set->set(
                "replace_mob_feature",
                $form->getInput("replace_mob_feature")
            );
            $lm_set->set(
                "html_export_ids",
                $form->getInput("html_export_ids")
            );
            $lm_set->setScormDebug(
                "scormdebug_global_activate",
                $form->getInput("scormdebug_global_activate")
            );
            $lm_set->set(
                "scorm_login_as_learner_id",
                $form->getInput("scorm_login_as_learner_id")
            );
            $lm_set->set(
                "scormdebug_disable_cache",
                $form->getInput("scormdebug_disable_cache")
            );
            $lm_set->set(
                "scorm_without_session",
                $form->getInput("scorm_without_session")
            );
            $lm_set->set(
                "scorm_lp_auto_activate",
                $form->getInput("scorm_lp_auto_activate")
            );
            $lm_set->set(
                "est_reading_time",
                $form->getInput("lm_est_reading_time")
            );


            $privacy = ilPrivacySettings::getInstance();
            $privacy->enableSahsProtocolData((int) $form->getInput('enable_sahs_pd'));
            $privacy->enableExportSCORM((int) $form->getInput('export_scorm'));
            $privacy->save();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "view");
        } else {
            $form->setValuesByPost();
            $this->editSettings($form);
        }
    }

    public function addToExternalSettingsForm(int $a_form_id): array
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_PRIVACY:

                $privacy = ilPrivacySettings::getInstance();

                $fields = array('enable_sahs_protocol_data' => array($privacy->enabledSahsProtocolData(), ilAdministrationSettingsFormHandler::VALUE_BOOL));

                return array(array("editSettings", $fields));
        }
        return [];
    }
}
