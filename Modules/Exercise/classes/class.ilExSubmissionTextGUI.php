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
	
	protected function initAssignmentTextForm($a_read_only = false)
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
			$form->setFormAction($ilCtrl->getFormAction($this, "returnToParent"));
			$text = new ilNonEditableValueGUI($this->lng->txt("exc_files_returned_text"), "atxt", true);	
			$form->addItem($text);					
		}
		$form->addCommandButton("returnToParent", $this->lng->txt("cancel"));
		
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
			// extended deadline date should not be presented anywhere
			// see ilExAssignmentGUI::addSchedule()
			$dl_info = ilDatePresentation::formatDate(new ilDateTime($this->assignment->getDeadline(), IL_CAL_UNIX));
					
			// #16151 - extended deadline warning (only after deadline passed)
			if($this->assignment->getDeadline() < time())
			{							
				$dl = ilDatePresentation::formatDate(new ilDateTime($this->assignment->getDeadline(),IL_CAL_UNIX));
				$dl = '<br /><span class="warning">'.sprintf($this->lng->txt("exc_late_submission_warning"), $dl).'</span>';							
				$dl_info .= $dl;
			}
			
			ilUtil::sendInfo($this->lng->txt("exc_edit_until").": ".$dl_info);
		}
		
		$this->handleTabs();

		global $ilHelp;
		$ilHelp->setScreenIdComponent("exc");
		$ilHelp->setScreenId("text_submission");

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
				// #16532 - always send notifications
				$this->handleNewUpload();					
				
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
		if(!$this->submission->isTutor())		
		{
			$this->handleTabs();
		}
		
		$a_form = $this->initAssignmentTextForm(true);	
		
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
