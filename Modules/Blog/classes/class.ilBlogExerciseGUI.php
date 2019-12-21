<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBlogExerciseGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilBlogExerciseGUI:
 */
class ilBlogExerciseGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $node_id; // [int]
    protected $ass_id; // [int]
    protected $file; // [string]

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;
    
    public function __construct($a_node_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->node_id = $a_node_id;
        $this->ass_id = (int) $_GET["ass"];
        $this->file = trim(ilUtil::stripSlashes($_GET["file"]));
        $this->ui = $DIC->ui();
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->ass_id) {
            $this->ctrl->returnToParent($this);
        }
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
    
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
        
        return true;
    }
    
    public static function checkExercise($a_node_id)
    {
        $be = new ilBlogExercise($a_node_id);

        foreach ($be->getAssignmentsOfBlog() as $ass) {
            $part = self::getExerciseInfo($ass["ass_id"]);
            if ($part) {
                $info[] = $part;
            }
        }
        if (sizeof($info)) {
            return implode("<br />", $info);
        }
        return "";
    }

    protected static function getExerciseInfo($a_assignment_id)
    {
        global $DIC;

        $ui = $DIC->ui();

        $links = [];
        $buttons = [];
        $elements = [];

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
                    
        $ass = new ilExAssignment($a_assignment_id);
        $exercise_id = $ass->getExerciseId();
        if (!$exercise_id) {
            return;
        }
        
        // is the assignment still open?
        $times_up = $ass->afterDeadlineStrict();
        
        // exercise goto
        $exc_ref_id = array_shift(ilObject::_getAllReferences($exercise_id));
        $exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");

        $text = sprintf(
            $lng->txt("blog_exercise_info"),
            $ass->getTitle(),
            ilObject::_lookupTitle($exercise_id)
        );
        $links[] = $ui->factory()->link()->standard(ilObject::_lookupTitle($exercise_id), $exc_link);
        
        // submit button
        if (!$times_up) {
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");

            $buttons[] = $ui->factory()->button()->primary($lng->txt("blog_finalize_blog"), $submit_link);
        }
        
        // submitted files
        $submission = new ilExSubmission($ass, $ilUser->getId());
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();
            
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
            $dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcSubFile");
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
            
            $rel = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);

            $text .= "<br />" . sprintf(
                $lng->txt("blog_exercise_submitted_info"),
                ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
                ""
            );
            
            ilDatePresentation::setUseRelativeDates($rel);
            $buttons[] = $ui->factory()->button()->standard($lng->txt("blog_download_submission"), $dl_link);
        }
        
        
        // work instructions incl. files
        
        $tooltip = "";

        $inst = $ass->getInstruction();
        if ($inst) {
            $tooltip .= nl2br($inst);
        }

        $ass_files = $ass->getFiles();
        if (count($ass_files) > 0) {
            $tooltip .= "<br /><br />";
            
            foreach ($ass_files as $file) {
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
                $ilCtrl->setParameterByClass("ilblogexercisegui", "file", urlencode($file["name"]));
                $dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcAssFile");
                $ilCtrl->setParameterByClass("ilblogexercisegui", "file", "");
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");

                $items[] = $ui->renderer()->render($ui->factory()->button()->shy($file["name"], $dl_link));
            }
            $list = $ui->factory()->listing()->unordered($items);
            $tooltip .= $ui->renderer()->render($list);
        }
        
        if ($tooltip) {
            $modal = $ui->factory()->modal()->roundtrip($lng->txt("exc_instruction"), $ui->factory()->legacy($tooltip))
                ->withCancelButtonLabel("close");
            $elements[] = $modal;
            $buttons[] = $ui->factory()->button()->standard($lng->txt("exc_instruction"), '#')
                ->withOnClick($modal->getShowSignal());
        }

        $elements[] = $ui->factory()->messageBox()->info($text)
            ->withLinks($links)
            ->withButtons($buttons);

        return $ui->renderer()->render($elements);
    }
    
    protected function downloadExcAssFile()
    {
        if ($this->file) {
            $ass = new ilExAssignment($this->ass_id);
            $ass_files = $ass->getFiles();
            if (count($ass_files) > 0) {
                foreach ($ass_files as $file) {
                    if ($file["name"] == $this->file) {
                        ilUtil::deliverFile($file["fullpath"], $file["name"]);
                    }
                }
            }
        }
    }
    
    protected function downloadExcSubFile()
    {
        $ilUser = $this->user;
                
        $ass = new ilExAssignment($this->ass_id);
        $submission = new ilExSubmission($ass, $ilUser->getId());
        $submitted = $submission->getFiles();
        if (count($submitted) > 0) {
            $submitted = array_pop($submitted);

            $user_data = ilObjUser::_lookupName($submitted["user_id"]);
            $title = ilObject::_lookupTitle($submitted["obj_id"]) . " - " .
                $ass->getTitle() . " - " .
                $user_data["firstname"] . " " .
                $user_data["lastname"] . " (" .
                $user_data["login"] . ").zip";

            ilUtil::deliverFile($submitted["filename"], $title);
        }
    }
        
    /**
     * Finalize and submit blog to exercise
     */
    protected function finalize()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $exc_gui = ilExSubmissionObjectGUI::initGUIForSubmit($this->ass_id);
        $exc_gui->submitBlog($this->node_id);

        ilUtil::sendSuccess($lng->txt("blog_finalized"), true);
        $ilCtrl->returnToParent($this);
    }

    /**
     * Get submit link
     *
     * @param int $ass_id
     * @return object
     */
    public function getSubmitButton(int $ass_id)
    {
        $ilCtrl = $this->ctrl;
        $ui = $this->ui;
        $lng = $this->lng;

        $state = ilExcAssMemberState::getInstanceByIds($ass_id, $this->user->getId());

        if ($state->isSubmissionAllowed()) {
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $ass_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
            $button = $ui->factory()->button()->primary($lng->txt("blog_finalize_blog"), $submit_link);
            return $button;
        }
        return null;
    }

    /**
     * Get download button
     *
     * @param
     * @return
     */
    public function getDownloadSubmissionButton(int $ass_id)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        // submitted files
        $submission = new ilExSubmission(new ilExAssignment($ass_id), $this->user->getId());
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();
            if ($submitted["ts"] != "") {
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $ass_id);
                $dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcSubFile");
                $ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
                $button = $ui->factory()->button()->standard($lng->txt("blog_download_submission"), $dl_link);
                return $button;
            }
        }
        return null;
    }


    /**
     * Get action buttons
     *
     * @return array
     */
    public function getActionButtons()
    {
        $be = new ilBlogExercise($this->node_id);

        $buttons = [];
        foreach ($be->getAssignmentsOfBlog() as $exercise) {
            $ass_id = $exercise["ass_id"];
            $buttons[$ass_id] = [];
            $submit_button = $this->getSubmitButton($ass_id);
            if ($submit_button != null) {
                $buttons[$ass_id][] = $submit_button;
            }
            $download_button = $this->getDownloadSubmissionButton($ass_id);
            if ($download_button != null) {
                $buttons[$ass_id][] = $download_button;
            }
        }

        return $buttons;
    }
}
