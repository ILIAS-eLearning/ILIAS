<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
 * Class ilObjAdvancedEditingGUI
 *
 * @author Helmut Schottmüller <hschottm@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjAdvancedEditingGUI: ilPermissionGUI
 *
 * @ingroup ServicesAdvancedEditing
 */
class ilObjAdvancedEditingGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilSetting
     */
    protected $settings;

    public $conditions;

    /**
     * Constructor
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $rbacsystem = $DIC->rbac()->system();

        $this->type = "adve";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('adve');
        $this->lng->loadLanguageModule('meta');

        if (!$rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read_adve"), $this->ilias->error_obj->WARNING);
        }
    }
    
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret =&$this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "showGeneralPageEditorSettings";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }

    /**
    * save object
    * @access	public
    */
    public function saveObject()
    {
        $this->checkPermission("write");

        parent::saveObject();

        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->ctrl->redirect($this);
    }

    public function getAdminTabs()
    {
        $this->getTabs();
    }
    
    /**
    * Add rte subtabs
    */
    public function addSubtabs()
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
    
    /**
    * Show page editor settings subtabs
    */
    public function addPageEditorSettingsSubtabs()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilTabs->addSubTabTarget(
            "adve_pe_general",
            $ilCtrl->getLinkTarget($this, "showGeneralPageEditorSettings"),
            array("showGeneralPageEditorSettings", "", "view")
        );

        include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");
        $grps = ilPageEditorSettings::getGroups();
        
        foreach ($grps as $g => $types) {
            $ilCtrl->setParameter($this, "grp", $g);
            $ilTabs->addSubTabTarget(
                "adve_grp_" . $g,
                $ilCtrl->getLinkTarget($this, "showPageEditorSettings"),
                array("showPageEditorSettings")
            );
        }
        $ilCtrl->setParameter($this, "grp", $_GET["grp"]);
    }
    
    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
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
        $this->addSubtabs($this->tabs_gui);
    }
    
    
    /**
     * Display assessment folder settings form
     */
    public function settingsObject()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $editor = $this->object->_getRichTextEditor();
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->setTitle($lng->txt("adve_activation"));
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_use_tiny_mce"), "use_tiny");
        if ($editor == "tinymce") {
            $cb->setChecked(true);
        }
        $this->form->addItem($cb);
        if ($this->checkPermissionBool("write")) {
            $this->form->addCommandButton("saveSettings", $lng->txt("save"));
        }
        
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
    * Save Assessment settings
    */
    public function saveSettingsObject()
    {
        $this->checkPermission("write");

        if ($_POST["use_tiny"]) {
            $this->object->setRichTextEditor("tinymce");
        } else {
            $this->object->setRichTextEditor("");
        }
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, 'settings');
    }
    
    
    /**
    * Display settings for test and assessment.
    */
    public function assessmentObject()
    {
        $form = $this->initTagsForm(
            "assessment",
            "saveAssessmentSettings",
            "advanced_editing_assessment_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
    
    public function saveAssessmentSettingsObject()
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
    
    
    /**
    * Display settings for surveys.
    */
    public function surveyObject()
    {
        $form = $this->initTagsForm(
            "survey",
            "saveSurveySettings",
            "advanced_editing_survey_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
    
    public function saveSurveySettingsObject()
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
    
    
    /**
    * Display settings for forums.
    */
    public function frmPostObject()
    {
        $form = $this->initTagsForm(
            "frm_post",
            "saveFrmPostSettings",
            "advanced_editing_frm_post_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
        
    public function saveFrmPostSettingsObject()
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
    
    
    /**
    * Display settings for exercise assignments.
    */
    public function excAssObject()
    {
        $form = $this->initTagsForm(
            "exc_ass",
            "saveExcAssSettings",
            "advanced_editing_excass_settings"
        );
        
        $this->tpl->setContent($form->getHTML());
    }
        
    public function saveExcAssSettingsObject()
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
            
    
    protected function initTagsForm($a_id, $a_cmd, $a_title)
    {
        $ilAccess = $this->access;
        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, $a_cmd));
        $form->setTitle($this->lng->txt($a_title));
        
        $alltags = $this->object->getHTMLTags();
        $alltags = array_combine($alltags, $alltags);
        
        include_once "Services/Form/classes/class.ilMultiSelectInputGUI.php";
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
    
    protected function saveTags($a_id, $a_cmd, $form)
    {
        $this->checkPermission("write");
        try {
            if ($form->checkInput()) {
                // get rid of select all
                if (is_array($_POST['html_tags']) && $_POST['html_tags'][0] == "") {
                    unset($_POST['html_tags'][0]);
                }

                $this->object->setUsedHTMLTags((array) $_POST['html_tags'], $a_id);
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
    
    
    /**
    * Show page editor settings
    */
    public function showPageEditorSettingsObject()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $this->addPageEditorSettingsSubTabs();
        
        include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");
        $grps = ilPageEditorSettings::getGroups();
        
        $this->cgrp = $_GET["grp"];
        if ($this->cgrp == "") {
            $this->cgrp = key($grps);
        }

        $ilCtrl->setParameter($this, "grp", $this->cgrp);
        $ilTabs->setSubTabActive("adve_grp_" . $this->cgrp);
        
        $this->initPageEditorForm();
        $tpl->setContent($this->form->getHtml());
    }
    
    /**
    * Init page editor form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initPageEditorForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        $lng->loadLanguageModule("content");
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
    
        if ($this->cgrp == "test") {
            require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        
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

        
        include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");
        
        include_once("./Services/COPage/classes/class.ilPageContentGUI.php");
        $buttons = ilPageContentGUI::_getCommonBBButtons();
        foreach ($buttons as $b => $t) {
            // command button activation
            $cb = new ilCheckboxInputGUI(str_replace(":", "", $this->lng->txt("cont_text_" . $b)), "active_" . $b);
            $cb->setChecked(ilPageEditorSettings::lookupSetting($this->cgrp, "active_" . $b, true));
            $this->form->addItem($cb);
        }
    
        // save and cancel commands
        if ($this->checkPermissionBool("write")) {
            $this->form->addCommandButton("savePageEditorSettings", $lng->txt("save"));
        }
        
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }
    
    /**
    * Save page editor settings form
    *
    */
    public function savePageEditorSettingsObject()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        $this->checkPermission("write");
    
        $this->initPageEditorForm();
        if ($this->form->checkInput()) {
            include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");
            include_once("./Services/COPage/classes/class.ilPageContentGUI.php");
            $buttons = ilPageContentGUI::_getCommonBBButtons();
            foreach ($buttons as $b => $t) {
                ilPageEditorSettings::writeSetting(
                    $_GET["grp"],
                    "active_" . $b,
                    $this->form->getInput("active_" . $b)
                );
            }
            
            if ($_GET["grp"] == "test") {
                $ilSetting->set("enable_tst_page_edit", (int) $_POST["tst_page_edit"]);
            } elseif ($_GET["grp"] == "rep") {
                $ilSetting->set("enable_cat_page_edit", (int) $_POST["cat_page_edit"]);
            }
            
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->setParameter($this, "grp", $_GET["grp"]);
        $ilCtrl->redirect($this, "showPageEditorSettings");
    }
    
    
    /**
     * Show general page editor settings
     */
    public function showGeneralPageEditorSettingsObject()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $this->addPageEditorSettingsSubTabs();
        $ilTabs->activateTab("adve_page_editor_settings");
        
        $form = $this->initGeneralPageSettingsForm();
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Init general page editor settings form.
     */
    public function initGeneralPageSettingsForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        $aset = new ilSetting("adve");

        // use physical character styles
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_use_physical"), "use_physical");
        $cb->setInfo($this->lng->txt("adve_use_physical_info"));
        $cb->setChecked($aset->get("use_physical"));
        $form->addItem($cb);

        // blocking mode
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_blocking_mode"), "block_mode_act");
        $cb->setChecked($aset->get("block_mode_minutes") > 0);
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

        // auto url linking
        $cb = new ilCheckboxInputGUI($this->lng->txt("adve_auto_url_linking"), "auto_url_linking");
        $cb->setChecked($aset->get("auto_url_linking"));
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

        $comps = ilComponent::getAll();
        $comps_per_dir = array_column(array_map(function ($k, $v) {
            return [$v["type"]."/".$v["name"], $v];
        }, array_keys($comps), $comps), 1, 0);

        $cdef = new ilCOPageObjDef();
        foreach ($cdef->getDefinitions() as $key => $def) {
            if (in_array($key, $this->getPageObjectKeysWithOptionalHTML())) {
                $comp_id = $comps_per_dir[$def["component"]]["id"];
                $this->lng->loadLanguageModule($comp_id);
                $cb = new ilCheckboxInputGUI($def["component"] . ": " . $this->lng->txt($comp_id . "_page_type_" . $key), "act_html_" . $key);
                $cb->setChecked($aset->get("act_html_" . $key));
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
     *
     * @return array
     */
    protected function getPageObjectKeysWithOptionalHTML()
    {
        return ["lobj","copa","mep","blp","prtf","prtt","gdf","lm","qht","qpl","qfbg","qfbs","sahs","stys","cont","cstr","auth"];
    }

    /**
     * Save general page settings
     */
    public function saveGeneralPageSettingsObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $this->checkPermission("write");
        
        $form = $this->initGeneralPageSettingsForm();
        if ($form->checkInput()) {
            $aset = new ilSetting("adve");
            $aset->set("use_physical", $_POST["use_physical"]);
            if ($_POST["block_mode_act"]) {
                $aset->set("block_mode_minutes", (int) $_POST["block_mode_minutes"]);
            } else {
                $aset->set("block_mode_minutes", 0);
            }
            $aset->set("auto_url_linking", $_POST["auto_url_linking"]);

            $def = new ilCOPageObjDef();
            foreach ($def->getDefinitions() as $key => $def) {
                if (in_array($key, $this->getPageObjectKeysWithOptionalHTML())) {
                    $aset->set("act_html_" . $key, (int) $_POST["act_html_" . $key]);
                }
            }

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showGeneralPageEditorSettings");
        }
        
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Init the settings form for the selector of unicode characters
     */
    public function initCharSelectorSettingsForm(ilCharSelectorGUI $char_selector)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
    public function showCharSelectorSettingsObject()
    {
        $ilTabs = $this->tabs;
        $ilSetting = $this->settings;
        $tpl = $this->tpl;

        $ilTabs->activateTab("adve_char_selector_settings");
                
        require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
        $char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_ADMIN);
        $char_selector->getConfig()->setAvailability($ilSetting->get('char_selector_availability'));
        $char_selector->getConfig()->setDefinition($ilSetting->get('char_selector_definition'));
        $form = $this->initCharSelectorSettingsForm($char_selector);
        $char_selector->setFormValues($form);
        $tpl->setContent($form->getHTML());
    }
    
    
    /**
     *  Save the settings for the selector of unicode characters
     */
    public function saveCharSelectorSettingsObject()
    {
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $this->checkPermission("write");
        
        require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
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
} // END class.ilObjAdvancedEditingGUI
