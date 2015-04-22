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
		
		if(!$this->assignment ||
			$this->assignment->getType() != ilExAssignment::TYPE_TEXT ||
			!$this->submission->canView())
		{
			return;
		}
		
		$class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("showassignmenttext");		
		
		switch($class)
		{		
			default:									
				$this->{$cmd."Object"}();				
				break;			
		}
	}
	
	public static function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission)
	{
		global $lng, $ilCtrl;
		
		if($a_submission->canSubmit())
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
	
	protected function initAssignmentTextForm($a_read_only = false, $a_cancel_cmd = "returnToParent", $a_peer_review_class = null, $a_peer_review_cmd = null, $a_peer_rating_html = null)
	{		
		global $ilCtrl;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();		
		$form->setTitle($this->lng->txt("exc_assignment")." \"".$this->assignment->getTitle()."\"");
			
		if(!$a_read_only)
		{
			$text = new ilTextAreaInputGUI($this->lng->txt("exc_your_text"), "atxt");
			$text->setRequired((bool)$this->submission->getAssignment()->getMandatory());				
			$text->setRows(40);
			$form->addItem($text);
			
			// custom rte tags
			$text->setUseRte(true);		
			$text->setRTESupport($this->submission->getUserId(), "exca~", "exc_ass"); 
			
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
				
				$form->setFormAction($ilCtrl->getFormActionByClass($a_peer_review_class, $a_peer_review_cmd));
				$form->addCommandButton($a_peer_review_cmd, $this->lng->txt("save"));	
			}
		}
		$form->addCommandButton($a_cancel_cmd, $this->lng->txt("cancel"));
		
		return $form;
	}
	
	function editAssignmentTextObject(ilPropertyFormGUI $a_form = null)
	{
		global $ilCtrl;

		if(!$this->submission->canSubmit())				
		{
			ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		$deadline = max($this->assignment->getDeadline(), $this->assignment->getExtendedDeadline());
		if($deadline)
		{
			$dl_info = ilDatePresentation::formatDate(new ilDateTime($deadline, IL_CAL_UNIX));
					
			// extended deadline warning			
			if(time() >  $this->assignment->getDeadline())
			{							
				$dl = ilDatePresentation::formatDate(new ilDateTime($this->assignment->getDeadline(),IL_CAL_UNIX));
				$dl = "<br />".sprintf($this->lng->txt("exc_late_submission_warning"), $dl);				
				if(time() >  $this->assignment->getDeadline())
				{
					$dl = '<span class="warning">'.$dl.'</span>';		
				}
				$dl_info .= $dl;
			}
			
			ilUtil::sendInfo($this->lng->txt("exc_edit_until").": ".$dl_info);
		}
		
		$this->handleTabs();
		
		if(!$a_form)
		{
			$a_form = $this->initAssignmentTextForm();		

			$files = $this->submission->getFiles();
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
		global $ilCtrl;
		
		if(!$this->submission->canSubmit())
		{
			ilUtil::sendFailure($this->lng->txt("exercise_time_over"), true);
			$ilCtrl->redirect($this, "returnToParent");
		}
		
		$form = $this->initAssignmentTextForm();	
		
		// we are not using a purifier, so we have to set the valid RTE tags
		// :TODO: 
		include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
		$rte = $form->getItemByPostVar("atxt");
		$rte->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("exc_ass"));
		
		if($form->checkInput())
		{			
			$text = trim($form->getInput("atxt"));	
									
			$existing = $this->submission->getFiles();
												
			$returned_id = $this->submission->updateTextSubmission(
				// mob src to mob id
				ilRTE::_replaceMediaObjectImageSrc($text, 0));	
			
			// no empty text
			if($returned_id)
			{
				if(!$existing)
				{
					$this->handleNewUpload();			
				}
				
				// mob usage
				include_once "Services/MediaObjects/classes/class.ilObjMediaObject.php";
				$mobs = ilRTE::_getMediaObjects($text, 0);
				foreach($mobs as $mob)
				{
					if(ilObjMediaObject::_exists($mob))
					{
						ilObjMediaObject::_removeUsage($mob, 'exca~:html', $this->submission->getUserId());
						ilObjMediaObject::_saveUsage($mob, 'exca:html', $returned_id);
					}
				}
			}
			else
			{
				$this->handleRemovedUpload();
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
		global $ilCtrl, $lng, $tpl, $ilUser;
		
		$user_id = $this->submission->getUserId();
		$add_rating = null;		
		$add_peer_data = false;
		$cancel_cmd = "returnToParent";
				
		if($this->submission->isTutor())
		{
			$add_peer_data = true;
		}
		else if($this->submission->hasPeerReviewAccess())
		{					
			$add_peer_data = true; 
			
			$peer_class = "ilExPeerReviewGUI";
			$peer_cmd = "updatePeerReviewText";
			$cancel_cmd = "editPeerReview";
			
			$ilCtrl->setParameterByClass($peer_class, "peer_id", $user_id);	
			
			// rating						
			include_once './Services/Rating/classes/class.ilRatingGUI.php';
			$rating = new ilRatingGUI();
			$rating->setObject($this->assignment->getId(), "ass", $user_id, "peer");
			$rating->setUserId($ilUser->getId());
			$rating = '<div id="rtr_widget">'.$rating->getHTML(false, true,
				"il.ExcPeerReview.saveSingleRating(".$user_id.", %rating%)").'</div>';		

			$ilCtrl->setParameterByClass($peer_class, "ssrtg", 1);
			$tpl->addJavaScript("Modules/Exercise/js/ilExcPeerReview.js");
			$tpl->addOnLoadCode("il.ExcPeerReview.setAjax('".
				$ilCtrl->getLinkTargetByClass($peer_class, "updatePeerReviewComments", "", true, false).
				"')");
			$ilCtrl->setParameterByClass($peer_class, "ssrtg", "");			
		}		
		else
		{
			$this->handleTabs();
		}
		
		$a_form = $this->initAssignmentTextForm(true, $cancel_cmd, $peer_class, $peer_cmd, $rating);	
		
		if($add_peer_data)
		{
			if(!stristr($peer_cmd, "peer"))
			{
				include_once "Services/User/classes/class.ilUserUtil.php";
				$a_form->setDescription(ilUserUtil::getNamePresentation($user_id));						
			}
			else
			{							
				$ilCtrl->setParameterByClass($peer_class, "peer_id", "");	
				
				if(!$this->assignment->hasPeerReviewPersonalized())
				{
					$masked_id = $this->submission->getPeerReview()->getPeerMaskedId($ilUser->getId(), $user_id);					
					$a_form->setDescription($lng->txt("id").": ".$masked_id);
				}
				else
				{					
					include_once "Services/User/classes/class.ilUserUtil.php";
					$a_form->setDescription(ilUserUtil::getNamePresentation($user_id));	
				}
								
				foreach($this->submission->getPeerReview()->getPeerReviewsByPeerId($user_id) as $item)
				{
					if($item["giver_id"] == $ilUser->getId())
					{																
						$a_form->getItemByPostVar("comm")->setValue($item["pcomment"]);					
						
						if(!$this->submission->getPeerReview()->validatePeerReviewText($item["pcomment"]))
						{
							ilUtil::sendFailure(sprintf($this->lng->txt("exc_peer_review_chars_invalid"), 
								$this->submission->getAssignment()->getPeerReviewChars()));
						}
						
						break;
					}
				}
			}						
		}
		
		$files = $this->submission->getFiles();
		if($files)
		{
			$files = array_shift($files);
			if(trim($files["atext"]))
			{
				if($files["late"] && 
					!$this->submission->hasPeerReviewAccess())
				{
					ilUtil::sendFailure($this->lng->txt("exc_late_submission"));
				}
				
				$text = $a_form->getItemByPostVar("atxt");
				// mob id to mob src
				$text->setValue(nl2br(ilRTE::_replaceMediaObjectImageSrc($files["atext"], 1)));
			}
		}		
	
		$this->tpl->setContent($a_form->getHTML());	
	}
}
