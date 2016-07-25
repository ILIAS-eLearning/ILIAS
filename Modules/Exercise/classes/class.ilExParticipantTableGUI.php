<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
include_once("./Services/Rating/classes/class.ilRatingGUI.php");

/**
* Exercise participant table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilExParticipantTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_part_id)
	{
		global $ilCtrl, $lng, $ilAccess;
		
		$this->exc = $a_exc;
		$this->exc_id = $this->exc->getId();
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
		$this->part_id = $a_part_id;
		
		$this->setId("exc_part_".$this->exc_id."_".$this->part_id);
		
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		if ($this->part_id > 0)
		{
			$name = ilObjUser::_lookupName($this->part_id);
			if(trim($name["login"]))
			{
				$ilCtrl->setParameter($a_parent_obj, "member_id", $this->part_id);
				$this->user = new ilObjUser($this->part_id);
			}
			// #14650 - invalid user
			else
			{
				$ilCtrl->setParameter($a_parent_obj, "member_id", "");
				$ilCtrl->redirect($a_parent_obj, $a_parent_cmd);
			}
		}
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$data = ilExAssignment::getAssignmentDataOfExercise($this->exc_id);
		$this->setData($data);
		
//var_dump($data);

		if ($this->part_id > 0)
		{
			$this->setTitle($lng->txt("exc_participant").": ".
				$name["lastname"].", ".$name["firstname"]." [".$name["login"]."]");
		}
		else
		{
			$this->setTitle($lng->txt("exc_participant"));
		}
		
		$this->setTopCommands(true);
		//$this->setLimit(9999);
		
//		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("exc_assignment"), "order_val");
		$this->addColumn($this->lng->txt("exc_submission"), "submission");
		$this->addColumn($this->lng->txt("exc_grading"), "solved_time");
//		$this->addColumn($this->lng->txt("mail"), "feedback_time");
		$this->addColumn($this->lng->txt("feedback"), "feedback_time");
		
		$this->setDefaultOrderField("order_val");
		$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.exc_participant_row.html", "Modules/Exercise");
		//$this->disable("footer");
		$this->setEnableTitle(true);
//		$this->setSelectAllCheckbox("assid");

		if ($this->part_id > 0)
		{
			$this->addCommandButton("saveStatusParticipant", $lng->txt("exc_save_changes"));
		}
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
		$this->overlay_tpl = new ilTemplate("tpl.exc_learner_comment_overlay.html", true, true, "Modules/Exercise");
		
		// #18327
		if(!$ilAccess->checkAccessOfUser($a_part_id, "read","", $this->exc->getRefId()) &&
			is_array($info = $ilAccess->getInfo()))
		{
			$this->setDescription('<span class="warning">'.$info[0]['text'].'</span>');
		}		
	}
	
	/**
	 * Check whether field is numeric
	 */
	function numericOrdering($a_f)
	{
		if (in_array($a_f, array("order_val")))
		{
			return true;
		}
		return false;
	}
	
	
	/**
	* Fill table row
	*/
	protected function fillRow($d)
	{
		global $lng, $ilCtrl;		
			
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $d["id"]);
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->part_id);
				
		$this->tpl->setVariable("TXT_ASS_TITLE", $d["title"]);					
		
		$assignment = new ilExAssignment($d["id"]);
		$member_status = $assignment->getMemberStatus($this->part_id);
		$submission = new ilExSubmission($assignment, $this->part_id);
		$file_info = $submission->getDownloadedFilesInfoForTableGUIS($this->parent_obj, $this->parent_cmd);
		
		$has_no_team_yet = false;
		if($assignment->hasTeam())
		{			
			$members = $submission->getTeam()->getMembers();
			
			// #11957
			if(sizeof($members))
			{
				$this->tpl->setCurrentBlock("ass_members");
				foreach($members as $member_id)
				{					
					$this->tpl->setVariable("TXT_MEMBER_NAME", 
						ilObjUser::_lookupFullname($member_id));
					$this->tpl->parseCurrentBlock();					
				}			
				
				$this->tpl->setVariable("HREF_LOG", 
					$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "showTeamLog"));
				$this->tpl->setVariable("TXT_LOG", $lng->txt("exc_team_log"));				
			}
			else
			{
				// #11957
				$has_no_team_yet = true;
				$this->tpl->setCurrentBlock("team_info");
				$this->tpl->setVariable("TXT_TEAM_INFO", $lng->txt("exc_no_team_yet"));
				$this->tpl->setVariable("TXT_CREATE_TEAM", $lng->txt("exc_create_team"));
							
				$this->tpl->setVariable("URL_CREATE_TEAM", 						
					$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "createSingleMemberTeam"));
				
				if($file_info["files"]["count"])
				{
					$this->tpl->setVariable("TEAM_FILES_INFO", "<br />".
						$file_info["files"]["txt"].": ".
						$file_info["files"]["count"]);
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if(!$has_no_team_yet)
		{
			$this->tpl->setVariable("VAL_CHKBOX",
				ilUtil::formCheckbox(0, "assid[".$d["id"]."]",1));
			$this->tpl->setVariable("VAL_ID",
				$d["id"]);

			$this->tpl->setVariable("VAL_LAST_SUBMISSION", $file_info["last_submission"]["value"]);
			$this->tpl->setVariable("TXT_LAST_SUBMISSION", $file_info["last_submission"]["txt"]);

			$this->tpl->setVariable("TXT_SUBMITTED_FILES", $file_info["files"]["txt"]);
			$this->tpl->setVariable("VAL_SUBMITTED_FILES", $file_info["files"]["count"]);

			if($file_info["files"]["download_url"])
			{
				$this->tpl->setCurrentBlock("download_link");
				$this->tpl->setVariable("LINK_DOWNLOAD", $file_info["files"]["download_url"]);
				$this->tpl->setVariable("TXT_DOWNLOAD", $file_info["files"]["download_txt"]);		
				$this->tpl->parseCurrentBlock();
			}

			if($file_info["files"]["download_new_url"])
			{
				$this->tpl->setCurrentBlock("download_link");
				$this->tpl->setVariable("LINK_NEW_DOWNLOAD", $file_info["files"]["download_new_url"]);
				$this->tpl->setVariable("TXT_NEW_DOWNLOAD", $file_info["files"]["download_new_txt"]);		
				$this->tpl->parseCurrentBlock();
			}

			// note
			$this->tpl->setVariable("TXT_NOTE", $lng->txt("exc_note_for_tutor"));
			$this->tpl->setVariable("NAME_NOTE",
				"notice[".$d["id"]."]");
			$this->tpl->setVariable("VAL_NOTE",
				ilUtil::prepareFormOutput($member_status->getNotice()));

			// comment for learner
			
			$lcomment_value = $member_status->getComment();
			
			$overlay_id = "excasscomm_".$d["id"]."_".$this->part_id;
			$overlay_trigger_id = $overlay_id."_tr";
			$overlay = new ilOverlayGUI($overlay_id);
			$overlay->setAnchor($overlay_trigger_id);
			$overlay->setTrigger($overlay_trigger_id, "click", $overlay_trigger_id);
			$overlay->add();
			
			$this->tpl->setVariable("LCOMMENT_ID", $overlay_id."_snip");
			$this->tpl->setVariable("LCOMMENT_SNIPPET", ilUtil::shortenText($lcomment_value, 25, true));
			$this->tpl->setVariable("COMMENT_OVERLAY_TRIGGER_ID", $overlay_trigger_id);
			$this->tpl->setVariable("COMMENT_OVERLAY_TRIGGER_TEXT", $lng->txt("exc_comment_for_learner_edit"));
								
			$lcomment_form = new ilPropertyFormGUI();	
			$lcomment_form->setId($overlay_id);
			$lcomment_form->setPreventDoubleSubmission(false);
			
			$lcomment = new ilTextAreaInputGUI($lng->txt("exc_comment_for_learner"), "lcomment_".$d["id"]."_".$this->part_id);
			$lcomment->setInfo($lng->txt("exc_comment_for_learner_info"));
			$lcomment->setValue($lcomment_value);
			$lcomment->setCols(45);
			$lcomment->setRows(5);			
			$lcomment_form->addItem($lcomment);
			
			$lcomment_form->addCommandButton("save", $lng->txt("save"));
			// $lcomment_form->addCommandButton("cancel", $lng->txt("cancel"));
			
			$this->overlay_tpl->setCurrentBlock("overlay_bl");			
			$this->overlay_tpl->setVariable("COMMENT_OVERLAY_ID", $overlay_id);
			$this->overlay_tpl->setVariable("COMMENT_OVERLAY_FORM", $lcomment_form->getHTML());
			$this->overlay_tpl->parseCurrentBlock();
			
			$status = $member_status->getStatus();
			$this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
			$this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("exc_notgraded"));
			$this->tpl->setVariable("TXT_PASSED", $lng->txt("exc_passed"));
			$this->tpl->setVariable("TXT_FAILED", $lng->txt("exc_failed"));
			if (($sd = $member_status->getStatusTime()) > 0)
			{
				$this->tpl->setCurrentBlock("status_date");
				$this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
				$this->tpl->setVariable('VAL_STATUS_DATE',
					ilDatePresentation::formatDate(new ilDateTime($sd,IL_CAL_DATETIME)));
				$this->tpl->parseCurrentBlock();
			}		
			$pic = $member_status->getStatusIcon();
			$this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
			$this->tpl->setVariable("ALT_STATUS", $lng->txt("exc_".$status));

			// mark
			$this->tpl->setVariable("TXT_MARK", $lng->txt("exc_mark"));
			$this->tpl->setVariable("NAME_MARK",
				"mark[".$d["id"]."]");
			$mark = $member_status->getMark();
			$this->tpl->setVariable("VAL_MARK", ilUtil::prepareFormOutput($mark));

			// feedback			
			if (($ft = $member_status->getFeedbackTime()) > 0)
			{
				$this->tpl->setCurrentBlock("feedback_date");
				$this->tpl->setVariable("TXT_FEEDBACK_MAIL_SENT",
					sprintf($lng->txt("exc_sent_at"),
					ilDatePresentation::formatDate(new ilDateTime($ft,IL_CAL_DATETIME))));
				$this->tpl->parseCurrentBlock();
			}
			$ilCtrl->setParameter($this->parent_obj, "rcp_to", $this->user->getLogin());
			$this->tpl->setVariable("LINK_FEEDBACK",
				$ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail"));
				//"ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$mem_obj->getLogin());
			$this->tpl->setVariable("TXT_FEEDBACK",
				$lng->txt("exc_send_mail"));
			$ilCtrl->setParameter($this->parent_obj, "rcp_to", "");

			$storage = new ilFSStorageExercise($this->exc_id, $d["id"]);
			$cnt_files = $storage->countFeedbackFiles($submission->getFeedbackId());			
			$this->tpl->setVariable("LINK_FILE_FEEDBACK",
				$ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));			
			if ($cnt_files == 0)
			{
				$this->tpl->setVariable("TXT_FILE_FEEDBACK",
					$lng->txt("exc_add_feedback_file"));
			}
			else
			{
				$this->tpl->setVariable("TXT_FILE_FEEDBACK",
					$lng->txt("exc_fb_files")." (".$cnt_files.")");
			}

			// peer review / rating
			if($peer_review = $submission->getPeerReview())
			{				
				// :TODO: validate?
				$given = $peer_review->countGivenFeedback(true, $this->part_id);
				$received = sizeof($peer_review->getPeerReviewsByPeerId($this->part_id, true));
				
				$this->tpl->setCurrentBlock("peer_review_bl");
				
				$this->tpl->setVariable("LINK_PEER_REVIEW_GIVEN", 
					$ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showGivenPeerReview"));
				$this->tpl->setVariable("TXT_PEER_REVIEW_GIVEN", 
					$lng->txt("exc_peer_review_given")." (".$given.")");	
				
				$this->tpl->setVariable("TXT_PEER_REVIEW_RECEIVED", 
					$lng->txt("exc_peer_review_show")." (".$received.")");				
				$this->tpl->setVariable("LINK_PEER_REVIEW_RECEIVED", 
					$ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showReceivedPeerReview"));
				
				/* :TODO: restrict to valid?
				$rating = new ilRatingGUI();
				$rating->setObject($d["id"], "ass", $this->part_id, "peer");
				$rating->setUserId(0);			
				$this->tpl->setVariable("VAL_RATING", $rating->getHTML(true, false));		
				*/
				
				$this->tpl->parseCurrentBlock();
			}

		}
		else
		{
			$this->tpl->touchBlock("member_has_no_team_bl");
		}
			
		$ilCtrl->setParameter($this->parent_obj, "ass_id", "");
		$ilCtrl->setParameter($this->parent_obj, "member_id", "");				
	}
	
	public function render()
	{
		global $ilCtrl;
		
		$url = $ilCtrl->getLinkTarget($this->getParentObject(), "saveCommentForLearners", "", true, false);		
		$this->overlay_tpl->setVariable("AJAX_URL", $url);
		
		return parent::render().
			$this->overlay_tpl->get();
	}
}
?>