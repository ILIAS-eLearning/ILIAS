<?php


abstract class ilExSubmissionBaseGUI
{
	protected $exercise; // [ilObjExercise]
	protected $submission; // [ilExSubmission]
	protected $assignment; // [ilExAssignment]
	
	public function __construct(ilObjExercise $a_exercise, ilExSubmission $a_submission)
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		$this->exercise = $a_exercise;
		$this->submission = $a_submission;
		$this->assignment = $a_submission->getAssignment();
		
		// :TODO:
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->tpl = $tpl;		
	}
	
	abstract public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission);
	
	protected function handleTabs()
	{				
		$this->tabs_gui->clearTargets();		
			$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
				$this->ctrl->getLinkTarget($this, "returnToParent"));	
		
		$this->tabs_gui->addTab("submission", $this->lng->txt("exc_submission"), 
			$this->ctrl->getLinkTarget($this, ""));
		$this->tabs_gui->activateTab("submission");
					
		if($this->assignment->hasTeam())
		{
			include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";
			ilExSubmissionTeamGUI::handleTabs();
		}		
	}
	
	function returnToParentObject()
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
			$this->assignment->getId(),
			$this->submission->getUserIds(),
			$has_submitted);
		
		if($has_submitted &&
			!$a_no_notifications)
		{
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
		$this->handleNewUpload(true);
	}
}
