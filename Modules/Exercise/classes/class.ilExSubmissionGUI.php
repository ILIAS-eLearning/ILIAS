<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExSubmissionBaseGUI.php";
include_once "Modules/Exercise/classes/class.ilExSubmission.php";

/**
* Class ilExSubmissionGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @ilCtrl_Calls ilExSubmissionGUI: ilExSubmissionTeamGUI, ilExSubmissionFileGUI
* @ilCtrl_Calls ilExSubmissionGUI: ilExSubmissionTextGUI, ilExSubmissionObjectGUI
* @ilCtrl_Calls ilExSubmissionGUI: ilExPeerReviewGUI
* @ingroup ModulesExercise
*/
class ilExSubmissionGUI
{
    const MODE_OVERVIEW_CONTENT = 1;

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
     * @var ilObjUser
     */
    protected $user;

    protected $exercise; // [ilObjExercise]
    protected $submission; // [ilExSubmission]
    protected $assignment; // [ilExAssignment]

    /**
     * @var ilExAssignmentTypesGUI
     */
    protected $type_guis;
    
    /**
     * Constructor
     *
     * @param ilObjExercise $a_exercise
     * @param ilExAssignment $a_ass
     * @param int $a_user_id
     * @return object
     */
    public function __construct(ilObjExercise $a_exercise, ilExAssignment $a_ass, $a_user_id = null)
    {
        global $DIC;

        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilUser = $DIC->user();
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        
        $this->assignment = $a_ass;
        $this->exercise = $a_exercise;
        $this->user_id = $a_user_id;

        include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssignmentTypesGUI.php");
        $this->type_guis = ilExAssignmentTypesGUI::getInstance();

        // #12337
        if (!$this->exercise->members_obj->isAssigned($a_user_id)) {
            $this->exercise->members_obj->assignMember($a_user_id);
        }
                        
        // public submissions ???
        $public_submissions = false;
        if ($this->exercise->getShowSubmissions() &&
            $this->exercise->getTimestamp() - time() <= 0) { // ???
            $public_submissions = true;
        }
        $this->submission = new ilExSubmission($a_ass, $a_user_id, null, false, $public_submissions);
        
        // :TODO:
        $this->ctrl = $ilCtrl;
        $this->tabs_gui = $ilTabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }
    
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("listPublicSubmissions");
        
