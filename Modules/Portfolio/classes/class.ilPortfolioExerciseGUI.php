<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExAssignment.php";
include_once "Modules/Exercise/classes/class.ilExSubmission.php";

/**
* Class ilPortfolioExerciseGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilPortfolioExerciseGUI:
*/
class ilPortfolioExerciseGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $user_id; // [int]
    protected $obj_id; // [int]
    protected $ass_id; // [int]
    protected $file; // [string]
    
    public function __construct($a_user_id, $a_obj_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user_id = $a_user_id;
        $this->obj_id = $a_obj_id;
        $this->ass_id = (int) $_GET["ass"];
        $this->file = trim($_GET["file"]);
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        if (!$this->ass_id ||
            !$this->user_id) {
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
    
    public static function checkExercise($a_user_id, $a_obj_id, $a_add_submit = false, $as_array = false)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        
        $info = array();
        
        $exercises = ilExSubmission::findUserFiles($a_user_id, $a_obj_id);
        // #0022794
        if (!$exercises) {
            $exercises = ilExSubmission::findUserFiles($a_user_id, $a_obj_id . ".sec");
        }
        if ($exercises) {
            foreach ($exercises as $exercise) {
                // #9988
                $active_ref = false;
                foreach (ilObject::_getAllReferences($exercise["obj_id"]) as $ref_id) {
                    if (!$tree->isSaved($ref_id)) {
                        $active_ref = true;
                        break;
                    }
                }
                if ($active_ref) {
                    $part = self::getExerciseInfo($a_user_id, $exercise["ass_id"], $a_add_submit, $as_array);
                    if ($part) {
                        $info[] = $part;
                    }
                }
            }
            if (sizeof($info) && !$as_array) {
                return implode("<br />", $info);
            }
        }
        if ($as_array) {
            return $info;
        }
    }
    
    protected static function getExerciseInfo($a_user_id, $a_assignment_id, $a_add_submit = false, $as_array = false)
    {
        global $DIC;

        $ui = $DIC->ui();

        $links = [];
        $buttons = [];
        $elements = [];

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        include_once "Modules/Exercise/classes/class.ilExAssignment.php";
        $ass = new ilExAssignment($a_assignment_id);
        $exercise_id = $ass->getExerciseId();
        if (!$exercise_id) {
            return;
        }
        
        // is the assignment still open?
        $times_up = $ass->afterDeadlineStrict();
        
        // exercise goto
        include_once "./Services/Link/classes/class.ilLink.php";
        $exc_ref_id = array_shift(ilObject::_getAllReferences($exercise_id));
        $exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");

        $info_arr["ass_title"] = $ass->getTitle();
        $text = sprintf(
            $lng->txt("prtf_exercise_info"),
            $ass->getTitle(),
            ilObject::_lookupTitle($exercise_id)
        );
        $links[] = $ui->factory()->link()->standard(ilObject::_lookupTitle($exercise_id), $exc_link);
        $info_arr["exc_title"] = ilObject::_lookupTitle($exercise_id);
        
        // submit button
        if ($a_add_submit && !$times_up && !$as_array) {
            $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $a_assignment_id);
            $submit_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "finalize");
            $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");
            
            $buttons[] = $ui->factory()->button()->primary($lng->txt("prtf_finalize_portfolio"), $submit_link);
        }
        
        // submitted files
        include_once "Modules/Exercise/classes/class.ilExSubmission.php";
        $submission = new ilExSubmission($ass, $a_user_id);
        $info_arr["submitted"] = false;
        if ($submission->hasSubmitted()) {
            // #16888
            $submitted = $submission->getSelectedObject();

            if (!$as_array) {
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $a_assignment_id);
                $dl_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "downloadExcSubFile");
                $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");

                $rel = ilDatePresentation::useRelativeDates();
                ilDatePresentation::setUseRelativeDates(false);

                $text .= "<p>" . sprintf(
                    $lng->txt("prtf_exercise_submitted_info"),
                    ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
                    ""
                ) . "</p>";
                $buttons[] = $ui->factory()->button()->standard($lng->txt("prtf_download_submission"), $dl_link);
            }
            
            ilDatePresentation::setUseRelativeDates($rel);
            $info_arr["submitted_date"] = ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME));
            $info_arr["submitted"] = true;
            if ($submitted["ts"] == "") {
                $info_arr["submitted"] = false;
            }
        }

        // work instructions incl. files
        
        $tooltip = "";

        $inst = $ass->getInstruction();
        if ($inst) {
            $tooltip .= nl2br($inst);
        }

        $ass_files = $ass->getFiles();
        if (!$as_array) {
            if (count($ass_files) > 0) {
                if ($tooltip) {
                    $tooltip .= "<br /><br />";
                }

                $items = [];

                foreach ($ass_files as $file) {
                    $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", $a_assignment_id);
                    $ilCtrl->setParameterByClass("ilportfolioexercisegui", "file", urlencode($file["name"]));
                    $dl_link = $ilCtrl->getLinkTargetByClass("ilportfolioexercisegui", "downloadExcAssFile");
                    $ilCtrl->setParameterByClass("ilportfolioexercisegui", "file", "");
                    $ilCtrl->setParameterByClass("ilportfolioexercisegui", "ass", "");

                    $items[] = $ui->renderer()->render($ui->factory()->button()->shy($file["name"], $dl_link));
                }
                $list = $ui->factory()->listing()->unordered($items);
                $tooltip .= $ui->renderer()->render($list);
            }
        }
        
        if ($tooltip) {
            $modal = $ui->factory()->modal()->roundtrip($lng->txt("exc_instruction"), $ui->factory()->legacy($tooltip))
                ->withCancelButtonLabel("close");
            $elements[] = $modal;
            $buttons[] = $ui->factory()->button()->standard($lng->txt("exc_instruction"), '#')
                ->withOnClick($modal->getShowSignal());
        }

        if ($as_array) {
            return $info_arr;
        }

        $elements[] = $ui->factory()->messageBox()->info($text)
            ->withLinks($links)
            ->withButtons($buttons);

        return $ui->renderer()->render($elements);
    }
    
    public function downloadExcAssFile()
    {
        if ($this->file) {
            include_once "Modules/Exercise/classes/class.ilExAssignment.php";
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
    
    public function downloadExcSubFile()
    {
        $ass = new ilExAssignment($this->ass_id);
        $submission = new ilExSubmission($ass, $this->user_id);
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
     * Finalize and submit portfolio to exercise
     */
    protected function finalize()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once "Modules/Exercise/classes/class.ilExSubmissionBaseGUI.php";
        include_once "Modules/Exercise/classes/class.ilExSubmissionObjectGUI.php";
        $exc_gui = ilExSubmissionObjectGUI::initGUIForSubmit($this->ass_id);
        $exc_gui->submitPortfolio($this->obj_id);
        
        ilUtil::sendSuccess($lng->txt("prtf_finalized"), true);
        $ilCtrl->returnToParent($this);
    }
}
