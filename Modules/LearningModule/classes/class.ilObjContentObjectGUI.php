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

use ILIAS\LearningModule\Editing\EditingGUIRequest;

/**
 * Class ilObjContentObjectGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 * @ilCtrl_Calls ilObjContentObjectGUI: ilLMPageObjectGUI, ilStructureObjectGUI, ilObjectContentStyleSettingsGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjContentObjectGUI: ilLearningProgressGUI, ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjContentObjectGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilPageMultiLangGUI, ilObjectTranslationGUI
 * @ilCtrl_Calls ilObjContentObjectGUI: ilMobMultiSrtUploadGUI, ilLMImportGUI, ilLMEditShortTitlesGUI, ilLTIProviderObjectSettingGUI
 */
class ilObjContentObjectGUI extends ilObjectGUI
{
    protected \ILIAS\LearningModule\ReadingTime\SettingsGUI $reading_time_gui;
    protected ilLMMenuEditor $lmme_obj;
    protected ilObjLearningModule $lm_obj;
    protected string $lang_switch_mode;
    protected ilPropertyFormGUI $form;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilDBInterface $db;
    protected ilLogger $log;
    protected \ILIAS\DI\UIServices $ui;
    protected ilComponentRepository $component_repository;

    protected bool $to_props = false;
    protected int $requested_obj_id = 0;
    protected string $requested_new_type = "";
    protected string $requested_baseClass = "";
    protected int $requested_ref_id = 0;
    protected string $requested_transl = "";
    protected string $requested_backcmd = "";
    protected int $requested_menu_entry = 0;
    protected int $requested_lm_menu_expand = 0;
    protected int $requested_search_root_expand = 0;
    protected bool $requested_hierarchy = false;
    protected int $requested_root_id = 0;
    protected int $requested_glo_id = 0;
    protected int $requested_glo_ref_id = 0;
    protected string $requested_lang_switch_mode = "";
    protected int $requested_active_node = 0;
    protected int $requested_lmexpand = 0;
    protected int $requested_link_ref_id = 0;
    protected string $requested_totransl = "";
    protected bool $requested_lmmovecopy = false;
    protected ilObjLearningModule $lm;
    protected EditingGUIRequest $edit_request;
    protected \ILIAS\Style\Content\Service $content_style_service;

    /**
     * @param mixed $a_data
     * @param int  $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     * @throws ilCtrlException
     */
    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = false
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->help = $DIC["ilHelp"];
        $this->locator = $DIC["ilLocator"];
        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
        $this->ui = $DIC->ui();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->ctrl = $ilCtrl;
        $this->component_repository = $DIC["component.repository"];
        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("obj");
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->edit_request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $req = $this->edit_request;
        $this->to_props = $req->getToProps();
        $this->requested_obj_id = $req->getObjId();
        $this->requested_ref_id = $req->getRefId();
        $this->requested_root_id = $req->getRootId();
        $this->requested_glo_id = $req->getGlossaryId();
        $this->requested_glo_ref_id = $req->getGlossaryRefId();
        $this->requested_menu_entry = $req->getMenuEntry();
        $this->requested_lm_menu_expand = $req->getLMMenuExpand();
        $this->requested_search_root_expand = $req->getSearchRootExpand();
        $this->requested_new_type = $req->getNewType();
        $this->requested_baseClass = $req->getBaseClass();
        $this->requested_transl = $req->getTranslation();
        $this->requested_backcmd = $req->getBackCmd();
        $this->requested_hierarchy = $req->getHierarchy();
        $this->lang_switch_mode = $req->getLangSwitchMode();
        $this->requested_active_node = $req->getActiveNode();
        $this->requested_lmexpand = $req->getLMExpand();
        $this->requested_link_ref_id = $req->getLinkRefId();
        $this->requested_totransl = $req->getToTranslation();
        $this->requested_lmmovecopy = $req->getLMMoveCopy();
        $this->content_style_service = $DIC
            ->contentStyle();

