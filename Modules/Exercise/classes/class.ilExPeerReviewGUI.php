<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilExPeerReviewGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* 
* @ilCtrl_Calls ilExPeerReviewGUI: ilFileSystemGUI, ilRatingGUI
* 
* @ingroup ModulesExercise
*/
class ilExPeerReviewGUI
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
	public function __construct($a_exercise_id, ilExSubmission $a_submission)
	{
		$this->exercise_id = $a_exercise_id;
		$this->assignment = $a_submission->getAssignment();
	}
	
	public function executeCommand()
	{
		global $ilCtrl, $lng, $ilTabs, $ilUser;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listPublicSubmissions");		
		
		switch($class)
		{			
			case "ilfilesystemgui":							
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
				$this->{$cmd."Object"}();				
				break;
		}
	}	
		
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
	{
		global $lng, $ilCtrl;
		
		$ass = $a_submission->getAssignment();
		
		if($ass->afterDeadlineStrict() && 
			$ass->getPeerReview())
		{								
			$nr_missing_fb = ilExAssignment::getNumberOfMissingFeedbacks($ass->getId(), $ass->getPeerReviewMin());

			if(!$ass->getPeerReviewDeadline() || $ass->getPeerReviewDeadline() > time())
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
				$button->setUrl($ilCtrl->getLinkTargetByClass("ilExPeerReviewGUI", "editPeerReview"));							
				$edit_pc = $button->render();													
			}
			else if($ass->getPeerReviewDeadline())
			{
				$edit_pc = $lng->txt("exc_peer_review_deadline_reached");
			}
			if((!$ass->getPeerReviewDeadline() || $ass->getPeerReviewDeadline() < time()) && 
				!$nr_missing_fb)
			{						
				$button = ilLinkButton::getInstance();					
				$button->setCaption("exc_peer_review_show");
				$button->setUrl($ilCtrl->getLinkTargetByClass("ilExPeerReviewGUI", "showPersonalPeerReview"));							
				$view_pc = $button->render();							
			}
			/*
			else 
			{
				$view_pc = $lng->txt("exc_peer_review_show_not_rated_yet");
			}
			*/

			$a_info->addProperty($lng->txt("exc_peer_review"), $edit_pc." ".$view_pc);																									
		}						
	}
	
	function editPeerReviewObject()
	{
		global $ilCtrl, $ilUser, $tpl;
				
		if(!$this->ass || 
			!$this->ass->getPeerReview() ||
			!$this->ass->getDeadline() ||
			$this->ass->getDeadline()-time() > 0)				
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
		
		if(!$this->ass || 
			!$this->ass->getPeerReview() ||
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
				$this->ass->updatePeerReviewComment($parts[1], $value);				
			}			
		}
		
		ilUtil::sendInfo($this->lng->txt("exc_peer_review_updated"), true);
		$ilCtrl->redirect($this, "editPeerReview");	
	}
	
	function updatePeerReviewCommentsObject()
	{
		global $ilCtrl, $ilUser, $tpl;
		
		if(!$this->ass || 
			!$this->ass->getPeerReview() ||
			!$this->ass->getDeadline() ||
			$this->ass->getDeadline()-time() > 0 ||
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
				$this->ass->updatePeerReviewComment($peer_id, $value);				
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
		
		if(!$this->ass || 
			!$this->ass->getPeerReview() ||
			!$this->ass->getDeadline() ||
			$this->ass->getDeadline()-time() > 0 ||
			!(int)$_REQUEST["peer_id"])
		{
			$ilCtrl->redirect($this, "editPeerReview");	
		}
		
		$this->ass->updatePeerReviewComment((int)$_REQUEST["peer_id"], trim($_POST["comm"]));		
		
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
	
	
}
