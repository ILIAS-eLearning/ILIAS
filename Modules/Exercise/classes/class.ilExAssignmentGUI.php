<?php

/**
 * GUI class for exercise assignments
 *
 * This is not a real GUI class, could be moved to ilObjExerciseGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilExAssignmentGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $exc; // [ilObjExercise]
    protected $current_ass_id; // [int]

    /**
     * @var ilExerciseInternalService
     */
    protected $service;

    /**
     * @var ilExcMandatoryAssignmentManager
     */
    protected $mandatory_manager;
    
    /**
     * Constructor
     */
    public function __construct(ilObjExercise $a_exc, ilExerciseInternalService $service)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();

        $this->exc = $a_exc;
        $this->service = $service;
        $this->mandatory_manager = $service->getMandatoryAssignmentManager($this->exc);
    }
    
    /**
     * Get assignment header for overview
     */
    public function getOverviewHeader(ilExAssignment $a_ass)
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $lng->loadLanguageModule("exc");

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        $tpl = new ilTemplate("tpl.assignment_head.html", true, true, "Modules/Exercise");
        
        // we are completely ignoring the extended deadline here
        
        $idl = $a_ass->getPersonalDeadline($ilUser->getId());
        
        // :TODO: meaning of "ended on"
        $dl = max($a_ass->getDeadline(), $idl);
        // if ($dl &&
        //	$dl < time())
        if ($state->exceededOfficialDeadline()) {
            $tpl->setCurrentBlock("prop");
            $tpl->setVariable("PROP", $lng->txt("exc_ended_on"));
            $tpl->setVariable("PROP_VAL", $state->getCommonDeadlinePresentation());
            $tpl->parseCurrentBlock();
            
            // #14077						// this currently shows the feedback deadline during grace period
            if ($state->getPeerReviewDeadline()) {
                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_peer_review_deadline"));
                $tpl->setVariable("PROP_VAL", $state->getPeerReviewDeadlinePresentation());
                $tpl->parseCurrentBlock();
            }
        } elseif (!$state->hasGenerallyStarted()) {
            if ($state->getRelativeDeadline()) {
                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_earliest_start_time"));
                $tpl->setVariable("PROP_VAL", $state->getGeneralStartPresentation());
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_starting_on"));
                $tpl->setVariable("PROP_VAL", $state->getGeneralStartPresentation());
                $tpl->parseCurrentBlock();
            }
        } else {
            if ($state->getCommonDeadline() > 0) {
                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_time_to_send"));
                $tpl->setVariable("PROP_VAL", $state->getRemainingTimePresentation());
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_edit_until"));
                $tpl->setVariable("PROP_VAL", $state->getCommonDeadlinePresentation());
                $tpl->parseCurrentBlock();
            } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_rem_time_after_start"));
                $tpl->setVariable("PROP_VAL", $state->getRelativeDeadlinePresentation());
                $tpl->parseCurrentBlock();

                if ($state->getLastSubmissionOfRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                    $tpl->setCurrentBlock("prop");
                    $tpl->setVariable("PROP", $lng->txt("exc_rel_last_submission"));
                    $tpl->setVariable("PROP_VAL", $state->getLastSubmissionOfRelativeDeadlinePresentation());
                    $tpl->parseCurrentBlock();
                }
            }


            if ($state->getIndividualDeadline() > 0) {
                $tpl->setCurrentBlock("prop");
                $tpl->setVariable("PROP", $lng->txt("exc_individual_deadline"));
                $tpl->setVariable("PROP_VAL", $state->getIndividualDeadlinePresentation());
                $tpl->parseCurrentBlock();
            }
        }

        $mand = "";
        if ($this->mandatory_manager->isMandatoryForUser($a_ass->getId(), $this->user->getId())) {
            $mand = " (" . $lng->txt("exc_mandatory") . ")";
        }
        $tpl->setVariable("TITLE", $a_ass->getTitle() . $mand);

        // status icon
        $stat = $a_ass->getMemberStatus()->getStatus();
        $pic = $a_ass->getMemberStatus()->getStatusIcon();
        $tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
        $tpl->setVariable("ALT_STATUS", $lng->txt("exc_" . $stat));

        return $tpl->get();
    }

    /**
     * Get assignment body for overview
     */
    public function getOverviewBody(ilExAssignment $a_ass)
    {
        global $DIC;

        $ilUser = $DIC->user();

        $this->current_ass_id = $a_ass->getId();
        
        $tpl = new ilTemplate("tpl.assignment_body.html", true, true, "Modules/Exercise");

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        $info = new ilInfoScreenGUI(null);
        $info->setTableClass("");

        if ($state->areInstructionsVisible()) {
            $this->addInstructions($info, $a_ass);
            $this->addFiles($info, $a_ass);
        }

        $this->addSchedule($info, $a_ass);
        
        if ($state->hasSubmissionStarted()) {
            $this->addSubmission($info, $a_ass);
        }

        $tpl->setVariable("CONTENT", $info->getHTML());
        
        return $tpl->get();
    }
    
    
    protected function addInstructions(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
    {
        $ilUser = $this->user;

        $info = new ilExAssignmentInfo($a_ass->getId(), $ilUser->getId());
        $inst = $info->getInstructionInfo();
        if (count($inst) > 0) {
            $a_info->addSection($inst["instruction"]["txt"]);
            $a_info->addProperty("", $inst["instruction"]["value"]);
        }
    }
    
    protected function addSchedule(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $info = new ilExAssignmentInfo($a_ass->getId(), $ilUser->getId());
        $schedule = $info->getScheduleInfo();

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        $a_info->addSection($lng->txt("exc_schedule"));
        if ($state->getGeneralStart() > 0) {
            $a_info->addProperty($schedule["start_time"]["txt"], $schedule["start_time"]["value"]);
        }


        if ($state->getCommonDeadline()) {		// if we have a common deadline (target timestamp)
            $a_info->addProperty($schedule["until"]["txt"], $schedule["until"]["value"]);
        } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
            $but = "";
            if ($state->hasGenerallyStarted()) {
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_ass->getId());
                $but = $this->ui->factory()->button()->primary($lng->txt("exc_start_assignment"), $ilCtrl->getLinkTargetByClass("ilobjexercisegui", "startAssignment"));
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $_GET["ass_id"]);
                $but = $this->ui->renderer()->render($but);
            }

            $a_info->addProperty($schedule["time_after_start"]["txt"], $schedule["time_after_start"]["value"] . " " . $but);
            if ($state->getLastSubmissionOfRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                $a_info->addProperty(
                    $lng->txt("exc_rel_last_submission"),
                    $state->getLastSubmissionOfRelativeDeadlinePresentation()
                );
            }
        }

        if ($state->getOfficialDeadline() > $state->getCommonDeadline()) {
            $a_info->addProperty($schedule["individual_deadline"]["txt"], $schedule["individual_deadline"]["value"]);
        }
                
        if ($state->hasSubmissionStarted()) {
            $a_info->addProperty($schedule["time_to_send"]["txt"], $schedule["time_to_send"]["value"]);
        }
    }
    
    protected function addPublicSubmissions(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        // submissions are visible, even if other users may still have a larger individual deadline
        if ($state->hasSubmissionEnded()) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("exc_list_submission");
            $button->setUrl($this->getSubmissionLink("listPublicSubmissions"));

            $a_info->addProperty($lng->txt("exc_public_submission"), $button->render());
        } else {
            $a_info->addProperty(
                $lng->txt("exc_public_submission"),
                $lng->txt("exc_msg_public_submission")
            );
        }
    }
    
    protected function addFiles(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule("exc");
        
        $files = $a_ass->getFiles();

        if (count($files) > 0) {
            $a_info->addSection($lng->txt("exc_files"));

            global $DIC;

            //file has -> name,fullpath,size,ctime
            $cnt = 0;
            foreach ($files as $file) {
                $cnt++;
                // get mime type
                $mime = ilObjMediaObject::getMimeType($file['fullpath']);

                list($format, $type) = explode("/", $mime);

                $ui_factory = $DIC->ui()->factory();
                $ui_renderer = $DIC->ui()->renderer();

                if (in_array($mime, array("image/jpeg", "image/svg+xml", "image/gif", "image/png"))) {
                    $item_id = "il-ex-modal-img-" . $a_ass->getId() . "-" . $cnt;


                    $image = $ui_renderer->render($ui_factory->image()->responsive($file['fullpath'], $file['name']));
                    $image_lens = ilUtil::getImagePath("enlarge.svg");

                    $modal = ilModalGUI::getInstance();
                    $modal->setId($item_id);
                    $modal->setType(ilModalGUI::TYPE_LARGE);
                    $modal->setBody($image);
                    $modal->setHeading($file["name"]);
                    $modal = $modal->getHTML();

                    $img_tpl = new ilTemplate("tpl.image_file.html", true, true, "Modules/Exercise");
                    $img_tpl->setCurrentBlock("image_content");
                    $img_tpl->setVariable("MODAL", $modal);
                    $img_tpl->setVariable("ITEM_ID", $item_id);
                    $img_tpl->setVariable("IMAGE", $image);
                    $img_tpl->setvariable("IMAGE_LENS", $image_lens);
                    $img_tpl->setvariable("ALT_LENS", $lng->txt("exc_fullscreen"));
                    $img_tpl->parseCurrentBlock();

                    $a_info->addProperty($file["name"], $img_tpl->get());
                } elseif (in_array($mime, array("audio/mpeg", "audio/ogg", "video/mp4", "video/x-flv", "video/webm"))) {
                    $media_tpl = new ilTemplate("tpl.media_file.html", true, true, "Modules/Exercise");
                    $mp = new ilMediaPlayerGUI();
                    $mp->setFile($file['fullpath']);
                    $media_tpl->setVariable("MEDIA", $mp->getMediaPlayerHtml());

                    $but = $ui_factory->button()->shy(
                        $lng->txt("download"),
                        $this->getSubmissionLink("downloadFile", array("file" => urlencode($file["name"])))
                    );
                    $media_tpl->setVariable("DOWNLOAD_BUTTON", $ui_renderer->render($but));
                    $a_info->addProperty($file["name"], $media_tpl->get());
                } else {
                    $a_info->addProperty($file["name"], $lng->txt("download"), $this->getSubmissionLink("downloadFile", array("file" => urlencode($file["name"]))));
                }
            }
        }
    }

    protected function addSubmission(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        $a_info->addSection($lng->txt("exc_your_submission"));

        $submission = new ilExSubmission($a_ass, $ilUser->getId());

        ilExSubmissionGUI::getOverviewContent($a_info, $submission, $this->exc);

        $last_sub = null;
        if ($submission->hasSubmitted()) {
            $last_sub = $submission->getLastSubmission();
            if ($last_sub) {
                $last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub, IL_CAL_DATETIME));
                $a_info->addProperty($lng->txt("exc_last_submission"), $last_sub);
            }
        }

        if ($this->exc->getShowSubmissions()) {
            $this->addPublicSubmissions($a_info, $a_ass);
        }

        ilExPeerReviewGUI::getOverviewContent($a_info, $submission);

        // global feedback / sample solution
        if ($a_ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE) {
            $show_global_feedback = ($state->hasSubmissionEndedForAllUsers() && $a_ass->getFeedbackFile());
        }
        //If it is not well configured...(e.g. show solution before deadline)
        //the user can get the solution before he summit it.
        //we can check in the elseif $submission->hasSubmitted()
        elseif ($a_ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_CUSTOM) {
            $show_global_feedback = ($a_ass->afterCustomDate() && $a_ass->getFeedbackFile());
        } else {
            $show_global_feedback = ($last_sub && $a_ass->getFeedbackFile());
        }

        $this->addSubmissionFeedback($a_info, $a_ass, $submission->getFeedbackId(), $show_global_feedback);
    }
    
    protected function addSubmissionFeedback(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_feedback_id, $a_show_global_feedback)
    {
        $lng = $this->lng;

        $storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());
        $cnt_files = $storage->countFeedbackFiles($a_feedback_id);
        
        $lpcomment = $a_ass->getMemberStatus()->getComment();
        $mark = $a_ass->getMemberStatus()->getMark();
        $status = $a_ass->getMemberStatus()->getStatus();
        
        if ($lpcomment != "" ||
            $mark != "" ||
            $status != "notgraded" ||
            $cnt_files > 0 ||
            $a_show_global_feedback) {
            $a_info->addSection($lng->txt("exc_feedback_from_tutor"));
            if ($lpcomment != "") {
                $a_info->addProperty(
                    $lng->txt("exc_comment"),
                    nl2br($lpcomment)
                );
            }
            if ($mark != "") {
                $a_info->addProperty(
                    $lng->txt("exc_mark"),
                    $mark
                );
            }

            if ($status == "") {
                //				  $a_info->addProperty($lng->txt("status"),
//						$lng->txt("message_no_delivered_files"));
            } elseif ($status != "notgraded") {
                $img = '<img src="' . ilUtil::getImagePath("scorm/" . $status . ".svg") . '" ' .
                    ' alt="' . $lng->txt("exc_" . $status) . '" title="' . $lng->txt("exc_" . $status) .
                    '" />';
                $a_info->addProperty(
                    $lng->txt("status"),
                    $img . " " . $lng->txt("exc_" . $status)
                );
            }

            if ($cnt_files > 0) {
                $a_info->addSection($lng->txt("exc_fb_files") .
                    '<a name="fb' . $a_ass->getId() . '"></a>');

                if ($cnt_files > 0) {
                    $files = $storage->getFeedbackFiles($a_feedback_id);
                    foreach ($files as $file) {
                        $a_info->addProperty(
                            $file,
                            $lng->txt("download"),
                            $this->getSubmissionLink("downloadFeedbackFile", array("file" => urlencode($file)))
                        );
                    }
                }
            }

            // #15002 - global feedback
            if ($a_show_global_feedback) {
                $a_info->addSection($lng->txt("exc_global_feedback_file"));

                $a_info->addProperty(
                    $a_ass->getFeedbackFile(),
                    $lng->txt("download"),
                    $this->getSubmissionLink("downloadGlobalFeedbackFile")
                );
            }
        }
    }
    
    /**
     * Get time string for deadline
     */
    public function getTimeString($a_deadline)
    {
        $lng = $this->lng;
        
        if ($a_deadline == 0) {
            return $lng->txt("exc_submit_convenience_no_deadline");
        }
        
        if ($a_deadline - time() <= 0) {
            $time_str = $lng->txt("exc_time_over_short");
        } else {
            $time_str = ilUtil::period2String(new ilDateTime($a_deadline, IL_CAL_UNIX));
        }

        return $time_str;
    }
    
    protected function getSubmissionLink($a_cmd, array $a_params = null)
    {
        $ilCtrl = $this->ctrl;
        
        if (is_array($a_params)) {
            foreach ($a_params as $name => $value) {
                $ilCtrl->setParameterByClass("ilexsubmissiongui", $name, $value);
            }
        }
        
        $ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", $this->current_ass_id);
        $url = $ilCtrl->getLinkTargetByClass("ilexsubmissiongui", $a_cmd);
        $ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");
        
        if (is_array($a_params)) {
            foreach ($a_params as $name => $value) {
                $ilCtrl->setParameterByClass("ilexsubmissiongui", $name, "");
            }
        }
        
        return $url;
    }
}
