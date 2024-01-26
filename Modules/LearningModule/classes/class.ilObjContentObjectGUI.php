<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjContentObjectGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @ingroup ModulesLearningModule
 */
class ilObjContentObjectGUI extends ilObjectGUI implements ilLinkCheckerGUIRowHandling
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLocatorGUI
     */
    protected $locator;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;


    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = false)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->error = $DIC["ilErr"];
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->help = $DIC["ilHelp"];
        $this->locator = $DIC["ilLocator"];
        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
        $this->ui = $DIC->ui();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->ctrl = $ilCtrl;
        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("obj");
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    protected function checkCtrlPath() : void
    {
        if (!$this->getCreationMode()) {
            $baseclass = strtolower($_GET["baseClass"]);
            $next_class = strtolower($this->ctrl->getNextClass());
            // all calls must be routed through illmpresentationgui or
            // illmeditorgui...
            if (!in_array($baseclass, ["illmpresentationgui", "illmeditorgui"])) {
                // ...except the comman action handler routes to
                // activation/condition GUI, see https://mantis.ilias.de/view.php?id=32858
                if (in_array($next_class, ["ilcommonactiondispatchergui"])) {
                    return;
                }
                throw new ilLMException("Wrong ctrl path");
            }
        }
    }

    /**
     * execute command
     * @return bool|mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;

        $this->checkCtrlPath();
        
        if ($this->ctrl->getRedirectSource() == "ilinternallinkgui") {
            $this->explorer();
            return;
        }

        if ($this->ctrl->getCmdClass() == "ilinternallinkgui") {
            $this->ctrl->setReturn($this, "explorer");
        }

        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        if ($_GET["to_props"] == 1) {
            $cmd = $this->ctrl->getCmd("properties");
        } else {
            $cmd = $this->ctrl->getCmd("chapters");
        }

        
        switch ($next_class) {
            case 'illtiproviderobjectsettinggui':
                
                $this->setTabs();
                $ilTabs->setTabActive("settings");
                $this->setSubTabs("lti_provider");
                
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(true);
                $this->ctrl->forwardCommand($lti_gui);
                break;
            
            
            
            case "illearningprogressgui":
                $this->addHeaderAction();
                $this->addLocations();
                $this->setTabs("learning_progress");

                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->object->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }
                
                $this->addHeaderAction();
                $this->addLocations();
                $this->setTabs("meta");
                
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $md_gui->addMDObserver($this->object, 'MDUpdateListener', 'Educational'); // #9510
                $md_gui->addMDObserver($this->object, 'MDUpdateListener', 'General');
                $this->ctrl->forwardCommand($md_gui);
                break;

            case "ilobjstylesheetgui":
                $this->addLocations();
                $this->ctrl->setReturn($this, "editStyleProperties");
                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
                $style_gui->omitLocator();
                if ($cmd == "create" || $_GET["new_type"] == "sty") {
                    $style_gui->setCreationMode(true);
                }
                $ret = $this->ctrl->forwardCommand($style_gui);

                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
                    $style_id = $ret;
                    $this->object->setStyleSheetId($style_id);
                    $this->object->update();
                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
                }
                break;

            case "illmpageobjectgui":
                $this->setTitleAndDescription();
                $ilTabs->setBackTarget(
                    $lng->txt("learning module"),
                    $ilCtrl->getLinkTarget($this, "chapters")
                );
                $this->ctrl->saveParameter($this, array("obj_id"));
                $this->addLocations();
                $this->ctrl->setReturn($this, "chapters");

                $pg_gui = new ilLMPageObjectGUI($this->object);
                if ($_GET["obj_id"] != "") {
                    $obj = ilLMObjectFactory::getInstance($this->object, $_GET["obj_id"]);
                    $pg_gui->setLMPageObject($obj);
                }
                $ret = $this->ctrl->forwardCommand($pg_gui);
                break;

            case "ilstructureobjectgui":
                $ilTabs->setBackTarget(
                    $lng->txt("learning module"),
                    $ilCtrl->getLinkTarget($this, "chapters")
                );

                $this->ctrl->saveParameter($this, array("obj_id"));
                $this->addLocations();
                $this->ctrl->setReturn($this, "chapters");
                $st_gui = new ilStructureObjectGUI($this->object, $this->object->lm_tree);
                if ($_GET["obj_id"] != "") {
                    $obj = ilLMObjectFactory::getInstance($this->object, $_GET["obj_id"]);
                    $st_gui->setStructureObject($obj);
                }
                $ret = $this->ctrl->forwardCommand($st_gui);
                if ($cmd == "save" || $cmd == "cancel") {
                    if ($_GET["obj_id"] == "") {
                        $this->ctrl->redirect($this, "chapters");
                    } else {
                        $this->ctrl->setCmd("subchap");
                        $this->executeCommand();
                    }
                }
                break;

            case 'ilpermissiongui':
                if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
                    $this->prepareOutput();
                } else {
                    $this->addHeaderAction();
                    $this->addLocations(true);
                    $this->setTabs("perm");
                }
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            // infoscreen
            case 'ilinfoscreengui':
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("info");
                $info = new ilInfoScreenGUI($this);
                $info->enablePrivateNotes();
                $info->enableLearningProgress();
        
                $info->enableNews();
                if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
                    $info->enableNewsEditing();
                    $info->setBlockProperty("news", "settings", true);
                }
                
                // show standard meta data section
                $info->addMetaDataSections(
                    $this->object->getId(),
                    0,
                    $this->object->getType()
                );
        
                $ret = $this->ctrl->forwardCommand($info);
                break;
            
            case "ilexportgui":
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ot = ilObjectTranslation::getInstance($this->object->getId());
                if ($ot->getContentActivated()) {
                    $exp_gui->addFormat("xml_master", "XML (" . $lng->txt("cont_master_language_only") . ")", $this, "export");
                    $exp_gui->addFormat("xml_masternomedia", "XML (" . $lng->txt("cont_master_language_only_no_media") . ")", $this, "export");

                    $lng->loadLanguageModule("meta");
                    $langs = $ot->getLanguages();
                    foreach ($langs as $l => $ldata) {
                        $exp_gui->addFormat("html_" . $l, "HTML (" . $lng->txt("meta_l_" . $l) . ")", $this, "exportHTML");
                    }
                    $exp_gui->addFormat("html_all", "HTML (" . $lng->txt("cont_all_languages") . ")", $this, "exportHTML");
                } else {
                    $exp_gui->addFormat("html", "", $this, "exportHTML");
                }

                $exp_gui->addFormat("scorm", "", $this, "exportSCORM");
                $exp_gui->addCustomColumn(
                    $lng->txt("cont_public_access"),
                    $this,
                    "getPublicAccessColValue"
                );
                $exp_gui->addCustomMultiCommand(
                    $lng->txt("cont_public_access"),
                    $this,
                    "publishExportFile"
                );
                $ret = $this->ctrl->forwardCommand($exp_gui);
                ilUtil::sendInfo($this->lng->txt("lm_only_one_download_per_type"));
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("export");
                break;

            case 'ilobjecttranslationgui':
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("settings");
                $this->setSubTabs("obj_multilinguality");
                $transgui = new ilObjectTranslationGUI($this);
                $transgui->setTitleDescrOnlyMode(false);
                $this->ctrl->forwardCommand($transgui);
                break;


            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->prepareOutput();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('lm');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilmobmultisrtuploadgui":
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("content");
                $this->setContentSubTabs("srt_files");
                $gui = new ilMobMultiSrtUploadGUI(new ilLMMultiSrt($this->object));
                $this->ctrl->forwardCommand($gui);
                break;

            case "illmimportgui":
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("content");
                $this->setContentSubTabs("import");
                $gui = new ilLMImportGUI($this->object);
                $this->ctrl->forwardCommand($gui);
                break;

            case "illmeditshorttitlesgui":
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("content");
                $this->setContentSubTabs("short_titles");
                $gui = new ilLMEditShortTitlesGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $new_type = $_POST["new_type"]
                    ? $_POST["new_type"]
                    : $_GET["new_type"];


                if ($cmd == "create" &&
                    !in_array($new_type, array("lm"))) {
                    switch ($new_type) {
                        case "pg":
                            $this->setTabs();
                            $this->ctrl->setCmdClass("ilLMPageObjectGUI");
                            $ret = $this->executeCommand();
                            break;

                        case "st":
                            $this->setTabs();
                            $this->ctrl->setCmdClass("ilStructureObjectGUI");
                            $ret = $this->executeCommand();
                            break;
                    }
                } else {
                    // creation of new dbk/lm in repository
                    if ($this->getCreationMode() == true &&
                        in_array($new_type, array("lm"))) {
                        $this->prepareOutput();
                        if ($cmd == "") {			// this may be due to too big upload files
                            $cmd = "create";
                        }
                        $cmd .= "Object";
                        $ret = $this->$cmd();
                    } else {
                        $this->addHeaderAction();
                        $this->addLocations();
                        $ret = $this->$cmd();
                    }
                }
                break;
        }
        return $ret;
    }

    public static function _forwards()
    {
        return array("ilLMPageObjectGUI", "ilStructureObjectGUI","ilObjStyleSheetGUI");
    }

    /**
    * edit properties form
    */
    public function properties()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("style");
        $this->setTabs("settings");
        $this->setSubTabs("settings");

        // lm properties
        $this->initPropertiesForm();
        $this->getPropertiesFormValues();
        
        if ($this->object->getType() == "lm") {
            // Edit ecs export settings
            $ecs = new ilECSLearningModuleSettings($this->object);
            $ecs->addSettingsToForm($this->form, 'lm');
        }
        
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
    * Init properties form
    */
    public function initPropertiesForm()
    {
        $obj_service = $this->object_service;

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        $this->form = new ilPropertyFormGUI();
        
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setRequired(true);
        $this->form->addItem($ti);
        
        // description
        $ta = new ilTextAreaInputGUI($lng->txt("desc"), "description");
        $this->form->addItem($ta);

        $lng->loadLanguageModule("rep");
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $this->form->addItem($section);

        // online
        $online = new ilCheckboxInputGUI($lng->txt("cont_online"), "cobj_online");
        $this->form->addItem($online);

        // presentation
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('cont_presentation'));
        $this->form->addItem($section);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

        // default layout
        /*
        $layout = self::getLayoutOption($lng->txt("cont_def_layout"), "lm_layout");
        $this->form->addItem($layout);*/

        // layout per page
        /*
        $lpp = new ilCheckboxInputGUI($lng->txt("cont_layout_per_page"), "layout_per_page");
        $lpp->setInfo($this->lng->txt("cont_layout_per_page_info"));
        $this->form->addItem($lpp);*/

        // page header
        $page_header = new ilSelectInputGUI($lng->txt("cont_page_header"), "lm_pg_header");
        $option = array("st_title" => $this->lng->txt("cont_st_title"),
            "pg_title" => $this->lng->txt("cont_pg_title"),
            "none" => $this->lng->txt("cont_none"));
        $page_header->setOptions($option);
        $this->form->addItem($page_header);
        
        // chapter numeration
        $chap_num = new ilCheckboxInputGUI($lng->txt("cont_act_number"), "cobj_act_number");
        $this->form->addItem($chap_num);

        // toc mode
        $toc_mode = new ilSelectInputGUI($lng->txt("cont_toc_mode"), "toc_mode");
        $option = array("chapters" => $this->lng->txt("cont_chapters_only"),
            "pages" => $this->lng->txt("cont_chapters_and_pages"));
        $toc_mode->setOptions($option);
        $this->form->addItem($toc_mode);

        // show progress icons
        $progr_icons = new ilCheckboxInputGUI($lng->txt("cont_progress_icons"), "progr_icons");
        $progr_icons->setInfo($this->lng->txt("cont_progress_icons_info"));
        $this->form->addItem($progr_icons);

        // self assessment
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('cont_self_assessment'));
        $this->form->addItem($section);

        // tries
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_tries"), "store_tries");
        $radg->setValue(0);
        $op1 = new ilRadioOption($lng->txt("cont_tries_reset_on_visit"), 0, $lng->txt("cont_tries_reset_on_visit_info"));
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("cont_tries_store"), 1, $lng->txt("cont_tries_store_info"));
        $radg->addOption($op2);
        $this->form->addItem($radg);

        // restrict forward navigation
        $qfeed = new ilCheckboxInputGUI($lng->txt("cont_restrict_forw_nav"), "restrict_forw_nav");
        $qfeed->setInfo($this->lng->txt("cont_restrict_forw_nav_info"));
        $this->form->addItem($qfeed);

        // notification
        $not = new ilCheckboxInputGUI($lng->txt("cont_notify_on_blocked_users"), "notification_blocked_users");
        $not->setInfo($this->lng->txt("cont_notify_on_blocked_users_info"));
        $qfeed->addSubItem($not);

        // disable default feedback for questions
        $qfeed = new ilCheckboxInputGUI($lng->txt("cont_disable_def_feedback"), "disable_def_feedback");
        $qfeed->setInfo($this->lng->txt("cont_disable_def_feedback_info"));
        $this->form->addItem($qfeed);

        // additional features
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('obj_features'));
        $this->form->addItem($section);

        // public notes
        if (!$ilSetting->get('disable_comments')) {
            $this->lng->loadLanguageModule("notes");
            $pub_nodes = new ilCheckboxInputGUI($lng->txt("notes_comments"), "cobj_pub_notes");
            $pub_nodes->setInfo($this->lng->txt("cont_lm_comments_desc"));
            $this->form->addItem($pub_nodes);
        }

        // history user comments
        $com = new ilCheckboxInputGUI($lng->txt("enable_hist_user_comments"), "cobj_user_comments");
        $com->setInfo($this->lng->txt("enable_hist_user_comments_desc"));
        $this->form->addItem($com);

        // rating
        $this->lng->loadLanguageModule('rating');
        $rate = new ilCheckboxInputGUI($this->lng->txt('rating_activate_rating'), 'rating');
        $rate->setInfo($this->lng->txt('rating_activate_rating_info'));
        $this->form->addItem($rate);
        $ratep = new ilCheckboxInputGUI($this->lng->txt('lm_activate_rating'), 'rating_pages');
        $this->form->addItem($ratep);

        $this->form->setTitle($lng->txt("cont_lm_properties"));
        $this->form->addCommandButton("saveProperties", $lng->txt("save"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
    * Get values for properties form
    */
    public function getPropertiesFormValues()
    {
        $ilUser = $this->user;

        $values = array();

        $title = $this->object->getTitle();
        $description = $this->object->getLongDescription();
        $ot = ilObjectTranslation::getInstance($this->object->getId());
        if ($ot->getContentActivated()) {
            $title = $ot->getDefaultTitle();
            $description = $ot->getDefaultDescription();
        }

        $values["title"] = $title;
        $values["description"] = $description;
        if (!$this->object->getOfflineStatus()) {
            $values["cobj_online"] = true;
        }
        //$values["lm_layout"] = $this->object->getLayout();
        $values["lm_pg_header"] = $this->object->getPageHeader();
        if ($this->object->isActiveNumbering()) {
            $values["cobj_act_number"] = true;
        }
        $values["toc_mode"] = $this->object->getTOCMode();
        if ($this->object->publicNotes()) {
            $values["cobj_pub_notes"] = true;
        }
        if ($this->object->cleanFrames()) {
            $values["cobj_clean_frames"] = true;
        }
        if ($this->object->isActiveHistoryUserComments()) {
            $values["cobj_user_comments"] = true;
        }
        //$values["layout_per_page"] = $this->object->getLayoutPerPage();
        $values["rating"] = $this->object->hasRating();
        $values["rating_pages"] = $this->object->hasRatingPages();
        $values["disable_def_feedback"] = $this->object->getDisableDefaultFeedback();
        $values["progr_icons"] = $this->object->getProgressIcons();
        $values["store_tries"] = $this->object->getStoreTries();
        $values["restrict_forw_nav"] = $this->object->getRestrictForwardNavigation();

        $values["notification_blocked_users"] = ilNotification::hasNotification(
            ilNotification::TYPE_LM_BLOCKED_USERS,
            $ilUser->getId(),
            $this->object->getId()
        );

        $this->form->setValuesByArray($values);
    }
    
    /**
    * save properties
    */
    public function saveProperties()
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilSetting = $this->settings;
        $obj_service = $this->object_service;

        $valid = false;
        $this->initPropertiesForm();
        if ($this->form->checkInput()) {
            $ot = ilObjectTranslation::getInstance($this->object->getId());
            if ($ot->getContentActivated()) {
                $ot->setDefaultTitle($_POST['title']);
                $ot->setDefaultDescription($_POST['description']);
                $ot->save();
            }

            $this->object->setTitle($_POST['title']);
            $this->object->setDescription($_POST['description']);
            //$this->object->setLayout($_POST["lm_layout"]);
            $this->object->setPageHeader($_POST["lm_pg_header"]);
            $this->object->setTOCMode($_POST["toc_mode"]);
            $this->object->setOfflineStatus(!($_POST['cobj_online']));
            $this->object->setActiveNumbering($_POST["cobj_act_number"]);
            $this->object->setCleanFrames($_POST["cobj_clean_frames"]);
            if (!$ilSetting->get('disable_comments')) {
                $this->object->setPublicNotes($_POST["cobj_pub_notes"]);
            }
            $this->object->setHistoryUserComments($_POST["cobj_user_comments"]);
            //$this->object->setLayoutPerPage($_POST["layout_per_page"]);
            $this->object->setRating($_POST["rating"]);
            $this->object->setRatingPages($_POST["rating_pages"]);
            $this->object->setDisableDefaultFeedback((int) $_POST["disable_def_feedback"]);
            $this->object->setProgressIcons((int) $_POST["progr_icons"]);

            $add_info = "";
            if ($_POST["restrict_forw_nav"] && !$_POST["store_tries"]) {
                $_POST["store_tries"] = 1;
                $add_info = "</br>" . $lng->txt("cont_automatically_set_store_tries");
                $add_info = str_replace("$1", $lng->txt("cont_tries_store"), $add_info);
                $add_info = str_replace("$2", $lng->txt("cont_restrict_forw_nav"), $add_info);
            }

            $this->object->setStoreTries((int) $_POST["store_tries"]);
            $this->object->setRestrictForwardNavigation((int) $_POST["restrict_forw_nav"]);
            $this->object->updateProperties();
            $this->object->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

            ilNotification::setNotification(
                ilNotification::TYPE_LM_BLOCKED_USERS,
                $ilUser->getId(),
                $this->object->getId(),
                (bool) $this->form->getInput("notification_blocked_users")
            );


            if ($this->object->getType() == 'lm') {
                // Update ecs export settings
                $ecs = new ilECSLearningModuleSettings($this->object);
                if ($ecs->handleSettingsUpdate()) {
                    $valid = true;
                }
            } else {
                $valid = true;
            }
        }
        
        if ($valid) {
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified") . $add_info, true);
            $this->ctrl->redirect($this, "properties");
        } else {
            $lng->loadLanguageModule("style");
            $this->setTabs("settings");
            $this->setSubTabs("cont_general_properties");

            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    /**
    * Edit style properties
    */
    public function editStyleProperties()
    {
        $tpl = $this->tpl;
        
        $this->initStylePropertiesForm();
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
    * Init style properties form
    */
    public function initStylePropertiesForm()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilSetting = $this->settings;
        
        $lng->loadLanguageModule("style");
        $this->setTabs();
        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_style");

        $this->form = new ilPropertyFormGUI();
        
        $fixed_style = $ilSetting->get("fixed_content_style_id");
        $def_style = $ilSetting->get("default_content_style_id");
        $style_id = $this->object->getStyleSheetId();

        if ($fixed_style > 0) {
            $st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
            $st->setValue(ilObject::_lookupTitle($fixed_style) . " (" .
                $this->lng->txt("global_fixed") . ")");
            $this->form->addItem($st);
        } else {
            $st_styles = ilObjStyleSheet::_getStandardStyles(
                true,
                false,
                $_GET["ref_id"]
            );

            if ($def_style > 0) {
                $st_styles[0] = ilObject::_lookupTitle($def_style) . " (" . $this->lng->txt("default") . ")";
            } else {
                $st_styles[0] = $this->lng->txt("default");
            }
            ksort($st_styles);

            if ($style_id > 0) {
                // individual style
                if (!ilObjStyleSheet::_lookupStandard($style_id)) {
                    $st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
                    $st->setValue(ilObject::_lookupTitle($style_id));
                    $this->form->addItem($st);

                    // delete command
                    $this->form->addCommandButton(
                        "editStyle",
                        $lng->txt("cont_edit_style")
                    );
                    $this->form->addCommandButton(
                        "deleteStyle",
                        $lng->txt("cont_delete_style")
                    );
                }
            }

            if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id)) {
                $style_sel = new ilSelectInputGUI($lng->txt("cont_current_style"), "style_id");
                $style_sel->setOptions($st_styles);
                $style_sel->setValue($style_id);
                $this->form->addItem($style_sel);
                $this->form->addCommandButton(
                    "saveStyleSettings",
                    $lng->txt("save")
                );
                $this->form->addCommandButton(
                    "createStyle",
                    $lng->txt("sty_create_ind_style")
                );
            }
        }
        $this->form->setTitle($lng->txt("cont_style"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    /**
    * Create Style
    */
    public function createStyle()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
    }
    
    /**
    * Edit Style
    */
    public function editStyle()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilobjstylesheetgui", "edit");
    }

    /**
    * Delete Style
    */
    public function deleteStyle()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilobjstylesheetgui", "delete");
    }

    /**
    * Save style settings
    */
    public function saveStyleSettings()
    {
        $ilSetting = $this->settings;
    
        if ($ilSetting->get("fixed_content_style_id") <= 0 &&
            (ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
            || $this->object->getStyleSheetId() == 0)) {
            $this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
            $this->object->update();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        }
        $this->ctrl->redirect($this, "editStyleProperties");
    }

    /**
     * Init menu form
     */
    public function initMenuForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $form = new ilPropertyFormGUI();
    
        // enable menu
        $menu = new ilCheckboxInputGUI($this->lng->txt("cont_active"), "cobj_act_lm_menu");
        $menu->setChecked($this->object->isActiveLMMenu());
        $form->addItem($menu);
        
        // toc
        /*
        $toc = new ilCheckboxInputGUI($this->lng->txt("cont_toc"), "cobj_act_toc");
        $toc->setChecked($this->object->isActiveTOC());
        $form->addItem($toc);*/
        
        // print view
        $print = new ilCheckboxInputGUI($this->lng->txt("cont_print_view"), "cobj_act_print");
        $print->setChecked($this->object->isActivePrintView());
        $form->addItem($print);
        
        // prevent glossary appendix
        $glo = new ilCheckboxInputGUI($this->lng->txt("cont_print_view_pre_glo"), "cobj_act_print_prev_glo");
        $glo->setChecked($this->object->isActivePreventGlossaryAppendix());
        $print->addSubItem($glo);
    
        // hide header and footer in print view
        $hhfp = new ilCheckboxInputGUI($this->lng->txt("cont_hide_head_foot_print"), "hide_head_foot_print");
        $hhfp->setChecked($this->object->getHideHeaderFooterPrint());
        $print->addSubItem($hhfp);
    
        // downloads
        $no_download_file_available =
            " " . $lng->txt("cont_no_download_file_available") .
            " <a href='" . $ilCtrl->getLinkTargetByClass("ilexportgui", "") . "'>" . $lng->txt("change") . "</a>";
        $types = array("xml", "html", "scorm");
        foreach ($types as $type) {
            if ($this->object->getPublicExportFile($type) != "") {
                if (is_file($this->object->getExportDirectory($type) . "/" .
                    $this->object->getPublicExportFile($type))) {
                    $no_download_file_available = "";
                }
            }
        }
        $dl = new ilCheckboxInputGUI($this->lng->txt("cont_downloads"), "cobj_act_downloads");
        $dl->setInfo($this->lng->txt("cont_downloads_desc") . $no_download_file_available);
        $dl->setChecked($this->object->isActiveDownloads());
        $form->addItem($dl);
        
        // downloads in public area
        $pdl = new ilCheckboxInputGUI($this->lng->txt("cont_downloads_public_desc"), "cobj_act_downloads_public");
        $pdl->setChecked($this->object->isActiveDownloadsPublic());
        $dl->addSubItem($pdl);
            
        $form->addCommandButton("saveMenuProperties", $lng->txt("save"));
                    
        $form->setTitle($lng->txt("cont_lm_menu"));
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        return $form;
    }
    
    /**
     * Edit menu properies
     */
    public function editMenuProperties()
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;

        $lng->loadLanguageModule("style");
        $this->setTabs();
        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");
        
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        $ilToolbar->addFormButton($this->lng->txt("add_menu_entry"), "addMenuEntry");
        $ilToolbar->setCloseFormTag(false);
    
        $form = $this->initMenuForm();
        $form->setOpenTag(false);
        $form->setCloseTag(false);
        
        $this->__initLMMenuEditor();
        $entries = $this->lmme_obj->getMenuEntries();
        $table = new ilLMMenuItemsTableGUI($this, "editMenuProperties", $this->lmme_obj);
        $table->setOpenFormTag(false);
        
        $tpl->setContent($form->getHTML() . "<br />" . $table->getHTML());
    }

    /**
    * save properties
    */
    public function saveMenuProperties()
    {
        $this->object->setActiveLMMenu((int) $_POST["cobj_act_lm_menu"]);
        //$this->object->setActiveTOC((int) $_POST["cobj_act_toc"]);
        $this->object->setActivePrintView((int) $_POST["cobj_act_print"]);
        $this->object->setActivePreventGlossaryAppendix((int) $_POST["cobj_act_print_prev_glo"]);
        $this->object->setHideHeaderFooterPrint((int) $_POST["hide_head_foot_print"]);
        $this->object->setActiveDownloads((int) $_POST["cobj_act_downloads"]);
        $this->object->setActiveDownloadsPublic((int) $_POST["cobj_act_downloads_public"]);
        $this->object->updateProperties();

        $this->__initLMMenuEditor();
        //var_dump($_POST["menu_entries"]); exit;
        $this->lmme_obj->updateActiveStatus($_POST["menu_entries"]);

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editMenuProperties");
    }

    /**
    * output explorer tree
    */
    public function explorer()
    {
        $ilCtrl = $this->ctrl;

        $gui_class = "ilobjlearningmodulegui";

        $ilCtrl->setParameterByClass($gui_class, "active_node", $_GET["active_node"]);
        
        $this->tpl = new ilTemplate("tpl.main.html", true, true);

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

        $this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
        $this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));

        $exp = new ilLMEditorExplorer(
            $this->ctrl->getLinkTarget($this, "view"),
            $this->object,
            $gui_class
        );

        $exp->setTargetGet("obj_id");
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, "explorer"));

        if ($_GET["lmmovecopy"] == "1") {
            $this->proceedDragDrop();
        }


        if ($_GET["lmexpand"] == "") {
            $mtree = new ilTree($this->object->getId());
            $mtree->setTableNames('lm_tree', 'lm_data');
            $mtree->setTreeTablePK("lm_id");
            $expanded = $mtree->readRootId();
        } else {
            $expanded = $_GET["lmexpand"];
        }
        if ($_GET["active_node"] != "") {
            $path = $this->lm_tree->getPathId($_GET["active_node"]);
            $exp->setForceOpenPath($path);

            $exp->highlightNode($_GET["active_node"]);
        }
        $exp->setExpand($expanded);

        // build html-output
        $exp->setOutput(0);
        $output = $exp->getOutput();
        
        // asynchronous output
        if ($ilCtrl->isAsynch()) {
            echo $output;
            exit;
        }

        $this->tpl->setCurrentBlock("content");
        $this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_chap_and_pages"));
        $this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
        $this->tpl->setVariable("EXPLORER", $output);
        $this->ctrl->setParameter($this, "lmexpand", $_GET["lmexpand"]);
        $this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this, "explorer"));
        $this->tpl->parseCurrentBlock();
        $this->tpl->printToStdout(false);
        exit;
    }

    /**
    * popup window for wysiwyg editor
    */
    public function popup()
    {
        $popup = new ilWysiwygUtil();
        $popup->show($_GET["ptype"]);
        exit;
    }

    /**
    * proceed drag and drop operations on pages/chapters
    */
    public function proceedDragDrop()
    {
        $ilCtrl = $this->ctrl;
        
        $this->object->executeDragDrop(
            $_POST["il_hform_source_id"],
            $_POST["il_hform_target_id"],
            $_POST["il_hform_fc"],
            $_POST["il_hform_as_subitem"]
        );
        $ilCtrl->redirect($this, "chapters");
    }

    /* protected function initCreationForms($a_new_type)
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $this->initImportForm());

        return $forms;
    }*/

    protected function afterSave(ilObject $a_new_object)
    {
        $a_new_object->setCleanFrames(true);
        $a_new_object->update();

        // create content object tree
        $a_new_object->createLMTree();
        
        // create a first chapter
        $a_new_object->addFirstChapterAndPage();

        // always send a message
        ilUtil::sendSuccess($this->lng->txt($this->type . "_added"), true);
        ilUtil::redirect("ilias.php?ref_id=" . $a_new_object->getRefId() .
            "&baseClass=ilLMEditorGUI");
    }

    /**
    * Init import form.
    */
    public function initImportForm($a_new_type)
    {
        $form = parent::initImportForm($a_new_type);

        // validation
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_validate_file"), "validate");
        $cb->setInfo($this->lng->txt(""));
        $form->addItem($cb);
        return $form;
    }
    
    /**
    * export object
    *
    * @access	public
    */
    public function exportObject()
    {
        return;
    }

    /**
    * display dialogue for importing XML-LeaningObjects
    *
    * @access	public
    */
    public function importObject()
    {
        $this->createObject();
        return;
    }


    /**
    * display status information or report errors messages
    * in case of error
    *
    * @access	public
    */
    public function importFileObject($parent_id = null, $a_catch_errors = true)
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $tpl = $this->tpl;

        $form = $this->initImportForm("lm");

        try {
            // the new import
            parent::importFileObject(null, false);
            return;
        } catch (ilManifestFileNotFoundImportException $e) {
            // we just run through in this case.
            $no_manifest = true;
        } catch (ilException $e) {
            // display message and form again
            ilUtil::sendFailure($this->lng->txt("obj_import_file_error") . " <br />" . $e->getMessage());
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
            return;
        }

        if (!$no_manifest) {
            return;			// something different has gone wrong, but we have a manifest, this is definitely not "the old" import
        }

        // the "old" (pre 5.1) import

        if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"])) {
            $ilErr->raiseError($this->lng->txt("no_create_permission"), $ilErr->MESSAGE);
            return;
        }

        if ($form->checkInput()) {
            // create and insert object in objecttree
            $newObj = new ilObjContentObject();
            $newObj->setType($_GET["new_type"]);
            $newObj->setTitle($_FILES["importfile"]["name"]);
            $newObj->setDescription("");
            $newObj->create(true);
            $newObj->createReference();
            $newObj->putInTree($_GET["ref_id"]);
            $newObj->setPermissions($_GET["ref_id"]);
            
            // create learning module tree
            $newObj->createLMTree();

            // since the "new" import already did the extracting
            $mess = $newObj->importFromDirectory($this->tmp_import_dir, $_POST["validate"]);

            if ($mess == "") {
                ilUtil::sendSuccess($this->lng->txt($this->type . "_added"), true);
                ilUtil::redirect("ilias.php?ref_id=" . $newObj->getRefId() .
                    "&baseClass=ilLMEditorGUI");
            } else {
                $link = '<a href="' . "ilias.php?ref_id=" . $newObj->getRefId() .
                    "&baseClass=ilLMEditorGUI" . '" target="_top">' . $this->lng->txt("btn_next") . '</a>';
                $tpl->setContent("<br />" . $link . "<br /><br />" . $mess . $link);
            }
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    /**
    * show chapters
    */
    public function chapters()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();
        $this->setContentSubTabs("chapters");
        
        $ilCtrl->setParameter($this, "backcmd", "chapters");
        
        $form_gui = new ilChapterHierarchyFormGUI($this->object->getType(), $_GET["transl"]);
        $form_gui->setFormAction($ilCtrl->getFormAction($this));
        $form_gui->setTitle($this->object->getTitle());
        $form_gui->setIcon(ilUtil::getImagePath("icon_lm.svg"));
        $form_gui->setTree($this->lm_tree);
        $form_gui->setMaxDepth(0);
        $form_gui->setCurrentTopNodeId($this->tree->getRootId());
        $form_gui->addMultiCommand($lng->txt("delete"), "delete");
        $form_gui->addMultiCommand($lng->txt("cut"), "cutItems");
        $form_gui->addMultiCommand($lng->txt("copy"), "copyItems");
        if ($this->object->getLayoutPerPage()) {
            $form_gui->addMultiCommand($lng->txt("cont_set_layout"), "setPageLayoutInHierarchy");
        }
        $form_gui->setDragIcon(ilUtil::getImagePath("icon_st.svg"));
        $form_gui->addCommand($lng->txt("cont_save_all_titles"), "saveAllTitles");
        $up_gui = "ilobjlearningmodulegui";

        $ctpl = new ilTemplate("tpl.chap_and_pages.html", true, true, "Modules/LearningModule");
        $ctpl->setVariable("HIERARCHY_FORM", $form_gui->getHTML());
        $ilCtrl->setParameter($this, "obj_id", "");

        $ml_head = self::getMultiLangHeader($this->object->getId(), $this);
        
        $this->tpl->setContent($ml_head . $ctpl->get());
    }

    /**
     * Get multi lang header
     *
     * @param
     * @return
     */
    public static function getMultiLangHeader($a_lm_id, $a_gui_class, $a_mode = "")
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        // multi language
        $ot = ilObjectTranslation::getInstance($a_lm_id);
        if ($ot->getContentActivated()) {
            $ilCtrl->setParameter($a_gui_class, "lang_switch_mode", $a_mode);
            $lng->loadLanguageModule("meta");
            
            // info
            $ml_gui = new ilPageMultiLangGUI("lm", $a_lm_id);
            $ml_head = $ml_gui->getMultiLangInfo($_GET["transl"]);
            
            // language switch
            $list = new ilAdvancedSelectionListGUI();
            $list->setListTitle($lng->txt("actions"));
            $list->setId("copage_act");
            $entries = false;
            if (!in_array($_GET["transl"], array("", "-"))) {
                $l = $ot->getMasterLanguage();
                $list->addItem(
                    $lng->txt("cont_edit_language_version") . ": " .
                    $lng->txt("meta_l_" . $l),
                    "",
                    $ilCtrl->getLinkTarget($a_gui_class, "editMasterLanguage")
                );
                $entries = true;
            }

            foreach ($ot->getLanguages() as $al => $lang) {
                if ($_GET["transl"] != $al &&
                    $al != $ot->getMasterLanguage()) {
                    $ilCtrl->setParameter($a_gui_class, "totransl", $al);
                    $list->addItem(
                        $lng->txt("cont_edit_language_version") . ": " .
                        $lng->txt("meta_l_" . $al),
                        "",
                        $ilCtrl->getLinkTarget($a_gui_class, "switchToLanguage")
                    );
                    $ilCtrl->setParameter($a_gui_class, "totransl", $_GET["totransl"]);
                }
                $entries = true;
            }
            
            if ($entries) {
                $ml_head = '<div class="ilFloatLeft">' . $ml_head . '</div><div style="margin: 5px 0;" class="small ilRight">' . $list->getHTML() . "</div>";
            }
            $ilCtrl->setParameter($a_gui_class, "lang_switch_mode", "");
        }

        return $ml_head;
    }
    

    /*
    * List all pages of learning module
    */
    public function pages()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->setTabs();
        $this->setContentSubTabs("pages");

        $ilCtrl->setParameter($this, "backcmd", "pages");
        $ilCtrl->setParameterByClass("illmpageobjectgui", "new_type", "pg");
        $ilToolbar->addButton(
            $lng->txt("pg_add"),
            $ilCtrl->getLinkTargetByClass("illmpageobjectgui", "create")
        );
        $ilCtrl->setParameterByClass("illmpageobjectgui", "new_type", "");

        $t = new ilLMPagesTableGUI($this, "pages", $this->object);
        $tpl->setContent($t->getHTML());
    }

    /**
    * List all broken links
    */
    public function listLinks()
    {
        $tpl = $this->tpl;
        
        $this->setTabs();
        $this->setContentSubTabs("internal_links");
        
        $table_gui = new ilLinksTableGUI(
            $this,
            "listLinks",
            $this->object->getId(),
            $this->object->getType()
        );
        
        $tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * Show maintenance
     */
    public function showMaintenance()
    {
        $ilToolbar = $this->toolbar;
        
        $this->setTabs();
        $this->setContentSubTabs("maintenance");
        
        $ilToolbar->addButton(
            $this->lng->txt("cont_fix_tree"),
            $this->ctrl->getLinkTarget($this, "fixTreeConfirm")
        );
    }

    /**
    * activates or deactivates pages
    */
    public function activatePages()
    {
        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $act = ilLMPage::_lookupActive($id, $this->object->getType());
                ilLMPage::_writeActive($id, $this->object->getType(), !$act);
            }
        }

        $this->ctrl->redirect($this, "pages");
    }

    /**
    * paste page
    */
    public function pastePage()
    {
        $ilErr = $this->error;

        if (ilEditClipboard::getContentObjectType() != "pg") {
            $ilErr->raiseError($this->lng->txt("no_page_in_clipboard"), $ilErr->MESSAGE);
        }

        // paste selected object
        $id = ilEditClipboard::getContentObjectId();

        // copy page, if action is copy
        if (ilEditClipboard::getAction() == "copy") {
            // check wether page belongs to lm
            if (ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId())
                == $this->object->getID()) {
                $lm_page = new ilLMPageObject($this->object, $id);
                $new_page = $lm_page->copy();
                $id = $new_page->getId();
            } else {
                // get page from other content object into current content object
                $lm_id = ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId());
                $lm_obj = ilObjectFactory::getInstanceByObjId($lm_id);
                $lm_page = new ilLMPageObject($lm_obj, $id);
                $copied_nodes = array();
                $new_page = $lm_page->copyToOtherContObject($this->object, $copied_nodes);
                $id = $new_page->getId();
                ilLMObject::updateInternalLinks($copied_nodes);
            }
        }

        // cut is not be possible in "all pages" form yet
        if (ilEditClipboard::getAction() == "cut") {
            // check wether page belongs not to lm
            if (ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId())
                != $this->object->getID()) {
                $lm_id = ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId());
                $lm_obj = ilObjectFactory::getInstanceByObjId($lm_id);
                $lm_page = new ilLMPageObject($lm_obj, $id);
                $lm_page->setLMId($this->object->getID());
                $lm_page->update();
                $page = $lm_page->getPageObject();
                $page->buildDom();
                $page->setParentId($this->object->getID());
                $page->update();
            }
        }


        ilEditClipboard::clear();
        $this->ctrl->redirect($this, "pages");
    }

    /**
    * copy page
    */
    public function copyPage()
    {
        $ilErr = $this->error;
        
        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        $items = ilUtil::stripSlashesArray($_POST["id"]);
        ilLMObject::clipboardCopy($this->object->getId(), $items);
        ilEditClipboard::setAction("copy");

        ilUtil::sendInfo($this->lng->txt("cont_selected_items_have_been_copied"), true);

        $this->ctrl->redirect($this, "pages");
    }

    /**
    * confirm deletion screen for page object and structure object deletion
    *
    * @param	int		$a_parent_subobj_id		id of parent object (structure object)
    *											of the objects, that should be deleted
    *											(or no parent object id for top level)
    */
    public function delete($a_parent_subobj_id = 0)
    {
        $ilErr = $this->error;

        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        if (count($_POST["id"]) == 1 && $_POST["id"][0] == IL_FIRST_NODE) {
            $ilErr->raiseError($this->lng->txt("cont_select_item"), $ilErr->MESSAGE);
        }

        if ($a_parent_subobj_id == 0) {
            $this->setTabs("content");
        }
        
        if ($a_parent_subobj_id != 0) {
            $this->ctrl->setParameterByClass("ilStructureObjectGUI", "backcmd", $_GET["backcmd"]);
            $this->ctrl->setParameterByClass("ilStructureObjectGUI", "obj_id", $a_parent_subobj_id);
            $form_action = $this->ctrl->getFormActionByClass("ilStructureObjectGUI");
        } else {
            $this->ctrl->setParameter($this, "backcmd", $_GET["backcmd"]);
            $form_action = $this->ctrl->getFormAction($this);
        }
        
        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($form_action);
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");
        
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
                $obj = new ilLMObject($this->object, $id);
                $caption = ilUtil::getImageTagByType($obj->getType(), $this->tpl->tplPath) .
                    " " . $obj->getTitle();
                
                $cgui->addItem("id[]", $id, $caption);
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
    * cancel delete
    */
    public function cancelDelete()
    {
        $this->ctrl->redirect($this, $_GET["backcmd"]);
    }

    /**
    * delete page object or structure objects
    *
    * @param	int		$a_parent_subobj_id		id of parent object (structure object)
    *											of the objects, that should be deleted
    *											(or no parent object id for top level)
    */
    public function confirmedDelete($a_parent_subobj_id = 0)
    {
        $ilErr = $this->error;

        $tree = new ilTree($this->object->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");

        // check number of objects
        if (!$_POST["id"]) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        // delete all selected objects
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
                $obj = ilLMObjectFactory::getInstance($this->object, $id, false);
                $node_data = $tree->getNodeData($id);
                if (is_object($obj)) {
                    $obj->setLMId($this->object->getId());

                    ilHistory::_createEntry(
                        $this->object->getId(),
                        "delete_" . $obj->getType(),
                        array(ilLMObject::_lookupTitle($id), $id),
                        $this->object->getType()
                    );

                    $obj->delete();
                }
                if ($tree->isInTree($id)) {
                    $tree->deleteTree($node_data);
                }
            }
        }

        // check the tree
        $this->object->checkTree();

        // feedback
        ilUtil::sendSuccess($this->lng->txt("info_deleted"), true);

        if ($a_parent_subobj_id == 0) {
            $this->ctrl->redirect($this, $_GET["backcmd"]);
        }
    }



    /**
    * get context path in content object tree
    *
    * @param	int		$a_endnode_id		id of endnode
    * @param	int		$a_startnode_id		id of startnode
    */
    public function getContextPath($a_endnode_id, $a_startnode_id = 1)
    {
        $path = "";

        $tmpPath = $this->lm_tree->getPathFull($a_endnode_id, $a_startnode_id);

        // count -1, to exclude the learning module itself
        for ($i = 1; $i < (count($tmpPath) - 1); $i++) {
            if ($path != "") {
                $path .= " > ";
            }

            $path .= $tmpPath[$i]["title"];
        }

        return $path;
    }



    /**
    * show possible action (form buttons)
    *
    * @access	public
    */
    public function showActions($a_actions)
    {
        foreach ($a_actions as $name => $lng) {
            $d[$name] = array("name" => $name, "lng" => $lng);
        }

        $notoperations = array();

        $operations = array();

        if (is_array($d)) {
            foreach ($d as $row) {
                if (!in_array($row["name"], $notoperations)) {
                    $operations[] = $row;
                }
            }
        }

        if (count($operations) > 0) {
            foreach ($operations as $val) {
                $this->tpl->setCurrentBlock("operation_btn");
                $this->tpl->setVariable("BTN_NAME", $val["name"]);
                $this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("operation");
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * view content object
    */
    public function view()
    {
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            $this->prepareOutput();
            parent::viewObject();
        } else {
            $this->viewObject();
        }
    }


    /**
    * move a single chapter  (selection)
    */
    public function moveChapter($a_parent_subobj_id = 0)
    {
        $ilErr = $this->error;

        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }
        if (count($_POST["id"]) > 1) {
            $ilErr->raiseError($this->lng->txt("cont_select_max_one_item"), $ilErr->MESSAGE);
        }

        if (count($_POST["id"]) == 1 && $_POST["id"][0] == IL_FIRST_NODE) {
            $ilErr->raiseError($this->lng->txt("cont_select_item"), $ilErr->MESSAGE);
        }

        // SAVE POST VALUES
        ilEditClipboard::storeContentObject("st", $_POST["id"][0], "move");

        ilUtil::sendInfo($this->lng->txt("cont_chap_select_target_now"), true);

        if ($a_parent_subobj_id == 0) {
            $this->ctrl->redirect($this, "chapters");
        }
    }


    /**
    * copy a single chapter  (selection)
    */
    public function copyChapter($a_parent_subobj_id = 0)
    {
        $this->copyItems();
    }

    /**
    * paste chapter
    */
    public function pasteChapter($a_parent_subobj_id = 0)
    {
        return $this->insertChapterClip(false);
    }

    /**
    * move page
    */
    public function movePage()
    {
        $ilErr = $this->error;
        
        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        ilUtil::sendInfo($this->lng->txt("cont_selected_items_have_been_cut"), true);

        $items = ilUtil::stripSlashesArray($_POST["id"]);
        ilLMObject::clipboardCut($this->object->getId(), $items);
        ilEditClipboard::setAction("cut");
        
        $this->ctrl->redirect($this, "pages");
    }

    /**
    * cancel action
    */
    public function cancel()
    {
        if ($_GET["new_type"] == "pg") {
            $this->ctrl->redirect($this, "pages");
        } else {
            $this->ctrl->redirect($this, "chapters");
        }
    }


    /**
    * export content object
    */
    public function export()
    {
        $ot = ilObjectTranslation::getInstance($this->object->getId());
        $opt = "";
        if ($ot->getContentActivated()) {
            $format = explode("_", $_POST["format"]);
            $opt = ilUtil::stripSlashes($format[1]);
        }


        $cont_exp = new ilContObjectExport($this->object);
        $cont_exp->buildExportFile($opt);
    }

    /**
     * Get public access value for export table
     */
    public function getPublicAccessColValue($a_type, $a_file)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $changelink = "<a href='" . $ilCtrl->getLinkTarget($this, "editMenuProperties") . "'>" . $lng->txt("change") . "</a>";
        if (!$this->object->isActiveLMMenu()) {
            $add = "<br />" . $lng->txt("cont_download_no_menu") . " " . $changelink;
        } elseif (!$this->object->isActiveDownloads()) {
            $add = "<br />" . $lng->txt("cont_download_no_download") . " " . $changelink;
        }

        $basetype = explode("_", $a_type);
        $basetype = $basetype[0];

        if ($this->object->getPublicExportFile($basetype) == $a_file) {
            return $lng->txt("yes") . $add;
        }
    
        return " ";
    }



    /**
    * download export file
    */
    public function publishExportFile($a_files)
    {
        $ilCtrl = $this->ctrl;
        
        if (!isset($a_files)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
        } else {
            foreach ($a_files as $f) {
                $file = explode(":", $f);
                if (is_int(strpos($file[0], "_"))) {
                    $file[0] = explode("_", $file[0])[0];
                }
                $export_dir = $this->object->getExportDirectory($file[0]);
        
                if ($this->object->getPublicExportFile($file[0]) ==
                    $file[1]) {
                    $this->object->setPublicExportFile($file[0], "");
                } else {
                    $this->object->setPublicExportFile($file[0], $file[1]);
                }
            }
            $this->object->update();
        }
        $ilCtrl->redirectByClass("ilexportgui");
    }

    /**
    * download export file
    */
    public function downloadPDFFile()
    {
        $ilErr = $this->error;

        if (!isset($_POST["file"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }

        if (count($_POST["file"]) > 1) {
            $ilErr->raiseError($this->lng->txt("cont_select_max_one_item"), $ilErr->MESSAGE);
        }


        $export_dir = $this->object->getOfflineDirectory();
        
        $file = basename($_POST["file"][0]);
        
        ilUtil::deliverFile($export_dir . "/" . $file, $file);
    }


    /**
    * confirm screen for tree fixing
    *
    */
    public function fixTreeConfirm()
    {
        $this->setTabs();
        $this->setContentSubTabs("maintenance");
        
        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("cont_fix_tree_confirm"));
        $cgui->setCancel($this->lng->txt("cancel"), "showMaintenance");
        $cgui->setConfirm($this->lng->txt("cont_fix_tree"), "fixTree");
        $issues = $this->object->checkStructure();
        $mess = "";
        if (count($issues) > 0) {
            $mess = "Found Issues: <br>" . implode("<br>", $issues);
        }
        $this->tpl->setContent($cgui->getHTML() . $mess);
    }

    /**
     * Fix tree
     */
    public function fixTree()
    {
        $this->object->fixTree();
        ilUtil::sendSuccess($this->lng->txt("cont_tree_fixed"), true);
        $this->ctrl->redirect($this, "showMaintenance");
    }

    /**
    * get lm menu html
    */
    public function setilLMMenu(
        $a_offline = false,
        $a_export_format = "",
        $a_active = "content",
        $a_use_global_tabs = false,
        $a_as_subtabs = false,
        $a_cur_page = 0,
        $a_lang = "",
        $a_export_all = false
    ) {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $rbacsystem = $this->rbacsystem;
        $ilPluginAdmin = $this->plugin_admin;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("lm");

        if ($a_as_subtabs) {
            $addcmd = "addSubTabTarget";
            $getcmd = "getSubTabHTML";
        } else {
            $addcmd = "addTarget";
            $getcmd = "getHTML";
        }
        
        $active[$a_active] = true;

        if (!$this->object->isActiveLMMenu()) {
            return "";
        }

        if ($a_use_global_tabs) {
            $tabs_gui = $ilTabs;
        } else {
            $tabs_gui = new ilTabsGUI();
        }

        // workaround for preventing tooltips in export
        if ($a_offline) {
            $tabs_gui->setSetupMode(true);
        }
        
        // Determine whether the view of a learning resource should
        // be shown in the frameset of ilias, or in a separate window.
        $showViewInFrameset = true;

        if ($showViewInFrameset && !$a_offline) {
            $buttonTarget = ilFrameTargetInfo::_getFrame("MainContent");
        } else {
            $buttonTarget = "_top";
        }

        if ($a_export_format == "scorm") {
            $buttonTarget = "";
        }

        // content
        if (!$a_offline && $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
            $tabs_gui->$addcmd(
                "content",
                $ilCtrl->getLinkTargetByClass("illmpresentationgui", "layout"),
                "",
                "",
                $buttonTarget,
                $active["content"]
            );
            if ($active["content"]) {
                $ilHelp->setScreenId("content");
                $ilHelp->setSubScreenId("content");
            }
        } elseif ($a_offline) {
            $tabs_gui->setForcePresentationOfSingleTab(true);
        }
        // table of contents
        if ($this->object->isActiveTOC() && $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            if (!$a_offline) {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
                $link = $ilCtrl->getLinkTargetByClass("illmpresentationgui", "showTableOfContents");
            } else {
                if ($a_export_all) {
                    $link = "./table_of_contents_" . $a_lang . ".html";
                } else {
                    $link = "./table_of_contents.html";
                }
            }
            $tabs_gui->$addcmd(
                "cont_toc",
                $link,
                "",
                "",
                $buttonTarget,
                $active["toc"]
            );
        }

        // print view
        if ($this->object->isActivePrintView() && $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            if (!$a_offline) {		// has to be implemented for offline mode
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
                $link = $ilCtrl->getLinkTargetByClass("illmpresentationgui", "showPrintViewSelection");
                $tabs_gui->$addcmd(
                    "cont_print_view",
                    $link,
                    "",
                    "",
                    $buttonTarget,
                    $active["print"]
                );
            }
        }
        
        // download
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            $is_public = $this->object->isActiveDownloadsPublic();
        } else {
            $is_public = true;
        }

        if ($this->object->isActiveDownloads() && !$a_offline && $is_public &&
            $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
            $link = $ilCtrl->getLinkTargetByClass("illmpresentationgui", "showDownloadList");
            $tabs_gui->$addcmd(
                "download",
                $link,
                "",
                "",
                $buttonTarget,
                $active["download"]
            );
        }

        // info button
        if ($a_export_format != "scorm" && !$a_offline) {
            if (!$a_offline) {
                $ilCtrl->setParameterByClass("illmpresentationgui", "obj_id", $_GET["obj_id"]);
                $link = $this->ctrl->getLinkTargetByClass(
                    array("illmpresentationgui", "ilinfoscreengui"),
                    "showSummary"
                );
            } else {
                $link = "./info.html";
            }
            
            $tabs_gui->$addcmd(
                'info_short',
                $link,
                "",
                "",
                $buttonTarget,
                $active["info"]
            );
        }
        
        if (!$a_offline &&
            $ilAccess->checkAccess("read", "", $_GET["ref_id"]) && // #14075
            ilLearningProgressAccess::checkAccess($_GET["ref_id"])) {
            $olp = ilObjectLP::getInstance($this->object->getId());
            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL) {
                $tabs_gui->$addcmd(
                    "learning_progress",
                    $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "editManual"),
                    "",
                    "",
                    $buttonTarget,
                    $active["learning_progress"]
                );
            } elseif ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
                $tabs_gui->$addcmd(
                    "learning_progress",
                    $this->ctrl->getLinkTargetByClass(array("illmpresentationgui", "illearningprogressgui"), "showtlt"),
                    "",
                    "",
                    $buttonTarget,
                    $active["learning_progress"]
                );
            }
        }

        // get user defined menu entries
        $this->__initLMMenuEditor();
        $entries = $this->lmme_obj->getMenuEntries(true);
        if (count($entries) > 0 && $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            foreach ($entries as $entry) {
                // build goto-link for internal resources
                if ($entry["type"] == "intern") {
                    $entry["link"] = ILIAS_HTTP_PATH . "/goto.php?target=" . $entry["link"];
                }

                // add http:// prefix if not exist
                if (!strstr($entry["link"], '://') && !strstr($entry["link"], 'mailto:')) {
                    $entry["link"] = "http://" . $entry["link"];
                }

                if (!strstr($entry["link"], 'mailto:')) {
                    $entry["link"] = ilUtil::appendUrlParameterString($entry["link"], "ref_id=" . $this->ref_id . "&structure_id=" . $this->obj_id);
                }
                $tabs_gui->$addcmd(
                    $entry["title"],
                    $entry["link"],
                    "",
                    "",
                    "_blank",
                    "",
                    true
                );
            }
        }

        // edit learning module
        if (!$a_offline && $a_cur_page > 0) {
            if ($rbacsystem->checkAccess("write", $_GET["ref_id"])) {
                //$page_id = $this->getCurrentPageId();
                $page_id = $a_cur_page;
                $tabs_gui->$addcmd(
                    "edit_page",
                    ILIAS_HTTP_PATH . "/ilias.php?baseClass=ilLMEditorGUI&ref_id=" . $_GET["ref_id"] .
                    "&obj_id=" . $page_id . "&to_page=1",
                    "",
                    "",
                    $buttonTarget,
                    $active["edit_page"]
                );
            }
        }

        // user interface hook [uihk]
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        $plugin_html = false;
        foreach ($pl_names as $pl) {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->modifyGUI(
                "Modules/LearningModule",
                "lm_menu_tabs",
                array("lm_menu_tabs" => $tabs_gui)
            );
        }

        return $tabs_gui->$getcmd();
    }

    /**
    * export content object
    */
    public function createPDF()
    {
        $cont_exp = new ilContObjectExport($this->object, "pdf");
        $cont_exp->buildExportFile();
        $this->offlineList();
    }

    /**
     * create html package
     */
    public function exportHTML()
    {
        $ot = ilObjectTranslation::getInstance($this->object->getId());
        $lang = "";
        if ($ot->getContentActivated()) {
            $format = explode("_", $_POST["format"]);
            $lang = ilUtil::stripSlashes($format[1]);
        }
        $cont_exp = new ilContObjectExport($this->object, "html", $lang);
        $cont_exp->buildExportFile();
    }

    /**
    * create scorm package
    */
    public function exportSCORM()
    {
        $cont_exp = new ilContObjectExport($this->object, "scorm");
        $cont_exp->buildExportFile();
    }

    /**
    * display locator
    *
    * @param	boolean		$a_omit_obj_id	set to true, if obj id is not page id (e.g. permission gui)
    */
    public function addLocations($a_omit_obj_id = false)
    {
        $locator = $this->locator;

        if (!$a_omit_obj_id) {
            $obj_id = $_GET["obj_id"];
        }
        $lmtree = $this->object->getTree();

        if (($obj_id != 0) && $lmtree->isInTree($obj_id)) {
            $path = $lmtree->getPathFull($obj_id);
        } else {
            $path = $lmtree->getPathFull($lmtree->getRootId());
            if ($obj_id != 0) {
                $path[] = array("type" => "pg", "child" => $this->obj_id,
                    "title" => ilLMPageObject::_getPresentationTitle($this->obj_id));
            }
        }

        foreach ($path as $key => $row) {
            if ($row["child"] == 1) {
                $this->ctrl->setParameter($this, "obj_id", "");
                $locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "chapters"));
            } else {
                $title = $row["title"];
                switch ($row["type"]) {
                    case "st":
                        $this->ctrl->setParameterByClass("ilstructureobjectgui", "obj_id", $row["child"]);
                        $locator->addItem($title, $this->ctrl->getLinkTargetByClass("ilstructureobjectgui", "view"));
                        break;

                    case "pg":
                        $this->ctrl->setParameterByClass("illmpageobjectgui", "obj_id", $row["child"]);
                        $locator->addItem($title, $this->ctrl->getLinkTargetByClass("illmpageobjectgui", "edit"));
                        break;
                }
            }
        }
        if (!$a_omit_obj_id) {
            $this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        }
    }

    ////
    //// Questions
    ////


    /**
     * List questions
     */
    public function listQuestions()
    {
        $tpl = $this->tpl;

        $this->setTabs("questions");
        $this->setQuestionsSubTabs("question_stats");

        $table = new ilLMQuestionListTableGUI($this, "listQuestions", $this->object);
        $tpl->setContent($table->getHTML());
    }

    /**
     * List blocked users
     */
    public function listBlockedUsers()
    {
        $tpl = $this->tpl;

        $this->setTabs("questions");
        $this->setQuestionsSubTabs("blocked_users");

        $table = new ilLMBlockedUsersTableGUI($this, "listBlockedUsers", $this->object);
        $tpl->setContent($table->getHTML());
    }

    /**
     * Reset number of tries
     */
    public function resetNumberOfTries()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (is_array($_POST["userquest_id"])) {
            foreach ($_POST["userquest_id"] as $uqid) {
                $uqid = explode(":", $uqid);
                ilPageQuestionProcessor::resetTries((int) $uqid[0], (int) $uqid[1]);
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listBlockedUsers");
    }

    /**
     * Unlock blocked question
     */
    public function unlockQuestion()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (is_array($_POST["userquest_id"])) {
            foreach ($_POST["userquest_id"] as $uqid) {
                $uqid = explode(":", $uqid);
                ilPageQuestionProcessor::unlock((int) $uqid[0], (int) $uqid[1]);
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listBlockedUsers");
    }

    /**
     * Send Mail to blocked users
     */
    public function sendMailToBlockedUsers()
    {
        $ilCtrl = $this->ctrl;

        if (!is_array($_POST["userquest_id"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), 1);
            $ilCtrl->redirect($this, "listBlockedUsers");
        }

        $rcps = array();
        foreach ($_POST["userquest_id"] as $uqid) {
            $uqid = explode(":", $uqid);
            $login = ilObjUser::_lookupLogin($uqid[1]);
            if (!in_array($login, $rcps)) {
                $rcps[] = $login;
            }
        }
        ilUtil::redirect(ilMailFormCall::getRedirectTarget(
            $this,
            'listBlockedUsers',
            array(),
            array(
                'type' => 'new',
                'rcp_to' => implode(',', $rcps),
                'sig' => $this->getBlockedUsersMailSignature()
            )
        ));
    }

    /**
     * Get mail signature for blocked users
     */
    protected function getBlockedUsersMailSignature()
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('cont_blocked_users_mail_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }

    
    ////
    //// Tabs
    ////


    /**
    * output tabs
    */
    public function setTabs($a_act = "")
    {
        parent::setTitleAndDescription();
        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("lm");
        $this->addTabs($a_act);
    }

    /**
     * Set pages tabs
     *
     * @param string $a_active active subtab
     */
    public function setContentSubTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lm_set = new ilSetting("lm");

        // chapters
        $ilTabs->addSubtab(
            "chapters",
            $lng->txt("cont_chapters"),
            $ilCtrl->getLinkTarget($this, "chapters")
        );

        // all pages
        $ilTabs->addSubtab(
            "pages",
            $lng->txt("cont_all_pages"),
            $ilCtrl->getLinkTarget($this, "pages")
        );

        // all pages
        $ilTabs->addSubtab(
            "short_titles",
            $lng->txt("cont_short_titles"),
            $ilCtrl->getLinkTargetByClass("illmeditshorttitlesgui", "")
        );

        // export ids
        if ($lm_set->get("html_export_ids")) {
            if (!ilObjContentObject::isOnlineHelpModule($this->object->getRefId())) {
                $ilTabs->addSubtab(
                    "export_ids",
                    $lng->txt("cont_html_export_ids"),
                    $ilCtrl->getLinkTarget($this, "showExportIDsOverview")
                );
            }
        }
        if (ilObjContentObject::isOnlineHelpModule($this->object->getRefId())) {
            $lng->loadLanguageModule("help");
            $ilTabs->addSubtab(
                "export_ids",
                $lng->txt("cont_online_help_ids"),
                $ilCtrl->getLinkTarget($this, "showExportIDsOverview")
            );
            
            $ilTabs->addSubtab(
                "help_tooltips",
                $lng->txt("help_tooltips"),
                $ilCtrl->getLinkTarget($this, "showTooltipList")
            );
        }
        
        // list links
        $ilTabs->addSubtab(
            "internal_links",
            $lng->txt("cont_internal_links"),
            $ilCtrl->getLinkTarget($this, "listLinks")
        );

        // web link checker
        $ilTabs->addSubtab(
            "link_check",
            $lng->txt("link_check"),
            $ilCtrl->getLinkTarget($this, "linkChecker")
        );

        $ilTabs->addSubtab(
            "history",
            $lng->txt("history"),
            $this->ctrl->getLinkTarget($this, "history")
        );

        // maintenance
        $ilTabs->addSubtab(
            "maintenance",
            $lng->txt("cont_maintenance"),
            $ilCtrl->getLinkTarget($this, "showMaintenance")
        );

        // srt files
        $ilTabs->addSubtab(
            "srt_files",
            $lng->txt("cont_subtitle_files"),
            $ilCtrl->getLinkTargetByClass("ilmobmultisrtuploadgui", "")
        );

        // srt files
        $ilTabs->addSubtab(
            "import",
            $lng->txt("cont_import"),
            $ilCtrl->getLinkTargetByClass("illmimportgui", "")
        );

        $ilTabs->activateSubTab($a_active);
        $ilTabs->activateTab("content");
    }

    /**
     * Set pages tabs
     *
     * @param string $a_active active subtab
     */
    public function setQuestionsSubTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        // chapters
        $ilTabs->addSubtab(
            "question_stats",
            $lng->txt("cont_question_stats"),
            $ilCtrl->getLinkTarget($this, "listQuestions")
        );

        // blocked users
        $ilTabs->addSubtab(
            "blocked_users",
            $lng->txt("cont_blocked_users"),
            $ilCtrl->getLinkTarget($this, "listBlockedUsers")
        );

        $ilTabs->activateSubTab($a_active);
    }

    /**
     * Adds tabs
     */
    public function addTabs($a_act = "")
    {
        $rbacsystem = $this->rbacsystem;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        // content
        $ilTabs->addTab(
            "content",
            $lng->txt("content"),
            $this->ctrl->getLinkTarget($this, "chapters")
        );

        // info
        $ilTabs->addTab(
            "info",
            $lng->txt("info_short"),
            $this->ctrl->getLinkTargetByClass("ilinfoscreengui", 'showSummary')
        );
            
        // settings
        $ilTabs->addTab(
            "settings",
            $lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, 'properties')
        );

        // questions
        $ilTabs->addTab(
            "questions",
            $lng->txt("objs_qst"),
            $this->ctrl->getLinkTarget($this, "listQuestions")
        );

        // learning progress
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId()) and ($this->object->getType() == 'lm')) {
            $ilTabs->addTab(
                'learning_progress',
                $lng->txt("learning_progress"),
                $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'), '')
            );
        }

        if ($this->object->getType() != "lm") {
            // bibliographical data
            $ilTabs->addTab(
                "bib_data",
                $lng->txt("bib_data"),
                $this->ctrl->getLinkTarget($this, "editBibItem")
            );
        }

        // meta data
        $mdgui = new ilObjectMetaDataGUI($this->object);
        $mdtab = $mdgui->getTab();
        if ($mdtab) {
            $ilTabs->addTab(
                "meta",
                $lng->txt("meta_data"),
                $mdtab
            );
        }

        if ($this->object->getType() == "lm") {
            // export
            $ilTabs->addTab(
                "export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }
        
        // permissions
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $ilTabs->addTab(
                "perm",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
        
        if ($a_act != "") {
            $ilTabs->activateTab($a_act);
        }
        
        // presentation view
        $ilTabs->addNonTabbedLink(
            "pres_mode",
            $lng->txt("cont_presentation_view"),
            "ilias.php?baseClass=ilLMPresentationGUI&ref_id=" . $this->object->getRefID(),
            "_top"
        );
    }

    /**
    * Set sub tabs
    */
    public function setSubTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $ilSetting = $this->settings;

        if (in_array(
            $a_active,
            array("settings", "cont_style", "cont_lm_menu", "public_section",
                "cont_glossaries", "cont_multilinguality", "obj_multilinguality",
                "lti_provider")
        )) {
            // general properties
            $ilTabs->addSubTabTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, 'properties'),
                "",
                ""
            );
                
            // style properties
            $ilTabs->addSubTabTarget(
                "cont_style",
                $this->ctrl->getLinkTarget($this, 'editStyleProperties'),
                "",
                ""
            );

            // menu properties
            $ilTabs->addSubTabTarget(
                "cont_lm_menu",
                $this->ctrl->getLinkTarget($this, 'editMenuProperties'),
                "",
                ""
            );

            // glossaries
            $ilTabs->addSubTabTarget(
                "cont_glossaries",
                $this->ctrl->getLinkTarget($this, 'editGlossaries'),
                "",
                ""
            );

            if ($ilSetting->get("pub_section")) {
                // public section
                $ilTabs->addSubTabTarget(
                    "public_section",
                    $this->ctrl->getLinkTarget($this, 'editPublicSection'),
                    "",
                    ""
                );
            }

            $ilTabs->addSubTabTarget(
                "obj_multilinguality",
                $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "")
            );
            
            $lti_settings = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
            if ($lti_settings->hasSettingsAccess()) {
                $ilTabs->addSubTabTarget(
                    'lti_provider',
                    $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class)
                );
            }
            
            $ilTabs->setSubTabActive($a_active);
        }
    }

    public function editPublicSection()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilAccess = $this->access;

        
        if (!$ilAccess->checkAccessOfUser(ANONYMOUS_USER_ID, "read", "", $this->object->getRefId())) {
            ilUtil::sendInfo($this->lng->txt("cont_anonymous_user_missing_perm"));
        }
        
        $this->setTabs();
        $this->setSubTabs("public_section");
        $ilTabs->setTabActive("settings");

        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.lm_public_selector.html",
            "Modules/LearningModule"
        );

        // get learning module object
        $this->lm_obj = new ilObjLearningModule($this->ref_id, true);


        // public mode
        $modes = array("complete" => $this->lng->txt("all_pages"), "selected" => $this->lng->txt("selected_pages_only"));
        $si = new ilSelectInputGUI($this->lng->txt("choose_public_mode"), "lm_public_mode");
        $si->setOptions($modes);
        $si->setValue($this->object->getPublicAccessMode());
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->addFormButton($this->lng->txt("save"), "savePublicSectionAccess");
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "savePublicSectionAccess"));

        if ($this->object->getPublicAccessMode() == "selected") {
            $this->tpl->setCurrentBlock("select_pages");
            $this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this, "savePublicSectionPages"));

            $tree = new ilPublicSectionExplorerGUI($this, "editPublicSection", $this->lm_obj);
            $tree->setSelectMode("pages", true);
            $tree->setSkipRootNode(true);

            $this->tpl->setVariable("EXPLORER", $tree->getHTML());
            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
            
            $this->tpl->parseCurrentBlock();
        }
    }

    public function savePublicSection()
    {
        //var_dump($_POST["lm_public_mode"]);exit;
        $this->object->setPublicAccessMode($_POST["lm_public_mode"]);
        $this->object->updateProperties();
        ilLMObject::_writePublicAccessStatus($_POST["pages"], $this->object->getId());
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editPublicSection");
    }

    /**
     * Saves lm access mode
     */
    public function savePublicSectionAccess()
    {
        $this->object->setPublicAccessMode($_POST["lm_public_mode"]);
        $this->object->updateProperties();
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editPublicSection");
    }

    /**
     * Saves public lm pages
     */
    public function savePublicSectionPages()
    {
        ilLMObject::_writePublicAccessStatus($_POST["pages"], $this->object->getId());
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editPublicSection");
    }

    /**
    * history
    *
    * @access	public
    */
    public function history()
    {
        $this->setTabs("content");
        $this->setContentSubTabs("history");

        $hist_gui = new ilHistoryTableGUI(
            $this,
            "history",
            $this->object->getId(),
            $this->object->getType()
        );
        $hist_gui->initTable();
        $hist_gui->setCommentVisibility($this->object->isActiveHistoryUserComments());

        $this->tpl->setContent($hist_gui->getHTML());
    }
    
    /**
     *
     * @see		ilLinkCheckerGUIRowHandling::formatInvalidLinkArray()
     * @param	array Unformatted array
     * @return	array Formatted array
     * @access	public
     *
     */
    public function formatInvalidLinkArray(array $row)
    {
        $row['title'] = ilLMPageObject::_getPresentationTitle($row['page_id'], $this->object->getPageHeader());
    
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setSelectionHeaderClass('small');
        $actions->setItemLinkClass('xsmall');
        $actions->setListTitle($this->lng->txt('actions'));
        $actions->setId($row['page_id']);
        $this->ctrl->setParameterByClass('ilLMPageObjectGUI', 'obj_id', $row['page_id']);
        $actions->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTargetByClass('ilLMPageObjectGUI', 'edit')
        );
        $this->ctrl->clearParametersByClass('ilLMPageObjectGUI');
        $row['action_html'] = $actions->getHTML();
        
        return $row;
    }

    public function linkChecker()
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;

        $this->__initLinkChecker();

        $this->setTabs();
        $this->setContentSubTabs("link_check");
        
        $toolbar = new ilToolbarGUI();
        
        // #13684
        if (ilCronManager::isJobActive("lm_link_check")) {
            $chb = new ilCheckboxInputGUI($this->lng->txt('link_check_message_a'), 'link_check_message');
            $chb->setValue(1);
            $chb->setChecked((bool) ilLinkCheckNotify::_getNotifyStatus($ilUser->getId(), $this->object->getId()));
            $chb->setOptionTitle($this->lng->txt('link_check_message_b'));
            
            $toolbar->addInputItem($chb);
            $toolbar->addFormButton($this->lng->txt('save'), 'saveLinkCheck');
            $toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'saveLinkCheck'));
        }
        
        $tgui = new ilLinkCheckerTableGUI($this, 'linkChecker');
        $tgui->setLinkChecker($this->link_checker_obj)
             ->setRowHandler($this)
             ->setRefreshButton($this->lng->txt('refresh'), 'refreshLinkCheck');
        
        return $tpl->setContent($tgui->prepareHTML()->getHTML() . $toolbar->getHTML());
    }
    
    public function saveLinkCheck()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;

        $link_check_notify = new ilLinkCheckNotify($ilDB);
        $link_check_notify->setUserId($ilUser->getId());
        $link_check_notify->setObjId($this->object->getId());

        if ($_POST['link_check_message']) {
            ilUtil::sendSuccess($this->lng->txt('link_check_message_enabled'));
            $link_check_notify->addNotifier();
        } else {
            ilUtil::sendSuccess($this->lng->txt('link_check_message_disabled'));
            $link_check_notify->deleteNotifier();
        }
        $this->linkChecker();

        return true;
    }



    public function refreshLinkCheck()
    {
        $this->__initLinkChecker();
        $this->link_checker_obj->checkLinks();
        ilUtil::sendSuccess($this->lng->txt('link_checker_refreshed'));

        $this->linkChecker();

        return true;
    }

    public function __initLinkChecker()
    {
        $ilDB = $this->db;

        $this->link_checker_obj = new ilLinkChecker($ilDB, false);
        $this->link_checker_obj->setObjId($this->object->getId());

        return true;
    }

    public function __initLMMenuEditor()
    {
        $this->lmme_obj = new ilLMMenuEditor();
        $this->lmme_obj->setObjId($this->object->getId());

        return true;
    }

    /**
    * display add menu entry form
    */
    public function addMenuEntry()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        $this->setTabs();

        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");

        $ilToolbar->addButton(
            $this->lng->txt("lm_menu_select_internal_object"),
            $ilCtrl->getLinkTarget($this, "showEntrySelector")
        );
        
        $form = $this->initMenuEntryForm("create");
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init menu entry form.
     *
     * @param string $a_mode Edit Mode
     */
    public function initMenuEntryForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($this->lng->txt("lm_menu_entry_title"), "title");
        $ti->setMaxLength(255);
        $ti->setSize(40);
        $form->addItem($ti);
        
        // target
        $ta = new ilTextInputGUI($this->lng->txt("lm_menu_entry_target"), "target");
        $ta->setMaxLength(255);
        $ta->setSize(40);
        $form->addItem($ta);
        
        if ($a_mode == "edit") {
            $this->__initLMMenuEditor();
            $this->lmme_obj->readEntry($_REQUEST["menu_entry"]);
            $ti->setValue($this->lmme_obj->getTitle());
            $ta->setValue($this->lmme_obj->getTarget());
        }

        if (isset($_GET["link_ref_id"])) {
            $link_ref_id = (int) $_GET["link_ref_id"];
            $obj_type = ilObject::_lookupType($link_ref_id, true);
            $obj_id = ilObject::_lookupObjectId($link_ref_id);
            $title = ilObject::_lookupTitle($obj_id);

            $target_link = $obj_type . "_" . $link_ref_id;
            $ti->setValue($title);
            $ta->setValue($target_link);
            
            // link ref id
            $hi = new ilHiddenInputGUI("link_ref_id");
            $hi->setValue($link_ref_id);
            $form->addItem($hi);
        }
        
        
        // save and cancel commands
        if ($a_mode == "create") {
            $form->addCommandButton("saveMenuEntry", $lng->txt("save"));
            $form->addCommandButton("editMenuProperties", $lng->txt("cancel"));
            $form->setTitle($lng->txt("lm_menu_new_entry"));
        } else {
            $form->addCommandButton("updateMenuEntry", $lng->txt("save"));
            $form->addCommandButton("editMenuProperties", $lng->txt("cancel"));
            $form->setTitle($lng->txt("lm_menu_edit_entry"));
        }
        
        $form->setFormAction($ilCtrl->getFormAction($this));
     
        return $form;
    }
    
    /**
    * save new menu entry
    */
    public function saveMenuEntry()
    {
        $ilCtrl = $this->ctrl;
        
        // check title and target
        if (empty($_POST["title"])) {
            ilUtil::sendFailure($this->lng->txt("please_enter_title"), true);
            $ilCtrl->redirect($this, "addMenuEntry");
        }
        if (empty($_POST["target"])) {
            ilUtil::sendFailure($this->lng->txt("please_enter_target"), true);
            $ilCtrl->redirect($this, "addMenuEntry");
        }

        $this->__initLMMenuEditor();
        $this->lmme_obj->setTitle($_POST["title"]);
        $this->lmme_obj->setTarget($_POST["target"]);
        $this->lmme_obj->setLinkRefId($_POST["link_ref_id"]);

        if ($_POST["link_ref_id"]) {
            $this->lmme_obj->setLinkType("intern");
        }

        $this->lmme_obj->create();

        ilUtil::sendSuccess($this->lng->txt("msg_entry_added"), true);
        $this->ctrl->redirect($this, "editMenuProperties");
    }

    /**
    * drop a menu entry
    */
    public function deleteMenuEntry()
    {
        $ilErr = $this->error;

        if (empty($_GET["menu_entry"])) {
            $ilErr->raiseError($this->lng->txt("no_menu_entry_id"), $ilErr->MESSAGE);
        }

        $this->__initLMMenuEditor();
        $this->lmme_obj->delete($_GET["menu_entry"]);

        ilUtil::sendSuccess($this->lng->txt("msg_entry_removed"), true);
        $this->ctrl->redirect($this, "editMenuProperties");
    }

    /**
    * edit menu entry form
    */
    public function editMenuEntry()
    {
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilErr = $this->error;

        $this->setTabs();

        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");


        if (empty($_GET["menu_entry"])) {
            $ilErr->raiseError($this->lng->txt("no_menu_entry_id"), $ilErr->MESSAGE);
        }

        $ilCtrl->saveParameter($this, array("menu_entry"));
        $ilToolbar->addButton(
            $this->lng->txt("lm_menu_select_internal_object"),
            $ilCtrl->getLinkTarget($this, "showEntrySelector")
        );
        
        $form = $this->initMenuEntryForm("edit");
        $this->tpl->setContent($form->getHTML());
    }

    /**
    * update a menu entry
    */
    public function updateMenuEntry()
    {
        $ilErr = $this->error;

        if (empty($_REQUEST["menu_entry"])) {
            $ilErr->raiseError($this->lng->txt("no_menu_entry_id"), $ilErr->MESSAGE);
        }

        // check title and target
        if (empty($_POST["title"])) {
            $ilErr->raiseError($this->lng->txt("please_enter_title"), $ilErr->MESSAGE);
        }
        if (empty($_POST["target"])) {
            $ilErr->raiseError($this->lng->txt("please_enter_target"), $ilErr->MESSAGE);
        }

        $this->__initLMMenuEditor();
        $this->lmme_obj->readEntry($_REQUEST["menu_entry"]);
        $this->lmme_obj->setTitle($_POST["title"]);
        $this->lmme_obj->setTarget($_POST["target"]);
        if ($_POST["link_ref_id"]) {
            $this->lmme_obj->setLinkType("intern");
        }
        if (is_int(strpos($_POST["target"], "."))) {
            $this->lmme_obj->setLinkType("extern");
        }
        $this->lmme_obj->update();

        ilUtil::sendSuccess($this->lng->txt("msg_entry_updated"), true);
        $this->ctrl->redirect($this, "editMenuProperties");
    }

    public function showEntrySelector()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $this->setTabs();

        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");

        $ilCtrl->saveParameter($this, array("menu_entry"));
        
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lm_menu_object_selector.html", "Modules/LearningModule");

        ilUtil::sendInfo($this->lng->txt("lm_menu_select_object_to_add"));

        $exp = new ilLMMenuObjectSelector($this->ctrl->getLinkTarget($this, 'test'), $this);

        $exp->setExpand($_GET["lm_menu_expand"] ? $_GET["lm_menu_expand"] : $this->tree->readRootId());
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showEntrySelector'));
        $exp->setTargetGet("ref_id");
        $exp->setRefId($this->cur_ref_id);

        $sel_types = array('mcst', 'mep', 'cat', 'lm','glo','frm','exc','tst','svy', 'chat', 'wiki', 'sahs',
            "crs", "grp", "book", "tst", "file");
        $exp->setSelectableTypes($sel_types);

        // build html-output
        $exp->setOutput(0);
        $output = $exp->getOutput();

        // get page ids
        foreach ($exp->format_options as $node) {
            if (!$node["container"]) {
                $pages[] = $node["child"];
            }
        }

        // access mode selector
        $this->tpl->setVariable("TXT_SET_PUBLIC_MODE", $this->lng->txt("set_public_mode"));
        $this->tpl->setVariable("TXT_CHOOSE_PUBLIC_MODE", $this->lng->txt("choose_public_mode"));
        $modes = array("complete" => $this->lng->txt("all_pages"), "selected" => $this->lng->txt("selected_pages_only"));
        $select_public_mode = ilUtil::formSelect($this->object->getPublicAccessMode(), "lm_public_mode", $modes, false, true);
        $this->tpl->setVariable("SELECT_PUBLIC_MODE", $select_public_mode);

        $this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("choose_public_pages"));
        $this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
        $this->tpl->setVariable("EXPLORER", $output);
        $this->tpl->setVariable("ONCLICK", $js_pages);
        $this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
        $this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this, "savePublicSection"));
    }

    /**
    * select page as header
    */
    public function selectHeader()
    {
        $ilErr = $this->error;

        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }
        if (count($_POST["id"]) > 1) {
            $ilErr->raiseError($this->lng->txt("cont_select_max_one_item"), $ilErr->MESSAGE);
        }
        if ($_POST["id"][0] != $this->object->getHeaderPage()) {
            $this->object->setHeaderPage($_POST["id"][0]);
        } else {
            $this->object->setHeaderPage(0);
        }
        $this->object->updateProperties();
        $this->ctrl->redirect($this, "pages");
    }

    /**
    * select page as footer
    */
    public function selectFooter()
    {
        $ilErr = $this->error;

        if (!isset($_POST["id"])) {
            $ilErr->raiseError($this->lng->txt("no_checkbox"), $ilErr->MESSAGE);
        }
        if (count($_POST["id"]) > 1) {
            $ilErr->raiseError($this->lng->txt("cont_select_max_one_item"), $ilErr->MESSAGE);
        }
        if ($_POST["id"][0] != $this->object->getFooterPage()) {
            $this->object->setFooterPage($_POST["id"][0]);
        } else {
            $this->object->setFooterPage(0);
        }
        $this->object->updateProperties();
        $this->ctrl->redirect($this, "pages");
    }

    /**
    * Save all titles of chapters/pages
    */
    public function saveAllTitles()
    {
        $ilCtrl = $this->ctrl;
        
        ilLMObject::saveTitles($this->object, ilUtil::stripSlashesArray($_POST["title"]), $_GET["transl"]);

        ilUtil::sendSuccess($this->lng->txt("lm_save_titles"), true);
        $ilCtrl->redirect($this, "chapters");
    }

    /**
    * Insert (multiple) chapters at node
    */
    public function insertChapter()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $num = ilChapterHierarchyFormGUI::getPostMulti();
        $node_id = ilChapterHierarchyFormGUI::getPostNodeId();
        
        if (!ilChapterHierarchyFormGUI::getPostFirstChild()) {	// insert after node id
            $parent_id = $this->lm_tree->getParentId($node_id);
            $target = $node_id;
        } else {													// insert as first child
            $parent_id = $node_id;
            $target = IL_FIRST_NODE;
        }

        for ($i = 1; $i <= $num; $i++) {
            $chap = new ilStructureObject($this->object);
            $chap->setType("st");
            $chap->setTitle($lng->txt("cont_new_chap"));
            $chap->setLMId($this->object->getId());
            $chap->create();
            ilLMObject::putInTree($chap, $parent_id, $target);
        }

        $ilCtrl->redirect($this, "chapters");
    }
    
    /**
    * Insert Chapter from clipboard
    */
    public function insertChapterClip()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilLog = $this->log;
        
        $node_id = ilChapterHierarchyFormGUI::getPostNodeId();
        $first_child = ilChapterHierarchyFormGUI::getPostFirstChild();

        $ilLog->write("InsertChapterClip, num: $num, node_id: $node_id, " .
            " getPostFirstChild " . ilChapterHierarchyFormGUI::getPostFirstChild());

        if (!$first_child) {	// insert after node id
            $parent_id = $this->lm_tree->getParentId($node_id);
            $target = $node_id;
        } else {													// insert as first child
            $parent_id = $node_id;
            $target = IL_FIRST_NODE;
        }
        
        // copy and paste
        $chapters = $ilUser->getClipboardObjects("st", true);
        $copied_nodes = array();
        foreach ($chapters as $chap) {
            $ilLog->write("Call pasteTree, Target LM: " . $this->object->getId() . ", Chapter ID: " . $chap["id"]
                . ", Parent ID: " . $parent_id . ", Target: " . $target);
            $cid = ilLMObject::pasteTree(
                $this->object,
                $chap["id"],
                $parent_id,
                $target,
                $chap["insert_time"],
                $copied_nodes,
                (ilEditClipboard::getAction() == "copy")
            );
            $target = $cid;
        }
        ilLMObject::updateInternalLinks($copied_nodes);

        if (ilEditClipboard::getAction() == "cut") {
            $ilUser->clipboardDeleteObjectsOfType("pg");
            $ilUser->clipboardDeleteObjectsOfType("st");
            ilEditClipboard::clear();
        }
        
        $this->object->checkTree();
        $ilCtrl->redirect($this, "chapters");
    }

    /**
    * redirect script
    *
    * @param	string		$a_target
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $_GET["baseClass"] = "ilLMPresentationGUI";
            $_GET["ref_id"] = $a_target;
            $_GET["cmd"] = "resume";
            include("ilias.php");
            exit;
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            $_GET["baseClass"] = "ilLMPresentationGUI";
            $_GET["ref_id"] = $a_target;
            $_GET["cmd"] = "infoScreen";
            include("ilias.php");
            exit;
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }


        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    /**
    * Copy items to clipboard, then cut them from the current tree
    */
    public function cutItems($a_return = "chapters")
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $items = ilUtil::stripSlashesArray($_POST["id"]);
        if (!is_array($items)) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, $a_return);
        }

        $todel = array();			// delete IDs < 0 (needed for non-js editing)
        foreach ($items as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($items[$k]);
        }
        ilLMObject::clipboardCut($this->object->getId(), $items);
        ilEditClipboard::setAction("cut");
        ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_cut"), true);
        
        $ilCtrl->redirect($this, $a_return);
    }

    /**
    * Copy items to clipboard
    */
    public function copyItems()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $items = ilUtil::stripSlashesArray($_POST["id"]);
        if (!is_array($items)) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "chapters");
        }

        $todel = array();				// delete IDs < 0 (needed for non-js editing)
        foreach ($items as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($items[$k]);
        }
        ilLMObject::clipboardCopy($this->object->getId(), $items);
        ilEditClipboard::setAction("copy");
        ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_copied"), true);
        $ilCtrl->redirect($this, "chapters");
    }

    /**
    * Cut chapter(s)
    */
    public function cutChapter()
    {
        $this->cutItems("chapters");
    }

    ////
    //// HTML export IDs
    ////

    /**
     * Show export IDs overview
     *
     * @param
     * @return
     */
    public function showExportIDsOverview($a_validation = false)
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();
        $this->setContentSubTabs("export_ids");
        
        if (ilObjContentObject::isOnlineHelpModule($this->object->getRefId())) {
            // toolbar
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $lm_tree = $this->object->getTree();
            $childs = $lm_tree->getChilds($lm_tree->readRootId());
            $options = array("" => $lng->txt("all"));
            foreach ($childs as $c) {
                $options[$c["child"]] = $c["title"];
            }
            $si = new ilSelectInputGUI($this->lng->txt("help_component"), "help_chap");
            $si->setOptions($options);
            $si->setValue(ilSession::get("help_chap"));
            $ilToolbar->addInputItem($si, true);
            $ilToolbar->addFormButton($lng->txt("help_filter"), "filterHelpChapters");
            
            $tbl = new ilHelpMappingTableGUI($this, "showExportIDsOverview", $a_validation, false);
        } else {
            $tbl = new ilExportIDTableGUI($this, "showExportIDsOverview", $a_validation, false);
        }

        $tpl->setContent($tbl->getHTML());
    }
    
    /**
     * Filter help chapters
     *
     * @param
     * @return
     */
    public function filterHelpChapters()
    {
        $ilCtrl = $this->ctrl;
        
        ilSession::set("help_chap", ilUtil::stripSlashes($_POST["help_chap"]));
        $ilCtrl->redirect($this, "showExportIDsOverview");
    }
    

    /**
     * Save export IDs
     */
    public function saveExportIds()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // check all export ids
        $ok = true;
        if (is_array($_POST["exportid"])) {
            foreach ($_POST["exportid"] as $pg_id => $exp_id) {
                if ($exp_id != "" && !preg_match(
                    "/^([a-zA-Z]+)[0-9a-zA-Z_]*$/",
                    trim($exp_id)
                )) {
                    $ok = false;
                }
            }
        }
        if (!$ok) {
            ilUtil::sendFailure($lng->txt("cont_exp_ids_not_resp_format1") . ": a-z, A-Z, 0-9, '_'. " .
                $lng->txt("cont_exp_ids_not_resp_format3") . " " .
                $lng->txt("cont_exp_ids_not_resp_format2"));
            $this->showExportIDsOverview(true);
            return;
        }


        if (is_array($_POST["exportid"])) {
            foreach ($_POST["exportid"] as $pg_id => $exp_id) {
                ilLMPageObject::saveExportId(
                    $this->object->getId(),
                    $pg_id,
                    ilUtil::stripSlashes($exp_id),
                    ilLMObject::_lookupType($pg_id)
                );
            }
        }

        ilUtil::sendSuccess($lng->txt("cont_saved_export_ids"), true);
        $ilCtrl->redirect($this, "showExportIdsOverview");
    }

    /**
     * Save help mapping
     *
     * @param
     * @return
     */
    public function saveHelpMapping()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if (is_array($_POST["screen_ids"])) {
            foreach ($_POST["screen_ids"] as $chap => $ids) {
                $ids = explode("\n", $ids);
                ilHelpMapping::saveScreenIdsForChapter($chap, $ids);
            }
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showExportIdsOverview");
    }
    
    ////
    //// Help tooltips
    ////

    /**
     * Show export IDs overview
     *
     * @param
     * @return
     */
    public function showTooltipList()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->setTabs();
        $this->setContentSubTabs("help_tooltips");
        
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        $ti = new ilTextInputGUI($this->lng->txt("help_tooltip_id"), "tooltip_id");
        $ti->setMaxLength(200);
        $ti->setSize(20);
        $ilToolbar->addInputItem($ti, true);
        $ilToolbar->addFormButton($lng->txt("add"), "addTooltip");
        $ilToolbar->addSeparator();
        
        $options = ilHelp::getTooltipComponents();
        if (ilSession::get("help_tt_comp") != "") {
            $options[ilSession::get("help_tt_comp")] = ilSession::get("help_tt_comp");
        }
        $si = new ilSelectInputGUI($this->lng->txt("help_component"), "help_tt_comp");
        $si->setOptions($options);
        $si->setValue(ilSession::get("help_tt_comp"));
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->addFormButton($lng->txt("help_filter"), "filterTooltips");
        
        $tbl = new ilHelpTooltipTableGUI($this, "showTooltipList", ilSession::get("help_tt_comp"));

        $tpl->setContent($tbl->getHTML());
    }

    /**
     * Add tooltip
     *
     * @param
     * @return
     */
    public function addTooltip()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $tt_id = ilUtil::stripSlashes($_POST["tooltip_id"]);
        if (trim($tt_id) != "") {
            if (is_int(strpos($tt_id, "_"))) {
                ilHelp::addTooltip(trim($tt_id), "");
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

                $fu = strpos($tt_id, "_");
                $comp = substr($tt_id, 0, $fu);
                ilSession::set("help_tt_comp", ilUtil::stripSlashes($comp));
            } else {
                ilUtil::sendFailure($lng->txt("cont_help_no_valid_tooltip_id"), true);
            }
        }
        $ilCtrl->redirect($this, "showTooltipList");
    }
    
    /**
     * Filter tooltips
     *
     * @param
     * @return
     */
    public function filterTooltips()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        ilSession::set("help_tt_comp", ilUtil::stripSlashes($_POST["help_tt_comp"]));
        $ilCtrl->redirect($this, "showTooltipList");
    }
    
    
    /**
     * Save tooltips
     *
     * @param
     * @return
     */
    public function saveTooltips()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (is_array($_POST["text"])) {
            foreach ($_POST["text"] as $id => $text) {
                ilHelp::updateTooltip(
                    (int) $id,
                    ilUtil::stripSlashes($text),
                    ilUtil::stripSlashes($_POST["tt_id"][(int) $id])
                );
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showTooltipList");
    }
    
    /**
     * Delete tooltips
     */
    public function deleteTooltips()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                ilHelp::deleteTooltip((int) $id);
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showTooltipList");
    }

    ////
    //// Set layout
    ////
    
    /**
     * Get layout option
     *
     * @return object layout form option
     */
    public static function getLayoutOption($a_txt, $a_var, $a_def_option = "")
    {
        global $DIC;

        $lng = $DIC->language();
        
        // default layout
        $layout = new ilRadioGroupInputGUI($a_txt, $a_var);
        if ($a_def_option != "") {
            if (is_file($im = ilUtil::getImagePath("layout_" . $a_def_option . ".png"))) {
                $im_tag = ilUtil::img($im, $a_def_option);
            }
            $layout->addOption(new ilRadioOption("<table><tr><td>" . $im_tag . "</td><td><b>" .
                $lng->txt("cont_lm_default_layout") .
                "</b>: " . $lng->txt("cont_layout_" . $a_def_option) .
                "</td></tr></table>", ""));
        }
        foreach (ilObjContentObject::getAvailableLayouts() as $l) {
            $im_tag = "";
            if (is_file($im = ilUtil::getImagePath("layout_" . $l . ".png"))) {
                $im_tag = ilUtil::img($im, $l);
            }
            $layout->addOption(new ilRadioOption("<table><tr><td style='padding: 0px 5px 5px;'>" .
                $im_tag . "</td><td style='padding:5px;'><b>" . $lng->txt("cont_layout_" . $l) . "</b>: " .
                $lng->txt("cont_layout_" . $l . "_desc") . "</td></tr></table>", $l));
        }
        
        return $layout;
    }
    
    /**
     * Set layout for multipl pages
     */
    public function setPageLayoutInHierarchy()
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "hierarchy", "1");
        $this->setPageLayout(true);
    }
    
    
    /**
     * Set layout for multipl pages
     */
    public function setPageLayout($a_in_hierarchy = false)
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (!is_array($_POST["id"])) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            
            if ($a_in_hierarchy) {
                $ilCtrl->redirect($this, "chapters");
            } else {
                $ilCtrl->redirect($this, "pages");
            }
        }
        
        $this->initSetPageLayoutForm();
        
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Init set page layout form.
     */
    public function initSetPageLayoutForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->form = new ilPropertyFormGUI();
        
        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $hi = new ilHiddenInputGUI("id[]");
                $hi->setValue($id);
                $this->form->addItem($hi);
            }
        }
        $layout = self::getLayoutOption(
            $lng->txt("cont_layout"),
            "layout",
            $this->object->getLayout()
        );
        $this->form->addItem($layout);
    
        $this->form->addCommandButton("savePageLayout", $lng->txt("save"));
        $this->form->addCommandButton("pages", $lng->txt("cancel"));
        
        $this->form->setTitle($lng->txt("cont_set_layout"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    /**
     * Save page layout
     */
    public function savePageLayout()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameter($this, "hierarchy", $_GET["hierarchy"]);
        
        foreach ($_POST["id"] as $id) {
            ilLMPageObject::writeLayout(
                ilUtil::stripSlashes($id),
                ilUtil::stripSlashes($_POST["layout"]),
                $this->object
            );
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        
        if ($_GET["hierarchy"] == 1) {
            $ilCtrl->redirect($this, "chapters");
        } else {
            $ilCtrl->redirect($this, "pages");
        }
    }
    
    //
    // Auto glossaries
    //
    
    /**
     * Edit automatically linked glossaries
     *
     * @param
     * @return
     */
    public function editGlossaries()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $this->setTabs();
        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_glossaries");
        
        $ilToolbar->addButton(
            $lng->txt("add"),
            $ilCtrl->getLinkTarget($this, "showLMGlossarySelector")
        );
        
        $tab = new ilLMGlossaryTableGUI($this->object, $this, "editGlossaries");
        
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Select LM Glossary
     *
     * @param
     * @return
     */
    public function showLMGlossarySelector()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        
        $this->setTabs();
        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_glossaries");

        $exp = new ilSearchRootSelector($ilCtrl->getLinkTarget($this, 'showLMGlossarySelector'));
        $exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $tree->readRootId());
        $exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'showLMGlossarySelector'));
        $exp->setTargetClass(get_class($this));
        $exp->setCmd('confirmGlossarySelection');
        $exp->setClickableTypes(array("glo"));
        $exp->addFilter("glo");

        // build html-output
        $exp->setOutput(0);
        $tpl->setContent($exp->getOutput());
    }
    
    /**
     * Confirm glossary selection
     */
    public function confirmGlossarySelection()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
            
        $cgui = new ilConfirmationGUI();
        $ilCtrl->setParameter($this, "glo_ref_id", $_GET["root_id"]);
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setHeaderText($lng->txt("cont_link_glo_in_lm"));
        $cgui->setCancel($lng->txt("no"), "selectLMGlossary");
        $cgui->setConfirm($lng->txt("yes"), "selectLMGlossaryLink");
        $tpl->setContent($cgui->getHTML());
    }
    
    /**
     * Select a glossary and link all its terms
     *
     * @param
     * @return
     */
    public function selectLMGlossaryLink()
    {
        $glo_ref_id = (int) $_GET["glo_ref_id"];
        $glo_id = ilObject::_lookupObjId($glo_ref_id);
        $this->object->autoLinkGlossaryTerms($glo_ref_id);
        $this->selectLMGlossary();
    }
    
    
    /**
     * Select lm glossary
     *
     * @param
     * @return
     */
    public function selectLMGlossary()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $glos = $this->object->getAutoGlossaries();
        $glo_ref_id = (int) $_GET["glo_ref_id"];
        $glo_id = ilObject::_lookupObjId($glo_ref_id);
        if (!in_array($glo_id, $glos)) {
            $glos[] = $glo_id;
        }
        $this->object->setAutoGlossaries($glos);
        $this->object->update();
        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editGlossaries");
    }
    
    /**
     * Remove lm glossary
     *
     * @param
     * @return
     */
    public function removeLMGlossary()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->object->removeAutoGlossary((int) $_GET["glo_id"]);
        $this->object->update();
        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editGlossaries");
    }
    
    /**
     * Edit master language
     *
     * @param
     * @return
     */
    public function editMasterLanguage()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameter($this, "transl", "");
        if ($_GET["lang_switch_mode"] == "short_titles") {
            $ilCtrl->redirectByClass("illmeditshorttitlesgui", "");
        }
        $ilCtrl->redirect($this, "chapters");
    }

    /**
     * Switch to language
     *
     * @param
     * @return
     */
    public function switchToLanguage()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameter($this, "transl", $_GET["totransl"]);
        if ($_GET["lang_switch_mode"] == "short_titles") {
            $ilCtrl->redirectByClass("illmeditshorttitlesgui", "");
        }
        $ilCtrl->redirect($this, "chapters");
    }
    
    public function redrawHeaderAction()
    {
        // #12281
        return parent::redrawHeaderActionObject();
    }
}
