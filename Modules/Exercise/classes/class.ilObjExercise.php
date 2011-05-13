<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "classes/class.ilObject.php";
require_once "./Modules/Exercise/classes/class.ilFileDataExercise.php";
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

	/**
	 * Save submitted file of user
	 */
	function deliverFile($a_http_post_files, $a_ass_id, $user_id, $unzip = false)
	{
		global $ilDB;
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getId(), $a_ass_id);
		$deliver_result = $storage->deliverFile($a_http_post_files, $user_id, $unzip);
//var_dump($deliver_result);
		if ($deliver_result)
		{
			$next_id = $ilDB->nextId("exc_returned");
			$query = sprintf("INSERT INTO exc_returned ".
							 "(returned_id, obj_id, user_id, filename, filetitle, mimetype, ts, ass_id) ".
							 "VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
				$ilDB->quote($next_id, "integer"),
				$ilDB->quote($this->getId(), "integer"),
				$ilDB->quote($user_id, "integer"),
				$ilDB->quote($deliver_result["fullname"], "text"),
				$ilDB->quote($a_http_post_files["name"], "text"),
				$ilDB->quote($deliver_result["mimetype"], "text"),
				$ilDB->quote(ilUtil::now(), "timestamp"),
				$ilDB->quote($a_ass_id, "integer")
			);
			$ilDB->manipulate($query);
			if (!$this->members_obj->isAssigned($user_id))
			{
				$this->members_obj->assignMember($user_id);
			}
			ilExAssignment::updateStatusReturnedForUser($a_ass_id, $user_id, 1);
			ilExerciseMembers::_writeReturned($this->getId(), $user_id, 1);
		}
		return true;
	}

	/**
	 * Upload assigment files
	 */
	function addUploadedFile($a_http_post_files, $unzipUploadedFile = false)
	{
		global $lng;
		if ($unzipUploadedFile && preg_match("/zip/",	$a_http_post_files["type"]) == 1)
		{

			$this->processUploadedFile($a_http_post_files["tmp_name"], "storeUploadedFile", true);
			return true;
			
			
		}
		else 
		{
			$this->file_obj->storeUploadedFile($a_http_post_files, true);
			return true;
		}
	}
	function deleteFiles($a_files)
	{
		$this->file_obj->unlinkFiles($a_files);
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
			'compl_by_submission' => array('integer', (int)$this->isCompletionBySubmissionEnabled())
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
	 	
		// Copy assignments
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		ilExAssignment::cloneAssignmentsOfExercise($this->getId(), $new_obj->getId());
		//$tmp_file_obj =& new ilFileDataExercise($this->getId());
		//$tmp_file_obj->ilClone($new_obj->getId());
		//unset($tmp_file_obj);
		
		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
				
		return $new_obj;
	}
	
	/**
	* Deletes already delivered files
	* @param array $file_id_array An array containing database ids of the delivered files
	* @param numeric $user_id The database id of the user
	* @access	public
	*/
	function deleteDeliveredFiles($a_exc_id, $a_ass_id, $file_id_array, $user_id)
	{
		ilExAssignment::deleteDeliveredFiles($a_exc_id, $a_ass_id, $file_id_array, $user_id);

		// Finally update status 'returned' of member if no file exists
		if(!count(ilExAssignment::getDeliveredFiles($a_exc_id, $a_ass_id, $user_id)))
		{
			ilExAssignment::updateStatusReturnedForUser($a_ass_id, $user_id, 0);
		}
	}

	/**
	* Delivers the returned files of an user
	* @param numeric $user_id The database id of the user
	* @access	public
	*/
	function deliverReturnedFiles($user_id)
	{
		require_once "./Services/Utilities/classes/class.ilUtil.php";
	}

	/**
	* delete course and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;

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

		// remove all notifications
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::removeForObject(ilNotification::TYPE_EXERCISE_SUBMISSION, $this->getId());

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
		}
		
		$this->members_obj = new ilExerciseMembers($this);

		// GET FILE ASSIGNED TO EXERCISE
//		$this->file_obj = new ilFileDataExercise($this->getId());
//		$this->files = $this->file_obj->getFiles();

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
	function sendAssignment($a_exc_id, $a_ass_id, $a_members)
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		$files = $storage->getFiles();

		if(count($files))
		{
			include_once "./classes/class.ilFileDataMail.php";

			$mfile_obj = new ilFileDataMail($_SESSION["AccountId"]);
			foreach($files as $file)
			{
				$mfile_obj->copyAttachmentFile($file["fullpath"], $file["name"]);
				$file_names[] = $file["name"];
			}
		}
		
		include_once "Services/Mail/classes/class.ilMail.php";

		$tmp_mail_obj = new ilMail($_SESSION["AccountId"]);
		$message = $tmp_mail_obj->sendMail(
			$this->__formatRecipients($a_members),"","",
			$this->__formatSubject(), $this->__formatBody($a_ass_id),
			count($file_names) ? $file_names : array(),array("normal"));

		unset($tmp_mail_obj);

		if(count($file_names))
		{
			$mfile_obj->unlinkFiles($file_names);
			unset($mfile_obj);
		}


		// SET STATUS SENT FOR ALL RECIPIENTS
		foreach($a_members as $member_id => $value)
		{
			ilExAssignment::updateStatusSentForUser($a_ass_id, $member_id, 1);
		}

		return true;
	}

	/**
	* Get time when exercise has been set to solved.
	*/
	function _lookupStatusTime($exc_id, $member_id)
	{

  		global $ilDB, $lng;

  		$q = "SELECT * ".
		"FROM exc_members ".
		"WHERE obj_id= ".$ilDB->quote($exc_id, "integer").
		" AND usr_id= ".$ilDB->quote($member_id, "integer");

  		$set = $ilDB->query($q);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return ilUtil::getMySQLTimestamp($rec["status_time"]);
		}
	}

	// PRIVATE METHODS
	function __formatBody($a_ass_id)
	{
		global $lng;

		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = new ilExAssignment($a_ass_id);

		$body = $ass->getInstruction();
		$body .= "\n\n";
		$body .= $lng->txt("exc_edit_until") . ": ".
			ilFormat::formatDate(date("Y-m-d H:i:s",$ass->getDeadline()), "datetime", true);
		$body .= "\n\n";
		$body .= ILIAS_HTTP_PATH.
			"/goto.php?target=".
			$this->getType().
			"_".$this->getRefId()."&client_id=".CLIENT_ID;

		return $body;
	}

	function __formatSubject()
	{
		return $subject = $this->getTitle()." (".$this->getDescription().")";
	}

	function __formatRecipients($a_members)
	{
		foreach($a_members as $member_id => $value)
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($member_id);
			$tmp_members[] = $tmp_obj->getLogin();

			unset($tmp_obj);
		}

		return implode(',',$tmp_members ? $tmp_members : array());
	}

	function _checkCondition($a_exc_id,$a_operator,$a_value,$a_usr_id = 0)
	{
		global $ilUser;
		
		$a_usr_id = $a_usr_id ? $a_usr_id : $ilUser->getId();

		switch($a_operator)
		{
			case 'passed':
				if (ilExerciseMembers::_lookupStatus($a_exc_id, $a_usr_id) == "passed")
				{
					return true;
				}
				else
				{
					return false;
				}
				break;

			default:
				return true;
		}
		return true;
	}

	/**
	* processes errorhandling etc for uploaded archive
	* @param string $tmpFile path and filename to uploaded file
	* @param string $storageMethod deliverFile or storeUploadedFile 
	* @param boolean $persistentErrorMessage Defines whether sendInfo will be persistent or not
	*/
	function processUploadedFile ($fileTmp, $storageMethod, $persistentErrorMessage,
		$a_ass_id)
	{
		global $lng, $ilUser;

		// Create unzip-directory
		$newDir = ilUtil::ilTempnam();
		ilUtil::makeDir($newDir);

		include_once ("Services/Utilities/classes/class.ilFileUtils.php");
		
		try 
		{
			$processDone = ilFileUtils::processZipFile($newDir,$fileTmp, false);
			ilFileUtils::recursive_dirscan($newDir, $filearray);			

			foreach ($filearray["file"] as $key => $filename)
			{
				$a_http_post_files["name"] = ilFileUtils::utf8_encode($filename);
				$a_http_post_files["type"] = "other";
				$a_http_post_files["tmp_name"] = $filearray["path"][$key]."/".$filename;
				$a_http_post_files["error"] = 0;
				$a_http_post_files["size"] = filesize($filearray["path"][$key]."/".$filename);

				if ($storageMethod == "deliverFile")
				{
					$this->$storageMethod($a_http_post_files, $a_ass_id, $ilUser->id, true);
				}
				else if ($storageMethod == "storeUploadedFile")
				{
					$this->file_obj->$storageMethod($a_http_post_files, true, true);				
				}
			}
			ilExerciseMembers::_writeReturned($this->getId(), $ilUser->id, 1);
			ilUtil::sendSuccess($this->lng->txt("file_added"),$persistentErrorMessage);					

		} 
		catch (ilFileUtilsException $e) 
		{
			ilUtil::sendFailure($e->getMessage(), $persistentErrorMessage);
		}
		

		ilUtil::delDir($newDir);
		return $processDone;

	}
	
	/**
	* This function fixes filenames. Prior to ILIAS 3.10.0 filenames have been
	* stored with full path in exc_returned.filename, e.g.
	* /opt/ilias/my_client/exercise/547/157/20070813113926_README.doc
	*
	* Problems occur, if the server is moved from one location to another.
	* We do the following: The filename will be parsed and if it contains the string
	* "/exercise/" we truncate everything
	* before "/exercise/" and replace it with the current CLIENT_DATA_DIR.
	*/
	static function _fixFilename($a_filename)
	{
		$ex_pos = strrpos($a_filename, "/exercise/");
		if ($ex_pos > 0)
		{
			$a_filename = CLIENT_DATA_DIR.substr($a_filename, $ex_pos);
		}
		return $a_filename;
	}
	
	/**
	* Iterates an associative array and fixes all fields with the key "filename"
	* using the _fixFilename() method
	*/
	static function _fixFilenameArray($a_array)
	{
		if (is_array($a_array))
		{
			foreach ($a_array as $k => $v)
			{
				if ($v["filename"] != "")
				{
					$a_array[$k]["filename"] = ilObjExercise::_fixFilename($a_array[$k]["filename"]);
				}
			}
		}
		
		return $a_array;
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
		$ass = ilExAssignment::getAssignmentDataOfExercise($this->getId());
		
		$passed_all_mandatory = true;
		$failed_a_mandatory = false;
		$cnt_passed = 0;
		$cnt_notgraded = 0;
		$passed_at_least_one = false;
		
		foreach ($ass as $a)
		{
			$stat = ilExAssignment::lookupStatusOfUser($a["id"], $a_user_id);
			if ($a["mandatory"] && ($stat == "failed" || $stat == "notgraded"))
			{
				$passed_all_mandatory = false;
			}
			if ($a["mandatory"] && ($stat == "failed"))
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
		$ass_data = ilExAssignment::getAssignmentDataOfExercise($this->getId());
		
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
		$mainworksheet->writeString(0, $cnt, ilExcelUtils::_convert_text($this->lng->txt("exc_total_exc")));
		
		// data rows
		$this->mem_obj = new ilExerciseMembers($this);
		$getmems = $this->mem_obj->getMembers();
		$mems = array();
		foreach ($getmems as $user_id)
		{
			$mems[$user_id] = ilObjUser::_lookupName($user_id);
		}
		$mems = ilUtil::sortArray($mems, "lastname", "asc", false, true);

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
				$status = ilExAssignment::lookupStatusOfUser($ass["id"], $user_id);
				$mainworksheet->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($this->lng->txt("exc_".$status)));
				$col_cnt++;
			}
			
			// total status
			$status = ilExerciseMembers::_lookupStatus($this->getId(), $user_id);
			$mainworksheet->writeString($row_cnt, $col_cnt, ilExcelUtils::_convert_text($this->lng->txt("exc_".$status)));

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
				$worksheet2->writeString($row_cnt, $col_cnt,
					ilExcelUtils::_convert_text(ilExAssignment::lookupMarkOfUser($ass["id"], $user_id)));
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
	function sendFeedbackFileNotification($a_feedback_file, $a_user_id, $a_ass_id)
	{
		include_once("./Modules/Exercise/classes/class.ilExerciseMailNotification.php");
		$not = new ilExerciseMailNotification();
		$not->setType(ilExerciseMailNotification::TYPE_FEEDBACK_FILE_ADDED);
		$not->setAssignmentId($a_ass_id);
		$not->setRefId($this->getRefId());
		$not->setRecipients(array($a_user_id));
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
	
	/**
	 * 
	 * This method is called after an user submitted one or more files.
	 * It should handle the setting "Completion by Submission" and, if enabled, set the status of
	 * the current user to either 'passed' or 'notgraded'.
	 * 
	 * @param	integer
	 * @access	public
	 * 
	 */
	public function handleSubmission($ass_id)
	{
		global $ilUser, $ilDB;

		if($this->isCompletionBySubmissionEnabled())
		{
			include_once 'Modules/Exercise/classes/class.ilExAssignment.php';
	
			$res = $ilDB->queryF(
				'SELECT returned_id FROM exc_returned WHERE obj_id = %s AND user_id = %s AND ass_id = %s',
				array('integer', 'integer', 'integer'),
				array($this->getId(), $ilUser->getId(), (int)$ass_id)
			);
	
			if($num = $ilDB->numRows($res))
			{
				ilExAssignment::updateStatusOfUser($ass_id, $ilUser->getId(), 'passed');
			}
			else
			{
				ilExAssignment::updateStatusOfUser($ass_id, $ilUser->getId(), 'notgraded');
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
}

?>