<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object-based submissions (ends up as static file)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * 
 * @ilCtrl_Calls ilExSubmissionTextGUI: 
 * @ingroup ModulesExercise 
 */
class ilExSubmissionTextGUI extends ilExSubmissionBaseGUI
{			
	public function executeCommand()
	{
		global $ilCtrl;
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("showassignmenttext");		
		
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
		
		if($a_ass->beforeDeadline())
		{
			$button = ilLinkButton::getInstance();
			$button->setPrimary(true);
			$button->setCaption("exc_text_assignment_edit");
			$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTextGUI"), "editAssignmentText"));							
			$files_str = $button->render();							
		}
		else
		{
			$button = ilLinkButton::getInstance();
			$button->setCaption("exc_text_assignment_show");
			$button->setUrl($ilCtrl->getLinkTargetByClass(array("ilExSubmissionGUI", "ilExSubmissionTextGUI"), "showAssignmentText"));							
			$files_str = $button->render();														
		}

		$a_info->addProperty($lng->txt("exc_files_returned_text"), $files_str);	
	}
	
	
	//
	// TEXT ASSIGNMENT (EDIT)
	// 
	
	protected function initAssignmentTextForm(ilExAssignment $a_ass, $a_read_only = false, $a_cancel_cmd = "returnToParent", $a_peer_review_cmd = null, $a_peer_rating_html = null)
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
				$form->setFormAction($ilCtrl->getFormAction($this, "returnToParent"));
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

		if(!$this->assignment || 
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT ||
			($this->assignment->getDeadline() && $this->assignment->getDeadline() - time() < 0))				
		{
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		if($this->assignment->getDeadline())
		{
			ilUtil::sendInfo($this->lng->txt("exc_edit_until").": ".
				ilDatePresentation::formatDate(new ilDateTime($this->assignment->getDeadline(),IL_CAL_UNIX)));
		}
		
		$this->handleTabs();
		
		if(!$a_form)
		{
			$a_form = $this->initAssignmentTextForm($this->assignment);		

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
		
		if(!$this->assignment || 
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT ||
			$times_up)
		{
			if($times_up)
			{
				ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			}
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		$form = $this->initAssignmentTextForm($this->assignment);	
		
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
				$ilCtrl->redirect($this, "returnToParent");
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
		
		if(!$this->assignment || 
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT)	
		{
			$ilCtrl->redirect($this, "returnToParent");
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
			$cancel_cmd = "returnToParent";
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
