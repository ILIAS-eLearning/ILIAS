<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilExPeerReviewGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExPeerReviewGUI: ilFileSystemGUI, ilRatingGUI, ilExSubmissionTextGUI
* 
* @ingroup ModulesExercise
*/
class ilExPeerReviewGUI
{
	protected $ass; // [ilExAssignment]
	protected $submission; // [ilExSubmission]
	
	/**
	 * Constructor
	 * 
	 * @param ilExAssignment $a_ass
	 * @param ilExSubmission $a_sub
	 * @return object
	 */
	public function __construct(ilExAssignment $a_ass, ilExSubmission $a_submission = null)
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;
		
		$this->ass = $a_ass;
		$this->submission = $a_submission;
		
		// :TODO:
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;
		$this->lng = $lng;
		$this->tpl = $tpl;	
	}
	
	public function executeCommand()
	{
		global $ilCtrl, $lng, $ilTabs, $ilUser;
		
		if(!$this->ass->getPeerReview())
		{
			$this->returnToParentObject();
		}
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("showpeerreviewoverview");		
		
		switch($class)
		{			
			case "ilfilesystemgui":							
				$ilCtrl->saveParameter($this, array("fu"));										

				// see self::downloadPeerReview()
				$parts = explode("__", $_GET["fu"]);
				$giver_id = $parts[0];
				$peer_id = $parts[1];

				if(!$this->canGive())
				{
					$this->returnToParentObject();
				}					

				$valid = false;
				$peer_items = $this->submission->getPeerReview()->getPeerReviewsByPeerId($peer_id, true);				
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

				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "editPeerReview"));

				include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
				$fstorage = new ilFSStorageExercise($this->ass->getExerciseId(), $this->ass->getId());
				$fstorage->create();

				include_once("./Services/FileSystem/classes/class.ilFileSystemGUI.php");
				$fs_gui = new ilFileSystemGUI($fstorage->getPeerReviewUploadPath($peer_id, $giver_id));
				$fs_gui->setTableId("excfbpeer");
				$fs_gui->setAllowDirectories(false);					
				$fs_gui->setTitle($this->ass->getTitle().": ".
					$lng->txt("exc_peer_review")." - ".
					$lng->txt("exc_peer_review_give"));						 
				$ret = $this->ctrl->forwardCommand($fs_gui);
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
			
			case "ilexsubmissiontextgui":				
				if(!$this->submission->isTutor())
				{
					$ilTabs->clearTargets();				
					$ilTabs->setBackTarget($lng->txt("back"),
						$ilCtrl->getLinkTarget($this, "editPeerReview"));
					$this->ctrl->setReturn($this, "editPeerReview");
				}
				else
				{
					$ilTabs->clearTargets();				
					$ilTabs->setBackTarget($lng->txt("back"),
						$ilCtrl->getLinkTarget($this, "showGivenPeerReview"));
					$this->ctrl->setReturn($this, "showGivenPeerReview");
				}
				include_once "Modules/Exercise/classes/class.ilExSubmissionTextGUI.php";
				$gui = new ilExSubmissionTextGUI(new ilObjExercise($this->ass->getExerciseId(), false), $this->submission);				
				$ilCtrl->forwardCommand($gui); 
				break;
						
			default:									
				$this->{$cmd."Object"}();				
				break;
		}
	}	
	
	function returnToParentObject()
	{
		$this->ctrl->returnToParent($this);
	}
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
	{
		global $lng, $ilCtrl;
		
		$ass = $a_submission->getAssignment();
		
		if($ass->afterDeadlineStrict() && 
			$ass->getPeerReview())
		{								
			$ilCtrl->setParameterByClass("ilExPeerReviewGUI", "ass_id", $a_submission->getAssignment()->getId());
			
			$nr_missing_fb = $a_submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived($ass->getId(), $ass->getPeerReviewMin());
			
			// before deadline (if any)
			if(!$ass->getPeerReviewDeadline() || 
				$ass->getPeerReviewDeadline() > time())
			{			
				$dl_info = "";
				if($ass->getPeerReviewDeadline())
				{
					$dl_info = " (".sprintf($lng->txt("exc_peer_review_deadline_info_button"), 
						ilDatePresentation::formatDate(new ilDateTime($ass->getPeerReviewDeadline(), IL_CAL_UNIX))).")";							
				}

				$button = ilLinkButton::getInstance();
				$button->setPrimary($nr_missing_fb);
				$button->setCaption($lng->txt("exc_peer_review_give").$dl_info, false);
				$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExPeerReviewGUI"), "editPeerReview"));							
				$edit_pc = $button->render();													
			}
			else if($ass->getPeerReviewDeadline())
			{
				$edit_pc = $lng->txt("exc_peer_review_deadline_reached");
			}
			
			// after deadline (if any)
			if((!$ass->getPeerReviewDeadline() || 
				$ass->getPeerReviewDeadline() < time()))
			{						 
				// given peer review should be accessible at all times (read-only when not editable - see above)
				if($ass->getPeerReviewDeadline() &&
					$a_submission->getPeerReview()->countGivenFeedback(false))
				{
					$button = ilLinkButton::getInstance();					
					$button->setCaption("exc_peer_review_given");
					$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExPeerReviewGUI"), "showGivenPeerReview"));							
					$view_pc = $button->render()." ";
				}
				
				// did give enough feedback
				if(!$nr_missing_fb)
				{					
					// received any?
					$received = (bool)sizeof($a_submission->getPeerReview()->getPeerReviewsByPeerId($a_submission->getUserId(), true));				
					if($received)
					{
						$button = ilLinkButton::getInstance();					
						$button->setCaption("exc_peer_review_show");
						$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExPeerReviewGUI"), "showPersonalPeerReview"));							
						$view_pc .= $button->render();
					}
					// received none
					else
					{
						$view_pc .= $lng->txt("exc_peer_review_show_received_none");
					}
				}
				// did not give enough
				else
				{
					$view_pc .= $lng->txt("exc_peer_review_show_missing");
				}
			}
			/* must give before showing received
			else 
			{
				$view_pc = $lng->txt("exc_peer_review_show_not_rated_yet");
			}
			*/

			$a_info->addProperty($lng->txt("exc_peer_review"), $edit_pc." ".$view_pc);	
			
			$ilCtrl->setParameterByClass("ilExPeerReviewGUI", "ass_id", "");
		}						
	}
	
	protected function canGive()
	{
		return ($this->submission->isOwner() &&
			$this->ass->afterDeadlineStrict() &&
			(!$this->ass->getPeerReviewDeadline() ||
				$this->ass->getPeerReviewDeadline() > time()));
	}
	
	protected function canView()
	{
		return ($this->submission->isTutor() ||
			($this->submission->isOwner() &&
			$this->ass->afterDeadlineStrict() &&
			(!$this->ass->getPeerReviewDeadline() ||
				$this->ass->getPeerReviewDeadline() < time())));
	}
	
	function showGivenPeerReviewObject()
	{		
		if(!$this->canView())
		{
			$this->returnToParentObject();
		}
		$this->editPeerReviewObject(true);
	}
	
	function editPeerReviewObject($a_read_only = false)
	{
		global $ilCtrl, $tpl;
		
		if(!$a_read_only &&
			!$this->canGive())
		{
			$this->returnToParentObject();
		}
		
		$peer_items = $this->submission->getPeerReview()->getPeerReviewsByGiver($this->submission->getUserId());
		if(!sizeof($peer_items))
		{
			ilUtil::sendFailure($this->lng->txt("exc_peer_review_no_peers"), true);
			$this->returnToParentObject();
		}
				
		if(!$a_read_only)
		{
			$missing = $this->submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived();
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
		}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentPeerReviewTableGUI.php";
		
		if($this->submission->isTutor())
		{
			$mode = ilExAssignmentPeerReviewTableGUI::MODE_TUTOR_GIVEN;
		}
		else
		{
			$mode = $a_read_only 
				? ilExAssignmentPeerReviewTableGUI::MODE_VIEW 
				: ilExAssignmentPeerReviewTableGUI::MODE_EDIT;
		}
		
		$tbl = new ilExAssignmentPeerReviewTableGUI($this, "editPeerReview", $this->ass, $this->submission->getUserId(), 
			$peer_items,  "returnToParent", $mode);
		
		if(!$this->submission->isTutor())
		{
			$invalid = $tbl->getInvalidItems();
			if($invalid)
			{
				ilUtil::sendFailure(sprintf($this->lng->txt("exc_peer_review_chars_invalid"), $this->ass->getPeerReviewChars()));
			}	
		}
		
		$tpl->setContent($tbl->getHTML());
	}
	
	function updatePeerReviewObject()
	{
		global $ilUser, $ilCtrl;
		
		if(!$this->canGive() ||			
			!sizeof($_POST["pc"]))
		{
			$this->returnToParentObject();
		}
		
		$peer_items = $this->submission->getPeerReview()->getPeerReviewsByGiver($ilUser->getId());
		if(!sizeof($peer_items))
		{
			ilUtil::sendFailure($this->lng->txt("exc_peer_review_no_peers"), true);
			$ilCtrl->redirect($this, "returnToParent");
		}
	
		foreach($_POST["pc"] as $idx => $value)
		{						
			$parts = explode("__", $idx);					
			if($parts[0] == $ilUser->getId())
			{
				$this->submission->getPeerReview()->updatePeerReviewComment($parts[1], $value);				
			}			
		}
		
		$this->handlePeerReviewChange();
		
		ilUtil::sendSuccess($this->lng->txt("exc_peer_review_updated"), true);
		$ilCtrl->redirect($this, "editPeerReview");	
	}
	
	function updatePeerReviewCommentsObject()
	{
		global $ilCtrl, $ilUser, $tpl;
		
		if(!$this->canGive() ||			
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
				$this->submission->getPeerReview()->updatePeerReviewComment($peer_id, $value);				
			}
		}
		
		$this->handlePeerReviewChange();
		
		
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
		
		if(!$this->canGive() ||			
			!(int)$_REQUEST["peer_id"])
		{
			$this->returnToParentObject();
		}
		
		$this->submission->getPeerReview()->updatePeerReviewComment((int)$_REQUEST["peer_id"], trim($_POST["comm"]));		
		$this->handlePeerReviewChange();
		
		if(!$this->submission->getPeerReview()->validatePeerReviewText($_POST["comm"]))
		{
			$ilCtrl->setParameterByClass("ilexsubmissiontextgui", "member_id", $_REQUEST["peer_id"]);
			$ilCtrl->redirectByClass("ilexsubmissiontextgui", "showAssignmentText");	
		}
		else 
		{
			ilUtil::sendSuccess($this->lng->txt("exc_peer_review_updated"), true);
			$ilCtrl->redirect($this, "editPeerReview");	
		}
	}
	
	protected function handlePeerReviewChange()
	{
		// (in)valid peer reviews could change assignment status
		$exercise = new ilObjExercise($this->ass->getExerciseId(), false);
		$exercise->processExerciseStatus($this->ass, 
			$this->submission->getUserIds(),
			$this->submission->hasSubmitted(),
			$this->submission->validatePeerReviews()
		);
	}
	
	function downloadPeerReviewObject()
	{
		global $ilCtrl;
		
		if(!$this->canView() &&
			!$this->canGive())
		{
			$this->returnToParentObject();
		}			
		
		$parts = explode("__", $_GET["fu"]);
		$giver_id = $parts[0];
		$peer_id = $parts[1];
		
		$peer_items = $this->submission->getPeerReview()->getPeerReviewsByPeerId($peer_id, true);				
		if(sizeof($peer_items))
		{
			foreach($peer_items as $item)
			{
				if($item["giver_id"] == $giver_id)
				{													
					$files = $this->submission->getPeerReview()->getPeerUploadFiles($peer_id, $giver_id);			
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
		
		$ilCtrl->redirect($this, "returnToParent");		
	}
	
	function showPersonalPeerReviewObject()
	{
		global $ilCtrl, $tpl;
		
		if(!$this->canView() ||
			(!$this->submission->isTutor() &&
			$this->submission->getPeerReview()->getNumberOfMissingFeedbacksForReceived()))
		{
			$this->returnToParentObject();
		}				
	
		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "returnToParent"));				
		
		$user_id = $this->submission->getUserId();
		
		$peer_items = $this->submission->getPeerReview()->getPeerReviewsByPeerId($user_id, !$this->submission->isTutor());
		if(!sizeof($peer_items))
		{
			// #11373
			ilUtil::sendFailure($this->lng->txt("exc_peer_review_no_peers_reviewed_yet"), true);
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		include_once "Modules/Exercise/classes/class.ilExAssignmentPeerReviewTableGUI.php";
		$tbl = new ilExAssignmentPeerReviewTableGUI($this, "editPeerReview", 
			$this->ass, $user_id, $peer_items, "returnToParent", 
			ilExAssignmentPeerReviewTableGUI::MODE_TUTOR_RECEIVED);
		
		$tpl->setContent($tbl->getHTML());		
	}	
	
	public function showPeerReviewOverviewObject()
	{
		global $tpl;
		
		if(!$this->ass || 
			!$this->ass->getPeerReview())				
		{
			$this->returnToParentObject();
		}
	
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
			$this->returnToParentObject();
		}
		
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
		
		if(!$this->ass || 
			!$this->ass->getPeerReview())				
		{
			$this->returnToParentObject();
		}
		
		include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
		$peer_review = new ilExPeerReview($this->ass);
		$all_giver_ids = $peer_review->resetPeerReviews();
		
		if(is_array($all_giver_ids))
		{
			// if peer review is valid for completion, we have to re-calculate all assignment members
			$exercise = new ilObjExercise($this->ass->getExerciseId(), false);
			if($exercise->isCompletionBySubmissionEnabled() &&
				$this->ass->getPeerReviewValid() != ilExAssignment::PEER_REVIEW_VALID_NONE)
			{
				include_once "Modules/Exercise/classes/class.ilExSubmission.php";
				foreach($all_giver_ids as $user_id)
				{
					$submission = new ilExSubmission($this->ass, $user_id);
					$pgui = new self($this->ass, $submission);
					$pgui->handlePeerReviewChange();
				}
			}
		}		

		ilUtil::sendSuccess($this->lng->txt("exc_peer_review_reset_done"), true);								
		$ilCtrl->redirect($this, "showPeerReviewOverview");
	}
	
	
}
