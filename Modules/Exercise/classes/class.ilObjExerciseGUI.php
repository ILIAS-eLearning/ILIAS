<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjExerciseGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @author Michael Jansen <mjansen@databay.de>
* $Id$
* 
* @ilCtrl_Calls ilObjExerciseGUI: ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilObjectCopyGUI, ilFileSystemGUI, ilExportGUI, ilShopPurchaseGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilRepositorySearchGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilCertificateGUI, ilRatingGUI
* 
* @ingroup ModulesExercise
*/
class ilObjExerciseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExerciseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "exc";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		$lng->loadLanguageModule("exercise");
		$lng->loadLanguageModule("exc");
		$this->ctrl->saveParameter($this,
			array("ass_id", "part_id", "fsmode"));
		
		if ($_GET["ass_id"] > 0)
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			$this->ass = new ilExAssignment((int) $_GET["ass_id"]);
		}
	}

	function getFiles()
	{
		return $this->files ? $this->files : array();
	}
	
	function setFiles($a_files)
	{
		$this->files = $a_files;
	}

	function executeCommand()
	{
  		global $ilUser,$ilCtrl, $ilTabs, $lng;
  
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
  
//echo "-".$next_class."-".$cmd."-"; exit;
  		switch($next_class)
		{
			case "ilfilesystemgui":				
				if($_GET["fsmode"] == "peer")
				{					
					$ilCtrl->saveParameter($this, array("fu"));										
					
					// see self::downloadPeerReview()
					$parts = explode("__", $_GET["fu"]);
					$giver_id = $parts[0];
					$peer_id = $parts[1];

					if($giver_id == $ilUser->getId() || 
						$peer_id == $ilUser->getId())
					{		
						$this->checkPermission("read");			
					}
					else
					{
						$this->checkPermission("write");												
					}
		
					/* could be 1st review and not saved yet
					$valid = false;
					$peer_items = $this->ass->getPeerReviewsByPeerId($peer_id, true);				
					if(sizeof($peer_items))
					{
						foreach($peer_items as $item)
						{
							if($item["giver_id"] == $giver_id)
							{	
								$valid = true;
							}
						}
					}
					if(!$valid)
					{
						$ilCtrl->redirect($this, "editPeerReview");
					}
					*/
					
					$ilTabs->clearTargets();
					$ilTabs->setBackTarget($lng->txt("back"),
						$ilCtrl->getLinkTarget($this, "editPeerReview"));
															
					include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
					$fstorage = new ilFSStorageExercise($this->object->getId(), $this->ass->getId());
					$fstorage->create();
					
					include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
					$fs_gui = new ilFileSystemGUI($fstorage->getPeerReviewUploadPath($peer_id, $giver_id));
					$fs_gui->setTableId("excfbpeer");
					$fs_gui->setAllowDirectories(false);					
					$fs_gui->setTitle($this->ass->getTitle().": ".
						$lng->txt("exc_peer_review")." - ".
						$lng->txt("exc_peer_review_give"));						 
					$ret = $this->ctrl->forwardCommand($fs_gui);
				}				
				else if ($_GET["fsmode"] == "feedback" ||
					$_GET["fsmode"] == "feedbackpart")	// feedback files
				{
					$this->checkPermission("write");				
					$ilCtrl->saveParameter($this, array("member_id"));
					//$this->setAssignmentHeader();
					//$ilTabs->activateTab("ass_files");
					$ilTabs->clearTargets();
					
					if ($_GET["fsmode"] != "feedbackpart")
					{
						$ilTabs->setBackTarget($lng->txt("back"),
							$ilCtrl->getLinkTarget($this, "members"));
					}
					else
					{
						$ilTabs->setBackTarget($lng->txt("back"),
							$ilCtrl->getLinkTarget($this, "showParticipant"));
					}
					
					ilUtil::sendInfo($lng->txt("exc_fb_tutor_info"));
										
					include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
					$fstorage = new ilFSStorageExercise($this->object->getId(), (int) $_GET["ass_id"]);
					$fstorage->create();
					
					include_once("./Services/User/classes/class.ilUserUtil.php");
					$noti_rec_ids = array();
					if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
					{
						$team_id = $this->ass->getTeamId((int) $_GET["member_id"]);
						$feedback_id = "t".$team_id;
						$fs_title = array();
						foreach($this->ass->getTeamMembers($team_id) as $team_user_id)
						{
							$fs_title[] = ilUserUtil::getNamePresentation($team_user_id, false, false, "", true);
							$noti_rec_ids[] = $team_user_id;
						}
						$fs_title = implode(" / ", $fs_title);
					}
					else
					{
						$feedback_id = $noti_rec_ids = (int) $_GET["member_id"];
						$fs_title = ilUserUtil::getNamePresentation((int) $_GET["member_id"], false, false, "", true);
					}
					
					include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
					$fs_gui = new ilFileSystemGUI($fstorage->getFeedbackPath($feedback_id));
					$fs_gui->setTableId("excfbfil".(int)$_GET["ass_id"]."_".$feedback_id);
					$fs_gui->setAllowDirectories(false);					
					$fs_gui->setTitle($lng->txt("exc_fb_files")." - ".
						ilExAssignment::lookupTitle((int) $_GET["ass_id"])." - ".
						$fs_title);
					$pcommand = $fs_gui->getLastPerformedCommand();					
					if (is_array($pcommand) && $pcommand["cmd"] == "create_file")
					{
						$this->object->sendFeedbackFileNotification($pcommand["name"], 
							$noti_rec_ids, (int) $_GET["ass_id"]);
					}					 
					$ret = $this->ctrl->forwardCommand($fs_gui);
				}
				else 		// assignment files
				{
					$this->setAssignmentHeader();
					$ilTabs->activateTab("ass_files");
					include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
					$fstorage = new ilFSStorageExercise($this->object->getId(), (int) $_GET["ass_id"]);
					$fstorage->create();
					include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
					$fs_gui = new ilFileSystemGUI($fstorage->getPath());
					$fs_gui->setTitle($lng->txt("exc_instruction_files"));
					$fs_gui->setTableId("excassfil".$_GET["ass_id"]);
					$fs_gui->setAllowDirectories(false);
					$ret = $this->ctrl->forwardCommand($fs_gui);
				}
				break;

			case "ilinfoscreengui":
				$ilTabs->activateTab("info");
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				$ilTabs->activateTab("permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
			break;
	
			case "illearningprogressgui":
				$ilTabs->activateTab("learning_progress");
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
	
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
					$this->object->getRefId(),
					$_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
			break;

			case 'ilrepositorysearchgui':
				$ilTabs->activateTab("grades");
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();
				if(!$_REQUEST["ctx"])
				{
					$rep_search->setTitle($this->lng->txt("exc_add_participant"));
					$rep_search->setCallback($this,'addMembersObject');

					// Set tabs
					$this->tabs_gui->setTabActive('members');
					$this->ctrl->setReturn($this,'members');
					
					#$this->__setSubTabs('members');
					#$this->tabs_gui->setSubTabActive('members');
				}
				else
				{
					$this->ctrl->saveParameterByClass('ilRepositorySearchGUI', 'ctx', 1);
					
					$rep_search->setTitle($this->lng->txt("exc_team_member_add"));
					$rep_search->setCallback($this,'addTeamMemberActionObject');
					
					// Set tabs
					$this->initTeamSubmission("submissionScreenTeam");
					$this->ctrl->setReturn($this,'submissionScreenTeam');
				}
				$ret =& $this->ctrl->forwardCommand($rep_search);
				break;
				
			case 'ilobjectcopygui':
				$ilCtrl->saveParameter($this, 'new_type');
				$ilCtrl->setReturnByClass(get_class($this),'create');

				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('exc');
				$this->ctrl->forwardCommand($cp);
				break;

			case "ilexportgui":
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
				break;
			case 'ilshoppurchasegui':
				include_once './Services/Payment/classes/class.ilShopPurchaseGUI.php';
				$sp = new ilShopPurchaseGUI($_GET['ref_id']);

				$this->ctrl->forwardCommand($sp);
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilcertificategui":
				$this->setSettingsSubTabs();
				$this->tabs_gui->activateTab("settings");
				$this->tabs_gui->activateSubTab("certificate");
				include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilExerciseCertificateAdapter($this->object));
				$this->ctrl->forwardCommand($output_gui);
				break;
			
			case "ilratinggui":				
				$this->ass->updatePeerReviewTimestamp((int)$_REQUEST["peer_id"]);
				
				include_once("./Services/Rating/classes/class.ilRatingGUI.php");
				$rating_gui = new ilRatingGUI();
				$rating_gui->setObject($this->ass->getId(), "ass",
					(int)$_REQUEST["peer_id"], "peer");
				$this->ctrl->forwardCommand($rating_gui);
				$ilCtrl->redirect($this, "editPeerReview");
				break;

			default:		
				$this->ctrl->setParameter($this, "fsmode", ""); // #15115
				
				if(!$cmd)
				{
					$cmd = "infoScreen";
				}
	
				$cmd .= "Object";
	
				$this->$cmd();
	
			break;
		}
		
		$this->addHeaderAction();
  
  		return true;
	}

	function viewObject()
	{
		$this->infoScreenObject();
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
		
		$this->checkPermission("read");
		if (count($_REQUEST["delivered"]))
		{
			if(!is_array($_REQUEST["delivered"]))
			{
				$_REQUEST["delivered"] = array($_REQUEST["delivered"]);
			}
			ilExAssignment::downloadSelectedFiles($this->object->getId(), (int) $_GET["ass_id"],
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
  
	/**
	* Displays a form which allows members to deliver their solutions
	*
	* @access public
	*/
	function submissionScreenObject()
	{
		global $ilToolbar;

		$this->initTeamSubmission("showOverview", false);
		$this->tabs_gui->activateTab("submissions");
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
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
		$tab = new ilExcDeliveredFilesTableGUI($this, "submissionScreen", $this->object, $_GET["ass_id"]);
		$this->tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Display form for single file upload 
	 */
	public function uploadFormObject()
	{		
		if (mktime() < $this->ass->getDeadline() || ($this->ass->getDeadline() == 0))
		{
			$this->tabs_gui->clearTargets();
			$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
				$this->ctrl->getLinkTarget($this, "submissionScreen"));

			global $ilHelp;
			$ilHelp->setScreenIdComponent("exc");
			$ilHelp->setScreenId("upload_submission");

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
		if (mktime() < $this->ass->getDeadline() || ($this->ass->getDeadline() == 0))
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
		
		$this->checkPermission("read");
		
		// #15322
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
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
			if(!$this->object->deliverFile($file, (int) $_GET["ass_id"], $ilUser->id))
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
			$this->object->handleSubmission((int)$_GET['ass_id']);
		}
		$ilCtrl->redirect($this, "submissionScreen");
	}

	/**
	 * Upload zip file
	 */
	function deliverUnzipObject()
	{
		global $ilCtrl;
	
		$this->checkPermission("read");
		
		// #15322
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
			return;
		}

		if (preg_match("/zip/",$_FILES["deliver"]["type"]) == 1)
		{
			if($this->object->processUploadedFile($_FILES["deliver"]["tmp_name"], "deliverFile", false,
				(int) $_GET["ass_id"]))
			{
				$this->sendNotifications((int)$_GET["ass_id"]);
				$this->object->handleSubmission((int)$_GET['ass_id']);
			}
		}

		$ilCtrl->redirect($this, "submissionScreen");
	}
  
 	/**
 	 * Download feedback file
 	 */
	function downloadFeedbackFileObject()
	{
		global $rbacsystem, $ilUser;
		
		$file = $_REQUEST["file"];

		// check read permission
		$this->checkPermission("read");
		
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
		$storage = new ilFSStorageExercise($this->object->getId(), (int) $_GET["ass_id"]);
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
  
 	/**
 	 * Download assignment file
 	 */
	function downloadFileObject()
	{
		global $rbacsystem;
		
		$file = $_REQUEST["file"];

		// check read permission
		$this->checkPermission("read");
		
		if (!isset($file))
		{
			ilUtil::sendFailure($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		
		// check, whether file belongs to assignment
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$files = ilExAssignment::getFiles($this->object->getId(), (int) $_GET["ass_id"]);
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
			$storage = new ilFSStorageExercise($this->object->getId(), (int) $_GET["ass_id"]);
			$p = $storage->getAssignmentFilePath($file);
			ilUtil::deliverFile($p, $file);
		}
	
		return true;
	}

	protected function  afterSave(ilObject $a_new_object)
	{
		$a_new_object->saveData();
		
		ilUtil::sendSuccess($this->lng->txt("exc_added"), true);
		ilUtil::redirect("ilias.php?baseClass=ilExerciseHandlerGUI&ref_id=".$a_new_object->getRefId()."&cmd=addAssignment");
	}

	/**
	* Init properties form.
	*/
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		$a_form->setTitle($this->lng->txt("exc_edit_exercise"));

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('exc_passing_exc'));
		$a_form->addItem($section);

		// pass mode
		$radg = new ilRadioGroupInputGUI($this->lng->txt("exc_pass_mode"), "pass_mode");
	
			$op1 = new ilRadioOption($this->lng->txt("exc_pass_all"), "all",
				$this->lng->txt("exc_pass_all_info"));
			$radg->addOption($op1);
			$op2 = new ilRadioOption($this->lng->txt("exc_pass_minimum_nr"), "nr",
				$this->lng->txt("exc_pass_minimum_nr_info"));
			$radg->addOption($op2);

			// minimum number of assignments to pass
			$ni = new ilNumberInputGUI($this->lng->txt("exc_min_nr"), "pass_nr");
			$ni->setSize(4);
			$ni->setMaxLength(4);
			$ni->setRequired(true);
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			$mand = ilExAssignment::countMandatory($this->object->getId());
			$min = max($mand, 1);
			$ni->setMinValue($min, true);
			$ni->setInfo($this->lng->txt("exc_min_nr_info"));
			$op2->addSubItem($ni);

		$a_form->addItem($radg);

		// completion by submission
		$subcompl = new ilRadioGroupInputGUI($this->lng->txt("exc_passed_status_determination"), "completion_by_submission");
			$op1 = new ilRadioOption($this->lng->txt("exc_completion_by_tutor"), 0, "");
			$subcompl->addOption($op1);
			$op2 = new ilRadioOption($this->lng->txt("exc_completion_by_submission"), 1,$this->lng->txt("exc_completion_by_submission_info"));
			$subcompl->addOption($op2);
		$a_form->addItem($subcompl);

		/*$subcompl = new ilCheckboxInputGUI($this->lng->txt('exc_completion_by_submission'), 'completion_by_submission');
		$subcompl->setInfo($this->lng->txt('exc_completion_by_submission_info'));
		$subcompl->setValue(1);
		$a_form->addItem($subcompl);*/

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('exc_publishing'));
		$a_form->addItem($section);

		// show submissions
		$cb = new ilCheckboxInputGUI($this->lng->txt("exc_show_submissions"), "show_submissions");
		$cb->setInfo($this->lng->txt("exc_show_submissions_info"));
		$a_form->addItem($cb);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('exc_notification'));
		$a_form->addItem($section);

		// submission notifications
		$cbox = new ilCheckboxInputGUI($this->lng->txt("exc_submission_notification"), "notification");
		$cbox->setInfo($this->lng->txt("exc_submission_notification_info"));
		$a_form->addItem($cbox);		
	}
	
	/**
	* Get values for properties form
	*/
	protected function getEditFormCustomValues(array &$a_values)
	{
		global $ilUser;

		$a_values["desc"] = $this->object->getLongDescription();
		$a_values["show_submissions"] = $this->object->getShowSubmissions();
		$a_values["pass_mode"] = $this->object->getPassMode();
		if ($a_values["pass_mode"] == "nr")
		{
			$a_values["pass_nr"] = $this->object->getPassNr();
		}
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		$a_values["notification"] = ilNotification::hasNotification(
				ilNotification::TYPE_EXERCISE_SUBMISSION, $ilUser->getId(),
				$this->object->getId());
				
		$a_values['completion_by_submission'] = (int) $this->object->isCompletionBySubmissionEnabled();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		global $ilUser;
		$this->object->setShowSubmissions($a_form->getInput("show_submissions"));
		$this->object->setPassMode($a_form->getInput("pass_mode"));		
		if ($this->object->getPassMode() == "nr")
		{
			$this->object->setPassNr($a_form->getInput("pass_nr"));
		}
		
		$this->object->setCompletionBySubmission($a_form->getInput('completion_by_submission') == 1 ? true : false);
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::setNotification(ilNotification::TYPE_EXERCISE_SUBMISSION,
			$ilUser->getId(), $this->object->getId(),
			(bool)$a_form->getInput("notification"));
	}
  
	function cancelEditObject()
	{
		$this->ctrl->redirect($this, "view");
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
	* update data of members table
	*/
	function updateMembersObject()
	{
		global $rbacsystem;
	
		$this->checkPermission("write");
	
		if ($_POST["downloadReturned"])
		{
			$this->object->members_obj->deliverReturnedFiles(key($_POST["downloadReturned"]));
			exit;
		}
		else
		{
			switch($_POST["action"])
			{
				case "save_status":
					$this->saveStatusObject();
					break;
					
				case "send_member":
					$this->sendMembersObject();
					break;
				
				case "redirectFeedbackMail":
					$this->redirectFeedbackMailObject();
					break;
					
				case "delete_member":
					$this->deassignMembersObject();
					break;
			}
		}
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
				
				// add team member
				if($_REQUEST['ctx'])
				{
					return $this->submissionScreenTeamObject();
				}	
				else
				{
					return $this->membersObject();
				}
			}
			
			$user_ids[] = $user_id;
		}

		if($_REQUEST['ctx'])
		{		
			return $this->addTeamMemberActionObject($user_ids);						
		}						

		if(!$this->addMembersObject($user_ids));
		{
			$this->membersObject();
			return false;
		}
		return true;
	}

	/**
	 * Add new partipant
	 */
	function addMembersObject($a_user_ids = array())
	{
		global $ilAccess,$ilErr;

		$this->checkPermission("write");
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return false;
		}

		if(!$this->object->members_obj->assignMembers($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("exc_members_already_assigned"));
			return false;
		}
		else
		{
			// #9946 - create team for new user(s) for each team upload assignment
			foreach(ilExAssignment::getAssignmentDataOfExercise($this->object->getId()) as $ass)
			{
				if($ass["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$ass_obj = new ilExAssignment($ass["id"]);
					foreach($a_user_ids as $user_id)
					{
						$ass_obj->getTeamId($user_id, true);
					}
				}
			}						
			
			ilUtil::sendSuccess($this->lng->txt("exc_members_assigned"),true);
		}
//exit;
		$this->ctrl->redirect($this, "members");
		return true;
	}


	/**
	 * All participants and submission of one assignment
	 */
	function membersObject()
	{
		global $rbacsystem, $tree, $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;

		$ilTabs->activateTab("grades");
		
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
	
		$this->checkPermission("write");
		$this->addSubmissionSubTabs("assignment");
		
		// assignment selection
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getAssignmentDataOfExercise($this->object->getId());
		
		if ($_GET["ass_id"] == "")
		{
			$a = current($ass);
			$_GET["ass_id"] = $a["id"];
		}
		
		reset($ass);
		if (count($ass) > 1)
		{
			$options = array();
			foreach ($ass as $a)
			{
				$options[$a["id"]] = $a["title"];
			}
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($this->lng->txt(""), "ass_id");
			$si->setOptions($options);
			$si->setValue($_GET["ass_id"]);
			$ilToolbar->addInputItem($si);
					
			$ilToolbar->addFormButton($this->lng->txt("exc_select_ass"),
				"selectAssignment");
			$ilToolbar->addSeparator();
		}
		
		// add member
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'submit_name'			=> $lng->txt('add'),
				'add_search'			=> true,
				'add_from_container'    => $_GET["ref_id"]
			)
		);
		
		// we do not want the ilRepositorySearchGUI form action
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

		$ilToolbar->addSeparator();
		
		// multi-feebdack
		$ilToolbar->addButton($this->lng->txt("exc_multi_feedback"),
			$this->ctrl->getLinkTarget($this, "showMultiFeedback"));
		
		if (count($ass) > 0)
		{
			$ctype = null;
			foreach($ass as $item)
			{
				if($item["id"] == $_GET["ass_id"])
				{
					$ctype = $item["type"];
				}
			}						
			if($ctype == ilExAssignment::TYPE_TEXT)
			{
				$ilToolbar->addSeparator();
				$ilToolbar->addFormButton($lng->txt("exc_list_text_assignment"), "listTextAssignment");					
			}		
			else if(count(ilExAssignment::getAllDeliveredFiles($this->object->getId(), $_GET["ass_id"])))
			{			
				$ilToolbar->addSeparator();
				$ilToolbar->addFormButton($lng->txt("download_all_returned_files"), "downloadAll");			
			}		
			
			include_once("./Modules/Exercise/classes/class.ilExerciseMemberTableGUI.php");
			$exc_tab = new ilExerciseMemberTableGUI($this, "members", $this->object, $_GET["ass_id"]);
			$tpl->setContent($exc_tab->getHTML());
		}
		else
		{
			ilUtil::sendInfo($lng->txt("exc_no_assignments_available"));
		}
		return;		
	}

	/**
	 * Select assignment
	 */
	function selectAssignmentObject()
	{
		global $ilTabs;

		$ilTabs->activateTab("grades");

		$_GET["ass_id"] = ilUtil::stripSlashes($_POST["ass_id"]);
		$this->membersObject();
	}
	
	/**
	 * Show Participant
	 */
	function showParticipantObject()
	{
		global $rbacsystem, $tree, $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;

		$this->checkPermission("write");
		
		$ilTabs->activateTab("grades");
		$this->addSubmissionSubTabs("participant");
		
		// participant selection
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getAssignmentDataOfExercise($this->object->getId());
		$members = $this->object->members_obj->getMembers();
		
		if (count($members) == 0)
		{
			ilUtil::sendInfo($lng->txt("exc_no_participants"));
			return;
		}
		
		$mems = array();
		foreach ($members as $mem_id)
		{
			if (ilObject::_lookupType($mem_id) == "usr")
			{
				include_once("./Services/User/classes/class.ilObjUser.php");
				$name = ilObjUser::_lookupName($mem_id);
				$mems[$mem_id] = $name;
			}
		}
		
		$mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);
		
		if ($_GET["part_id"] == "" && count($mems) > 0)
		{
			$_GET["part_id"] = key($mems);
		}
		
		reset($mems);
		if (count($mems) > 1)
		{
			$options = array();
			foreach ($mems as $k => $m)
			{
				$options[$k] =
					$m["lastname"].", ".$m["firstname"]." [".$m["login"]."]";
			}
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($this->lng->txt(""), "part_id");
			$si->setOptions($options);
			$si->setValue($_GET["part_id"]);
			$ilToolbar->addInputItem($si);
			
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
			$ilToolbar->addFormButton($this->lng->txt("exc_select_part"),
				"selectParticipant");
		}

		if (count($mems) > 0)
		{
			include_once("./Modules/Exercise/classes/class.ilExParticipantTableGUI.php");
			$part_tab = new ilExParticipantTableGUI($this, "showParticipant",
				$this->object, $_GET["part_id"]);
			$tpl->setContent($part_tab->getHTML());
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("exc_no_assignments_available"));
		}
	}
	
	/**
	 * Select participant
	 */
	function selectParticipantObject()
	{
		global $ilTabs;

		$ilTabs->activateTab("grades");

		$_GET["part_id"] = ilUtil::stripSlashes($_POST["part_id"]);
		$this->showParticipantObject();
	}

	/**
	 * Show grades overview
	 */
	function showGradesOverviewObject()
	{
		global $rbacsystem, $tree, $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;
		
		$this->checkPermission("write");

		$ilTabs->activateTab("grades");
		$this->addSubmissionSubTabs("grades");
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$mem_obj = new ilExerciseMembers($this->object);
		$mems = $mem_obj->getMembers();

		if (count($mems) > 0)
		{
			$ilToolbar->addButton($lng->txt("exc_export_excel"),
				$ilCtrl->getLinkTarget($this, "exportExcel"));
		}

		include_once("./Modules/Exercise/classes/class.ilExGradesTableGUI.php");
		$grades_tab = new ilExGradesTableGUI($this, "showGradesOverview",
			$this->object, $mem_obj);
		$tpl->setContent($grades_tab->getHTML()); 
	}

	/**
	* set feedback status for member and redirect to mail screen
	*/
	function redirectFeedbackMailObject()
	{
		$this->checkPermission("write");
		
		$members = array();
						
		if ($_GET["member_id"] != "")
		{	
			if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$members = ilExAssignment::getTeamMembersByAssignmentId($this->ass->getId(), $_GET["member_id"]);
			}
			else
			{
				$members = array($_GET["member_id"]);
			}			
		}
		else if(count($_POST["member"]) > 0)
		{
			if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				foreach(array_keys($_POST["member"]) as $user_id)
				{
					$members = array_merge($members, ilExAssignment::getTeamMembersByAssignmentId($this->ass->getId(), $user_id));
				}
				$members = array_unique($members);
			}
			else
			{
				$members = array_keys($_POST["member"]);	
			}
		}
		
		if($members)
		{
			$logins = array();
			foreach($members as $user_id)
			{				
				ilExAssignment::updateStatusFeedbackForUser($this->ass->getId(), $user_id, 1);
				$logins[] = ilObjUser::_lookupLogin($user_id);
			}
			$logins = implode($logins, ",");
						
			require_once 'Services/Mail/classes/class.ilMailFormCall.php';
			ilUtil::redirect(ilMailFormCall::getRedirectTarget($this, 'members', array(), array('type' => 'new', 'rcp_to' => $logins)));
		}

		ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
		$this->ctrl->redirect($this, "members");
	}
	
	/**
	* Download all submitted files (of all members).
	*/
	function downloadAllObject()
	{
		$this->checkPermission("write");
		
		$members = array();

		foreach($this->object->members_obj->getMembers() as $member_id)
		{
			// update download time
			ilExAssignment::updateTutorDownloadTime($this->object->getId(),
				(int) $_GET["ass_id"], $member_id);

			// get member object (ilObjUser)
			if (ilObject::_exists($member_id))
			{
				$tmp_obj =& ilObjectFactory::getInstanceByObjId($member_id);
				$members[$member_id] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
				unset($tmp_obj);
			}
		}
	
		ilExAssignment::downloadAllDeliveredFiles($this->object->getId(),
			(int) $_GET["ass_id"], $members);
		exit;
	}
	

	function __getMembersOfObject($a_result,$a_type)
	{

		switch($a_type)
		{
			case "usr":
				return $a_result;
			case "grp":
				include_once "./Modules/Group/classes/class.ilObjGroup.php";
	
				$all_members = array();
				foreach($a_result as $group)
				{
					$tmp_grp_obj = ilObjectFactory::getInstanceByRefId($group["id"]);
	
					$members = $tmp_grp_obj->getGroupMemberIds();
					$all_members = array_merge($all_members,$members);
				}
				// FORMAT ARRAY
				$all_members = array_unique($all_members);
				foreach($all_members as $member)
				{
					$result[] = array("id" => $member);
				}
				return $result;
		}
  		return true;
	}

	function __showObjectSelect($a_result,$a_type)
	{
  		include_once "./Services/Object/classes/class.ilObjectFactory.php";
  
		foreach($a_result as $obj)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($obj["id"]);
			$this->tpl->setCurrentBlock("OBJ_SELECT_ROW");
			$this->tpl->setVariable("OBJ_ROW_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("OBJ_ROW_ID",$tmp_obj->getRefId());
			$this->tpl->setVariable("OBJ_ROW_DESCRIPTION",$tmp_obj->getDescription());
			$this->tpl->parseCurrentBlock();
	
			unset($tmp_obj);
		}
		$this->tpl->setCurrentBlock("OBJ_SELECT");
		$this->tpl->setVariable("OBJ_SELECT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("OBJ_SELECT_DESCRIPTION",$this->lng->txt("description"));
  
		$this->tpl->setVariable("OBJ_BTN1_VALUE",$this->lng->txt("select"));
		$this->tpl->setVariable("OBJ_BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Send assignment per mail to participants
	*/
	function sendMembersObject()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		if(!count($_POST["member"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
		}
		else
		{			
			// team upload?
			if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$members = array();
				foreach(array_keys($_POST["member"]) as $user_id)
				{					
					$tmembers = ilExAssignment::getTeamMembersByAssignmentId($this->ass->getId(), $user_id);
					foreach($tmembers as $tuser_id)
					{
						$members[$tuser_id] = 1;
					}
				}
			}
			else
			{
				$members = $_POST["member"];
			}
			
			$this->object->sendAssignment($this->object->getId(),
				(int) $_GET["ass_id"], $members);
			
			ilUtil::sendSuccess($this->lng->txt("exc_sent"),true);
		}
		$ilCtrl->redirect($this, "members");
	}

	/**
	* Confirm deassigning members
	*/
	function confirmDeassignMembersObject()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;
		
		$this->checkPermission("write");
		$ilTabs->activateTab("grades");
			
		if (!is_array($_POST["member"]) || count($_POST["member"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "members");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("exc_msg_sure_to_deassign_participant"));
			$cgui->setCancel($lng->txt("cancel"), "members");
			$cgui->setConfirm($lng->txt("remove"), "deassignMembers");
			
			// team upload?
			if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$members = array();
				foreach(array_keys($_POST["member"]) as $user_id)
				{					
					$tmembers = ilExAssignment::getTeamMembersByAssignmentId($this->ass->getId(), $user_id);
					foreach($tmembers as $tuser_id)
					{
						$members[$tuser_id] = 1;
					}
				}
			}
			else
			{
				$members = $_POST["member"];
			}			
			
			include_once("./Services/User/classes/class.ilUserUtil.php");
			foreach ($members as $k => $m)
			{								
				$cgui->addItem("member[$k]", $m,
					ilUserUtil::getNamePresentation((int) $k, false, false, "", true));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Deassign members from exercise 
	 */
	function deassignMembersObject()
	{
		global $ilCtrl, $lng;
		
		$this->checkPermission("write");
		
		if(is_array($_POST["member"]))
		{
			foreach(array_keys($_POST["member"]) as $usr_id)
			{
				$this->object->members_obj->deassignMember((int) $usr_id);
			}
			ilUtil::sendSuccess($lng->txt("exc_msg_participants_removed"), true);
			$ilCtrl->redirect($this, "members");
		}
  		else
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
			$ilCtrl->redirect($this, "members");
		}
	}

	function saveCommentsObject() 
	{
		$this->checkPermission("write");
		
		if(!isset($_POST['comments_value']))
		{
			return;
		}
  
		$this->object->members_obj->setNoticeForMember($_GET["member_id"],
			ilUtil::stripSlashes($_POST["comments_value"]));
		ilUtil::sendSuccess($this->lng->txt("exc_members_comments_saved"));
		$this->membersObject();
	}


	/**
	 * Save assignment status (participant view)
	 */
	function saveStatusParticipantObject()
	{
		$this->saveStatusObject(true);
	}
	
	function saveStatusAllObject()
	{
		$this->saveStatusObject(false, true);
	}
	
	/**
	 * Save status of selecte members 
	 */
	function saveStatusObject($a_part_view = false, $a_force_all = false)
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
//		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		$saved_for = array();
				
		foreach($_POST["id"] as $key => $value)
		{
			if (!$a_part_view)
			{						
				if (!$a_force_all && $_POST["member"][$key] != "1")
				{
					continue;
				}
				else
				{					
					$uname = ilObjUser::_lookupName($key);
					$saved_for[] = $uname["lastname"].", ".$uname["firstname"];					
				}
			}
			if (!$a_part_view)
			{
				$ass_id = (int) $_GET["ass_id"];
				$user_id = (int) $key;
			}
			else
			{
				$ass_id = (int) $key;
				$user_id = (int) $_GET["part_id"];
			}
			
			// team upload?
			if(is_object($this->ass) and $this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$team_id = $this->ass->getTeamId($user_id);
				$user_ids = $this->ass->getTeamMembers($team_id);		
				
				if (count($_POST["member"]) > 0)
				{
					foreach($user_ids as $user_id)
					{
						if($user_id != $key)
						{
							$uname = ilObjUser::_lookupName($user_id);
							$saved_for[] = $uname["lastname"].", ".$uname["firstname"];
						}
					}
				}
			}
			else
			{
				$user_ids = array($user_id);
			}
			
			foreach($user_ids as $user_id)
			{								
				ilExAssignment::updateStatusOfUser($ass_id, $user_id,
					ilUtil::stripSlashes($_POST["status"][$key]));
				ilExAssignment::updateNoticeForUser($ass_id, $user_id,
					ilUtil::stripSlashes($_POST["notice"][$key]));

				if (ilUtil::stripSlashes($_POST['mark'][$key]) != 
					ilExAssignment::lookupMarkOfUser($ass_id, $user_id))
				{
					ilExAssignment::updateStatusTimeOfUser($ass_id, $user_id);
				}

				ilExAssignment::updateMarkOfUser($ass_id, $user_id,
					ilUtil::stripSlashes($_POST['mark'][$key]));
				
				/*
				ilExAssignment::updateCommentForUser($ass_id, $user_id,
					ilUtil::stripSlashes($_POST['lcomment'][$key]));				 
				*/
			}			
		}
		
		if (count($saved_for) > 0)
		{
			$save_for_str = "(".implode($saved_for, " - ").")";
		}
		if($save_for_str || $a_part_view)
		{
			ilUtil::sendSuccess($this->lng->txt("exc_status_saved")." ".$save_for_str,true);
		}		
		if (!$a_part_view)
		{
			$ilCtrl->redirect($this, "members");
		}
		else
		{
			$ilCtrl->redirect($this, "showParticipant");
		}
	}

	function __getDateSelect($a_type,$a_selected)
	{
  		switch($a_type)
		{
			case "hour":
				for($i=0; $i<24; $i++)
				{
					$hours[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_hour",$hours,false,true);
	
			case "minutes":
				for($i=0;$i<60;$i++)
				{
					$minutes[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_minutes",$minutes,false,true);
	
			case "day":
				for($i=1; $i<32; $i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_day",$days,false,true);
	
			case "month":
				for($i=1; $i<13; $i++)
				{
					$month[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_month",$month,false,true);
	
			case "year":
				for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
				{
					$year[$i] = $i;
				}
				return ilUtil::formSelect($a_selected,"d_year",$year,false,true);
		}
	}

	function __filterAssignedUsers($a_result)
	{
		foreach($a_result as $user)
		{
			if(!$this->object->members_obj->isAssigned($user["id"]))
			{
				$filtered[] = $user;
			}
		}
	
  		return $filtered ? $filtered : array();
	}
	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function addSubmissionSubTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl;
		
		$ilTabs->addSubTab("assignment", $lng->txt("exc_assignment_view"),
			$ilCtrl->getLinkTarget($this, "members"));
		$ilTabs->addSubTab("participant", $lng->txt("exc_participant_view"),
			$ilCtrl->getLinkTarget($this, "showParticipant"));
		$ilTabs->addSubTab("grades", $lng->txt("exc_grades_overview"),
			$ilCtrl->getLinkTarget($this, "showGradesOverview"));
		$ilTabs->activateSubTab($a_activate);
	}

	/**
	 * Add subtabs of content view
	 *
	 * @param	object		$tabs_gui		ilTabsGUI object
	 */
	function addContentSubTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl, $ilAccess;
		
		$ilTabs->addSubTab("content", $lng->txt("view"),
			$ilCtrl->getLinkTarget($this, "showOverview"));
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$ilTabs->addSubTab("list_assignments", $lng->txt("edit"),
				$ilCtrl->getLinkTarget($this, "listAssignments"));
		}
		$ilTabs->activateSubTab($a_activate);
	}

	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs($tabs_gui)
	{
		global $ilAccess, $ilUser, $lng, $ilHelp;
  
		$ilHelp->setScreenIdComponent("exc");
		
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$tabs_gui->addTab("content",
				$lng->txt("exc_assignments"),
				$this->ctrl->getLinkTarget($this, "showOverview"));
		}

		$next_class = strtolower($this->ctrl->getNextClass());
		if ($ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$tabs_gui->addTab("info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}

		// edit properties
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			/*$tabs_gui->addTab("assignments",
				$lng->txt("exc_edit_assignments"),
				$this->ctrl->getLinkTarget($this, 'listAssignments'));*/
			
			$tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, 'edit'));
			
			$tabs_gui->addTab("grades",
				$lng->txt("exc_submissions_and_grades"),
				$this->ctrl->getLinkTarget($this, 'members'));
		}

		// learning progress
		$save_sort_order = $_GET["sort_order"];		// hack, because exercise sort parameters
		$save_sort_by = $_GET["sort_by"];			// must not be forwarded to learning progress
		$save_offset = $_GET["offset"];
		$_GET["offset"] = $_GET["sort_by"] = $_GET["sort_order"] = "";
		
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTab('learning_progress',
				$lng->txt('learning_progress'),
				$this->ctrl->getLinkTargetByClass(array('ilobjexercisegui','illearningprogressgui'),''));
		}

		$_GET["sort_order"] = $save_sort_order;		// hack, part ii
		$_GET["sort_by"] = $save_sort_by;
		$_GET["offset"] = $save_offset;

		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$tabs_gui->addTab("export",
				$lng->txt("export"),
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}


		// permissions
		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			$tabs_gui->addTab('permissions',
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"));
		}
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser, $ilTabs, $lng;
		
		$ilTabs->activateTab("info");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		$info->enablePrivateNotes();
		
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
			$info->setBlockProperty("news", "settings", true);
		}
		
		// standard meta data
		//$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());

		// instructions
		$info->addSection($this->lng->txt("exc_overview"));
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getAssignmentDataOfExercise($this->object->getId());
		$cnt = 0;
		$mcnt = 0;
		foreach ($ass as $a)
		{
			$cnt++;
			if ($a["mandatory"])
			{
				$mcnt++;
			}
		}
		$info->addProperty($lng->txt("exc_assignments"), $cnt);
		$info->addProperty($lng->txt("exc_mandatory"), $mcnt);
		if ($this->object->getPassMode() != "nr")
		{
			$info->addProperty($lng->txt("exc_pass_mode"),
				$lng->txt("exc_msg_all_mandatory_ass"));
		}
		else
		{
			$info->addProperty($lng->txt("exc_pass_mode"),
				sprintf($lng->txt("exc_msg_min_number_ass"), $this->object->getPassNr()));
		}

		// feedback from tutor
		include_once("Services/Tracking/classes/class.ilLPMarks.php");
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			$lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
			$mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
			//$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $ilUser->getId());
			$st = $this->object->determinStatusOfUser($ilUser->getId());
			$status = $st["overall_status"];
			if ($lpcomment != "" || $mark != "" || $status != "notgraded")
			{
				$info->addSection($this->lng->txt("exc_feedback_from_tutor"));
				if ($lpcomment != "")
				{
					$info->addProperty($this->lng->txt("exc_comment"),
						$lpcomment);
				}
				if ($mark != "")
				{
					$info->addProperty($this->lng->txt("exc_mark"),
						$mark);
				}

				//if ($status == "") 
				//{
				//  $info->addProperty($this->lng->txt("status"),
				//		$this->lng->txt("message_no_delivered_files"));				
				//}
				//else
				if ($status != "notgraded")
				{
					$img = '<img src="'.ilUtil::getImagePath("scorm/".$status.".svg").'" '.
						' alt="'.$lng->txt("exc_".$status).'" title="'.$lng->txt("exc_".$status).
						'" />';

					$add = "";
					if ($st["failed_a_mandatory"])
					{
						$add = " (".$lng->txt("exc_msg_failed_mandatory").")";
					}
					else if ($status == "failed")
					{
						$add = " (".$lng->txt("exc_msg_missed_minimum_number").")";
					}
					$info->addProperty($this->lng->txt("status"),
						$img." ".$this->lng->txt("exc_".$status).$add);
				}
			}
		}
		
		// forward the command
		$this->ctrl->forwardCommand($info);
	}
	
	function editObject() 
	{
		$this->setSettingsSubTabs();
		$this->tabs_gui->activateSubTab("edit");
		return parent::editObject();
	}
	
	protected function setSettingsSubTabs()
	{
		$this->tabs_gui->addSubTab("edit",
			$this->lng->txt("general_settings"),
			$this->ctrl->getLinkTarget($this, "edit"));
		
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if(ilCertificate::isActive())
		{
			$this->tabs_gui->addSubTab("certificate",
				$this->lng->txt("certificate"),
				$this->ctrl->getLinkTarget($this, "certificate"));		
		}
	}

	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	public static function _goto($a_target, $a_raw)
	{
		global $ilErr, $lng, $ilAccess;

		$ass_id = null;
		$parts = explode("_", $a_raw);
		if(sizeof($parts) == 2)
		{
			$ass_id = (int)$parts[1];
		}
		
		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			if($ass_id)
			{
				$_GET["ass_id_goto"] = $ass_id;
			}
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "showOverview";
			$_GET["baseClass"] = "ilExerciseHandlerGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "infoScreen";
			$_GET["baseClass"] = "ilExerciseHandlerGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
		
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}		

	/**
	* Add locator item
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
		}
	}
	
	////
	//// Assignments Editing
	////
	
	/**
	 * List assignments
	 */
	function listAssignmentsObject()
	{
		global $tpl, $ilTabs, $ilToolbar, $lng, $ilCtrl;
		
		$this->checkPermission("write");
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("list_assignments");
		
		$ilToolbar->addButton($lng->txt("exc_add_assignment"),
			$ilCtrl->getLinkTarget($this, "addAssignment"));
		
		include_once("./Modules/Exercise/classes/class.ilAssignmentsTableGUI.php");
		$t = new ilAssignmentsTableGUI($this, "listAssignments", $this->object);
		$tpl->setContent($t->getHTML());
	}
	
	/**
	 * Create assignment
	 */
	function addAssignmentObject()
	{
		global $tpl, $ilTabs;
		
		$this->checkPermission("write");
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("list_assignments");
		
		$this->initAssignmentForm("create");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init assignment form.
	*
	* @param        int        $a_mode        "create"/"edit"
	*/
	public function initAssignmentForm($a_mode = "create")
	{
		global $lng, $ilCtrl, $ilSetting;

		// init form
		$lng->loadLanguageModule("form");
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTableWidth("600px");
		if ($a_mode == "edit")
		{
			$this->form->setTitle($lng->txt("exc_edit_assignment"));
		}
		else
		{
			$this->form->setTitle($lng->txt("exc_new_assignment"));
		}
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		
		// type
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$types = array(ilExAssignment::TYPE_UPLOAD => $this->lng->txt("exc_type_upload"),
			ilExAssignment::TYPE_UPLOAD_TEAM => $this->lng->txt("exc_type_upload_team"),
			ilExAssignment::TYPE_TEXT => $this->lng->txt("exc_type_text"));
		if(!$ilSetting->get('disable_wsp_blogs'))
		{
			$types[ilExAssignment::TYPE_BLOG] = $this->lng->txt("exc_type_blog");
		}
		if($ilSetting->get('user_portfolios'))
		{
			$types[ilExAssignment::TYPE_PORTFOLIO] = $this->lng->txt("exc_type_portfolio");
		}
		if(sizeof($types) > 1)
		{
			$ty = new ilSelectInputGUI($this->lng->txt("exc_assignment_type"), "type");
			$ty->setOptions($types);
			$ty->setRequired(true);
		}
		else
		{
			$ty = new ilHiddenInputGUI("type");
			$ty->setValue(ilExAssignment::TYPE_UPLOAD);			
		}
		$this->form->addItem($ty);
		
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// start time y/n
		$cb = new ilCheckboxInputGUI($this->lng->txt("exc_start_time"), "start_time_cb");
		$this->form->addItem($cb);
		
			// start time
			$edit_date = new ilDateTimeInputGUI("", "start_time");
			$edit_date->setShowTime(true);
			$cb->addSubItem($edit_date);
		
			
		// deadline y/n
		$dcb = new ilCheckboxInputGUI($this->lng->txt("exc_deadline"), "deadline_cb");
		$dcb->setChecked(true);
		$this->form->addItem($dcb);

			// Deadline
			$edit_date = new ilDateTimeInputGUI($lng->txt(""), "deadline");
			$edit_date->setShowTime(true);
			$dcb->addSubItem($edit_date);

		// mandatory
		$cb = new ilCheckboxInputGUI($this->lng->txt("exc_mandatory"), "mandatory");
		$cb->setInfo($this->lng->txt("exc_mandatory_info"));
		$cb->setChecked(true);
		$this->form->addItem($cb);

		// Work Instructions
		$desc_input = new ilTextAreaInputGUI($lng->txt("exc_instruction"), "instruction");
		$desc_input->setRows(20);
		$desc_input->setUseRte(true);				
		$desc_input->setRteTagSet("mini");		
		$this->form->addItem($desc_input);		
								
		// files
		if ($a_mode == "create")
		{
			$files = new ilFileWizardInputGUI($this->lng->txt('objs_file'),'files');
			$files->setFilenames(array(0 => ''));
			$this->form->addItem($files);						
		}
				
		// peer review
		$peer = new ilCheckboxInputGUI($lng->txt("exc_peer_review"), "peer");		
		$peer->setInfo($this->lng->txt("exc_peer_review_ass_setting_info"));
		$this->form->addItem($peer);
		
		if ($a_mode == "create")
		{
			$peer->setInfo($lng->txt("exc_peer_review_info"));
		}
		
		$peer_min = new ilNumberInputGUI($lng->txt("exc_peer_review_min_number"), "peer_min");
		$peer_min->setInfo($lng->txt("exc_peer_review_min_number_info"));
		$peer_min->setRequired(true);
		$peer_min->setValue(5);
		$peer_min->setSize(3);
		$peer_min->setValue(2);
		$peer->addSubItem($peer_min);
		
		$peer_dl = new ilDateTimeInputGUI($lng->txt("exc_peer_review_deadline"), "peer_dl");
		$peer_dl->setInfo($lng->txt("exc_peer_review_deadline_info"));
		$peer_dl->enableDateActivation("", "peer_dl_tgl");
		$peer_dl->setShowTime(true);
		$peer->addSubItem($peer_dl);
				
		$peer_file = new ilCheckboxInputGUI($lng->txt("exc_peer_review_file"), "peer_file");				
		$peer_file->setInfo($lng->txt("exc_peer_review_file_info"));
		$peer->addSubItem($peer_file);
		
		$peer_prsl = new ilCheckboxInputGUI($lng->txt("exc_peer_review_personal"), "peer_prsl");				
		$peer_prsl->setInfo($lng->txt("exc_peer_review_personal_info"));
		$peer->addSubItem($peer_prsl);
				
		if($a_mode != "create" && // #13745
			$this->ass && 
			$this->ass->getDeadline() && $this->ass->getDeadline() < time())
		{
			$peer_prsl->setDisabled(true);
		}
		
		
		// global feedback
		
		$fb = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file"), "fb");				
		$this->form->addItem($fb);
		
		$fb_file = new ilFileInputGUI($lng->txt("file"), "fb_file");
		$fb_file->setRequired(true); // will be disabled on update if file exists (see below)
		// $fb_file->setAllowDeletion(true); makes no sense if required (overwrite or keep)
		$fb->addSubItem($fb_file);
		
		// #15467
		if($a_mode != "create" && 
			$this->ass && 
			$this->ass->getFeedbackFile())
		{
			$fb_file->setRequired(false); 
		}
		
		$fb_date = new ilRadioGroupInputGUI($lng->txt("exc_global_feedback_file_date"), "fb_date");
		$fb_date->setRequired(true);
		$fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_deadline"), ilExAssignment::FEEDBACK_DATE_DEADLINE));
		$fb_date->addOption(new ilRadioOption($lng->txt("exc_global_feedback_file_date_upload"), ilExAssignment::FEEDBACK_DATE_SUBMISSION));
		$fb->addSubItem($fb_date);
		
		$fb_cron = new ilCheckboxInputGUI($lng->txt("exc_global_feedback_file_cron"), "fb_cron");
		$fb_cron->setInfo($lng->txt("exc_global_feedback_file_cron_info"));
		$fb->addSubItem($fb_cron);
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("saveAssignment", $lng->txt("save"));
			$this->form->addCommandButton("listAssignments", $lng->txt("cancel"));
		}
		else
		{
			$this->form->addCommandButton("updateAssignment", $lng->txt("save"));
			$this->form->addCommandButton("listAssignments", $lng->txt("cancel"));
		}
	}
	
	/**
	* Save assignment
	*
	*/
	public function saveAssignmentObject()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;
		
		$this->checkPermission("write");
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("list_assignments");
	
		$this->initAssignmentForm("create");
		if ($this->form->checkInput())
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			
			// additional checks
			
			$valid = true;
			
			if ($_POST["start_time_cb"] && $_POST["deadline_cb"])
			{
				// check whether start date is before end date
				$start_date =
					$this->form->getItemByPostVar("start_time")->getDate();
				$end_date =
					$this->form->getItemByPostVar("deadline")->getDate();
				if ($start_date->get(IL_CAL_UNIX) >=
					$end_date->get(IL_CAL_UNIX))
				{					
					$this->form->getItemByPostVar("start_time")
						->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
					$this->form->getItemByPostVar("deadline")
						->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
					$valid = false;		
				}
			}
			
			if($_POST["type"] == ilExAssignment::TYPE_UPLOAD_TEAM && $_POST["peer"])
			{				
				$this->form->getItemByPostVar("peer")
					->setAlert($lng->txt("exc_team_upload_not_supported"));
				$valid = false;
			}
			
			if(!$_POST["deadline_cb"])
			{
				if($_POST["peer"])
				{
					$this->form->getItemByPostVar("peer")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}					
				if($_POST["fb"])
				{
					$this->form->getItemByPostVar("fb")
						->setAlert($lng->txt("exc_needs_deadline"));
					$valid = false;
				}				 
			}
			else
			{
				if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM &&
					$_POST["peer"] && 
					$_POST["peer_dl_tgl"])
				{
					$peer_dl =	$this->form->getItemByPostVar("peer_dl")->getDate();					
					$peer_dl = $peer_dl->get(IL_CAL_UNIX);										
					$end_date = $this->form->getItemByPostVar("deadline")->getDate();
					$end_date = $end_date->get(IL_CAL_UNIX);
					
					// #13877
					if ($peer_dl < $end_date)
					{
						$this->form->getItemByPostVar("peer_dl")
							->setAlert($lng->txt("exc_peer_deadline_mismatch"));
						$valid = false;
					}
				}			
			}
			
			if(!$valid)
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
				$this->form->setValuesByPost();		
				$tpl->setContent($this->form->getHtml());
				return;
			}
			
			$ass = new ilExAssignment();
			$ass->setTitle($_POST["title"]);
			$ass->setInstruction($_POST["instruction"]);
			$ass->setExerciseId($this->object->getId());
			$ass->setMandatory($_POST["mandatory"]);
			$ass->setType($_POST["type"]);
			
			if ($_POST["start_time_cb"])
			{
				$date =
					$this->form->getItemByPostVar("start_time")->getDate();
				$ass->setStartTime($date->get(IL_CAL_UNIX));
			}
			else
			{
				$ass->setStartTime(null);
			}
			
			// deadline
			if ($_POST["deadline_cb"])
			{
				$date =
					$this->form->getItemByPostVar("deadline")->getDate();
				$ass->setDeadline($date->get(IL_CAL_UNIX));
			}
			else
			{
				$ass->setDeadline(null);
			}
			
			if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$ass->setPeerReview($_POST["peer"]);
				$ass->setPeerReviewMin($_POST["peer_min"]);
				$ass->setPeerReviewFileUpload($_POST["peer_file"]);
				
				if($ass->getDeadline() && $ass->getDeadline() > time())
				{
					$ass->setPeerReviewPersonalized($_POST["peer_prsl"]);
				}
										
				if($_POST["peer_dl_tgl"])
				{
					$peer_dl =	$this->form->getItemByPostVar("peer_dl")->getDate();
					$ass->setPeerReviewDeadline($peer_dl->get(IL_CAL_UNIX));
				}
				else
				{
					$ass->setPeerReviewDeadline(null);
				}		
			}
						
			$ass->setFeedbackCron($_POST["fb_cron"]); // #13380
			$ass->setFeedbackDate($_POST["fb_date"]);

			$ass->save();
			
			// save files
			$ass->uploadAssignmentFiles($_FILES["files"]);
									
			if($_FILES["fb_file"]["tmp_name"])
			{
				$ass->handleFeedbackFileUpload($_FILES["fb_file"]);
				$ass->update();
			}
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
						
			if($ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
			{				
				if(sizeof(ilExAssignment::getAdoptableTeamAssignments($this->object->getId(), $ass->getId())))
				{
					$ilCtrl->setParameter($this, "ass_id", $ass->getId());
					$ilCtrl->redirect($this, "adoptTeamAssignmentsForm");
				}
			}			
			
			$ilCtrl->redirect($this, "listAssignments");
		}
		else
		{
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}

	/**
	 * Edit assignment
	 */
	function editAssignmentObject()
	{
		global $tpl, $ilTabs, $tpl;
		
		$this->checkPermission("write");
		
		$this->setAssignmentHeader();
		$ilTabs->activateTab("ass_settings");
		
		$this->initAssignmentForm("edit");
		$this->getAssignmentValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Get current values for assignment from 
	 *
	 */
	public function getAssignmentValues()
	{
		$values = array();
	
		$ass = new ilExAssignment($_GET["ass_id"]);
		$values["title"] = $ass->getTitle();
		if ($ass->getStartTime() > 0)
		{
			$values["start_time_cb"] = true;
		}
		$values["mandatory"] = $ass->getMandatory();
		$values["instruction"] = $ass->getInstruction();
		$values["type"] = $ass->getType();
		if ($ass->getDeadline() > 0)
		{
			$values["deadline_cb"] = true;
		}			
		if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$this->form->removeItemByPostVar("peer");
			$this->form->removeItemByPostVar("peer_min");			
			$this->form->removeItemByPostVar("peer_dl");			
		}
		else
		{			
			if ($ass->getPeerReviewDeadline() > 0)
			{
				$values["peer_dl_tgl"] = true;
				$peer_dl_date = new ilDateTime($ass->getPeerReviewDeadline(), IL_CAL_UNIX);
				$peer_dl = $this->form->getItemByPostVar("peer_dl");
				$peer_dl->setDate($peer_dl_date);
			}					
		}		
		$this->form->setValuesByArray($values);

		if ($ass->getStartTime() > 0)
		{
			$edit_date = new ilDateTime($ass->getStartTime(), IL_CAL_UNIX);
			$ed_item = $this->form->getItemByPostVar("start_time");
			$ed_item->setDate($edit_date);
		}
		
		if($ass->getFeedbackFile())
		{						
			$this->form->getItemByPostVar("fb")->setChecked(true);			
			$this->form->getItemByPostVar("fb_file")->setValue(basename($ass->getFeedbackFilePath()));			
		}
		$this->form->getItemByPostVar("fb_cron")->setChecked($ass->hasFeedbackCron());			
		$this->form->getItemByPostVar("fb_date")->setValue($ass->getFeedbackDate());			
		
		$this->handleDisabledAssignmentFields($ass, $this->form);	
	}
	
	protected function handleDisabledAssignmentFields(ilExAssignment $a_ass, ilPropertyFormGUI $a_form)
	{					
		// potentially disabled elements are initialized here to re-use this 
		// method after setValuesByPost() - see updateAssignmentObject()
					
		// if there are any submissions we cannot change type anymore
		if(sizeof(ilExAssignment::getAllDeliveredFiles($this->object->getId(), $a_ass->getId())) ||
			$a_ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$a_form->getItemByPostVar("type")->setDisabled(true);
		}
		
		if($a_ass->getDeadline() > 0)
		{
			$a_form->getItemByPostVar("deadline_cb")->setChecked(true);
			$edit_date = new ilDateTime($a_ass->getDeadline(), IL_CAL_UNIX);
			$ed_item = $a_form->getItemByPostVar("deadline");
			$ed_item->setDate($edit_date);			
		}
		
		// team assignments do not support peer review
		if($a_ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			return;
		}
		
		$a_form->getItemByPostVar("peer")->setChecked($a_ass->getPeerReview());
		$a_form->getItemByPostVar("peer_min")->setValue($a_ass->getPeerReviewMin());
		$a_form->getItemByPostVar("peer_file")->setChecked($a_ass->hasPeerReviewFileUpload());
		$a_form->getItemByPostVar("peer_prsl")->setChecked($a_ass->hasPeerReviewPersonalized());						
				
		// with no active peer review there is nothing to protect
		if(!$a_ass->getPeerReview())
		{
			return;
		}
		
		// #14450 
		if($a_ass->hasPeerReviewGroups())
		{
			// deadline(s) are past and must not change
			$a_form->getItemByPostVar("deadline_cb")->setDisabled(true);			
			$a_form->getItemByPostVar("deadline")->setDisabled(true);	
			
			// JourFixe, 2015-05-11 - editable again
			// $a_form->getItemByPostVar("peer_dl")->setDisabled(true);

			$a_form->getItemByPostVar("peer")->setDisabled(true);			   
			$a_form->getItemByPostVar("peer_min")->setDisabled(true);				
			$a_form->getItemByPostVar("peer_file")->setDisabled(true);
			$a_form->getItemByPostVar("peer_prsl")->setDisabled(true);														
		}	
		
	}

	/**
	 * Update assignment
	 *
	 */
	public function updateAssignmentObject()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;
		
		$this->checkPermission("write");
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("list_assignments");
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");			
		$ass = new ilExAssignment($_GET["ass_id"]);
			
		$this->initAssignmentForm("edit");
		if ($this->form->checkInput())
		{						
			// #14450
			$protected_peer_review_groups = false;
			if($ass->getPeerReview() &&
				$ass->hasPeerReviewGroups())
			{
				$protected_peer_review_groups = true;
				
				// checkInput() will add alert to disabled fields
				$this->form->getItemByPostVar("deadline")->setAlert(null);			
				$this->form->getItemByPostVar("peer_min")->setAlert(null);
			}
			
			// additional checks
			
			$valid = true;	
			
			if(!$protected_peer_review_groups)
			{
				$peer = $_POST["peer"];
				
				if ($_POST["deadline_cb"])
				{
					$end_date =
						$this->form->getItemByPostVar("deadline")->getDate()->get(IL_CAL_UNIX);	
				}
				
				if ($_POST["start_time_cb"] && $end_date)
				{
					// check whether start date is before end date
					$start_date =
						$this->form->getItemByPostVar("start_time")->getDate()->get(IL_CAL_UNIX);
					if ($start_date >= $end_date)
					{					
						$this->form->getItemByPostVar("start_time")
							->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
						$this->form->getItemByPostVar("deadline")
							->setAlert($lng->txt("exc_start_date_should_be_before_end_date"));
						$valid = false;					
					}
				}

				if(!$end_date)
				{
					if($_POST["peer"])
					{
						$this->form->getItemByPostVar("peer")
							->setAlert($lng->txt("exc_needs_deadline"));
						$valid = false;
					}	
					if($_POST["fb"] && $_POST["fb_date"] == ilExAssignment::FEEDBACK_DATE_DEADLINE)
					{
						$this->form->getItemByPostVar("fb")
							->setAlert($lng->txt("exc_needs_deadline"));
						$valid = false;
					}	
				}							
			}
			else 
			{
				$peer = true;
				$end_date = $ass->getDeadline();
			}
			
			if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM &&
				$peer && 
				$_POST["peer_dl_tgl"])
			{
				$peer_dl =	$this->form->getItemByPostVar("peer_dl")->getDate();					
				$peer_dl = $peer_dl->get(IL_CAL_UNIX);		
				
				// #13877
				if ($peer_dl < $end_date)
				{
					$this->form->getItemByPostVar("peer_dl")
						->setAlert($lng->txt("exc_peer_deadline_mismatch"));
					$valid = false;
				}
			}	
			
			if(!$valid)
			{
				ilUtil::sendFailure($lng->txt("form_input_not_valid"));
				$this->form->setValuesByPost();		
				$this->handleDisabledAssignmentFields($ass, $this->form);	
				$tpl->setContent($this->form->getHtml());
				return;
			}
						
			$ass->setTitle($_POST["title"]);
			$ass->setInstruction($_POST["instruction"]);
			$ass->setExerciseId($this->object->getId());
			$ass->setMandatory($_POST["mandatory"]);
			$ass->setType($_POST["type"]);
			
			if ($_POST["start_time_cb"])
			{
				$date =
					$this->form->getItemByPostVar("start_time")->getDate();
				$ass->setStartTime($date->get(IL_CAL_UNIX));
			}
			else
			{
				$ass->setStartTime(null);
			}
			
			if(!$protected_peer_review_groups)
			{
				// deadline
				if ($_POST["deadline_cb"])
				{
					$date =	$this->form->getItemByPostVar("deadline")->getDate();
					$ass->setDeadline($date->get(IL_CAL_UNIX));
				}
				else
				{
					$ass->setDeadline(null);
				}

				if($_POST["type"] != ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$ass->setPeerReview($_POST["peer"]);
					$ass->setPeerReviewMin($_POST["peer_min"]);
					$ass->setPeerReviewFileUpload($_POST["peer_file"]);

					if($ass->getDeadline() && $ass->getDeadline() > time())
					{
						$ass->setPeerReviewPersonalized($_POST["peer_prsl"]);
					}
				}
			}
			
			if($_POST["peer_dl_tgl"])
			{
				$peer_dl = $this->form->getItemByPostVar("peer_dl")->getDate();				
				$ass->setPeerReviewDeadline($peer_dl->get(IL_CAL_UNIX));					
			}
			else
			{
				$ass->setPeerReviewDeadline(null);
			}
			
			if(!$_POST["fb"] ||
				$this->form->getItemByPostVar("fb_file")->getDeletionFlag())
			{
				$ass->deleteFeedbackFile();
				$ass->setFeedbackFile(null);
			}
			else if($_FILES["fb_file"]["tmp_name"]) // #15189
			{
				$ass->handleFeedbackFileUpload($_FILES["fb_file"]);
			}
						
			$ass->setFeedbackCron($_POST["fb_cron"]); // #13380
			$ass->setFeedbackDate($_POST["fb_date"]);
			
			$ass->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editAssignment");
		}
		else
		{
			$this->form->setValuesByPost();
			$this->handleDisabledAssignmentFields($ass, $this->form);	
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	
	/**
	* Confirm assignments deletion
	*/
	function confirmAssignmentsDeletionObject()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;
		
		$this->checkPermission("write");
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("list_assignments");

		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listAssignments");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("exc_conf_del_assignments"));
			$cgui->setCancel($lng->txt("cancel"), "listAssignments");
			$cgui->setConfirm($lng->txt("delete"), "deleteAssignments");
			
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			
			foreach ($_POST["id"] as $i)
			{
				$cgui->addItem("id[]", $i, ilExAssignment::lookupTitle($i));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete assignments
	 */
	function deleteAssignmentsObject()
	{
		global $ilDB, $ilCtrl, $lng;
		
		$this->checkPermission("write");
		
		$delete = false;
		if (is_array($_POST["id"]))
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			foreach($_POST["id"] as $id)
			{
				$ass = new ilExAssignment(ilUtil::stripSlashes($id));
				$ass->delete();
				$delete = true;
			}
		}
		
		if ($delete)
		{
			ilUtil::sendSuccess($lng->txt("exc_assignments_deleted"), true);
		}
		$ilCtrl->redirect($this, "listAssignments");
	}
	
	/**
	 * Save assignments order
	 */
	function saveAssignmentOrderObject()
	{
		global $lng, $ilCtrl;
		
		$this->checkPermission("write");
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		ilExAssignment::saveAssOrderOfExercise($this->object->getId(), $_POST["order"]);
		
		ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
		$ilCtrl->redirect($this, "listAssignments");
	}
	
	/**
	 * Order by deadline
	 */
	function orderAssignmentsByDeadlineObject()
	{
		global $lng, $ilCtrl;
		
		$this->checkPermission("write");
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		ilExAssignment::orderAssByDeadline($this->object->getId());
		
		ilUtil::sendSuccess($lng->txt("exc_saved_order"), true);
		$ilCtrl->redirect($this, "listAssignments");
	}

	/**
	 * Set assignment header
	 */
	function setAssignmentHeader()
	{
		global $ilTabs, $lng, $ilCtrl, $tpl, $ilHelp;
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$tpl->setTitle(ilExAssignment::lookupTitle((int) $_GET["ass_id"]));
		$tpl->setDescription("");
		
		$ilTabs->clearTargets();
		$ilHelp->setScreenIdComponent("exc");
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listAssignments"));

		$ilTabs->addTab("ass_settings",
			$lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "editAssignment"));

		$ilTabs->addTab("ass_files",
			$lng->txt("exc_instruction_files"),
			$ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));

	}
	
	
	////
	//// Assignments, Learner's View
	////

	/**
	 * Show overview of assignments
	 */
	function showOverviewObject()
	{
		global $tpl, $ilTabs, $ilUser, $ilToolbar;
		
		$this->checkPermission("read");
		
		include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),
			$this->object->getRefId(), 'exc');
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("content");
		
		// show certificate?
		if($this->object->hasUserCertificate($ilUser->getId()))
		{					
			include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
			include_once "./Services/Certificate/classes/class.ilCertificate.php";
			$adapter = new ilExerciseCertificateAdapter($this->object);
			if(ilCertificate::_isComplete($adapter))
			{
				$ilToolbar->addButton($this->lng->txt("certificate"),
					$this->ctrl->getLinkTarget($this, "outCertificate"));
			}
		}	
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
		$acc = new ilAccordionGUI();
		$acc->setId("exc_ow_".$this->object->getId());
		$ass_data = ilExAssignment::getAssignmentDataOfExercise($this->object->getId());
		include_once("./Modules/Exercise/classes/class.ilExAssignmentGUI.php");
		$ass_gui = new ilExAssignmentGUI($this->object);
		
		foreach ($ass_data as $ass)
		{
			// incoming assignment deeplink
			$force_open = false;
			if(isset($_GET["ass_id_goto"]) &&
				(int)$_GET["ass_id_goto"] == $ass["id"])
			{
				$force_open = true;
			}	
			
			$acc->addItem($ass_gui->getOverviewHeader($ass),
				$ass_gui->getOverviewBody($ass),
				$force_open);										
		}
		
		if (count($ass_data) < 2)
		{
			$acc->setBehaviour("FirstOpen");
		}
		else
		{
			$acc->setUseSessionStorage(true);
		}
		
		$tpl->setContent($acc->getHTML());
	}
	
	/**
	 * List all submissions
	 */
	function listPublicSubmissionsObject()
	{
		global $tpl, $ilTabs;
		
		$this->checkPermission("read");
		
		if(!$this->object->getShowSubmissions())
		{
			$this->ctrl->redirect($this, "view");
		}
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("content");
		
		if($this->ass->getType() != ilExAssignment::TYPE_TEXT)
		{		
			include_once("./Modules/Exercise/classes/class.ilPublicSubmissionsTableGUI.php");
			$tab = new ilPublicSubmissionsTableGUI($this, "listPublicSubmissions",
				$this->object, (int) $_GET["ass_id"]);
			$tpl->setContent($tab->getHTML());
		}
		else
		{				
			// #13271
			include_once "Modules/Exercise/classes/class.ilExAssignmentListTextTableGUI.php";
			$tbl = new ilExAssignmentListTextTableGUI($this, "listPublicSubmissions", $this->ass, false, true);		
			$tpl->setContent($tbl->getHTML());		
		}
	}
	
	/**
	 * Export as excel
	 */
	function exportExcelObject()
	{
		$this->checkPermission("write");
		$this->object->exportGradesExcel();
		exit;
	}
	
	/**
	 * Save grades
	 */
	function saveGradesObject()
	{
		global $ilCtrl, $lng;
		
		$this->checkPermission("write");
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		
		if (is_array($_POST["lcomment"]))
		{
			foreach ($_POST["lcomment"] as $k => $v)
			{
				$marks_obj = new ilLPMarks($this->object->getId(), (int) $k);
				$marks_obj->setComment(ilUtil::stripSlashes($v));
				$marks_obj->setMark(ilUtil::stripSlashes($_POST["mark"][$k]));
				$marks_obj->update();
			}
		}
		ilUtil::sendSuccess($lng->txt("exc_msg_saved_grades"), true);
		$ilCtrl->redirect($this, "showGradesOverview");
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
	
	protected function createBlogObject()
	{
		global $ilUser;
		
		$this->checkPermission("read");
		
		// $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		$this->tabs_gui->setTabActive("content");
		$this->addContentSubTabs("content");
		
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_create_blog").": ".$this->ass->getTitle());
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
		
		$this->checkPermission("read");
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
		{
			ilUtil::sendInfo($this->lng->txt("exercise_time_over"));
		}
		
		$tpl = new ilTemplate("tpl.exc_select_resource.html", true, true, "Modules/Exercise");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_select_blog").": ".$this->ass->getTitle());
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
		$blog->setTitle($this->object->getTitle()." - ".$this->ass->getTitle());
		$blog->create();
		
		$tree = new ilWorkspaceTree($ilUser->getId());
		
		$node_id = $tree->insertObject($parent_node, $blog->getId());
		
		$access_handler = new ilWorkspaceAccessHandler($tree);
		$access_handler->setPermissions($parent_node, $node_id);
		
		$this->object->addResourceObject($node_id, $this->ass->getId(), $ilUser->getId());
		
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
				$this->object->addResourceObject($node["wsp_id"], $this->ass->getId(), $ilUser->getId());
				
				ilUtil::sendSuccess($this->lng->txt("exc_blog_selected"), true);
				$this->ctrl->setParameter($this, "blog_id", $node["wsp_id"]);
				$this->ctrl->redirect($this, "askDirectionSubmission");				
			}
		}
		
		ilUtil::sendFailure($this->lng->txt("select_one"));
		return $this->selectPortfolioObject();
	}
	
	/**
	 * remove existing files/submissions for assignment
	 */
	public function removeExistingSubmissions()
	{		
		global $ilUser;
		
		$submitted = ilExAssignment::getDeliveredFiles($this->ass->getExerciseId(), $this->ass->getId(), $ilUser->getId());
		if($submitted)
		{
			$files = array();
			foreach($submitted as $item)
			{
				$files[] = $item["returned_id"];
			}
			ilExAssignment::deleteDeliveredFiles($this->ass->getExerciseId(), $this->ass->getId(), $files, $ilUser->getId());
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
		
		if($this->object && $this->ass)
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
					$this->object->deliverFile($meta, $this->ass->getId(), $ilUser->getId(), true);	

					$this->sendNotifications($this->ass->getId());
					$this->object->handleSubmission($this->ass->getId());	
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
		
		if($this->object && $this->ass)
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
					$this->object->deliverFile($meta, $this->ass->getId(), $ilUser->getId(), true);	

					$this->sendNotifications($this->ass->getId());
					$this->object->handleSubmission($this->ass->getId());	
					return true;
				}
			}
		}
		return false;
	}	
	
	protected function selectPortfolioObject()
	{
		global $ilUser;
		
		$this->checkPermission("read");
		
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));
		
		// $this->tabs_gui->setTabActive("content");
		// $this->addContentSubTabs("content");
		
		if (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0))
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
		
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("exc_select_portfolio").": ".$this->ass->getTitle());
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
		$form->setTitle($this->lng->txt("exc_create_portfolio").": ".$this->ass->getTitle());	
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
		$this->checkPermission("read");
		
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
		$this->checkPermission("read");
		
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
				$title = $this->object->getTitle()." - ".$this->ass->getTitle();
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "exc_id", $this->object->getRefId());
				$this->ctrl->setParameterByClass("ilObjPortfolioGUI", "ass_id", $this->ass->getId());
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
		
		$this->checkPermission("read");
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$portfolio = new ilObjPortfolio();
		$portfolio->setTitle($this->object->getTitle()." - ".$this->ass->getTitle());
		$portfolio->create();
	
		$this->object->addResourceObject($portfolio->getId(), $this->ass->getId(), $ilUser->getId());
		
		ilUtil::sendSuccess($this->lng->txt("exc_portfolio_created"), true);
		$this->ctrl->redirect($this, "showOverview");
	}
	
	protected function setSelectedPortfolioObject()
	{
		global $ilUser;
		
		if($_POST["item"])
		{			
			$this->removeExistingSubmissions();
			$this->object->addResourceObject($_POST["item"], $this->ass->getId(), $ilUser->getId());
						
			ilUtil::sendSuccess($this->lng->txt("exc_portfolio_selected"), true);
			$this->ctrl->setParameter($this, "prtf_id", $_POST["item"]);
			$this->ctrl->redirect($this, "askDirectionSubmission");									
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
	
	function certificateObject()
	{
		$this->setSettingsSubTabs();
		$this->tabs_gui->activateTab("settings");
		$this->tabs_gui->activateSubTab("certificate");
		
		include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
		include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
		$output_gui = new ilCertificateGUI(new ilExerciseCertificateAdapter($this->object));
		$output_gui->certificateEditor();				
	}
	
	function outCertificateObject()
	{
		global $ilUser;
	
		if($this->object->hasUserCertificate($ilUser->getId()))
		{	
			ilUtil::sendFailure($this->lng->txt("msg_failed"));
			$this->showOverviewObject();			
		}
		
		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
		$certificate = new ilCertificate(new ilExerciseCertificateAdapter($this->object));
		$certificate->outCertificate(array("user_id" => $ilUser->getId()));					
	}
	
	protected function initTeamSubmission($a_back_cmd, $a_mandatory_team = true)
	{
		global $ilUser, $ilHelp;
		
		$this->checkPermission("read");
		
		if($a_mandatory_team && $this->ass->getType() != ilExAssignment::TYPE_UPLOAD_TEAM)
		{		
			$this->ctrl->redirect($this, "submissionScreen");
		}
			
		$this->tabs_gui->clearTargets();
		$ilHelp->setScreenIdComponent("exc");
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), 
			$this->ctrl->getLinkTarget($this, $a_back_cmd));
		
		if($this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
		{			
			$this->tabs_gui->addTab("submissions", $this->lng->txt("files"), 
				$this->ctrl->getLinkTarget($this, "submissionScreen"));
		
			$this->tabs_gui->addTab("team", $this->lng->txt("exc_team"), 
				$this->ctrl->getLinkTarget($this, "submissionScreenTeam"));

			$this->tabs_gui->addTab("log", $this->lng->txt("exc_team_log"), 
				$this->ctrl->getLinkTarget($this, "submissionScreenTeamLog"));
			
			$this->tabs_gui->activateTab("team");
			
			$team_id = $this->ass->getTeamId($ilUser->getId());
			
			if(!$team_id)
			{
				$team_id = $this->ass->getTeamId($ilUser->getId(), true);
				
				// #12337
				if (!$this->object->members_obj->isAssigned($ilUser->getId()))
				{
					$this->object->members_obj->assignMember($ilUser->getId());
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
		$read_only = (mktime() > $this->ass->getDeadline() && ($this->ass->getDeadline() != 0));
				
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
					'add_from_container'    => $this->object->getRefId()		
				)
			);
	 	}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamTableGUI.php";
		$tbl = new ilExAssignmentTeamTableGUI($this, "submissionScreenTeam",
			ilExAssignmentTeamTableGUI::MODE_EDIT, $team_id, $this->ass, null, $read_only);
		
		$this->tpl->setContent($tbl->getHTML());				
	}
	
	public function addTeamMemberActionObject($a_user_ids = array())
	{		
		global $ilUser;
		
		$this->checkPermission("read");
		
		if(!count($a_user_ids))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return false;
		}
		
		$team_id = $this->ass->getTeamId($ilUser->getId());
		$has_files = $this->ass->getDeliveredFiles($this->object->getId(), 
			$this->ass->getId(), 
			$ilUser->getId());
		$all_members = $this->ass->getMembersOfAllTeams();
		$members = $this->ass->getTeamMembers($team_id);
		
		foreach($a_user_ids as $user_id)
		{
			if(!in_array($user_id, $all_members))
			{
				$this->ass->addTeamMember($team_id, $user_id, $this->ref_id);
				
				// #14277
				if (!$this->object->members_obj->isAssigned($user_id))
				{
					$this->object->members_obj->assignMember($user_id);
				}

				// see ilObjExercise::deliverFile()
				if($has_files)
				{					
					ilExAssignment::updateStatusReturnedForUser($this->ass->getId(), $user_id, 1);
					ilExerciseMembers::_writeReturned($this->object->getId(), $user_id, 1);
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
		
		$team_id = $this->ass->getTeamId($ilUser->getId());
		$members = $this->ass->getTeamMembers($team_id);
		
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

		$files = ilExAssignment::getDeliveredFiles($this->ass->getExerciseId(), 
			$this->ass->getId(), $ilUser->getId());
		
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
		
		$team_id = $this->ass->getTeamId($ilUser->getId());
		$members = $this->ass->getTeamMembers($team_id);
		
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
			$this->ass->removeTeamMember($team_id, $user_id, $this->ref_id);		
			
			ilExAssignment::updateStatusReturnedForUser($this->ass->getId(), $user_id, 0);
			ilExerciseMembers::_writeReturned($this->object->getId(), $user_id, 0);
			
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
			$this->ass->getTeamId($user_id, true);		
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
		
			$team_id = ilExAssignment::getTeamIdByAssignment($this->ass->getId(), (int)$_GET["lmem"]);
			
			$this->ctrl->saveParameter($this, "lmem");
		}
		else
		{
			$this->addSubmissionSubTabs("participant");
			
			$this->tabs_gui->setBackTarget($this->lng->txt("back"),
				$this->ctrl->getLinkTarget($this, "showParticipant"));
		
			$team_id = ilExAssignment::getTeamIdByAssignment($this->ass->getId(), (int)$_GET["lpart"]);
			
			$this->ctrl->saveParameter($this, "lpart");
		}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentTeamLogTableGUI.php";
		$tbl = new ilExAssignmentTeamLogTableGUI($this, "showTeamLog",
			$team_id);
		
		$this->tpl->setContent($tbl->getHTML());						
	}
	
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
			$this->ass->getType() != ilExAssignment::TYPE_TEXT ||
			($this->ass->getDeadline() && $this->ass->getDeadline() - time() < 0))				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$this->checkPermission("read");		
			
		//$ilTabs->activateTab("content");
		//$this->addContentSubTabs("content");

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"),
			$this->ctrl->getLinkTarget($this, "showOverview"));

		global $ilHelp;
		$ilHelp->setScreenIdComponent("exc");
		$ilHelp->setScreenId("text_submission");

		if($this->ass->getDeadline())
		{
			ilUtil::sendInfo($this->lng->txt("exc_edit_until").": ".
				ilDatePresentation::formatDate(new ilDateTime($this->ass->getDeadline(),IL_CAL_UNIX)));
		}
		
		if(!$a_form)
		{
			$a_form = $this->initAssignmentTextForm($this->ass);		

			$files = ilExAssignment::getDeliveredFiles($this->ass->getExerciseId(), $this->ass->getId(), $ilUser->getId());
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
		
		$times_up = ($this->ass->getDeadline() && $this->ass->getDeadline() - time() < 0);
		
		if(!$this->ass || 
			$this->ass->getType() != ilExAssignment::TYPE_TEXT ||
			$times_up)
		{
			if($times_up)
			{
				ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			}
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$this->checkPermission("read");		
		
		$form = $this->initAssignmentTextForm($this->ass);	
		
		// we are not using a purifier, so we have to set the valid RTE tags
		// :TODO: 
		include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
		$rte = $form->getItemByPostVar("atxt");
		$rte->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("exc_ass"));
		
		if($form->checkInput())
		{			
			$text = trim($form->getInput("atxt"));	
									
			$existing = (bool)ilExAssignment::getDeliveredFiles($this->ass->getExerciseId(), 
				$this->ass->getId(), $ilUser->getId());			
												
			$returned_id = $this->object->updateTextSubmission(
				$this->ass->getExerciseId(), 
				$this->ass->getId(), 
				$ilUser->getId(), 
				// mob src to mob id
				ilRTE::_replaceMediaObjectImageSrc($text, 0));	
			
			// no empty text
			if($returned_id)
			{
				if(!$existing)
				{
					// #14332 - new text
					$this->sendNotifications($this->ass->getId());
					$this->object->handleSubmission($this->ass->getId());						
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
			$this->ass->getType() != ilExAssignment::TYPE_TEXT)	
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$add_rating = null;
		
		// tutor
		if((int)$_GET["grd"])
		{
			$this->checkPermission("write");
			
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
		else if($this->ass->hasPeerReviewAccess((int)$_GET["member_id"]))
		{
			$this->checkPermission("read");		
					
			$user_id = (int)$_GET["member_id"];
			$cancel_cmd = "editPeerReview";		
			
			// rating
			$add_rating = "updatePeerReviewText";
			$ilCtrl->setParameter($this, "peer_id", $user_id);		
			include_once './Services/Rating/classes/class.ilRatingGUI.php';
			$rating = new ilRatingGUI();
			$rating->setObject($this->ass->getId(), "ass", $user_id, "peer");
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
			$this->checkPermission("read");		
			
			$user_id = $ilUser->getId();
			$cancel_cmd = "showOverview";
		}
					
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, $cancel_cmd));		
		
		$a_form = $this->initAssignmentTextForm($this->ass, true, $cancel_cmd, $add_rating, $rating);	
		
		if(($user_id != $ilUser->getId() || (bool)$_GET["grd"]))
		{
			if(!stristr($cancel_cmd, "peer"))
			{
				include_once "Services/User/classes/class.ilUserUtil.php";
				$a_form->setDescription(ilUserUtil::getNamePresentation($user_id));						
			}
			else
			{			
				if(!$this->ass->hasPeerReviewPersonalized())
				{
					$a_form->setDescription($lng->txt("id").": ".(int)$_GET["seq"]);
				}
				else
				{
					include_once "Services/User/classes/class.ilUserUtil.php";
					$a_form->setDescription(ilUserUtil::getNamePresentation($user_id));	
				}
								
				foreach($this->ass->getPeerReviewsByPeerId($user_id) as $item)
				{
					if($item["giver_id"] == $ilUser->getId())
					{						
						$a_form->getItemByPostVar("comm")->setValue($item["pcomment"]);					
						break;
					}
				}
			}						
		}
		
		$files = ilExAssignment::getDeliveredFiles($this->ass->getExerciseId(), $this->ass->getId(), $user_id);
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
	
	function listTextAssignmentWithPeerReviewObject()
	{
		$this->listTextAssignmentObject(true);
	}
	
	function listTextAssignmentObject($a_show_peer_review = false)
	{
		global $tpl, $ilToolbar, $ilCtrl, $ilTabs, $lng;
		
		$this->checkPermission("write");
		
		if(!$this->ass || $this->ass->getType() != ilExAssignment::TYPE_TEXT)
		{
			$ilCtrl->redirect($this, "member");
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "members"));
		
		if($a_show_peer_review)
		{
			$cmd = "listTextAssignmentWithPeerReview";
		}
		else
		{
			$cmd = "listTextAssignment";
		}
		include_once "Modules/Exercise/classes/class.ilExAssignmentListTextTableGUI.php";
		$tbl = new ilExAssignmentListTextTableGUI($this, $cmd, $this->ass, $a_show_peer_review);		
		$tpl->setContent($tbl->getHTML());		
	}
	
	protected function canPeerReviewBeEdited()
	{
		// #16130
		return ($this->ass &&
			$this->ass->getPeerReview()  &&
			$this->ass->getDeadline() &&
			$this->ass->getDeadline() < time() &&
			(!$this->ass->getPeerReviewDeadline() ||
			$this->ass->getPeerReviewDeadline() > time()));
	}
	
	function editPeerReviewObject()
	{
		global $ilCtrl, $ilUser, $tpl;
				
		if(!$this->canPeerReviewBeEdited())				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
				
		$this->checkPermission("read");		
					
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showOverview"));				

		$peer_items = $this->ass->getPeerReviewsByGiver($ilUser->getId());
		if(!sizeof($peer_items))
		{
			ilUtil::sendFailure($this->lng->txt("exc_peer_review_no_peers"), true);
			$ilCtrl->redirect($this, "showOverview");
		}
				
		$missing = ilExAssignment::getNumberOfMissingFeedbacks($this->ass->getId(), $this->ass->getPeerReviewMin());
		if($missing)
		{
			$dl = $this->ass->getPeerReviewDeadline();
			if(!$dl || $dl < time())
			{
				ilUtil::sendInfo(sprintf($this->lng->txt("exc_peer_review_missing_info"), $missing));
			}
			else
			{
				ilUtil::sendInfo(sprintf($this->lng->txt("exc_peer_review_missing_info_deadline"), $missing, 
					ilDatePresentation::formatDate(new ilDateTime($dl, IL_CAL_UNIX))));
			}
		}			
		
		$tpl->addJavaScript("Modules/Exercise/js/ilExcPeerReview.js");
		$tpl->addOnLoadCode("il.ExcPeerReview.setAjax('".
			$ilCtrl->getLinkTarget($this, "updatePeerReviewComments", "", true, false).
			"')");
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentPeerReviewTableGUI.php";
		$tbl = new ilExAssignmentPeerReviewTableGUI($this, "editPeerReview", $this->ass, $ilUser->getId(), $peer_items,  "exc_peer_review_give", "showOverview");
		
		$tpl->setContent($tbl->getHTML());
	}
	
	function updatePeerReviewObject()
	{
		global $ilUser, $ilCtrl;
		
		if(!$this->canPeerReviewBeEdited() ||
			!sizeof($_POST["pc"]))				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$this->checkPermission("read");		
		
		$peer_items = $this->ass->getPeerReviewsByGiver($ilUser->getId());
		if(!sizeof($peer_items))
		{
			ilUtil::sendFailure($this->lng->txt("exc_peer_review_no_peers"), true);
			$ilCtrl->redirect($this, "showOverview");
		}
	
		foreach($_POST["pc"] as $idx => $value)
		{						
			$parts = explode("__", $idx);					
			if($parts[0] == $ilUser->getId())
			{
				$this->ass->updatePeerReviewComment($parts[1], ilUtil::stripSlashes($value)); // #16128					
			}			
		}
		
		ilUtil::sendInfo($this->lng->txt("exc_peer_review_updated"), true);
		$ilCtrl->redirect($this, "editPeerReview");	
	}
	
	function updatePeerReviewCommentsObject()
	{
		global $ilCtrl, $ilUser, $tpl;
		
		if(!$this->canPeerReviewBeEdited() ||
			!sizeof($_POST["pc"]) ||
			!$ilCtrl->isAsynch())				
		{
			exit();
		}
		
		$rating_peer_id = $_POST["rating_peer_id"];
		$giver_id = $ilUser->getId();
				
		// save rating
		include_once './Services/Rating/classes/class.ilRating.php';
		ilRating::writeRatingForUserAndObject($this->ass->getId(), "ass", 
			$rating_peer_id, "peer", $giver_id, $_POST["rating"]);
		
		// save comments
		foreach($_POST["pc"] as $peer_id => $value)
		{
			if($peer_id)
			{
				$this->ass->updatePeerReviewComment($peer_id, ilUtil::stripSlashes($value)); // #16128				
			}
		}
		
		
		// render current rating
		
		$ilCtrl->setParameter($this->parent_obj, "peer_id", $rating_peer_id);		
		
		include_once './Services/Rating/classes/class.ilRatingGUI.php';
		$rating = new ilRatingGUI();
		$rating->setObject($this->ass->getId(), "ass", $rating_peer_id, "peer");
		$rating->setUserId($giver_id);
		
		if(!$_REQUEST["ssrtg"])
		{	
			echo $rating->getHTML(false, true, 
					"il.ExcPeerReview.saveComments(".$rating_peer_id.", %rating%)");	
		}
		else
		{		
			echo '<div id="rtr_widget">'.$rating->getHTML(false, true,
				"il.ExcPeerReview.saveSingleRating(".$rating_peer_id.", %rating%)").'</div>';
		}
		
		echo $tpl->getOnLoadCodeForAsynch();
		exit();
	}
	
	function updatePeerReviewTextObject()
	{
		global $ilCtrl;
		
		if(!$this->canPeerReviewBeEdited() ||
			!(int)$_REQUEST["peer_id"])
		{
			$ilCtrl->redirect($this, "editPeerReview");	
		}
		
		$this->ass->updatePeerReviewComment((int)$_REQUEST["peer_id"], ilUtil::stripSlashes(trim($_POST["comm"])));	// #16128			
		
		ilUtil::sendInfo($this->lng->txt("exc_peer_review_updated"), true);
		$ilCtrl->redirect($this, "editPeerReview");	
	}
	
	function downloadPeerReviewObject()
	{
		global $ilCtrl, $ilUser;
		
		if(!$this->ass || 
			!$this->ass->getPeerReview() ||
			!$this->ass->hasPeerReviewFileUpload() ||
			!$_GET["fu"] ||
			!$_GET["fuf"])				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$parts = explode("__", $_GET["fu"]);
		$giver_id = $parts[0];
		$peer_id = $parts[1];
		
		if($giver_id == $ilUser->getId() || 
			$peer_id == $ilUser->getId())
		{		
			$this->checkPermission("read");			
		}
		else
		{
			$this->checkPermission("write");												
		}
		
		$peer_items = $this->ass->getPeerReviewsByPeerId($peer_id, true);				
		if(sizeof($peer_items))
		{
			foreach($peer_items as $item)
			{
				if($item["giver_id"] == $giver_id)
				{													
					$files = $this->ass->getPeerUploadFiles($peer_id, $giver_id);			
					foreach($files as $file)
					{
						if(md5($file) == trim($_GET["fuf"]))
						{
							ilUtil::deliverFile($file, basename($file));
							break(2);
						}
					}										
				}
			}
		}		
		
		$ilCtrl->redirect($this, "showOverview");		
	}
	
	function showPersonalPeerReviewObject()
	{
		global $ilCtrl, $ilUser, $tpl;
		
		if(!$this->ass || 
			!$this->ass->getPeerReview() ||
			!$this->ass->getDeadline() ||
			$this->ass->getDeadline()-time() > 0)				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		// tutor
		if((int)$_GET["grd"])
		{
			$this->checkPermission("write");
			
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
		// personal
		else
		{
			$this->checkPermission("read");		
			
			$user_id = $ilUser->getId();
			$cancel_cmd = "showOverview";
		}
					
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, $cancel_cmd));				
		
		$peer_items = $this->ass->getPeerReviewsByPeerId($user_id, true);
		if(!sizeof($peer_items))
		{
			// #11373
			ilUtil::sendFailure($this->lng->txt("exc_peer_review_no_peers_reviewed_yet"), true);
			$ilCtrl->redirect($this, "showOverview");
		}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentPeerReviewTableGUI.php";
		$tbl = new ilExAssignmentPeerReviewTableGUI($this, "editPeerReview", 
			$this->ass, $user_id, $peer_items, "exc_peer_review_show", $cancel_cmd, true);
		
		$tpl->setContent($tbl->getHTML());		
	}	
	
	public function showPeerReviewOverviewObject()
	{
		global $ilCtrl, $ilTabs, $tpl;
		
		if(!$this->ass || 
			!$this->ass->getPeerReview())				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$this->checkPermission("write");
				
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listAssignments"));
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentPeerReviewOverviewTableGUI.php";
		$tbl = new ilExAssignmentPeerReviewOverviewTableGUI($this, "showPeerReviewOverview", 
			$this->ass);		
		
		$panel = "";
		$panel_data = $tbl->getPanelInfo();
		if(sizeof($panel_data))
		{
			$ptpl = new ilTemplate("tpl.exc_peer_review_overview_panel.html", true, true, "Modules/Exercise");
			foreach($panel_data as $item)
			{
				$ptpl->setCurrentBlock("user_bl");
				foreach($item["value"] as $user)
				{
					$ptpl->setVariable("USER", $user);
					$ptpl->parseCurrentBlock();
				}
				
				$ptpl->setCurrentBlock("item_bl");
				$ptpl->setVariable("TITLE", $item["title"]);
				$ptpl->parseCurrentBlock();
			}
		
			include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
			$panel = ilPanelGUI::getInstance();
			$panel->setHeading($this->lng->txt("exc_peer_review_overview_invalid_users"));
			$panel->setBody($ptpl->get());
			$panel = $panel->getHTML();
		}
		
		$tpl->setContent($tbl->getHTML().$panel);
	}
	
	public function confirmResetPeerReviewObject()
	{
		global $ilCtrl, $tpl, $ilTabs;
		
		if(!$this->ass || 
			!$this->ass->getPeerReview())				
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		$this->checkPermission("write");
		
		$ilTabs->clearTargets();
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText(sprintf($this->lng->txt("exc_peer_review_reset_sure"), $this->ass->getTitle()));
		$cgui->setCancel($this->lng->txt("cancel"), "showPeerReviewOverview");
		$cgui->setConfirm($this->lng->txt("delete"), "resetPeerReview");

		$tpl->setContent($cgui->getHTML());		
	}
	
	public function resetPeerReviewObject()
	{
		global $ilCtrl;
		
		if($this->ass &&
			$this->ass->getPeerReview())				
		{
			$this->checkPermission("write");
			$this->ass->resetPeerReviews();

			ilUtil::sendSuccess($this->lng->txt("exc_peer_review_reset_done"), true);
		}
						
		$ilCtrl->redirect($this, "showPeerReviewOverview");
	}
	
	public function downloadGlobalFeedbackFileObject()
	{
		global $ilCtrl, $ilUser;
		
		$needs_dl = ($this->ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE);
		
		if(!$this->ass || 
			!$this->ass->getFeedbackFile() ||
			($needs_dl && !$this->ass->getDeadline()) ||
			($needs_dl && $this->ass->getDeadline() > time()) ||
			(!$needs_dl && !ilExAssignment::getLastSubmission($this->ass->getId(), $ilUser->getId())))						
		{
			$ilCtrl->redirect($this, "showOverview");
		}
		
		ilUtil::deliverFile($this->ass->getFeedbackFilePath(), $this->ass->getFeedbackFile());
	}
	
	////
	//// Multi Feedback
	////
	
	function initMultiFeedbackForm($a_ass_id)
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("uploadMultiFeedback", $lng->txt("upload"));
		$form->addCommandButton("members", $lng->txt("cancel"));
		
		// multi feedback file
		$fi = new ilFileInputGUI($lng->txt("exc_multi_feedback_file"), "mfzip");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);
				
		$form->setTitle(ilExAssignment::lookupTitle($a_ass_id));
		$form->setFormAction($this->ctrl->getFormAction($this, "uploadMultiFeedback"));		
		
		return $form;
	}
	
	/**
	 * Show multi-feedback screen
	 *
	 * @param
	 * @return
	 */
	function showMultiFeedbackObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilTabs, $ilToolbar, $lng, $tpl;
		
		$ass_id = (int)$_GET["ass_id"];
		
		ilUtil::sendInfo($lng->txt("exc_multi_feedb_info"));
		
		$ilTabs->activateTab("grades");
		$this->checkPermission("write");
		$this->addSubmissionSubTabs("assignment");
		
		// #13719
		include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
		$button = ilLinkButton::getInstance();				
		$button->setCaption("exc_download_zip_structure");
		$button->setUrl($this->ctrl->getLinkTarget($this, "downloadMultiFeedbackZip"));							
		$button->setOmitPreventDoubleSubmission(true);
		$ilToolbar->addButtonInstance($button);
		
		if(!$a_form)
		{
			$a_form = $this->initMultiFeedbackForm($ass_id);
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	/**
	 * Download multi-feedback structrue file
	 */
	function downloadMultiFeedbackZipObject()
	{
		$ass = new ilExAssignment((int) $_GET["ass_id"]);
		$ass->sendMultiFeedbackStructureFile($this->object);
	}
	
	/**
	 * Upload multi feedback file
	 */
	function uploadMultiFeedbackObject()
	{		
		$ass_id = (int)$_GET["ass_id"];
		
		// #11983
		$form = $this->initMultiFeedbackForm($ass_id);
		if($form->checkInput())
		{
			try
			{
				$ass = new ilExAssignment($ass_id);
				$ass->uploadMultiFeedbackFile(ilUtil::stripSlashesArray($_FILES["mfzip"]));
				$this->ctrl->redirect($this, "showMultiFeedbackConfirmationTable");
			}
			catch (ilExerciseException $e)
			{
				ilUtil::sendFailure($e->getMessage(), true);
				$this->ctrl->redirect($this, "showMultiFeedback");
			}
		}
		
		$form->setValuesByPost();
		$this->showMultiFeedbackObject($form);
	}
	
	/**
	 * Show multi feedback confirmation table
	 *
	 * @param
	 * @return
	 */
	function showMultiFeedbackConfirmationTableObject()
	{
		global $ilTabs, $tpl;
		
		$ilTabs->activateTab("grades");
		$this->checkPermission("write");
		$this->addSubmissionSubTabs("assignment");
		
		$ass = new ilExAssignment((int) $_GET["ass_id"]);
		include_once("./Modules/Exercise/classes/class.ilFeedbackConfirmationTable2GUI.php");
		$tab = new ilFeedbackConfirmationTable2GUI($this, "showMultiFeedbackConfirmationTable", $ass);
		$tpl->setContent($tab->getHTML());		
	}
	
	/**
	 * Cancel Multi Feedback
	 */
	function cancelMultiFeedbackObject()
	{
		$this->checkPermission("write");
		$ass = new ilExAssignment((int) $_GET["ass_id"]);
		$ass->clearMultiFeedbackDirectory();
		
		$this->ctrl->redirect($this, "members");
	}
	
	/**
	 * Save multi feedback
	 */
	function saveMultiFeedbackObject()
	{
		$this->checkPermission("write");
		$ass = new ilExAssignment((int) $_GET["ass_id"]);
		$ass->saveMultiFeedbackFiles($_POST["file"]);
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "members");
	}
	
	/**
	 * Save comment for learner (asynch)
	 */
	function saveCommentForLearnersObject()
	{
		$this->checkPermission("write");
		
		$res = array("result"=>false);
		
		if($this->ctrl->isAsynch())
		{
			$ass_id = (int)$_POST["ass_id"];
			$user_id = (int)$_POST["mem_id"];
			$comment = trim($_POST["comm"]);
			
			if($ass_id && $user_id)
			{				
				// team upload?
				if(is_object($this->ass) && $this->ass->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					$team_id = $this->ass->getTeamId($user_id);
					$user_ids = $this->ass->getTeamMembers($team_id);		
				}
				else
				{
					$user_ids = array($user_id);
				}				
				
				$all_members = new ilExerciseMembers($this->object);
				$all_members = $all_members->getMembers();
				
				$reci_ids = array();
				foreach($user_ids as $user_id)
				{
					if(in_array($user_id, $all_members))
					{
						ilExAssignment::updateCommentForUser($ass_id, $user_id,
							ilUtil::stripSlashes($comment));
						
						if(trim($comment))
						{
							$reci_ids[] = $user_id;
						}
					}
				}
				
				if(sizeof($reci_ids))
				{
					// send notification
					$this->object->sendFeedbackFileNotification(null, $reci_ids, 
						$ass_id, true);
				}
				
				$res = array("result"=>true, "snippet"=>ilUtil::shortenText($comment, 25, true));
			}						
		}				
		
		echo(json_encode($res));		
		exit();
	}
	
	public function createTeamObject()
	{		
		global $ilCtrl, $ilUser, $ilTabs, $lng, $tpl;
		
		$this->checkPermission("read");
		
		if($this->ass->getDeadline() == 0 ||
			mktime() < $this->ass->getDeadline())
		{			
			$options = ilExAssignment::getAdoptableTeamAssignments($this->ass->getExerciseId(), $this->ass->getId(), $ilUser->getId());
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
				
				$current_map = ilExAssignment::getAssignmentTeamMap($this->ass->getId());

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
			
			$this->ass->getTeamId($ilUser->getId(), true);		
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);	
		}
		
		$ilCtrl->redirect($this, "showOverview");
	}
	
	public function createAdoptedTeamObject()
	{
		global $ilCtrl, $ilUser, $lng;
		
		$this->checkPermission("read");
		
		if($this->ass->getDeadline() == 0 ||
			mktime() < $this->ass->getDeadline())
		{	
			$src_ass_id = (int)$_POST["ass_adpt"];
			if($src_ass_id > 0)
			{
				$this->ass->adoptTeams($src_ass_id, $ilUser->getId(), $this->ref_id);						
			}
			else
			{
				$this->ass->getTeamId($ilUser->getId(), true);		
			}
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
		
		$ilCtrl->redirect($this, "showOverview");
	}
	
	public function adoptTeamAssignmentsFormObject()
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		$this->checkPermission("write");
		
		if(!$this->ass)
		{
			$ilCtrl->redirect($this, "listAssignments");
		}
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("list_assignments");
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();		         
		$form->setTitle($lng->txt("exc_team_assignment_adopt"));
		$form->setFormAction($ilCtrl->getFormAction($this, "adoptTeamAssignments"));
		
		$options = ilExAssignment::getAdoptableTeamAssignments($this->ass->getExerciseId());
		
		// we must not have existing teams in assignment
		if(array_key_exists($this->ass->getId(), $options))
		{
			$ilCtrl->redirect($this, "listAssignments");
		}
		
		$teams = new ilRadioGroupInputGUI($lng->txt("exc_assignment"), "ass_adpt");
		$teams->setValue(-1);
		
		$teams->addOption(new ilRadioOption($lng->txt("exc_team_assignment_adopt_none"), -1));
		
		foreach($options as $id => $item)
		{
			$option = new ilRadioOption($item["title"], $id);
			$option->setInfo($lng->txt("exc_team_assignment_adopt_teams").": ".$item["teams"]);
			$teams->addOption($option);
		}
		
		$form->addItem($teams);
	
		$form->addCommandButton("adoptTeamAssignments", $lng->txt("save"));
		$form->addCommandButton("listAssignments", $lng->txt("cancel"));

		$tpl->setContent($form->getHTML());
	}
	
	public function adoptTeamAssignmentsObject()
	{
		global $ilCtrl, $lng;
		
		$this->checkPermission("write");
		
		$src_ass_id = (int)$_POST["ass_adpt"];
		
		if($this->ass && $src_ass_id > 0)
		{
			// no notifications, assignment is not ready
			$this->ass->adoptTeams($src_ass_id);			
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
		}
							
		$ilCtrl->redirect($this, "listAssignments");		
	}
}

?>