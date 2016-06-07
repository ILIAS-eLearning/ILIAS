<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
include_once("./Modules/Exercise/classes/class.ilExAssignmentMemberStatus.php");
include_once("./Modules/Exercise/classes/class.ilExAssignmentTeam.php");
include_once("./Modules/Exercise/classes/class.ilExSubmission.php");

/**
* Exercise member table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilExerciseMemberTableGUI extends ilTable2GUI
{
	protected $exc;
	protected $ass;
	protected $exc_id;
	protected $ass_id;
	protected $sent_col;
	protected $selected = array();
	protected $teams = array();
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_ass)
	{
		global $ilCtrl, $lng;
		
		$this->exc = $a_exc;
		$this->exc_id = $this->exc->getId();
		$this->ass = $a_ass;
		$this->ass_id = $this->ass->getId();
		$this->setId("exc_mem_".$this->ass_id);
		
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$this->storage = new ilFSStorageExercise($this->exc_id, $this->ass_id);
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setTitle($lng->txt("exc_assignment").": ".$this->ass->getTitle());
		$this->setTopCommands(true);
		//$this->setLimit(9999);
		
		$data = $this->ass->getMemberListData();
		
		// team upload?  (1 row == 1 team)
		if($this->ass->hasTeam())
		{	
			$this->teams = ilExAssignmentTeam::getInstancesFromMap($this->ass_id);							
			$team_map = ilExAssignmentTeam::getAssignmentTeamMap($this->ass_id);
			
			$tmp = array();
			
			foreach($data as $item)
			{
				$team_id = $team_map[$item["usr_id"]];
								
				if(!$team_id)
				{
					// #11058
					// $team_id = $ass_obj->getTeamId($item["usr_id"], true);
					
					// #11957
					$team_id = "nty".$item["usr_id"];
				}
				
				if(!isset($tmp[$team_id]))
				{
					$tmp[$team_id] = $item;
				}
				
				$tmp[$team_id]["team"][$item["usr_id"]] = $item["name"];
				$tmp[$team_id]["team_id"] = $team_id;
			}
			
			$data = $tmp;
			unset($tmp);
		}
		else
		{
			// peer review / rating	
			$ass_obj = new ilExAssignment($this->ass_id);
			if($ass_obj->getPeerReview())
			{
				include_once './Services/Rating/classes/class.ilRatingGUI.php';
			}														
		}
		
		$this->setData($data);
		
		$this->addColumn("", "", "1", true);
				
		if(!$this->ass->hasTeam())
		{
			$this->selected = $this->getSelectedColumns();				
			if(in_array("image", $this->selected))
			{
				$this->addColumn($this->lng->txt("image"), "", "1");
			}
			$this->addColumn($this->lng->txt("name"), "name");
			if(in_array("login", $this->selected))
			{
				$this->addColumn($this->lng->txt("login"), "login");
			}
		}
		else
		{
			$this->addColumn($this->lng->txt("exc_team"));
		}
		
		$this->sent_col = ilExAssignmentMemberStatus::lookupAnyExerciseSent($this->ass_id);
		if ($this->sent_col)
		{
			$this->addColumn($this->lng->txt("exc_exercise_sent"), "sent_time");
		}
		$this->addColumn($this->lng->txt("exc_submission"), "submission");
		$this->addColumn($this->lng->txt("exc_grading"), "solved_time");
		$this->addColumn($this->lng->txt("feedback"), "feedback_time");
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.exc_members_row.html", "Modules/Exercise");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->setSelectAllCheckbox("member");

		$this->addMultiCommand("saveStatusSelected", $lng->txt("exc_save_selected"));
		$this->addMultiCommand("redirectFeedbackMail", $lng->txt("exc_send_mail"));
		$this->addMultiCommand("sendMembers", $lng->txt("exc_send_assignment"));
		
		if($this->ass->hasTeam())
		{
			$this->addMultiCommand("createTeams", $lng->txt("exc_team_multi_create"));
			$this->addMultiCommand("dissolveTeams", $lng->txt("exc_team_multi_dissolve"));
		}
		
		$this->addMultiCommand("confirmDeassignMembers", $lng->txt("exc_deassign_members"));	
		
		$this->addCommandButton("saveStatusAll", $lng->txt("exc_save_all"));	
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
		$this->overlay_tpl = new ilTemplate("tpl.exc_learner_comment_overlay.html", true, true, "Modules/Exercise");
	}
	
	function getSelectableColumns()
	{
		$columns = array();
		
		$columns["image"] = array(
				"txt" => $this->lng->txt("image"),
				"default" => true
			);
		
		$columns["login"] = array(
				"txt" => $this->lng->txt("login"),
				"default" => true
			);
		
		return $columns;
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($member)
	{
		global $lng, $ilCtrl, $ilAccess;
		
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $this->ass_id);
		$ilCtrl->setParameter($this->parent_obj, "member_id", $member["usr_id"]);

		include_once "./Services/Object/classes/class.ilObjectFactory.php";		
		$member_id = $member["usr_id"];

		if(!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
		{
			return;
		}
		
		$has_no_team_yet = (substr($member["team_id"], 0, 3) == "nty");
		$member_status = $this->ass->getMemberStatus($member_id);
	
		// checkbox
		$this->tpl->setVariable("VAL_CHKBOX",
			ilUtil::formCheckbox(0,"member[$member_id]",1));
		$this->tpl->setVariable("VAL_ID", $member_id);		
		
		if(!$has_no_team_yet)
		{
			// mail sent
			if ($this->sent_col)
			{
				if ($member_status->getSent())
				{
					$this->tpl->setCurrentBlock("mail_sent");
					if (($st = $member_status->getSentTime()) > 0)
					{
						$this->tpl->setVariable("TXT_MAIL_SENT",
							sprintf($lng->txt("exc_sent_at"),
							ilDatePresentation::formatDate(new ilDateTime($st,IL_CAL_DATETIME))));
					}
					else
					{
						$this->tpl->setVariable("TXT_MAIL_SENT",
							$lng->txt("sent"));
					}
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setCurrentBlock("mail_sent");
					$this->tpl->setVariable("TXT_MAIL_SENT",
						"&nbsp;");
					$this->tpl->parseCurrentBlock();
				}
			}	
		}
		
		if(!isset($member["team"]))
		{
			$submission = new ilExSubmission($this->ass, $member_id);
		}
		else
		{			
			if(!$has_no_team_yet)
			{
				$member_team = $this->teams[$member["team_id"]];
			}
			else
			{
				// ilExSubmission should not try to auto-load
				$member_team = new ilExAssignmentTeam();
			}
			$submission = new ilExSubmission($this->ass, $member_id, $member_team);
		}				
		$file_info = $submission->getDownloadedFilesInfoForTableGUIS($this->parent_obj, $this->parent_cmd);
			
		// name and login
		if(!isset($member["team"]))
		{
			$this->tpl->setVariable("TXT_NAME",
				$member["name"]);
			
			if(in_array("login", $this->selected))
			{
				$this->tpl->setVariable("TXT_LOGIN",
					"[".$member["login"]."]");
			}
			
			if(in_array("image", $this->selected))
			{
				// image
				$this->tpl->setVariable("USR_IMAGE",
					$mem_obj->getPersonalPicturePath("xxsmall"));
				$this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));
			}
			
			// #18327
			if(!$ilAccess->checkAccessOfUser($member_id, "read","", $this->exc->getRefId()) &&
				is_array($info = $ilAccess->getInfo()))
			{
				$this->tpl->setCurrentBlock('access_warning');
				$this->tpl->setVariable('PARENT_ACCESS', $info[0]["text"]);
				$this->tpl->parseCurrentBlock();
			}			
		}
		// team upload
		else
		{									
			asort($member["team"]);
			foreach($member["team"] as $team_member_id => $team_member_name) // #10749
			{
				if(sizeof($member["team"]) > 1)
				{
					$ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", $team_member_id);
					$url = $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "confirmRemoveTeamMember");
					$ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", "");
					
					include_once "Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php";
					
					$this->tpl->setCurrentBlock("team_member_removal_bl");
					$this->tpl->setVariable("URL_TEAM_MEMBER_REMOVAL", $url);
					$this->tpl->setVariable("TXT_TEAM_MEMBER_REMOVAL", 
						ilGlyphGUI::get(ilGlyphGUI::CLOSE, $lng->txt("remove")));
					$this->tpl->parseCurrentBlock();
				}
								
				// #18327
				if(!$ilAccess->checkAccessOfUser($team_member_id, "read","", $this->exc->getRefId()) &&
					is_array($info = $ilAccess->getInfo()))
				{
					$this->tpl->setCurrentBlock('team_access_warning');
					$this->tpl->setVariable('TEAM_PARENT_ACCESS', $info[0]["text"]);
					$this->tpl->parseCurrentBlock();
				}		
				
				$this->tpl->setCurrentBlock("team_member");
				$this->tpl->setVariable("TXT_MEMBER_NAME", $team_member_name);
				$this->tpl->parseCurrentBlock();				
			}
						
			if(!$has_no_team_yet)
			{
				$this->tpl->setCurrentBlock("team_log");
				$this->tpl->setVariable("HREF_LOG", 
					$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "showTeamLog"));
				$this->tpl->setVariable("TXT_LOG", $lng->txt("exc_team_log"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{				
				// #11957
				$this->tpl->setCurrentBlock("team_info");
				$this->tpl->setVariable("TXT_TEAM_INFO", $lng->txt("exc_no_team_yet"));
				
				/*
				$this->tpl->setVariable("TXT_CREATE_TEAM", $lng->txt("exc_create_team"));				
				$this->tpl->setVariable("URL_CREATE_TEAM", 						
					$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "createSingleMemberTeam"));
				*/
				
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
				"notice[$member_id]");
			$this->tpl->setVariable("VAL_NOTE",
				ilUtil::prepareFormOutput($member_status->getNotice()));

			
			// comment for learner	
			
			$lcomment_value = $member_status->getComment();
			
			$overlay_id = "excasscomm_".$this->ass_id."_".$member_id;
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
			
			$lcomment = new ilTextAreaInputGUI($lng->txt("exc_comment_for_learner"), "lcomment_".$this->ass_id."_".$member_id);
			$lcomment->setInfo($lng->txt("exc_comment_for_learner_info"));
			$lcomment->setValue($lcomment_value);
			$lcomment->setCols(45);
			$lcomment->setRows(10);			
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
				"mark[$member_id]");
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

			// feedback mail		
			$this->tpl->setVariable("LINK_FEEDBACK",
				$ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail"));
			$this->tpl->setVariable("TXT_FEEDBACK",
				$lng->txt("exc_send_mail"));

			// file feedback
			$cnt_files = $this->storage->countFeedbackFiles($submission->getFeedbackId());
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
				$given = $peer_review->countGivenFeedback(true, $member_id);
				$received = sizeof($peer_review->getPeerReviewsByPeerId($member_id, true));
								
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
				$rating->setObject($this->ass_id, "ass", $member_id, "peer");
				$rating->setUserId(0);			
				$this->tpl->setVariable("VAL_RATING", $rating->getHTML(true, false));		
				*/
				
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->touchBlock("member_has_no_team_bl");
		}
		
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $this->ass_id); // #17140
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