        switch ($class) {
            case "ilexsubmissionteamgui":
                // team gui has no base gui - see we have to handle tabs here
                
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "returnToParent")
                );

                // forward to type gui
                if ($this->submission->getSubmissionType() != ilExSubmission::TYPE_REPO_OBJECT) {
                    $this->tabs_gui->addTab(
                        "submission",
                        $this->lng->txt("exc_submission"),
                        $this->ctrl->getLinkTargetByClass("ilexsubmission" . $this->submission->getSubmissionType() . "gui", "")
                    );
                }

                include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";
                $gui = new ilExSubmissionTeamGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;
            
            case "ilexsubmissiontextgui":
                include_once "Modules/Exercise/classes/class.ilExSubmissionTextGUI.php";
                $gui = new ilExSubmissionTextGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;
            
            case "ilexsubmissionfilegui":
                include_once "Modules/Exercise/classes/class.ilExSubmissionFileGUI.php";
                $gui = new ilExSubmissionFileGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;
            
            case "ilexsubmissionobjectgui":
                include_once "Modules/Exercise/classes/class.ilExSubmissionObjectGUI.php";
                $gui = new ilExSubmissionObjectGUI($this->exercise, $this->submission);
                $ilCtrl->forwardCommand($gui);
                break;
            
            case "ilexpeerreviewgui":
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "returnToParent")
                );
        
                include_once("./Modules/Exercise/classes/class.ilExPeerReviewGUI.php");
                $peer_gui = new ilExPeerReviewGUI($this->assignment, $this->submission);
                $this->ctrl->forwardCommand($peer_gui);
                break;
                
            default:


                // forward to type gui
                if ($this->type_guis->isExAssTypeGUIClass($class)) {
                    $type_gui = $this->type_guis->getByClassName($class);
                    $type_gui->setSubmission($this->submission);
                    $type_gui->setExercise($this->exercise);
                    return $ilCtrl->forwardCommand($type_gui);
                }

                $this->{$cmd . "Object"}();
                break;
        }
    }

    /**
     * @param ilInfoScreenGUI $a_info
     * @param ilExSubmission $a_submission
     * @param ilObjExercise $a_exc
     * @return string
     * @throws ilCtrlException
     */
    public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission, ilObjExercise $a_exc)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        
        if (!$a_submission->canView()) {
            return;
        }
            
        $ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", $a_submission->getAssignment()->getId());
            
        if ($a_submission->getAssignment()->hasTeam()) {
            include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";
            ilExSubmissionTeamGUI::getOverviewContent($a_info, $a_submission);
        }
        
        $submission_type = $a_submission->getSubmissionType();
        // old handling -> forward to submission type gui class
        // @todo migrate everything to new concept
        if ($submission_type != ilExSubmission::TYPE_REPO_OBJECT) {
            $class = "ilExSubmission" . $submission_type . "GUI";
            include_once "Modules/Exercise/classes/class." . $class . ".php";
            $class::getOverviewContent($a_info, $a_submission);
        } else { // new: get HTML from assignemt type gui class
            include_once("./Modules/Exercise/classes/class.ilExSubmissionGUI.php");
            $sub_gui = new ilExSubmissionGUI($a_exc, $a_submission->getAssignment());
            $ilCtrl->getHTML($sub_gui, array(
                "mode" => self::MODE_OVERVIEW_CONTENT,
                "info" => $a_info,
                "submission" => $a_submission
            ));
        }
            
        $ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", "");
    }

    /**
     * Get HTML
     *
     * @param
     * @return
     */
    public function getHTML($par)
    {
        switch ($par["mode"]) {
            // get overview content from ass type gui
            case self::MODE_OVERVIEW_CONTENT:
                $type_gui = $this->type_guis->getById($par["submission"]->getAssignment()->getType());
                return $type_gui->getOverviewContent($par["info"], $par["submission"]);
                break;
        }
    }

    
    /**
     * List all submissions
     */
    public function listPublicSubmissionsObject()
    {
        $ilTabs = $this->tabs_gui;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (!$this->exercise->getShowSubmissions()) {
            $this->returnToParentObject();
        }
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "returnToParent")
        );
        
        if ($this->assignment->getType() != ilExAssignment::TYPE_TEXT) {
            include_once("./Modules/Exercise/classes/class.ilPublicSubmissionsTableGUI.php");
            $tab = new ilPublicSubmissionsTableGUI($this, "listPublicSubmissions", $this->assignment);
            $this->tpl->setContent($tab->getHTML());
        } else {
            // #13271
            include_once "Modules/Exercise/classes/class.ilExAssignmentListTextTableGUI.php";
            $tbl = new ilExAssignmentListTextTableGUI($this, "listPublicSubmissions", $this->assignment, false, true);
            $this->tpl->setContent($tbl->getHTML());
        }
    }
    
    /**
     * Download feedback file
     */
    public function downloadFeedbackFileObject()
    {
        $ilUser = $this->user;
        
        $file = $_REQUEST["file"];

        if (!isset($file)) {
            ilUtil::sendFailure($this->lng->txt("exc_select_one_file"), true);
            $this->ctrl->redirect($this, "view");
        }
        
        // check, whether file belongs to assignment
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        $storage = new ilFSStorageExercise($this->exercise->getId(), $this->assignment->getId());
        $files = $storage->getFeedbackFiles($this->submission->getFeedbackId());
        $file_exist = false;
        foreach ($files as $fb_file) {
            if ($fb_file == $file) {
                $file_exist = true;
                break;
            }
        }
        if (!$file_exist) {
            echo "FILE DOES NOT EXIST";
            exit;
        }
        
        // check whether assignment has already started
        if (!$this->assignment->notStartedYet()) {
            // deliver file
            $p = $storage->getFeedbackFilePath($this->submission->getFeedbackId(), $file);
            ilUtil::deliverFile($p, $file);
        }
    
        return true;
    }
    
    public function downloadGlobalFeedbackFileObject()
    {
        $ilCtrl = $this->ctrl;

        include_once("./Modules/Exercise/classes/class.ilExcAssMemberState.php");
        $state = ilExcAssMemberState::getInstanceByIds($this->assignment->getId(), $this->user_id);
        
        // fix bug 28466, this code should be streamlined with the if above and
        // the presentation of the download link in the ilExAssignmentGUI->addSubmission
        if (!$state->isGlobalFeedbackFileAccessible($this->submission)) {
            $ilCtrl->redirect($this, "returnToParent");
        }

        // this is due to temporary bug in handleGlobalFeedbackFileUpload that missed the last "/"
        $file = (is_file($this->assignment->getGlobalFeedbackFilePath()))
            ? $this->assignment->getGlobalFeedbackFilePath()
            : $this->assignment->getGlobalFeedbackFileStoragePath() . $this->assignment->getFeedbackFile();

        ilUtil::deliverFile($file, $this->assignment->getFeedbackFile());
    }
    
    /**
     * Download assignment file
     */
    public function downloadFileObject()
    {
        $file = $_REQUEST["file"];

        if (!isset($file)) {
            ilUtil::sendFailure($this->lng->txt("exc_select_one_file"), true);
            $this->ctrl->redirect($this, "view");
        }
        
        // check whether assignment as already started
        $state = ilExcAssMemberState::getInstanceByIds($this->assignment->getId(), $this->user_id);
        if ($state->areInstructionsVisible()) {
            // check, whether file belongs to assignment
            $files = $this->assignment->getFiles();
            $file_exist = false;
            foreach ($files as $lfile) {
                if ($lfile["name"] == $file) {
                    // deliver file
                    ilUtil::deliverFile($lfile["fullpath"], $file);
                    exit();
                }
            }
            if (!$file_exist) {
                echo "FILE DOES NOT EXIST";
                exit;
            }
        }
        
        return true;
    }
    
    public function returnToParentObject()
    {
        $this->ctrl->returnToParent($this);
    }
}
