<?php

declare(strict_types=1);

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

use ILIAS\GlobalScreen\ScreenContext\ContextServices;

/**
 * Class ilObjSCORMLearningModuleGUI
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Hendrik Holtmann <holtmann@mac.com>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 *
 * @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilFileSystemGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilLearningProgressGUI
 * @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilInfoScreenGUI, ilSCORM2004ChapterGUI, ilSCORM2004SeqChapterGUI, ilSCORM2004PageNodeGUI, ilSCORM2004ScoGUI
 * @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilCertificateGUI, ilObjStyleSheetGUI, ilNoteGUI, ilSCORM2004AssetGUI
 * @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilSCORM2004TrackingItemsPerScoFilterGUI, ilSCORM2004TrackingItemsPerUserFilterGUI, ilSCORM2004TrackingItemsTableGUI
 * @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilLTIProviderObjectSettingGUI
 */
class ilObjSCORM2004LearningModuleGUI extends ilObjSCORMLearningModuleGUI
{
    protected \ILIAS\DI\Container $dic;
    protected ilTabsGUI $tabs;
    protected ilRbacSystem $rbacsystem;
    protected ilHelpGUI $help;
    protected ilErrorHandling $error;
    protected ContextServices $tool_context;

    public function __construct(array $a_data, int $a_id, bool $a_call_by_reference, ?bool $a_prepare_output = true)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->tree = $DIC->repositoryTree();
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();
        $this->help = $DIC["ilHelp"];
        $this->error = $DIC["ilErr"];
        $this->user = $DIC->user();
        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $lng = $DIC->language();

