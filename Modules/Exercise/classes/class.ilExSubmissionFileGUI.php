<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * File-based submissions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * 
 * @ilCtrl_Calls ilExSubmissionFileGUI: 
 * @ingroup ModulesExercise
 */
class ilExSubmissionFileGUI extends ilExSubmissionBaseGUI
{		
	public function executeCommand()
	{
		global $ilCtrl;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("submissionScreen");		
		
		switch($class)
		{		
			default:									
				$this->{$cmd."Object"}();				
				break;			
		}
	}
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_missing_team, array $a_files)
	{		
		global $lng, $ilCtrl;
		
		$titles = array();
		foreach($a_files as $file)
		{
			$titles[] = $file["filetitle"];
		}
		$files_str = implode($titles, ", ");
		if ($files_str == "")
		{
			$files_str = $lng->txt("message_no_delivered_files");
		}

		// no team == no submission
		if(!$a_missing_team)
		{							
			if ($a_ass->beforeDeadline())
			{
				$title = (count($titles) == 0
					? $lng->txt("exc_hand_in")
					: $lng->txt("exc_edit_submission"));												

				$button = ilLinkButton::getInstance();
				$button->setPrimary(true);
				$button->setCaption($title, false);
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen"));							
				$files_str.= " ".$button->render();								
			}
			else
			{
				if (count($titles) > 0)
				{								
					$button = ilLinkButton::getInstance();								
					$button->setCaption("already_delivered_files");
					$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionFileGUI"), "submissionScreen"));											
					$files_str.= " ".$button->render();
				}
			}
		}

		$a_info->addProperty($lng->txt("exc_files_returned"), $files_str);	
	}
	
	/**
	* Displays a form which allows members to deliver their solutions
	*
	* @access public
	*/
	function submissionScreenObject()
	{
		global $ilToolbar, $ilHelp;

		$ilHelp->setScreenIdComponent("exc");
		$ilHelp->setScreenId("submissions");
		
		$this->handleTabs();
		
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
  	
	
  
 	
  
 	

	
	
	
	
	
}
