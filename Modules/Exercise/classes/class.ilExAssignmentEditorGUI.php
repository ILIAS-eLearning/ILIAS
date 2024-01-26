<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilExAssignmentEditorGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @ilCtrl_Calls ilExAssignmentEditorGUI: ilExAssignmentFileSystemGUI, ilExPeerReviewGUI, ilPropertyFormGUI
 *
* @ingroup ModulesExercise
*/
class ilExAssignmentEditorGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var int
     */
    protected $exercise_id;

    /**
     * @var ilExAssignment
     */
    protected $assignment;

    /**
     * @var bool
     */
    protected $enable_peer_review_completion;

    /**
     * @var ilExAssignmentTypes
     */
    protected $types;


    /**
     * @var ilExcRandomAssignmentManager
     */
    protected $random_manager;

    /**
     * @var ilObjExercise|null
     */
    protected $exc;

    /**
     * Constructor
     *
     * @param int $a_exercise_id
     * @param bool  $a_enable_peer_review_completion_settings
     * @param ilExAssignment $a_ass
     * @return object
     */
    public function __construct($a_exercise_id, $a_enable_peer_review_completion_settings, ilExAssignment $a_ass = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->settings = $DIC->settings();
        $this->help = $DIC["ilHelp"];
        $this->exercise_id = $a_exercise_id;
        $this->assignment = $a_ass;
        $this->enable_peer_review_completion = (bool) $a_enable_peer_review_completion_settings;
        $this->types = ilExAssignmentTypes::getInstance();
        $this->type_guis = ilExAssignmentTypesGUI::getInstance();
        $request = $DIC->exercise()->internal()->request();
        $this->exc = $request->getRequestedExercise();
        $this->random_manager = $DIC->exercise()->internal()->service()->getRandomAssignmentManager(
            $request->getRequestedExercise()
        );
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("listAssignments");
        
        switch ($class) {
            case "ilpropertyformgui":
                $form = $this->initAssignmentForm($_GET["ass_type"]);
                $ilCtrl->forwardCommand($form);
                break;

            // instruction files
            case "ilexassignmentfilesystemgui":
                $this->setAssignmentHeader();
                $ilTabs->activateTab("ass_files");
                
                $fstorage = new ilFSWebStorageExercise($this->exercise_id, $this->assignment->getId());
                $fstorage->create();

                $fs_gui = new ilExAssignmentFileSystemGUI($fstorage->getPath());
                $fs_gui->setTitle($lng->txt("exc_instruction_files"));
                $fs_gui->setTableId("excassfil" . $this->assignment->getId());
                $fs_gui->setAllowDirectories(false);
                $ilCtrl->forwardCommand($fs_gui);
                break;
                    
            case "ilexpeerreviewgui":
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, "listAssignments")
                );
        
                $peer_gui = new ilExPeerReviewGUI($this->assignment);
                $ilCtrl->forwardCommand($peer_gui);
                break;
            
            default:
                $this->{$cmd . "Object"}();
                break;
        }
    }
    
    /**
     * List assignments
     */
    public function listAssignmentsObject()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilToolbar->setFormAction($ilCtrl->getFormAction($this, "addAssignment"));
        
        $ilToolbar->addStickyItem($this->getTypeDropdown(), true);
        
        $button = ilSubmitButton::getInstance();
        $button->setCaption("exc_add_assignment");
        $button->setCommand("addAssignment");
        $ilToolbar->addStickyItem($button);
        
        
        $t = new ilAssignmentsTableGUI($this, "listAssignments", $this->exercise_id);
        $tpl->setContent($t->getHTML());
    }
    
    /**
     * Create assignment
     */
    public function addAssignmentObject()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        
        // #16163 - ignore ass id from request
        $this->assignment = null;

        if (!(int) $_POST["type"]) {
            $ilCtrl->redirect($this, "listAssignments");
        }

        $form = $this->initAssignmentForm((int) $_POST["type"], "create");
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Get type selection dropdown
     *
     * @return ilSelectInputGUI
     */
    protected function getTypeDropdown()
    {
        $lng = $this->lng;

        $types = [];
        foreach ($this->types->getAllAllowed($this->exc) as $k => $t) {
            $types[$k] = $t->getTitle();
        }

        $ty = new ilSelectInputGUI($lng->txt("exc_assignment_type"), "type");
        $ty->setOptions($types);
        $ty->setRequired(true);
        return $ty;
    }
    
    /**
    * Init assignment form.
    *
    * @param int $a_type
    * @param int $a_mode "create"/"edit"
    */
    protected function initAssignmentForm($a_type, $a_mode = "create")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ass_type = $this->types->getById($a_type);
        $ass_type_gui = $this->type_guis->getById($a_type);
        $ilCtrl->setParameter($this, "ass_type", $a_type);
        
        $lng->loadLanguageModule("form");
        $form = new ilPropertyFormGUI();
        if ($a_mode == "edit") {
            $form->setTitle($lng->txt("exc_edit_assignment"));
        } else {
            $form->setTitle($lng->txt("exc_new_assignment"));
        }
        $form->setFormAction($ilCtrl->getFormAction($this));

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $form->addItem($ti);

        // type
        $ty = $this->getTypeDropdown();
        $ty->setValue($a_type);
        $ty->setDisabled(true);
        $form->addItem($ty);

        //
        // type specific start
        //

        $ass_type_gui->addEditFormCustomProperties($form);

        //
        // type specific end
        //

        if ($ass_type->usesTeams()) {
            if ($a_mode == "edit") {
                $has_teams = (bool) count(ilExAssignmentTeam::getAssignmentTeamMap($this->assignment->getId()));
            } else {
                $has_teams = false;
            }

            // Radio for creators
            $rd_team = new ilRadioGroupInputGUI($lng->txt("exc_team_formation"), "team_creator");

            $radio_participants = new ilRadioOption(
                $lng->txt("exc_team_by_participants"),
                ilExAssignment::TEAMS_FORMED_BY_PARTICIPANTS,
                $lng->txt("exc_team_by_participants_info")
            );

            $radio_tutors = new ilRadioOption(
                $lng->txt("exc_team_by_tutors"),
                ilExAssignment::TEAMS_FORMED_BY_TUTOR,
                $lng->txt("exc_team_by_tutors_info")
            );

            #23679
            if (!$has_teams) {
                // Creation options
                $rd_creation_method = new ilRadioGroupInputGUI($lng->txt("exc_team_creation"), "team_creation");
                $rd_creation_method->setRequired(true);

                //manual
                $rd_creation_manual = new ilRadioOption(
                    $lng->txt("exc_team_by_tutors_manual"),
                    0,
                    $lng->txt("exc_team_by_tutors_manual_info")
                );
                $rd_creation_method->addOption($rd_creation_manual);

                //random options
                $add_info = "";
                if ($this->getExerciseTotalMembers() < 4) {
                    $add_info = " <strong>" . $lng->txt("exc_team_by_random_add_info") . "</strong>";
                }
                $rd_creation_random = new ilRadioOption(
                    $lng->txt("exc_team_by_random"),
                    ilExAssignment::TEAMS_FORMED_BY_RANDOM,
                    $lng->txt("exc_team_by_random_info") . "<br>" . $lng->txt("exc_total_members") . ": " . $this->getExerciseTotalMembers() . $add_info
                );
                $rd_creation_method->addOption($rd_creation_random);

                $number_teams = new ilNumberInputGUI($lng->txt("exc_num_teams"), "number_teams");
                $number_teams->setSize(3);
                $number_teams->setMinValue(1);
                $number_teams->setMaxValue($this->getExerciseTotalMembers());
                $number_teams->setRequired(true);
                $number_teams->setSuffix($lng->txt("exc_team_assignment_adopt_teams"));
                $rd_creation_random->addSubItem($number_teams);

                $min_team_participants = new ilNumberInputGUI($lng->txt("exc_min_team_participants"), "min_participants_team");
                $min_team_participants->setSize(3);
                $min_team_participants->setMinValue(1);
                $min_team_participants->setMaxValue($this->getExerciseTotalMembers());
                $min_team_participants->setRequired(true);
                $min_team_participants->setSuffix($lng->txt("exc_participants"));
                $rd_creation_random->addSubItem($min_team_participants);

                $max_team_participants = new ilNumberInputGUI($lng->txt("exc_max_team_participants"), "max_participants_team");
                $max_team_participants->setSize(3);
                $max_team_participants->setMinValue(1);
                $max_team_participants->setMaxValue($this->getExerciseTotalMembers());
                $max_team_participants->setRequired(true);
                $max_team_participants->setSuffix($lng->txt("exc_participants"));
                $rd_creation_random->addSubItem($max_team_participants);

                $options = ilExAssignmentTeam::getAdoptableTeamAssignments($this->exercise_id);
                if (count($options)) {
                    $radio_assignment = new ilRadioOption(
                        $lng->txt("exc_team_by_assignment"),
                        ilExAssignment::TEAMS_FORMED_BY_ASSIGNMENT,
                        $lng->txt("exc_team_by_assignment_info")
                    );

                    $radio_assignment_adopt = new ilRadioGroupInputGUI($lng->txt("exc_assignment"), "ass_adpt");
                    $radio_assignment_adopt->setRequired(true);
                    $radio_assignment_adopt->addOption(new ilRadioOption($lng->txt("exc_team_assignment_adopt_none"), -1));

                    foreach ($options as $id => $item) {
                        $option = new ilRadioOption($item["title"], $id);
                        $option->setInfo($lng->txt("exc_team_assignment_adopt_teams") . ": " . $item["teams"]);
                        $radio_assignment_adopt->addOption($option);
                    }
                    $radio_assignment->addSubItem($radio_assignment_adopt);
                    $rd_creation_method->addOption($radio_assignment);
                }

                $radio_tutors->addSubItem($rd_creation_method);
            }
            $rd_team->addOption($radio_participants);
            $rd_team->addOption($radio_tutors);
            /*if(!$has_teams) {
                $rd_team->addOption($radio_assignment);
            }*/
            $form->addItem($rd_team);

            if ($has_teams) {
                $rd_team->setDisabled(true);
            }
        }

        // mandatory
        if (!$this->random_manager->isActivated()) {
            $cb = new ilCheckboxInputGUI($lng->txt("exc_mandatory"), "mandatory");
            $cb->setInfo($lng->txt("exc_mandatory_info"));
            $cb->setChecked(true);
            $form->addItem($cb);
        } else {
            //
            $ne = new ilNonEditableValueGUI($lng->txt("exc_mandatory"), "");
            $ne->setValue($lng->txt("exc_mandatory_rand_determined"));
            $form->addItem($ne);
        }

        // Work Instructions
        $sub_header = new ilFormSectionHeaderGUI();
        $sub_header->setTitle($lng->txt("exc_work_instructions"), "work_instructions");
        $form->addItem($sub_header);

        $desc_input = new ilTextAreaInputGUI($lng->txt("exc_instruction"), "instruction");
        $desc_input->setRows(20);
        if (ilObjAdvancedEditing::_getRichTextEditor() === "tinymce") {
            $desc_input->setUseRte(true);
            $desc_input->setRteTagSet("mini");
        }
        $form->addItem($desc_input);

        // files
        if ($a_mode == "create") {
            $files = new ilFileWizardInputGUI($lng->txt('objs_file'), 'files');
            $files->setFilenames(array(0 => ''));
            $form->addItem($files);
        }

        // Schedule
        $sub_header = new ilFormSectionHeaderGUI();
        $sub_header->setTitle($lng->txt("exc_schedule"), "schedule");
        $form->addItem($sub_header);

        // start time
        $start_date = new ilDateTimeInputGUI($lng->txt("exc_start_time"), "start_time");
        $start_date->setShowTime(true);
        $form->addItem($start_date);

        // Deadline Mode
        $radg = new ilRadioGroupInputGUI($lng->txt("exc_deadline"), "deadline_mode");
        $radg->setValue(0);
        $op1 = new ilRadioOption($lng->txt("exc_fixed_date"), 0, $lng->txt("exc_fixed_date_info"));
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("exc_relative_date"), 1, $lng->txt("exc_relative_date_info"));
        $radg->addOption($op2);
        $form->addItem($radg);

        // Deadline fixed date
        $deadline = new ilDateTimeInputGUI($lng->txt("date"), "deadline");
        $deadline->setShowTime(true);
        $op1->addSubItem($deadline);
        
        // extended Deadline
        $deadline2 = new ilDateTimeInputGUI($lng->txt("exc_deadline_extended"), "deadline2");
        $deadline2->setInfo($lng->txt("exc_deadline_extended_info"));
        $deadline2->setShowTime(true);
        $deadline->addSubItem($deadline2);


        // submit reminder
        $rmd_submit = new ilCheckboxInputGUI($this->lng->txt("exc_reminder_submit_setting"), "rmd_submit_status");

        $rmd_submit_start = new ilNumberInputGUI($this->lng->txt("exc_reminder_start"), "rmd_submit_start");
        $rmd_submit_start->setSize(3);
        $rmd_submit_start->setMaxLength(3);
        $rmd_submit_start->setSuffix($lng->txt('days'));
        $rmd_submit_start->setInfo($this->lng->txt("exc_reminder_start_info"));
        $rmd_submit_start->setRequired(true);
        $rmd_submit_start->setMinValue(1);
        $rmd_submit->addSubItem($rmd_submit_start);

        $rmd_submit_frequency = new ilNumberInputGUI($this->lng->txt("exc_reminder_frequency"), "rmd_submit_freq");
        $rmd_submit_frequency->setSize(3);
        $rmd_submit_frequency->setMaxLength(3);
        $rmd_submit_frequency->setSuffix($lng->txt('days'));
        $rmd_submit_frequency->setRequired(true);
        $rmd_submit_frequency->setMinValue(1);
        $rmd_submit->addSubItem($rmd_submit_frequency);

        $rmd_submit_end = new ilDateTimeInputGUI($lng->txt("exc_reminder_end"), "rmd_submit_end");
        $rmd_submit_end->setRequired(true);
        $rmd_submit->addSubItem($rmd_submit_end);

        $rmd_submit->addSubItem($this->addMailTemplatesRadio(ilExAssignmentReminder::SUBMIT_REMINDER));

        // grade reminder
        $rmd_grade = new ilCheckboxInputGUI($this->lng->txt("exc_reminder_grade_setting"), "rmd_grade_status");

        $rmd_grade_frequency = new ilNumberInputGUI($this->lng->txt("exc_reminder_frequency"), "rmd_grade_freq");
        $rmd_grade_frequency->setSize(3);
        $rmd_grade_frequency->setMaxLength(3);
        $rmd_grade_frequency->setSuffix($lng->txt('days'));
        $rmd_grade_frequency->setRequired(true);
        $rmd_grade_frequency->setMinValue(1);
        $rmd_grade->addSubItem($rmd_grade_frequency);

        $rmd_grade_end = new ilDateTimeInputGUI($lng->txt("exc_reminder_end"), "rmd_grade_end");
        $rmd_grade_end->setRequired(true);
        $rmd_grade->addSubItem($rmd_grade_end);

        $rmd_grade->addSubItem($this->addMailTemplatesRadio(ilExAssignmentReminder::GRADE_REMINDER));

        $form->addItem($rmd_submit);
        $form->addItem($rmd_grade);

        // relative deadline
        $ti = new ilNumberInputGUI($lng->txt("days"), "relative_deadline");
        $ti->setMaxLength(3);
        $ti->setSize(3);
        $op2->addSubItem($ti);

        // last submission for relative deadline
        $last_submission = new ilDateTimeInputGUI($lng->txt("exc_rel_last_submission"), "rel_deadline_last_subm");
        $last_submission->setInfo($lng->txt("exc_rel_last_submission_info"));
        $last_submission->setShowTime(true);
        $op2->addSubItem($last_submission);



        // max number of files
        if ($ass_type->usesFileUpload()) {
            $sub_header = new ilFormSectionHeaderGUI();
            $sub_header->setTitle($ass_type->getTitle());
            $form->addItem($sub_header);
            $max_file_tgl = new ilCheckboxInputGUI($lng->txt("exc_max_file_tgl"), "max_file_tgl");
            $form->addItem($max_file_tgl);

            $max_file = new ilNumberInputGUI($lng->txt("exc_max_file"), "max_file");
            $max_file->setInfo($lng->txt("exc_max_file_info"));
            $max_file->setRequired(true);
            $max_file->setSize(3);
            $max_file->setMinValue(1);
            $max_file_tgl->addSubItem($max_file);
        }

        // after submission
        $sub_header = new ilFormSectionHeaderGUI();
        $sub_header->setTitle($lng->txt("exc_after_submission"), "after_submission");
        $form->addItem($sub_header);

        if (!$ass_type->usesTeams() && !$this->random_manager->isActivated()) {
            // peer review
            $peer = new ilCheckboxInputGUI($lng->txt("exc_peer_review"), "peer");
            $peer->setInfo($lng->txt("exc_peer_review_ass_setting_info"));
            $form->addItem($peer);
        }
        
        
        // global feedback
        
        $fb = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file"), "fb");
        $form->addItem($fb);
        
        $fb_file = new ilFileInputGUI($lng->txt("file"), "fb_file");
        $fb_file->setRequired(true); // will be disabled on update if file exists - see getAssignmentValues()
        // $fb_file->setAllowDeletion(true); makes no sense if required (overwrite or keep)
        $fb->addSubItem($fb_file);
        
        $fb_date = new ilRadioGroupInputGUI($lng->txt("exc_global_feedback_file_date"), "fb_date");
        $fb_date->setRequired(true);
        $fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_deadline"), ilExAssignment::FEEDBACK_DATE_DEADLINE));
        $fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_upload"), ilExAssignment::FEEDBACK_DATE_SUBMISSION));

        //Extra radio option with date selection
        $fb_date_custom_date = new ilDateTimeInputGUI($lng->txt("date"), "fb_date_custom");
        $fb_date_custom_date->setRequired(true);
        $fb_date_custom_date->setShowTime(true);
        $fb_date_custom_option = new ilRadioOption($lng->txt("exc_global_feedback_file_after_date"), ilExAssignment::FEEDBACK_DATE_CUSTOM);
        $fb_date_custom_option->addSubItem($fb_date_custom_date);
        $fb_date->addOption($fb_date_custom_option);


        $fb->addSubItem($fb_date);

        $fb_cron = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file_cron"), "fb_cron");
        $fb_cron->setInfo($lng->txt("exc_global_feedback_file_cron_info"));
        $fb->addSubItem($fb_cron);
        
        
        if ($a_mode == "create") {
            $form->addCommandButton("saveAssignment", $lng->txt("save"));
            $form->addCommandButton("listAssignments", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("updateAssignment", $lng->txt("save"));
            $form->addCommandButton("listAssignments", $lng->txt("cancel"));
        }
        
        return $form;
    }

    public function addMailTemplatesRadio($a_reminder_type)
    {
        global $DIC;

        $post_var = "rmd_" . $a_reminder_type . "_template_id";

        $r_group = new ilRadioGroupInputGUI($this->lng->txt("exc_reminder_mail_template"), $post_var);
        $r_group->setRequired(true);
        $r_group->addOption(new ilRadioOption($this->lng->txt("exc_reminder_mail_no_tpl"), 0));
        $r_group->setValue(0);

        switch ($a_reminder_type) {
            case ilExAssignmentReminder::SUBMIT_REMINDER:
                $context = new ilExcMailTemplateSubmitReminderContext();
                break;
            case ilExAssignmentReminder::GRADE_REMINDER:
                $context = new ilExcMailTemplateGradeReminderContext();
                break;
            case ilExAssignmentReminder::FEEDBACK_REMINDER:
                $context = new ilExcMailTemplatePeerReminderContext();
                break;
            default:
                exit();
        }

        /** @var \ilMailTemplateService $templateService */
        $templateService = $DIC['mail.texttemplates.service'];
        foreach ($templateService->loadTemplatesForContextId((string) $context->getId()) as $template) {
            $r_group->addOption(new ilRadioOption($template->getTitle(), $template->getTplId()));
            if ($template->isDefault()) {
                $r_group->setValue($template->getTplId());
            }
        }

        return $r_group;
    }

    /**
     * Custom form validation
     *
     * @param ilPropertyFormGUI $a_form
     * @return array
     */
    protected function processForm(ilPropertyFormGUI $a_form)
    {
        $lng = $this->lng;
                
        $protected_peer_review_groups = false;
        
        if ($this->assignment) {
            if ($this->assignment->getPeerReview()) {
                $peer_review = new ilExPeerReview($this->assignment);
                if ($peer_review->hasPeerReviewGroups()) {
                    $protected_peer_review_groups = true;
                }
            }
            
            if ($this->assignment->getFeedbackFile()) {
                $a_form->getItemByPostVar("fb_file")->setRequired(false); // #15467
            }
        }

        $valid = $a_form->checkInput();

        if ($protected_peer_review_groups) {
            // checkInput() will add alert to disabled fields
            $a_form->getItemByPostVar("deadline")->setAlert(null);
            $a_form->getItemByPostVar("deadline2")->setAlert(null);
        }
        
        if ($valid) {
            $type = $a_form->getInput("type");
            $ass_type = $this->types->getById($type);

            // dates
            
            $time_start = $a_form->getItemByPostVar("start_time")->getDate();
            $time_start = $time_start
                ? $time_start->get(IL_CAL_UNIX)
                : null;

            $time_deadline = $a_form->getItemByPostVar("deadline")->getDate();
            $time_deadline = $time_deadline
                ? $time_deadline->get(IL_CAL_UNIX)
                : null;
            $time_deadline_ext = $a_form->getItemByPostVar("deadline2")->getDate();
            $time_deadline_ext = $time_deadline_ext
                ? $time_deadline_ext->get(IL_CAL_UNIX)
                : null;
            $time_fb_custom_date = $a_form->getItemByPostVar("fb_date_custom")->getDate();
            $time_fb_custom_date = $time_fb_custom_date
                ? $time_fb_custom_date->get(IL_CAL_UNIX)
                : null;

            $reminder_submit_end_date = $a_form->getItemByPostVar("rmd_submit_end")->getDate();
            $reminder_submit_end_date = $reminder_submit_end_date
                ? $reminder_submit_end_date->get(IL_CAL_UNIX)
                : null;

            $reminder_grade_end_date = $a_form->getItemByPostVar("rmd_grade_end")->getDate();
            $reminder_grade_end_date = $reminder_grade_end_date
                ? $reminder_grade_end_date->get(IL_CAL_UNIX)
                : null;

            $time_deadline = null;
            $time_deadline_ext = null;

            if ((int) $a_form->getInput("deadline_mode") == ilExAssignment::DEADLINE_ABSOLUTE) {
                $time_deadline = $a_form->getItemByPostVar("deadline")->getDate();
                $time_deadline = $time_deadline
                    ? $time_deadline->get(IL_CAL_UNIX)
                    : null;
                $time_deadline_ext = $a_form->getItemByPostVar("deadline2")->getDate();
                $time_deadline_ext = $time_deadline_ext
                    ? $time_deadline_ext->get(IL_CAL_UNIX)
                    : null;
            }


            // handle disabled elements
            if ($protected_peer_review_groups) {
                $time_deadline = $this->assignment->getDeadline();
                $time_deadline_ext = $this->assignment->getExtendedDeadline();
            }
                    
            // no deadline?
            if (!$time_deadline) {
                // peer review
                if (!$protected_peer_review_groups &&
                    $a_form->getInput("peer")) {
                    $a_form->getItemByPostVar("peer")
                        ->setAlert($lng->txt("exc_needs_fixed_deadline"));
                    $valid = false;
                }
                // global feedback
                if ($a_form->getInput("fb") &&
                    $a_form->getInput("fb_date") == ilExAssignment::FEEDBACK_DATE_DEADLINE) {
                    $a_form->getItemByPostVar("fb")
                        ->setAlert($lng->txt("exc_needs_deadline"));
                    $valid = false;
                }
            } else {
                // #18269
                if ($a_form->getInput("peer")) {
                    $time_deadline_max = max($time_deadline, $time_deadline_ext);
                    $peer_dl = $this->assignment // #18380
                        ? $this->assignment->getPeerReviewDeadline()
                        : null;
                    if ($peer_dl && $peer_dl < $time_deadline_max) {
                        $a_form->getItemByPostVar($peer_dl < $time_deadline_ext
                            ? "deadline2"
                            : "deadline")
                            ->setAlert($lng->txt("exc_peer_deadline_mismatch"));
                        $valid = false;
                    }
                }
                
                if ($time_deadline_ext && $time_deadline_ext < $time_deadline) {
                    $a_form->getItemByPostVar("deadline2")
                        ->setAlert($lng->txt("exc_deadline_ext_mismatch"));
                    $valid = false;
                }
                    
                $time_deadline_min = $time_deadline_ext
                    ? min($time_deadline, $time_deadline_ext)
                    : $time_deadline;
                $time_deadline_max = max($time_deadline, $time_deadline_ext);
            
                // start > any deadline ?
                if ($time_start && $time_deadline_min && $time_start > $time_deadline_min) {
                    $a_form->getItemByPostVar("start_time")
                        ->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
                    $valid = false;
                }
            }

            if ($ass_type->usesTeams()) {
                if ($a_form->getInput("team_creation") == ilExAssignment::TEAMS_FORMED_BY_RANDOM &&
                    $a_form->getInput("team_creator") == ilExAssignment::TEAMS_FORMED_BY_TUTOR) {
                    $team_validation = $this->validationTeamsFormation(
                        $a_form->getInput("number_teams"),
                        $a_form->getInput("min_participants_team"),
                        $a_form->getInput("max_participants_team")
                    );
                    if ($team_validation['status'] == 'error') {
                        $a_form->getItemByPostVar("team_creation")
                            ->setAlert($team_validation['msg']);
                        $a_form->getItemByPostVar($team_validation["field"])
                            ->setAlert($lng->txt("exc_value_can_not_set"));
                        $valid = false;
                    }
                }
            }

            if ($valid) {
                $res = array(
                    // core
                    "type" => $a_form->getInput("type")
                    ,"title" => trim($a_form->getInput("title"))
                    ,"instruction" => trim($a_form->getInput("instruction"))
                    // dates
                    ,"start" => $time_start
                    ,"deadline" => $time_deadline
                    ,"deadline_ext" => $time_deadline_ext
                    ,"max_file" => $a_form->getInput("max_file_tgl")
                        ? $a_form->getInput("max_file")
                        : null
                );
                if (!$this->random_manager->isActivated()) {
                    $res["mandatory"] = $a_form->getInput("mandatory");
                }

                if ($a_form->getInput("team_creator") == ilExAssignment::TEAMS_FORMED_BY_TUTOR) {
                    $res['team_creator'] = $a_form->getInput("team_creator");
                    $res["team_creation"] = $a_form->getInput("team_creation");

                    if ($a_form->getInput("team_creation") == ilExAssignment::TEAMS_FORMED_BY_RANDOM) {
                        $res["number_teams"] = $a_form->getInput("number_teams");
                        $res["min_participants_team"] = $a_form->getInput("min_participants_team");
                        $res["max_participants_team"] = $a_form->getInput("max_participants_team");
                    } elseif ($a_form->getInput("team_creation") == ilExAssignment::TEAMS_FORMED_BY_ASSIGNMENT) {
                        $res['ass_adpt'] = $a_form->getInput("ass_adpt");
                    }
                }

                // portfolio template
                //if($a_form->getInput("template_id") && $a_form->getInput("template"))
                //{
                //	$res['template_id'] = $a_form->getInput("template_id");
                //}

                // text limitations
                /*
                if($a_form->getInput("limit_characters"))
                {
                    $res['limit_characters'] = $a_form->getInput("limit_characters");
                }
                if($a_form->getInput("limit_characters") && $a_form->getInput("max_char_limit"))
                {
                    $res['max_char_limit'] = $a_form->getInput("max_char_limit");
                }
                if($a_form->getInput("limit_characters") && $a_form->getInput("min_char_limit"))
                {
                    $res['min_char_limit'] = $a_form->getInput("min_char_limit");

                }*/


                $res["deadline_mode"] = $a_form->getInput("deadline_mode");

                if ($res["deadline_mode"] == ilExAssignment::DEADLINE_RELATIVE) {
                    $res["relative_deadline"] = $a_form->getInput("relative_deadline");
                    $rel_deadline_last_subm = $a_form->getItemByPostVar("rel_deadline_last_subm")->getDate();
                    $rel_deadline_last_subm = $rel_deadline_last_subm
                        ? $rel_deadline_last_subm->get(IL_CAL_UNIX)
                        : null;
                    $res["rel_deadline_last_subm"] = $rel_deadline_last_subm;
                }

                // peer
                if ($a_form->getInput("peer") ||
                    $protected_peer_review_groups) {
                    $res["peer"] = true;
                }
                
                // files
                if (is_array($_FILES["files"])) {
                    // #15994 - we are keeping the upload files array structure
                    // see ilFSStorageExercise::uploadAssignmentFiles()
                    $res["files"] = $_FILES["files"];
                }
                
                // global feedback
                if ($a_form->getInput("fb")) {
                    $res["fb"] = true;
                    $res["fb_cron"] = $a_form->getInput("fb_cron");
                    $res["fb_date"] = $a_form->getInput("fb_date");
                    $res["fb_date_custom"] = $time_fb_custom_date;

                    if ($_FILES["fb_file"]["tmp_name"]) {
                        $res["fb_file"] = $_FILES["fb_file"];
                    }
                }
                if ($a_form->getInput("rmd_submit_status")) {
                    $res["rmd_submit_status"] = true;
                    $res["rmd_submit_start"] = $a_form->getInput("rmd_submit_start");
                    $res["rmd_submit_freq"] = $a_form->getInput("rmd_submit_freq");
                    $res["rmd_submit_end"] = $reminder_submit_end_date;
                    $res["rmd_submit_template_id"] = $a_form->getInput("rmd_submit_template_id");
                }
                if ($a_form->getInput("rmd_grade_status")) {
                    $res["rmd_grade_status"] = true;
                    $res["rmd_grade_freq"] = $a_form->getInput("rmd_grade_freq");
                    $res["rmd_grade_end"] = $reminder_grade_end_date;
                    $res["rmd_grade_template_id"] = $a_form->getInput("rmd_grade_template_id");
                }
                
                return $res;
            } else {
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
            }
        }
    }
    
    /**
     * Import form values to assignment
     *
     * @param ilExAssignment $a_ass
     * @param array $a_input
     */
    protected function importFormToAssignment(ilExAssignment $a_ass, array $a_input)
    {
        $is_create = !(bool) $a_ass->getId();
        
        $a_ass->setTitle($a_input["title"]);
        $a_ass->setInstruction($a_input["instruction"]);
        if (!$this->random_manager->isActivated()) {
            $a_ass->setMandatory($a_input["mandatory"]);
        }

        $a_ass->setStartTime($a_input["start"]);
        $a_ass->setDeadline($a_input["deadline"]);
        $a_ass->setExtendedDeadline($a_input["deadline_ext"]);
        $a_ass->setDeadlineMode($a_input["deadline_mode"]);
        $a_ass->setRelativeDeadline($a_input["relative_deadline"]);
        $a_ass->setRelDeadlineLastSubmission($a_input["rel_deadline_last_subm"]);
                                    
        $a_ass->setMaxFile($a_input["max_file"]);
        $a_ass->setTeamTutor($a_input["team_creator"]);

        //$a_ass->setPortfolioTemplateId($a_input['template_id']);

        //$a_ass->setMinCharLimit($a_input['min_char_limit']);
        //$a_ass->setMaxCharLimit($a_input['max_char_limit']);

        if (!$this->random_manager->isActivated()) {
            $a_ass->setPeerReview((bool) $a_input["peer"]);
        }
        
        // peer review default values (on separate form)
        if ($is_create) {
            $a_ass->setPeerReviewMin(2);
            $a_ass->setPeerReviewSimpleUnlock(false);
            $a_ass->setPeerReviewValid(ilExAssignment::PEER_REVIEW_VALID_NONE);
            $a_ass->setPeerReviewPersonalized(false);
            $a_ass->setPeerReviewFileUpload(false);
            $a_ass->setPeerReviewText(true);
            $a_ass->setPeerReviewRating(true);
        }
        
        if ($a_input["fb"]) {
            $a_ass->setFeedbackCron($a_input["fb_cron"]); // #13380
            $a_ass->setFeedbackDate($a_input["fb_date"]);
            $a_ass->setFeedbackDateCustom($a_input["fb_date_custom"]);
        }
        
        // id needed for file handling
        if ($is_create) {
            $a_ass->save();
            
            // #15994 - assignment files
            if (is_array($a_input["files"])) {
                $a_ass->uploadAssignmentFiles($a_input["files"]);
            }
        } else {
            // remove global feedback file?
            if (!$a_input["fb"]) {
                $a_ass->deleteGlobalFeedbackFile();
                $a_ass->setFeedbackFile(null);
            }
            
            $a_ass->update();
        }
                            
        // add global feedback file?
        if ($a_input["fb"]) {
            if (is_array($a_input["fb_file"])) {
                $a_ass->handleGlobalFeedbackFileUpload($a_input["fb_file"]);
                $a_ass->update();
            }
        }
        $this->importFormToAssignmentReminders($a_input, $a_ass->getId());
    }

    protected function importFormToAssignmentReminders($a_input, $a_ass_id)
    {
        $reminder = new ilExAssignmentReminder($this->exercise_id, $a_ass_id, ilExAssignmentReminder::SUBMIT_REMINDER);
        $this->saveReminderData($reminder, $a_input);

        $reminder = new ilExAssignmentReminder($this->exercise_id, $a_ass_id, ilExAssignmentReminder::GRADE_REMINDER);
        $this->saveReminderData($reminder, $a_input);
    }

    //todo maybe we can refactor this method to use only one importFormToReminders
    protected function importPeerReviewFormToAssignmentReminders($a_input, $a_ass_id)
    {
        $reminder = new ilExAssignmentReminder($this->exercise_id, $a_ass_id, ilExAssignmentReminder::FEEDBACK_REMINDER);
        $this->saveReminderData($reminder, $a_input);
    }

    protected function saveReminderData(ilExAssignmentReminder $reminder, $a_input)
    {
        if ($reminder->getReminderStatus() == null) {
            $action = "save";
        } else {
            $action = "update";
        }
        $type = $reminder->getReminderType();
        $reminder->setReminderStatus((bool) $a_input["rmd_" . $type . "_status"]);
        $reminder->setReminderStart((int) $a_input["rmd_" . $type . "_start"]);
        $reminder->setReminderEnd((int) $a_input["rmd_" . $type . "_end"]);
        $reminder->setReminderFrequency((int) $a_input["rmd_" . $type . "_freq"]);
        $reminder->setReminderMailTemplate((int) $a_input["rmd_" . $type . "_template_id"]);
        $reminder->{$action}();
    }
    
    /**
    * Save assignment
    *
    */
    public function saveAssignmentObject()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        // #16163 - ignore ass id from request
        $this->assignment = null;
        
        $form = $this->initAssignmentForm((int) $_POST["type"], "create");
        $input = $this->processForm($form);
        if (is_array($input)) {
            $ass = new ilExAssignment();
            $ass->setExerciseId($this->exercise_id);
            $ass->setType($input["type"]);
            $ass_type = $ass->getAssignmentType();
            $ass_type_gui = $this->type_guis->getById($ass->getType());

            $this->importFormToAssignment($ass, $input);

            $this->generateTeams($ass, $input);
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

            $ass_type_gui->importFormToAssignment($ass, $form);
            $ass->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

            // because of sub-tabs we stay on settings screen
            $ilCtrl->setParameter($this, "ass_id", $ass->getId());
            $ilCtrl->redirect($this, "editAssignment");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    /**
     * Edit assignment
     */
    public function editAssignmentObject()
    {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        
        $this->setAssignmentHeader();
        $ilTabs->activateTab("ass_settings");
        
        $form = $this->initAssignmentForm($this->assignment->getType(), "edit");
        $this->getAssignmentValues($form);
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Get current values for assignment from
     */
    public function getAssignmentValues(ilPropertyFormGUI $a_form)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ass_type_gui = $this->type_guis->getById($this->assignment->getType());

        $values = array();
        $values["type"] = $this->assignment->getType();
        $values["title"] = $this->assignment->getTitle();
        $values["mandatory"] = $this->assignment->getMandatory();
        $values["instruction"] = $this->assignment->getInstruction();
        //$values['template_id'] = $this->assignment->getPortfolioTemplateId();

        //if($this->assignment->getPortfolioTemplateId())
        //{
        //	$values["template"] = 1;
        //}

        /*if($this->assignment->getMinCharLimit())
        {
            $values['limit_characters'] = 1;
            $values['min_char_limit'] = $this->assignment->getMinCharLimit();
        }
        if($this->assignment->getMaxCharLimit())
        {
            $values['limit_characters'] = 1;
            $values['max_char_limit'] = $this->assignment->getMaxCharLimit();
        }*/

        if ($this->assignment->getStartTime()) {
            $values["start_time"] = new ilDateTime($this->assignment->getStartTime(), IL_CAL_UNIX);
        }
        
        if ($this->assignment->getAssignmentType()->usesFileUpload()) {
            if ($this->assignment->getMaxFile()) {
                $values["max_file_tgl"] = true;
                $values["max_file"] = $this->assignment->getMaxFile();
            }
        }
                    
        if ($this->assignment->getAssignmentType()->usesTeams()) {
            $values["team_creator"] = $this->assignment->getTeamTutor();
        }

        if ($this->assignment->getFeedbackDateCustom()) {
            $values["fb_date_custom"] = new ilDateTime($this->assignment->getFeedbackDateCustom(), IL_CAL_UNIX);
        }

        //Reminders
        $rmd_sub = new ilExAssignmentReminder($this->exercise_id, $this->assignment->getId(), ilExAssignmentReminder::SUBMIT_REMINDER);
        if ($rmd_sub->getReminderStatus()) {
            $values["rmd_submit_status"] = $rmd_sub->getReminderStatus();
            $values["rmd_submit_start"] = $rmd_sub->getReminderStart();
            $values["rmd_submit_end"] = new ilDateTime($rmd_sub->getReminderEnd(), IL_CAL_UNIX);
            $values["rmd_submit_freq"] = $rmd_sub->getReminderFrequency();
            $values["rmd_submit_template_id"] = $rmd_sub->getReminderMailTemplate();
        }

        $rmd_grade = new ilExAssignmentReminder($this->exercise_id, $this->assignment->getId(), ilExAssignmentReminder::GRADE_REMINDER);
        if ($rmd_grade->getReminderStatus()) {
            $values["rmd_grade_status"] = $rmd_grade->getReminderStatus();
            $values["rmd_grade_end"] = new ilDateTime($rmd_grade->getReminderEnd(), IL_CAL_UNIX);
            $values["rmd_grade_freq"] = $rmd_grade->getReminderFrequency();
            $values["rmd_grade_template_id"] = $rmd_grade->getReminderMailTemplate();
        }

        $type_values = $ass_type_gui->getFormValuesArray($this->assignment);
        $values = array_merge($values, $type_values);


        $values["deadline_mode"] = $this->assignment->getDeadlineMode();
        $values["relative_deadline"] = $this->assignment->getRelativeDeadline();
        $values["rel_deadline_last_subm"] = new ilDateTime($this->assignment->getRelDeadlineLastSubmission(), IL_CAL_UNIX);


        $a_form->setValuesByArray($values);
        
        // global feedback
        if ($this->assignment->getFeedbackFile()) {
            $a_form->getItemByPostVar("fb")->setChecked(true);
            $a_form->getItemByPostVar("fb_file")->setValue(basename($this->assignment->getGlobalFeedbackFilePath()));
            $a_form->getItemByPostVar("fb_file")->setRequired(false); // #15467
            $a_form->getItemByPostVar("fb_file")->setInfo(
                // #16400
                '<a href="' . $ilCtrl->getLinkTarget($this, "downloadGlobalFeedbackFile") . '">' .
                $lng->txt("download") . '</a>'
            );
        }
        $a_form->getItemByPostVar("fb_cron")->setChecked($this->assignment->hasFeedbackCron());
        $a_form->getItemByPostVar("fb_date")->setValue($this->assignment->getFeedbackDate());
                        
        $this->handleDisabledFields($a_form, true);
    }
    
    protected function setDisabledFieldValues(ilPropertyFormGUI $a_form)
    {
        // dates
        if ($this->assignment->getDeadline() > 0) {
            $edit_date = new ilDateTime($this->assignment->getDeadline(), IL_CAL_UNIX);
            $ed_item = $a_form->getItemByPostVar("deadline");
            $ed_item->setDate($edit_date);
            
            if ($this->assignment->getExtendedDeadline() > 0) {
                $edit_date = new ilDateTime($this->assignment->getExtendedDeadline(), IL_CAL_UNIX);
                $ed_item = $a_form->getItemByPostVar("deadline2");
                $ed_item->setDate($edit_date);
            }
        }
            
        if ($this->assignment->getPeerReview()) {
            $a_form->getItemByPostVar("peer")->setChecked($this->assignment->getPeerReview());
        }
    }
    
    protected function handleDisabledFields(ilPropertyFormGUI $a_form, $a_force_set_values = false)
    {
        // potentially disabled elements are initialized here to re-use this
        // method after setValuesByPost() - see updateAssignmentObject()
        
        // team assignments do not support peer review
        // with no active peer review there is nothing to protect
        if (!$this->assignment->getAssignmentType()->usesTeams() &&
            $this->assignment->getPeerReview()) {
            // #14450
            $peer_review = new ilExPeerReview($this->assignment);
            if ($peer_review->hasPeerReviewGroups()) {
                // deadline(s) are past and must not change
                $a_form->getItemByPostVar("deadline")->setDisabled(true);
                $a_form->getItemByPostVar("deadline2")->setDisabled(true);

                $a_form->getItemByPostVar("peer")->setDisabled(true);

                $a_form->getItemByPostVar("deadline_mode")->setDisabled(true);
            }
        }
        
        if ($a_force_set_values ||
            ($peer_review && $peer_review->hasPeerReviewGroups())) {
            $this->setDisabledFieldValues($a_form);
        }
    }

    /**
     * Update assignment
     *
     */
    public function updateAssignmentObject()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $form = $this->initAssignmentForm($this->assignment->getType(), "edit");
        $input = $this->processForm($form);

        $ass_type = $this->assignment->getType();
        $ass_type_gui = $this->type_guis->getById($ass_type);

        if (is_array($input)) {
            $old_deadline = $this->assignment->getDeadline();
            $old_ext_deadline = $this->assignment->getExtendedDeadline();

            $this->importFormToAssignment($this->assignment, $input);
            $this->generateTeams($this->assignment, $input);

            $ass_type_gui->importFormToAssignment($this->assignment, $form);
            $this->assignment->update();

            $new_deadline = $this->assignment->getDeadline();
            $new_ext_deadline = $this->assignment->getExtendedDeadline();
            
            // if deadlines were changed
            if ($old_deadline != $new_deadline ||
                $old_ext_deadline != $new_ext_deadline) {
                $this->assignment->recalculateLateSubmissions();
            }

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editAssignment");
        } else {
            $this->setAssignmentHeader();
            $ilTabs->activateTab("ass_settings");
            
            $form->setValuesByPost();
            $this->handleDisabledFields($form);
            $tpl->setContent($form->getHtml());
        }
    }
    
    /**
    * Confirm assignments deletion
    */
    public function confirmAssignmentsDeletionObject()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listAssignments");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("exc_conf_del_assignments"));
            $cgui->setCancel($lng->txt("cancel"), "listAssignments");
            $cgui->setConfirm($lng->txt("delete"), "deleteAssignments");
            
            foreach ($_POST["id"] as $i) {
                $cgui->addItem("id[]", $i, ilExAssignment::lookupTitle($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete assignments
     */
    public function deleteAssignmentsObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $delete = false;
        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $ass = new ilExAssignment(ilUtil::stripSlashes($id));
                $ass->delete();
                $delete = true;
            }
        }
        
        if ($delete) {
            ilUtil::sendSuccess($lng->txt("exc_assignments_deleted"), true);
        }
        $ilCtrl->setParameter($this, "ass_id", "");
        $ilCtrl->redirect($this, "listAssignments");
    }
    
    /**
     * Save assignments order
     */
    public function saveAssignmentOrderObject()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
                
        ilExAssignment::saveAssOrderOfExercise($this->exercise_id, $_POST["order"]);
        
        ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
        $ilCtrl->redirect($this, "listAssignments");
    }
    
    /**
     * Order by deadline
     */
    public function orderAssignmentsByDeadlineObject()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
                
        ilExAssignment::orderAssByDeadline($this->exercise_id);
        
        ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
        $ilCtrl->redirect($this, "listAssignments");
    }

    /**
     * Set assignment header
     */
    public function setAssignmentHeader()
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilHelp = $this->help;
                
        $tpl->setTitle($this->assignment->getTitle());
        $tpl->setDescription("");
        
        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("exc");
        
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listAssignments")
        );

        $ilTabs->addTab(
            "ass_settings",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "editAssignment")
        );

        if (!$this->assignment->getAssignmentType()->usesTeams() &&
            $this->assignment->getPeerReview()) {
            $ilTabs->addTab(
                "peer_settings",
                $lng->txt("exc_peer_review"),
                $ilCtrl->getLinkTarget($this, "editPeerReview")
            );
        }
        
        $ilTabs->addTab(
            "ass_files",
            $lng->txt("exc_instruction_files"),
            $ilCtrl->getLinkTargetByClass(array("ilexassignmenteditorgui", "ilexassignmentfilesystemgui"), "listFiles")
        );
    }
    
    public function downloadGlobalFeedbackFileObject()
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->assignment ||
            !$this->assignment->getFeedbackFile()) {
            $ilCtrl->redirect($this, "returnToParent");
        }
        
        ilUtil::deliverFile($this->assignment->getGlobalFeedbackFilePath(), $this->assignment->getFeedbackFile());
    }
    
    
    //
    // PEER REVIEW
    //
    
    protected function initPeerReviewForm()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("exc_peer_review"));
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        $peer_min = new ilNumberInputGUI($lng->txt("exc_peer_review_min_number"), "peer_min");
        $peer_min->setInfo($lng->txt("exc_peer_review_min_number_info")); // #16161
        $peer_min->setRequired(true);
        $peer_min->setSize(3);
        $peer_min->setValue(2);
        $form->addItem($peer_min);

        $peer_unlock = new ilRadioGroupInputGUI($lng->txt("exc_peer_review_simple_unlock"), "peer_unlock");
        $peer_unlock->addOption(new ilRadioOption($lng->txt("exc_peer_review_simple_unlock_active"), 1));
        $peer_unlock->addOption(new ilRadioOption($lng->txt("exc_peer_review_simple_unlock_inactive"), 0));
        $peer_unlock->setRequired(true);
        $peer_unlock->setValue(0);
        $form->addItem($peer_unlock);

        if ($this->enable_peer_review_completion) {
            $peer_cmpl = new ilRadioGroupInputGUI($lng->txt("exc_peer_review_completion"), "peer_valid");
            $option = new ilRadioOption($lng->txt("exc_peer_review_completion_none"), ilExAssignment::PEER_REVIEW_VALID_NONE);
            $option->setInfo($lng->txt("exc_peer_review_completion_none_info"));
            $peer_cmpl->addOption($option);
            $option = new ilRadioOption($lng->txt("exc_peer_review_completion_one"), ilExAssignment::PEER_REVIEW_VALID_ONE);
            $option->setInfo($lng->txt("exc_peer_review_completion_one_info"));
            $peer_cmpl->addOption($option);
            $option = new ilRadioOption($lng->txt("exc_peer_review_completion_all"), ilExAssignment::PEER_REVIEW_VALID_ALL);
            $option->setInfo($lng->txt("exc_peer_review_completion_all_info"));
            $peer_cmpl->addOption($option);
            $peer_cmpl->setRequired(true);
            $peer_cmpl->setValue(ilExAssignment::PEER_REVIEW_VALID_NONE);
            $form->addItem($peer_cmpl);
        }

        $peer_dl = new ilDateTimeInputGUI($lng->txt("exc_peer_review_deadline"), "peer_dl");
        $peer_dl->setInfo($lng->txt("exc_peer_review_deadline_info"));
        $peer_dl->setShowTime(true);
        $form->addItem($peer_dl);

        $peer_prsl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_personal"), "peer_prsl");
        $peer_prsl->setInfo($lng->txt("exc_peer_review_personal_info"));
        $form->addItem($peer_prsl);

        //feedback reminders
        $rmd_feedback = new ilCheckboxInputGUI($this->lng->txt("exc_reminder_feedback_setting"), "rmd_peer_status");

        $rmd_submit_start = new ilNumberInputGUI($this->lng->txt("exc_reminder_feedback_start"), "rmd_peer_start");
        $rmd_submit_start->setSize(3);
        $rmd_submit_start->setMaxLength(3);
        $rmd_submit_start->setSuffix($lng->txt('days'));
        $rmd_submit_start->setRequired(true);
        $rmd_submit_start->setMinValue(1);
        $rmd_feedback->addSubItem($rmd_submit_start);

        $rmd_submit_frequency = new ilNumberInputGUI($this->lng->txt("exc_reminder_frequency"), "rmd_peer_freq");
        $rmd_submit_frequency->setSize(3);
        $rmd_submit_frequency->setMaxLength(3);
        $rmd_submit_frequency->setSuffix($lng->txt('days'));
        $rmd_submit_frequency->setRequired(true);
        $rmd_submit_frequency->setMinValue(1);
        $rmd_feedback->addSubItem($rmd_submit_frequency);

        $rmd_submit_end = new ilDateTimeInputGUI($lng->txt("exc_reminder_end"), "rmd_peer_end");
        $rmd_submit_end->setRequired(true);
        $rmd_feedback->addSubItem($rmd_submit_end);

        $rmd_feedback->addSubItem($this->addMailTemplatesRadio(ilExAssignmentReminder::FEEDBACK_REMINDER));

        $form->addItem($rmd_feedback);

        // criteria
        
        $cats = new ilRadioGroupInputGUI($lng->txt("exc_criteria_catalogues"), "crit_cat");
        $form->addItem($cats);
        
        // default (no catalogue)
        
        $def = new ilRadioOption($lng->txt("exc_criteria_catalogue_default"), -1);
        $cats->addOption($def);
                
        $peer_text = new ilCheckboxInputGUI($lng->txt("exc_peer_review_text"), "peer_text");
        $def->addSubItem($peer_text);
        
        $peer_char = new ilNumberInputGUI($lng->txt("exc_peer_review_min_chars"), "peer_char");
        $peer_char->setInfo($lng->txt("exc_peer_review_min_chars_info"));
        $peer_char->setSize(3);
        $peer_text->addSubItem($peer_char);
        
        $peer_rating = new ilCheckboxInputGUI($lng->txt("exc_peer_review_rating"), "peer_rating");
        $def->addSubItem($peer_rating);
                    
        $peer_file = new ilCheckboxInputGUI($lng->txt("exc_peer_review_file"), "peer_file");
        $peer_file->setInfo($lng->txt("exc_peer_review_file_info"));
        $def->addSubItem($peer_file);
                        
        // catalogues
        
        $cat_objs = ilExcCriteriaCatalogue::getInstancesByParentId($this->exercise_id);
        if (sizeof($cat_objs)) {
            foreach ($cat_objs as $cat_obj) {
                $crits = ilExcCriteria::getInstancesByParentId($cat_obj->getId());
                
                // only non-empty catalogues
                if (sizeof($crits)) {
                    $titles = array();
                    foreach ($crits as $crit) {
                        $titles[] = $crit->getTitle();
                    }
                    $opt = new ilRadioOption($cat_obj->getTitle(), $cat_obj->getId());
                    $opt->setInfo(implode(", ", $titles));
                    $cats->addOption($opt);
                }
            }
        } else {
            // see ilExcCriteriaCatalogueGUI::view()
            $url = $ilCtrl->getLinkTargetByClass("ilexccriteriacataloguegui", "");
            $def->setInfo('<a href="' . $url . '">[+] ' .
                $lng->txt("exc_add_criteria_catalogue") .
                '</a>');
        }
        
        
        $form->addCommandButton("updatePeerReview", $lng->txt("save"));
        $form->addCommandButton("editAssignment", $lng->txt("cancel"));

        return $form;
    }
            
    public function editPeerReviewObject(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        
        $this->setAssignmentHeader();
        $ilTabs->activateTab("peer_settings");
        
        if ($a_form === null) {
            $a_form = $this->initPeerReviewForm();
            $this->getPeerReviewValues($a_form);
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function getPeerReviewValues($a_form)
    {
        $values = array();
        
        if ($this->assignment->getPeerReviewDeadline() > 0) {
            $values["peer_dl"] = new ilDateTime($this->assignment->getPeerReviewDeadline(), IL_CAL_UNIX);
        }

        $this->assignment->getId();
        $this->exercise_id;
        $reminder = new ilExAssignmentReminder($this->exercise_id, $this->assignment->getId(), ilExAssignmentReminder::FEEDBACK_REMINDER);
        if ($reminder->getReminderStatus()) {
            $values["rmd_peer_status"] = $reminder->getReminderStatus();
            $values["rmd_peer_start"] = $reminder->getReminderStart();
            $values["rmd_peer_end"] = new ilDateTime($reminder->getReminderEnd(), IL_CAL_UNIX);
            $values["rmd_peer_freq"] = $reminder->getReminderFrequency();
            $values["rmd_peer_template_id"] = $reminder->getReminderMailTemplate();
        }

        $a_form->setValuesByArray($values);
        
        $this->handleDisabledPeerFields($a_form, true);
    }
    
    protected function setDisabledPeerReviewFieldValues(ilPropertyFormGUI $a_form)
    {
        $a_form->getItemByPostVar("peer_min")->setValue($this->assignment->getPeerReviewMin());
        $a_form->getItemByPostVar("peer_prsl")->setChecked($this->assignment->hasPeerReviewPersonalized());
        $a_form->getItemByPostVar("peer_unlock")->setValue((int) $this->assignment->getPeerReviewSimpleUnlock());

        if ($this->enable_peer_review_completion) {
            $a_form->getItemByPostVar("peer_valid")->setValue($this->assignment->getPeerReviewValid());
        }

        $cat = $this->assignment->getPeerReviewCriteriaCatalogue();
        if ($cat < 1) {
            $cat = -1;
            
            // default / no catalogue
            $a_form->getItemByPostVar("peer_text")->setChecked($this->assignment->hasPeerReviewText());
            $a_form->getItemByPostVar("peer_rating")->setChecked($this->assignment->hasPeerReviewRating());
            $a_form->getItemByPostVar("peer_file")->setChecked($this->assignment->hasPeerReviewFileUpload());
            if ($this->assignment->getPeerReviewChars() > 0) {
                $a_form->getItemByPostVar("peer_char")->setValue($this->assignment->getPeerReviewChars());
            }
        }
        $a_form->getItemByPostVar("crit_cat")->setValue($cat);
    }
    
    protected function handleDisabledPeerFields(ilPropertyFormGUI $a_form, $a_force_set_values = false)
    {
        // #14450
        $peer_review = new ilExPeerReview($this->assignment);
        if ($peer_review->hasPeerReviewGroups()) {
            // JourFixe, 2015-05-11 - editable again
            // $a_form->getItemByPostVar("peer_dl")->setDisabled(true);
            
            $a_form->getItemByPostVar("peer_min")->setDisabled(true);
            $a_form->getItemByPostVar("peer_prsl")->setDisabled(true);
            $a_form->getItemByPostVar("peer_unlock")->setDisabled(true);
            
            if ($this->enable_peer_review_completion) {
                $a_form->getItemByPostVar("peer_valid")->setDisabled(true);
            }
            
            $a_form->getItemByPostVar("crit_cat")->setDisabled(true);
            $a_form->getItemByPostVar("peer_text")->setDisabled(true);
            $a_form->getItemByPostVar("peer_char")->setDisabled(true);
            $a_form->getItemByPostVar("peer_rating")->setDisabled(true);
            $a_form->getItemByPostVar("peer_file")->setDisabled(true);
            
            // required number input is a problem
            $min = new ilHiddenInputGUI("peer_min");
            $min->setValue($this->assignment->getPeerReviewMin());
            $a_form->addItem($min);
        }
        
        if ($a_force_set_values ||
            $peer_review->hasPeerReviewGroups()) {
            $this->setDisabledPeerReviewFieldValues($a_form);
        }
    }
    
    protected function processPeerReviewForm(ilPropertyFormGUI $a_form)
    {
        $lng = $this->lng;
        
        $protected_peer_review_groups = false;
        $peer_review = new ilExPeerReview($this->assignment);
        if ($peer_review->hasPeerReviewGroups()) {
            $protected_peer_review_groups = true;
        }
        
        $valid = $a_form->checkInput();
        if ($valid) {
            // dates
            $time_deadline = $this->assignment->getDeadline();
            $time_deadline_ext = $this->assignment->getExtendedDeadline();
            $time_deadline_max = max($time_deadline, $time_deadline_ext);
            
            $date = $a_form->getItemByPostVar("peer_dl")->getDate();
            $time_peer = $date
                ? $date->get(IL_CAL_UNIX)
                : null;

            $reminder_date = $a_form->getItemByPostVar("rmd_peer_end")->getDate();
            $reminder_date = $reminder_date
                ? $reminder_date->get(IL_CAL_UNIX)
                : null;
            
            // peer < any deadline?
            if ($time_peer && $time_deadline_max && $time_peer < $time_deadline_max) {
                $a_form->getItemByPostVar("peer_dl")
                    ->setAlert($lng->txt("exc_peer_deadline_mismatch"));
                $valid = false;
            }
            
            if (!$protected_peer_review_groups) {
                if ($a_form->getInput("crit_cat") < 0 &&
                    !$a_form->getInput("peer_text") &&
                    !$a_form->getInput("peer_rating") &&
                    !$a_form->getInput("peer_file")) {
                    $a_form->getItemByPostVar("peer_file")
                        ->setAlert($lng->txt("select_one"));
                    $valid = false;
                }
            }
            
            if ($valid) {
                $res = array();
                $res["peer_dl"] = $time_peer;

                if ($protected_peer_review_groups) {
                    $res["peer_min"] = $this->assignment->getPeerReviewMin();
                    $res["peer_unlock"] = $this->assignment->getPeerReviewSimpleUnlock();
                    $res["peer_prsl"] = $this->assignment->hasPeerReviewPersonalized();
                    $res["peer_valid"] = $this->assignment->getPeerReviewValid();
                    
                    $res["peer_text"] = $this->assignment->hasPeerReviewText();
                    $res["peer_rating"] = $this->assignment->hasPeerReviewRating();
                    $res["peer_file"] = $this->assignment->hasPeerReviewFileUpload();
                    $res["peer_char"] = $this->assignment->getPeerReviewChars();
                    $res["crit_cat"] = $this->assignment->getPeerReviewCriteriaCatalogue();
                    
                    $res["peer_valid"] = $this->enable_peer_review_completion
                            ? $res["peer_valid"]
                            : null;
                } else {
                    $res["peer_min"] = $a_form->getInput("peer_min");
                    $res["peer_unlock"] = $a_form->getInput("peer_unlock");
                    $res["peer_prsl"] = $a_form->getInput("peer_prsl");
                    $res["peer_valid"] = $a_form->getInput("peer_valid");
                    
                    $res["peer_text"] = $a_form->getInput("peer_text");
                    $res["peer_rating"] = $a_form->getInput("peer_rating");
                    $res["peer_file"] = $a_form->getInput("peer_file");
                    $res["peer_char"] = $a_form->getInput("peer_char");
                    $res["crit_cat"] = $a_form->getInput("crit_cat");
                }
                if ($a_form->getInput("rmd_peer_status")) {
                    $res["rmd_peer_status"] = $a_form->getInput("rmd_peer_status");
                    $res["rmd_peer_start"] = $a_form->getInput("rmd_peer_start");
                    $res["rmd_peer_end"] = $reminder_date;
                    $res["rmd_peer_freq"] = $a_form->getInput("rmd_peer_freq");
                    $res["rmd_peer_template_id"] = $a_form->getInput("rmd_peer_template_id");
                }

                return $res;
            } else {
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
            }
        }
    }
    
    protected function importPeerReviewFormToAssignment(ilExAssignment $a_ass, array $a_input)
    {
        $a_ass->setPeerReviewMin($a_input["peer_min"]);
        $a_ass->setPeerReviewDeadline($a_input["peer_dl"]);
        $a_ass->setPeerReviewSimpleUnlock($a_input["peer_unlock"]);
        $a_ass->setPeerReviewPersonalized($a_input["peer_prsl"]);
        
        // #18964
        $a_ass->setPeerReviewValid($a_input["peer_valid"]
            ? $a_input["peer_valid"]
            : ilExAssignment::PEER_REVIEW_VALID_NONE);

        $a_ass->setPeerReviewFileUpload($a_input["peer_file"]);
        $a_ass->setPeerReviewChars($a_input["peer_char"]);
        $a_ass->setPeerReviewText($a_input["peer_text"]);
        $a_ass->setPeerReviewRating($a_input["peer_rating"]);
        $a_ass->setPeerReviewCriteriaCatalogue($a_input["crit_cat"] > 0
            ? $a_input["crit_cat"]
            : null);
    
        $a_ass->update();

        $this->importPeerReviewFormToAssignmentReminders($a_input, $a_ass->getId());
    }
    
    protected function updatePeerReviewObject()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $form = $this->initPeerReviewForm();
        $input = $this->processPeerReviewForm($form);
        if (is_array($input)) {
            $this->importPeerReviewFormToAssignment($this->assignment, $input);
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editPeerReview");
        } else {
            $this->setAssignmentHeader();
            $ilTabs->activateTab("peer_settings");
            
            $form->setValuesByPost();
            $this->handleDisabledPeerFields($form);
            $tpl->setContent($form->getHtml());
        }
    }
    
    
    //
    // TEAM
    //

    /**
     * @param $a_num_teams integer
     * @param $a_min_participants integer
     * @param $a_max_participants integer
     * @return array
     */
    public function validationTeamsFormation($a_num_teams, $a_min_participants, $a_max_participants)
    {
        $total_members = $this->getExerciseTotalMembers();
        $number_of_teams = $a_num_teams;

        if ($number_of_teams) {
            $members_per_team = round($total_members / $a_num_teams);
        } else {
            if ($a_min_participants) {
                $number_of_teams = round($total_members / $a_min_participants);
                $participants_extra_team = $total_members % $a_min_participants;
                if ($participants_extra_team > $number_of_teams) {
                    //Can't create teams with this minimum of participants.
                    $message = sprintf($this->lng->txt("exc_team_minimal_too_big"), $a_min_participants);
                    return array("status" => "error", "msg" => $message, "field" => "min_participants_team");
                }
            }
            $members_per_team = 0;
        }

        if ($a_min_participants > $a_max_participants) {
            $message = $this->lng->txt("exc_team_min_big_than_max");
            return array("status" => "error", "msg" => $message, "field" => "max_participants_team");
        }

        if ($a_max_participants > 0 && $members_per_team > $a_max_participants) {
            $message = sprintf($this->lng->txt("exc_team_max_small_than_members"), $a_max_participants, $members_per_team);
            return array("status" => "error", "msg" => $message, "field" => "max_participants_team");
        }

        if ($members_per_team > 0 && $members_per_team < $a_min_participants) {
            $message = sprintf($this->lng->txt("exc_team_min_small_than_members"), $a_min_participants, $members_per_team);
            return array("status" => "error", "msg" => $message, "field" => "min_participants_team");
        }

        return array("status" => "success", "msg" => "");
    }

    /**
     * Get the total number of exercise members
     * @return int
     */
    public function getExerciseTotalMembers()
    {
        $exercise = new ilObjExercise($this->exercise_id, false);
        $exc_members = new ilExerciseMembers($exercise);

        return count($exc_members->getMembers());
    }

    /**
     * @param ilExAssignment $a_assignment
     * @param array $a_input
     */
    public function generateTeams(ilExAssignment $a_assignment, $a_input)
    {
        $ass_type = $a_assignment->getAssignmentType();
        if ($ass_type->usesTeams() &&
            $a_input['team_creator'] == ilExAssignment::TEAMS_FORMED_BY_TUTOR) {
            if ($a_input['team_creation'] == ilExAssignment::TEAMS_FORMED_BY_RANDOM) {
                $number_teams = $a_input['number_teams'];
                if (count(ilExAssignmentTeam::getAssignmentTeamMap($a_assignment->getId())) == 0) {
                    $ass_team = new ilExAssignmentTeam();
                    $ass_team->createRandomTeams($this->exercise_id, $a_assignment->getId(), $number_teams, $a_input['min_participants_team']);
                }
            } elseif ($a_input['team_creation'] == ilExAssignment::TEAMS_FORMED_BY_ASSIGNMENT) {
                ilExAssignmentTeam::adoptTeams($a_input["ass_adpt"], $a_assignment->getId());
                ilUtil::sendInfo($this->lng->txt("exc_teams_assignment_adopted"), true);
            }
        }
    }
}
