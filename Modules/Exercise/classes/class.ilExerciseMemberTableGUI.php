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
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc, $a_ass_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
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
			$team_map = ilExAssignment::getAssignmentTeamMap($this->ass_id);
			$tmp = array();
			
			foreach($data as $item)
			{
				$team_id = $team_map[$item["usr_id"]];
				
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
		
		$this->setData($data);
		
		$this->addColumn("", "", "1", true);
				
		if($this->type != ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$this->addColumn($this->lng->txt("image"), "", "1");
			$this->addColumn($this->lng->txt("name"), "name");
			$this->addColumn($this->lng->txt("login"), "login");
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

		$this->addMultiCommand("saveStatus", $lng->txt("exc_save_changes"));
		$this->addMultiCommand("redirectFeedbackMail", $lng->txt("exc_send_mail"));
		$this->addMultiCommand("sendMembers", $lng->txt("exc_send_assignment"));
		$this->addMultiCommand("confirmDeassignMembers", $lng->txt("exc_deassign_members"));
		
		//if(count($this->exc->members_obj->getAllDeliveredFiles()))
		if (count(ilExAssignment::getAllDeliveredFiles($this->exc_id, $this->ass_id)))
		{
			$this->addCommandButton("downloadAll", $lng->txt("download_all_returned_files"));
		}		
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
						ilDatePresentation::formatDate(new ilDateTime($st,IL_CAL_DATE))));
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
			
		// name and login
		if(!isset($member["team"]))
		{
			$this->tpl->setVariable("TXT_NAME",
				$member["name"]);
			$this->tpl->setVariable("TXT_LOGIN",
				"[".$member["login"]."]");
			
			// image
			$this->tpl->setVariable("USR_IMAGE",
				$mem_obj->getPersonalPicturePath("xxsmall"));
			$this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));
		}
		// team upload
		else
		{
			$this->tpl->setCurrentBlock("team_member");
			asort($member["team"]);
			foreach($member["team"] as $member_name)  // #10749
			{
				$this->tpl->setVariable("TXT_MEMBER_NAME", $member_name);
				$this->tpl->parseCurrentBlock();
			}
			
			$ilCtrl->setParameter($this->parent_obj, "lmem", $member_id);
			$this->tpl->setVariable("HREF_LOG", 
				$ilCtrl->getLinkTarget($this->parent_obj, "showTeamLog"));
			$this->tpl->setVariable("TXT_LOG", $lng->txt("exc_team_log"));
			$ilCtrl->setParameter($this->parent_obj, "lmem", "");
		}

		// submission:
		// see if files have been resubmmited after solved
		$last_sub =
			ilExAssignment::getLastSubmission($this->ass_id, $member_id);
		if ($last_sub)
		{
			$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
		}
		else
		{
			$last_sub = "---";
		}
		if (ilExAssignment::lookupUpdatedSubmission($this->ass_id, $member_id) == 1) 
		{
			$last_sub = "<b>".$last_sub."</b>";
		}
		$this->tpl->setVariable("VAL_LAST_SUBMISSION", $last_sub);
		$this->tpl->setVariable("TXT_LAST_SUBMISSION",
			$lng->txt("exc_last_submission"));
		
		switch($this->type)
		{			
			case ilExAssignment::TYPE_UPLOAD_TEAM:
				// data is merged by team - see above
				// fallthrough
				
			case ilExAssignment::TYPE_UPLOAD:
				// nr of submitted files
				$this->tpl->setVariable("TXT_SUBMITTED_FILES",
					$lng->txt("exc_files_returned"));
				//$sub_cnt = count($this->exc->getDeliveredFiles($member_id, $this->ass_id));
				$sub_cnt = count(ilExAssignment::getDeliveredFiles($this->exc_id, $this->ass_id, $member_id));
				$new = ilExAssignment::lookupNewFiles($this->ass_id, $member_id);
				if (count($new) > 0)
				{
					$sub_cnt.= " ".sprintf($lng->txt("cnt_new"),count($new));
				}
				$this->tpl->setVariable("VAL_SUBMITTED_FILES",
					$sub_cnt);

				// download command
				$ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
				if ($sub_cnt > 0)
				{
					$this->tpl->setCurrentBlock("download_link");
					$this->tpl->setVariable("LINK_DOWNLOAD",
						$ilCtrl->getLinkTarget($this->parent_obj, "downloadReturned"));
					if (count($new) <= 0)
					{
						$this->tpl->setVariable("TXT_DOWNLOAD",
							$lng->txt("exc_download_files"));
					}
					else
					{
						$this->tpl->setVariable("TXT_DOWNLOAD",
							$lng->txt("exc_download_all"));
					}
					$this->tpl->parseCurrentBlock();

					// download new files only
					if (count($new) > 0)
					{
						$this->tpl->setCurrentBlock("download_link");
						$this->tpl->setVariable("LINK_NEW_DOWNLOAD",
							$ilCtrl->getLinkTarget($this->parent_obj, "downloadNewReturned"));
						$this->tpl->setVariable("TXT_NEW_DOWNLOAD",
							$lng->txt("exc_download_new"));
						$this->tpl->parseCurrentBlock();
					}
				}
				break;
				
			case ilExAssignment::TYPE_BLOG:
				$has_submitted = false;
				$this->tpl->setVariable("TXT_SUBMITTED_FILES",
					$lng->txt("exc_blog_returned"));
				$blogs = ilExAssignment::getDeliveredFiles($this->exc_id, $this->ass_id, $member_id);
				if($blogs)
				{
					$blogs = array_pop($blogs);					
					if($blogs && substr($blogs["filename"], -1) != "/")
					{
						$has_submitted = true;
						$this->tpl->setVariable("VAL_SUBMITTED_FILES", 1);
						
						$ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
						$this->tpl->setCurrentBlock("download_link");
						$this->tpl->setVariable("LINK_DOWNLOAD",
							$ilCtrl->getLinkTarget($this->parent_obj, "downloadReturned"));												
						$this->tpl->setVariable("TXT_DOWNLOAD",
							$lng->txt("exc_download_files"));						
						$this->tpl->parseCurrentBlock();
					}
				}
				if(!$has_submitted)
				{
					$this->tpl->setVariable("VAL_SUBMITTED_FILES", "---");
				}
				break;
				
			case ilExAssignment::TYPE_PORTFOLIO:
				$has_submitted = false;
				$this->tpl->setVariable("TXT_SUBMITTED_FILES",
					$lng->txt("exc_portfolio_returned"));
				$portfolios = ilExAssignment::getDeliveredFiles($this->exc_id, $this->ass_id, $member_id);
				if($portfolios)
				{
					$portfolios = array_pop($portfolios);									
					if($portfolios && substr($portfolios["filename"], -1) != "/")
					{	
						$has_submitted = true;
						$this->tpl->setVariable("VAL_SUBMITTED_FILES", 1);
						
						$ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
						$this->tpl->setCurrentBlock("download_link");
						$this->tpl->setVariable("LINK_DOWNLOAD",
							$ilCtrl->getLinkTarget($this->parent_obj, "downloadReturned"));												
						$this->tpl->setVariable("TXT_DOWNLOAD",
							$lng->txt("exc_download_files"));						
						$this->tpl->parseCurrentBlock();
					}
				}
				if(!$has_submitted)
				{
					$this->tpl->setVariable("VAL_SUBMITTED_FILES", "---");
				}
				break;
		}

	
		
		// note
		$this->tpl->setVariable("TXT_NOTE", $lng->txt("note"));
		$this->tpl->setVariable("NAME_NOTE",
			"notice[$member_id]");
		$this->tpl->setVariable("VAL_NOTE",
			ilUtil::prepareFormOutput(ilExAssignment::lookupNoticeOfUser($this->ass_id, $member_id)));
			
		// comment for learner
		$this->tpl->setVariable("TXT_LCOMMENT", $lng->txt("exc_comment_for_learner"));
		$this->tpl->setVariable("NAME_LCOMMENT",
			"lcomment[$member_id]");
		$lpcomment = ilExAssignment::lookupCommentForUser($this->ass_id, $member_id);
		$this->tpl->setVariable("VAL_LCOMMENT",
			ilUtil::prepareFormOutput($lpcomment));

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
			case "passed": 	$pic = "scorm/passed.png"; break;
			case "failed":	$pic = "scorm/failed.png"; break;
			default: 		$pic = "scorm/not_attempted.png"; break;
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

		$this->tpl->parseCurrentBlock();
	}

}
?>