        $lng->loadLanguageModule("content");
        $lng->loadLanguageModule("sahs");
        $lng->loadLanguageModule("search");
        $lng->loadLanguageModule("exp");
        $this->type = "sahs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        #$this->tabs_gui = new ilTabsGUI();
    }

    public function executeCommand(): void
    {
//        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
//        $tpl = $this->tpl;
//        $ilTabs = $this->tabs;
//        $lng = $this->lng;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        parent::executeCommand();
        $this->addHeaderAction();
    }

    /**
     * Scorm 2004 module properties
     */
    public function properties(): void
    {
        $rbacsystem = $this->rbacsystem;
        $tree = $this->tree;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $ilTabs = $this->tabs;

        $this->setSettingsSubTabs();
        $ilTabs->setSubTabActive('cont_settings');
        // view
        $ilToolbar->addButtonInstance($this->object->getViewButton());

        // output forms
        $this->initPropertiesForm();
        $this->getPropertiesFormValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Initialize properties form
     */
    public function initPropertiesForm(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $obj_service = $this->getObjectService();

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt("cont_lm_properties"));

        //title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "Fobject_title");
        $ti->setMaxLength(200);
        $this->form->addItem($ti);

        //description
        $ti = new ilTextAreaInputGUI($this->lng->txt("description"), "Fobject_description");
        $this->form->addItem($ti);

        // SCORM-type
        $ne = new ilNonEditableValueGUI($this->lng->txt("type"), "");
        $ne->setValue($this->lng->txt("lm_type_" . ilObjSAHSLearningModule::_lookupSubType($this->object->getID())));
        $this->form->addItem($ne);

        // version
        $ne = new ilNonEditableValueGUI($this->lng->txt("cont_sc_version"), "");
        $ne->setValue($this->object->getModuleVersion());
        $ne->setInfo($this->lng->txt("cont_sc_version_info"));
        $this->form->addItem($ne);

        //
        // activation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("activation"));
        $this->form->addItem($sh);

        // online
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
        $cb->setInfo($this->lng->txt("cont_online_info"));
        $this->form->addItem($cb);


        //
        // presentation
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_presentation"));
        $this->form->addItem($sh);

        // display mode (open)
        // $options = array(
        // "0" => $this->lng->txt("cont_open_normal"),
        // "1" => $this->lng->txt("cont_open_iframe_max"),
        // "2" => $this->lng->txt("cont_open_iframe_defined"),
        // "5" => $this->lng->txt("cont_open_window_undefined"),
        // "6" => $this->lng->txt("cont_open_window_defined")
        // );
        // $si = new ilSelectInputGUI($this->lng->txt("cont_open"), "open_mode");
        // $si->setOptions($options);
        // $si->setValue($this->object->getOpenMode());
        // $this->form->addItem($si);

        $radg = new ilRadioGroupInputGUI($lng->txt("cont_open"), "open_mode");
        $op0 = new ilRadioOption($this->lng->txt("cont_open_normal"), "0");
        $radg->addOption($op0);
        $op1 = new ilRadioOption($this->lng->txt("cont_open_iframe"), "1");
        $radg->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("cont_open_window"), "5");
        $radg->addOption($op2);
        // width
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $op2->addSubItem($ni);
        // height
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_0");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op1->addSubItem($ni);
        $ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height_1");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $ni->setInfo($this->lng->txt("cont_width_height_info"));
        $op2->addSubItem($ni);

        // force IE to render again
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_ie_force_render"), "cobj_ie_force_render");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_ie_force_render_info"));
        $op2->addSubItem($cb);

        $this->form->addItem($radg);


        // disable top menu
        //Hide Top Navigation Bar
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_nomenu"), "cobj_nomenu");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_nomenu_info"));
        $this->form->addItem($cb);

        // disable left-side navigation
        // Hide Left Navigation Tree
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_hidenavig"), "cobj_hidenavig");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_hidenavig_info"));
        $this->form->addItem($cb);

        // auto navigation to last visited item
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_last_visited"), "cobj_auto_last_visited");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_auto_last_visited_info"));
        $this->form->addItem($cb);

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

        //
        // scorm options
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_scorm_options"));
        $this->form->addItem($sh);

        // lesson mode
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_def_lesson_mode"), "lesson_mode");
        $op0 = new ilRadioOption($this->lng->txt("cont_sc_less_mode_normal"), "normal");
        $radg->addOption($op0);
        $op1 = new ilRadioOption($this->lng->txt("cont_sc_less_mode_browse"), "browse");
        $radg->addOption($op1);
        // credit mode
        $cmradg = new ilRadioGroupInputGUI($lng->txt("cont_credit_mode"), "credit_mode");
        $cmop0 = new ilRadioOption($this->lng->txt("cont_credit_on"), "credit");
        $cmradg->addOption($cmop0);
        $cmop1 = new ilRadioOption($this->lng->txt("cont_credit_off"), "no_credit");
        $cmradg->addOption($cmop1);
        $op0->addSubItem($cmradg);
        // set lesson mode review when completed
        $options = array(
            "n" => $this->lng->txt("cont_sc_auto_review_no"),
            "r" => $this->lng->txt("cont_sc_auto_review_completed_not_failed_or_passed"),
            "p" => $this->lng->txt("cont_sc_auto_review_passed"),
            "q" => $this->lng->txt("cont_sc_auto_review_passed_or_failed"),
            "c" => $this->lng->txt("cont_sc_auto_review_completed"),
            "d" => $this->lng->txt("cont_sc_auto_review_completed_and_passed"),
            "y" => $this->lng->txt("cont_sc_auto_review_completed_or_passed"),
            "s" => $this->lng->txt("cont_sc_store_if_previous_score_was_lower")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_auto_review_2004"), "auto_review");
        $si->setOptions($options);
        $si->setInfo($this->lng->txt("cont_sc_auto_review_info_2004"));
        $op0->addSubItem($si);
        // end lesson mode
        $this->form->addItem($radg);


        // mastery_score
        if ($this->object->getMasteryScoreValues() != "") {
            $ni = new ilNumberInputGUI($this->lng->txt("cont_mastery_score_2004"), "mastery_score");
            $ni->setMaxLength(3);
            $ni->setSize(3);
            $ni->setInfo($this->lng->txt("cont_mastery_score_2004_info") . $this->object->getMasteryScoreValues());
            $this->form->addItem($ni);
        }

        //
        // rte settings
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_rte_settings"));
        $this->form->addItem($sh);

        // unlimited session timeout
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_usession"), "cobj_session");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_sc_usession_info"));
        $this->form->addItem($cb);

        // SCORM 2004 fourth edition features
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_fourth_edition"), "cobj_fourth_edition");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_fourth_edition_info"));
        $this->form->addItem($cb);

        // sequencing
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_sequencing"), "cobj_sequencing");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_sequencing_info"));
        $this->form->addItem($cb);

        // storage of interactions
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_interactions"), "cobj_interactions");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_interactions_info"));
        $this->form->addItem($cb);

        // objectives
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_objectives"), "cobj_objectives");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_objectives_info"));
        $this->form->addItem($cb);

        // comments
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_comments"), "cobj_comments");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_comments_info"));
        $this->form->addItem($cb);

        // time from lms
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_time_from_lms"), "cobj_time_from_lms");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_time_from_lms_info"));
        $this->form->addItem($cb);

        // check values
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_check_values"), "cobj_check_values");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_check_values_info"));
        $this->form->addItem($cb);

        // auto cmi.exit to suspend
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_suspend"), "cobj_auto_suspend");
        $cb->setValue("y");
        $cb->setInfo($this->lng->txt("cont_auto_suspend_info"));
        $this->form->addItem($cb);

        // settings for student_id
        $options = array(
            0 => $this->lng->txt("cont_sc_id_setting_user_id"),
            1 => $this->lng->txt("cont_sc_id_setting_user_login"),
            2 => $this->lng->txt("cont_sc_id_setting_user_id_plus_ref_id"),
            3 => $this->lng->txt("cont_sc_id_setting_user_login_plus_ref_id"),
            4 => $this->lng->txt("cont_sc_id_setting_user_id_plus_obj_id"),
            5 => $this->lng->txt("cont_sc_id_setting_user_login_plus_obj_id")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_id_setting_2004"), "id_setting");
        $si->setOptions($options);
        $si->setInfo($this->lng->txt("cont_sc_id_setting_info"));
        $this->form->addItem($si);

        // settings for student_name
        $options = array(
            0 => $this->lng->txt("cont_sc_name_setting_last_firstname"),
            1 => $this->lng->txt("cont_sc_name_setting_first_lastname"),
            2 => $this->lng->txt("cont_sc_name_setting_fullname"),
            3 => $this->lng->txt("cont_sc_name_setting_salutation_lastname"),
            4 => $this->lng->txt("cont_sc_name_setting_first_name"),
            9 => $this->lng->txt("cont_sc_name_setting_no_name")
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_sc_name_setting_2004"), "name_setting");
        $si->setOptions($options);
        $si->setInfo($this->lng->txt("cont_sc_name_setting_info"));
        $this->form->addItem($si);

        //
        // debugging
        //
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("cont_debugging"));
        $this->form->addItem($sh);

        // test tool
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_debug"), "cobj_debug");
        $cb->setValue("y");
        if ($this->object->getDebugActivated() == false) {
            $cb->setDisabled(true);
            $cb->setInfo($this->lng->txt("cont_debug_deactivated"));
        } else {
            $cb->setInfo($this->lng->txt("cont_debug_deactivate"));
        }
        $this->form->addItem($cb);
        $this->form->addCommandButton("saveProperties", $lng->txt("save"));
    }


    /**
     * Get values for properties form
     */
    public function getPropertiesFormValues(): void
    {
        //check/select only once
        $this->object->checkMasteryScoreValues();

        $values = array();
        $values["Fobject_title"] = $this->object->getTitle();
        $values["Fobject_description"] = $this->object->getDescription();
        if (!$this->object->getOfflineStatus()) {
            $values["cobj_online"] = true;
        }
        $values["open_mode"] = $this->object->getOpenMode();
        $values["width_0"] = $this->object->getWidth();
        $values["width_1"] = $this->object->getWidth();
        $values["height_0"] = $this->object->getHeight();
        $values["height_1"] = $this->object->getHeight();
        $values["cobj_ie_force_render"] = $this->object->getIe_force_render();
        $values["cobj_nomenu"] = $this->object->getNoMenu();
        $values["cobj_hidenavig"] = $this->object->getHideNavig();
        $values["cobj_auto_last_visited"] = $this->object->getAuto_last_visited();
        $values["lesson_mode"] = $this->object->getDefaultLessonMode();
        $values["credit_mode"] = $this->object->getCreditMode();
        $values["auto_review"] = $this->object->getAutoReviewChar();
        $values["mastery_score"] = $this->object->getMasteryScore();
        $values["cobj_session"] = $this->object->getSession();
        $values["cobj_fourth_edition"] = $this->object->getFourth_edition();
        $values["cobj_sequencing"] = $this->object->getSequencing();
        $values["cobj_interactions"] = $this->object->getInteractions();
        $values["cobj_objectives"] = $this->object->getObjectives();
        $values["cobj_comments"] = $this->object->getComments();
        $values["cobj_time_from_lms"] = $this->object->getTime_from_lms();
        $values["cobj_check_values"] = $this->object->getCheck_values();
        $values["cobj_auto_suspend"] = $this->object->getAutoSuspend();
        $values["id_setting"] = $this->object->getIdSetting();
        $values["name_setting"] = $this->object->getNameSetting();
        $values["cobj_debug"] = $this->object->getDebug();
        $this->form->setValuesByArray($values);
    }

    /**
    * save scorm 2004 module properties
    */
    public function saveProperties(): void
    {
        $ilSetting = $this->settings;
        $obj_service = $this->getObjectService();
        $this->initPropertiesForm();
        $this->form->checkInput();

        if ($this->dic->http()->wrapper()->post()->has('mastery_score')) {
            $this->object->setMasteryScore($this->dic->http()->wrapper()->post()->retrieve('mastery_score', $this->dic->refinery()->kindlyTo()->int()));
            // $this->object->updateMasteryScoreValues();
        }

        $t_auto_review = $this->dic->http()->wrapper()->post()->retrieve('auto_review', $this->dic->refinery()->kindlyTo()->string());
        $t_auto_suspend = $this->dic->http()->wrapper()->post()->has('cobj_auto_suspend');
        $t_session = $this->dic->http()->wrapper()->post()->has('cobj_session');
        if ($t_auto_review === "s") {
            $t_auto_suspend = true;
            //if not storing without session
            $t_session = true;
        }

        $t_height = $this->object->getHeight();
        if ($this->dic->http()->wrapper()->post()->retrieve('height_0', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getHeight()) {
            $t_height = $this->dic->http()->wrapper()->post()->retrieve('height_0', $this->dic->refinery()->kindlyTo()->int());
        }
        if ($this->dic->http()->wrapper()->post()->retrieve('height_1', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getHeight()) {
            $t_height = $this->dic->http()->wrapper()->post()->retrieve('height_1', $this->dic->refinery()->kindlyTo()->int());
        }

        $t_width = $this->object->getWidth();
        if ($this->dic->http()->wrapper()->post()->retrieve('width_0', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getWidth()) {
            $t_width = $this->dic->http()->wrapper()->post()->retrieve('width_0', $this->dic->refinery()->kindlyTo()->int());
        }
        if ($this->dic->http()->wrapper()->post()->retrieve('width_1', $this->dic->refinery()->kindlyTo()->int()) != $this->object->getWidth()) {
            $t_width = $this->dic->http()->wrapper()->post()->retrieve('width_1', $this->dic->refinery()->kindlyTo()->int());
        }

        $this->object->setOfflineStatus(!($this->dic->http()->wrapper()->post()->has('cobj_online')));
        $this->object->setOpenMode($this->dic->http()->wrapper()->post()->retrieve('open_mode', $this->dic->refinery()->kindlyTo()->int()));
        $this->object->setWidth($t_width);
        $this->object->setHeight($t_height);
        $this->object->setCreditMode($this->dic->http()->wrapper()->post()->retrieve('credit_mode', $this->dic->refinery()->kindlyTo()->string()));
//        $this->object->setMaxAttempt($this->dic->http()->wrapper()->post()->retrieve('max_attempt',$this->dic->refinery()->kindlyTo()->int()));
        $this->object->setAutoReviewChar($t_auto_review);
        $this->object->setDefaultLessonMode($this->dic->http()->wrapper()->post()->retrieve('lesson_mode', $this->dic->refinery()->kindlyTo()->string()));
        $this->object->setSession($t_session);
        $this->object->setNoMenu($this->dic->http()->wrapper()->post()->has('cobj_nomenu'));
        $this->object->setHideNavig($this->dic->http()->wrapper()->post()->has('cobj_hidenavig'));
        $this->object->setAuto_last_visited($this->dic->http()->wrapper()->post()->has('cobj_auto_last_visited'));
        $this->object->setIe_force_render($this->dic->http()->wrapper()->post()->has('cobj_ie_force_render'));
        $this->object->setFourth_edition($this->dic->http()->wrapper()->post()->has('cobj_fourth_edition'));
        $this->object->setSequencing($this->dic->http()->wrapper()->post()->has('cobj_sequencing'));
        $this->object->setInteractions($this->dic->http()->wrapper()->post()->has('cobj_interactions'));
        $this->object->setObjectives($this->dic->http()->wrapper()->post()->has('cobj_objectives'));
        $this->object->setComments($this->dic->http()->wrapper()->post()->has('cobj_comments'));
        $this->object->setTime_from_lms($this->dic->http()->wrapper()->post()->has('cobj_time_from_lms'));
        $this->object->setCheck_values($this->dic->http()->wrapper()->post()->has('cobj_check_values'));
        $this->object->setAutoSuspend($t_auto_suspend);
//            $this->object->setOfflineMode($tmpOfflineMode);
        $this->object->setDebug($this->dic->http()->wrapper()->post()->has('cobj_debug'));//ilUtil::yn2tf($this->dic->http()->wrapper()->post()->retrieve('cobj_debug',$this->dic->refinery()->kindlyTo()->string())));
        $this->object->setIdSetting($this->dic->http()->wrapper()->post()->retrieve('id_setting', $this->dic->refinery()->kindlyTo()->int()));
        $this->object->setNameSetting($this->dic->http()->wrapper()->post()->retrieve('name_setting', $this->dic->refinery()->kindlyTo()->int()));
        $this->object->setTitle($this->dic->http()->wrapper()->post()->retrieve('Fobject_title', $this->dic->refinery()->kindlyTo()->string()));
        $this->object->setDescription($this->dic->http()->wrapper()->post()->retrieve('Fobject_description', $this->dic->refinery()->kindlyTo()->string()));

        // tile image
        $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

        $this->object->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "properties");
    }

    /**
    * show tracking data
    */
    protected function showTrackingItemsBySco(): bool
    {
        $ilTabs = $this->tabs;

        $this->setSubTabs();
        $ilTabs->setTabActive("cont_tracking_data");
        $ilTabs->setSubTabActive("cont_tracking_bysco");

        $reports = array('exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','tracInteractionItem','tracInteractionUser','tracInteractionUserAnswers');

        $scoSelected = "all";
        if ($this->dic->http()->wrapper()->post()->has('scoSelected')) {
            $scoSelected = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('scoSelected', $this->dic->refinery()->kindlyTo()->string()));
        }

        $this->ctrl->setParameter($this, 'scoSelected', $scoSelected);

        $report = "choose";
        if ($this->dic->http()->wrapper()->post()->has('report')) {
            $report = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('report', $this->dic->refinery()->kindlyTo()->string()));
        }
        $this->ctrl->setParameter($this, 'report', $report);
        $filter = new ilSCORM2004TrackingItemsPerScoFilterGUI($this, 'showTrackingItemsBySco');
        $filter->parse($scoSelected, $report, $reports);
        if ($report === "choose") {
            $this->tpl->setContent($filter->form->getHTML());
        } else {
            $scosSelected = array();
            if ($scoSelected !== "all") {
                $scosSelected[] = $scoSelected;
            } else {
                $tmpscos = $this->object->getTrackedItems();
                foreach ($tmpscos as $i => $value) {
                    $scosSelected[] = $value["id"];
                }
            }
            $a_users = ilTrQuery::getParticipantsForObject($this->ref_id);
            $tbl = new ilSCORM2004TrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItemsBySco', $a_users, $scosSelected, $report);
            $this->tpl->setContent($filter->form->getHTML() . $tbl->getHTML());
        }
        return true;
    }

    public function showTrackingItems(): bool
    {
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;

        $ilTabs->setTabActive('cont_tracking_data');

        if ($ilAccess->checkAccess("read_learning_progress", "", $this->object->getRefId())) {
            $this->setSubTabs();
            $ilTabs->setSubTabActive('cont_tracking_byuser');

            $reports = array('exportSelectedSuccess','exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','exportObjGlobalToSystem');

            $userSelected = "all";
            if ($this->dic->http()->wrapper()->post()->has('userSelected')) {
                $userSelected = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('userSelected', $this->dic->refinery()->kindlyTo()->string()));
            }
            $this->ctrl->setParameter($this, 'userSelected', $userSelected);

            $report = "choose";
            if ($this->dic->http()->wrapper()->post()->has('report')) {
                $report = ilUtil::stripSlashes($this->dic->http()->wrapper()->post()->retrieve('report', $this->dic->refinery()->kindlyTo()->string()));
            }
            $this->ctrl->setParameter($this, 'report', $report);
            $filter = new ilSCORM2004TrackingItemsPerUserFilterGUI($this, 'showTrackingItems');
            $filter->parse($userSelected, $report, $reports);
            if ($report === "choose") {
                $this->tpl->setContent($filter->form->getHTML());
            } else {
                $usersSelected = array();
                if ($userSelected !== "all") {
                    $usersSelected[] = $userSelected;
                } else {
                    $users = ilTrQuery::getParticipantsForObject($this->ref_id);
                    foreach ($users as $usr) {
                        $user = (int) $usr;
                        if (ilObject::_exists($user) && ilObject::_lookUpType($user) === 'usr') {
                            $usersSelected[] = $user;
                        }
                    }
                }
                $scosSelected = array();
                $tmpscos = $this->object->getTrackedItems();
                foreach ($tmpscos as $i => $value) {
                    $scosSelected[] = $value["id"];
                }
                $tbl = new ilSCORM2004TrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItems', $usersSelected, $scosSelected, $report);
                $this->tpl->setContent($filter->form->getHTML() . $tbl->getHTML());
            }
        } elseif ($ilAccess->checkAccess("edit_learning_progress", "", $this->object->getRefId())) {
            $this->modifyTrackingItems();
        }
        return true;
    }
}
