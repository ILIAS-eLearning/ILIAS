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
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$this->assignment = $a_ass;
		$this->exercise = $a_exercise;		
		
		// #12337
		if (!$this->exercise->members_obj->isAssigned($a_user_id))
		{
			$this->exercise->members_obj->assignMember($a_user_id);				
		}		
						
		// public submissions ???
		$public_submissions = false;
		if ($this->exercise->getShowSubmissions() &&
			$this->exercise->getTimestamp() - time() <= 0) // ???
		{
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
		
		switch($class)
		{					
			case "ilexsubmissionteamgui":		
				// team gui has no base gui - see we have to handle tabs here
				
				$this->tabs_gui->clearTargets();		
				$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
					$this->ctrl->getLinkTarget($this, "returnToParent"));	
		
				$this->tabs_gui->addTab("submission", $this->lng->txt("exc_submission"), 
					$this->ctrl->getLinkTargetByClass("ilexsubmission".$this->submission->getSubmissionType()."gui", ""));
			
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
				$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
					$this->ctrl->getLinkTarget($this, "returnToParent"));	
		
				include_once("./Modules/Exercise/classes/class.ilExPeerReviewGUI.php");
				$peer_gui = new ilExPeerReviewGUI($this->assignment, $this->submission);
				$this->ctrl->forwardCommand($peer_gui);
				break;
				
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}	
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
	{
		global $DIC;

		$ilCtrl = $DIC->ctrl();
		
		if(!$a_submission->canView())
		{
			return;
		}
			
		$ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", $a_submission->getAssignment()->getId());
			
		if($a_submission->getAssignment()->hasTeam())
		{
			include_once "Modules/Exercise/classes/class.ilExSubmissionTeamGUI.php";			
			ilExSubmissionTeamGUI::getOverviewContent($a_info, $a_submission);
		}
		
		$submission_type = $a_submission->getSubmissionType();
		$class = "ilExSubmission".$submission_type."GUI";		
		include_once "Modules/Exercise/classes/class.".$class.".php";			
		$class::getOverviewContent($a_info, $a_submission);																	
			
		$ilCtrl->setParameterByClass("ilExSubmissionGUI", "ass_id", "");
	}
			
	
	/**
	 * List all submissions
	 */
	function listPublicSubmissionsObject()
	{				
		$ilTabs = $this->tabs_gui;
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		if(!$this->exercise->getShowSubmissions())
		{
			$this->returnToParentObject();
		}
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "returnToParent"));
		
		if($this->assignment->getType() != ilExAssignment::TYPE_TEXT)
		{		
			include_once("./Modules/Exercise/classes/class.ilPublicSubmissionsTableGUI.php");
			$tab = new ilPublicSubmissionsTableGUI($this, "listPublicSubmissions", $this->assignment);
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
		$ilUser = $this->user;
		
		$file = $_REQUEST["file"];

		if (!isset($file))
		{
			ilUtil::sendFailure($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		
		// check, whether file belongs to assignment
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->exercise->getId(), $this->assignment->getId());
		$files = $storage->getFeedbackFiles($this->submission->getFeedbackId());
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
			$p = $storage->getFeedbackFilePath($this->submission->getFeedbackId(), $file);
			ilUtil::deliverFile($p, $file);
		}
	
		return true;
	}
	
	public function downloadGlobalFeedbackFileObject()
	{
		$ilCtrl = $this->ctrl;
		
		$needs_dl = ($this->assignment->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE);
		
		if(!$this->assignment || 
			!$this->assignment->getFeedbackFile() ||
			($needs_dl && !$this->assignment->afterDeadlineStrict()) ||				
			(!$needs_dl && !$this->submission->hasSubmitted()))						
		{
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		ilUtil::deliverFile($this->assignment->getGlobalFeedbackFilePath(), $this->assignment->getFeedbackFile());
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
		
		// check whether assignment as already started		
		if (!$this->assignment->notStartedYet())
		{
			// check, whether file belongs to assignment
			$files = $this->assignment->getFiles();
			$file_exist = false;
			foreach($files as $lfile)
			{
				if($lfile["name"] == $file)
				{
					// deliver file
					ilUtil::deliverFile($lfile["fullpath"], $file);
					exit();
				}
			}
			if(!$file_exist)
			{
				echo "FILE DOES NOT EXIST";
				exit;
			}
		}
		
		return true;
	}
	
	function returnToParentObject()
	{
		$this->ctrl->returnToParent($this);
	}
}
