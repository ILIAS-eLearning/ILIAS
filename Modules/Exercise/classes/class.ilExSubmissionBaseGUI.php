<?php


abstract class ilExSubmissionBaseGUI
{
	protected $exercise_id; // [int]
	protected $exercise; // [ilObjExercise]
	protected $assignment; // [ilExAssignment]
	protected $participant_id; // [int]
	
	public function __construct(ilObjExercise $a_exercise, ilExAssignment $a_ass, $a_participant_id = null)
	{
		global $ilCtrl, $ilTabs, $lng, $tpl, $ilUser;
		
		if(!$a_participant_id)
		{
			$a_participant_id = $ilUser->getId();
		}
		
		$this->exercise_id = $a_ass->getExerciseId();
		$this->exercise = $a_exercise;
		$this->assignment = $a_ass;
		$this->participant_id = $a_participant_id;				
		
		// :TODO:
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->tpl = $tpl;		
	}
	
	abstract public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_missing_team, array $a_files);
	
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
	
	/**
	 * Send submission notifications
	 * @param	int	$assignment_id
	 */
    protected function sendNotifications($assignment_id)
	{
		include_once "./Services/Notification/classes/class.ilNotification.php";
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $this->exercise_id);

		include_once "./Modules/Exercise/classes/class.ilExerciseMailNotification.php";
		$not = new ilExerciseMailNotification();
		$not->setType(ilExerciseMailNotification::TYPE_SUBMISSION_UPLOAD);
		$not->setAssignmentId($assignment_id);
		$not->setRefId($this->exercise->getRefId());
		$not->setRecipients($users);
		$not->send();
	}
}
