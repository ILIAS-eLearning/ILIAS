<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\AdvancedEditing\StandardGUIRequest;

/**
 * Class ilObjAdvancedEditingGUI
 *
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @ilCtrl_Calls ilObjAdvancedEditingGUI: ilPermissionGUI
 */
class ilObjAdvancedEditingGUI extends ilObjectGUI
{
    protected ilPropertyFormGUI $form;
    protected string $cgrp = "";
    protected ilRbacAdmin $rbacadmin;
    protected ilTabsGUI $tabs;
    protected StandardGUIRequest $std_request;
    protected ilComponentRepository $component_repository;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $this->component_repository = $DIC["component.repository"];

        $this->type = "adve";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('adve');
        $this->lng->loadLanguageModule('meta');
        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }
    
    public function executeCommand() : void
    {
        if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
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

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "showGeneralPageEditorSettings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    public function saveObject() : void
    {
        $this->checkPermission("write");

        parent::saveObject();

        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this);
    }

    public function getAdminTabs() : void
    {
        $this->getTabs();
    }
    
    public function addSubtabs() : void
    {
        $ilCtrl = $this->ctrl;

        if ($ilCtrl->getNextClass() != "ilpermissiongui" &&
            !in_array($ilCtrl->getCmd(), array("showPageEditorSettings",
                "showGeneralPageEditorSettings", "showCharSelectorSettings", "", "view"))) {
            $this->tabs_gui->addSubTabTarget(
                "adve_general_settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings", "saveSettings"),
                "",
                ""
            );
            $this->tabs_gui->addSubTabTarget(
                "adve_assessment_settings",
                $this->ctrl->getLinkTarget($this, "assessment"),
                array("assessment", "saveAssessmentSettings"),
                "",
                ""
            );
            $this->tabs_gui->addSubTabTarget(
                "adve_survey_settings",
                $this->ctrl->getLinkTarget($this, "survey"),
                array("survey", "saveSurveySettings"),
                "",
                ""
            );
            $this->tabs_gui->addSubTabTarget(
                "adve_frm_post_settings",
                $this->ctrl->getLinkTarget($this, "frmPost"),
                array("frmPost", "saveFrmPostSettings"),
                "",
                ""
            );
            $this->tabs_gui->addSubTabTarget(
                "adve_excass_settings",
                $this->ctrl->getLinkTarget($this, "excass"),
                array("excass", "saveExcAssSettings"),
                "",
                ""
            );
        }
    }
    
    public function addPageEditorSettingsSubtabs() : void
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilTabs->addSubTabTarget(
            "adve_pe_general",
            $ilCtrl->getLinkTarget($this, "showGeneralPageEditorSettings"),
            array("showGeneralPageEditorSettings", "", "view")
        );

        $grps = ilPageEditorSettings::getGroups();
        
        foreach ($grps as $g => $types) {
            $ilCtrl->setParameter($this, "grp", $g);
            $ilTabs->addSubTabTarget(
                "adve_grp_" . $g,
                $ilCtrl->getLinkTarget($this, "showPageEditorSettings"),
                array("showPageEditorSettings")
            );
        }
        $ilCtrl->setParameter($this, "grp", $this->std_request->getGroup());
    }
    
    protected function getTabs() : void
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "adve_page_editor_settings",
                $this->ctrl->getLinkTarget($this, "showGeneralPageEditorSettings"),
                array("showPageEditorSettings", "","view")
            );

            $this->tabs_gui->addTarget(
                "adve_rte_settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings","assessment", "survey", "frmPost", "excass"),
                "",
                ""
            );
            
            $this->tabs_gui->addTarget(
                "adve_char_selector_settings",
                $this->ctrl->getLinkTarget($this, "showCharSelectorSettings"),
                array("showCharSelectorSettings", "","view")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
        $this->addSubtabs();
    }
    
    public function settingsObject() : void
    {
        $tpl = $this->tpl;
        $form = $this->getTinyForm();
        $tpl->setContent($form->getHTML());
    }

    public function getTinyForm() : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $editor = $this->object->_getRichTextEditor();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("adve_activation"));
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_use_tiny_mce"), "use_tiny");
        if ($editor == "tinymce") {
            $cb->setChecked(true);
        }
        $form->addItem($cb);
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton("saveSettings", $lng->txt("save"));
        }
        
        return $form;
    }
    
    public function saveSettingsObject() : void
    {
        $this->checkPermission("write");

        $form = $this->getTinyForm();
        $form->checkInput();

        if ($form->getInput("use_tiny")) {
            $this->object->setRichTextEditor("tinymce");
        } else {
            $this->object->setRichTextEditor("");
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, 'settings');
    }
    
    public function assessmentObject() : void
    {
        $form = $this->initTagsForm(
            "assessment",
            "saveAssessmentSettings",
            "advanced_editing_assessment_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
    
    public function saveAssessmentSettingsObject() : void
    {
        $form = $this->initTagsForm(
            "assessment",
            "saveAssessmentSettings",
            "advanced_editing_assessment_settings"
        );
        if (!$this->saveTags("assessment", "assessment", $form)) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
    
    public function surveyObject() : void
    {
        $form = $this->initTagsForm(
            "survey",
            "saveSurveySettings",
            "advanced_editing_survey_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
    
    public function saveSurveySettingsObject() : void
    {
        $form = $this->initTagsForm(
            "survey",
            "saveSurveySettings",
            "advanced_editing_survey_settings"
        );
        if (!$this->saveTags("survey", "survey", $form)) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
    
    public function frmPostObject() : void
    {
        $form = $this->initTagsForm(
            "frm_post",
            "saveFrmPostSettings",
            "advanced_editing_frm_post_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
        
    public function saveFrmPostSettingsObject() : void
    {
        $form = $this->initTagsForm(
            "frm_post",
            "saveFrmPostSettings",
            "advanced_editing_frm_post_settings"
        );
        if (!$this->saveTags("frm_post", "frmPost", $form)) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
    
    public function excAssObject() : void
    {
        $form = $this->initTagsForm(
            "exc_ass",
            "saveExcAssSettings",
            "advanced_editing_excass_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
        
    public function saveExcAssSettingsObject() : void
    {
        $form = $this->initTagsForm(
            "exc_ass",
            "saveExcAssSettings",
            "advanced_editing_excass_settings"
        );
        if (!$this->saveTags("exc_ass", "excAss", $form)) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
            
    
    protected function initTagsForm(
        string $a_id,
        string $a_cmd,
        string $a_title
    ) : ilPropertyFormGUI {
        $ilAccess = $this->access;
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, $a_cmd));
        $form->setTitle($this->lng->txt($a_title));
        
        $alltags = $this->object->getHTMLTags();
        $alltags = array_combine($alltags, $alltags);
        
        $tags = new ilMultiSelectInputGUI($this->lng->txt("advanced_editing_allow_html_tags"), "html_tags");
        $tags->setHeight(400);
        $tags->enableSelectAll(true);
        $tags->enableSelectedFirst(true);
        $tags->setOptions($alltags);
        $tags->setValue(ilObjAdvancedEditing::_getUsedHTMLTags($a_id));
        $form->addItem($tags);
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton($a_cmd, $this->lng->txt("save"));
        }

        return $form;
    }
    
    protected function saveTags(
        string $a_id,
        string $a_cmd,
        ilPropertyFormGUI $form
    ) : bool {
        $this->checkPermission("write");
        try {
            if ($form->checkInput()) {
                $html_tags = $form->getInput("html_tags");
                // get rid of select all
                if ($html_tags[0] == "") {
                    unset($html_tags[0]);
                }
                $this->object->setUsedHTMLTags((array) $html_tags, $a_id);
                ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            } else {
                return false;
            }
        } catch (ilAdvancedEditingRequiredTagsException $e) {
            ilUtil::sendInfo($e->getMessage(), true);
        }
        $this->ctrl->redirect($this, $a_cmd);
        return true;
    }
    
    public function showPageEditorSettingsObject() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $this->addPageEditorSettingsSubtabs();
        
        $grps = ilPageEditorSettings::getGroups();
        
        $this->cgrp = $this->std_request->getGroup();
        if ($this->cgrp == "") {
            $this->cgrp = (string) key($grps);
        }

        $ilCtrl->setParameter($this, "grp", $this->cgrp);
        $ilTabs->setSubTabActive("adve_grp_" . $this->cgrp);
        
        $this->initPageEditorForm();
        $tpl->setContent($this->form->getHTML());
    }
    
    public function initPageEditorForm() : void
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        $lng->loadLanguageModule("content");
        
        $this->form = new ilPropertyFormGUI();
    
        if ($this->cgrp == "test") {
            $this->form->setTitle($lng->txt("adve_activation"));
            $cb = new ilCheckboxInputGUI($this->lng->txt("advanced_editing_tst_editing"), "tst_page_edit");
            $cb->setInfo($this->lng->txt("advanced_editing_tst_editing_desc"));
            if ($ilSetting->get("enable_tst_page_edit", ilObjAssessmentFolder::ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_DISABLED)) {
                $cb->setChecked(true);
            }
            $this->form->addItem($cb);

            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("adve_text_content_features"));
            $this->form->addItem($sh);
        } elseif ($this->cgrp == "rep") {
            $this->form->setTitle($lng->txt("adve_activation"));
            $cb = new ilCheckboxInputGUI($this->lng->txt("advanced_editing_rep_page_editing"), "cat_page_edit");
            $cb->setInfo($this->lng->txt("advanced_editing_rep_page_editing_desc"));
            if ($ilSetting->get("enable_cat_page_edit")) {
                $cb->setChecked(true);
            }
            $this->form->addItem($cb);

            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("adve_text_content_features"));
            $this->form->addItem($sh);
        } else {
            $this->form->setTitle($lng->txt("adve_text_content_features"));
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
            $this->form->addCommandButton("savePageEditorSettings", $lng->txt("save"));
        }
        
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }
    
    public function savePageEditorSettingsObject() : void
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
            
            if ($this->std_request->getGroup() == "test") {
                $ilSetting->set("enable_tst_page_edit", (string) $this->form->getInput("tst_page_edit"));
            } elseif ($this->std_request->getGroup() == "rep") {
                $ilSetting->set("enable_cat_page_edit", (string) $this->form->getInput("cat_page_edit"));
            }
            
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->setParameter($this, "grp", $this->std_request->getGroup());
        $ilCtrl->redirect($this, "showPageEditorSettings");
    }
    
    public function showGeneralPageEditorSettingsObject() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $this->addPageEditorSettingsSubtabs();
        $ilTabs->activateTab("adve_page_editor_settings");
        
        $form = $this->initGeneralPageSettingsForm();
        $tpl->setContent($form->getHTML());
    }
    
    public function initGeneralPageSettingsForm() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
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
            $form->addCommandButton("saveGeneralPageSettings", $lng->txt("save"));
        }

        // enable html/js
        $this->lng->loadLanguageModule("copg");
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("copg_allow_html"));
        $sh->setInfo($lng->txt("copg_allow_html_info"));
        $form->addItem($sh);

        $comps = iterator_to_array($this->component_repository->getComponents());
        $comps_per_dir = array_column(array_map(function ($k, $c) {
            return [$c->getType() . "/" . $c->getName(), $c];
        }, array_keys($comps), $comps), 1, 0);

        $cdef = new ilCOPageObjDef();
        foreach ($cdef->getDefinitions() as $key => $def) {
            if (in_array($key, $this->getPageObjectKeysWithOptionalHTML())) {
                $comp_id = $comps_per_dir[$def["component"]]->getId();
                $this->lng->loadLanguageModule($comp_id);
                $cb = new ilCheckboxInputGUI($def["component"] . ": " . $this->lng->txt($comp_id . "_page_type_" . $key), "act_html_" . $key);
                $cb->setChecked((bool) $aset->get("act_html_" . $key));
                $form->addItem($cb);
            }
        }

        // workaround for glossaries to force rewriting of shot texts
        ilGlossaryDefinition::setShortTextsDirtyGlobally();

                    
        $form->setTitle($lng->txt("adve_pe_general"));
        $form->setFormAction($ilCtrl->getFormAction($this));
     
        return $form;
    }

    /**
     * This limits the possibility to allow html for these page objects
     * that supported the feature in the past.
     *
     * PLEASE do not add additional keys here. The whole feature might be abandonded in
     * the future.
     */
    protected function getPageObjectKeysWithOptionalHTML() : array
    {
        return ["lobj","copa","mep","blp","prtf","prtt","gdf","lm","qht","qpl","qfbg","qfbs","sahs","stys","cont","cstr","auth"];
    }

    public function saveGeneralPageSettingsObject() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

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

                $def = new ilCOPageObjDef();
                foreach ($def->getDefinitions() as $key => $def) {
                    if (in_array($key, $this->getPageObjectKeysWithOptionalHTML())) {
                        $aset->set("act_html_" . $key, (string) (int) $form->getInput("act_html_" . $key));
                    }
                }

                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ilCtrl->redirect($this, "showGeneralPageEditorSettings");
            }
        }
        
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }
    
    public function initCharSelectorSettingsForm(ilCharSelectorGUI $char_selector) : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt('settings'));
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton("saveCharSelectorSettings", $lng->txt("save"));
        }
        $char_selector->addFormProperties($form);

        return $form;
    }
    
    
    /**
     * Show the settings for the selector of unicode characters
     */
    public function showCharSelectorSettingsObject() : void
    {
        $ilTabs = $this->tabs;
        $ilSetting = $this->settings;
        $tpl = $this->tpl;

        $ilTabs->activateTab("adve_char_selector_settings");
                
        $char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_ADMIN);
        $char_selector->getConfig()->setAvailability((int) $ilSetting->get('char_selector_availability'));
        $char_selector->getConfig()->setDefinition((string) $ilSetting->get('char_selector_definition'));
        $form = $this->initCharSelectorSettingsForm($char_selector);
        $char_selector->setFormValues($form);
        $tpl->setContent($form->getHTML());
    }
    
    
    /**
     *  Save the settings for the selector of unicode characters
     */
    public function saveCharSelectorSettingsObject() : void
    {
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $this->checkPermission("write");
        
        $char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_ADMIN);
        $form = $this->initCharSelectorSettingsForm($char_selector);
        if ($form->checkInput()) {
            $char_selector->getFormValues($form);

            $ilSetting->set('char_selector_availability', $char_selector->getConfig()->getAvailability());
            $ilSetting->set('char_selector_definition', $char_selector->getConfig()->getDefinition());
            
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showCharSelectorSettings");
        }
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }
}
