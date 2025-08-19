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

use ILIAS\COPage\AdvancedEditing\StandardGUIRequest;

/**
 * Class ilObjAdvancedEditingGUI
 *
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @ilCtrl_Calls ilObjAdvancedEditingGUI: ilPermissionGUI, ilRTESettingsGUI
 */
class ilObjAdvancedEditingGUI extends ilObjectGUI
{
    protected ilPropertyFormGUI $form;
    protected string $cgrp = "";
    protected StandardGUIRequest $std_request;
    protected ilComponentRepository $component_repository;
    protected ilObjUser $current_user;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->component_repository = $DIC["component.repository"];
        $this->current_user = $DIC['ilUser'];

        $this->type = "adve";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('adve');
        $this->lng->loadLanguageModule('meta');
        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $this->refinery
        );
    }

    public function executeCommand(): void
    {
        if (!$this->rbac_system->checkAccess('read', $this->object->getRefId())) {
            $mess = str_replace("%s", $this->object->getTitle(), $this->lng->txt("msg_no_perm_read_item"));
            $this->ilias->raiseError($mess, $this->ilias->error_obj->WARNING);
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilRTESettingsGUI::class):
                $rte_gui = new ilRTESettingsGUI(
                    $this->ref_id,
                    $this->tpl,
                    $this->ctrl,
                    $this->lng,
                    $this->access,
                    $this->current_user,
                    $this->tabs_gui
                );
                $rte_gui->$cmd();
                break;

            default:
                if ($cmd === null || $cmd === "" || $cmd === "view") {
                    $cmd = "showGeneralPageEditorSettings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    public function saveObject(): void
    {
        $this->checkPermission("write");

        parent::saveObject();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this);
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    public function addPageEditorSettingsSubtabs(): void
    {
        $this->tabs_gui->addSubTabTarget(
            "adve_pe_general",
            $this->ctrl->getLinkTarget($this, "showGeneralPageEditorSettings"),
            array("showGeneralPageEditorSettings", "", "view")
        );

        $grps = ilPageEditorSettings::getGroups();

        foreach ($grps as $g => $types) {
            $this->ctrl->setParameter($this, "grp", $g);
            $this->tabs_gui->addSubTabTarget(
                "adve_grp_" . $g,
                $this->ctrl->getLinkTarget($this, "showPageEditorSettings"),
                array("showPageEditorSettings")
            );
        }
        $this->ctrl->setParameter($this, "grp", $this->std_request->getGroup());
    }

    protected function getTabs(): void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "adve_page_editor_settings",
                $this->ctrl->getLinkTarget($this, "showGeneralPageEditorSettings"),
                array("showPageEditorSettings", "","view")
            );

            $this->tabs_gui->addTarget(
                "adve_rte_settings",
                $this->ctrl->getLinkTargetByClass([self::class, ilRTESettingsGUI::class], "settings"),
                array("settings","assessment", "frmPost"),
                "",
                ""
            );
        }

        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    public function showPageEditorSettingsObject(): void
    {
        $this->addPageEditorSettingsSubtabs();

        $grps = ilPageEditorSettings::getGroups();

        $this->cgrp = $this->std_request->getGroup();
        if ($this->cgrp === "") {
            $this->cgrp = (string) key($grps);
        }

        $this->ctrl->setParameter($this, "grp", $this->cgrp);
        $this->tabs_gui->setSubTabActive("adve_grp_" . $this->cgrp);

        $this->initPageEditorForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function initPageEditorForm(): void
    {
        $this->lng->loadLanguageModule("content");

        $this->form = new ilPropertyFormGUI();

        if ($this->cgrp === "test") {
            $this->form->setTitle($this->lng->txt("adve_activation"));
            $cb = new ilCheckboxInputGUI($this->lng->txt("advanced_editing_tst_editing"), "tst_page_edit");
            $cb->setInfo($this->lng->txt("advanced_editing_tst_editing_desc"));
            if ($this->settings->get("enable_tst_page_edit", ilObjTestFolder::ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_DISABLED)) {
                $cb->setChecked(true);
            }
            $this->form->addItem($cb);

            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($this->lng->txt("adve_text_content_features"));
            $this->form->addItem($sh);
        } elseif ($this->cgrp === "rep") {
            $this->form->setTitle($this->lng->txt("adve_activation"));
            $cb = new ilCheckboxInputGUI($this->lng->txt("advanced_editing_rep_page_editing"), "cat_page_edit");
            $cb->setInfo($this->lng->txt("advanced_editing_rep_page_editing_desc"));
            if ($this->settings->get("enable_cat_page_edit")) {
                $cb->setChecked(true);
            }
            $this->form->addItem($cb);

            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($this->lng->txt("adve_text_content_features"));
            $this->form->addItem($sh);
        } else {
            $this->form->setTitle($this->lng->txt("adve_text_content_features"));
        }


        $buttons = ilPageContentGUI::_getCommonBBButtons();
        foreach ($buttons as $b => $t) {
            // command button activation
            $cb = new ilCheckboxInputGUI(str_replace(":", "", $this->lng->txt("cont_text_" . $b)), "active_" . $b);
            $cb->setChecked((bool) ilPageEditorSettings::lookupSetting($this->cgrp, "active_" . $b, true));
            $this->form->addItem($cb);
        }

        // save and cancel commands
        if ($this->checkPermissionBool("write")) {
            $this->form->addCommandButton("savePageEditorSettings", $this->lng->txt("save"));
        }

        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    public function savePageEditorSettingsObject(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        $this->checkPermission("write");

        $this->initPageEditorForm();
        if ($this->form->checkInput()) {
            $buttons = ilPageContentGUI::_getCommonBBButtons();
            foreach ($buttons as $b => $t) {
                ilPageEditorSettings::writeSetting(
                    $this->std_request->getGroup(),
                    "active_" . $b,
                    $this->form->getInput("active_" . $b)
                );
            }

            if ($this->std_request->getGroup() === "test") {
                $ilSetting->set("enable_tst_page_edit", (string) $this->form->getInput("tst_page_edit"));
            } elseif ($this->std_request->getGroup() === "rep") {
                $ilSetting->set("enable_cat_page_edit", (string) $this->form->getInput("cat_page_edit"));
            }

            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->setParameter($this, "grp", $this->std_request->getGroup());
        $ilCtrl->redirect($this, "showPageEditorSettings");
    }

    public function showGeneralPageEditorSettingsObject(): void
    {
        $this->addPageEditorSettingsSubtabs();
        $this->tabs_gui->activateTab("adve_page_editor_settings");

        $form = $this->initGeneralPageSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function initGeneralPageSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        $aset = new ilSetting("adve");

        // use physical character styles
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_use_physical"), "use_physical");
        $cb->setInfo($this->lng->txt("adve_use_physical_info"));
        $cb->setChecked((bool) $aset->get("use_physical"));
        $form->addItem($cb);

        // blocking mode
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_blocking_mode"), "block_mode_act");
        $cb->setChecked((bool) $aset->get("block_mode_minutes") > 0);
        $form->addItem($cb);

        // number of minutes
        $ni = new ilNumberInputGUI($this->lng->txt("adve_minutes"), "block_mode_minutes");
        $ni->setMinValue(2);
        $ni->setMaxLength(5);
        $ni->setSize(5);
        $ni->setRequired(true);
        $ni->setInfo($this->lng->txt("adve_minutes_info"));
        $ni->setValue($aset->get("block_mode_minutes"));
        $cb->addSubItem($ni);

        // autosave
        $as = new ilNumberInputGUI($this->lng->txt("adve_autosave"), "autosave");
        $as->setSuffix($this->lng->txt("seconds"));
        $as->setMaxLength(5);
        $as->setSize(5);
        $as->setInfo($this->lng->txt("adve_autosave_info"));
        $as->setValue($aset->get("autosave"));
        $form->addItem($as);

        // auto url linking
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_auto_url_linking"), "auto_url_linking");
        $cb->setChecked((bool) $aset->get("auto_url_linking"));
        $cb->setInfo($this->lng->txt("adve_auto_url_linking_info"));
        $form->addItem($cb);

        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton("saveGeneralPageSettings", $this->lng->txt("save"));
        }

        // workaround for glossaries to force rewriting of short texts
        ilGlossaryTerm::setShortTextsDirtyGlobally();


        $form->setTitle($this->lng->txt("adve_pe_general"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * This limits the possibility to allow html for these page objects
     * that supported the feature in the past.
     *
     * PLEASE do not add additional keys here. The whole feature might be abandonded in
     * the future.
     */
    protected function getPageObjectKeysWithOptionalHTML(): array
    {
        return ["lobj","copa","mep","blp","prtf","prtt","term","lm","qht","qpl","qfbg","qfbs","sahs","stys","cont","cstr","auth"];
    }

    public function saveGeneralPageSettingsObject(): void
    {
        $this->checkPermission("write");

        $form = $this->initGeneralPageSettingsForm();
        if ($form->checkInput()) {
            $autosave = (int) $form->getInput("autosave");
            $ok = true;

            // autosave must be greater 10, if activated
            if ($autosave > 0 && $autosave < 10) {
                $form->getItemByPostVar("autosave")->setAlert($this->lng->txt("adve_autosave_info_min_10"));
                $ok = false;
            }

            if ($ok) {
                $aset = new ilSetting("adve");
                $aset->set("use_physical", $form->getInput("use_physical"));
                if ($form->getInput("block_mode_act")) {
                    $aset->set("block_mode_minutes", (string) (int) $form->getInput("block_mode_minutes"));
                } else {
                    $aset->set("block_mode_minutes", 0);
                }
                $aset->set("auto_url_linking", $form->getInput("auto_url_linking"));

                $aset->set("autosave", $form->getInput("autosave"));

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->redirect($this, "showGeneralPageEditorSettings");
            }
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }
}
