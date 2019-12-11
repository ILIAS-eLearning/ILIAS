<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise submission base gui
 *
 * This is an abstract base class for all types of submissions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
abstract class ilExSubmissionBaseGUI
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

    protected $exercise; // [ilObjExercise]
    protected $submission; // [ilExSubmission]
    protected $assignment; // [ilExAssignment]

    /**
     * @var ilExAssignmentTypesGUI
     */
    protected $type_guis;
    
    public function __construct(ilObjExercise $a_exercise, ilExSubmission $a_submission)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilTabs = $DIC->tabs();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        
        $this->exercise = $a_exercise;
        $this->submission = $a_submission;
        $this->assignment = $a_submission->getAssignment();
        
        // :TODO:
        $this->ctrl = $ilCtrl;
        $this->tabs_gui = $ilTabs;
        $this->lng = $lng;
        $this->tpl = $tpl;

        include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssignmentTypesGUI.php");
        $this->type_guis = ilExAssignmentTypesGUI::getInstance();
    }
    
    abstract public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission);
    
    protected function handleTabs()
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "returnToParent")
        );
        
        $this->tabs_gui->addTab(
            "submission",
            $this->lng->txt("exc_submission"),
            $this->ctrl->getLinkTarget($this, "")
        );
        $this->tabs_gui->activateTab("submission");
                    
        if ($this->assignment->hasTeam()) {
            include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";
            ilExSubmissionTeamGUI::handleTabs();
        }
    }
    
    public function returnToParentObject()
    {
        $this->ctrl->returnToParent($this);
    }
    
    
    //
    // RETURNED/EXERCISE STATUS
    //
    
    protected function handleNewUpload($a_no_notifications = false)
    {
        $has_submitted = $this->submission->hasSubmitted();
        
        $this->exercise->processExerciseStatus(
            $this->assignment,
            $this->submission->getUserIds(),
            $has_submitted,
            $this->submission->validatePeerReviews()
        );
        
        if ($has_submitted &&
            !$a_no_notifications) {
            include_once "./Services/Notification/classes/class.ilNotification.php";
            $users = ilNotification::getNotificationsForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $this->exercise->getId());

            include_once "./Modules/Exercise/classes/class.ilExerciseMailNotification.php";
            $not = new ilExerciseMailNotification();
            $not->setType(ilExerciseMailNotification::TYPE_SUBMISSION_UPLOAD);
            $not->setAssignmentId($this->assignment->getId());
            $not->setRefId($this->exercise->getRefId());
            $not->setRecipients($users);
            $not->send();
        }
    }
    
    protected function handleRemovedUpload()
    {
        // #16532 - always send notifications
        $this->handleNewUpload();
    }
}