        $id = (isset($this->object))
            ? $this->object->getId()
            : 0;
        $this->reading_time_gui = new \ILIAS\LearningModule\ReadingTime\SettingsGUI($id);
    }

    protected function checkCtrlPath(): void
    {
        if (!$this->getCreationMode()) {
            $baseclass = strtolower($this->requested_baseClass);
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
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $this->checkCtrlPath();

        if ($this->ctrl->getRedirectSource() == "ilinternallinkgui") {
            throw new ilLMException("No Explorer found.");
            //$this->explorer();
            //return "";
        }

        if ($this->ctrl->getCmdClass() == "ilinternallinkgui") {
            $this->ctrl->setReturn($this, "explorer");
        }

        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        if ($this->to_props) {
            $cmd = $this->ctrl->getCmd("properties");
        } else {
            $cmd = $this->ctrl->getCmd("chapters");
        }


        switch ($next_class) {
            case 'illtiproviderobjectsettinggui':

                $this->setTabs();
                $ilTabs->setTabActive("settings");
                $this->setSubTabs("lti_provider");

                $lti_gui = new ilLTIProviderObjectSettingGUI($this->lm->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->lm->getRefId()));
                $lti_gui->offerLTIRolesForSelection(true);
                $this->ctrl->forwardCommand($lti_gui);
                break;



            case "illearningprogressgui":
                $this->addHeaderAction();
                $this->addLocations();
                $this->setTabs("learning_progress");

                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY, $this->lm->getRefId());
                $this->ctrl->forwardCommand($new_gui);

                break;

            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->lm->getRefId())) {
                    throw new ilPermissionException($this->lng->txt('permission_denied'));
                }

                $this->addHeaderAction();
                $this->addLocations();
                $this->setTabs("meta");

                $md_gui = new ilObjectMetaDataGUI($this->lm);
                $md_gui->addMDObserver($this->lm, 'MDUpdateListener', 'Educational'); // #9510
                $md_gui->addMDObserver($this->lm, 'MDUpdateListener', 'General');
                $this->ctrl->forwardCommand($md_gui);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->addHeaderAction();
                $this->setTitleAndDescription();
                $this->addLocations();
                $this->setTabs("settings");
                $this->setSubTabs("cont_style");

                $settings_gui = $this->content_style_service
                    ->gui()
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
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

                $pg_gui = new ilLMPageObjectGUI($this->lm);
                if ($this->requested_obj_id > 0) {
                    /** @var ilLMPageObject $obj */
                    $obj = ilLMObjectFactory::getInstance($this->lm, $this->requested_obj_id);
                    $pg_gui->setLMPageObject($obj);
                }
                $this->ctrl->forwardCommand($pg_gui);
                break;

            case "ilstructureobjectgui":
                $ilTabs->setBackTarget(
                    $lng->txt("learning module"),
                    $ilCtrl->getLinkTarget($this, "chapters")
                );

                $this->ctrl->saveParameter($this, array("obj_id"));
                $this->addLocations();
                $this->ctrl->setReturn($this, "chapters");
                $st_gui = new ilStructureObjectGUI($this->lm, $this->lm->lm_tree);
                if ($this->requested_obj_id > 0) {
                    /** @var ilStructureObject $obj */
                    $obj = ilLMObjectFactory::getInstance($this->lm, $this->requested_obj_id);
                    $st_gui->setStructureObject($obj);
                }
                $this->ctrl->forwardCommand($st_gui);
                if ($cmd == "save" || $cmd == "cancel") {
                    if ($this->requested_obj_id == 0) {
                        $this->ctrl->redirect($this, "chapters");
                    } else {
                        $this->ctrl->setCmd("subchap");
                        $this->executeCommand();
                    }
                }
                break;

            case 'ilpermissiongui':
                if (strtolower($this->requested_baseClass) == "iladministrationgui") {
                    $this->prepareOutput();
                } else {
                    $this->addHeaderAction();
                    $this->addLocations(true);
                    $this->setTabs("perm");
                }
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
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
                if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
                    $info->enableNewsEditing();
                    $info->setBlockProperty("news", "settings", true);
                }

                // show standard meta data section
                $info->addMetaDataSections(
                    $this->lm->getId(),
                    0,
                    $this->lm->getType()
                );

                $this->ctrl->forwardCommand($info);
                break;

            case "ilexportgui":
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ot = ilObjectTranslation::getInstance($this->lm->getId());
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
                $this->ctrl->forwardCommand($exp_gui);
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("lm_only_one_download_per_type"));
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
                $gui = new ilMobMultiSrtUploadGUI(new ilLMMultiSrt($this->lm));
                $this->ctrl->forwardCommand($gui);
                break;

            case "illmimportgui":
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("content");
                $this->setContentSubTabs("import");
                $gui = new ilLMImportGUI($this->lm);
                $this->ctrl->forwardCommand($gui);
                break;

            case "illmeditshorttitlesgui":
                $this->addHeaderAction();
                $this->addLocations(true);
                $this->setTabs("content");
                $this->setContentSubTabs("short_titles");
                /** @var ilObjLearningModuleGUI $lm_gui */
                $lm_gui = $this;
                $gui = new ilLMEditShortTitlesGUI(
                    $lm_gui,
                    $this->edit_request->getTranslation()
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $new_type = $this->requested_new_type;

                if ($cmd == "create" &&
                    !in_array($new_type, array("lm"))) {
                    switch ($new_type) {
                        case "pg":
                            $this->setTabs();
                            $this->ctrl->setCmdClass("ilLMPageObjectGUI");
                            $this->executeCommand();
                            break;

                        case "st":
                            $this->setTabs();
                            $this->ctrl->setCmdClass("ilStructureObjectGUI");
                            $this->executeCommand();
                            break;
                    }
                } else {
                    // creation of new dbk/lm in repository
                    if ($this->getCreationMode() === true &&
                        $new_type === "lm") {
                        $this->prepareOutput();
                        if ($cmd == "") {			// this may be due to too big upload files
                            $cmd = "create";
                        }
                        $cmd .= "Object";
                    } else {
                        $this->addHeaderAction();
                        $this->addLocations();
                    }
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * edit properties form
     */
    public function properties(): void
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("style");
        $this->setTabs("settings");
        $this->setSubTabs("settings");

        // lm properties
        $this->initPropertiesForm();
        $this->getPropertiesFormValues();

        // Edit ecs export settings
        $ecs = new ilECSLearningModuleSettings($this->lm);
        $ecs->addSettingsToForm($this->form, 'lm');

        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Init properties form
     */
    public function initPropertiesForm(): void
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
        $obj_service->commonSettings()->legacyForm($this->form, $this->lm)->addTileImage();

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

        $this->reading_time_gui->addSettingToForm($this->form);

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

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $this->form,
            [
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
            ]
        );
    }

    /**
     * Get values for properties form
     */
    public function getPropertiesFormValues(): void
    {
        $ilUser = $this->user;

        $values = array();

        $title = $this->lm->getTitle();
        $description = $this->lm->getLongDescription();
        $ot = ilObjectTranslation::getInstance($this->lm->getId());
        if ($ot->getContentActivated()) {
            $title = $ot->getDefaultTitle();
            $description = $ot->getDefaultDescription();
        }

        $values["title"] = $title;
        $values["description"] = $description;
        if (!$this->lm->getOfflineStatus()) {
            $values["cobj_online"] = true;
        }
        //$values["lm_layout"] = $this->lm->getLayout();
        $values["lm_pg_header"] = $this->lm->getPageHeader();
        if ($this->lm->isActiveNumbering()) {
            $values["cobj_act_number"] = true;
        }
        $values["toc_mode"] = $this->lm->getTOCMode();
        if ($this->lm->publicNotes()) {
            $values["cobj_pub_notes"] = true;
        }
        if ($this->lm->cleanFrames()) {
            $values["cobj_clean_frames"] = true;
        }
        if ($this->lm->isActiveHistoryUserComments()) {
            $values["cobj_user_comments"] = true;
        }
        //$values["layout_per_page"] = $this->lm->getLayoutPerPage();
        $values["rating"] = $this->lm->hasRating();
        $values["rating_pages"] = $this->lm->hasRatingPages();
        $values["disable_def_feedback"] = $this->lm->getDisableDefaultFeedback();
        $values["progr_icons"] = $this->lm->getProgressIcons();
        $values["store_tries"] = $this->lm->getStoreTries();
        $values["restrict_forw_nav"] = $this->lm->getRestrictForwardNavigation();

        $values["notification_blocked_users"] = ilNotification::hasNotification(
            ilNotification::TYPE_LM_BLOCKED_USERS,
            $ilUser->getId(),
            $this->lm->getId()
        );

        $values["cont_show_info_tab"] = $this->object->isInfoEnabled();

        $this->form->setValuesByArray($values, true);
    }

    /**
     * save properties
     */
    public function saveProperties(): void
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilSetting = $this->settings;
        $obj_service = $this->object_service;

        $add_info = "";

        $valid = false;
        $this->initPropertiesForm();
        $form = $this->form;
        if ($form->checkInput()) {
            $ot = ilObjectTranslation::getInstance($this->lm->getId());
            if ($ot->getContentActivated()) {
                $ot->setDefaultTitle($form->getInput('title'));
                $ot->setDefaultDescription($form->getInput('description'));
                $ot->save();
            }

            $this->lm->setTitle($form->getInput('title'));
            $this->lm->setDescription($form->getInput('description'));
            $this->lm->setPageHeader($form->getInput("lm_pg_header"));
            $this->lm->setTOCMode($form->getInput("toc_mode"));
            $this->lm->setOfflineStatus(!($form->getInput('cobj_online')));
            $this->lm->setActiveNumbering((bool) $form->getInput("cobj_act_number"));
            $this->lm->setCleanFrames((bool) $form->getInput("cobj_clean_frames"));
            if (!$ilSetting->get('disable_comments')) {
                $this->lm->setPublicNotes($form->getInput("cobj_pub_notes"));
            }
            $this->lm->setHistoryUserComments((bool) $form->getInput("cobj_user_comments"));
            $this->lm->setRating((bool) $form->getInput("rating"));
            $this->lm->setRatingPages((bool) $form->getInput("rating_pages"));
            $this->lm->setDisableDefaultFeedback((int) $form->getInput("disable_def_feedback"));
            $this->lm->setProgressIcons((int) $form->getInput("progr_icons"));
            $this->reading_time_gui->saveSettingFromForm($this->form);

            $add_info = "";
            $store_tries = $form->getInput("store_tries");
            if ($form->getInput("restrict_forw_nav") && !$form->getInput("store_tries")) {
                $store_tries = 1;
                $add_info = "</br>" . $lng->txt("cont_automatically_set_store_tries");
                $add_info = str_replace("$1", $lng->txt("cont_tries_store"), $add_info);
                $add_info = str_replace("$2", $lng->txt("cont_restrict_forw_nav"), $add_info);
            }

            $this->lm->setStoreTries((int) $store_tries);
            $this->lm->setRestrictForwardNavigation((int) $form->getInput("restrict_forw_nav"));
            $this->lm->updateProperties();
            $this->lm->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->lm)->saveTileImage();

            ilNotification::setNotification(
                ilNotification::TYPE_LM_BLOCKED_USERS,
                $ilUser->getId(),
                $this->lm->getId(),
                (bool) $this->form->getInput("notification_blocked_users")
            );

            // services
            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $this->form,
                array(
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY
                )
            );


            // Update ecs export settings
            $ecs = new ilECSLearningModuleSettings($this->lm);
            if ($ecs->handleSettingsUpdate()) {
                $valid = true;
            }
        }

        if ($valid) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified") . $add_info, true);
            $this->ctrl->redirect($this, "properties");
        } else {
            $lng->loadLanguageModule("style");
            $this->setTabs("settings");
            $this->setSubTabs("cont_general_properties");

            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    public function initMenuForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // enable menu
        $menu = new ilCheckboxInputGUI($this->lng->txt("cont_active"), "cobj_act_lm_menu");
        $menu->setChecked($this->lm->isActiveLMMenu());
        $form->addItem($menu);

        // toc
        $toc = new ilCheckboxInputGUI($this->lng->txt("cont_toc"), "cobj_act_toc");
        $toc->setChecked($this->lm->isActiveTOC());
        $form->addItem($toc);

        // print view
        $print = new ilCheckboxInputGUI($this->lng->txt("cont_print_view"), "cobj_act_print");
        $print->setChecked($this->lm->isActivePrintView());
        $form->addItem($print);

        // prevent glossary appendix
        $glo = new ilCheckboxInputGUI($this->lng->txt("cont_print_view_pre_glo"), "cobj_act_print_prev_glo");
        $glo->setChecked($this->lm->isActivePreventGlossaryAppendix());
        $print->addSubItem($glo);

        // hide header and footer in print view
        $hhfp = new ilCheckboxInputGUI($this->lng->txt("cont_hide_head_foot_print"), "hide_head_foot_print");
        $hhfp->setChecked($this->lm->getHideHeaderFooterPrint());
        $print->addSubItem($hhfp);

        // downloads
        $no_download_file_available =
            " " . $lng->txt("cont_no_download_file_available") .
            " <a href='" . $ilCtrl->getLinkTargetByClass("ilexportgui", "") . "'>" . $lng->txt("change") . "</a>";
        $types = array("xml", "html");
        foreach ($types as $type) {
            if ($this->lm->getPublicExportFile($type) != "") {
                if (is_file($this->lm->getExportDirectory($type) . "/" .
                    $this->lm->getPublicExportFile($type))) {
                    $no_download_file_available = "";
                }
            }
        }
        $dl = new ilCheckboxInputGUI($this->lng->txt("cont_downloads"), "cobj_act_downloads");
        $dl->setInfo($this->lng->txt("cont_downloads_desc") . $no_download_file_available);
        $dl->setChecked($this->lm->isActiveDownloads());
        $form->addItem($dl);

        // downloads in public area
        $pdl = new ilCheckboxInputGUI($this->lng->txt("cont_downloads_public_desc"), "cobj_act_downloads_public");
        $pdl->setChecked($this->lm->isActiveDownloadsPublic());
        $dl->addSubItem($pdl);

        $form->addCommandButton("saveMenuProperties", $lng->txt("save"));

        $form->setTitle($lng->txt("cont_lm_menu"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    public function editMenuProperties(): void
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

    public function saveMenuProperties(): void
    {
        $form = $this->initMenuForm();
        if ($form->checkInput()) {
            $this->lm->setActiveLMMenu((int) $form->getInput("cobj_act_lm_menu"));
            $this->lm->setActiveTOC((int) $form->getInput("cobj_act_toc"));
            $this->lm->setActivePrintView((int) $form->getInput("cobj_act_print"));
            $this->lm->setActivePreventGlossaryAppendix((int) $form->getInput("cobj_act_print_prev_glo"));
            $this->lm->setHideHeaderFooterPrint((int) $form->getInput("hide_head_foot_print"));
            $this->lm->setActiveDownloads((int) $form->getInput("cobj_act_downloads"));
            $this->lm->setActiveDownloadsPublic((int) $form->getInput("cobj_act_downloads_public"));
            $this->lm->updateProperties();
        }

        $this->__initLMMenuEditor();
        $this->lmme_obj->updateActiveStatus($this->edit_request->getMenuEntries());

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editMenuProperties");
    }

    public function proceedDragDrop(): void
    {
        $ilCtrl = $this->ctrl;

        $req = $this->edit_request;
        $this->lm->executeDragDrop(
            $req->getHFormPar("source_id"),
            $req->getHFormPar("target_id"),
            $req->getHFormPar("fc"),
            $req->getHFormPar("as_subitem")
        );
        $ilCtrl->redirect($this, "chapters");
    }

    protected function afterSave(ilObject $new_object): void
    {
        $new_object->setCleanFrames(true);
        $new_object->update();

        // create content object tree
        $new_object->createLMTree();

        // create a first chapter
        $new_object->addFirstChapterAndPage();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt($this->type . "_added"), true);
        $this->ctrl->setParameterByClass(ilObjLearningModuleGUI::class, "ref_id", $new_object->getRefId());
        $this->ctrl->redirectByClass([ilLMEditorGUI::class, ilObjLearningModuleGUI::class], "");
    }

    protected function initImportForm(string $new_type): ilPropertyFormGUI
    {
        $form = parent::initImportForm($new_type);

        // validation
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_validate_file"), "validate");
        $cb->setInfo($this->lng->txt(""));
        $form->addItem($cb);
        return $form;
    }

    protected function importFileObject(int $parent_id = null, bool $catch_errors = true): void
    {
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
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("obj_import_file_error") . " <br />" . $e->getMessage());
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
            return;
        }

        if (!$no_manifest) {
            return;			// something different has gone wrong, but we have a manifest, this is definitely not "the old" import
        }

        throw new ilLMOldExportFileException("This file seems to be from ILIAS version 5.0.x or lower. Import is not supported anymore.");
    }

    /**
     * show chapters
     */
    public function chapters(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();
        $this->setContentSubTabs("chapters");

        $ilCtrl->setParameter($this, "backcmd", "chapters");

        $form_gui = new ilChapterHierarchyFormGUI($this->lm->getType(), $this->requested_transl);
        $form_gui->setFormAction($ilCtrl->getFormAction($this));
        $form_gui->setTitle($this->lm->getTitle());
        $form_gui->setIcon(ilUtil::getImagePath("icon_lm.svg"));
        $form_gui->setTree($this->lm_tree);
        $form_gui->setMaxDepth(0);
        $this->tree->readRootId();
        $form_gui->setCurrentTopNodeId($this->tree->getRootId());
        $form_gui->addMultiCommand($lng->txt("delete"), "delete");
        $form_gui->addMultiCommand($lng->txt("cut"), "cutItems");
        $form_gui->addMultiCommand($lng->txt("copy"), "copyItems");
        if ($this->lm->getLayoutPerPage()) {
            $form_gui->addMultiCommand($lng->txt("cont_set_layout"), "setPageLayoutInHierarchy");
        }
        $form_gui->setDragIcon(ilUtil::getImagePath("icon_st.svg"));
        $form_gui->addCommand($lng->txt("cont_save_all_titles"), "saveAllTitles");
        $up_gui = "ilobjlearningmodulegui";

        $ctpl = new ilTemplate("tpl.chap_and_pages.html", true, true, "Modules/LearningModule");
        $ctpl->setVariable("HIERARCHY_FORM", $form_gui->getHTML());
        $ilCtrl->setParameter($this, "obj_id", null);

        $ml_head = self::getMultiLangHeader($this->lm->getId(), $this);

        $this->tpl->setContent($ml_head . $ctpl->get());
    }

    public static function getMultiLangHeader(
        int $a_lm_id,
        object $a_gui_class,
        string $a_mode = ""
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $edit_request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $requested_transl = $edit_request->getTranslation();
        $requested_totransl = $edit_request->getToTranslation();

        $ml_head = "";

        // multi language
        $ot = ilObjectTranslation::getInstance($a_lm_id);
        if ($ot->getContentActivated()) {
            $ilCtrl->setParameter($a_gui_class, "lang_switch_mode", $a_mode);
            $lng->loadLanguageModule("meta");

            // info
            $ml_gui = new ilPageMultiLangGUI("lm", $a_lm_id);
            $ml_head = $ml_gui->getMultiLangInfo($requested_transl);

            // language switch
            $list = new ilAdvancedSelectionListGUI();
            $list->setListTitle($lng->txt("actions"));
            $list->setId("copage_act");
            $entries = false;
            if (!in_array($requested_transl, array("", "-"))) {
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
                if ($requested_transl != $al &&
                    $al != $ot->getMasterLanguage()) {
                    $ilCtrl->setParameter($a_gui_class, "totransl", $al);
                    $list->addItem(
                        $lng->txt("cont_edit_language_version") . ": " .
                        $lng->txt("meta_l_" . $al),
                        "",
                        $ilCtrl->getLinkTarget($a_gui_class, "switchToLanguage")
                    );
                    $ilCtrl->setParameter($a_gui_class, "totransl", $requested_totransl);
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

    public function pages(): void
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

        $t = new ilLMPagesTableGUI($this, "pages", $this->lm);
        $tpl->setContent($t->getHTML());
    }

    /**
     * List all broken links
     */
    public function listLinks(): void
    {
        $tpl = $this->tpl;

        $this->setTabs();
        $this->setContentSubTabs("internal_links");

        $table_gui = new ilLinksTableGUI(
            $this,
            "listLinks",
            $this->lm->getId(),
            $this->lm->getType()
        );

        $tpl->setContent($table_gui->getHTML());
    }

    /**
     * Show maintenance
     */
    public function showMaintenance(): void
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
    public function activatePages(): void
    {
        $ids = $this->edit_request->getIds();
        foreach ($ids as $id) {
            $act = ilLMPage::_lookupActive($id, $this->lm->getType());
            ilLMPage::_writeActive($id, $this->lm->getType(), !$act);
        }

        $this->ctrl->redirect($this, "pages");
    }

    /**
     * paste page
     */
    public function pastePage(): void
    {
        if (ilEditClipboard::getContentObjectType() != "pg") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_page_in_clipboard"), true);
            $this->ctrl->redirect($this, "pages");
        }

        // paste selected object
        $id = ilEditClipboard::getContentObjectId();

        // copy page, if action is copy
        if (ilEditClipboard::getAction() == "copy") {
            // check wether page belongs to lm
            if (ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId())
                == $this->lm->getId()) {
                $lm_page = new ilLMPageObject($this->lm, $id);
                $new_page = $lm_page->copy($this->lm);
                $id = $new_page->getId();
            } else {
                // get page from other content object into current content object
                $lm_id = ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId());
                /** @var ilObjLearningModule $lm_obj */
                $lm_obj = ilObjectFactory::getInstanceByObjId($lm_id);
                $lm_page = new ilLMPageObject($lm_obj, $id);
                $copied_nodes = array();
                $new_page = $lm_page->copyToOtherContObject($this->lm, $copied_nodes);
                $id = $new_page->getId();
                ilLMObject::updateInternalLinks($copied_nodes);
            }
        }

        // cut is not be possible in "all pages" form yet
        if (ilEditClipboard::getAction() == "cut") {
            // check wether page belongs not to lm
            if (ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId())
                != $this->lm->getId()) {
                $lm_id = ilLMObject::_lookupContObjID(ilEditClipboard::getContentObjectId());
                /** @var ilObjLearningModule $lm_obj */
                $lm_obj = ilObjectFactory::getInstanceByObjId($lm_id);
                $lm_page = new ilLMPageObject($lm_obj, $id);
                $lm_page->setLMId($this->lm->getId());
                $lm_page->update();
                $page = $lm_page->getPageObject();
                $page->buildDom();
                $page->setParentId($this->lm->getId());
                $page->update();
            }
        }


        ilEditClipboard::clear();
        $this->ctrl->redirect($this, "pages");
    }

    public function copyPage(): void
    {
        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            $this->ctrl->redirect($this, "pages");
        }

        ilLMObject::clipboardCopy($this->lm->getId(), $ids);
        ilEditClipboard::setAction("copy");

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_selected_items_have_been_copied"), true);

        $this->ctrl->redirect($this, "pages");
    }

    /**
     * confirm deletion screen for page object and structure object deletion
     * @param int $a_parent_subobj_id id of parent object (structure object)
     *								  of the objects, that should be deleted
     *								  (or no parent object id for top level)
     */
    public function delete(int $a_parent_subobj_id = 0): void
    {
        $ids = $this->edit_request->getIds();

        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->cancelDelete();
        }

        if (count($ids) == 1 && $ids[0] == ilTree::POS_FIRST_NODE) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_item"), true);
            $this->cancelDelete();
        }

        if ($a_parent_subobj_id == 0) {
            $this->setTabs();
        }

        if ($a_parent_subobj_id != 0) {
            $this->ctrl->setParameterByClass("ilStructureObjectGUI", "backcmd", $this->requested_backcmd);
            $this->ctrl->setParameterByClass("ilStructureObjectGUI", "obj_id", $a_parent_subobj_id);
            $form_action = $this->ctrl->getFormActionByClass("ilStructureObjectGUI");
        } else {
            $this->ctrl->setParameter($this, "backcmd", $this->requested_backcmd);
            $form_action = $this->ctrl->getFormAction($this);
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($form_action);
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");

        foreach ($ids as $id) {
            if ($id != ilTree::POS_FIRST_NODE) {
                $obj = new ilLMObject($this->lm, $id);
                $caption = $obj->getTitle();

                $cgui->addItem("id[]", $id, $caption);
            }
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->ctrl->redirect($this, $this->requested_backcmd);
    }

    /**
     * delete page object or structure objects
     *
     * @param	int		$a_parent_subobj_id		id of parent object (structure object)
     *											of the objects, that should be deleted
     *											(or no parent object id for top level)
     */
    public function confirmedDelete(int $a_parent_subobj_id = 0): void
    {
        $tree = new ilLMTree($this->lm->getId());

        $ids = $this->edit_request->getIds();

        // check number of objects
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            $this->ctrl->redirect($this, "cancelDelete");
        }

        // delete all selected objects
        foreach ($ids as $id) {
            if ($id != ilTree::POS_FIRST_NODE) {
                $obj = ilLMObjectFactory::getInstance($this->lm, $id, false);
                $node_data = $tree->getNodeData($id);
                if (is_object($obj)) {
                    $obj->setLMId($this->lm->getId());

                    ilHistory::_createEntry(
                        $this->lm->getId(),
                        "delete_" . $obj->getType(),
                        array(ilLMObject::_lookupTitle($id), $id),
                        $this->lm->getType()
                    );

                    $obj->delete();
                }
                if ($tree->isInTree($id)) {
                    $tree->deleteTree($node_data);
                }
            }
        }

        // check the tree
        $this->lm->checkTree();

        // feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("info_deleted"), true);

        if ($a_parent_subobj_id == 0) {
            $this->ctrl->redirect($this, $this->requested_backcmd);
        }
    }

    public function getContextPath(
        int $a_endnode_id,
        int $a_startnode_id = 1
    ): string {
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

    public function showActions(array $a_actions): void
    {
        $d = null;
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

    public function view(): void
    {
        if (strtolower($this->requested_baseClass) == "iladministrationgui") {
            $this->prepareOutput();
            parent::viewObject();
        } else {
            $this->viewObject();
        }
    }


    /**
     * move a single chapter  (selection)
     */
    public function moveChapter(int $a_parent_subobj_id = 0): void
    {
        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"));
            if ($a_parent_subobj_id == 0) {
                $this->ctrl->redirect($this, "chapters");
            }
            return;
        }
        if (count($ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_max_one_item"));
            if ($a_parent_subobj_id == 0) {
                $this->ctrl->redirect($this, "chapters");
            }
            return;
        }

        if (count($ids) == 1 && $ids[0] == ilTree::POS_FIRST_NODE) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_item"));
            if ($a_parent_subobj_id == 0) {
                $this->ctrl->redirect($this, "chapters");
            }
        }

        // SAVE POST VALUES
        ilEditClipboard::storeContentObject("st", $ids[0], "move");

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_chap_select_target_now"), true);

        if ($a_parent_subobj_id == 0) {
            $this->ctrl->redirect($this, "chapters");
        }
    }

    public function copyChapter(): void
    {
        $this->copyItems();
    }

    public function pasteChapter(): void
    {
        $this->insertChapterClip();
    }

    public function movePage(): void
    {
        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "pages");
        }

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_selected_items_have_been_cut"), true);

        ilLMObject::clipboardCut($this->lm->getId(), $ids);
        ilEditClipboard::setAction("cut");

        $this->ctrl->redirect($this, "pages");
    }

    public function cancel(): void
    {
        if ($this->requested_new_type == "pg") {
            $this->ctrl->redirect($this, "pages");
        } else {
            $this->ctrl->redirect($this, "chapters");
        }
    }

    public function export(): void
    {
        $ot = ilObjectTranslation::getInstance($this->lm->getId());
        $opt = "";
        if ($ot->getContentActivated()) {
            $format = explode("_", $this->edit_request->getFormat());
            $opt = ilUtil::stripSlashes($format[1]);
        }


        $cont_exp = new ilContObjectExport($this->lm);
        $cont_exp->buildExportFile($opt);
    }

    /**
     * Get public access value for export table
     */
    public function getPublicAccessColValue(
        string $a_type,
        string $a_file
    ): string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $add = "";

        $changelink = "<a href='" . $ilCtrl->getLinkTarget($this, "editMenuProperties") . "'>" . $lng->txt("change") . "</a>";
        if (!$this->lm->isActiveLMMenu()) {
            $add = "<br />" . $lng->txt("cont_download_no_menu") . " " . $changelink;
        } elseif (!$this->lm->isActiveDownloads()) {
            $add = "<br />" . $lng->txt("cont_download_no_download") . " " . $changelink;
        }

        $basetype = explode("_", $a_type);
        $basetype = $basetype[0];

        if ($this->lm->getPublicExportFile($basetype) == $a_file) {
            return $lng->txt("yes") . $add;
        }

        return " ";
    }

    public function publishExportFile(
        ?array $a_files
    ): void {
        $ilCtrl = $this->ctrl;

        if (!isset($a_files)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
        } else {
            foreach ($a_files as $f) {
                $file = explode(":", $f);
                if (is_int(strpos($file[0], "_"))) {
                    $file[0] = explode("_", $file[0])[0];
                }
                $export_dir = $this->lm->getExportDirectory($file[0]);

                if ($this->lm->getPublicExportFile($file[0]) ==
                    $file[1]) {
                    $this->lm->setPublicExportFile($file[0], "");
                } else {
                    $this->lm->setPublicExportFile($file[0], $file[1]);
                }
            }
            $this->lm->update();
        }
        $ilCtrl->redirectByClass("ilexportgui");
    }

    public function fixTreeConfirm(): void
    {
        $this->setTabs();
        $this->setContentSubTabs("maintenance");

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("cont_fix_tree_confirm"));
        $cgui->setCancel($this->lng->txt("cancel"), "showMaintenance");
        $cgui->setConfirm($this->lng->txt("cont_fix_tree"), "fixTree");
        $issues = $this->lm->checkStructure();
        $mess = "";
        if (count($issues) > 0) {
            $mess = "Found Issues: <br>" . implode("<br>", $issues);
        }
        $this->tpl->setContent($cgui->getHTML() . $mess);
    }

    public function fixTree(): void
    {
        $this->lm->fixTree();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("cont_tree_fixed"), true);
        $this->ctrl->redirect($this, "showMaintenance");
    }

    public function exportHTML(): void
    {
        $ot = ilObjectTranslation::getInstance($this->lm->getId());
        $lang = "";
        if ($ot->getContentActivated()) {
            $format = explode("_", $this->edit_request->getFormat());
            $lang = ilUtil::stripSlashes($format[1]);
        }
        $cont_exp = new ilContObjectExport($this->lm, "html", $lang);
        $cont_exp->buildExportFile();
    }

    /**
     * display locator
     * @param bool $a_omit_obj_id set to true, if obj id is not page id (e.g. permission gui)
     */
    public function addLocations(
        bool $a_omit_obj_id = false
    ): void {
        $locator = $this->locator;

        $obj_id = 0;
        if (!$a_omit_obj_id) {
            $obj_id = $this->requested_obj_id;
        }
        $lmtree = $this->lm->getTree();
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
                $this->ctrl->setParameter($this, "obj_id", null);
                $locator->addItem($this->lm->getTitle(), $this->ctrl->getLinkTarget($this, "chapters"));
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
            $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
        }
    }

    ////
    //// Questions
    ////


    public function listQuestions(): void
    {
        $tpl = $this->tpl;

        $this->setTabs("questions");
        $this->setQuestionsSubTabs("question_stats");

        $table = new ilLMQuestionListTableGUI($this, "listQuestions", $this->lm);
        $tpl->setContent($table->getHTML());
    }

    public function listBlockedUsers(): void
    {
        $tpl = $this->tpl;

        $this->setTabs("questions");
        $this->setQuestionsSubTabs("blocked_users");

        $table = new ilLMBlockedUsersTableGUI($this, "listBlockedUsers", $this->lm);
        $tpl->setContent($table->getHTML());
    }

    public function resetNumberOfTries(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $user_q_ids = $this->edit_request->getUserQuestionIds();
        if (count($user_q_ids) > 0) {
            foreach ($user_q_ids as $uqid) {
                $uqid = explode(":", $uqid);
                ilPageQuestionProcessor::resetTries((int) $uqid[0], (int) $uqid[1]);
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listBlockedUsers");
    }

    public function unlockQuestion(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $user_q_ids = $this->edit_request->getUserQuestionIds();
        if (count($user_q_ids) > 0) {
            foreach ($user_q_ids as $uqid) {
                $uqid = explode(":", $uqid);
                ilPageQuestionProcessor::unlock((int) $uqid[0], (int) $uqid[1]);
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listBlockedUsers");
    }

    public function sendMailToBlockedUsers(): void
    {
        $ilCtrl = $this->ctrl;

        $user_q_ids = $this->edit_request->getUserQuestionIds();
        if (count($user_q_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), 1);
            $ilCtrl->redirect($this, "listBlockedUsers");
        }

        $rcps = array();
        foreach ($user_q_ids as $uqid) {
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

    protected function getBlockedUsersMailSignature(): string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('cont_blocked_users_mail_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->lm->getRefId());
        return rawurlencode(base64_encode($link));
    }


    ////
    //// Tabs
    ////

    protected function setTabs(string $a_act = ""): void
    {
        parent::setTitleAndDescription();
        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("lm");
        $this->addTabs($a_act);
    }

    public function setContentSubTabs(string $a_active): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lm_set = new ilSetting("lm");

        // chapters
        $ilTabs->addSubTab(
            "chapters",
            $lng->txt("cont_chapters"),
            $ilCtrl->getLinkTarget($this, "chapters")
        );

        // all pages
        $ilTabs->addSubTab(
            "pages",
            $lng->txt("cont_all_pages"),
            $ilCtrl->getLinkTarget($this, "pages")
        );

        // all pages
        $ilTabs->addSubTab(
            "short_titles",
            $lng->txt("cont_short_titles"),
            $ilCtrl->getLinkTargetByClass("illmeditshorttitlesgui", "")
        );

        // export ids
        if ($lm_set->get("html_export_ids")) {
            if (!ilObjContentObject::isOnlineHelpModule($this->lm->getRefId())) {
                $ilTabs->addSubTab(
                    "export_ids",
                    $lng->txt("cont_html_export_ids"),
                    $ilCtrl->getLinkTarget($this, "showExportIDsOverview")
                );
            }
        }
        if (ilObjContentObject::isOnlineHelpModule($this->lm->getRefId())) {
            $lng->loadLanguageModule("help");
            $ilTabs->addSubTab(
                "export_ids",
                $lng->txt("cont_online_help_ids"),
                $ilCtrl->getLinkTarget($this, "showExportIDsOverview")
            );

            $ilTabs->addSubTab(
                "help_tooltips",
                $lng->txt("help_tooltips"),
                $ilCtrl->getLinkTarget($this, "showTooltipList")
            );
        }

        // list links
        $ilTabs->addSubTab(
            "internal_links",
            $lng->txt("cont_internal_links"),
            $ilCtrl->getLinkTarget($this, "listLinks")
        );

        $ilTabs->addSubTab(
            "history",
            $lng->txt("history"),
            $this->ctrl->getLinkTarget($this, "history")
        );

        // maintenance
        $ilTabs->addSubTab(
            "maintenance",
            $lng->txt("cont_maintenance"),
            $ilCtrl->getLinkTarget($this, "showMaintenance")
        );

        // srt files
        $ilTabs->addSubTab(
            "srt_files",
            $lng->txt("cont_subtitle_files"),
            $ilCtrl->getLinkTargetByClass("ilmobmultisrtuploadgui", "")
        );

        // srt files
        $ilTabs->addSubTab(
            "import",
            $lng->txt("cont_import"),
            $ilCtrl->getLinkTargetByClass("illmimportgui", "")
        );

        $ilTabs->activateSubTab($a_active);
        $ilTabs->activateTab("content");
    }

    public function setQuestionsSubTabs(string $a_active): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        // chapters
        $ilTabs->addSubTab(
            "question_stats",
            $lng->txt("cont_question_stats"),
            $ilCtrl->getLinkTarget($this, "listQuestions")
        );

        // blocked users
        $ilTabs->addSubTab(
            "blocked_users",
            $lng->txt("cont_blocked_users"),
            $ilCtrl->getLinkTarget($this, "listBlockedUsers")
        );

        $ilTabs->activateSubTab($a_active);
    }

    public function addTabs(string $a_act = ""): void
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
        if ($this->object->isInfoEnabled()) {
            $ilTabs->addTab(
                "info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", 'showSummary')
            );
        }

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
        if (ilLearningProgressAccess::checkAccess($this->lm->getRefId()) and ($this->lm->getType() == 'lm')) {
            $ilTabs->addTab(
                'learning_progress',
                $lng->txt("learning_progress"),
                $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'), '')
            );
        }

        // meta data
        $mdgui = new ilObjectMetaDataGUI($this->lm);
        $mdtab = $mdgui->getTab();
        if ($mdtab) {
            $ilTabs->addTab(
                "meta",
                $lng->txt("meta_data"),
                $mdtab
            );
        }

        // export
        $ilTabs->addTab(
            "export",
            $lng->txt("export"),
            $this->ctrl->getLinkTargetByClass("ilexportgui", "")
        );

        // permissions
        if ($rbacsystem->checkAccess('edit_permission', $this->lm->getRefId())) {
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
            "ilias.php?baseClass=ilLMPresentationGUI&ref_id=" . $this->lm->getRefId(),
            "_top"
        );
    }

    public function setSubTabs(string $a_active): void
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
                $this->ctrl->getLinkTargetByClass("ilObjectContentStyleSettingsGUI", ""),
                "",
                "ilObjectContentStyleSettingsGUI"
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

            $lti_settings = new ilLTIProviderObjectSettingGUI($this->lm->getRefId());
            if ($lti_settings->hasSettingsAccess()) {
                $ilTabs->addSubTabTarget(
                    'lti_provider',
                    $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class)
                );
            }

            $ilTabs->setSubTabActive($a_active);
        }
    }

    public function editPublicSection(): void
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilAccess = $this->access;


        if (!$ilAccess->checkAccessOfUser(ANONYMOUS_USER_ID, "read", "", $this->lm->getRefId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cont_anonymous_user_missing_perm"));
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
        $si->setValue($this->lm->getPublicAccessMode());
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->addFormButton($this->lng->txt("save"), "savePublicSectionAccess");
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this, "savePublicSectionAccess"));

        if ($this->lm->getPublicAccessMode() == "selected") {
            $this->tpl->setCurrentBlock("select_pages");
            $this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this, "savePublicSectionPages"));

            $tree = new ilPublicSectionExplorerGUI(
                $this,
                "editPublicSection",
                $this->lm_obj,
                $this->edit_request->getTranslation()
            );
            $tree->setSelectMode("pages", true);
            $tree->setSkipRootNode(true);

            $this->tpl->setVariable("EXPLORER", $tree->getHTML());
            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

            $this->tpl->parseCurrentBlock();
        }
    }

    public function savePublicSection(): void
    {
        $this->lm->setPublicAccessMode(
            $this->edit_request->getLMPublicMode()
        );
        $this->lm->updateProperties();
        ilLMObject::_writePublicAccessStatus(
            $this->edit_request->getPublicPages(),
            $this->lm->getId()
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editPublicSection");
    }

    /**
     * Saves lm access mode
     */
    public function savePublicSectionAccess(): void
    {
        $this->lm->setPublicAccessMode(
            $this->edit_request->getLMPublicMode()
        );
        $this->lm->updateProperties();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editPublicSection");
    }

    /**
     * Saves public lm pages
     */
    public function savePublicSectionPages(): void
    {
        ilLMObject::_writePublicAccessStatus(
            $this->edit_request->getPublicPages(),
            $this->lm->getId()
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editPublicSection");
    }

    public function history(): void
    {
        $this->setTabs("content");
        $this->setContentSubTabs("history");

        $hist_gui = new ilHistoryTableGUI(
            $this,
            "history",
            $this->lm->getId(),
            $this->lm->getType()
        );
        $hist_gui->initTable();
        $hist_gui->setCommentVisibility($this->lm->isActiveHistoryUserComments());

        $this->tpl->setContent($hist_gui->getHTML());
    }

    public function __initLMMenuEditor(): void
    {
        $this->lmme_obj = new ilLMMenuEditor();
        $this->lmme_obj->setObjId($this->lm->getId());
    }

    /**
     * display add menu entry form
     */
    public function addMenuEntry(?ilPropertyFormGUI $form = null): void
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;

        $this->setTabs();

        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");

        $ilToolbar->addButton(
            $this->lng->txt("lm_menu_select_internal_object"),
            $ilCtrl->getLinkTarget($this, "showEntrySelector")
        );

        if (is_null($form)) {
            $form = $this->initMenuEntryForm("create");
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initMenuEntryForm(string $a_mode = "edit"): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($this->lng->txt("lm_menu_entry_title"), "title");
        $ti->setMaxLength(255);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // target
        $ta = new ilTextInputGUI($this->lng->txt("lm_menu_entry_target"), "target");
        $ta->setMaxLength(255);
        $ta->setSize(40);
        $ta->setRequired(true);
        $form->addItem($ta);

        if ($a_mode == "edit") {
            $this->__initLMMenuEditor();
            $this->lmme_obj->readEntry($this->edit_request->getMenuEntry());
            $ti->setValue($this->lmme_obj->getTitle());
            $ta->setValue($this->lmme_obj->getTarget());
        }

        if ($this->requested_link_ref_id > 0) {
            $link_ref_id = $this->requested_link_ref_id;
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

    public function saveMenuEntry(): void
    {
        $form = $this->initMenuEntryForm("create");
        if ($form->checkInput()) {
            $this->__initLMMenuEditor();
            $this->lmme_obj->setTitle($form->getInput("title"));
            $this->lmme_obj->setTarget($form->getInput("target"));
            $this->lmme_obj->setLinkRefId((int) $form->getInput("link_ref_id"));

            if ($form->getInput("link_ref_id")) {
                $this->lmme_obj->setLinkType("intern");
            }

            $this->lmme_obj->create();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_entry_added"), true);
            $this->ctrl->redirect($this, "editMenuProperties");
        } else {
            $form->setValuesByPost();
            $this->addMenuEntry($form);
        }
    }

    public function deleteMenuEntry(): void
    {
        if (empty($this->requested_menu_entry)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_menu_entry_id"), true);
            $this->ctrl->redirect($this, "editMenuProperties");
        }

        $this->__initLMMenuEditor();
        $this->lmme_obj->delete($this->requested_menu_entry);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_entry_removed"), true);
        $this->ctrl->redirect($this, "editMenuProperties");
    }

    public function editMenuEntry(?ilPropertyFormGUI $form = null): void
    {
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $this->setTabs();

        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");


        if (empty($this->requested_menu_entry)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_menu_entry_id"), true);
            $this->ctrl->redirect($this, "editMenuProperties");
        }

        $ilCtrl->saveParameter($this, array("menu_entry"));
        $ilToolbar->addButton(
            $this->lng->txt("lm_menu_select_internal_object"),
            $ilCtrl->getLinkTarget($this, "showEntrySelector")
        );

        if (is_null($form)) {
            $form = $this->initMenuEntryForm("edit");
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function updateMenuEntry(): void
    {
        $form = $this->initMenuEntryForm("edit");
        if ($form->checkInput()) {
            if ($this->edit_request->getMenuEntry() == "") {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_menu_entry_id"), true);
                $this->ctrl->redirect($this, "editMenuProperties");
            }

            $this->__initLMMenuEditor();
            $this->lmme_obj->readEntry($this->edit_request->getMenuEntry());
            $this->lmme_obj->setTitle($form->getInput("title"));
            $this->lmme_obj->setTarget($form->getInput("target"));
            if ($form->getInput("link_ref_id")) {
                $this->lmme_obj->setLinkType("intern");
            }
            if (is_int(strpos($form->getInput("target"), "."))) {
                $this->lmme_obj->setLinkType("extern");
            }
            $this->lmme_obj->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_entry_updated"), true);
            $this->ctrl->redirect($this, "editMenuProperties");
        } else {
            $form->setValuesByPost();
            $this->editMenuEntry($form);
        }
    }

    public function showEntrySelector(): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $this->setTabs();

        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_lm_menu");

        $ilCtrl->saveParameter($this, array("menu_entry"));

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lm_menu_object_selector.html", "Modules/LearningModule");

        $this->tpl->setOnScreenMessage('info', $this->lng->txt("lm_menu_select_object_to_add"));

        $exp = new ilLMMenuObjectSelector(
            $this->ctrl->getLinkTarget($this, 'test'),
            $this,
            $this->edit_request->getMenuEntry()
        );

        $exp->setExpand($this->requested_lm_menu_expand ?: $this->tree->readRootId());
        $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showEntrySelector'));
        $exp->setTargetGet("ref_id");
        $exp->setRefId($this->requested_ref_id);

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
        $select_public_mode = ilLegacyFormElementsUtil::formSelect(
            $this->lm->getPublicAccessMode(),
            "lm_public_mode",
            $modes,
            false,
            true
        );
        $this->tpl->setVariable("SELECT_PUBLIC_MODE", $select_public_mode);

        $this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("choose_public_pages"));
        $this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
        $this->tpl->setVariable("EXPLORER", $output);
        //$this->tpl->setVariable("ONCLICK", $js_pages);
        $this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
        $this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
        $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this, "savePublicSection"));
    }

    /**
     * select page as header
     */
    public function selectHeader(): void
    {
        $ids = $this->edit_request->getIds();
        if (count($ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "pages");
        }
        if (count($ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_max_one_item"), true);
            $this->ctrl->redirect($this, "pages");
        }
        if ($ids[0] != $this->lm->getHeaderPage()) {
            $this->lm->setHeaderPage($ids[0]);
        } else {
            $this->lm->setHeaderPage(0);
        }
        $this->lm->updateProperties();
        $this->ctrl->redirect($this, "pages");
    }

    /**
     * select page as footer
     */
    public function selectFooter(): void
    {
        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "pages");
        }
        if (count($ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cont_select_max_one_item"), true);
            $this->ctrl->redirect($this, "pages");
        }
        if ($ids[0] != $this->lm->getFooterPage()) {
            $this->lm->setFooterPage($ids[0]);
        } else {
            $this->lm->setFooterPage(0);
        }
        $this->lm->updateProperties();
        $this->ctrl->redirect($this, "pages");
    }

    /**
     * Save all titles of chapters/pages
     */
    public function saveAllTitles(): void
    {
        $ilCtrl = $this->ctrl;

        ilLMObject::saveTitles(
            $this->lm,
            $this->edit_request->getTitles(),
            $this->requested_transl
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("lm_save_titles"), true);
        $ilCtrl->redirect($this, "chapters");
    }

    /**
     * Insert (multiple) chapters at node
     */
    public function insertChapter(): void
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
            $target = ilTree::POS_FIRST_NODE;
        }

        for ($i = 1; $i <= $num; $i++) {
            $chap = new ilStructureObject($this->lm);
            $chap->setType("st");
            $chap->setTitle($lng->txt("cont_new_chap"));
            $chap->setLMId($this->lm->getId());
            $chap->create();
            ilLMObject::putInTree($chap, $parent_id, $target);
        }

        $ilCtrl->redirect($this, "chapters");
    }

    /**
     * Insert Chapter from clipboard
     */
    public function insertChapterClip(): void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilLog = $this->log;

        $node_id = ilChapterHierarchyFormGUI::getPostNodeId();
        $first_child = ilChapterHierarchyFormGUI::getPostFirstChild();

        if (!$first_child) {	// insert after node id
            $parent_id = $this->lm_tree->getParentId($node_id);
            $target = $node_id;
        } else {													// insert as first child
            $parent_id = $node_id;
            $target = ilTree::POS_FIRST_NODE;
        }

        // copy and paste
        $chapters = $ilUser->getClipboardObjects("st", true);
        $copied_nodes = array();
        foreach ($chapters as $chap) {
            $ilLog->write("Call pasteTree, Target LM: " . $this->lm->getId() . ", Chapter ID: " . $chap["id"]
                . ", Parent ID: " . $parent_id . ", Target: " . $target);
            $cid = ilLMObject::pasteTree(
                $this->lm,
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

        $this->lm->checkTree();
        $ilCtrl->redirect($this, "chapters");
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $ctrl->setParameterByClass("ilLMPresentationGUI", "ref_id", $a_target);
            $ctrl->redirectByClass("ilLMPresentationGUI", "resume");
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            $ctrl->setParameterByClass("ilLMPresentationGUI", "ref_id", $a_target);
            $ctrl->redirectByClass("ilLMPresentationGUI", "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }


        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    public function cutItems(string $a_return = "chapters"): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, $a_return);
        }

        $todel = array();			// delete IDs < 0 (needed for non-js editing)
        foreach ($ids as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($ids[$k]);
        }
        ilLMObject::clipboardCut($this->lm->getId(), $ids);
        ilEditClipboard::setAction("cut");
        $this->tpl->setOnScreenMessage('info', $lng->txt("cont_selected_items_have_been_cut"), true);

        $ilCtrl->redirect($this, $a_return);
    }

    /**
     * Copy items to clipboard
     */
    public function copyItems(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "chapters");
        }

        $todel = array();				// delete IDs < 0 (needed for non-js editing)
        foreach ($ids as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($ids[$k]);
        }
        ilLMObject::clipboardCopy($this->lm->getId(), $ids);
        ilEditClipboard::setAction("copy");
        $this->tpl->setOnScreenMessage('info', $lng->txt("cont_selected_items_have_been_copied"), true);
        $ilCtrl->redirect($this, "chapters");
    }

    /**
     * Cut chapter(s)
     */
    public function cutChapter(): void
    {
        $this->cutItems("chapters");
    }

    ////
    //// HTML export IDs
    ////

    public function showExportIDsOverview(bool $a_validation = false): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();
        $this->setContentSubTabs("export_ids");

        if (ilObjContentObject::isOnlineHelpModule($this->lm->getRefId())) {
            // toolbar
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $lm_tree = $this->lm->getTree();
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

            $tbl = new ilHelpMappingTableGUI($this, "showExportIDsOverview", $a_validation);
        } else {
            $tbl = new ilExportIDTableGUI($this, "showExportIDsOverview", $a_validation, false);
        }

        $tpl->setContent($tbl->getHTML());
    }

    public function filterHelpChapters(): void
    {
        $ilCtrl = $this->ctrl;
        ilSession::set("help_chap", $this->edit_request->getHelpChap());
        $ilCtrl->redirect($this, "showExportIDsOverview");
    }

    public function saveExportIds(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // check all export ids
        $ok = true;
        foreach ($this->edit_request->getExportIds() as $exp_id) {
            if ($exp_id != "" && !preg_match(
                "/^([a-zA-Z]+)[0-9a-zA-Z_]*$/",
                trim($exp_id)
            )) {
                $ok = false;
            }
        }
        if (!$ok) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("cont_exp_ids_not_resp_format1") . ": a-z, A-Z, 0-9, '_'. " .
                $lng->txt("cont_exp_ids_not_resp_format3") . " " .
                $lng->txt("cont_exp_ids_not_resp_format2"));
            $this->showExportIDsOverview(true);
            return;
        }


        foreach ($this->edit_request->getExportIds() as $pg_id => $exp_id) {
            ilLMPageObject::saveExportId(
                $this->lm->getId(),
                $pg_id,
                ilUtil::stripSlashes($exp_id),
                ilLMObject::_lookupType($pg_id)
            );
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("cont_saved_export_ids"), true);
        $ilCtrl->redirect($this, "showExportIdsOverview");
    }

    public function saveHelpMapping(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        foreach ($this->edit_request->getScreenIds() as $chap => $ids) {
            $ids = explode("\n", $ids);
            ilHelpMapping::saveScreenIdsForChapter($chap, $ids);
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showExportIdsOverview");
    }

    ////
    //// Help tooltips
    ////

    public function showTooltipList(): void
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

    public function addTooltip(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $tt_id = $this->edit_request->getTooltipId();
        if (trim($tt_id) != "") {
            if (is_int(strpos($tt_id, "_"))) {
                ilHelp::addTooltip(trim($tt_id), "");
                $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

                $fu = strpos($tt_id, "_");
                $comp = substr($tt_id, 0, $fu);
                ilSession::set("help_tt_comp", ilUtil::stripSlashes($comp));
            } else {
                $this->tpl->setOnScreenMessage('failure', $lng->txt("cont_help_no_valid_tooltip_id"), true);
            }
        }
        $ilCtrl->redirect($this, "showTooltipList");
    }

    public function filterTooltips(): void
    {
        $ilCtrl = $this->ctrl;

        ilSession::set(
            "help_tt_comp",
            $this->edit_request->getTooltipComponent()
        );
        $ilCtrl->redirect($this, "showTooltipList");
    }

    public function saveTooltips(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $tooltip_ids = $this->edit_request->getTooltipIds();
        foreach ($this->edit_request->getTooltipTexts() as $id => $text) {
            ilHelp::updateTooltip(
                (int) $id,
                $text,
                $tooltip_ids[(int) $id]
            );
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showTooltipList");
    }

    public function deleteTooltips(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ids = $this->edit_request->getIds();
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                ilHelp::deleteTooltip($id);
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showTooltipList");
    }

    ////
    //// Set layout
    ////

    public static function getLayoutOption(
        string $a_txt,
        string $a_var,
        string $a_def_option = ""
    ): ilRadioGroupInputGUI {
        global $DIC;

        $im_tag = "";

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
     * Set layout for multiple pages
     */
    public function setPageLayoutInHierarchy(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "hierarchy", "1");
        $this->setPageLayout(true);
    }


    /**
     * Set layout for multiple pages
     */
    public function setPageLayout(
        bool $a_in_hierarchy = false
    ): void {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ids = $this->edit_request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);

            if ($a_in_hierarchy) {
                $ilCtrl->redirect($this, "chapters");
            } else {
                $ilCtrl->redirect($this, "pages");
            }
        }

        $this->initSetPageLayoutForm();

        $tpl->setContent($this->form->getHTML());
    }

    public function initSetPageLayoutForm(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();

        $ids = $this->edit_request->getIds();
        foreach ($ids as $id) {
            $hi = new ilHiddenInputGUI("id[]");
            $hi->setValue($id);
            $this->form->addItem($hi);
        }
        $layout = self::getLayoutOption(
            $lng->txt("cont_layout"),
            "layout",
            $this->lm->getLayout()
        );
        $this->form->addItem($layout);

        $this->form->addCommandButton("savePageLayout", $lng->txt("save"));
        $this->form->addCommandButton("pages", $lng->txt("cancel"));

        $this->form->setTitle($lng->txt("cont_set_layout"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function savePageLayout(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "hierarchy", $this->requested_hierarchy);

        $ids = $this->edit_request->getIds();
        foreach ($ids as $id) {
            ilLMPageObject::writeLayout(
                $id,
                $this->edit_request->getLayout(),
                $this->lm
            );
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);

        if ($this->requested_hierarchy) {
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
     */
    public function editGlossaries(): void
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

        $tab = new ilLMGlossaryTableGUI($this->lm, $this, "editGlossaries");

        $tpl->setContent($tab->getHTML());
    }

    public function showLMGlossarySelector(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $ilTabs = $this->tabs;

        $this->setTabs();
        $ilTabs->setTabActive("settings");
        $this->setSubTabs("cont_glossaries");

        $exp = new ilSearchRootSelector($ilCtrl->getLinkTarget($this, 'showLMGlossarySelector'));
        $exp->setExpand($this->requested_search_root_expand ?: $tree->readRootId());
        $exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'showLMGlossarySelector'));
        $exp->setTargetClass(get_class($this));
        $exp->setCmd('confirmGlossarySelection');
        $exp->setClickableTypes(array("glo"));
        $exp->addFilter("glo");

        // build html-output
        $exp->setOutput(0);
        $tpl->setContent($exp->getOutput());
    }

    public function confirmGlossarySelection(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $cgui = new ilConfirmationGUI();
        $ilCtrl->setParameter($this, "glo_ref_id", $this->requested_root_id);
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setHeaderText($lng->txt("cont_link_glo_in_lm"));
        $cgui->setCancel($lng->txt("no"), "selectLMGlossary");
        $cgui->setConfirm($lng->txt("yes"), "selectLMGlossaryLink");
        $tpl->setContent($cgui->getHTML());
    }

    public function selectLMGlossaryLink(): void
    {
        $glo_ref_id = $this->requested_glo_ref_id;
        $this->lm->autoLinkGlossaryTerms($glo_ref_id);
        $this->selectLMGlossary();
    }

    public function selectLMGlossary(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $glos = $this->lm->getAutoGlossaries();
        $glo_ref_id = $this->requested_glo_ref_id;
        $glo_id = ilObject::_lookupObjId($glo_ref_id);
        if (!in_array($glo_id, $glos)) {
            $glos[] = $glo_id;
        }
        $this->lm->setAutoGlossaries($glos);
        $this->lm->update();

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editGlossaries");
    }

    public function removeLMGlossary(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->lm->removeAutoGlossary($this->requested_glo_id);
        $this->lm->update();

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editGlossaries");
    }

    public function editMasterLanguage(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "transl", "");
        if ($this->lang_switch_mode == "short_titles") {
            $ilCtrl->redirectByClass("illmeditshorttitlesgui", "");
        }
        $ilCtrl->redirect($this, "chapters");
    }

    public function switchToLanguage(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "transl", $this->requested_totransl);
        if ($this->lang_switch_mode == "short_titles") {
            $ilCtrl->redirectByClass("illmeditshorttitlesgui", "");
        }
        $ilCtrl->redirect($this, "chapters");
    }

    public function redrawHeaderAction(): void
    {
        // #12281
        parent::redrawHeaderActionObject();
    }

    /**
     * Learning progress
     */
    protected function learningProgress(): void
    {
        $this->ctrl->redirectByClass(array('illearningprogressgui'), '');
    }
}
