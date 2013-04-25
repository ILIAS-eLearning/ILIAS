<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");

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
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->exc = $a_exc;
		$this->exc_id = $this->exc->getId();
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		
		$this->part_id = $a_part_id;
		
		$this->setId("exc_part_".$this->exc_id."_".$this->part_id);
		
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		if ($this->part_id > 0)
		{
			$name = ilObjUser::_lookupName($this->part_id);
			$this->user = new ilObjUser($this->part_id);
		}
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		//$this->setData(ilExAssignment::getMemberListData($this->exc_id, $this->ass_id));
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
		
		$this->tpl->setVariable("TXT_ASS_TITLE", $d["title"]);
		
		if($d["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
		{			
			$members = ilExAssignment::getTeamMembersByAssignmentId($d["id"], $this->part_id);
			
			$this->tpl->setCurrentBlock("ass_members");
			foreach($members as $member_id)
			{					
				$this->tpl->setVariable("TXT_MEMBER_NAME", 
					ilObjUser::_lookupFullname($member_id));
				$this->tpl->parseCurrentBlock();					
			}			
			
			$ilCtrl->setParameter($this->parent_obj, "lpart", $this->part_id);
			$this->tpl->setVariable("HREF_LOG", 
				$ilCtrl->getLinkTarget($this->parent_obj, "showTeamLog"));
			$this->tpl->setVariable("TXT_LOG", $lng->txt("exc_team_log"));
			$ilCtrl->setParameter($this->parent_obj, "lpart", "");
		}
		
		$this->tpl->setVariable("VAL_CHKBOX",
			ilUtil::formCheckbox(0, "assid[".$d["id"]."]",1));
		$this->tpl->setVariable("VAL_ID",
			$d["id"]);
		
		// submission:
		// see if files have been resubmmited after solved
		$last_sub =
			ilExAssignment::getLastSubmission($d["id"], $this->part_id);
		if ($last_sub)
		{
			$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
		}
		else
		{
			$last_sub = "---";
		}
		if (ilExAssignment::lookupUpdatedSubmission($d["id"], $this->part_id) == 1) 
		{
			$last_sub = "<b>".$last_sub."</b>";
		}
		$this->tpl->setVariable("VAL_LAST_SUBMISSION", $last_sub);
		$this->tpl->setVariable("TXT_LAST_SUBMISSION",
			$lng->txt("exc_last_submission"));

		// nr of submitted files
		$this->tpl->setVariable("TXT_SUBMITTED_FILES",
			$lng->txt("exc_files_returned"));
		$sub_cnt = count(ilExAssignment::getDeliveredFiles($this->exc_id, $d["id"], $this->part_id));
		$new = ilExAssignment::lookupNewFiles($d["id"], $this->part_id);
		if (count($new) > 0)
		{
			$sub_cnt.= " ".sprintf($lng->txt("cnt_new"),count($new));
		}
		$this->tpl->setVariable("VAL_SUBMITTED_FILES",
			$sub_cnt);
		
		// download command
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $d["id"]);
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->part_id);
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
			"notice[".$d["id"]."]");
		$this->tpl->setVariable("VAL_NOTE",
			ilUtil::prepareFormOutput(ilExAssignment::lookupNoticeOfUser($d["id"], $this->part_id)));
			
		// comment for learner
		$this->tpl->setVariable("TXT_LCOMMENT", $lng->txt("exc_comment_for_learner"));
		$this->tpl->setVariable("NAME_LCOMMENT",
			"lcomment[".$d["id"]."]");
		$lpcomment = ilExAssignment::lookupCommentForUser($d["id"], $this->part_id);
		$this->tpl->setVariable("VAL_LCOMMENT",
			ilUtil::prepareFormOutput($lpcomment));

		// solved
		//$this->tpl->setVariable("CHKBOX_SOLVED",
		//	ilUtil::formCheckbox($this->exc->members_obj->getStatusByMember($member_id),"solved[$member_id]",1));
		$status = ilExAssignment::lookupStatusOfUser($d["id"], $this->part_id);
		$this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
		$this->tpl->setVariable("TXT_NOTGRADED", $lng->txt("exc_notgraded"));
		$this->tpl->setVariable("TXT_PASSED", $lng->txt("exc_passed"));
		$this->tpl->setVariable("TXT_FAILED", $lng->txt("exc_failed"));
		if (($sd = ilExAssignment::lookupStatusTimeOfUser($d["id"], $this->part_id)) > 0)
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
			"mark[".$d["id"]."]");
		$mark = ilExAssignment::lookupMarkOfUser($d["id"], $this->part_id);
		$this->tpl->setVariable("VAL_MARK",
			ilUtil::prepareFormOutput($mark));

		// feedback
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->part_id);
		if (($ft = ilExAssignment::lookupFeedbackTimeOfUser($d["id"], $this->part_id)) > 0)
		{
			$this->tpl->setCurrentBlock("feedback_date");
			$this->tpl->setVariable("TXT_FEEDBACK_MAIL_SENT",
				sprintf($lng->txt("exc_sent_at"),
				ilDatePresentation::formatDate(new ilDateTime($ft,IL_CAL_DATETIME))));
			$this->tpl->parseCurrentBlock();
		}
		$ilCtrl->setParameter($this, "rcp_to", $this->user->getLogin());
		$this->tpl->setVariable("LINK_FEEDBACK",
			$ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail"));
			//"ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$mem_obj->getLogin());
		$this->tpl->setVariable("TXT_FEEDBACK",
			$lng->txt("exc_send_mail"));
		$ilCtrl->setParameter($this->parent_obj, "rcp_to", "");
		
		if($d["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
		{
			$feedback_id = "t".ilExAssignment::getTeamIdByAssignment($d["id"], $this->part_id);
		}
		else
		{
			$feedback_id = $this->part_id;
		}
		
		$storage = new ilFSStorageExercise($this->exc_id, $d["id"]);
		$cnt_files = $storage->countFeedbackFiles($feedback_id);
		$ilCtrl->setParameter($this->parent_obj, "fsmode", "feedbackpart");
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


		$ilCtrl->setParameter($this->parent_obj, "ass_id", $_GET["ass_id"]);
	}

}
?>