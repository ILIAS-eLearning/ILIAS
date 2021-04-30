<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilExerciseManagementGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @ilCtrl_Calls ilExerciseManagementGUI: ilFileSystemGUI, ilRepositorySearchGUI
* @ilCtrl_Calls ilExerciseManagementGUI: ilExSubmissionTeamGUI, ilExSubmissionFileGUI
* @ilCtrl_Calls ilExerciseManagementGUI: ilExSubmissionTextGUI, ilExPeerReviewGUI
*
* @ingroup ModulesExercise
*/
class ilExerciseManagementGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;

    /**
     * @var array
     */
    protected $filter;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    protected $exercise; // [ilObjExercise]
    protected $assignment; // [ilExAssignment]

    /**
     * @var \ILIAS\BackgroundTasks\Task\TaskFactory
     */
    protected $task_factory;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilObjUser
     */
    protected $user;

    const VIEW_ASSIGNMENT = 1;
    const VIEW_PARTICIPANT = 2;
    const VIEW_GRADES = 3;

    const FEEDBACK_ONLY_SUBMISSION = "submission_only";
    const FEEDBACK_FULL_SUBMISSION = "submission_feedback";

    const GRADE_NOT_GRADED = "notgraded";
    const GRADE_PASSED = "passed";
    const GRADE_FAILED = "failed";

    /**
     * @var ilExerciseInternalService
     */
    protected $service;

    /**
     * Constructor
     *
     * @param int $a_exercise_id
     * @return object
     */
    public function __construct(ilExerciseInternalService $service, ilExAssignment $a_ass = null)
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $this->log = ilLoggerFactory::getLogger("exc");

        $this->ctrl = $DIC->ctrl();
        $this->tabs_gui = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->task_factory = $DIC->backgroundTasks()->taskFactory();

        $request = $DIC->exercise()->internal()->request();

        $this->service = $service;

        $this->exercise = $request->getRequestedExercise();
        if ($a_ass) {
            $this->assignment = $a_ass;
            $this->ass_id = $this->assignment->getId();
        }
        
        $this->ctrl->saveParameter($this, array("vw", "member_id"));
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs_gui;
        
        $class = $ilCtrl->getNextClass($this);
        //$cmd = $ilCtrl->getCmd("listPublicSubmissions");
        
        switch ($class) {
            case "ilfilesystemgui":
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($this, $this->getViewBack())
                );
                
                ilUtil::sendInfo($lng->txt("exc_fb_tutor_info"));

                $fstorage = new ilFSStorageExercise($this->exercise->getId(), $this->assignment->getId());
                $fstorage->create();
                                
                $submission = new ilExSubmission($this->assignment, (int) $_GET["member_id"]);
                $feedback_id = $submission->getFeedbackId();
                $noti_rec_ids = $submission->getUserIds();
                
                $fs_title = array();
                foreach ($noti_rec_ids as $rec_id) {
                    $fs_title[] = ilUserUtil::getNamePresentation($rec_id, false, false, "", true);
                }
                $fs_title = implode(" / ", $fs_title);
                    
                $fs_gui = new ilFileSystemGUI($fstorage->getFeedbackPath($feedback_id));
                $fs_gui->setTableId("excfbfil" . $this->assignment->getId() . "_" . $feedback_id);
                $fs_gui->setAllowDirectories(false);
                $fs_gui->setTitle($lng->txt("exc_fb_files") . " - " .
                    $this->assignment->getTitle() . " - " .
                    $fs_title);
                $pcommand = $fs_gui->getLastPerformedCommand();
                if (is_array($pcommand) && $pcommand["cmd"] == "create_file") {
                    foreach ($noti_rec_ids as $user_id) {
                        $member_status = $this->assignment->getMemberStatus($user_id);
                        $member_status->setFeedback(true);
                        $member_status->update();
                    }
                    
                    $this->exercise->sendFeedbackFileNotification(
                        $pcommand["name"],
                        $noti_rec_ids,
                        $this->assignment->getId()
                    );
                }
                $this->ctrl->forwardCommand($fs_gui);
                break;
                
            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();
                $ref_id = $this->exercise->getRefId();
                $rep_search->addUserAccessFilterCallable(function ($a_user_ids) use ($ref_id) {
                    return $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'edit_submissions_grades',
                        'edit_submissions_grades',
                        $ref_id,
                        $a_user_ids
                    );
                });
                $rep_search->setTitle($this->lng->txt("exc_add_participant"));
                $rep_search->setCallback($this, 'addMembersObject');

                // Set tabs
                $this->addSubTabs("assignment");
                $this->ctrl->setReturn($this, 'members');
                
                $this->ctrl->forwardCommand($rep_search);
                break;
            
            case "ilexsubmissionteamgui":
                $gui = new ilExSubmissionTeamGUI($this->exercise, $this->initSubmission());
                $ilCtrl->forwardCommand($gui);
                break;
                
            case "ilexsubmissionfilegui":
                $gui = new ilExSubmissionFileGUI($this->exercise, $this->initSubmission());
                $ilCtrl->forwardCommand($gui);
                break;
                
            case "ilexsubmissiontextgui":
                $ilCtrl->saveParameter($this, array("part_id"));
                $gui = new ilExSubmissionTextGUI($this->exercise, $this->initSubmission());
                $ilCtrl->forwardCommand($gui);
                break;
            
            case "ilexpeerreviewgui":
                $gui = new ilExPeerReviewGUI($this->assignment, $this->initSubmission());
                $ilCtrl->forwardCommand($gui);
                break;
            
            default:
                $cmd = $ilCtrl->getCmd();
                switch ($cmd) {
                    case 'downloadSubmissions':
                        $cmd = $ilCtrl->getCmd("downloadSubmissions");
                        break;
                    default:
                        $cmd = $ilCtrl->getCmd("listPublicSubmissions");
                        break;
                }
                $this->{$cmd . "Object"}();
                break;
        }
    }
    
    protected function getViewBack()
    {
        switch ($_REQUEST["vw"]) {
            case self::VIEW_PARTICIPANT:
                $back_cmd = "showParticipant";
                break;
            
            case self::VIEW_GRADES:
                $back_cmd = "showGradesOverview";
                break;
            
            default:
            // case self::VIEW_ASSIGNMENT:
                $back_cmd = "members";
                break;
        }
        return $back_cmd;
    }
    
    protected function initSubmission()
    {
        $back_cmd = $this->getViewBack();
        $this->ctrl->setReturn($this, $back_cmd);
        
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, $back_cmd)
        );

        return new ilExSubmission($this->assignment, $_REQUEST["member_id"], null, true);
    }
    
    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function addSubTabs($a_activate)
    {
        $ilTabs = $this->tabs_gui;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ass_id = $_GET["ass_id"];
        $part_id = $_GET["part_id"];
                
        $ilCtrl->setParameter($this, "vw", "");
        $ilCtrl->setParameter($this, "member_id", "");
        $ilCtrl->setParameter($this, "ass_id", "");
        $ilCtrl->setParameter($this, "part_id", "");
        
        $ilTabs->addSubTab(
            "assignment",
            $lng->txt("exc_assignment_view"),
            $ilCtrl->getLinkTarget($this, "members")
        );
        $ilTabs->addSubTab(
            "participant",
            $lng->txt("exc_participant_view"),
            $ilCtrl->getLinkTarget($this, "showParticipant")
        );
        $ilTabs->addSubTab(
            "grades",
            $lng->txt("exc_grades_overview"),
            $ilCtrl->getLinkTarget($this, "showGradesOverview")
        );
        $ilTabs->activateSubTab($a_activate);
        
        $ilCtrl->setParameter($this, "ass_id", $ass_id);
        $ilCtrl->setParameter($this, "part_id", $part_id);
    }

    public function waitingDownloadObject()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilExSubmissionFileGUI", "member_id", (int) $_GET["member_id"]);
        $url = $ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI", "ilExerciseManagementGUI", "ilExSubmissionFileGUI"), "downloadNewReturned");
        $js_url = $ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI", "ilExerciseManagementGUI", "ilExSubmissionFileGUI"), "downloadNewReturned", "", "", false);
        ilUtil::sendInfo($lng->txt("exc_wait_for_files") . "<a href='$url'> " . $lng->txt('exc_download_files') . "</a><script>window.location.href ='" . $js_url . "';</script>");
        $this->membersObject();
    }

    /**
     * All participants and submission of one assignment
     */
    public function membersObject()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->addSubTabs("assignment");
        
        // assignment selection
        $ass = ilExAssignment::getInstancesByExercise($this->exercise->getId());
        
        if (!$this->assignment) {
            $this->assignment = current($ass);
        }
        
        reset($ass);
        if (count($ass) > 1) {
            $options = array();
            foreach ($ass as $a) {
                $options[$a->getId()] = $a->getTitle();
            }
            $si = new ilSelectInputGUI($this->lng->txt(""), "ass_id");
            $si->setOptions($options);
            $si->setValue($this->assignment->getId());
            $ilToolbar->addStickyItem($si);
                    
            $button = ilSubmitButton::getInstance();
            $button->setCaption("exc_select_ass");
            $button->setCommand("selectAssignment");
            $ilToolbar->addStickyItem($button);
            
            $ilToolbar->addSeparator();
        }
        // #16165 - if only 1 assignment dropdown is not displayed;
        elseif ($this->assignment) {
            $ilCtrl->setParameter($this, "ass_id", $this->assignment->getId());
        }
        
        // add member
        // is only shown if 'edit_submissions_grades' is granted by rbac. positions
        // access is not sufficient.
        $has_rbac_access = $GLOBALS['DIC']->access()->checkAccess(
            'edit_submissions_grades',
            '',
            $this->exercise->getRefId()
        );
        if ($has_rbac_access) {
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $ilToolbar,
                array(
                    'auto_complete_name' => $lng->txt('user'),
                    'submit_name' => $lng->txt('add'),
                    'add_search' => true,
                    'add_from_container' => $this->exercise->getRefId()
                )
            );
        }
        
        // #16168 - no assignments
        if (count($ass) > 0) {
            if ($has_rbac_access) {
                $ilToolbar->addSeparator();
            }

            // we do not want the ilRepositorySearchGUI form action
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));

            $ilCtrl->setParameter($this, "ass_id", $this->assignment->getId());

            if ($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM) {
                if (ilExAssignmentTeam::getAdoptableGroups($this->exercise->getRefId())) {
                    $ilToolbar->addButton(
                        $this->lng->txt("exc_adopt_group_teams"),
                        $this->ctrl->getLinkTarget($this, "adoptTeamsFromGroup")
                    );
                    
                    $ilToolbar->addSeparator();
                }
            } elseif ($this->exercise->hasTutorFeedbackFile()) {
                if (!$this->assignment->getAssignmentType()->usesTeams()) {
                    // multi-feedback
                    $ilToolbar->addButton(
                        $this->lng->txt("exc_multi_feedback"),
                        $this->ctrl->getLinkTarget($this, "showMultiFeedback")
                    );

                    $ilToolbar->addSeparator();
                }
            }

            $submission_repository = new ilExcSubmissionRepository($this->db);

            if ($submission_repository->hasSubmissions($this->assignment->getId())) {
                $ass_type = $this->assignment->getType();
                //todo change addFormButton for addButtonInstance
                if ($ass_type == ilExAssignment::TYPE_TEXT) {
                    $ilToolbar->addFormButton($lng->txt("exc_list_text_assignment"), "listTextAssignment");
                }
                $ilToolbar->addFormButton($lng->txt("download_all_returned_files"), "downloadSubmissions");
            }
            $this->ctrl->setParameter($this, "vw", self::VIEW_ASSIGNMENT);
            
            $exc_tab = new ilExerciseMemberTableGUI($this, "members", $this->exercise, $this->assignment->getId());
            $tpl->setContent(
                $exc_tab->getHTML() .
                $this->initIndividualDeadlineModal()
            );
        } else {
            ilUtil::sendInfo($lng->txt("exc_no_assignments_available"));
        }
        
        $ilCtrl->setParameter($this, "ass_id", "");

        return;
    }

    public function downloadSubmissionsObject()
    {
        $participant_id = $_REQUEST['part_id'];

        $download_task = new ilDownloadSubmissionsBackgroundTask(
            (int) $GLOBALS['DIC']->user()->getId(),
            (int) $this->exercise->getRefId(),
            (int) $this->exercise->getId(),
            (int) $this->ass_id,
            (int) $participant_id
        );

        if ($download_task->run()) {
            ilUtil::sendSuccess($this->lng->txt('exc_down_files_started_bg'), true);
        }

        if ($this->assignment) {
            $this->ctrl->redirect($this, "members");
        } else {
            $this->ctrl->redirect($this, "showParticipant");
        }
    }
    
    public function membersApplyObject()
    {
        $this->saveStatusAllObject(null, false);
        $exc_tab = new ilExerciseMemberTableGUI($this, "members", $this->exercise, $this->assignment->getId());
        $exc_tab->resetOffset();
        $exc_tab->writeFilterToSession();
        
        $this->membersObject();
    }
    
    public function membersResetObject()
    {
        $exc_tab = new ilExerciseMemberTableGUI($this, "members", $this->exercise, $this->assignment->getId());
        $exc_tab->resetOffset();
        $exc_tab->resetFilter();
        
        $this->membersObject();
    }
    
    /**
     * Save grades
     */
    public function saveGradesObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
                
        if (is_array($_POST["lcomment"])) {
            foreach ($_POST["lcomment"] as $k => $v) {
                $marks_obj = new ilLPMarks($this->exercise->getId(), (int) $k);
                $marks_obj->setComment(ilUtil::stripSlashes($v));
                $marks_obj->setMark(ilUtil::stripSlashes($_POST["mark"][$k]));
                $marks_obj->update();
            }
        }
        ilUtil::sendSuccess($lng->txt("exc_msg_saved_grades"), true);
        $ilCtrl->redirect($this, "showGradesOverview");
    }
    
    
    // TEXT ASSIGNMENT ?!

    /**
     * todo: Pagination.
     */
    public function listTextAssignmentObject()
    {
        $this->initFilter();

        $this->setBackToMembers();

        $button_print = $this->ui_factory->button()->standard($this->lng->txt('print'), "#")
            ->withOnLoadCode(function ($id) {
                return "$('#{$id}').click(function() { window.print(); return false; });";
            });
        $this->toolbar->addSeparator();
        $this->toolbar->addComponent($button_print);

        $group_panels_tpl = new ilTemplate("tpl.exc_group_report_panels.html", true, true, "Modules/Exercise");
        $group_panels_tpl->setVariable('TITLE', $this->lng->txt("exc_list_text_assignment") . ": " . $this->assignment->getTitle());

        $report_html = "";
        $total_reports = 0;

        $members = ilExSubmission::getAssignmentParticipants($this->exercise->getId(), $this->ass_id);
        $members_filter = new ilExerciseMembersFilter($this->exercise->getRefId(), $members, $this->user->getId());
        $members = $members_filter->filterParticipantsByAccess();

        foreach (ilExSubmission::getAssignmentFilesByUsers($this->exercise->getId(), $this->assignment->getId(), $members) as $file) {
            if (trim($file["atext"]) && ilObjUser::_exists($file["user_id"])) {
                $feedback_data = $this->collectFeedbackDataFromPeer($file);
                $submission_data = $this->assignment->getExerciseMemberAssignmentData($file["user_id"], $this->filter["status"]);

                if (is_array($submission_data)) {
                    $data = array_merge($feedback_data, $submission_data);
                    $report_html .= $this->getReportPanel($data);
                    $total_reports++;
                }
            }
        }
        if ($total_reports == 0) {
            $mtpl = new ilTemplate("tpl.message.html", true, true, "Services/Utilities");
            $mtpl->setCurrentBlock("info_message");
            $mtpl->setVariable("TEXT", $this->lng->txt("fiter_no_results"));
            $mtpl->parseCurrentBlock();
            $report_html .= $mtpl->get();
        }

        $group_panels_tpl->setVariable('CONTENT', $report_html);
        $this->tpl->setContent($group_panels_tpl->get());
    }

    /**
     * TODO -> Deal with the redirection after update the grade via action button.
     *
     * Extract the data collection to another method. List and compare use this. DRY
     */
    public function compareTextAssignmentsObject()
    {
        $this->setBackToMembers();

        $group_panels_tpl = new ilTemplate("tpl.exc_group_report_panels.html", true, true, "Modules/Exercise");
        $group_panels_tpl->setVariable('TITLE', $this->lng->txt("exc_compare_selected_submissions"));

        $report_html = "";
        //participant ids selected via checkboxes
        $participants = array_keys($this->getMultiActionUserIds());

        foreach ($participants as $participant_id) {
            $submission = new ilExSubmission($this->assignment, $participant_id);

            //submission data array
            $file = reset($submission->getFiles());

            if (!$file) {
                $file = [
                    "user_id" => $participant_id,
                    "ts" => null,
                    "atext" => null
                ];
            }

            $feedback_data = $this->collectFeedbackDataFromPeer($file);

            $submission_data = $this->assignment->getExerciseMemberAssignmentData($file["user_id"], $this->filter["status"]);

            if (is_array($submission_data)) {
                $data = array_merge($feedback_data, $submission_data);
                $report_html .= $this->getReportPanel($data);
                $total_reports++;
            }
        }

        $group_panels_tpl->setVariable('CONTENT', $report_html);
        $this->tpl->setContent($group_panels_tpl->get());
    }

    public function getReportPanel($a_data)
    {
        $modal = $this->getEvaluationModal($a_data);

        $this->ctrl->setParameter($this, "member_id", $a_data['uid']);
        $actions = array(
            $this->ui_factory->button()->shy($this->lng->txt("grade_evaluate"), "#")->withOnClick($modal->getShowSignal())
        );
        if ($this->exercise->hasTutorFeedbackMail()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_tbl_action_feedback_mail"),
                $this->ctrl->getLinkTarget($this, "redirectFeedbackMail")
            );
        }
        if ($this->exercise->hasTutorFeedbackFile()) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->lng->txt("exc_tbl_action_feedback_file"),
                $this->ctrl->getLinkTargetByClass("ilFileSystemGUI", "listFiles")
            );
        }

        $this->ctrl->setParameter($this, "member_id", "");

        $actions_dropdown = $this->ui_factory->dropdown()->standard($actions);

        if ($a_data['status'] == self::GRADE_NOT_GRADED) {
            $str_status_key = $this->lng->txt('exc_tbl_status');
            $str_status_value = $this->lng->txt('not_yet');
        } else {
            $str_status_key = $this->lng->txt('exc_tbl_status_time');
            $str_status_value = ilDatePresentation::formatDate(new ilDateTime($a_data["status_time"], IL_CAL_DATETIME));
        }

        $str_mark_key = $this->lng->txt("exc_tbl_mark");
        $str_mark_value = $this->lng->txt('not_yet');

        if (($a_data['mark'] != "")) {
            $str_mark_value = $a_data['mark'];
        }

        if ($a_data['feedback_time']) {
            $str_evaluation_key = $this->lng->txt('exc_tbl_feedback_time');
            $str_evaluation_value = ilDatePresentation::formatDate(new ilDateTime($a_data["feedback_time"], IL_CAL_DATETIME));
        } else {
            $str_evaluation_key = $this->lng->txt('exc_settings_feedback');
            $str_evaluation_value = $this->lng->txt('not_yet');
        }

        $card_content = array(
            $this->lng->txt("exc_tbl_submission_date") => ilDatePresentation::formatDate(new ilDateTime($a_data["udate"], IL_CAL_DATETIME)),
            $str_status_key => $str_status_value,
            $str_mark_key => $str_mark_value,
            $str_evaluation_key => $str_evaluation_value,
            $this->lng->txt('feedback_given') => $a_data['fb_given'],
            $this->lng->txt('feedback_received') => $a_data['fb_received']
        );
        $card_tpl = new ilTemplate("tpl.exc_report_details_card.html", true, true, "Modules/Exercise");
        foreach ($card_content as $key => $value) {
            $card_tpl->setCurrentBlock("assingment_card");
            $card_tpl->setVariable("ROW_KEY", $key);
            $card_tpl->setVariable("ROW_VALUE", $value);
            $card_tpl->parseCurrentBlock();
        }

        $main_panel = $this->ui_factory->panel()->sub($a_data['uname'], $this->ui_factory->legacy($a_data['utext']))
            ->withCard($this->ui_factory->card()->standard($this->lng->txt('text_assignment'))->withSections(array($this->ui_factory->legacy($card_tpl->get()))))->withActions($actions_dropdown);

        $feedback_tpl = new ilTemplate("tpl.exc_report_feedback.html", true, true, "Modules/Exercise");
        //if no feedback filter the feedback is displayed. Can be list submissions or compare submissions.
        if (array_key_exists("peer", $a_data) && ($this->filter["feedback"] == self::FEEDBACK_FULL_SUBMISSION) || $this->filter["feedback"] == "") {
            $feedback_tpl->setCurrentBlock("feedback");
            foreach ($a_data["peer"] as $peer_id) {
                if (ilObject::_lookupType($peer_id) == "usr") {
                    $user = new ilObjUser($peer_id);
                    $peer_name = $user->getFirstname() . " " . $user->getLastname();
                } else {
                    $peer_name = $this->lng->txt("exc_deleted_user");
                }

                $feedback_tpl->setCurrentBlock("peer_feedback");
                $feedback_tpl->setVariable("PEER_NAME", $peer_name);

                $submission = new ilExSubmission($this->assignment, $a_data["uid"]);
                $values = $submission->getPeerReview()->getPeerReviewValues($peer_id, $a_data["uid"]);

                $review_html = "";
                foreach ($this->assignment->getPeerReviewCriteriaCatalogueItems() as $crit) {
                    $crit_id = $crit->getId()
                        ? $crit->getId()
                        : $crit->getType();
                    $crit->setPeerReviewContext($this->assignment, $peer_id, $a_data["uid"]);

                    $review_html .=
                        '<div class="ilBlockPropertyCaption">' . $crit->getTitle() . '</div>' .
                        '<div style="margin:2px 0;">' . $crit->getHTML($values[$crit_id]) . '</div>';
                }
                $feedback_tpl->setVariable("PEER_FEEDBACK", $review_html);
                $feedback_tpl->parseCurrentBlock();
            }
            $feedback_tpl->parseCurrentBlock();
        }
        $feedback_tpl->setVariable("GRADE", $this->lng->txt('grade') . ": " . $this->lng->txt('exc_' . $a_data['status']));
        $feedback_tpl->setVariable("COMMENT", $this->lng->txt('exc_comment') . "<br>" . $a_data['comment']);

        $feedback_panel = $this->ui_factory->panel()->sub("", $this->ui_factory->legacy($feedback_tpl->get()));

        $report = $this->ui_factory->panel()->report("", array($main_panel, $feedback_panel));

        return $this->ui_renderer->render([$modal,$report]);
    }

    public function getEvaluationModal($a_data)
    {
        $modal_tpl = new ilTemplate("tpl.exc_report_evaluation_modal.html", true, true, "Modules/Exercise");
        $modal_tpl->setVariable("USER_NAME", $a_data['uname']);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveEvaluationFromModal"));
        $form->setId(uniqid('form'));

        //Grade
        $options = array(
            self::GRADE_NOT_GRADED => $this->lng->txt("exc_notgraded"),
            self::GRADE_PASSED => $this->lng->txt("exc_passed"),
            self::GRADE_FAILED => $this->lng->txt("exc_failed")
        );
        $si = new ilSelectInputGUI($this->lng->txt("exc_tbl_status"), "grade");
        $si->setOptions($options);
        $si->setValue($a_data['status']);
        $form->addItem($si);

        //Mark
        $mark_input = new ilTextInputGUI($this->lng->txt("exc_tbl_mark"), "mark");
        $mark_input->setValue($a_data['mark']);
        $mark_input->setMaxLength(32);
        $mark_input->setSize(4);
        $form->addItem($mark_input);

        $item = new ilHiddenInputGUI('mem_id');
        $item->setValue($a_data['uid']);
        $form->addItem($item);

        //TODO: CHECK ilias string utils. ilUtil shortenText with net blank.
        if ($this->exercise->hasTutorFeedbackText()) {
            $max_chars = 500;

            $u_text = strip_tags($a_data["utext"]); //otherwise will get open P
            $text = $u_text;
            //show more
            if (strlen($u_text) > $max_chars) {
                $text = "<input type='checkbox' class='read-more-state' id='post-1' />";
                $text .= "<div class='read-more-wrap'>";
                $text .= mb_substr($u_text, 0, $max_chars);
                $text .= "<span class='read-more-target'>";
                $text .= mb_substr($u_text, $max_chars);
                $text .= "</span></div>";
                $text .= "<label for='post-1' class='read-more-trigger'></label>";
            }
            $modal_tpl->setVariable("USER_TEXT", $text);

            $ta = new ilTextAreaInputGUI($this->lng->txt("exc_comment"), 'comment');
            $ta->setInfo($this->lng->txt("exc_comment_for_learner_info"));
            $ta->setValue($a_data['comment']);
            $ta->setRows(10);
            $form->addItem($ta);
        }

        $modal_tpl->setVariable("FORM", $form->getHTML());

        $form_id = 'form_' . $form->getId();
        $submit_btn = $this->ui_factory->button()->primary($this->lng->txt("save"), '#')
            ->withOnLoadCode(function ($id) use ($form_id) {
                return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
            });

        return  $this->ui_factory->modal()->roundtrip(strtoupper($this->lng->txt("grade_evaluate")), $this->ui_factory->legacy($modal_tpl->get()))->withActionButtons([$submit_btn]);
    }

    /**
     * Save assignment submission grade(status) and comment from the roundtrip modal.
     */
    public function saveEvaluationFromModalObject()
    {
        $comment = trim($_POST['comment']);
        $user_id = (int) $_POST['mem_id'];
        $grade = trim($_POST["grade"]);
        $mark = trim($_POST['mark']);

        if ($this->assignment->getId() && $user_id) {
            $member_status = $this->assignment->getMemberStatus($user_id);
            $member_status->setComment(ilUtil::stripSlashes($comment));
            $member_status->setStatus($grade);
            $member_status->setMark($mark);
            if ($comment != "") {
                $member_status->setFeedback(true);
            }
            $member_status->update();
        }
        ilUtil::sendSuccess($this->lng->txt("exc_status_saved"), true);
        $this->ctrl->redirect($this, "listTextAssignment");
    }

    /**
    * Add user as member
    */
    public function addUserFromAutoCompleteObject()
    {
        if (!strlen(trim($_POST['user_login']))) {
            ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
            $this->membersObject();
            return false;
        }
        $users = explode(',', $_POST['user_login']);

        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                ilUtil::sendFailure($this->lng->txt('user_not_known'));
                return $this->membersObject();
            }
            
            $user_ids[] = $user_id;
        }

        if (!$this->addMembersObject($user_ids)) {
            $this->membersObject();
            return false;
        }
        return true;
    }

    /**
     * Add new partipant
     */
    public function addMembersObject($a_user_ids = array())
    {
        if (!count($a_user_ids)) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            return false;
        }

        if (!$this->exercise->members_obj->assignMembers($a_user_ids)) {
            ilUtil::sendFailure($this->lng->txt("exc_members_already_assigned"));
            return false;
        } else {
            /* #16921
            // #9946 - create team for new user(s) for each team upload assignment
            foreach(ilExAssignment::getInstancesByExercise($this->exercise->getId()) as $ass)
            {
                if($ass->hasTeam())
                {
                    foreach($a_user_ids as $user_id)
                    {
                        // #15915
                        ilExAssignmentTeam::getTeamId($ass->getId(), $user_id, true);
                    }
                }
            }
            */
            
            ilUtil::sendSuccess($this->lng->txt("exc_members_assigned"), true);
        }

        $this->ctrl->redirect($this, "members");
        return true;
    }
    

    /**
     * Select assignment
     */
    public function selectAssignmentObject()
    {
        $_GET["ass_id"] = ilUtil::stripSlashes($_POST["ass_id"]);
        $this->membersObject();
    }
    
    /**
     * Show Participant
     */
    public function showParticipantObject()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->addSubTabs("participant");
        $this->ctrl->setParameter($this, "ass_id", "");
        
        // participant selection
        $ass = ilExAssignment::getAssignmentDataOfExercise($this->exercise->getId());
        $members = $this->exercise->members_obj->getMembers();
        
        $members = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $this->exercise->getRefId(),
            $members
        );
        
        
        if (count($members) == 0) {
            ilUtil::sendInfo($lng->txt("exc_no_participants"));
            return;
        }
        
        $mems = array();
        foreach ($members as $mem_id) {
            if (ilObject::_lookupType($mem_id) == "usr") {
                $name = ilObjUser::_lookupName($mem_id);
                if (trim($name["login"]) != "") {		// #20073
                    $mems[$mem_id] = $name;
                }
            }
        }
        
        $mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);
        
        if ($_GET["part_id"] == "" && count($mems) > 0) {
            $_GET["part_id"] = key($mems);
        }
        
        $current_participant = $_GET["part_id"];
        
        reset($mems);
        if (count($mems) > 1) {
            $options = array();
            foreach ($mems as $k => $m) {
                $options[$k] =
                    $m["lastname"] . ", " . $m["firstname"] . " [" . $m["login"] . "]";
            }
            $si = new ilSelectInputGUI($this->lng->txt(""), "part_id");
            $si->setOptions($options);
            $si->setValue($current_participant);
            $ilToolbar->addStickyItem($si);
            
            $button = ilSubmitButton::getInstance();
            $button->setCaption("exc_select_part");
            $button->setCommand("selectParticipant");
            $ilToolbar->addStickyItem($button);
        }

        if (count($mems) > 0) {
            $this->ctrl->setParameter($this, "vw", self::VIEW_PARTICIPANT);
            $this->ctrl->setParameter($this, "part_id", $current_participant);

            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->addFormButton($lng->txt("download_all_returned_files"), "downloadSubmissions");
            
            $part_tab = new ilExParticipantTableGUI(
                $this,
                "showParticipant",
                $this->exercise,
                $current_participant
            );
            $tpl->setContent($part_tab->getHTML() .
                $this->initIndividualDeadlineModal());
        } else {
            ilUtil::sendInfo($this->lng->txt("exc_no_assignments_available"));
        }
    }
    
    public function showParticipantApplyObject()
    {
        $exc_tab = new ilExParticipantTableGUI($this, "showParticipant", $this->exercise, $_GET["part_id"]);
        $exc_tab->resetOffset();
        $exc_tab->writeFilterToSession();
        
        $this->showParticipantObject();
    }
    
    public function showParticipantResetObject()
    {
        $exc_tab = new ilExParticipantTableGUI($this, "showParticipant", $this->exercise, $_GET["part_id"]);
        $exc_tab->resetOffset();
        $exc_tab->resetFilter();
        
        $this->showParticipantObject();
    }
    
    /**
     * Select participant
     */
    public function selectParticipantObject()
    {
        $_GET["part_id"] = ilUtil::stripSlashes($_POST["part_id"]);
        $this->showParticipantObject();
    }

    /**
     * Show grades overview
     */
    public function showGradesOverviewObject()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->addSubTabs("grades");
        
        $mem_obj = new ilExerciseMembers($this->exercise);
        $mems = $mem_obj->getMembers();
        
        $mems = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $this->exercise->getRefId(),
            $mems
        );
        if (count($mems) > 0) {
            $ilToolbar->addButton(
                $lng->txt("exc_export_excel"),
                $ilCtrl->getLinkTarget($this, "exportExcel")
            );
        }
        
        $this->ctrl->setParameter($this, "vw", self::VIEW_GRADES);

        $grades_tab = new ilExGradesTableGUI(
            $this,
            "showGradesOverview",
            $this->service,
            $mem_obj
        );
        $tpl->setContent($grades_tab->getHTML());
    }

    /**
    * set feedback status for member and redirect to mail screen
    */
    public function redirectFeedbackMailObject()
    {
        $members = array();
                        
        if ($_GET["member_id"] != "") {
            $submission = new ilExSubmission($this->assignment, $_GET["member_id"]);
            $members = $submission->getUserIds();
        } elseif ($members = $this->getMultiActionUserIds()) {
            $members = array_keys($members);
        }
        
        if ($members) {
            $logins = array();
            foreach ($members as $user_id) {
                $member_status = $this->assignment->getMemberStatus($user_id);
                $member_status->setFeedback(true);
                $member_status->update();

                $logins[] = ilObjUser::_lookupLogin($user_id);
            }
            $logins = implode(",", $logins);
            
            // #16530 - see ilObjCourseGUI::createMailSignature
            $sig = chr(13) . chr(10) . chr(13) . chr(10);
            $sig .= $this->lng->txt('exc_mail_permanent_link');
            $sig .= chr(13) . chr(10) . chr(13) . chr(10);
            $sig .= ilLink::_getLink($this->exercise->getRefId());
            $sig = rawurlencode(base64_encode($sig));
                        
            ilUtil::redirect(ilMailFormCall::getRedirectTarget(
                $this,
                $this->getViewBack(),
                array(),
                array(
                    'type' => 'new',
                    'rcp_to' => $logins,
                    ilMailFormCall::SIGNATURE_KEY => $sig
                )
            ));
        }
    }

    /**
    * Download all submitted files (of all members).
    */
    public function downloadAllObject()
    {
        $members = array();
        
        foreach ($this->exercise->members_obj->getMembers() as $member_id) {
            $submission = new ilExSubmission($this->assignment, $member_id);
            $submission->updateTutorDownloadTime();
            
            // get member object (ilObjUser)
            if (ilObject::_exists($member_id)) {
                $storage_id = "";
                // adding file metadata
                foreach ($submission->getFiles() as $file) {
                    if ($this->assignment->getAssignmentType()->isSubmissionAssignedToTeam()) {
                        $storage_id = $file["team_id"];
                    } else {
                        $storage_id = $file["user_id"];
                    }

                    $members[$storage_id]["files"][$file["returned_id"]] = $file;
                }
                if ($this->assignment->getAssignmentType()->isSubmissionAssignedToTeam()) {
                    $name = "Team " . $submission->getTeam()->getId();
                } else {
                    $tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
                    $name = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
                }
                if ($storage_id > 0) {
                    $members[$storage_id]["name"] = $name;
                }
                unset($tmp_obj);
            }
        }
        
        ilExSubmission::downloadAllAssignmentFiles($this->assignment, $members);
    }

    protected function getMultiActionUserIds($a_keep_teams = false)
    {
        // multi-user
        if ($this->assignment) {
            if (!$_POST["member"]) {
                ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
                $this->ctrl->redirect($this, "members");
            }
                    
            foreach (array_keys($_POST["member"]) as $user_id) {
                $submission = new ilExSubmission($this->assignment, $user_id);
                $tmembers = $submission->getUserIds();
                if (!(bool) $a_keep_teams) {
                    foreach ($tmembers as $tuser_id) {
                        $members[$tuser_id] = 1;
                    }
                } else {
                    if ($tmembers) {
                        $members[] = $tmembers;
                    } else {
                        // no team yet
                        $members[] = $user_id;
                    }
                }
            }
        }
        // multi-ass
        else {
            if (!$_POST["ass"]) {
                ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
                $this->ctrl->redirect($this, "showParticipant");
            }
            
            $user_id = $_GET["part_id"];
            
            foreach (array_keys($_POST["ass"]) as $ass_id) {
                $submission = new ilExSubmission(new ilExAssignment($ass_id), $user_id);
                $tmembers = $submission->getUserIds();
                if (!(bool) $a_keep_teams) {
                    foreach ($tmembers as $tuser_id) {
                        $members[$ass_id][] = $tuser_id;
                    }
                } else {
                    if ($tmembers) {
                        $members[$ass_id][] = $tmembers;
                    } else {
                        // no team yet
                        $members[$ass_id][] = $user_id;
                    }
                }
            }
        }
        
        return $members;
    }
            
    /**
    * Send assignment per mail to participants
    */
    public function sendMembersObject()
    {
        $members = $this->getMultiActionUserIds();
        
        ilUtil::sendSuccess($this->lng->txt("exc_sent"), true);
        if ($this->assignment) {
            $this->exercise->sendAssignment($this->assignment, array_keys($members));
            $this->ctrl->redirect($this, "members");
        } else {
            foreach ($members as $ass_id => $users) {
                $this->exercise->sendAssignment(new ilExAssignment($ass_id), $users);
            }
            $this->ctrl->setParameter($this, "part_id", $_GET["part_id"]); // #17629
            $this->ctrl->redirect($this, "showParticipant");
        }
    }

    /**
    * Confirm deassigning members
    */
    public function confirmDeassignMembersObject()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
            
        $members = $this->getMultiActionUserIds();
        
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setHeaderText($lng->txt("exc_msg_sure_to_deassign_participant"));
        $cgui->setCancel($lng->txt("cancel"), "members");
        $cgui->setConfirm($lng->txt("remove"), "deassignMembers");

        foreach ($members as $k => $m) {
            $cgui->addItem(
                "member[$k]",
                $m,
                ilUserUtil::getNamePresentation((int) $k, false, false, "", true)
            );
        }

        $tpl->setContent($cgui->getHTML());
    }
    
    /**
     * Deassign members from exercise
     */
    public function deassignMembersObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $members = $this->getMultiActionUserIds();
        
        foreach (array_keys($members) as $usr_id) {
            $this->exercise->members_obj->deassignMember((int) $usr_id);
            $this->removeUserSubmissionFilesFromWebDir((int) $usr_id);
        }
        ilUtil::sendSuccess($lng->txt("exc_msg_participants_removed"), true);
        $ilCtrl->redirect($this, "members");
    }

    public function removeUserSubmissionFilesFromWebDir(int $user_id) : void
    {
        $storage = new ilFSWebStorageExercise($this->exercise->getId(), $this->ass_id);
        $storage->deleteUserSubmissionDirectory($user_id);
    }

    public function saveCommentsObject()
    {
        if (!isset($_POST['comments_value'])) {
            return;
        }
  
        $this->exercise->members_obj->setNoticeForMember(
            $_GET["member_id"],
            ilUtil::stripSlashes($_POST["comments_value"])
        );
        ilUtil::sendSuccess($this->lng->txt("exc_members_comments_saved"));
        $this->membersObject();
    }

    /**
         * Save assignment status (participant view)
         */
    public function saveStatusParticipantObject(array $a_selected = null)
    {
        $ilCtrl = $this->ctrl;
        
        $member_id = (int) $_GET["part_id"];
        $data = array();
        foreach (array_keys($_POST["id"]) as $ass_id) {
            if (is_array($a_selected) &&
                !in_array($ass_id, $a_selected)) {
                continue;
            }
            
            $data[$ass_id][$member_id] = array(
                "status" => ilUtil::stripSlashes($_POST["status"][$ass_id])
            );
            
            if (array_key_exists("mark", $_POST)) {
                $data[$ass_id][$member_id]["mark"] = ilUtil::stripSlashes($_POST["mark"][$ass_id]);
            }
            if (array_key_exists("notice", $_POST)) {
                $data[$ass_id][$member_id]["notice"] = ilUtil::stripSlashes($_POST["notice"][$ass_id]);
            }
        }
        
        $ilCtrl->setParameter($this, "part_id", $member_id); // #17629
        $this->saveStatus($data);
    }
    

    public function saveStatusAllObject(array $a_selected = null, $a_redirect = true)
    {
        $user_ids = (array) array_keys((array) $_POST['id']);
        $filtered_user_ids = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $this->exercise->getRefId(),
            $user_ids
        );
        
        $data = array();
        foreach ($filtered_user_ids as $user_id) {
            if (is_array($a_selected) &&
                !in_array($user_id, $a_selected)) {
                continue;
            }

            $data[-1][$user_id] = array(
                "status" => ilUtil::stripSlashes($_POST["status"][$user_id])
            );

            if (array_key_exists("mark", $_POST)) {
                $data[-1][$user_id]["mark"] = ilUtil::stripSlashes($_POST["mark"][$user_id]);
            }
            if (array_key_exists("notice", $_POST)) {
                $data[-1][$user_id]["notice"] = ilUtil::stripSlashes($_POST["notice"][$user_id]);
            }
        }
        $this->saveStatus($data, $a_redirect);
    }
    
    public function saveStatusSelectedObject()
    {
        $members = $this->getMultiActionUserIds();
        
        if ($this->assignment) {
            $this->saveStatusAllObject(array_keys($members));
        } else {
            $this->saveStatusParticipantObject(array_keys($members));
        }
    }
    
    /**
     * Save status of selecte members
     */
    protected function saveStatus(array $a_data, $a_redirect = true)
    {
        $ilCtrl = $this->ctrl;
                
        $saved_for = array();
        foreach ($a_data as $ass_id => $users) {
            $ass = ($ass_id < 0)
                ? $this->assignment
                : new ilExAssignment($ass_id);
            foreach ($users as $user_id => $values) {
                // this will add team members if available
                $submission = new ilExSubmission($ass, $user_id);
                foreach ($submission->getUserIds() as $sub_user_id) {
                    $uname = ilObjUser::_lookupName($sub_user_id);
                    $saved_for[$sub_user_id] = $uname["lastname"] . ", " . $uname["firstname"];

                    $member_status = $ass->getMemberStatus($sub_user_id);

                    // see bug #22566
                    $status = $values["status"];
                    if ($status == "") {
                        $status = self::GRADE_NOT_GRADED;
                    }
                    $member_status->setStatus($status);
                    if (array_key_exists("mark", $values)) {
                        $member_status->setMark($values["mark"]);
                    }
                    if (array_key_exists("notice", $values)) {
                        $member_status->setNotice($values["notice"]);
                    }
                    $member_status->update();
                }
            }
        }
        
        if (count($saved_for) > 0) {
            $save_for_str = "(" . implode(" - ", $saved_for) . ")";
        }

        if ($a_redirect) {
            ilUtil::sendSuccess($this->lng->txt("exc_status_saved") . " " . $save_for_str, true);
            $ilCtrl->redirect($this, $this->getViewBack());
        }
    }

    /**
     * Save comment for learner (asynch)
     */
    public function saveCommentForLearnersObject()
    {
        $res = array("result" => false);
        
        if ($this->ctrl->isAsynch()) {
            $ass_id = (int) $_POST["ass_id"];
            $user_id = (int) $_POST["mem_id"];
            $comment = trim($_POST["comm"]);
            
            if ($ass_id && $user_id) {
                $submission = new ilExSubmission($this->assignment, $user_id);
                $user_ids = $submission->getUserIds();
                
                $all_members = new ilExerciseMembers($this->exercise);
                $all_members = $all_members->getMembers();
                
                $reci_ids = array();
                foreach ($user_ids as $user_id) {
                    if (in_array($user_id, $all_members)) {
                        $member_status = $this->assignment->getMemberStatus($user_id);
                        $member_status->setComment(ilUtil::stripSlashes($comment));
                        $member_status->setFeedback(true);
                        $member_status->update();
                        
                        if (trim($comment)) {
                            $reci_ids[] = $user_id;
                        }
                    }
                }
                
                if (sizeof($reci_ids)) {
                    // send notification
                    $this->exercise->sendFeedbackFileNotification(
                        null,
                        $reci_ids,
                        $ass_id,
                        true
                    );
                }
                
                $res = array("result" => true, "snippet" => nl2br($comment));
            }
        }
        
        echo(json_encode($res));
        exit();
    }
        
    /**
     * Export as excel
     */
    public function exportExcelObject()
    {
        $this->exercise->exportGradesExcel();
        exit;
    }
    
    
    //
    // TEAM
    //
    
    public function createTeamsObject()
    {
        $ilCtrl = $this->ctrl;
        
        $members = $this->getMultiActionUserIds(true);
                
        $new_members = array();

        foreach ($members as $group) {
            if (is_array($group)) {
                $new_members = array_merge($new_members, $group);

                $first_user = $group;
                $first_user = array_shift($first_user);
                $team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first_user);
                foreach ($group as $user_id) {
                    $team->removeTeamMember($user_id);
                }
            } else {
                $new_members[] = $group;
            }
        }

        if (sizeof($new_members)) {
            // see ilExSubmissionTeamGUI::addTeamMemberActionObject()

            $first_user = array_shift($new_members);
            $team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first_user, true);
            if (sizeof($new_members)) {
                foreach ($new_members as $user_id) {
                    $team->addTeamMember($user_id);
                }
            }

            // re-evaluate complete team, as some members might have had submitted
            $submission = new ilExSubmission($this->assignment, $first_user);
            $this->exercise->processExerciseStatus(
                $this->assignment,
                $team->getMembers(),
                $submission->hasSubmitted(),
                $submission->validatePeerReviews()
            );
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "members");
    }
    
    public function dissolveTeamsObject()
    {
        $ilCtrl = $this->ctrl;
        
        $members = $this->getMultiActionUserIds(true);
                        
        foreach ($members as $group) {
            // if single member - nothing to do
            if (is_array($group)) {
                // see ilExSubmissionTeamGUI::removeTeamMemberObject()

                $first_user = $group;
                $first_user = array_shift($first_user);
                $team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first_user);
                foreach ($group as $user_id) {
                    $team->removeTeamMember($user_id);
                }

                // reset ex team members, as any submission is not valid without team
                $this->exercise->processExerciseStatus(
                    $this->assignment,
                    $group,
                    false
                );
            }
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "members");
    }
    
    public function adoptTeamsFromGroupObject(ilPropertyFormGUI $a_form = null)
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs_gui;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, $this->getViewBack())
        );
        
        if (!$a_form) {
            $a_form = $this->initGroupForm();
        }
        $tpl->setContent($a_form->getHTML());
    }
    
    protected function initGroupForm()
    {
        $lng = $this->lng;
        
        $form = new ilPropertyFormGUI();
        $form->setTitle($lng->txt("exc_adopt_group_teams") . " - " . $this->assignment->getTitle());
        $form->setFormAction($this->ctrl->getFormAction($this, "createTeamsFromGroups"));
            
        $all_members = array();
        foreach (ilExAssignmentTeam::getGroupMembersMap($this->exercise->getRefId()) as $grp_id => $group) {
            if (sizeof($group["members"])) {
                $grp_team = new ilCheckboxGroupInputGUI($lng->txt("obj_grp") . " \"" . $group["title"] . "\"", "grpt_" . $grp_id);
                $grp_value = $options = array();
                foreach ($group["members"] as $user_id) {
                    $user_name = ilUserUtil::getNamePresentation($user_id, false, false, "", true);
                    $options[$user_id] = $user_name;
                    if (!in_array($user_id, $all_members)) {
                        $grp_value[] = $user_id;
                        $all_members[] = $user_id;
                    }
                }
                asort($options);
                foreach ($options as $user_id => $user_name) {
                    $grp_team->addOption(new ilCheckboxOption($user_name, $user_id));
                }
                $grp_team->setValue($grp_value);
                $form->addItem($grp_team);
            } else {
                $grp_team = new ilNonEditableValueGUI($group["title"]);
                $grp_team->setValue($lng->txt("exc_adopt_group_teams_no_members"));
                $form->addItem($grp_team);
            }
        }
        
        if (sizeof($all_members)) {
            $form->addCommandButton("createTeamsFromGroups", $lng->txt("save"));
        }
        $form->addCommandButton("members", $lng->txt("cancel"));
        
        return $form;
    }
    
    public function createTeamsFromGroupsObject()
    {
        $lng = $this->lng;
        
        $form = $this->initGroupForm();
        if ($form->checkInput()) {
            $map = ilExAssignmentTeam::getGroupMembersMap($this->exercise->getRefId());
            $all_members = $teams = array();
            $valid = true;
            foreach (array_keys($map) as $grp_id) {
                $postvar = "grpt_" . $grp_id;
                $members = $_POST[$postvar];
                if (is_array($members)) {
                    $teams[] = $members;
                    $invalid_team_members = array();
                    
                    foreach ($members as $user_id) {
                        if (!array_key_exists($user_id, $all_members)) {
                            $all_members[$user_id] = $grp_id;
                        } else {
                            // user is selected in multiple groups
                            $invalid_team_members[] = $user_id;
                        }
                    }
                    
                    if (sizeof($invalid_team_members)) {
                        $valid = false;
                        
                        $alert = array();
                        foreach ($invalid_team_members as $user_id) {
                            $user_name = ilUserUtil::getNamePresentation($user_id, false, false, "", true);
                            $grp_title = $map[$all_members[$user_id]]["title"];
                            $alert[] = sprintf($lng->txt("exc_adopt_group_teams_conflict"), $user_name, $grp_title);
                        }
                        $input = $form->getItemByPostVar($postvar);
                        $input->setAlert(implode("<br/>", $alert));
                    }
                }
            }
            if ($valid) {
                if (sizeof($teams)) {
                    $existing_users = array_keys(ilExAssignmentTeam::getAssignmentTeamMap($this->assignment->getId()));
                    
                    // create teams from group selections
                    $sum = array("added" => 0, "blocked" => 0);
                    foreach ($teams as $members) {
                        foreach ($members as $user_id) {
                            if (!$this->exercise->members_obj->isAssigned($user_id)) {
                                $this->exercise->members_obj->assignMember($user_id);
                            }
                            
                            if (!in_array($user_id, $existing_users)) {
                                $sum["added"]++;
                            } else {
                                $sum["blocked"]++;
                            }
                        }
                        
                        $first = array_shift($members);
                        $team = ilExAssignmentTeam::getInstanceByUserId($this->assignment->getId(), $first, true);
                        
                        // getTeamId() does NOT send notification
                        // $team->sendNotification($this->exercise->getRefId(), $first, "add");
                        
                        if (sizeof($members)) {
                            foreach ($members as $user_id) {
                                $team->addTeamMember($user_id);
                            }
                        }
                    }
                    
                    $mess = array();
                    if ($sum["added"]) {
                        $mess[] = sprintf($lng->txt("exc_adopt_group_teams_added"), $sum["added"]);
                    }
                    if ($sum["blocked"]) {
                        $mess[] = sprintf($lng->txt("exc_adopt_group_teams_blocked"), $sum["blocked"]);
                    }
                    if ($sum["added"]) {
                        ilUtil::sendSuccess(implode(" ", $mess), true);
                    } else {
                        ilUtil::sendFailure(implode(" ", $mess), true);
                    }
                }
                $this->ctrl->redirect($this, "members");
            } else {
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
            }
        }
        
        $form->setValuesByPost();
        $this->adoptTeamsFromGroupObject($form);
    }
    
    
    ////
    //// Multi Feedback
    ////
    
    public function initMultiFeedbackForm($a_ass_id)
    {
        $lng = $this->lng;
        
        $form = new ilPropertyFormGUI();
        $form->addCommandButton("uploadMultiFeedback", $lng->txt("upload"));
        $form->addCommandButton("members", $lng->txt("cancel"));
        
        // multi feedback file
        $fi = new ilFileInputGUI($lng->txt("exc_multi_feedback_file"), "mfzip");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);
                
        $form->setTitle(ilExAssignment::lookupTitle($a_ass_id));
        $form->setFormAction($this->ctrl->getFormAction($this, "uploadMultiFeedback"));
        
        return $form;
    }
    
    /**
     * Show multi-feedback screen
     *
     * @param
     * @return
     */
    public function showMultiFeedbackObject(ilPropertyFormGUI $a_form = null)
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        ilUtil::sendInfo($lng->txt("exc_multi_feedb_info"));
        
        $this->addSubTabs("assignment");
        
        // #13719
        $button = ilLinkButton::getInstance();
        $button->setCaption("exc_download_zip_structure");
        $button->setUrl($this->ctrl->getLinkTarget($this, "downloadMultiFeedbackZip"));
        $button->setOmitPreventDoubleSubmission(true);
        $ilToolbar->addButtonInstance($button);
        
        if (!$a_form) {
            $a_form = $this->initMultiFeedbackForm($this->assignment->getId());
        }
        
        $tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Download multi-feedback structrue file
     */
    public function downloadMultiFeedbackZipObject()
    {
        $this->assignment->sendMultiFeedbackStructureFile($this->exercise);
    }
    
    /**
     * Upload multi feedback file
     */
    public function uploadMultiFeedbackObject()
    {
        // #11983
        $form = $this->initMultiFeedbackForm($this->assignment->getId());
        if ($form->checkInput()) {
            try {
                $this->assignment->uploadMultiFeedbackFile(ilUtil::stripSlashesArray($_FILES["mfzip"]));
                $this->ctrl->redirect($this, "showMultiFeedbackConfirmationTable");
            } catch (ilExerciseException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
                $this->ctrl->redirect($this, "showMultiFeedback");
            }
        }
        
        $form->setValuesByPost();
        $this->showMultiFeedbackObject($form);
    }
    
    /**
     * Show multi feedback confirmation table
     *
     * @param
     * @return
     */
    public function showMultiFeedbackConfirmationTableObject()
    {
        $tpl = $this->tpl;
        
        $this->addSubTabs("assignment");
                
        $tab = new ilFeedbackConfirmationTable2GUI($this, "showMultiFeedbackConfirmationTable", $this->assignment);
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Cancel Multi Feedback
     */
    public function cancelMultiFeedbackObject()
    {
        $this->assignment->clearMultiFeedbackDirectory();
        $this->ctrl->redirect($this, "members");
    }
    
    /**
     * Save multi feedback
     */
    public function saveMultiFeedbackObject()
    {
        $this->assignment->saveMultiFeedbackFiles($_POST["file"], $this->exercise);
        
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "members");
    }
    
    
    //
    // individual deadlines
    //
    
    protected function initIndividualDeadlineModal()
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        
        // prepare modal+
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($lng->txt("exc_individual_deadline"));
        $modal->setId("ilExcIDl");
        $modal->setBody('<div id="ilExcIDlBody"></div>');
        $modal = $modal->getHTML();

        $ajax_url = $this->ctrl->getLinkTarget($this, "handleIndividualDeadlineCalls", "", true, false);

        $tpl->addJavaScript("./Modules/Exercise/js/ilExcIDl.js", true, 3);
        $tpl->addOnloadCode('il.ExcIDl.init("' . $ajax_url . '");');
                
        ilCalendarUtil::initDateTimePicker();

        return $modal;
    }
    
    protected function parseIndividualDeadlineData(array $a_data)
    {
        if ($a_data) {
            $map = array();
            $ass_tmp = array();
            foreach ($a_data as $item) {
                $item = explode("_", $item);
                $ass_id = $item[0];
                $user_id = $item[1];

                if (!array_key_exists($ass_id, $ass_tmp)) {
                    if ($this->assignment &&
                        $ass_id == $this->assignment->getId()) {
                        $ass_tmp[$ass_id] = $this->assignment;
                    } else {
                        $ass_tmp[$ass_id] = new ilExAssignment($ass_id);
                    }
                }
                
                $map[$ass_id][] = $user_id;
            }

            return array($map, $ass_tmp);
        }
    }
    
    protected function handleIndividualDeadlineCallsObject()
    {
        $tpl = $this->tpl;
        
        $this->ctrl->saveParameter($this, "part_id");
        
        // we are done
        if ((bool) $_GET["dn"]) {
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, $this->assignment
                ? "members"
                : "showParticipant");
        }
        
        // initial form call
        if ($_GET["idlid"]) {
            $tmp = $this->parseIndividualDeadlineData(explode(",", $_GET["idlid"]));
            if (is_array($tmp)) {
                $form = $this->initIndividualDeadlineForm($tmp[1], $tmp[0]);
                echo $form->getHTML() .
                    $tpl->getOnLoadCodeForAsynch();
            }
        }
        // form "submit"
        else {
            $tmp = array();
            foreach (array_keys($_POST) as $id) {
                if (substr($id, 0, 3) == "dl_") {
                    $tmp[] = substr($id, 3);
                }
            }
            $tmp = $this->parseIndividualDeadlineData($tmp);
            $ass_map = $tmp[1];
            $users = $tmp[0];
            unset($tmp);
            
            $form = $this->initIndividualDeadlineForm($ass_map, $users);
            $res = array();
            if ($valid = $form->checkInput()) {
                foreach ($users as $ass_id => $users) {
                    $ass = $ass_map[$ass_id];
                    
                    // :TODO: should individual deadlines BEFORE extended be possible?
                    $dl = new ilDateTime($ass->getDeadline(), IL_CAL_UNIX);
                    
                    foreach ($users as $user_id) {
                        $date_field = $form->getItemByPostVar("dl_" . $ass_id . "_" . $user_id);
                        if (ilDate::_before($date_field->getDate(), $dl)) {
                            $date_field->setAlert(sprintf($this->lng->txt("exc_individual_deadline_before_global"), ilDatePresentation::formatDate($dl)));
                            $valid = false;
                        } else {
                            $res[$ass_id][$user_id] = $date_field->getDate();
                        }
                    }
                }
            }

            if (!$valid) {
                $form->setValuesByPost();
                echo $form->getHTML() .
                    $tpl->getOnLoadCodeForAsynch();
            } else {
                foreach ($res as $ass_id => $users) {
                    $ass = $ass_map[$ass_id];
                    
                    foreach ($users as $id => $date) {
                        $ass->setIndividualDeadline($id, $date);
                    }

                    $ass->recalculateLateSubmissions();
                }

                echo "ok";
            }
        }
        
        exit();
    }
    
    protected function initIndividualDeadlineForm(array $a_ass_map, array $ids)
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setName("ilExcIDlForm");
        
        foreach ($ids as $ass_id => $users) {
            $ass = $a_ass_map[$ass_id];
            
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($ass->getTitle());
            $form->addItem($section);
        
            $teams = ilExAssignmentTeam::getInstancesFromMap($ass->getId());

            $values = $ass->getIndividualDeadlines();
            
            foreach ($users as $id) {
                // single user
                if (is_numeric($id)) {
                    $name = ilObjUser::_lookupName($id);
                    $name = $name["lastname"] . ", " . $name["firstname"];
                }
                // team
                else {
                    $name = "";
                    $team_id = (int) substr($id, 1);
                    if (array_key_exists($team_id, $teams)) {
                        $name = array();
                        foreach ($teams[$team_id]->getMembers() as $member_id) {
                            $uname = ilObjUser::_lookupName($member_id);
                            $name[] = $uname["lastname"] . ", " . $uname["firstname"];
                        }
                        asort($name);
                        $name = implode("<br />", $name);
                    }
                }

                $dl = new ilDateTimeInputGUI($name, "dl_" . $ass_id . "_" . $id);
                $dl->setShowTime(true);
                $dl->setRequired(true);
                $form->addItem($dl);

                if (array_key_exists($id, $values)) {
                    $dl->setDate(new ilDateTime($values[$id], IL_CAL_UNIX));
                }
            }
        }
        
        $form->addCommandButton("", $this->lng->txt("save"));
        
        return $form;
    }
    
    protected function setIndividualDeadlineObject()
    {
        // this will only get called if no selection
        ilUtil::sendFailure($this->lng->txt("select_one"));

        if ($this->assignment) {
            $this->membersObject();
        } else {
            $this->showParticipantObject();
        }
    }

    public function initFilter()
    {
        if ($_POST["filter_status"]) {
            $this->filter["status"] = trim(ilUtil::stripSlashes($_POST["filter_status"]));
        }

        if ($_POST["filter_feedback"]) {
            $this->filter["feedback"] = trim(ilUtil::stripSlashes($_POST["filter_feedback"]));
        }

        $this->lng->loadLanguageModule("search");

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "listTextAssignment"));

        // Status

        if ($_POST["filter_status"]) {
            $this->filter["status"] = trim(ilUtil::stripSlashes($_POST["filter_status"]));
        }

        $si_status = new ilSelectInputGUI($this->lng->txt("exc_tbl_status"), "filter_status");
        $options = array(
            "" => $this->lng->txt("search_any"),
            self::GRADE_NOT_GRADED => $this->lng->txt("exc_notgraded"),
            self::GRADE_PASSED => $this->lng->txt("exc_passed"),
            self::GRADE_FAILED => $this->lng->txt("exc_failed")
        );
        $si_status->setOptions($options);
        $si_status->setValue($this->filter["status"]);

        $si_feedback = new ilSelectInputGUI($this->lng->txt("feedback"), "filter_feedback");
        $options = array(
            self::FEEDBACK_FULL_SUBMISSION => $this->lng->txt("submissions_feedback"),
            self::FEEDBACK_ONLY_SUBMISSION => $this->lng->txt("submissions_only")
        );
        $si_feedback->setOptions($options);
        $si_feedback->setValue($this->filter["feedback"]);

        $this->toolbar->addInputItem($si_status, true);

        // Submissions and Feedback
        #24713
        if ($this->assignment->getPeerReview()) {
            if ($_POST["filter_feedback"]) {
                $this->filter["feedback"] = trim(ilUtil::stripSlashes($_POST["filter_feedback"]));
            } else {
                $this->filter["feedback"] = "submission_feedback";
            }

            $si_feedback = new ilSelectInputGUI($this->lng->txt("feedback"), "filter_feedback");
            $options = array(
                "submission_feedback" => $this->lng->txt("submissions_feedback"),
                "submission_only" => $this->lng->txt("submissions_only")
            );
            $si_feedback->setOptions($options);
            $si_feedback->setValue($this->filter["feedback"]);

            $this->toolbar->addInputItem($si_feedback, true);
        }

        //todo: old school here.
        $submit = ilSubmitButton::getInstance();
        $submit->setCaption("filter");
        $submit->setCommand("listTextAssignment");
        $this->toolbar->addButtonInstance($submit);
    }

    /**
     * Open HTML view for portfolio submissions
     */
    public function openSubmissionViewObject()
    {
        global $DIC;

        $ass_id = (int) $_GET["ass_id"];
        $member_id = (int) $_GET["member_id"];

        $submission = new ilExSubmission($this->assignment, $member_id);

        $last_opening = $submission->getLastOpeningHTMLView();

        $submission_time = $submission->getLastSubmission();

        $zip_original_full_path = $this->getSubmissionZipFilePath($submission);

        $zip_internal_path = $this->getWebFilePathFromExternalFilePath($zip_original_full_path);

        list($obj_date, $obj_id) = explode("_", basename($zip_original_full_path));

        $obj_dir = $this->assignment->getAssignmentType()->getStringIdentifier() . "_" . $obj_id;

        $index_html_file = ILIAS_WEB_DIR .
            DIRECTORY_SEPARATOR .
            CLIENT_ID .
            DIRECTORY_SEPARATOR .
            dirname($zip_internal_path) .
            DIRECTORY_SEPARATOR .
            $obj_dir .
            DIRECTORY_SEPARATOR .
            "index.html";

        ilWACSignedPath::signFolderOfStartFile($index_html_file);

        $web_filesystem = $DIC->filesystem()->web();

        if ($last_opening > $submission_time && $web_filesystem->has($index_html_file)) {
            ilUtil::redirect($index_html_file);
        }
        if ($zip_original_full_path) {
            $file_copied = $this->copyFileToWebDir($zip_original_full_path, $zip_internal_path);

            if ($file_copied) {
                ilUtil::unzip($file_copied, true);

                $web_filesystem->delete($zip_internal_path);

                $submission_repository = new ilExcSubmissionRepository();
                $submission_repository->updateWebDirAccessTime($this->assignment->getId(), $member_id);

                ilUtil::redirect($index_html_file);
            }

            $error_msg = $this->lng->txt("exc_copy_zip_error");
        }

        if (!$error_msg) {
            $error_msg = $this->lng->txt("exc_find_zip_error");
        }

        ilUtil::sendFailure($error_msg);
    }

    /**
     * Returns the ZIP file path from outside web directory
     * @param ilExSubmission user who created the submission
     * @return string|null
     */
    protected function getSubmissionZipFilePath(ilExSubmission $submission) : ?string
    {
        $submitted = $submission->getFiles();

        if (count($submitted) > 0) {
            $submitted = array_pop($submitted);

            return $submitted['filename'];
        }

        return null;
    }

    /**
     * Generate the directories and copy the file if necessary.
     * Returns the copied file path.
     * @param string $external_file
     * @return null |string
     */
    protected function copyFileToWebDir(string $origin_path_filename, string $internal_file_path) : ?string
    {
        global $DIC;

        $web_filesystem = $DIC->filesystem()->web();
        $data_filesystem = $DIC->filesystem()->storage();

        $internal_dirs = dirname($internal_file_path);
        $zip_file = basename($internal_file_path);

        if ($data_filesystem->has($internal_file_path)) {
            if (!$web_filesystem->hasDir($internal_dirs)) {
                $web_filesystem->createDir($internal_dirs);
            }

            if (!$web_filesystem->has($internal_file_path)) {
                $stream = $data_filesystem->readStream($internal_file_path);
                $web_filesystem->writeStream($internal_file_path, $stream);

                return ILIAS_ABSOLUTE_PATH .
                    DIRECTORY_SEPARATOR .
                    ILIAS_WEB_DIR .
                    DIRECTORY_SEPARATOR .
                    CLIENT_ID .
                    DIRECTORY_SEPARATOR .
                    $internal_dirs .
                    DIRECTORY_SEPARATOR .
                    $zip_file;
            }
        }

        return null;
    }

    /**
     * Get the object specific file path from an external full file path.
     * @param string $external_file_path
     * @return string
     */
    protected function getWebFilePathFromExternalFilePath(string $external_file_path) : string
    {
        list($external_path, $internal_file_path) = explode(CLIENT_ID . "/", $external_file_path);

        return $internal_file_path;
    }

    /*
     * Add the Back link to the tabs. (used in submission list and submission compare)
     */
    protected function setBackToMembers()
    {
        //tabs
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "members")
        );
    }

    /**
     * @param $a_data array submission data
     * @return $data array
     */
    public function collectFeedbackDataFromPeer(array $a_data) : array
    {
        $user = new ilObjUser($a_data["user_id"]);
        $uname = $user->getFirstname() . " " . $user->getLastname();

        $data = array(
            "uid" => $a_data["user_id"],
            "uname" => $uname,
            "udate" => $a_data["ts"],
            "utext" => ilRTE::_replaceMediaObjectImageSrc($a_data["atext"], 1) // mob id to mob src
        );

        //get data peer and assign it
        $peer_review = new ilExPeerReview($this->assignment);
        $data["peer"] = array();
        foreach ($peer_review->getPeerReviewsByPeerId($a_data['user_id']) as $key => $value) {
            $data["peer"][] = $value['giver_id'];
        }

        $data["fb_received"] = count($data["peer"]);
        $data["fb_given"] = $peer_review->countGivenFeedback(true, $a_data["user_id"]);

        return $data;
    }
}
