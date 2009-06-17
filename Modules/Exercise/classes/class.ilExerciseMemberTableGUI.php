<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

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
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->exc = $a_exc;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($this->exc->getMemberListData());
		$this->setTitle($lng->txt("members"));
		//$this->setLimit(9999);
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("image"), "", "1");
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("login"), "login");
		$this->sent_col = $this->exc->_lookupAnyExerciseSent($this->exc->getId());
		if ($this->sent_col)
		{
			$this->addColumn($this->lng->txt("exc_exercise_sent"), "sent_time");
		}
		$this->addColumn($this->lng->txt("exc_submission"), "submission");
		$this->addColumn($this->lng->txt("exc_grading"), "solved_time");
		$this->addColumn($this->lng->txt("mail"), "feedback_time");
		
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
		$this->addMultiCommand("sendMembers", $lng->txt("exc_send_exercise"));
		$this->addMultiCommand("deassignMembers", $lng->txt("exc_deassign_members"));
		
		if(count($this->exc->members_obj->getAllDeliveredFiles()))
		{
			$this->addCommandButton("downloadAll", $lng->txt("download_all_returned_files"));
		}

		
//		$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($member)
	{
		global $lng, $ilCtrl;

		include_once "./classes/class.ilObjectFactory.php";		
		$member_id = $member["usr_id"];
		if(!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
		{
			continue;
		}

		// checkbox
		$this->tpl->setVariable("VAL_CHKBOX",
			ilUtil::formCheckbox(0,"member[$member_id]",1));
		$this->tpl->setVariable("VAL_ID",
			$member_id);
			
		// name and login
		$this->tpl->setVariable("TXT_NAME",
			$member["name"]);
		$this->tpl->setVariable("TXT_LOGIN",
			"[".$member["login"]."]");
			
		// image
		$this->tpl->setVariable("USR_IMAGE",
			$mem_obj->getPersonalPicturePath("xxsmall"));
		$this->tpl->setVariable("USR_ALT", $lng->txt("personal_picture"));

		// mail sent
		if ($this->exc->members_obj->getStatusSentByMember($member_id))
		{
			if (($st = ilObjExercise::_lookupSentTime($this->exc->getId(),
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
		}

		// submission:
		// see if files have been resubmmited after solved
		$last_sub =
			$this->exc->getLastSubmission($member_id);
			
		if ($last_sub)
		{
			$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
		}
		else
		{
			$last_sub = "---";
		}
		if ($this->exc->_lookupUpdatedSubmission($this->exc->getId(), $member_id) == 1) 
		{
			$last_sub = "<b>".$last_sub."</b>";
		}
		$this->tpl->setVariable("VAL_LAST_SUBMISSION", $last_sub);
		$this->tpl->setVariable("TXT_LAST_SUBMISSION",
			$lng->txt("exc_last_submission"));

		// nr of submitted files
		$this->tpl->setVariable("TXT_SUBMITTED_FILES",
			$lng->txt("exc_files_returned"));
		$sub_cnt = count($this->exc->getDeliveredFiles($member_id));
		$new = $this->exc->_lookupNewFiles($this->exc->getId(), $member_id);
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
		
		// note
		$this->tpl->setVariable("TXT_NOTE", $lng->txt("note"));
		$this->tpl->setVariable("NAME_NOTE",
			"notice[$member_id]");
		$this->tpl->setVariable("VAL_NOTE",
			ilUtil::prepareFormOutput($this->exc->members_obj->getNoticeByMember($member_id)));
			
		// comment for learner
		$this->tpl->setVariable("TXT_LCOMMENT", $lng->txt("exc_comment_for_learner"));
		$this->tpl->setVariable("NAME_LCOMMENT",
			"lcomment[$member_id]");
		$lpcomment = ilLPMarks::_lookupComment($member_id,$this->exc->getId());
		$this->tpl->setVariable("VAL_LCOMMENT",
			ilUtil::prepareFormOutput($lpcomment));

		// solved
		//$this->tpl->setVariable("CHKBOX_SOLVED",
		//	ilUtil::formCheckbox($this->exc->members_obj->getStatusByMember($member_id),"solved[$member_id]",1));
		$status = ilExerciseMembers::_lookupStatus($this->exc->getId(), $member_id);
		$this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
		$this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("exc_notgraded"));
		$this->tpl->setVariable("TXT_PASSED", $lng->txt("exc_passed"));
		$this->tpl->setVariable("TXT_FAILED", $lng->txt("exc_failed"));
		if (($sd = ilObjExercise::_lookupStatusTime($this->exc->getId(), $member_id)) > 0)
		{
			$this->tpl->setCurrentBlock("status_date");
			$this->tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
			$this->tpl->setVariable('VAL_STATUS_DATE',
				ilDatePresentation::formatDate(new ilDateTime($sd,IL_CAL_DATETIME)));
			$this->tpl->parseCurrentBlock();
		}
		switch($status)
		{
			case "passed": 	$pic = "scorm/passed.gif"; break;
			case "failed":	$pic = "scorm/failed.gif"; break;
			default: 		$pic = "scorm/not_attempted.gif"; break;
		}
		$this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
		$this->tpl->setVariable("ALT_STATUS", $lng->txt("exc_".$status));
		
		// mark
		$this->tpl->setVariable("TXT_MARK", $lng->txt("exc_mark"));
		$this->tpl->setVariable("NAME_MARK",
			"mark[$member_id]");
		$mark = ilLPMarks::_lookupMark($member_id,$this->exc->getId());
		$this->tpl->setVariable("VAL_MARK",
			ilUtil::prepareFormOutput($mark));
			
		// feedback
		$ilCtrl->setParameter($this->parent_obj, "member_id", $member_id);
		$this->tpl->setVariable("CHKBOX_FEEDBACK",
			ilUtil::formCheckbox($this->exc->members_obj->getStatusFeedbackByMember($member_id),"feedback[$member_id]",1));
		if (($ft = ilObjExercise::_lookupFeedbackTime($this->exc->getId(), $member_id)) > 0)
		{
			$this->tpl->setCurrentBlock("feedback_date");
			$this->tpl->setVariable("TXT_FEEDBACK_MAIL_SENT",
				sprintf($lng->txt("exc_sent_at"),
				ilDatePresentation::formatDate(new ilDateTime($ft,IL_CAL_DATETIME))));
			$this->tpl->parseCurrentBlock();
		}
		$ilCtrl->setParameter($this, "rcp_to", $mem_obj->getLogin());
		$this->tpl->setVariable("LINK_FEEDBACK",
			$ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail"));
			//"ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$mem_obj->getLogin());
		$this->tpl->setVariable("TXT_FEEDBACK",
			$lng->txt("exc_send_mail"));
		$ilCtrl->setParameter($this->parent_obj, "rcp_to", "");

		$this->tpl->parseCurrentBlock();
	}

}
?>