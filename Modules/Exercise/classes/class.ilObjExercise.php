<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once "classes/class.ilObject.php";
require_once "./Modules/Exercise/classes/class.ilFileDataExercise.php";
require_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";

/** @defgroup ModulesExercise Modules/Exercise
 */

/**
* Class ilObjExercise
*
* @author Stefan Meyer <smeyer@databay.de>
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
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjExercise($a_id = 0,$a_call_by_reference = true)
	{
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
	function getFiles()
	{
		return $this->files;
	}

	function checkDate()
	{
		return	$this->hour == (int) date("H",$this->timestamp) and
			$this->minutes == (int) date("i",$this->timestamp) and
			$this->day == (int) date("d",$this->timestamp) and
			$this->month == (int) date("m",$this->timestamp) and
			$this->year == (int) date("Y",$this->timestamp);

	}

	function deliverFile($a_http_post_files, $user_id, $unzip = false)
	{
		global $ilDB;
		
		$deliver_result = $this->file_obj->deliverFile($a_http_post_files, $user_id, $unzip);
//var_dump($deliver_result);
		if ($deliver_result)
		{
			$next_id = $ilDB->nextId("exc_returned");
			$query = sprintf("INSERT INTO exc_returned ".
							 "(returned_id, obj_id, user_id, filename, filetitle, mimetype, ts) ".
							 "VALUES (%s, %s, %s, %s, %s, %s, %s)",
				$ilDB->quote($next_id, "integer"),
				$ilDB->quote($this->getId(), "integer"),
				$ilDB->quote($user_id, "integer"),
				$ilDB->quote($deliver_result["fullname"], "text"),
				$ilDB->quote($a_http_post_files["name"], "text"),
				$ilDB->quote($deliver_result["mimetype"], "text"),
				$ilDB->quote(ilUtil::now(), "timestamp")
			);
			$ilDB->manipulate($query);
			if (!$this->members_obj->isAssigned($user_id))
			{
				$this->members_obj->assignMember($user_id);
			}
			$this->members_obj->setStatusReturnedForMember($user_id, 1);
		}
		return true;
	}

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
			"time_stamp" => array("integer", $this->getTimestamp())
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
	 	$new_obj->saveData();
	 	
		// Copy files
		$tmp_file_obj =& new ilFileDataExercise($this->getId());
		$tmp_file_obj->ilClone($new_obj->getId());
		unset($tmp_file_obj);
		
		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
		
		return $new_obj;
	}
	

	/**
	* Returns the delivered files of an user
	* @param numeric $user_id The database id of the user
	* @return array An array containing the information on the delivered files
	* @access	public
	*/
	function &getDeliveredFiles($user_id)
	{
		$delivered_files =& $this->members_obj->getDeliveredFiles($user_id);
		return $delivered_files;
	}

	/**
	* Deletes already delivered files
	* @param array $file_id_array An array containing database ids of the delivered files
	* @param numeric $user_id The database id of the user
	* @access	public
	*/
	function deleteDeliveredFiles($file_id_array, $user_id)
	{
		$this->members_obj->deleteDeliveredFiles($file_id_array, $user_id);

		// Finally update status 'returned' of member if no file exists
		if(!count($this->members_obj->getDeliveredFiles($user_id)))
		{
			$this->members_obj->setStatusReturnedForMember($user_id,0);
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

		$this->file_obj->delete();
		$this->members_obj->delete();

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
		}
		$this->members_obj =& new ilExerciseMembers($this->getId(),$this->getRefId());
		$this->members_obj->read();

		// GET FILE ASSIGNED TO EXERCISE
		$this->file_obj = new ilFileDataExercise($this->getId());
		$this->files = $this->file_obj->getFiles();

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
		
		$ilDB->update("exc_data", array(
			"instruction" => array("clob", $this->getInstruction()),
			"time_stamp" => array("integer", $this->getTimestamp())
			), array(
			"obj_id" => array("integer", $this->getId())
			));

		//$res = $this->ilias->db->query($query);

		#$this->members_obj->update();
		return true;
	}

	/**
	* get member list data
	*/
	function getMemberListData()
	{
		global $ilDB;

		$mem = array();
		$q = "SELECT * FROM exc_members ".
			"WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($q);
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (ilObject::_exists($rec["usr_id"]) &&
				(ilObject::_lookupType($rec["usr_id"]) == "usr"))
			{
				$name = ilObjUser::_lookupName($rec["usr_id"]);
				$login = ilObjUser::_lookupLogin($rec["usr_id"]);
				$mem[] =
					array(
					"name" => $name["lastname"].", ".$name["firstname"],
					"login" => $login,
					"sent_time" => $rec["sent_time"],
					"submission" => $this->getLastSubmission($rec["usr_id"]),
					"status_time" => $rec["status_time"],
					"feedback_time" => $rec["feedback_time"],
					"usr_id" => $rec["usr_id"],
					"lastname" => $name["lastname"],
					"firstname" => $name["firstname"],
					"notice" => $rec["notice"],
					"status" => $rec["status"]					
					);
			}
		}
		return $mem;
	}

	/**
	* Get the date of the last submission of a user for the exercise.
	*
	* @param	int		$member_id	User ID of member.
	* @return	mixed	false or mysql timestamp of last submission
	*/
	function getLastSubmission($member_id)
	{
		global $ilDB, $lng;

		$q="SELECT obj_id,user_id,ts FROM exc_returned ".
			"WHERE obj_id =".$ilDB->quote($this->getId(), "integer")." AND user_id=".
			$ilDB->quote($member_id, "integer").
			" ORDER BY ts DESC";

		$usr_set = $ilDB->query($q);

		$array = $ilDB->fetchAssoc($usr_set);
		if ($array["ts"]==NULL)
		{
			return false;
  		}
		else
		{
			return ilUtil::getMySQLTimestamp($array["ts"]);
  		}
	}

	/**
	* send exercise per mail to members
	*/
	function send($a_members)
	{
		$files = $this->file_obj->getFiles();
		if(count($files))
		{
			include_once "./classes/class.ilFileDataMail.php";

			$mfile_obj = new ilFileDataMail($_SESSION["AccountId"]);
			foreach($files as $file)
			{
				$mfile_obj->copyAttachmentFile($this->file_obj->getAbsolutePath($file["name"]),$file["name"]);
				$file_names[] = $file["name"];
			}
		}

		include_once "Services/Mail/classes/class.ilMail.php";

		$tmp_mail_obj = new ilMail($_SESSION["AccountId"]);
		$message = $tmp_mail_obj->sendMail($this->__formatRecipients($a_members),"","",$this->__formatSubject(),$this->__formatBody(),
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
			$this->members_obj->setStatusSentForMember($member_id,1);
		}

		return true;
	}

	/**
	* Check whether student has upload new files after tutor has
	* set the exercise to another than notgraded.
	*/
	function _lookupUpdatedSubmission($exc_id, $member_id)
	{

  		global $ilDB, $lng;

  		$q="SELECT exc_members.status_time, exc_returned.ts ".
			"FROM exc_members, exc_returned ".
			"WHERE exc_members.status_time < exc_returned.ts ".
			"AND NOT exc_members.status_time IS NULL ".
			"AND exc_returned.obj_id = exc_members.obj_id ".
			"AND exc_returned.user_id = exc_members.usr_id ".
			"AND exc_returned.obj_id=".$ilDB->quote($exc_id, "integer")." AND exc_returned.user_id=".
			$ilDB->quote($member_id, "integer");

  		$usr_set = $ilDB->query($q);

  		$array = $ilDB->fetchAssoc($usr_set);

		if (count($array)==0)
		{
			return 0;
  		}
		else
		{
			return 1;
		}

	}


	/**
	* Check whether exercise has been sent to any student per mail.
	*/
	function _lookupAnyExerciseSent($a_exc_id)
	{
  		global $ilDB;

  		$q = "SELECT count(*) AS cnt FROM exc_members".
			" WHERE NOT sent_time IS NULL".
			" AND obj_id = ".$ilDB->quote($a_exc_id, "integer");
		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);

		if ($rec["cnt"] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Check how much files have been uploaded by the learner
	* after the last download of the tutor.
	*/
	function _lookupNewFiles($exc_id, $member_id)
	{
  		global $ilDB, $ilUser;

  		$q = "SELECT exc_returned.returned_id AS id ".
			"FROM exc_usr_tutor, exc_returned ".
			"WHERE exc_returned.obj_id = exc_usr_tutor.obj_id ".
			" AND exc_returned.user_id = exc_usr_tutor.usr_id ".
			" AND exc_returned.obj_id = ".$ilDB->quote($exc_id, "integer").
			" AND exc_returned.user_id = ".$ilDB->quote($member_id, "integer").
			" AND exc_usr_tutor.tutor_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND exc_usr_tutor.download_time < exc_returned.ts ";

  		$new_up_set = $ilDB->query($q);

		$new_up = array();
  		while ($new_up_rec = $ilDB->fetchAssoc($new_up_set))
		{
			$new_up[] = $new_up_rec["id"];
		}

		return $new_up;
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

	/**
	* Get time when exercise has been sent per e-mail to user
	*/
	function _lookupSentTime($exc_id, $member_id)
	{

  		global $ilDB, $lng;

  		$q = "SELECT * ".
		"FROM exc_members ".
		"WHERE obj_id= ".$ilDB->quote($exc_id, "integer").
		" AND usr_id= ".$ilDB->quote($member_id, "integer");

  		$set = $ilDB->query($q);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return ilUtil::getMySQLTimestamp($rec["sent_time"]);
		}
	}

	/**
	* Get time when feedback mail has been sent.
	*/
	function _lookupFeedbackTime($exc_id, $member_id)
	{

  		global $ilDB, $lng;

  		$q = "SELECT * ".
		"FROM exc_members ".
		"WHERE obj_id= ".$ilDB->quote($exc_id, "integer").
		" AND usr_id= ".$ilDB->quote($member_id, "integer");

  		$set = $ilDB->query($q);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return ilUtil::getMySQLTimestamp($rec["feedback_time"]);
		}
	}

	// PRIVATE METHODS
	function __formatBody()
	{
		global $lng;

		$body = $this->getInstruction();
		$body .= "\n\n";
		$body .= $lng->txt("exc_edit_until") . ": ".
			ilFormat::formatDate(date("Y-m-d H:i:s",$this->getTimestamp()), "datetime", true);
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
	function processUploadedFile ($fileTmp, $storageMethod, $persistentErrorMessage)
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
					$this->$storageMethod($a_http_post_files, $ilUser->id, true);
				}
				else if ($storageMethod == "storeUploadedFile")
				{
					$this->file_obj->$storageMethod($a_http_post_files, true, true);				
				}
			}
			ilUtil::sendInfo($this->lng->txt("file_added"),$persistentErrorMessage);					

		} 
		catch (ilFileUtilsException $e) 
		{
			ilUtil::sendInfo($e->getMessage(), $persistentErrorMessage);
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
	
} //END class.ilObjExercise
?>
