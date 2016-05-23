<?php

/**
 * GUI class for exercise assignments
 * 
 * This is not a real GUI class, could be moved to ilObjExerciseGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilExAssignmentGUI
{
	protected $exc; // [ilObjExercise]
	protected $current_ass_id; // [int]
	
	/**
	 * Constructor
	 */
	function __construct(ilObjExercise $a_exc)
	{
		$this->exc = $a_exc;
	}	
	
	/**
	 * Get assignment header for overview
	 */
	function getOverviewHeader(ilExAssignment $a_ass)
	{
		global $lng;
		
		$lng->loadLanguageModule("exc");
		
		$tpl = new ilTemplate("tpl.assignment_head.html", true, true, "Modules/Exercise");
		
		// we are completely ignoring the extended deadline here

		if ($a_ass->getDeadline() &&
			$a_ass->getDeadline() < time())
		{
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_ended_on"));
			$tpl->setVariable("PROP_VAL",
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX)));
			$tpl->parseCurrentBlock();
			
			// #14077
			if($a_ass->getPeerReview() &&
				$a_ass->getPeerReviewDeadline())
			{								
				$tpl->setCurrentBlock("prop");
				$tpl->setVariable("PROP", $lng->txt("exc_peer_review_deadline"));
				$tpl->setVariable("PROP_VAL",
					ilDatePresentation::formatDate(new ilDateTime($a_ass->getPeerReviewDeadline(),IL_CAL_UNIX)));
				$tpl->parseCurrentBlock();
			}		
		}
		else if ($a_ass->notStartedYet())
		{
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_starting_on"));
			$tpl->setVariable("PROP_VAL",
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getStartTime(),IL_CAL_UNIX)));
			$tpl->parseCurrentBlock();
		}
		else
		{					
			$time_str = $this->getTimeString($a_ass->getDeadline());
			$tpl->setCurrentBlock("prop");
			$tpl->setVariable("PROP", $lng->txt("exc_time_to_send"));
			$tpl->setVariable("PROP_VAL", $time_str);
			$tpl->parseCurrentBlock();
	
			if ($a_ass->getDeadline())
			{
				$tpl->setCurrentBlock("prop");
				$tpl->setVariable("PROP", $lng->txt("exc_edit_until"));
				$tpl->setVariable("PROP_VAL",
					ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX)));
				$tpl->parseCurrentBlock();
			}
			
		}

		$mand = "";
		if ($a_ass->getMandatory())
		{
			$mand = " (".$lng->txt("exc_mandatory").")";
		}
		$tpl->setVariable("TITLE", $a_ass->getTitle().$mand);

		// status icon
		$stat = $a_ass->getMemberStatus()->getStatus();
		$pic = $a_ass->getMemberStatus()->getStatusIcon();	
		$tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
		$tpl->setVariable("ALT_STATUS", $lng->txt("exc_".$stat));

		return $tpl->get();
	}

	/**
	 * Get assignment body for overview
	 */
	function getOverviewBody(ilExAssignment $a_ass)
	{		
		$this->current_ass_id = $a_ass->getId();
		
		$tpl = new ilTemplate("tpl.assignment_body.html", true, true, "Modules/Exercise");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		include_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");

		if(IS_PAYMENT_ENABLED)
		{
			include_once './Services/Payment/classes/class.ilPaymentObject.php';
		}
		
		$info = new ilInfoScreenGUI(null);
		$info->setTableClass("");
		
		$this->addInstructions($info, $a_ass);
		$this->addSchedule($info, $a_ass);
		
		if ($this->exc->getShowSubmissions())
		{
			$this->addPublicSubmissions($info, $a_ass);
		}
		
		if (!$a_ass->notStartedYet())
		{
			$this->addFiles($info, $a_ass);
			$this->addSubmission($info, $a_ass);			
		}

		$tpl->setVariable("CONTENT", $info->getHTML());
		
		return $tpl->get();
	}
	
	
	protected function addInstructions(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{		
		global $lng;
		
		if (!$a_ass->notStartedYet())
		{			
			$inst = $a_ass->getInstruction();	
			if(trim($inst))
			{				
				$a_info->addSection($lng->txt("exc_instruction"));

				$is_html = (strlen($inst) != strlen(strip_tags($inst)));
				if(!$is_html)
				{
					$inst = nl2br(ilUtil::makeClickable($inst, true));
				}						
				$a_info->addProperty("", $inst);
			}
		}
	}
	
	protected function addSchedule(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{		
		global $lng;
		
		$a_info->addSection($lng->txt("exc_schedule"));
		if ($a_ass->getStartTime() > 0)
		{
			$a_info->addProperty($lng->txt("exc_start_time"),
				ilDatePresentation::formatDate(new ilDateTime($a_ass->getStartTime(),IL_CAL_UNIX)));
		}
		if ($a_ass->getDeadline())
		{
			$until = ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX));
			
			// extended deadline date should not be presented anywhere
			
			// extended deadline info/warning
			if($a_ass->getDeadline() < time() &&				
				$a_ass->beforeDeadline())
			{				
				$dl = ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(),IL_CAL_UNIX));
				$dl = "<br />".sprintf($lng->txt("exc_late_submission_warning"), $dl);								
				$dl = '<span class="warning">'.$dl.'</span>';						
				$until .= $dl;
			}
			$a_info->addProperty($lng->txt("exc_edit_until"), $until);			
		}
		$time_str = $this->getTimeString($a_ass->getDeadline());
		if (!$a_ass->notStartedYet())
		{
			$a_info->addProperty($lng->txt("exc_time_to_send"),
				"<b>".$time_str."</b>");
		}
	}
	
	protected function addPublicSubmissions(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{		
		global $lng;
		
		if ($a_ass->afterDeadline())
		{				
			$button = ilLinkButton::getInstance();				
			$button->setCaption("exc_list_submission");
			$button->setUrl($this->getSubmissionLink("listPublicSubmissions"));							

			$a_info->addProperty($lng->txt("exc_public_submission"), $button->render());
		}
		else
		{
			$a_info->addProperty($lng->txt("exc_public_submission"),
				$lng->txt("exc_msg_public_submission"));
		}		
	}
	
	protected function addFiles(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{		
		global $lng, $ilCtrl;
		
		$files = $a_ass->getFiles();
		if (count($files) > 0)
		{
			$a_info->addSection($lng->txt("exc_files"));
			foreach($files as $file)
			{
				// if download must be purchased first show a "buy"-button
				if(IS_PAYMENT_ENABLED && (ilPaymentObject::_isBuyable($this->exc->getRefId(),'download') &&
				   !ilPaymentObject::_hasAccess($this->exc->getRefId(),'','download')))
				{
					$a_info->addProperty($file["name"],
						$lng->txt("buy"),
						$ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails"));
				}
				else
				{						
					$a_info->addProperty($file["name"],
						$lng->txt("download"),
						$this->getSubmissionLink("downloadFile", array("file"=>urlencode($file["name"]))));
				}
			}
		}			
	}

	protected function addSubmission(ilInfoScreenGUI $a_info, ilExAssignment $a_ass)
	{		
		global $lng, $ilCtrl, $ilUser;
						
		// if submission must be purchased first
		if(IS_PAYMENT_ENABLED
			&& (ilPaymentObject::_isBuyable($this->exc->getRefId(),'upload')
			&& !ilPaymentObject::_hasAccess($this->exc->getRefId(),'','upload')))
		{
			$a_info->addSection($lng->txt("exc_your_submission"));

			$ilCtrl->clearParameters($this);

			$ilCtrl->setParameter($this, "ref_id", $this->exc->getRefId());
			$ilCtrl->setParameter($this,'subtype','upload');
			$a_info->addProperty($lng->txt('exc_hand_in'),
				$lng->txt("buy"),
				$ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails"));
		}
		else 
		{						
			$a_info->addSection($lng->txt("exc_your_submission"));
			
			include_once "Modules/Exercise/classes/class.ilExSubmission.php";
			$submission = new ilExSubmission($a_ass, $ilUser->getId());			
				
			include_once "Modules/Exercise/classes/class.ilExSubmissionGUI.php";
			ilExSubmissionGUI::getOverviewContent($a_info, $submission);
				
			$last_sub = null;
			if($submission->hasSubmitted())
			{
				$last_sub = $submission->getLastSubmission();
				if($last_sub)
				{
					$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
					$a_info->addProperty($lng->txt("exc_last_submission"), $last_sub);
				}				
			}

			include_once "Modules/Exercise/classes/class.ilExPeerReviewGUI.php";
			ilExPeerReviewGUI::getOverviewContent($a_info, $submission);
			
			// global feedback / sample solution
			if($a_ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE)
			{
				$show_global_feedback = ($a_ass->afterDeadlineStrict() && $a_ass->getFeedbackFile());
			}
			else
			{
				$show_global_feedback = ($last_sub && $a_ass->getFeedbackFile());
			}								

			$this->addSubmissionFeedback($a_info, $a_ass, $submission->getFeedbackId(), $show_global_feedback);												
		}
	}
	
	protected function addSubmissionFeedback(ilInfoScreenGUI $a_info, ilExAssignment $a_ass, $a_feedback_id, $a_show_global_feedback)
	{
		global $lng;
		
		$storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());					
		$cnt_files = $storage->countFeedbackFiles($a_feedback_id);
		
		$lpcomment = $a_ass->getMemberStatus()->getComment();
		$mark = $a_ass->getMemberStatus()->getMark();
		$status = $a_ass->getMemberStatus()->getStatus();	
		
		if ($lpcomment != "" || 
			$mark != "" || 
			$status != "notgraded" || 
			$cnt_files > 0 || 
			$a_show_global_feedback)
		{
			$a_info->addSection($lng->txt("exc_feedback_from_tutor"));
			if ($lpcomment != "")
			{
				$a_info->addProperty($lng->txt("exc_comment"),
					$lpcomment);
			}
			if ($mark != "")
			{
				$a_info->addProperty($lng->txt("exc_mark"),
					$mark);
			}

			if ($status == "") 
			{
//				  $a_info->addProperty($lng->txt("status"),
//						$lng->txt("message_no_delivered_files"));				
			}
			else if ($status != "notgraded")
			{
				$img = '<img src="'.ilUtil::getImagePath("scorm/".$status.".svg").'" '.
					' alt="'.$lng->txt("exc_".$status).'" title="'.$lng->txt("exc_".$status).
					'" />';
				$a_info->addProperty($lng->txt("status"),
					$img." ".$lng->txt("exc_".$status));
			}

			if ($cnt_files > 0)
			{
				$a_info->addSection($lng->txt("exc_fb_files").
					'<a name="fb'.$a_ass->getId().'"></a>');

				if($cnt_files > 0)
				{
					$files = $storage->getFeedbackFiles($a_feedback_id);
					foreach($files as $file)
					{								
						$a_info->addProperty($file,
							$lng->txt("download"),
							$this->getSubmissionLink("downloadFeedbackFile", array("file"=>urlencode($file))));								
					}
				}												
			}	

			// #15002 - global feedback																	
			if($a_show_global_feedback)
			{
				$a_info->addSection($lng->txt("exc_global_feedback_file"));

				$a_info->addProperty($a_ass->getFeedbackFile(),
					$lng->txt("download"),
					$this->getSubmissionLink("downloadGlobalFeedbackFile"));								
			}
		}			
	}
	
	/**
	 * Get time string for deadline
	 */
	function getTimeString($a_deadline)
	{
		global $lng;
		
		if ($a_deadline == 0)
		{
			return $lng->txt("exc_no_deadline_specified");
		}
		
		if ($a_deadline - time() <= 0)
		{
			$time_str = $lng->txt("exc_time_over_short");
		}
		else
		{
			$time_str = ilUtil::period2String(new ilDateTime($a_deadline, IL_CAL_UNIX));
		}

		return $time_str;
	}
	
	protected function getSubmissionLink($a_cmd, array $a_params = null)
	{
		global $ilCtrl;
		
		if(is_array($a_params))
		{
			foreach($a_params as $name => $value)
			{
				$ilCtrl->setParameterByClass("ilexsubmissiongui", $name, $value);
			}
		}
		
		$ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", $this->current_ass_id);
		$url = $ilCtrl->getLinkTargetByClass("ilexsubmissiongui", $a_cmd);
		$ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");
		
		if(is_array($a_params))
		{
			foreach($a_params as $name => $value)
			{
				$ilCtrl->setParameterByClass("ilexsubmissiongui", $name, "");
			}
		}
		
		return $url;
	}
}
