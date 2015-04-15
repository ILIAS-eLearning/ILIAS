<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExSubmissionBaseGUI.php";

/**
* Class ilExSubmissionGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExSubmissionGUI: ilExSubmissionTeamGUI, ilExSubmissionFileGUI 
* @ilCtrl_Calls ilExSubmissionGUI: ilExSubmissionTextGUI, ilExSubmissionObjectGUI 
* @ingroup ModulesExercise
*/
class ilExSubmissionGUI
{
	protected $exercise_id; // [int]
	protected $exercise; // [ilObjExercise]
	protected $assignment; // [ilExAssignment]
	protected $participant_id; // [int]
	
	/**
	 * Constructor
	 * 
	 * @param ilObjExercise $a_exercise
	 * @param ilExAssignment $a_ass
	 * @param int $a_participant_id
	 * @return object
	 */
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
	
	public function executeCommand()
	{
		global $ilCtrl;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listPublicSubmissions");	
		
		switch($class)
		{					
			case "ilexsubmissionteamgui":		
				// team gui has no base gui - see we have to handle tabs here
				
				$this->tabs_gui->clearTargets();		
					$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
						$this->ctrl->getLinkTarget($this, "returnToParent"));	
		
				$this->tabs_gui->addTab("submission", $this->lng->txt("exc_submission"), 
					$this->ctrl->getLinkTargetByClass("ilexsubmission".$this->assignment->getSubmissionType()."gui", ""));
			
				include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";
				$gui = new ilExSubmissionTeamGUI($this->exercise, $this->assignment, $this->participant_id);
				$ilCtrl->forwardCommand($gui);
				break;
			
			case "ilexsubmissiontextgui":
				include_once "Modules/Exercise/classes/class.ilExSubmissionTextGUI.php";
				$gui = new ilExSubmissionTextGUI($this->exercise, $this->assignment, $this->participant_id);
				$ilCtrl->forwardCommand($gui);
				break;
			
			case "ilexsubmissionfilegui":
				include_once "Modules/Exercise/classes/class.ilExSubmissionFileGUI.php";
				$gui = new ilExSubmissionFileGUI($this->exercise, $this->assignment, $this->participant_id);
				$ilCtrl->forwardCommand($gui);
				break;
			
			case "ilexsubmissionobjectgui":
				include_once "Modules/Exercise/classes/class.ilExSubmissionObjectGUI.php";
				$gui = new ilExSubmissionObjectGUI($this->exercise, $this->assignment, $this->participant_id);
				$ilCtrl->forwardCommand($gui);
				break;
				
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}	
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{
		global $ilUser, $ilCtrl;
			
		$ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", $a_ass->getId());
			
		if($a_ass->hasTeam())
		{
			include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";			
			$missing_team = ilExSubmissionTeamGUI::getOverviewContent($a_info, $a_ass);
		}
						
		$delivered_files = ilExAssignment::getDeliveredFiles($a_ass->getExerciseId(), $a_ass->getId(), $ilUser->getId());
					
		/*
		 submission
		 - file based 
		 -- object based
		 --- blog
		 --- portfolio
		 - text based			 
		*/			
		
		$submission_type = $a_ass->getSubmissionType();
		$class = "ilExSubmission".$submission_type."GUI";		
		include_once "Modules/Exercise/classes/class.".$class.".php";			
		$class::getOverviewContent($a_info, $a_ass, $missing_team, $delivered_files);																	
			
		$ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", "");
	}
			
	
	/**
	 * List all submissions
	 */
	function listPublicSubmissionsObject()
	{		
		if(!$this->exercise->getShowSubmissions())
		{
			$this->ctrl->redirect($this, "view");
		}
		
		if($this->assignment->getType() != ilExAssignment::TYPE_TEXT)
		{		
			include_once("./Modules/Exercise/classes/class.ilPublicSubmissionsTableGUI.php");
			$tab = new ilPublicSubmissionsTableGUI($this, "listPublicSubmissions",
				$this->exercise, $this->assignment->getId());
			$this->tpl->setContent($tab->getHTML());
		}
		else
		{				
			// #13271
			include_once "Modules/Exercise/classes/class.ilExAssignmentListTextTableGUI.php";
			$tbl = new ilExAssignmentListTextTableGUI($this, "listPublicSubmissions", $this->assignment, false, true);		
			$this->tpl->setContent($tbl->getHTML());		
		}
	}
	
	/**
 	 * Download feedback file
 	 */
	function downloadFeedbackFileObject()
	{
		global $rbacsystem, $ilUser;
		
		$file = $_REQUEST["file"];

		if (!isset($file))
		{
			ilUtil::sendFailure($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		
		if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{			
			$feedback_id = "t".$this->assignment->getTeamId($this->participant_id);
		}
		else
		{
			$feedback_id = $ilUser->getId();
		}
		
		// check, whether file belongs to assignment
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->exercise_id, $this->assignment->getId());
		$files = $storage->getFeedbackFiles($feedback_id);
		$file_exist = false;	
		foreach($files as $fb_file)
		{
			if($fb_file == $file)
			{
				$file_exist = true;
				break;
			}
		}		
		if(!$file_exist)
		{
			echo "FILE DOES NOT EXIST";
			exit;
		}
		
		// check whether assignment has already started						
		if (!$this->assignment->notStartedYet())
		{
			// deliver file
			$p = $storage->getFeedbackFilePath($feedback_id, $file);
			ilUtil::deliverFile($p, $file);
		}
	
		return true;
	}
	
	public function downloadGlobalFeedbackFileObject()
	{
		global $ilCtrl, $ilUser;
		
		$needs_dl = ($this->assignment->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE);
		
		if(!$this->assignment || 
			!$this->assignment->getFeedbackFile() ||
			($needs_dl && !$this->assignment->getDeadline()) ||
			($needs_dl && $this->assignment->getDeadline() > time()) ||
			(!$needs_dl && !ilExAssignment::getLastSubmission($this->assignment->getId(), $ilUser->getId())))						
		{
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		ilUtil::deliverFile($this->assignment->getFeedbackFilePath(), $this->assignment->getFeedbackFile());
	}
	
	/**
 	 * Download assignment file
 	 */
	function downloadFileObject()
	{		
		$file = $_REQUEST["file"];

		if (!isset($file))
		{
			ilUtil::sendFailure($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		
		// check, whether file belongs to assignment
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$files = ilExAssignment::getFiles($this->exercise_id, $this->assignment->getId());
		$file_exist = false;
		foreach($files as $lfile)
		{
			if($lfile["name"] == $file)
			{
				$file_exist = true;
				break;
			}
		}
		if(!$file_exist)
		{
			echo "FILE DOES NOT EXIST";
			exit;
		}
		
		// check whether assignment as already started		
		if (!$this->assignment->notStartedYet())
		{
			// deliver file
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$storage = new ilFSStorageExercise($this->exercise_id, $this->assignment->getId());
			$p = $storage->getAssignmentFilePath($file);
			ilUtil::deliverFile($p, $file);
		}
	
		return true;
	}
	
	function returnToParentObject()
	{
		$this->ctrl->returnToParent($this);
	}
}
