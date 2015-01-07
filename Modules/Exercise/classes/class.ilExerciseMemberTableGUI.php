<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

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
	protected $exc_id;
	protected $ass_id;
	protected $type;
	protected $sent_col;
	protected $peer_review;
	protected $selected = array();
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_ass_id)
	{
		global $ilCtrl, $lng;
		
		$this->exc = $a_exc;
		$this->exc_id = $this->exc->getId();
		$this->setId("exc_mem_".$a_ass_id);
		
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$this->storage = new ilFSStorageExercise($this->exc_id, $a_ass_id);
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
		$this->ass_id = $a_ass_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setTitle($lng->txt("exc_assignment").": ".
			ilExAssignment::lookupTitle($a_ass_id));
		$this->setTopCommands(true);
		//$this->setLimit(9999);
		
		$this->type = ilExAssignment::lookupType($this->ass_id);
		
		$data = ilExAssignment::getMemberListData($this->exc_id, $this->ass_id);
		
		// team upload?  (1 row == 1 team)
		if($this->type == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$ass_obj = new ilExAssignment($this->ass_id);
			
			$team_map = ilExAssignment::getAssignmentTeamMap($this->ass_id);
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
			$this->peer_review = $ass_obj->getPeerReview();
			if($this->peer_review)
			{
				include_once './Services/Rating/classes/class.ilRatingGUI.php';
			}														
		}
		
		$this->setData($data);
		
		$this->addColumn("", "", "1", true);
				
		if($this->type != ilExAssignment::TYPE_UPLOAD_TEAM)
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
		
		$this->sent_col = ilExAssignment::lookupAnyExerciseSent($this->exc->getId(), $this->ass_id);
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

		$this->addMultiCommand("saveStatus", $lng->txt("exc_save_selected"));
		$this->addMultiCommand("redirectFeedbackMail", $lng->txt("exc_send_mail"));
		$this->addMultiCommand("sendMembers", $lng->txt("exc_send_assignment"));
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
		global $lng, $ilCtrl;

		include_once "./Services/Object/classes/class.ilObjectFactory.php";		
		$member_id = $member["usr_id"];

		if(!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
		{
			return;
		}
		
		$has_no_team_yet = (substr($member["team_id"], 0, 3) == "nty");

		if(!$has_no_team_yet)
		{
			// mail sent
			if ($this->sent_col)
			{
				if (ilExAssignment::lookupStatusSentOfUser($this->ass_id, $member_id))
				{
					$this->tpl->setCurrentBlock("mail_sent");
					if (($st = ilExAssignment::lookupSentTimeOfUser($this->ass_id,
						$member_id)) > 0)
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

			// checkbox
			$this->tpl->setVariable("VAL_CHKBOX",
				ilUtil::formCheckbox(0,"member[$member_id]",1));
			$this->tpl->setVariable("VAL_ID",
				$member_id);				
		}
		
		$file_info = ilExAssignment::getDownloadedFilesInfoForTableGUIS($this->parent_obj, $this->exc_id, $this->type, $this->ass_id, $member_id, $this->parent_cmd);
			
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
		}
		// team upload
		else
		{						
			$this->tpl->setCurrentBlock("team_member");
			asort($member["team"]);
			foreach($member["team"] as $member_name) // #10749
			{
				$this->tpl->setVariable("TXT_MEMBER_NAME", $member_name);
				$this->tpl->parseCurrentBlock();
			}
						
			if(!$has_no_team_yet)
			{
				$this->tpl->setCurrentBlock("team_log");
				$ilCtrl->setParameter($this->parent_obj, "lmem", $member_id);
				$this->tpl->setVariable("HREF_LOG", 
					$ilCtrl->getLinkTarget($this->parent_obj, "showTeamLog"));
				$this->tpl->setVariable("TXT_LOG", $lng->txt("exc_team_log"));
				$ilCtrl->setParameter($this->parent_obj, "lmem", "");
				$this->tpl->parseCurrentBlock();
			}
			else
			{				
				// #11957
				$this->tpl->setCurrentBlock("team_info");
				$this->tpl->setVariable("TXT_TEAM_INFO", $lng->txt("exc_no_team_yet"));
				$this->tpl->setVariable("TXT_CREATE_TEAM", $lng->txt("exc_create_team"));
				
				$ilCtrl->setParameter($this->parent_obj, "lmem", $member_id);
				$this->tpl->setVariable("URL_CREATE_TEAM", 						
					$ilCtrl->getLinkTarget($this->getParentObject(), "createSingleMemberTeam"));
				$ilCtrl->setParameter($this->parent_obj, "lmem", "");
				
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
				ilUtil::prepareFormOutput(ilExAssignment::lookupNoticeOfUser($this->ass_id, $member_id)));

			
			// comment for learner	
			
			$lcomment_value = ilExAssignment::lookupCommentForUser($this->ass_id, $member_id);
			
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
			
			
			// solved
			//$this->tpl->setVariable("CHKBOX_SOLVED",
			//	ilUtil::formCheckbox($this->exc->members_obj->getStatusByMember($member_id),"solved[$member_id]",1));
			$status = ilExAssignment::lookupStatusOfUser($this->ass_id, $member_id);
			$this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
			$this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("exc_notgraded"));
			$this->tpl->setVariable("TXT_PASSED", $lng->txt("exc_passed"));
			$this->tpl->setVariable("TXT_FAILED", $lng->txt("exc_failed"));
			if (($sd = ilExAssignment::lookupStatusTimeOfUser($this->ass_id, $member_id)) > 0)
			{
				$this->tpl->setCurrentBlock("status_date");
				$this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
				$this->tpl->setVariable('VAL_STATUS_DATE',
					ilDatePresentation::formatDate(new ilDateTime($sd,IL_CAL_DATETIME)));
				$this->tpl->parseCurrentBlock();
			}
			switch($status)
			{
				case "passed": 	$pic = "scorm/passed.svg"; break;
				case "failed":	$pic = "scorm/failed.svg"; break;
				default: 		$pic = "scorm/not_attempted.svg"; break;
			}
			$this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
			$this->tpl->setVariable("ALT_STATUS", $lng->txt("exc_".$status));

			// mark
			$this->tpl->setVariable("TXT_MARK", $lng->txt("exc_mark"));
			$this->tpl->setVariable("NAME_MARK",
				"mark[$member_id]");
			$mark = ilExAssignment::lookupMarkOfUser($this->ass_id, $member_id);
			$this->tpl->setVariable("VAL_MARK",
				ilUtil::prepareFormOutput($mark));

			// feedback
			$ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
			if (($ft = ilExAssignment::lookupFeedbackTimeOfUser($this->ass_id, $member_id)) > 0)
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

			if($this->type == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$feedback_id = "t".$member["team_id"];
			}
			else
			{
				$feedback_id = $member_id;
			}

			// file feedback
			$cnt_files = $this->storage->countFeedbackFiles($feedback_id);
			$ilCtrl->setParameter($this->parent_obj, "fsmode", "feedback");
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
			if(!isset($member["team"]) && $this->peer_review)
			{						
				$this->tpl->setCurrentBlock("peer_review_bl");
				$this->tpl->setVariable("TXT_PEER_REVIEW", $lng->txt("exc_peer_review_show"));

				$ilCtrl->setParameter($this->parent_obj, "grd", 1);
				$this->tpl->setVariable("LINK_PEER_REVIEW", 
					$ilCtrl->getLinkTarget($this->parent_obj, "showPersonalPeerReview"));
				$ilCtrl->setParameter($this->parent_obj, "grd", "");

				$rating = new ilRatingGUI();
				$rating->setObject($this->ass_id, "ass", $member_id, "peer");
				$rating->setUserId(0);			
				$this->tpl->setVariable("VAL_RATING", $rating->getHTML(true, false));		

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->touchBlock("member_has_no_team_bl");
		}
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