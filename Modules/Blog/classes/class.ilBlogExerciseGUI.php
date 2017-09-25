<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Modules/Exercise/classes/class.ilExAssignment.php";	
include_once "Modules/Exercise/classes/class.ilExSubmission.php";	

/**
* Class ilBlogExerciseGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilBlogExerciseGUI: 
*/
class ilBlogExerciseGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	protected $node_id; // [int]
	protected $ass_id; // [int]
	protected $file; // [string]
	
	public function __construct($a_node_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->user = $DIC->user();
		$this->lng = $DIC->language();
		$this->node_id = $a_node_id;
		$this->ass_id = (int)$_GET["ass"];
		$this->file = trim($_GET["file"]);
	}
	
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		
		if(!$this->ass_id)
		{
			$this->ctrl->returnToParent($this);
		}
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
	
		switch($next_class)
		{
			default:				
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	public static function checkExercise($a_node_id)
	{			
		global $DIC;

		$tree = $DIC->repositoryTree();
		$ilUser = $DIC->user();
	
		$exercises = ilExSubmission::findUserFiles($ilUser->getId(), $a_node_id);
		if($exercises)
		{
			$info = array();				
			foreach($exercises as $exercise)
			{					
				// #9988
				$active_ref = false;
				foreach(ilObject::_getAllReferences($exercise["obj_id"]) as $ref_id)
				{
					if(!$tree->isSaved($ref_id))
					{
						$active_ref = true;
						break;
					}
				}
				if($active_ref)
				{					
					$part = self::getExerciseInfo($exercise["ass_id"]);
					if($part)
					{
						$info[] = $part;
					}
				}
			}				
			if(sizeof($info))
			{
				return implode("<br />", $info);										
			}
		}		
	}

	protected static function getExerciseInfo($a_assignment_id)
	{		
		global $DIC;

		$lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();
		$ilUser = $DIC->user();
					
		$ass = new ilExAssignment($a_assignment_id);		
		$exercise_id = $ass->getExerciseId();		
		if(!$exercise_id)
		{
			return;
		}
		
		// is the assignment still open?
		$times_up = $ass->afterDeadlineStrict();
		
		// exercise goto
		include_once "./Services/Link/classes/class.ilLink.php";
		$exc_ref_id = array_shift(ilObject::_getAllReferences($exercise_id));
		$exc_link = ilLink::_getStaticLink($exc_ref_id, "exc");
		
		$info = sprintf($lng->txt("blog_exercise_info"), 
			$ass->getTitle(),
			"<a href=\"".$exc_link."\">".
			ilObject::_lookupTitle($exercise_id)."</a>");
		
		// submit button
		if(!$times_up)
		{						
			$ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
			$submit_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "finalize");
			$ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("blog_finalize_blog");
			$button->setPrimary(true);
			$button->setUrl($submit_link);			
			$info .= " ".$button->render();
		}
		
		// submitted files
		include_once "Modules/Exercise/classes/class.ilExSubmission.php";		
		$submission = new ilExSubmission($ass, $ilUser->getId());
		if($submission->hasSubmitted())
		{
			// #16888
			$submitted = $submission->getSelectedObject();				
			
			$ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
			$dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcSubFile");
			$ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");
			
			$rel = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			
			include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
			$button = ilLinkButton::getInstance();
			$button->setCaption("download");		
			$button->setUrl($dl_link);			
			
			$info .= "<br />".sprintf($lng->txt("blog_exercise_submitted_info"), 
				ilDatePresentation::formatDate(new ilDateTime($submitted["ts"], IL_CAL_DATETIME)),
				$button->render());
			
			ilDatePresentation::setUseRelativeDates($rel);
		}		
		
		
		// work instructions incl. files
		
		$tooltip = "";

		$inst = $ass->getInstruction();
		if($inst)
		{
			$tooltip .= nl2br($inst);					
		}

		$ass_files = $ass->getFiles();
		if (count($ass_files) > 0)
		{
			$tooltip .= "<br /><br />";
			
			foreach($ass_files as $file)
			{
				$ilCtrl->setParameterByClass("ilblogexercisegui", "ass", $a_assignment_id);
				$ilCtrl->setParameterByClass("ilblogexercisegui", "file", urlencode($file["name"]));
				$dl_link = $ilCtrl->getLinkTargetByClass("ilblogexercisegui", "downloadExcAssFile");
				$ilCtrl->setParameterByClass("ilblogexercisegui", "file", "");			
				$ilCtrl->setParameterByClass("ilblogexercisegui", "ass", "");			
				
				$tooltip .= $file["name"].": <a href=\"".$dl_link."\">".
					$lng->txt("download")."</a>";										
			}
		}			
		
		if($tooltip)
		{
			$ol_id = "exc_ass_".$a_assignment_id;

			include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
			$overlay = new ilOverlayGUI($ol_id);

			// overlay
			$overlay->setAnchor($ol_id."_tr");
			$overlay->setTrigger($ol_id."_tr", "click", $ol_id."_tr");
			$overlay->add();

			$info .= "<div id=\"".$ol_id."_tr\"><a href=\"#\">".$lng->txt("exc_instruction")."</a></div>".
				"<div id=\"".$ol_id."\" style=\"display:none; padding:10px;\" class=\"ilOverlay\">".$tooltip."</div>";
		}
		
		return "<div>".$info."</div>";
	}
	
	protected function downloadExcAssFile()
	{
		if($this->file)
		{		
			include_once "Modules/Exercise/classes/class.ilExAssignment.php";			
			$ass = new ilExAssignment($this->ass_id);			
			$ass_files = $ass->getFiles();
			if (count($ass_files) > 0)
			{
				foreach($ass_files as $file)
				{
					if($file["name"] == $this->file)
					{
						ilUtil::deliverFile($file["fullpath"], $file["name"]);						
					}												
				}
			}
		}					
	}
	
	protected function downloadExcSubFile()
	{
		$ilUser = $this->user;
				
		$ass = new ilExAssignment($this->ass_id);
		$submission = new ilExSubmission($ass, $ilUser->getId());
		$submitted = $submission->getFiles();			
		if (count($submitted) > 0)
		{
			$submitted = array_pop($submitted);			

			$user_data = ilObjUser::_lookupName($submitted["user_id"]);
			$title = ilObject::_lookupTitle($submitted["obj_id"])." - ".
				$ass->getTitle()." - ".
				$user_data["firstname"]." ".
				$user_data["lastname"]." (".
				$user_data["login"].").zip";

			ilUtil::deliverFile($submitted["filename"], $title);																	
		}			
	}
		
	/**
	 * Finalize and submit blog to exercise
	 */
	protected function finalize()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		include_once "Modules/Exercise/classes/class.ilExSubmissionBaseGUI.php";
		include_once "Modules/Exercise/classes/class.ilExSubmissionObjectGUI.php";		
		$exc_gui = ilExSubmissionObjectGUI::initGUIForSubmit($this->ass_id);
		$exc_gui->submitBlog($this->node_id);

		ilUtil::sendSuccess($lng->txt("blog_finalized"), true);
		$ilCtrl->returnToParent($this);		
	}	
}
