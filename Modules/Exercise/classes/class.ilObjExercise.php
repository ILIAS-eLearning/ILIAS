<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
require_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";

/** @defgroup ModulesExercise Modules/Exercise
 */

/**
* Class ilObjExercise
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExercise extends ilObject
{
	var $file_obj;
	var $members_obj;
	var $files;

	var $timestamp;
	var $hour;
	var $minutes;
	var $day;
	var $month;
	var $year;
	var $instruction;
	var $certificate_visibility;
	
	/**
	 * 
	 * Indicates whether completion by submission is enabled or not
	 * 
	 * @var boolean
	 * @access protected
	 * 
	 */
	protected $completion_by_submission = false;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjExercise($a_id = 0,$a_call_by_reference = true)
	{
		$this->setPassMode("all");
		$this->type = "exc";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	// SET, GET METHODS
	function setDate($a_hour,$a_minutes,$a_day,$a_month,$a_year)
	{
		$this->hour = (int) $a_hour;
		$this->minutes = (int) $a_minutes;
		$this->day = (int) $a_day;
		$this->month = (int) $a_month;
		$this->year = (int) $a_year;
		$this->timestamp = mktime($this->hour,$this->minutes,0,$this->month,$this->day,$this->year);
		return true;
	}
	function getTimestamp()
	{
		return $this->timestamp;
	}
	function setTimestamp($a_timestamp)
	{
		$this->timestamp = $a_timestamp;
	}
	function setInstruction($a_instruction)
	{
		$this->instruction = $a_instruction;
	}
	function getInstruction()
	{
		return $this->instruction;
	}
	
	/**
	 * Set pass mode (all | nr)
	 *
	 * @param	string		pass mode
	 */
	function setPassMode($a_val)
	{
		$this->pass_mode = $a_val;
	}
	
	/**
	 * Get pass mode (all | nr) 
	 *
	 * @return	string		pass mode
	 */
	function getPassMode()
	{
		return $this->pass_mode;
	}
	
	/**
	 * Set number of assignments that must be passed to pass the exercise
	 *
	 * @param	integer		pass nr
	 */
	function setPassNr($a_val)
	{
		$this->pass_nr = $a_val;
	}
	
	/**
	 * Get number of assignments that must be passed to pass the exercise 
	 *
	 * @return	integer		pass nr
	 */
	function getPassNr()
	{
		return $this->pass_nr;
	}
	
	/**
	 * Set whether submissions of learners should be shown to other learners after deadline
	 *
	 * @param	boolean		show submissions
	 */
	function setShowSubmissions($a_val)
	{
		$this->show_submissions = $a_val;
	}
	
	/**
	 * Get whether submissions of learners should be shown to other learners after deadline 
	 *
	 * @return	integer		show submissions
	 */
	function getShowSubmissions()
	{
		return $this->show_submissions;
	}
	

/*	function getFiles()
	{
		return $this->files;
	}*/

	function checkDate()
	{
		return	$this->hour == (int) date("H",$this->timestamp) and
			$this->minutes == (int) date("i",$this->timestamp) and
			$this->day == (int) date("d",$this->timestamp) and
			$this->month == (int) date("m",$this->timestamp) and
			$this->year == (int) date("Y",$this->timestamp);

	}

	
	function saveData()
	{
		global $ilDB;
		
		// SAVE ONLY EXERCISE SPECIFIC DATA
		/*$query = "INSERT INTO exc_data SET ".
			"obj_id = ".$ilDB->quote($this->getId()).", ".
			"instruction = ".$ilDB->quote($this->getInstruction()).", ".
			"time_stamp = ".$ilDB->quote($this->getTimestamp());
		$this->ilias->db->query($query);*/

		$ilDB->insert("exc_data", array(
			"obj_id" => array("integer", $this->getId()),
			"instruction" => array("clob", $this->getInstruction()),
			"time_stamp" => array("integer", $this->getTimestamp()),
			"pass_mode" => array("text", $this->getPassMode()),
			"pass_nr" => array("text", $this->getPassNr()),
			"show_submissions" => array("integer", (int) $this->getShowSubmissions()),
			'compl_by_submission' => array('integer', (int)$this->isCompletionBySubmissionEnabled()),
			"certificate_visibility" => array("integer", (int)$this->getCertificateVisibility())
			));
		return true;
	}
	
	/**
	 * Clone exercise (no member data)
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB;
		
		// Copy settings
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	$new_obj->setInstruction($this->getInstruction());
	 	$new_obj->setTimestamp($this->getTimestamp());
	 	$new_obj->setPassMode($this->getPassMode());
	 	$new_obj->saveData();
	 	$new_obj->setPassNr($this->getPassNr());
	 	$new_obj->setShowSubmissions($this->getShowSubmissions());
	 	$new_obj->setCompletionBySubmission($this->isCompletionBySubmissionEnabled());	 
	 	$new_obj->update();
	 	
		// Copy criteria catalogues
		$crit_cat_map = array();
		include_once("./Modules/Exercise/classes/class.ilExcCriteriaCatalogue.php");
		foreach(ilExcCriteriaCatalogue::getInstancesByParentId($this->getId()) as $crit_cat)
		{
			$new_id = $crit_cat->cloneObject($new_obj->getId());
			$crit_cat_map[$crit_cat->getId()] = $new_id;			
		}
			
		// Copy assignments
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		ilExAssignment::cloneAssignmentsOfExercise($this->getId(), $new_obj->getId(), $crit_cat_map);	
		
		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
			
		return $new_obj;
	}
	
	/**
	* delete course and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB, $ilAppEventHandler;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		// put here course specific stuff
		$ilDB->manipulate("DELETE FROM exc_data ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(), "integer"));

		//$this->ilias->db->query($query);

		//$this->file_obj->delete();
		//$this->members_obj->delete();
		
		include_once "Modules/Exercise/classes/class.ilExcCriteriaCatalogue.php";
		ilExcCriteriaCatalogue::deleteByParent($this->getId());

		// remove all notifications
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::removeForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $this->getId());		
			
		$ilAppEventHandler->raise('Modules/Exercise',
			'delete',
			array('obj_id'=>$this->getId()));		

		return true;
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional paramters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_node_id,$a_params = 0)
	{
		// object specific event handling

		parent::notify($a_event,$a_ref_id,$a_node_id,$a_params);
	}

	function read()
	{
		global $ilDB;

		parent::read();

		$query = "SELECT * FROM exc_data ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$this->setInstruction($row->instruction);
			$this->setTimestamp($row->time_stamp);
			$pm = ($row->pass_mode == "")
				? "all"
				: $row->pass_mode;
			$this->setPassMode($pm);
			$this->setShowSubmissions($row->show_submissions);
			if ($row->pass_mode == "nr")
			{
				$this->setPassNr($row->pass_nr);
			}
			$this->setCompletionBySubmission($row->compl_by_submission == 1 ? true : false);
			$this->setCertificateVisibility($row->certificate_visibility);
		}
		
		$this->members_obj = new ilExerciseMembers($this);

		return true;
	}

	function update()
	{
		global $ilDB;

		parent::update();

		/*$query = "UPDATE exc_data SET ".
			"instruction = ".$ilDB->quote($this->getInstruction()).", ".
			"time_stamp = ".$ilDB->quote($this->getTimestamp())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		*/
		
		if ($this->getPassMode() == "all")
		{
			$pass_nr = null;
		}
		else
		{
			$pass_nr = $this->getPassNr();
		}

		$ilDB->update("exc_data", array(
			"instruction" => array("clob", $this->getInstruction()),
			"time_stamp" => array("integer", $this->getTimestamp()),
			"pass_mode" => array("text", $this->getPassMode()),
			"pass_nr" => array("integer", $this->getPassNr()),
			"show_submissions" => array("integer", (int) $this->getShowSubmissions()),
			'compl_by_submission' => array('integer', (int)$this->isCompletionBySubmissionEnabled())
			), array(
			"obj_id" => array("integer", $this->getId())
			));

		$this->updateAllUsersStatus();
		
		//$res = $this->ilias->db->query($query);

		#$this->members_obj->update();
		return true;
	}

	/**
	 * send exercise per mail to members
	 */
	function sendAssignment(ilExAssignment $a_ass, $a_members)
	{
		global $lng, $ilUser;
		
		$a_members = array_keys($a_members);
		
		$lng->loadLanguageModule("exc");
		
		// subject
		$subject = $a_ass->getTitle()
			? $this->getTitle().": ".$a_ass->getTitle()
			: $this->getTitle();
		
		
		// body
		
		$body = $a_ass->getInstruction();
		$body .= "\n\n";
		
		$body .= $lng->txt("exc_edit_until").": ";
		$body .= (!$a_ass->getDeadline())
		  ? $lng->txt("exc_no_deadline_specified")
		  : ilDatePresentation::formatDate(new ilDateTime($a_ass->getDeadline(), IL_CAL_UNIX));
		$body .= "\n\n";
		
		include_once "Services/Link/classes/class.ilLink.php";
		$body .= ilLink::_getLink($this->getRefId(), "exc");
		

		// files
		$file_names = array();
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());
		$files = $storage->getFiles();
		if(count($files))
		{
			include_once "./Services/Mail/classes/class.ilFileDataMail.php";
			$mfile_obj = new ilFileDataMail($_SESSION["AccountId"]);
			foreach($files as $file)
			{
				$mfile_obj->copyAttachmentFile($file["fullpath"], $file["name"]);
				$file_names[] = $file["name"];
			}
		}
		
		// recipients
		$recipients = array();
		foreach($a_members as $member_id)
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
			$recipients[] = $tmp_obj->getLogin();
			unset($tmp_obj);
		}
		$recipients = implode("," ,$recipients);
	
		// send mail
		include_once "Services/Mail/classes/class.ilMail.php";
		$tmp_mail_obj = new ilMail($ilUser->getId());
		$message = $tmp_mail_obj->sendMail(
			$recipients,
			"",
			"",
			$subject, 
			$body,
			$file_names,
			array("normal")
		);
		unset($tmp_mail_obj);

		// remove tmp files
		if(sizeof($file_names))
		{
			$mfile_obj->unlinkFiles($file_names);
			unset($mfile_obj);
		}

		// set recipients mail status
		foreach($a_members as $member_id)
		{
			$member_status = $a_ass->getMemberStatus($member_id);
			$member_status->setSent(true);
			$member_status->update();			
		}

		return true;
	}

	/**
	 * Determine status of user
	 */
	function determinStatusOfUser($a_user_id = 0)
	{
		global $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getInstancesByExercise($this->getId());
		
		$passed_all_mandatory = true;
		$failed_a_mandatory = false;
		$cnt_passed = 0;
		$cnt_notgraded = 0;
		$passed_at_least_one = false;
		
		foreach ($ass as $a)
		{
			$stat = $a->getMemberStatus($a_user_id)->getStatus();
			if ($a->getMandatory() && ($stat == "failed" || $stat == "notgraded"))
			{
				$passed_all_mandatory = false;
			}
			if ($a->getMandatory() && ($stat == "failed"))
			{
				$failed_a_mandatory = true;
			}
			if ($stat == "passed")
			{
				$cnt_passed++;
			}
			if ($stat == "notgraded")
			{
				$cnt_notgraded++;
			}
		}
		
		if (count($ass) == 0)
		{
			$passed_all_mandatory = false;
		}
		
		if ($this->getPassMode() != "nr")
		{
//echo "5";
			$overall_stat = "notgraded";
			if ($failed_a_mandatory)
			{
//echo "6";
				$overall_stat = "failed";
			}
			else if ($passed_all_mandatory && $cnt_passed > 0)
			{
//echo "7";
				$overall_stat = "passed";
			}
		}
		else
		{
//echo "8";
			$min_nr = $this->getPassNr();
			$overall_stat = "notgraded";
//echo "*".$cnt_passed."*".$cnt_notgraded."*".$min_nr."*";
			if ($failed_a_mandatory || ($cnt_passed + $cnt_notgraded < $min_nr))
			{
//echo "9";
				$overall_stat = "failed";
			}
			else if ($passed_all_mandatory && $cnt_passed >= $min_nr)
			{
//echo "A";
				$overall_stat = "passed";
			}
		}
		
		$ret =  array(
			"overall_status" => $overall_stat,
			"failed_a_mandatory" => $failed_a_mandatory);
//echo "<br>p:".$cnt_passed.":ng:".$cnt_notgraded;
//var_dump($ret);
		return $ret;
	}
	
	/**
	 * Update exercise status of user
	 */
	function updateUserStatus($a_user_id = 0)
	{
		global $ilUser;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$st = $this->determinStatusOfUser($a_user_id);

		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		ilExerciseMembers::_writeStatus($this->getId(), $a_user_id, 
			$st["overall_status"]);
	}
	
	/**
	 * Update status of all users
	 */
	function updateAllUsersStatus()
	{
		if (!is_object($this->members_obj));
		{
			$this->members_obj = new ilExerciseMembers($this);
		}
		
		$mems = $this->members_obj->getMembers();
		foreach ($mems as $mem)
		{
			$this->updateUserStatus($mem);
		}
	}
	
	/**
	 * Exports grades as excel
	 */
	function exportGradesExcel()
	{
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass_data = ilExAssignment::getInstancesByExercise($this->getId());
		
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$excelfile = ilUtil::ilTempnam();
		$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
		$workbook = $adapter->getWorkbook();
		$workbook->setVersion(8); // Use Excel97/2000 Format
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		
		//
		// status
		//
		$mainworksheet = $workbook->addWorksheet();
		
		// header row
		$mainworksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("name")));
		$cnt = 1;
		foreach ($ass_data as $ass)
		{
			$mainworksheet->writeString(0, $cnt, $cnt);
			$cnt++;
		}
		$mainworksheet->writeString(0, $cnt++, ilExcelUtils::_convert_text($this->lng->txt("exc_total_exc")));
		$mainworksheet->writeString(0, $cnt++, ilExcelUtils::_convert_text($this->lng->txt("exc_mark")));
		$mainworksheet->writeString(0, $cnt, ilExcelUtils::_convert_text($this->lng->txt("exc_comment_for_learner")));
		
		// data rows
		$this->mem_obj = new ilExerciseMembers($this);
		$getmems = $this->mem_obj->getMembers();
		$mems = array();
		foreach ($getmems as $user_id)
		{
			$mems[$user_id] = ilObjUser::_lookupName($user_id);
		}
		$mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);

		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		
		$data = array();
		$row_cnt = 1;
		foreach ($mems as $user_id => $d)
		{
			$col_cnt = 1;

			// name
			$mainworksheet->writeString($row_cnt, 0,
				ilExcelUtils::_convert_text($d["lastname"].", ".$d["firstname"]." [".$d["login"]."]"));

			reset($ass_data);

			foreach ($ass_data as $ass)
			{
				$status = $ass->getMemberStatus($user_id)->getStatus();
				$mainworksheet->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($this->lng->txt("exc_".$status)));
				$col_cnt++;
			}
			
			// total status
			$status = ilExerciseMembers::_lookupStatus($this->getId(), $user_id);
			$mainworksheet->writeString($row_cnt, $col_cnt++, ilExcelUtils::_convert_text($this->lng->txt("exc_".$status)));

			// #18096
			$marks_obj = new ilLPMarks($this->getId(), $user_id);
			$mainworksheet->writeString($row_cnt, $col_cnt++, ilExcelUtils::_convert_text($marks_obj->getMark()));
			$mainworksheet->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($marks_obj->getComment()));
			
			$row_cnt++;
		}
		
		//
		// mark
		//
		$worksheet2 = $workbook->addWorksheet();
		
		// header row
		$worksheet2->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("name")));
		$cnt = 1;
		foreach ($ass_data as $ass)
		{
			$worksheet2->writeString(0, $cnt, $cnt);
			$cnt++;
		}
		$worksheet2->writeString(0, $cnt, ilExcelUtils::_convert_text($this->lng->txt("exc_total_exc")));
		
		// data rows
		$data = array();
		$row_cnt = 1;
		reset($mems);
		foreach ($mems as $user_id => $d)
		{
			$col_cnt = 1;
			$d = ilObjUser::_lookupName($user_id);

			// name
			$worksheet2->writeString($row_cnt, 0,
				ilExcelUtils::_convert_text($d["lastname"].", ".$d["firstname"]." [".$d["login"]."]"));

			reset($ass_data);

			foreach ($ass_data as $ass)
			{
				$mark = $ass->getMemberStatus($user_id)->getMark();
				$worksheet2->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($mark));
				$col_cnt++;
			}
			
			// total mark
			include_once 'Services/Tracking/classes/class.ilLPMarks.php';
			$worksheet2->writeString($row_cnt, $col_cnt,
				ilExcelUtils::_convert_text(ilLPMarks::_lookupMark($user_id, $this->getId())));

			$row_cnt++;
		}

		
		$workbook->close();
		$exc_name = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->getTitle()));
		ilUtil::deliverFile($excelfile, $exc_name.".xls", "application/vnd.ms-excel");
	}
	
	/**
	 * Send feedback file notification to user
	 */
	function sendFeedbackFileNotification($a_feedback_file, $a_user_id, $a_ass_id, $a_is_text_feedback = false)
	{
		$user_ids = $a_user_id;
		if(!is_array($user_ids))
		{
			$user_ids = array($user_ids);
		}
		
		include_once("./Modules/Exercise/classes/class.ilExerciseMailNotification.php");
		
		$type = (bool)$a_is_text_feedback
			? ilExerciseMailNotification::TYPE_FEEDBACK_TEXT_ADDED
			: ilExerciseMailNotification::TYPE_FEEDBACK_FILE_ADDED;
				
		$not = new ilExerciseMailNotification();
		$not->setType($type);
		$not->setAssignmentId($a_ass_id);
		$not->setObjId($this->getId());
		if ($this->getRefId() > 0)
		{
			$not->setRefId($this->getRefId());
		}
		$not->setRecipients($user_ids);
		$not->send();
	}
	
	/**
	 * 
	 * Checks whether completion by submission is enabled or not
	 * 
	 * @return	boolean
	 * @access	public
	 * 
	 */
	public function isCompletionBySubmissionEnabled()
	{
		return $this->completion_by_submission;
	}
	
	/**
	 * 
	 * Enabled/Disable completion by submission
	 * 
	 * @param	boolean
	 * @return	ilObjExercise
	 * @access	public
	 * 
	 */
	public function setCompletionBySubmission($bool)
	{
		$this->completion_by_submission = (bool)$bool;
		
		return $this;
	}
	
	public function processExerciseStatus(ilExAssignment $a_ass, array $a_user_ids, $a_has_submitted, array $a_valid_submissions = null)
	{
		$a_has_submitted = (bool)$a_has_submitted;			
		
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		foreach($a_user_ids as $user_id)
		{		
			$member_status = $a_ass->getMemberStatus($user_id);
			$member_status->setReturned($a_has_submitted);	
			$member_status->update();	
			
			ilExerciseMembers::_writeReturned($this->getId(), $user_id, $a_has_submitted);
		}		 
				
		// re-evaluate exercise status
		if($this->isCompletionBySubmissionEnabled())
		{							
			foreach($a_user_ids as $user_id)
			{
				$status = 'notgraded';
				if($a_has_submitted)
				{
					if(!is_array($a_valid_submissions) ||
						$a_valid_submissions[$user_id])
					{
						$status = 'passed';				
					}					
				}
									
				$member_status = $a_ass->getMemberStatus($user_id);
				$member_status->setStatus($status);		
				$member_status->update();				
			}
		}			
	}	
	
	/**
	 * Get all exercises for user
	 *
	 * @param <type> $a_user_id
	 * @return array (exercise id => passed)
	 */
	public static function _lookupFinishedUserExercises($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT obj_id, status FROM exc_members".
			" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
			" AND (status = ".$ilDB->quote("passed", "text").
			" OR status = ".$ilDB->quote("failed", "text").")");

		$all = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$all[$row["obj_id"]] = ($row["status"] == "passed");
		}
		return $all;
	}
	

	/**
	* Returns the visibility settings of the certificate
	*
	* @return integer The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access public
	*/
	function getCertificateVisibility()
	{
		return (strlen($this->certificate_visibility)) ? $this->certificate_visibility : 0;
	}

	/**
	* Sets the visibility settings of the certificate
	*
	* @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access public
	*/
	function setCertificateVisibility($a_value)
	{
		$this->certificate_visibility = $a_value;
	}
	
	/**
	* Saves the visibility settings of the certificate
	*
	* @param integer $a_value The value for the visibility settings (0 = always, 1 = only passed,  2 = never)
	* @access private
	*/
	function saveCertificateVisibility($a_value)
	{
		global $ilDB;

		$affectedRows = $ilDB->manipulateF("UPDATE exc_data SET certificate_visibility = %s WHERE obj_id = %s",
			array('integer', 'integer'),
			array($a_value, $this->getId())
		);
	}
	
	/**
	 * Check if given user has certificate to show/download
	 * 
	 * @param int $a_user_id
	 * @return bool 
	 */
	function hasUserCertificate($a_user_id)
	{
		// show certificate?
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if(ilCertificate::isActive() && ilCertificate::isObjectActive($this->getId()))
		{
			$certificate_visible = $this->getCertificateVisibility();
			// if not never
			if($certificate_visible != 2)
			{
				// if passed only
				include_once 'Modules/Exercise/classes/class.ilExerciseMembers.php';
				$status = ilExerciseMembers::_lookupStatus($this->getId(), $a_user_id);
				if($certificate_visible == 1 && $status == "passed")
				{
					return true;
				}
				// always (excluding notgraded)
				else if($certificate_visible == 0 && $status != "notgraded")
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Add to desktop after hand-in
	 * 
	 * @return bool
	 */
	function hasAddToDesktop()	
	{
		$exc_set = new ilSetting("excs");
		return (bool)$exc_set->get("add_to_pd", true);
	}
}

?>