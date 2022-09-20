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
 * Awareness tool administration
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjAwarenessAdministrationGUI: ilPermissionGUI, ilUserActionAdminGUI
 * @ilCtrl_IsCalledBy ilObjAwarenessAdministrationGUI: ilAdministrationGUI
 */
class ilObjAwarenessAdministrationGUI extends ilObjectGUI
{
    protected \ILIAS\Awareness\AdminManager $admin_manager;

    /**
     * @param mixed $a_data
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->type = "awra";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("awrn");
        $this->lng->loadLanguageModule("pd");
        $this->lng->loadLanguageModule("usr");
        $this->admin_manager = $DIC->awareness()
            ->internal()
            ->domain()
            ->admin($this->requested_ref_id);
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'iluseractionadmingui':
                $gui = new ilUserActionAdminGUI($this->requested_ref_id);
                $gui->setActionContext(new ilAwarenessUserActionContext());
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("actions");
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
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
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    public function setSubTabs(string $a_id): void
    {
        $this->tabs_gui->addSubTab(
            "settings",
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, "editSettings")
        );

        $this->tabs_gui->addSubTab(
            "actions",
            $this->lng->txt("user_actions"),
            $this->ctrl->getLinkTargetByClass("iluseractionadmingui")
        );

        $this->tabs_gui->activateSubTab($a_id);
    }


    /**
     * Edit settings.
     */
    public function editSettings(?ilPropertyFormGUI $a_form = null): bool
    {
        $this->tabs_gui->setTabActive('settings');
        $this->setSubTabs("settings");

        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
        return true;
    }

    /**
     * Save settings
     */
    public function saveSettings(): void
    {
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $awrn_set = new ilSetting("awrn");
            $awrn_set->set("awrn_enabled", (bool) $form->getInput("enable_awareness"));

            $p = (int) $form->getInput("caching_period");
            if ($p < 0) {
                $p = 0;
            }
            $awrn_set->set("caching_period", $p);

            $awrn_set->set("max_nr_entries", (int) $form->getInput("max_nr_entries"));
            $awrn_set->set("use_osd", (int) $form->getInput("use_osd"));

            $pd_set = new ilSetting("pd");
            $pd_set->set("user_activity_time", (int) $form->getInput("time_removal"));

            $prov = $this->admin_manager->getAllUserProviders();
            foreach ($prov as $p) {
                $this->admin_manager->setActivationMode(
                    $p->getProviderId(),
                    (int) $form->getInput("up_act_mode_" . $p->getProviderId())
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "editSettings");
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }

    /**
     * Save settings
     */
    public function cancel(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "view");
    }

    protected function initFormSettings(): ilPropertyFormGUI
    {
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('awareness_settings'));

        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
            $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        }

        $en = new ilCheckboxInputGUI($lng->txt("awrn_enable"), "enable_awareness");
        $form->addItem($en);

        $awrn_set = new ilSetting("awrn");
        $en->setChecked($awrn_set->get("awrn_enabled", false));

        // caching period
        $ti = new ilNumberInputGUI($this->lng->txt("awrn_caching_period"), "caching_period");
        $ti->setInfo($this->lng->txt("awrn_caching_period_info"));
        $ti->setSuffix($this->lng->txt("awrn_seconds"));
        $ti->setSize(6);
        $ti->setMaxLength(6);
        $ti->setValue($awrn_set->get("caching_period"));
        $en->addSubItem($ti);

        // limit number of entries
        $ti = new ilNumberInputGUI($this->lng->txt("awrn_max_nr_entries"), "max_nr_entries");
        $ti->setInfo($this->lng->txt("awrn_max_nr_entries_info"));
        $ti->setSize(3);
        $ti->setMaxLength(3);
        $ti->setMinValue(5);
        $ti->setMaxValue(200);
        $ti->setValue($awrn_set->get("max_nr_entries"));
        $en->addSubItem($ti);

        // maximum inactivity time
        $pd_set = new ilSetting("pd");		// under pd settings due to historical reasons
        $ti_prop = new ilNumberInputGUI(
            $lng->txt("awrn_max_inactivity"),
            "time_removal"
        );
        $ti_prop->setSuffix($this->lng->txt("awrn_minutes"));
        if ($pd_set->get("user_activity_time") > 0) {
            $ti_prop->setValue($pd_set->get("user_activity_time"));
        }
        $ti_prop->setInfo($lng->txt("awrn_max_inactivity_info"));
        $ti_prop->setMaxLength(3);
        $ti_prop->setSize(3);
        $en->addSubItem($ti_prop);

        // activate osd
        $osd = new ilCheckboxInputGUI($this->lng->txt("awrn_use_osd"), "use_osd");
        $osd->setInfo($this->lng->txt("awrn_use_osd_info"));
        $osd->setChecked($awrn_set->get("use_osd", true));
        $en->addSubItem($osd);


        $prov = $this->admin_manager->getAllUserProviders();
        foreach ($prov as $p) {
            // activation mode
            $options = $this->admin_manager->getModeOptions();
            $si = new ilSelectInputGUI($p->getTitle(), "up_act_mode_" . $p->getProviderId());
            $si->setOptions($options);
            $si->setInfo($p->getInfo());
            $si->setValue($this->admin_manager->getActivationMode($p->getProviderId()));
            $en->addSubItem($si);
        }

        return $form;
    }
}
