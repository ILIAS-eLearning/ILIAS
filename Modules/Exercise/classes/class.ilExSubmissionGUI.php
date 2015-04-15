<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilExSubmissionGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExSubmissionGUI: ilRepositorySearchGUI
* @ingroup ModulesExercise
*/
class ilExSubmissionGUI
{
	protected $exercise_id; // [int]
	protected $assignment; // [ilExAssignment]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_exercise_id
	 * @param ilExAssignment $a_ass
	 * @return object
	 */
	public function __construct($a_exercise_id, ilExAssignment $a_ass, $a_participant_id = null)
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		$this->exercise_id = $a_exercise_id;
		$this->assignment = $a_ass;
		
		// :TODO:
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->tpl = $tpl;
	}
	
	public function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listPublicSubmissions");		
		
		switch($class)
		{		
			case 'ilrepositorysearchgui':	
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();		
				$rep_search->setTitle($this->lng->txt("exc_team_member_add"));
				$rep_search->setCallback($this,'addTeamMemberActionObject');

				// Set tabs
				$this->initTeamSubmission("submissionScreenTeam");
				$this->ctrl->setReturn($this,'submissionScreenTeam');
				
				$this->ctrl->forwardCommand($rep_search);
				break;
							
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}	
	
	/**
	 * List all submissions
	 */
	function listPublicSubmissionsObject()
	{
		global $tpl, $ilTabs;
		
		if(!$this->exercise->getShowSubmissions())
		{
			$this->ctrl->redirect($this, "view");
		}
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("content");
		
		if($this->assignment->getType() != ilExAssignment::TYPE_TEXT)
		{		
			include_once("./Modules/Exercise/classes/class.ilPublicSubmissionsTableGUI.php");
			$tab = new ilPublicSubmissionsTableGUI($this, "listPublicSubmissions",
				$this->exercise, (int) $_GET["ass_id"]);
			$tpl->setContent($tab->getHTML());
		}
		else
		{				
			// #13271
			include_once "Modules/Exercise/classes/class.ilExAssignmentListTextTableGUI.php";
			$tbl = new ilExAssignmentListTextTableGUI($this, "listPublicSubmissions", $this->assignment, false, true);		
			$tpl->setContent($tbl->getHTML());		
		}
	}
	
	/**
 	 * Download assignment file
 	 */
	function downloadFileObject()
	{
		global $rbacsystem;
		
		$file = $_REQUEST["file"];

		if (!isset($file))
		{
			ilUtil::sendFailure($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		
		// check, whether file belongs to assignment
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$files = ilExAssignment::getFiles($this->exercise->getId(), (int) $_GET["ass_id"]);
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
		$ass = new ilExAssignment((int) $_GET["ass_id"]);
		$not_started_yet = false;
		if ($ass->getStartTime() > 0 && time() - $ass->getStartTime() <= 0)
		{
			$not_started_yet = true;
		}

		// deliver file
		if (!$not_started_yet)
		{
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$storage = new ilFSStorageExercise($this->exercise->getId(), (int) $_GET["ass_id"]);
			$p = $storage->getAssignmentFilePath($file);
			ilUtil::deliverFile($p, $file);
		}
	
		return true;
	}
	
	
	/**
	 * User downloads (own) submitted files
	 *
	 * @param
	 * @return
	 */
	function downloadObject()
	{
		global $ilUser, $ilCtrl;

		if (count($_REQUEST["delivered"]))
		{
			if(!is_array($_REQUEST["delivered"]))
			{
				$_REQUEST["delivered"] = array($_REQUEST["delivered"]);
			}
			ilExAssignment::downloadSelectedFiles($this->exercise->getId(), (int) $_GET["ass_id"],
				$ilUser->getId(), $_REQUEST["delivered"]);
			exit;
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_download"), true);
			$ilCtrl->redirect($this, "submissionScreen");
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
		
		$ass = new ilExAssignment((int) $_GET["ass_id"]);
		
		if($ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{			
			$feedback_id = "t".$ass->getTeamId($ilUser->getId());
		}
		else
		{
			$feedback_id = $ilUser->getId();
		}
		
		// check, whether file belongs to assignment
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->exercise->getId(), (int) $_GET["ass_id"]);
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
		
		// check whether assignment as already started		
		$not_started_yet = false;
		if ($ass->getStartTime() > 0 && time() - $ass->getStartTime() <= 0)
		{
			$not_started_yet = true;
		}

		// deliver file
		if (!$not_started_yet)
		{
			$p = $storage->getFeedbackFilePath($feedback_id, $file);
			ilUtil::deliverFile($p, $file);
		}
	
		return true;
	}
				
	public function downloadGlobalFeedbackFileObject()
	{
		global $ilCtrl, $ilUser;
		
		$needs_dl = ($this->assignment->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE);
		
		if(!$this->ass || 
			!$this->assignment->getFeedbackFile() ||
			($needs_dl && !$this->assignment->getDeadline()) ||
			($needs_dl && $this->assignment->getDeadline() > time()) ||
			(!$needs_dl && !ilExAssignment::getLastSubmission($this->assignment->getId(), $ilUser->getId())))						
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		ilUtil::deliverFile($this->assignment->getFeedbackFilePath(), $this->assignment->getFeedbackFile());
	}
	
	
	
	/**
	 * Confirm deletion of delivered files
	 */
	function confirmDeleteDeliveredObject()
	{
		global $ilCtrl, $tpl, $lng, $ilUser;

		$this->checkPermission("read");
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
			$this->ctrl->getLinkTarget($this, "submissionScreen"));
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
		{
			ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			$ilCtrl->redirect($this, "submissionScreen");
		}

		if (!is_array($_POST["delivered"]) || count($_POST["delivered"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "submissionScreen");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("info_delete_sure"));
			$cgui->setCancel($lng->txt("cancel"), "submissionScreen");
			$cgui->setConfirm($lng->txt("delete"), "deleteDelivered");
			
			$files = ilExAssignment::getDeliveredFiles($this->object->getId(), (int) $_GET["ass_id"],
				$ilUser->getId());
//var_dump($files);
			foreach ($_POST["delivered"] as $i)
			{
				reset ($files);
				$title = "";
				foreach ($files as $f)
				{
					if ($f["returned_id"] == $i)
					{
						$title = $f["filetitle"];
					}
				}
				$cgui->addItem("delivered[]", $i, $title);
			}

			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete file(s) submitted by user
	 *
	 * @param
	 * @return
	 */
	function deleteDeliveredObject()
	{
		global $ilUser, $ilCtrl;
		
		$this->checkPermission("read");
		
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
		{
			ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			$ilCtrl->redirect($this, "submissionScreen");
		}
		
		if (count($_POST["delivered"]) && (mktime() < $this->ass->getDeadline() ||
			$this->ass->getDeadline() == 0))
		{
			$this->object->deleteDeliveredFiles($this->object->getId(), (int) $_GET["ass_id"],
				$_POST["delivered"], $ilUser->id);
				
			$this->object->handleSubmission((int)$_GET['ass_id']);
			
			ilUtil::sendSuccess($this->lng->txt("exc_submitted_files_deleted"), true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_delete"), true);
		}
		$ilCtrl->redirect($this, "submissionScreen");
	}
  	
	
  
 	
  
 	

	
	
	
	
	
	
	/**
	* Displays a form which allows members to deliver their solutions
	*
	* @access public
	*/
	function submissionScreenObject()
	{
		global $ilToolbar;

		$this->initTeamSubmission("returnToParent", false);
		$this->tabs_gui->activateTab("submissions");
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		else
		{
			$ilToolbar->addButton($this->lng->txt("file_add"), 
				$this->ctrl->getLinkTarget($this, "uploadForm"));
			
			$ilToolbar->addButton($this->lng->txt("header_zip"), 
				$this->ctrl->getLinkTarget($this, "uploadZipForm"));
		}

		include_once("./Modules/Exercise/classes/class.ilExcDeliveredFilesTableGUI.php");
		$tab = new ilExcDeliveredFilesTableGUI($this, "submissionScreen", $this->assignment);
		$this->tpl->setContent($tab->getHTML());
	}
	
	
	function returnToParentObject()
	{
		$this->ctrl->returnToParent($this);
	}
	
	// 
	// FILE SUBMISSION
	// 
	
	/**
	 * Display form for single file upload 
	 */
	public function uploadFormObject()
	{		
		if (mktime() < $this->assignment->getDeadline() || ($this->assignment->getDeadline() == 0))
		{
			$this->tabs_gui->clearTargets();
			$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
				$this->ctrl->getLinkTarget($this, "submissionScreen"));		
			
			$this->initUploadForm();
			$this->tpl->setContent($this->form->getHTML());
		}
		else
		{
			$this->ctrl->redirect($this, "submissionScreen");
		}
	}
	
	/**
	 * Display form for zip file upload 
	 */
	public function uploadZipFormObject()
	{		
		if (mktime() < $this->assignment->getDeadline() || ($this->assignment->getDeadline() == 0))
		{
			$this->tabs_gui->clearTargets();
			$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
				$this->ctrl->getLinkTarget($this, "submissionScreen"));		
			
			$this->initZipUploadForm();
			$this->tpl->setContent($this->form->getHTML());
		}
		else
		{
			$this->ctrl->redirect($this, "submissionScreen");
		}
	}
 
	/**
	 * Init upload form form.
	 */
	public function initUploadForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// file input
		include_once("./Services/Form/classes/class.ilFileWizardInputGUI.php");
		$fi = new ilFileWizardInputGUI($lng->txt("file"), "deliver");
		$fi->setFilenames(array(0 => ''));
		//$fi->setInfo($lng->txt(""));
		$this->form->addItem($fi);
	
		$this->form->addCommandButton("deliverFile", $lng->txt("upload"));
		$this->form->addCommandButton("submissionScreen", $lng->txt("cancel"));
	                
		$this->form->setTitle($lng->txt("file_add"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Init upload form form.
	 */
	public function initZipUploadForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// desc
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("file"), "deliver");
		$fi->setSuffixes(array("zip"));
		$this->form->addItem($fi);
	
		$this->form->addCommandButton("deliverUnzip", $lng->txt("upload"));
		$this->form->addCommandButton("submissionScreen", $lng->txt("cancel"));
	                
		$this->form->setTitle($lng->txt("header_zip"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
 
 	/**
 	 * Upload files
 	 */
	function deliverFileObject()
	{
		global $ilUser, $lng, $ilCtrl;
		
		// #15322
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
			return;
		}

		$success = false;
		foreach ($_FILES["deliver"]["name"] as $k => $v)
		{
			$file = array(
				"name" => $_FILES["deliver"]["name"][$k], 
				"type" => $_FILES["deliver"]["type"][$k],
				"tmp_name" => $_FILES["deliver"]["tmp_name"][$k],
				"error" => $_FILES["deliver"]["error"][$k],
				"size" => $_FILES["deliver"]["size"][$k],
				);
			if(!$this->exercise->deliverFile($file, (int) $_GET["ass_id"], $ilUser->id))
			{
				ilUtil::sendFailure($this->lng->txt("exc_upload_error"), true);
			}
			else
			{
				$success = true;
			}
		}

		if($success)
		{
			$this->sendNotifications((int)$_GET["ass_id"]);
			$this->exercise->handleSubmission((int)$_GET['ass_id']);
		}
		$ilCtrl->redirect($this, "submissionScreen");
	}

	/**
	 * Upload zip file
	 */
	function deliverUnzipObject()
	{
		global $ilCtrl;
	
		// #15322
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
			return;
		}

		if (preg_match("/zip/",$_FILES["deliver"]["type"]) == 1)
		{
			if($this->exercise->processUploadedFile($_FILES["deliver"]["tmp_name"], "deliverFile", false,
				(int) $_GET["ass_id"]))
			{
				$this->sendNotifications((int)$_GET["ass_id"]);
				$this->exercise->handleSubmission((int)$_GET['ass_id']);
			}
		}

		$ilCtrl->redirect($this, "submissionScreen");
	}
	
	function uploadZipObject()
	{
		global $rbacsystem;
		
		$this->checkPermission("write");

		if(!$this->object->addUploadedFile($_FILES["zipfile"], true))
		{
			ilUtil::sendFailure($this->lng->txt("exc_upload_error"),true);
		}
		$this->ctrl->redirect($this, "edit");
	
	}

	function uploadFileObject()
	{
		global $rbacsystem;

		$this->checkPermission("write");

		if(!$this->object->addUploadedFile($_FILES["file"]))
		{
			ilUtil::sendFailure($this->lng->txt("exc_upload_error"),true);
		}
		$this->ctrl->redirect($this, "edit");
	}
	
	/**
	 * Download submitted files of user.
	 */
	function downloadReturnedObject()
	{
		global $ilAccess;
		
		$peer_review_mask_filename = false;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			if ($ilAccess->checkAccess("read", "", $this->object->getRefId()) &&
				$this->object->getShowSubmissions() &&
				$this->object->getTimestamp() - time() <= 0)
			{
				// ok: read access + public submissions
			}
			else
			{
				// ok: read access + peer review
				$ass = new ilExAssignment((int) $_GET["ass_id"]);
				if(!($ilAccess->checkAccess("read", "", $this->object->getRefId()) && 
					$ass->hasPeerReviewAccess((int) $_GET["member_id"])))
				{
					$this->checkPermission("write");
				}
				else
				{
					$peer_review_mask_filename = true;
				}
			}
		}
		
		if (!ilExAssignment::deliverReturnedFiles(
			$this->object->getId(), (int) $_GET["ass_id"], (int) $_GET["member_id"], 
				false, $peer_review_mask_filename))
		{
			$this->ctrl->redirect($this, "members");
		}
		exit;
	}

	/**
	* Download newly submitted files of user.
	*/
	function downloadNewReturnedObject()
	{
		global $ilAccess;
		
		$peer_review_mask_filename = false;
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			// ok: read access + peer review
			$ass = new ilExAssignment((int) $_GET["ass_id"]);
			if(!($ilAccess->checkAccess("read", "", $this->object->getRefId()) && 
				$ass->hasPeerReviewAccess((int) $_GET["member_id"])))
			{
				$this->checkPermission("write");
			}
			else
			{
				$peer_review_mask_filename = true;
			}
		}
		
		if (!ilExAssignment::deliverReturnedFiles(
			$this->object->getId(), (int) $_GET["ass_id"], (int) $_GET["member_id"], 
				true, $peer_review_mask_filename))
		{
			$this->ctrl->redirect($this, "members");
		}
		exit;
	}
	
	

	/**
	 * Send submission notifications
	 * @param	int	$assignment_id
	 */
    protected function sendNotifications($assignment_id)
	{
		include_once "./Services/Notification/classes/class.ilNotification.php";
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $this->object->getId());

		include_once "./Modules/Exercise/classes/class.ilExerciseMailNotification.php";
		$not = new ilExerciseMailNotification();
		$not->setType(ilExerciseMailNotification::TYPE_SUBMISSION_UPLOAD);
		$not->setAssignmentId($assignment_id);
		$not->setRefId($this->ref_id);
		$not->setRecipients($users);
		$not->send();
	}
	
	
	
	
	
	
	
	//
	// TEAM
	//
	
	protected function initTeamSubmission($a_back_cmd, $a_mandatory_team = true)
	{
		global $ilUser, $ilHelp;
		
		if($a_mandatory_team && $this->assignment->getType() != ilExAssignment::TYPE_UPLOAD_TEAM)
		{		
			$this->ctrl->redirect($this, "submissionScreen");
		}
			
		$this->tabs_gui->clearTargets();
		$ilHelp->setScreenIdComponent("exc");
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
			$this->ctrl->getLinkTarget($this, $a_back_cmd));
		
		if($this->assignment->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{			
			$this->tabs_gui->addTab("submissions", $this->lng->txt("files"), 
				$this->ctrl->getLinkTarget($this, "submissionScreen"));
		
			$this->tabs_gui->addTab("team", $this->lng->txt("exc_team"), 
				$this->ctrl->getLinkTarget($this, "submissionScreenTeam"));

			$this->tabs_gui->addTab("log", $this->lng->txt("exc_team_log"), 
				$this->ctrl->getLinkTarget($this, "submissionScreenTeamLog"));
			
			$this->tabs_gui->activateTab("team");
			
			$team_id = $this->assignment->getTeamId($ilUser->getId());
			
			if(!$team_id)
			{
				$team_id = $this->assignment->getTeamId($ilUser->getId(), true);
				
				// #12337
				if (!$this->exercise->members_obj->isAssigned($ilUser->getId()))
				{
					$this->exercise->members_obj->assignMember($ilUser->getId());
				}				
			}
			
			return $team_id;
		}
		else
		{
			$ilHelp->setScreenId("submissions");
		}
	}
	
	/**
	* Displays a form which allows members to manage team uploads
	*
	* @access public
	*/
	function submissionScreenTeamObject()
	{
		global $ilToolbar;
		
		$team_id = $this->initTeamSubmission("showOverview");
						
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		// #13414
		$read_only = (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0));
				
		if ($read_only)
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		else
		{					
			$this->ctrl->setParameterByClass('ilRepositorySearchGUI', 'ctx', 1);
			$this->ctrl->setParameter($this, 'ctx', 1);
			
			// add member
			include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$ilToolbar,
				array(
					'auto_complete_name'	=> $this->lng->txt('user'),
					'submit_name'			=> $this->lng->txt('add'),
					'add_search'			=> true,
					'add_from_container'    => $this->exercise->getRefId()		
				)
			);
	 	}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamTableGUI.php";
		$tbl = new ilExAssignmentTeamTableGUI($this, "submissionScreenTeam",
			ilExAssignmentTeamTableGUI::MODE_EDIT, $team_id, $this->assignment, null, $read_only);
		
		$this->tpl->setContent($tbl->getHTML());				
	}
	
	public function addTeamMemberActionObject($a_user_ids = array())
	{		
		global $ilUser;
		
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return false;
		}
		
		$team_id = $this->assignment->getTeamId($ilUser->getId());
		$has_files = $this->assignment->getDeliveredFiles($this->exercise->getId(), 
			$this->assignment->getId(), 
			$ilUser->getId());
		$all_members = $this->assignment->getMembersOfAllTeams();
		$members = $this->assignment->getTeamMembers($team_id);
		
		foreach($a_user_ids as $user_id)
		{
			if(!in_array($user_id, $all_members))
			{
				$this->assignment->addTeamMember($team_id, $user_id, $this->ref_id);
				
				// #14277
				if (!$this->exercise->members_obj->isAssigned($user_id))
				{
					$this->exercise->members_obj->assignMember($user_id);
				}

				// see ilObjExercise::deliverFile()
				if($has_files)
				{					
					ilExAssignment::updateStatusReturnedForUser($this->assignment->getId(), $user_id, 1);
					ilExerciseMembers::_writeReturned($this->exercise->getId(), $user_id, 1);
				}

				// :TODO: log, notification
			}
			else if(!in_array($user_id, $members))
			{
				ilUtil::sendFailure($this->lng->txt("exc_members_already_assigned"), true);
			}
		}

		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "submissionScreenTeam");
	}
	
	public function confirmRemoveTeamMemberObject()
	{
		global $ilUser, $tpl;
		
		$ids = $_POST["id"];
		
		if(!sizeof($ids))
		{
			ilUtil::sendFailure($this->lng->txt("select_one"), true);
			$this->ctrl->redirect($this, "submissionScreenTeam");
		}
		
		$team_id = $this->assignment->getTeamId($ilUser->getId());
		$members = $this->assignment->getTeamMembers($team_id);
		
		$team_deleted = false;
		if(sizeof($members) <= sizeof($ids))
		{
			if(sizeof($members) == 1 && $members[0] == $ilUser->getId())
			{
				// direct team deletion - no confirmation
				return $this->removeTeamMemberObject();
			}						
			else
			{
				ilUtil::sendFailure($this->lng->txt("exc_team_at_least_one"), true);
				$this->ctrl->redirect($this, "submissionScreenTeam");
			}
		}
		
		// #11957
		
		$team_id = $this->initTeamSubmission("showOverview");
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("exc_team_member_remove_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "submissionScreenTeam");
		$cgui->setConfirm($this->lng->txt("remove"), "removeTeamMember");

		$files = ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), 
			$this->assignment->getId(), $ilUser->getId());
		
		include_once "Services/User/classes/class.ilUserUtil.php";
		
		foreach($ids as $id)
		{
			$details = array();
			foreach ($files as $file)
			{				
				if($file["owner_id"] == $id)
				{
					$details[] = $file["filetitle"];
				}							
			}
			$uname = ilUserUtil::getNamePresentation($id);
			if(sizeof($details))
			{
				$uname .= ": ".implode(", ", $details);
			}
			$cgui->addItem("id[]", $id, $uname);
		}

		$tpl->setContent($cgui->getHTML());		
	}
	
	public function removeTeamMemberObject()
	{
		global $ilUser;
		
		$ids = $_POST["id"];
		
		if(!sizeof($ids))
		{
			ilUtil::sendFailure($this->lng->txt("select_one"), true);
			$this->ctrl->redirect($this, "submissionScreenTeam");
		}
		
		$team_id = $this->assignment->getTeamId($ilUser->getId());
		$members = $this->assignment->getTeamMembers($team_id);
		
		$team_deleted = false;
		if(sizeof($members) <= sizeof($ids))
		{
			if(sizeof($members) == 1 && $members[0] == $ilUser->getId())
			{
				$team_deleted = true;
			}						
			else
			{
				ilUtil::sendFailure($this->lng->txt("exc_team_at_least_one"), true);
				$this->ctrl->redirect($this, "submissionScreenTeam");
			}
		}
		
		foreach($ids as $user_id)
		{
			$this->assignment->removeTeamMember($team_id, $user_id, $this->ref_id);		
			
			ilExAssignment::updateStatusReturnedForUser($this->assignment->getId(), $user_id, 0);
			ilExerciseMembers::_writeReturned($this->exercise->getId(), $user_id, 0);
			
			// :TODO: log, notification
		}
				
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		
		if(!$team_deleted)
		{
			$this->ctrl->redirect($this, "submissionScreenTeam");		
		}
		else
		{
			$this->ctrl->redirect($this, "showOverview");	
		}		
	}
	
	function submissionScreenTeamLogObject()
	{
		$team_id = $this->initTeamSubmission("showOverview");
		$this->tabs_gui->activateTab("log");
	
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamLogTableGUI.php";
		$tbl = new ilExAssignmentTeamLogTableGUI($this, "submissionScreenTeamLog",
			$team_id);
		
		$this->tpl->setContent($tbl->getHTML());						
	}
	
	function createSingleMemberTeamObject()
	{
		if(isset($_GET["lmem"]))
		{				
			$user_id = $_GET["lmem"];
			$cmd = "members";												
		}	
		else
		{
			$user_id = $_GET["lpart"];
			$cmd = "showParticipant";		
		}
		if($user_id)
		{
			$this->assignment->getTeamId($user_id, true);		
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
		$this->ctrl->redirect($this, $cmd);	
	}			
	
	function showTeamLogObject()
	{		
		$this->checkPermission("write");								
		$this->tabs_gui->activateTab("grades");	
						
		if(isset($_GET["lmem"]))
		{					
			$this->addSubmissionSubTabs("assignment");
			
			$this->tabs_gui->setBackTarget($this->lng->txt("back"),
				$this->ctrl->getLinkTarget($this, "members"));
		
			$team_id = ilExAssignment::getTeamIdByAssignment($this->assignment->getId(), (int)$_GET["lmem"]);
			
			$this->ctrl->saveParameter($this, "lmem");
		}
		else
		{
			$this->addSubmissionSubTabs("participant");
			
			$this->tabs_gui->setBackTarget($this->lng->txt("back"),
				$this->ctrl->getLinkTarget($this, "showParticipant"));
		
			$team_id = ilExAssignment::getTeamIdByAssignment($this->assignment->getId(), (int)$_GET["lpart"]);
			
			$this->ctrl->saveParameter($this, "lpart");
		}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamLogTableGUI.php";
		$tbl = new ilExAssignmentTeamLogTableGUI($this, "showTeamLog",
			$team_id);
		
		$this->tpl->setContent($tbl->getHTML());						
	}
		
	public function createTeamObject()
	{		
		global $ilCtrl, $ilUser, $ilTabs, $lng, $tpl;
		
		if($this->assignment->getDeadline() == 0 ||
			mktime() < $this->assignment->getDeadline())
		{			
			$options = ilExAssignment::getAdoptableTeamAssignments($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
			if(sizeof($options))
			{								
				$ilTabs->activateTab("content");
				$this->addContentSubTabs("content");
	
				include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
				$form = new ilPropertyFormGUI();		         
				$form->setTitle($lng->txt("exc_team_assignment_adopt_user"));
				$form->setFormAction($ilCtrl->getFormAction($this, "createAdoptedTeam"));


				$teams = new ilRadioGroupInputGUI($lng->txt("exc_assignment"), "ass_adpt");
				$teams->setValue(-1);

				$teams->addOption(new ilRadioOption($lng->txt("exc_team_assignment_adopt_none_user"), -1));
				
				$current_map = ilExAssignment::getAssignmentTeamMap($this->assignment->getId());

				include_once "Services/User/classes/class.ilUserUtil.php";
				foreach($options as $id => $item)
				{
					$members = array();
					$free = false;
					foreach($item["user_team"] as $user_id)
					{
						$members[$user_id] = ilUserUtil::getNamePresentation($user_id);
						
						if(array_key_exists($user_id, $current_map))
						{
							$members[$user_id] .= " (".$lng->txt("exc_team_assignment_adopt_already_assigned").")";
						}
						else
						{
							$free = true;
						}
					}
					asort($members);
					$members = implode("<br />", $members);
					$option = new ilRadioOption($item["title"], $id);
					$option->setInfo($members);
					if(!$free)
					{
						$option->setDisabled(true);
					}
					$teams->addOption($option);
				}

				$form->addItem($teams);

				$form->addCommandButton("createAdoptedTeam", $lng->txt("save"));
				$form->addCommandButton("showOverview", $lng->txt("cancel"));

				$tpl->setContent($form->getHTML());
				return;
			}			
			
			$this->assignment->getTeamId($ilUser->getId(), true);		
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);	
		}
		
		$ilCtrl->redirect($this, "showOverview");
	}
	
	public function createAdoptedTeamObject()
	{
		global $ilCtrl, $ilUser, $lng;
		
		if($this->assignment->getDeadline() == 0 ||
			mktime() < $this->assignment->getDeadline())
		{	
			$src_ass_id = (int)$_POST["ass_adpt"];
			if($src_ass_id > 0)
			{
				$this->assignment->adoptTeams($src_ass_id, $ilUser->getId(), $this->ref_id);						
			}
			else
			{
				$this->assignment->getTeamId($ilUser->getId(), true);		
			}
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
		
		$ilCtrl->redirect($this, "showOverview");
	}	
	
	
	/**
	* Add user as member
	*/
	public function addUserFromAutoCompleteObject()
	{		
		if(!strlen(trim($_POST['user_login'])))
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_search_string'));
			$this->membersObject();
			return false;
		}
		$users = explode(',', $_POST['user_login']);

		$user_ids = array();
		foreach($users as $user)
		{
			$user_id = ilObjUser::_lookupId($user);

			if(!$user_id)
			{
				ilUtil::sendFailure($this->lng->txt('user_not_known'));								
				return $this->submissionScreenTeamObject();				
			}
			
			$user_ids[] = $user_id;
		}
	
		return $this->addTeamMemberActionObject($user_ids);								
	}
	
	
	//
	// BLOG
	//
	
	protected function createBlogObject()
	{
		global $ilUser;
		
		// $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		$this->tabs_gui->setTabActive("content");
		$this->addContentSubTabs("content");
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_create_blog").": ".$this->assignment->getTitle());
		$tpl->setVariable("TREE", $this->renderWorkspaceExplorer("createBlog"));
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("CMD_SUBMIT", "saveBlog");
		$tpl->setVariable("CMD_CANCEL", "showOverview");
		
		ilUtil::sendInfo($this->lng->txt("exc_create_blog_select_info"));
					
		$this->tpl->setContent($tpl->get());
	}
	
	protected function selectBlogObject()
	{
		global $ilUser;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_select_blog").": ".$this->assignment->getTitle());
		$tpl->setVariable("TREE", $this->renderWorkspaceExplorer("selectBlog"));
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("CMD_SUBMIT", "setSelectedBlog");
		$tpl->setVariable("CMD_CANCEL", "showOverview");
		
		ilUtil::sendInfo($this->lng->txt("exc_select_blog_info"));
					
		$this->tpl->setContent($tpl->get());
	}
	
	protected function saveBlogObject()
	{
		global $ilUser;
		
		if(!$_POST["node"])
		{
			ilUtil::sendFailure($this->lng->txt("select_one"));
			return $this->createBlogObject();
		}
		
		$parent_node = $_POST["node"];
		
		include_once "Modules/Blog/classes/class.ilObjBlog.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
		
		$blog = new ilObjBlog();
		$blog->setTitle($this->exercise->getTitle()." - ".$this->assignment->getTitle());
		$blog->create();
		
		$tree = new ilWorkspaceTree($ilUser->getId());
		
		$node_id = $tree->insertObject($parent_node, $blog->getId());
		
		$access_handler = new ilWorkspaceAccessHandler($tree);
		$access_handler->setPermissions($parent_node, $node_id);
		
		$this->exercise->addResourceObject($node_id, $this->assignment->getId(), $ilUser->getId());
		
		ilUtil::sendSuccess($this->lng->txt("exc_blog_created"), true);
		$this->ctrl->redirect($this, "showOverview");
	}
	
	protected function setSelectedBlogObject()
	{
		global $ilUser;
		
		if($_POST["node"])
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";		
			$tree = new ilWorkspaceTree($ilUser->getId());
			$node = $tree->getNodeData($_POST["node"]);
			if($node && $node["type"] == "blog")
			{
				$this->removeExistingSubmissions();
				$this->exercise->addResourceObject($node["wsp_id"], $this->assignment->getId(), $ilUser->getId());
				
				ilUtil::sendSuccess($this->lng->txt("exc_blog_selected"), true);
				$this->ctrl->setParameter($this, "blog_id", $node["wsp_id"]);
				$this->ctrl->redirect($this, "askDirectionSubmission");				
			}
		}
		
		ilUtil::sendFailure($this->lng->txt("select_one"));
		return $this->selectPortfolioObject();
	}
	
	protected function renderWorkspaceExplorer($a_cmd)
	{
		global $ilUser;
		
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
		require_once 'Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php';
		
		$tree = new ilWorkspaceTree($ilUser->getId());
		$access_handler = new ilWorkspaceAccessHandler($tree);
		$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_RADIO, '', 
			'exc_wspexpand', $tree, $access_handler);
		$exp->setTargetGet('wsp_id');
		
		if($a_cmd == "selectBlog")
		{
			$exp->removeAllFormItemTypes();
			$exp->addFilter('blog');
			$exp->addFormItemForType('blog');
		}
	
		if($_GET['exc_wspexpand'] == '')
		{
			// not really used as session is already set [see above]
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET['exc_wspexpand'];
		}
		
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, $a_cmd));
		$exp->setPostVar('node');
		$exp->setExpand($expanded);
		$exp->setOutput(0);
	
		return $exp->getOutput();
	}
	
	
	//
	// PORTFOLIO
	//
	
	protected function selectPortfolioObject()
	{
		global $ilUser;
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->assignment->getDeadline() && ($this->assignment->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$portfolios = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());
		if($portfolios)
		{
			$tpl->setCurrentBlock("item");
			foreach($portfolios as $portfolio)
			{
				$tpl->setVariable("ITEM_ID", $portfolio["id"]);
				$tpl->setVariable("ITEM_TITLE", $portfolio["title"]);
				$tpl->parseCurrentBlock();				
			}			
		}
		
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_select_portfolio").": ".$this->assignment->getTitle());
		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("CMD_SUBMIT", "setSelectedPortfolio");
		$tpl->setVariable("CMD_CANCEL", "showOverview");
		
		ilUtil::sendInfo($this->lng->txt("exc_select_portfolio_info"));
					
		$this->tpl->setContent($tpl->get());
	}
	
	protected function initPortfolioTemplateForm(array $a_templates)
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();		
		$form->setTitle($this->lng->txt("exc_create_portfolio").": ".$this->assignment->getTitle());	
		$form->setFormAction($this->ctrl->getFormAction($this, "setSelectedPortfolioTemplate"));
				
		$prtt = new ilRadioGroupInputGUI($this->lng->txt("obj_prtt"), "prtt");
		$prtt->setRequired(true);
		$prtt->addOption(new ilRadioOption($this->lng->txt("exc_create_portfolio_no_template"), -1));		
		foreach($a_templates as $id => $title)
		{
			$prtt->addOption(new ilRadioOption('"'.$title.'"', $id));
		}
		$prtt->setValue(-1);
		$form->addItem($prtt);
			
		$form->addCommandButton("setSelectedPortfolioTemplate", $this->lng->txt("save"));				
		$form->addCommandButton("showOverview", $this->lng->txt("cancel"));
		
		return $form;		
	}
	
	protected function createPortfolioTemplateObject(ilPropertyFormGUI $a_form = null)
	{
				include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		$templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
		if(!sizeof($templates))
		{
			$this->ctrl->redirect($this, "showOverview");
		}
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		if(!$a_form)
		{
			$a_form = $this->initPortfolioTemplateForm($templates);
		}
		
		$this->tpl->setContent($a_form->getHTML());		
	}
	
	protected function setSelectedPortfolioTemplateObject()
	{		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		$templates = ilObjPortfolioTemplate::getAvailablePortfolioTemplates();
		if(!sizeof($templates))
		{
			$this->ctrl->redirect($this, "showOverview");
		}
		
		$form = $this->initPortfolioTemplateForm($templates);
		if($form->checkInput())
		{
			$prtt = $form->getInput("prtt");
			if($prtt > 0 && array_key_exists($prtt, $templates))
			{
				$title = $this->exercise->getTitle()." - ".$this->assignment->getTitle();
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "exc_id", $this->exercise->getRefId());
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "ass_id", $this->assignment->getId());
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "pt", $title);
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "prtt", $prtt);
				$this->ctrl->redirectByClass(array("ilPersonalDesktopGUI", "ilPortfolioRepositoryGUI", "ilObjPortfolioGUI"), "createPortfolioFromTemplate");
			}
			else
			{
				// do not use template
				return $this->createPortfolioObject();
			}			
		}
		
		$form->setValuesByPost();
		$this->createPortfolioTemplateObject($form);
	}
	
	protected function createPortfolioObject()
	{
		global $ilUser;
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$portfolio = new ilObjPortfolio();
		$portfolio->setTitle($this->exercise->getTitle()." - ".$this->assignment->getTitle());
		$portfolio->create();
	
		$this->exercise->addResourceObject($portfolio->getId(), $this->assignment->getId(), $ilUser->getId());
		
		ilUtil::sendSuccess($this->lng->txt("exc_portfolio_created"), true);
		$this->ctrl->redirect($this, "showOverview");
	}
	
	protected function setSelectedPortfolioObject()
	{
		global $ilUser;
		
		if($_POST["item"])
		{			
			$this->removeExistingSubmissions();
			$this->exercise->addResourceObject($_POST["item"], $this->assignment->getId(), $ilUser->getId());
						
			ilUtil::sendSuccess($this->lng->txt("exc_portfolio_selected"), true);
			$this->ctrl->setParameter($this, "prtf_id", $_POST["item"]);
			$this->ctrl->redirect($this, "askDirectionSubmission");									
		}
		
		ilUtil::sendFailure($this->lng->txt("select_one"));
		return $this->selectPortfolioObject();
	}
	
	
	//
	// SUBMIT BLOG/PORTFOLIO
	//	
	
	/**
	 * remove existing files/submissions for assignment
	 */
	public function removeExistingSubmissions()
	{		
		global $ilUser;
		
		$submitted = ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
		if($submitted)
		{
			$files = array();
			foreach($submitted as $item)
			{
				$files[] = $item["returned_id"];
			}
			ilExAssignment::deleteDeliveredFiles($this->assignment->getExerciseId(), $this->assignment->getId(), $files, $ilUser->getId());
		}			
	}
	
	protected function askDirectionSubmissionObject()
	{
		global $tpl;
		
		$this->tabs_gui->setTabActive("content");
		$this->addContentSubTabs("content");
		
		include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$conf = new ilConfirmationGUI();
		
		
		if($_REQUEST["blog_id"])
		{
			$this->ctrl->setParameter($this, "blog_id", $_REQUEST["blog_id"]);
			$txt = $this->lng->txt("exc_direct_submit_blog"); 
		}
		else
		{
			$this->ctrl->setParameter($this, "prtf_id", $_REQUEST["prtf_id"]);
			$txt = $this->lng->txt("exc_direct_submit_portfolio"); 
		}
		$conf->setFormAction($this->ctrl->getFormAction($this, "directSubmit"));
		
		$conf->setHeaderText($txt);
		$conf->setConfirm($this->lng->txt("submit"), "directSubmit");
		$conf->setCancel($this->lng->txt("cancel"), "showOverview");
		
		$tpl->setContent($conf->getHTML());
	}
	
	protected function directSubmitObject()
	{
		global $ilUser;
		
		$success = false;
		
		// submit current version of blog
		if($_REQUEST["blog_id"])
		{
			$success = $this->submitBlog($_REQUEST["blog_id"]);
			$this->ctrl->setParameter($this, "blog_id", "");
		}
		// submit current version of portfolio
		else if($_REQUEST["prtf_id"])
		{
			$success = 	$this->submitPortfolio($_REQUEST["prtf_id"]);
			$this->ctrl->setParameter($this, "prtf_id", "");
		}
				
		if($success)
		{	
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("msg_failed"), true);
		}
		$this->ctrl->redirect($this, "showOverview");		
	}
	
	/**
	 * Submit blog for assignment
	 * 
	 * @param int $a_blog_id
	 * @return bool
	 */
	function submitBlog($a_blog_id)
	{
		global $ilUser;
		
		if($this->exercise && $this->ass)
		{
			$blog_id = $a_blog_id;		

			include_once "Modules/Blog/classes/class.ilObjBlogGUI.php";
			$blog_gui = new ilObjBlogGUI($blog_id, ilObjBlogGUI::WORKSPACE_NODE_ID);
			if($blog_gui->object)
			{
				$file = $blog_gui->buildExportFile();
				$size = filesize($file);
				if($size)
				{
					$this->removeExistingSubmissions();
					
					$meta = array(
						"name" => $blog_id,
						"tmp_name" => $file,
						"size" => $size	
						);		
					$this->exercise->deliverFile($meta, $this->assignment->getId(), $ilUser->getId(), true);	

					$this->sendNotifications($this->assignment->getId());
					$this->exercise->handleSubmission($this->assignment->getId());	
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Submit portfolio for assignment
	 * 
	 * @param int $a_portfolio_id
	 * @return bool 
	 */
	function submitPortfolio($a_portfolio_id)
	{
		global $ilUser;
		
		if($this->exercise && $this->ass)
		{
			$prtf_id = $a_portfolio_id;			

			include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
			$prtf = new ilObjPortfolio($prtf_id, false);	
			if($prtf->getTitle())
			{
				include_once "Modules/Portfolio/classes/class.ilPortfolioHTMLExport.php";
				$export = new ilPortfolioHTMLExport(null, $prtf);
				$file = $export->buildExportFile();
				$size = filesize($file);
				if($size)
				{
					$this->removeExistingSubmissions();
					
					$meta = array(
						"name" => $prtf_id,
						"tmp_name" => $file,
						"size" => $size
						);		
					$this->exercise->deliverFile($meta, $this->assignment->getId(), $ilUser->getId(), true);	

					$this->sendNotifications($this->assignment->getId());
					$this->exercise->handleSubmission($this->assignment->getId());	
					return true;
				}
			}
		}
		return false;
	}	
	
	
	//
	// TEXT ASSIGNMENT (EDIT)
	// 
	
	protected function initAssignmentTextForm(ilExAssignment $a_ass, $a_read_only = false, $a_cancel_cmd = "showOverview", $a_peer_review_cmd = null, $a_peer_rating_html = null)
	{		
		global $ilCtrl, $ilUser;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();		
		$form->setTitle($this->lng->txt("exc_assignment")." \"".$a_ass->getTitle()."\"");
			
		if(!$a_read_only)
		{
			$text = new ilTextAreaInputGUI($this->lng->txt("exc_your_text"), "atxt");
			$text->setRequired((bool)$a_ass->getMandatory());				
			$text->setRows(40);
			$form->addItem($text);
			
			// custom rte tags
			$text->setUseRte(true);		
			$text->setRTESupport($ilUser->getId(), "exca~", "exc_ass"); 
			
			// see ilObjForumGUI
			$text->disableButtons(array(
				'charmap',
				'undo',
				'redo',
				'justifyleft',
				'justifycenter',
				'justifyright',
				'justifyfull',
				'anchor',
				'fullscreen',
				'cut',
				'copy',
				'paste',
				'pastetext',
				// 'formatselect' #13234
			));
			
			$form->setFormAction($ilCtrl->getFormAction($this, "updateAssignmentText"));
			$form->addCommandButton("updateAssignmentTextAndReturn", $this->lng->txt("save_return"));		
			$form->addCommandButton("updateAssignmentText", $this->lng->txt("save"));							
		}
		else
		{
			$text = new ilNonEditableValueGUI($this->lng->txt("exc_files_returned_text"), "atxt", true);	
			$form->addItem($text);		
			
			if(!$a_peer_review_cmd)
			{
				$form->setFormAction($ilCtrl->getFormAction($this, "showOverview"));
			}
			else
			{				
				$rating = new ilCustomInputGUI($this->lng->txt("exc_peer_review_rating"));
				$rating->setHtml($a_peer_rating_html);
				$form->addItem($rating);				
				
				$comm = new ilTextAreaInputGUI($this->lng->txt("exc_peer_review_comment"), "comm");
				$comm->setCols(75);
				$comm->setRows(15);				
				$form->addItem($comm);
				
				$form->setFormAction($ilCtrl->getFormAction($this, $a_peer_review_cmd));
				$form->addCommandButton($a_peer_review_cmd, $this->lng->txt("save"));	
			}
		}
		$form->addCommandButton($a_cancel_cmd, $this->lng->txt("cancel"));
		
		return $form;
	}
	
	function editAssignmentTextObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilTabs, $ilCtrl, $ilUser;

		if(!$this->ass || 
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT ||
			($this->assignment->getDeadline() && $this->assignment->getDeadline() - time() < 0))				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
			
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("content");
		
		if($this->assignment->getDeadline())
		{
			ilUtil::sendInfo($this->lng->txt("exc_edit_until").": ".
				ilDatePresentation::formatDate(new ilDateTime($this->assignment->getDeadline(),IL_CAL_UNIX)));
		}
		
		if(!$a_form)
		{
			$a_form = $this->initAssignmentTextForm($this->ass);		

			$files = ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), $this->assignment->getId(), $ilUser->getId());
			if($files)
			{
				$files = array_shift($files);
				if(trim($files["atext"]))
				{
				   $text = $a_form->getItemByPostVar("atxt");
				   // mob id to mob src
				   $text->setValue(ilRTE::_replaceMediaObjectImageSrc($files["atext"], 1));
				}
			}
		}
	
		$this->tpl->setContent($a_form->getHTML());
	}
	
	function updateAssignmentTextAndReturnObject()
	{
		$this->updateAssignmentTextObject(true);		
	}
	
	function updateAssignmentTextObject($a_return = false)
	{
		global $ilCtrl, $ilUser;
		
		$times_up = ($this->assignment->getDeadline() && $this->assignment->getDeadline() - time() < 0);
		
		if(!$this->ass || 
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT ||
			$times_up)
		{
			if($times_up)
			{
				ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			}
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$form = $this->initAssignmentTextForm($this->ass);	
		
		// we are not using a purifier, so we have to set the valid RTE tags
		// :TODO: 
		include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
		$rte = $form->getItemByPostVar("atxt");
		$rte->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("exc_ass"));
		
		if($form->checkInput())
		{			
			$text = trim($form->getInput("atxt"));	
									
			$existing = (bool)ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), 
				$this->assignment->getId(), $ilUser->getId());			
												
			$returned_id = $this->exercise->updateTextSubmission(
				$this->assignment->getExerciseId(), 
				$this->assignment->getId(), 
				$ilUser->getId(), 
				// mob src to mob id
				ilRTE::_replaceMediaObjectImageSrc($text, 0));	
			
			// no empty text
			if($returned_id)
			{
				if(!$existing)
				{
					// #14332 - new text
					$this->sendNotifications($this->assignment->getId());
					$this->exercise->handleSubmission($this->assignment->getId());						
				}
				
				// mob usage
				include_once "Services/MediaObjects/classes/class.ilObjMediaObject.php";
				$mobs = ilRTE::_getMediaObjects($text, 0);
				foreach($mobs as $mob)
				{
					if(ilObjMediaObject::_exists($mob))
					{
						ilObjMediaObject::_removeUsage($mob, 'exca~:html', $ilUser->getId());
						ilObjMediaObject::_saveUsage($mob, 'exca:html', $returned_id);
					}
				}
			}
			
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
			if($a_return)
			{
				$ilCtrl->redirect($this, "showOverview");
			}
			else
			{
				$ilCtrl->redirect($this, "editAssignmentText");
			}
		}
		
		$form->setValuesByPost();
		$this->editAssignmentTextObject($form);		
	}
	
	function showAssignmentTextObject()
	{
		global $ilCtrl, $ilUser, $lng, $tpl;
		
		if(!$this->ass || 
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT)	
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$add_rating = null;
		
		// tutor
		if((int)$_GET["grd"])
		{			
			if((int)$_GET["grd"] == 1)
			{													
				$user_id = (int)$_GET["member_id"];				
				$cancel_cmd = "members";	
			}
			else
			{			
				$user_id = (int)$_GET["part_id"];					
				$cancel_cmd = "showParticipant";		
			}									
		}		
		// peer review
		else if($this->assignment->hasPeerReviewAccess((int)$_GET["member_id"]))
		{					
			$user_id = (int)$_GET["member_id"];
			$cancel_cmd = "editPeerReview";		
			
			// rating
			$add_rating = "updatePeerReviewText";
			$ilCtrl->setParameter($this, "peer_id", $user_id);		
			include_once './Services/Rating/classes/class.ilRatingGUI.php';
			$rating = new ilRatingGUI();
			$rating->setObject($this->assignment->getId(), "ass", $user_id, "peer");
			$rating->setUserId($ilUser->getId());
			$rating = '<div id="rtr_widget">'.$rating->getHTML(false, true,
				"il.ExcPeerReview.saveSingleRating(".$user_id.", %rating%)").'</div>';		
			
			$ilCtrl->setParameter($this, "ssrtg", 1);
			$tpl->addJavaScript("Modules/Exercise/js/ilExcPeerReview.js");
			$tpl->addOnLoadCode("il.ExcPeerReview.setAjax('".
				$ilCtrl->getLinkTarget($this, "updatePeerReviewComments", "", true, false).
				"')");
			$ilCtrl->setParameter($this, "ssrtg", "");
		}
		// personal
		else
		{			
			$user_id = $ilUser->getId();
			$cancel_cmd = "showOverview";
		}
					
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, $cancel_cmd));		
		
		$a_form = $this->initAssignmentTextForm($this->assignment, true, $cancel_cmd, $add_rating, $rating);	
		
		if(($user_id != $ilUser->getId() || (bool)$_GET["grd"]))
		{
			if(!stristr($cancel_cmd, "peer"))
			{
				include_once "Services/User/classes/class.ilUserUtil.php";
				$a_form->setDescription(ilUserUtil::getNamePresentation($user_id));						
			}
			else
			{			
				if(!$this->assignment->hasPeerReviewPersonalized())
				{
					$a_form->setDescription($lng->txt("id").": ".(int)$_GET["seq"]);
				}
				else
				{
					include_once "Services/User/classes/class.ilUserUtil.php";
					$a_form->setDescription(ilUserUtil::getNamePresentation($user_id));	
				}
								
				foreach($this->assignment->getPeerReviewsByPeerId($user_id) as $item)
				{
					if($item["giver_id"] == $ilUser->getId())
					{						
						$a_form->getItemByPostVar("comm")->setValue($item["pcomment"]);					
						break;
					}
				}
			}						
		}
		
		$files = ilExAssignment::getDeliveredFiles($this->assignment->getExerciseId(), $this->assignment->getId(), $user_id);
		if($files)
		{
			$files = array_shift($files);
			if(trim($files["atext"]))
			{
			   $text = $a_form->getItemByPostVar("atxt");
			   // mob id to mob src
			   $text->setValue(ilRTE::_replaceMediaObjectImageSrc($files["atext"], 1));
			}
		}		
	
		$this->tpl->setContent($a_form->getHTML());	
	}
}
