<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Exercise assignment
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesExercise
*/
class ilExAssignment
{
	const TYPE_UPLOAD = 1;
	const TYPE_BLOG = 2;
	const TYPE_PORTFOLIO = 3;
	
	/**
	 * Constructor
	 */
	function __construct($a_id = 0)
	{
		$this->setType(self::TYPE_UPLOAD);
		
		if ($a_id > 0)
		{
			$this->setId($a_id);
			$this->read();
		}
	}
	
	/**
	 * Set assignment id
	 *
	 * @param	int		assignment id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get assignment id
	 *
	 * @return	int	assignment id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set exercise id
	 *
	 * @param	int		exercise id
	 */
	function setExerciseId($a_val)
	{
		$this->exc_id = $a_val;
	}
	
	/**
	 * Get exercise id
	 *
	 * @return	int	exercise id
	 */
	function getExerciseId()
	{
		return $this->exc_id;
	}
	
	/**
	 * Set start time (timestamp)
	 *
	 * @param	int		start time (timestamp)
	 */
	function setStartTime($a_val)
	{
		$this->start_time = $a_val;
	}
	
	/**
	 * Get start time (timestamp)
	 *
	 * @return	int		start time (timestamp)
	 */
	function getStartTime()
	{
		return $this->start_time;
	}

	/**
	 * Set deadline (timestamp)
	 *
	 * @param	int		deadline (timestamp)
	 */
	function setDeadline($a_val)
	{
		$this->deadline = $a_val;
	}
	
	/**
	 * Get deadline (timestamp)
	 *
	 * @return	int		deadline (timestamp)
	 */
	function getDeadline()
	{
		return $this->deadline;
	}

	/**
	 * Set instruction
	 *
	 * @param	string		instruction
	 */
	function setInstruction($a_val)
	{
		$this->instruction = $a_val;
	}
	
	/**
	 * Get instruction
	 *
	 * @return	string		instruction
	 */
	function getInstruction()
	{
		return $this->instruction;
	}

	/**
	 * Set title
	 *
	 * @param	string		title
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return	string	title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set mandatory
	 *
	 * @param	int		mandatory
	 */
	function setMandatory($a_val)
	{
		$this->mandatory = $a_val;
	}
	
	/**
	 * Get mandatory
	 *
	 * @return	int	mandatory
	 */
	function getMandatory()
	{
		return $this->mandatory;
	}

	/**
	 * Set order nr
	 *
	 * @param	int		order nr
	 */
	function setOrderNr($a_val)
	{
		$this->order_nr = $a_val;
	}
	
	/**
	 * Get order nr
	 *
	 * @return	int	order nr
	 */
	function getOrderNr()
	{
		return $this->order_nr;
	}
	
	/**
	 * Set type
	 * 
	 * @param int $a_value 
	 */
	function setType($a_value)
	{
		if($this->isValidType($a_value))
		{
			$this->type = (int)$a_value;
		}
	}
	
	/**
	 * Get type
	 * 
	 * @return int
	 */
	function getType()
	{
		return $this->type;
	}
	
	/**
	 * Is given type valid?
	 * 
	 * @param int $a_value
	 * @return bool
	 */
	function isValidType($a_value)
	{
		if(in_array((int)$a_value, array(self::TYPE_UPLOAD, self::TYPE_BLOG, self::TYPE_PORTFOLIO)))
		{
			return true;
		}
		return false;
	}

	/**
	 * Read from db
	 */
	function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM exc_assignment ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$this->setExerciseId($rec["exc_id"]);
			$this->setDeadline($rec["time_stamp"]);
			$this->setInstruction($rec["instruction"]);
			$this->setTitle($rec["title"]);
			$this->setStartTime($rec["start_time"]);
			$this->setOrderNr($rec["order_nr"]);
			$this->setMandatory($rec["mandatory"]);
			$this->setType($rec["type"]);
		}
	}
	
	/**
	 * Save assignment
	 */
	function save()
	{
		global $ilDB;
		
		if ($this->getOrderNr() == 0)
		{
			$this->setOrderNr(
				ilExAssignment::lookupMaxOrderNrForEx($this->getExerciseId())
				+ 10);
		}
		
		$next_id = $ilDB->nextId("exc_assignment");
		$ilDB->insert("exc_assignment", array(
			"id" => array("integer", $next_id),
			"exc_id" => array("integer", $this->getExerciseId()),
			"time_stamp" => array("integer", $this->getDeadline()),
			"instruction" => array("clob", $this->getInstruction()),
			"title" => array("text", $this->getTitle()),
			"start_time" => array("integer", $this->getStartTime()),
			"order_nr" => array("integer", $this->getOrderNr()),
			"mandatory" => array("integer", $this->getMandatory()),
			"type" => array("integer", $this->getType())
			));
		$this->setId($next_id);
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		ilExAssignment::createNewAssignmentRecords($next_id, $exc);
	}
	
	/**
	 * Update
	 */
	function update()
	{
		global $ilDB;
		
		$ilDB->update("exc_assignment",
			array(
			"exc_id" => array("integer", $this->getExerciseId()),
			"time_stamp" => array("integer", $this->getDeadline()),
			"instruction" => array("clob", $this->getInstruction()),
			"title" => array("text", $this->getTitle()),
			"start_time" => array("integer", $this->getStartTime()),
			"order_nr" => array("integer", $this->getOrderNr()),
			"mandatory" => array("integer", $this->getMandatory()),
			"type" => array("integer", $this->getType())
			),
			array(
			"id" => array("integer", $this->getId()),
			));
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
	}
	
	/**
	 * Delete assignment
	 */
	function delete()
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM exc_assignment WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
	}
	
	
	/**
	 * Get assignments data of an exercise in an array
	 */
	static function getAssignmentDataOfExercise($a_exc_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_exc_id, "integer").
			" ORDER BY order_nr ASC");
		$data = array();

		$order_val = 10;
		while ($rec = $ilDB->fetchAssoc($set))
		{
			
			$data[] = array(
				"id" => $rec["id"],
				"exc_id" => $rec["exc_id"],
				"deadline" => $rec["time_stamp"],
				"instruction" => $rec["instruction"],
				"title" => $rec["title"],
				"start_time" => $rec["start_time"],
				"order_val" => $order_val,
				"mandatory" => $rec["mandatory"],
				"type" => $rec["type"]
				);
			$order_val += 10;
		}
		return $data;
	}
	
	/**
	 * Clone assignments of exercise
	 *
	 * @param
	 * @return
	 */
	function cloneAssignmentsOfExercise($a_old_exc_id, $a_new_exc_id)
	{
		$ass_data = ilExAssignment::getAssignmentDataOfExercise($a_old_exc_id);
		foreach ($ass_data as $d)
		{
			// clone assignment
			$new_ass = new ilExAssignment();
			$new_ass->setExerciseId($a_new_exc_id);
			$new_ass->setTitle($d["title"]);
			$new_ass->setDeadline($d["deadline"]);
			$new_ass->setInstruction($d["instruction"]);
			$new_ass->setMandatory($d["mandatory"]);
			$new_ass->setOrderNr($d["order_val"]);
			$new_ass->setStartTime($d["start_time"]);
			$new_ass->setType($d["type"]);
			$new_ass->save();
			
			// clone assignment files
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$old_storage = new ilFSStorageExercise($a_old_exc_id, (int) $d["id"]);
			$new_storage = new ilFSStorageExercise($a_new_exc_id, (int) $new_ass->getId());
			$new_storage->create();
			
			if (is_dir($old_storage->getPath()))
			{
				ilUtil::rCopy($old_storage->getPath(), $new_storage->getPath());
			}
		}
	}
	
	/**
	 * Get files
	 */
	static function getFiles($a_exc_id, $a_ass_id)
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		return $storage->getFiles();
	}
	
	/**
	 * Select the maximum order nr for an exercise
	 */
	static function lookupMaxOrderNrForEx($a_exc_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT MAX(order_nr) mnr FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_exc_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			return (int) $rec["mnr"];
		}
		return 0;
	}
	
	/**
	 * Check if assignment is online
	 * @param int $a_ass_id
	 * @return bool
	 */
	public static function lookupAssignmentOnline($a_ass_id)
	{
		global $ilDB;
		
		$query = "SELECT id FROM exc_assignment ".
			"WHERE start_time <= ".$ilDB->quote(time(),'integer').' '.
			"AND time_stamp >= ".$ilDB->quote(time(),'integer').' '.
			"AND id = ".$ilDB->quote($a_ass_id,'integer');
		$res = $ilDB->query($query);
		
		return $res->numRows() ? true : false;
	}
	
	
	/**
	 * Private lookup
	 */
	private static function lookup($a_id, $a_field)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT ".$a_field." FROM exc_assignment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);

		$rec = $ilDB->fetchAssoc($set);

		return $rec[$a_field];
	}
	
	/**
	 * Lookup title
	 */
	static function lookupTitle($a_id)
	{
		return ilExAssignment::lookup($a_id, "title");
	}
	
	/**
	 * Lookup type
	 */
	static function lookupType($a_id)
	{
		return ilExAssignment::lookup($a_id, "type");
	}
	
	/**
	 * Save ordering of all assignments of an exercise
	 */
	function saveAssOrderOfExercise($a_ex_id, $a_order)
	{
		global $ilDB;
		
		$result_order = array();
		asort($a_order);
		$nr = 10;
		foreach ($a_order as $k => $v)
		{
			// the check for exc_id is for security reasons. ass ids are unique.
			$ilDB->manipulate($t = "UPDATE exc_assignment SET ".
				" order_nr = ".$ilDB->quote($nr, "integer").
				" WHERE id = ".$ilDB->quote((int) $k, "integer").
				" AND exc_id = ".$ilDB->quote((int) $a_ex_id, "integer")
				);
			$nr+=10;
		}
	}
	
	/**
	 * Order assignments by deadline date
	 */
	function orderAssByDeadline($a_ex_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT id FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_ex_id, "integer").
			" ORDER BY time_stamp ASC"
			);
		$nr = 10;
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE exc_assignment SET ".
				" order_nr = ".$ilDB->quote($nr, "integer").
				" WHERE id = ".$ilDB->quote($rec["id"], "integer")
				);
			$nr += 10;
		}
	}

	/**
	 * Order assignments by deadline date
	 */
	function countMandatory($a_ex_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT count(*) cntm FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_ex_id, "integer").
			" AND mandatory = ".$ilDB->quote(1, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["cntm"];
	}

////
//// Functions from ilExerciseMembers -> migrate!
////
	
	/**
	 * Lookup a field value of ass/member table
	 */
	private function lookupAssMemberField($a_ass_id, $a_user_id, $a_field)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT ".$a_field." FROM exc_mem_ass_status ".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		
		return $rec[$a_field];
	}
	
	/**
	 * Update a field value of ass/member table
	 */
	private function updateAssMemberField($a_ass_id, $a_user_id, $a_field, $a_value, $a_type)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE exc_mem_ass_status SET ".
			" ".$a_field." = ".$ilDB->quote($a_value, $a_type).
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
	}
	

/*	function setStatus($a_status)
	{
		if(is_array($a_status))
		{
			$this->status = $a_status;
			return true;
		}
	}
	function getStatus()
	{
		return $this->status ? $this->status : array();
	}*/
	
	/**
	 * Lookup comment for the user
	 */
	function lookupCommentForUser($a_ass_id, $a_user_id)
	{
		return ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "u_comment");
	}

	/**
	 * Update comment
	 */
	function updateCommentForUser($a_ass_id, $a_user_id, $a_value)
	{
		ilExAssignment::updateAssMemberField($a_ass_id, $a_user_id,
			"u_comment", $a_value, "text");
	}

	/**
	 * Lookup user mark
	 */
	function lookupMarkOfUser($a_ass_id, $a_user_id)
	{
		return ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "mark");
	}

	/**
	 * Update mark
	 */
	function updateMarkOfUser($a_ass_id, $a_user_id, $a_value)
	{
		ilExAssignment::updateAssMemberField($a_ass_id, $a_user_id,
			"mark", $a_value, "text");
	}

	/**
	 * was: getStatusByMember
	 */
	function lookupStatusOfUser($a_ass_id, $a_user_id)
	{
		$stat = ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "status");
		if ($stat == "")
		{
			$stat = "notgraded";
		}
		return $stat;
	}

	/**
	 * was: setStatusForMember($a_member_id,$a_status)
	 */
	function updateStatusOfUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET status = %s, status_time= %s ".
			" WHERE ass_id = %s AND usr_id = %s AND status <> %s ",
			array("text", "timestamp", "integer", "integer", "text"),
			array($a_status, ilUtil::now(), $a_ass_id, $a_user_id, $a_status));
		
		$ass = new ilExAssignment($a_ass_id);
		$exc = new ilObjExercise($ass->getExerciseId(), false);
		$exc->updateUserStatus($a_user_id);
	}

	/**
	 * was: updateStatusTimeForMember($a_user_id)
	 */
	function updateStatusTimeOfUser($a_ass_id, $a_user_id)
	{
		ilExAssignment::updateAssMemberField($a_ass_id, $a_user_id,
			"status_time", ilUtil::now(), "timestamp");
	}


	/*function setStatusSent($a_status)
	{
		if(is_array($a_status))
		{
			$this->status_sent = $a_status;
			return true;
		}
	}
	function getStatusSent()
	{
		return $this->status_sent ? $this->status_sent : array(0 => 0);
	}*/
	
	/**
	 * was: getStatusSentByMember($a_member_id)
	 */
	function lookupStatusSentOfUser($a_ass_id, $a_user_id)
	{
		return ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "sent");
	}
	
	/**
	 * was: setStatusSentForMember($a_member_id,$a_status)
	 */
	function updateStatusSentForUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET sent = %s, status_time= %s, sent_time = %s ".
			" WHERE ass_id = %s AND usr_id = %s ",
			array("integer", "timestamp", "timestamp", "integer", "integer"),
			array((int) $a_status, ilUtil::now(), ($a_status ? ilUtil::now() : null),
				$a_ass_id, $a_user_id));
	}

	/*function getStatusReturned()
	{
		return $this->status_returned ? $this->status_returned : array(0 => 0);
	}
	function setStatusReturned($a_status)
	{
		if(is_array($a_status))
		{
			$this->status_returned = $a_status;
			return true;
		}
		return false;
	}*/

	/**
	 * was: getStatusReturnedByMember($a_member_id)
	 */
	function lookupStatusReturnedOfUser($a_ass_id, $a_user_id)
	{
		return ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "returned");
	}

	/**
	 * was: setStatusReturnedForMember($a_member_id,$a_status)
	 */
	function updateStatusReturnedForUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET returned = %s, status_time= %s ".
			" WHERE ass_id = %s AND usr_id = %s",
			array("integer", "timestamp", "integer", "integer"),
			array((int) $a_status, ilUtil::now(),
				$a_ass_id, $a_user_id));
	}

/*	// feedback functions
	function setStatusFeedback($a_status)
	{
		if(is_array($a_status))
		{
			$this->status_feedback = $a_status;
			return true;
		}
	}
	function getStatusFeedback()
	{
		return $this->status_feedback ? $this->status_feedback : array(0 => 0);
	}*/
	
	/**
	 * was: getStatusFeedbackByMember($a_member_id)
	 */
	function lookupStatusFeedbackOfUser($a_ass_id, $a_user_id)
	{
		return ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "feedback");
	}

	/**
	 * was: setStatusFeedbackForMember($a_member_id,$a_status)
	 */
	function updateStatusFeedbackForUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET feedback = %s, status_time= %s, feedback_time = %s ".
			" WHERE ass_id = %s AND usr_id = %s",
			array("integer", "timestamp", "timestamp", "integer", "integer"),
			array((int) $a_status, ilUtil::now(), ($a_status ? ilUtil::now() : null),
				$a_ass_id, $a_user_id));
	}

	/**
	 * Get time when exercise has been sent per e-mail to user
	 */
	static function lookupSentTimeOfUser($a_ass_id, $a_user_id)
	{
		return ilUtil::getMySQLTimestamp(
			ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "sent_time"));
	}

	/**
	 * Get time when feedback mail has been sent.
	 */
	static function lookupFeedbackTimeOfUser($a_ass_id, $a_user_id)
	{
		return ilUtil::getMySQLTimestamp(
			ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "feedback_time"));
	}
	
	/**
	 * Get status time
	 */
	static function lookupStatusTimeOfUser($a_ass_id, $a_user_id)
	{
		return ilUtil::getMySQLTimestamp(
			ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "status_time"));
	}

	/*function getNotice()
	{
		return $this->notice ? $this->notice : array(0 => 0);
	}

	function setNotice($a_notice)
	{
		if(is_array($a_notice))
		{
			$this->notice = $a_notice;
			return true;
		}
		return false;
	}*/

	/**
	 * was: getNoticeByMember($a_member_id)
	 */
	function lookupNoticeOfUser($a_ass_id, $a_user_id)
	{
		return ilExAssignment::lookupAssMemberField($a_ass_id, $a_user_id, "notice");
	}

	/**
	 * was: hasReturned($a_member_id)
	 */
	function hasReturned($a_ass_id, $a_user_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT returned_id FROM exc_returned WHERE ass_id = %s AND user_id = %s",
			array("integer", "integer"),
			array($ass_id, $a_user_id));
		return $ilDB->numRows($result);
	}

	
	/**
	 * was: getAllDeliveredFiles()
	 */
	function getAllDeliveredFiles($a_exc_id, $a_ass_id)
	{
		global $ilDB;
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);

		$query = "SELECT * FROM exc_returned WHERE ass_id = ".
			$ilDB->quote($a_ass_id, "integer");

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$row["timestamp"] = $row["ts"];
			$row["filename"] = $fs->getAbsoluteSubmissionPath().
				"/".$row["user_id"]."/".basename($row["filename"]);
			$delivered[] = $row;
		}
		
		//$delivered = ilObjExercise::_fixFilenameArray($delivered);

		return $delivered ? $delivered : array();
	}

	/**
	 * was: getDeliveredFiles($a_member_id)
	 */
	function getDeliveredFiles($a_exc_id, $a_ass_id, $a_user_id)
	{
		global $ilDB;

		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		
		$result = $ilDB->queryF("SELECT * FROM exc_returned WHERE ass_id = %s AND user_id = %s ORDER BY ts",
			array("integer", "integer"),
			array($a_ass_id, $a_user_id));
		
		$delivered_files = array();
		if ($ilDB->numRows($result))
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$row["timestamp"] = $row["ts"];
				$row["timestamp14"] = substr($row["ts"], 0, 4).
					substr($row["ts"], 5, 2).substr($row["ts"], 8, 2).
					substr($row["ts"], 11, 2).substr($row["ts"], 14, 2).
					substr($row["ts"], 17, 2);
				$row["filename"] = $fs->getAbsoluteSubmissionPath().
					"/".$row["user_id"]."/".basename($row["filename"]);
				array_push($delivered_files, $row);
			}
		}
		
		//$delivered_files = ilObjExercise::_fixFilenameArray($delivered_files);

		return $delivered_files;
	}

	/**
	 * was: deleteDeliveredFiles($file_id_array, $a_member_id)
	 */
	function deleteDeliveredFiles($a_exc_id, $a_ass_id, $file_id_array, $a_user_id)
	{
		global $ilDB;

		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);

		if (count($file_id_array))
		{
			$result = $ilDB->query("SELECT * FROM exc_returned WHERE user_id = ".
				$ilDB->quote($a_user_id, "integer")." AND ".
				$ilDB->in("returned_id", $file_id_array, false, "integer"));

			if ($ilDB->numRows($result))
			{
				$result_array = array();
				while ($row = $ilDB->fetchAssoc($result))
				{
					$row["timestamp"] = $row["ts"];
					array_push($result_array, $row);
				}
				// delete the entries in the database
				$ilDB->manipulate("DELETE FROM exc_returned WHERE user_id = ".
					$ilDB->quote($a_user_id, "integer")." AND ".
					$ilDB->in("returned_id", $file_id_array, false, "integer"));
					//returned_id IN ("
					//.implode(ilUtil::quoteArray($file_id_array) ,",").")",
					//$this->ilias->db->quote($a_member_id . "")

				// delete the files
				foreach ($result_array as $key => $value)
				{
					$filename = $fs->getAbsoluteSubmissionPath().
						"/".$value["user_id"]."/".basename($value["filename"]);
					unlink($filename);
				}
			}
		}
	}

	/**
	 * was: deliverReturnedFiles($a_member_id, $a_only_new = false)
	 */
	function deliverReturnedFiles($a_exc_id, $a_ass_id, $a_user_id, $a_only_new = false)
	{
		global $ilUser, $ilDB;

		// get last download time
		$and_str = "";
		if ($a_only_new)
		{
			$q = "SELECT download_time FROM exc_usr_tutor WHERE ".
				" ass_id = ".$ilDB->quote($a_ass_id, "integer")." AND ".
				" usr_id = ".$ilDB->quote($a_user_id, "integer")." AND ".
				" tutor_id = ".$ilDB->quote($ilUser->getId(), "integer");
			$lu_set = $ilDB->query($q);
			if ($lu_rec = $ilDB->fetchAssoc($lu_set))
			{
				if ($lu_rec["download_time"] > 0)
				{
					$and_str = " AND ts > ".$ilDB->quote($lu_rec["download_time"], "timestamp");
				}
			}
		}

		ilExAssignment::updateTutorDownloadTime($a_exc_id, $a_ass_id, $a_user_id);

		$query = sprintf("SELECT * FROM exc_returned WHERE ass_id = %s AND user_id = %s".
			$and_str,
			$ilDB->quote($a_ass_id, "integer"),
			$ilDB->quote($a_user_id, "integer"));
		$result = $ilDB->query($query);
		$count = $ilDB->numRows($result);
		if ($count == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			ilExAssignment::downloadSingleFile($a_exc_id, $a_ass_id, $a_user_id,
				$row["filename"], $row["filetitle"]);
		}
		else if ($count > 0)
		{
			$array_files = array();
			$filename = "";
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($array_files, basename($row["filename"]));
//				$filename = $row["filename"];
//				$pathinfo = pathinfo($filename);
//				$dir = $pathinfo["dirname"];
//				$file = $pathinfo["basename"];
//				array_push($array_files, $file);
			}
			$pathinfo = pathinfo($filename);
			$dir = $pathinfo["dirname"];

			ilExAssignment::downloadMultipleFiles($a_exc_id, $a_ass_id, $array_files, $a_user_id);
		}
		else
		{
			return false;
		}

		return true;
	}

	// Update the timestamp of the last download of current user (=tutor)
	/**
	 * was: updateTutorDownloadTime($member_id)
	 */
	function updateTutorDownloadTime($a_exc_id, $a_ass_id, $a_user_id)
	{
		global $ilUser, $ilDB;

		$ilDB->manipulateF("DELETE FROM exc_usr_tutor ".
			"WHERE ass_id = %s AND usr_id = %s AND tutor_id = %s",
			array("integer", "integer", "integer"),
			array($a_ass_id, $a_user_id, $ilUser->getId()));

		$ilDB->manipulateF("INSERT INTO exc_usr_tutor (ass_id, obj_id, usr_id, tutor_id, download_time) VALUES ".
			"(%s, %s, %s, %s, %s)",
			array("integer", "integer", "integer", "integer", "timestamp"),
			array($a_ass_id, $a_exc_id, $a_user_id, $ilUser->getId(), ilUtil::now()));
	}

	/**
	 * was: downloadSelectedFiles($array_file_id,$a_user_id)?
	 */
	function downloadSelectedFiles($a_exc_id, $a_ass_id, $a_user_id, $array_file_id)
	{
		global $ilDB;
		
		if (count($array_file_id))
		{
			$result = $ilDB->query("SELECT * FROM exc_returned WHERE ".
				$ilDB->in("returned_id", $array_file_id, false, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id));
			if ($ilDB->numRows($result))
			{
				$array_found = array();
				while ($row = $ilDB->fetchAssoc($result))
				{
					$row["timestamp"] = $row["ts"];
					array_push($array_found, $row);
				}
				if (count($array_found) == 1)
				{
					ilExAssignment::downloadSingleFile($a_exc_id, $a_ass_id, $a_user_id,
						$array_found[0]["filename"], $array_found[0]["filetitle"]);
				}
				else
				{
					$filenames = array();
					$dir = "";
					$file = "";
					foreach ($array_found as $key => $value)
					{
						//$pathinfo = pathinfo(ilObjExercise::_fixFilename($value["filename"]));
						//$dir = $pathinfo["dirname"];
						//$file = $pathinfo["basename"];
						//array_push($filenames, $file);
						array_push($filenames, basename($value["filename"]));
					}
					ilExAssignment::downloadMultipleFiles($a_exc_id, $a_ass_id, 
						$filenames, $a_user_id);
				}
			}
		}
	}

	/**
	 * was: downloadSingleFile($filename, $filetitle)
	 */
	function downloadSingleFile($a_exc_id, $a_ass_id, $a_user_id, $filename, $filetitle)
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);

		$filename = $fs->getAbsoluteSubmissionPath().
			"/".$a_user_id."/".basename($filename);

		require_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::deliverFile($filename, $filetitle);
	}

	/**
	 * was: downloadMultipleFiles($array_filenames, $pathname, $a_member_id = 0)
	 */
// @todo: check whether files of multiple users are downloaded this way
	function downloadMultipleFiles($a_exc_id, $a_ass_id, $array_filenames,
		$a_user_id)
	{
		global $lng, $ilObjDataCache;
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		$pathname = $fs->getAbsoluteSubmissionPath().
			"/".$a_user_id;
		
		require_once "./Services/Utilities/classes/class.ilUtil.php";
		$cdir = getcwd();

		$zip = PATH_TO_ZIP;
		$tmpdir = ilUtil::ilTempnam();
		$tmpfile = ilUtil::ilTempnam();
		$tmpzipfile = $tmpfile . ".zip";

		ilUtil::makeDir($tmpdir);
		chdir($tmpdir);

		$assTitle = ilExAssignment::lookupTitle($a_ass_id);
		$deliverFilename = str_replace(" ", "_", $assTitle);
		if ($a_user_id > 0)
		{
			$userName = ilObjUser::_lookupName($a_user_id);
			$deliverFilename .= "_".$userName["lastname"]."_".$userName["firstname"];
		}
		else
		{
			$deliverFilename .= "_files";
		}
		$orgDeliverFilename = trim($deliverFilename);
		$deliverFilename = ilUtil::getASCIIFilename($orgDeliverFilename);
		ilUtil::makeDir($tmpdir."/".$deliverFilename);
		chdir($tmpdir."/".$deliverFilename);

		//copy all files to a temporary directory and remove them afterwards
		foreach ($array_filenames as $key => $filename)
		{
			// remove timestamp
			$newFilename = trim($filename);
			$pos = strpos($newFilename , "_");
			if ($pos === false)
			{
			} else
			{
				$newFilename= substr($newFilename, $pos + 1);
			}
			$newFilename = $tmpdir.DIRECTORY_SEPARATOR.$deliverFilename.DIRECTORY_SEPARATOR.$newFilename;
			// copy to temporal directory
			$oldFilename =  $pathname.DIRECTORY_SEPARATOR.$filename;
			if (!copy ($oldFilename, $newFilename))
			{
				echo 'Could not copy '.$oldFilename.' to '.$newFilename;
			}
			touch($newFilename, filectime($oldFilename));
			$array_filenames[$key] =  ilUtil::escapeShellArg($deliverFilename.DIRECTORY_SEPARATOR.basename($newFilename)); //$array_filenames[$key]);
		}
		chdir($tmpdir);
		$zipcmd = $zip." ".ilUtil::escapeShellArg($tmpzipfile)." ".join($array_filenames, " ");
//echo getcwd()."<br>";
//echo $zipcmd; exit;
		exec($zipcmd);
		ilUtil::delDir($tmpdir);
		
		chdir($cdir);
		ilUtil::deliverFile($tmpzipfile, $orgDeliverFilename.".zip");
		unlink($tmpzipfile);
	}

	/**
	 * Download all submitted files of an assignment (all user)
	 *
	 * @param	$members		array of user names, key is user id
	 */
	function downloadAllDeliveredFiles($a_exc_id, $a_ass_id, $members)
	{
		global $lng, $ilObjDataCache, $ilias;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		
		$storage = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		$storage->create();
		
		ksort($members);
		//$savepath = $this->getExercisePath() . "/" . $this->obj_id . "/";
		$savepath = $storage->getAbsoluteSubmissionPath();
		$cdir = getcwd();


		// important check: if the directory does not exist
		// ILIAS stays in the current directory (echoing only a warning)
		// and the zip command below archives the whole ILIAS directory
		// (including the data directory) and sends a mega file to the user :-o
		if (!is_dir($savepath))
		{
			return;
		}
		// Safe mode fix
//		chdir($this->getExercisePath());
		chdir($storage->getTempPath());
		$zip = PATH_TO_ZIP;

		// check first, if we have enough free disk space to copy all files to temporary directory
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		chdir($tmpdir);


		$dirsize = 0;
		foreach ($members as $id => $object) {
			$directory = $savepath.DIRECTORY_SEPARATOR.$id;
			$dirsize += ilUtil::dirsize($directory);
		}
		if ($dirsize > disk_free_space($tmpdir)) {
			return -1;
		}

		// copy all member directories to the temporary folder
		// switch from id to member name and append the login if the member name is double
		// ensure that no illegal filenames will be created
		// remove timestamp from filename
		$cache = array();
		foreach ($members as $id => $user)
		{
			$sourcedir = $savepath.DIRECTORY_SEPARATOR.$id;
			if (!is_dir($sourcedir))
				continue;
			$userName = ilObjUser::_lookupName($id);
			$directory = ilUtil::getASCIIFilename(trim($userName["lastname"])."_".trim($userName["firstname"]));
			if (array_key_exists($directory, $cache))
			{
				// first try is to append the login;
				$directory = ilUtil::getASCIIFilename($directory."_".trim(ilObjUser::_lookupLogin($id)));
				if (array_key_exists($directory, $cache)) {
					// second and secure: append the user id as well.
					$directory .= "_".$id;
				}
			}

			$cache[$directory] = $directory;
			ilUtil::makeDir ($directory);
			$sourcefiles = scandir($sourcedir);
			foreach ($sourcefiles as $sourcefile) {
				if ($sourcefile == "." || $sourcefile == "..")
					continue;
				$targetfile = trim(basename($sourcefile));
				$pos = strpos($targetfile, "_");
				if ($pos === false)
				{
				} else
				{
					$targetfile= substr($targetfile, $pos + 1);
				}
				$targetfile = $directory.DIRECTORY_SEPARATOR.$targetfile;
				$sourcefile = $sourcedir.DIRECTORY_SEPARATOR.$sourcefile;

				if (!copy ($sourcefile, $targetfile))
				{
					//echo 'Could not copy '.$sourcefile.' to '.$targetfile;
					$ilias->raiseError('Could not copy '.basename($sourcefile)." to '".$targetfile."'.",
						$ilias->error_obj->MESSAGE);
				}
				else
				{
					// preserve time stamp
					touch($targetfile, filectime($sourcefile));
				}

			}
		}

		$tmpfile = ilUtil::ilTempnam();
		$tmpzipfile = $tmpfile . ".zip";
		// Safe mode fix
		$zipcmd = $zip." -r ".ilUtil::escapeShellArg($tmpzipfile)." .";
		exec($zipcmd);
		ilUtil::delDir($tmpdir);

		$assTitle = ilExAssignment::lookupTitle($a_ass_id);
		chdir($cdir);
		ilUtil::deliverFile($tmpzipfile, (strlen($assTitle) == 0
			? strtolower($lng->txt("exc_assignment"))
			: $assTitle). ".zip");
		unlink($tmpfile);
		unlink($tmpzipfile);
	}

	/**
	* was: setNoticeForMember($a_member_id,$a_notice)
	 */
	function updateNoticeForUser($a_ass_id, $a_user_id, $a_notice)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET notice = %s, status_time= %s ".
			" WHERE ass_id = %s AND usr_id = %s AND ".
			$ilDB->equalsNot("notice", $a_notice, "text", true),
			array("text", "timestamp", "integer", "integer"),
			array($a_notice, ilUtil::now(), $a_ass_id, $a_user_id));

	}

	/**
	 * was: _getReturned($a_obj_id)
	 */
	function _getReturned($a_ass_id)
	{
		global $ilDB;

		$query = "SELECT DISTINCT(usr_id) as ud FROM exc_mem_ass_status ".
			"WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer")." ".
			"AND returned = 1";

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$usr_ids[] = $row->ud;
		}

		return $usr_ids ? $usr_ids : array();
	}

	/**
	 * Get the date of the last submission of a user for the assignment
	 *
	 * @param	int		Assignment ID
	 * @param	int		User ID
	 * @return	mixed	false or mysql timestamp of last submission
	 */
	static function getLastSubmission($a_ass_id, $a_user_id)
	{
		global $ilDB, $lng;

		$q = "SELECT obj_id,user_id,ts FROM exc_returned ".
			"WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer")." AND user_id = ".
			$ilDB->quote($a_user_id, "integer").
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
	 * Check whether exercise has been sent to any student per mail.
	 */
	static function lookupAnyExerciseSent($a_exc_id, $a_ass_id)
	{
  		global $ilDB;

  		$q = "SELECT count(*) AS cnt FROM exc_mem_ass_status".
			" WHERE NOT sent_time IS NULL".
			" AND ass_id = ".$ilDB->quote($a_ass_id, "integer")." ".
			" ";
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
	 * Check whether student has upload new files after tutor has
	 * set the exercise to another than notgraded.
	 */
	static function lookupUpdatedSubmission($ass_id, $member_id)
	{

  		global $ilDB, $lng;

  		$q="SELECT exc_mem_ass_status.status_time, exc_returned.ts ".
			"FROM exc_mem_ass_status, exc_returned ".
			"WHERE exc_mem_ass_status.status_time < exc_returned.ts ".
			"AND NOT exc_mem_ass_status.status_time IS NULL ".
			"AND exc_returned.ass_id = exc_mem_ass_status.ass_id ".
			"AND exc_returned.user_id = exc_mem_ass_status.usr_id ".
			"AND exc_returned.ass_id=".$ilDB->quote($ass_id, "integer")." AND exc_returned.user_id=".
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
	 * Check how much files have been uploaded by the learner
	 * after the last download of the tutor.
	 */
	static function lookupNewFiles($ass_id, $member_id)
	{
  		global $ilDB, $ilUser;

  		$q = "SELECT exc_returned.returned_id AS id ".
			"FROM exc_usr_tutor, exc_returned ".
			"WHERE exc_returned.ass_id = exc_usr_tutor.ass_id ".
			" AND exc_returned.user_id = exc_usr_tutor.usr_id ".
			" AND exc_returned.ass_id = ".$ilDB->quote($ass_id, "integer").
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
	 * get member list data
	 */
	function getMemberListData($a_exc_id, $a_ass_id)
	{
		global $ilDB;

		$mem = array();
		
		// first get list of members from member table
		$set = $ilDB->query("SELECT * FROM exc_members ".
			"WHERE obj_id = ".$ilDB->quote($a_exc_id, "integer"));
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (ilObject::_exists($rec["usr_id"]) &&
				(ilObject::_lookupType($rec["usr_id"]) == "usr"))
			{
				$name = ilObjUser::_lookupName($rec["usr_id"]);
				$login = ilObjUser::_lookupLogin($rec["usr_id"]);
				$mem[$rec["usr_id"]] =
					array(
					"name" => $name["lastname"].", ".$name["firstname"],
					"login" => $login,
					"usr_id" => $rec["usr_id"],
					"lastname" => $name["lastname"],
					"firstname" => $name["firstname"]
					);
			}
		}

		$q = "SELECT * FROM exc_mem_ass_status ".
			"WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer");
		$set = $ilDB->query($q);
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (isset($mem[$rec["usr_id"]]))
			{
				$mem[$rec["usr_id"]]["sent_time"] = $rec["sent_time"];
				$mem[$rec["usr_id"]]["submission"] = ilExAssignment::getLastSubmission($a_ass_id, $rec["usr_id"]);
				$mem[$rec["usr_id"]]["status_time"] = $rec["status_time"];
				$mem[$rec["usr_id"]]["feedback_time"] = $rec["feedback_time"];
				$mem[$rec["usr_id"]]["notice"] = $rec["notice"];
				$mem[$rec["usr_id"]]["status"] = $rec["status"];
			}
		}
		return $mem;
	}
	
	/**
	 * Create member status record for a new participant for all assignments
	 */
	static function createNewUserRecords($a_user_id, $a_exc_id)
	{
		global $ilDB;
		
		$ass_data = ilExAssignment::getAssignmentDataOfExercise($a_exc_id);
		foreach ($ass_data as $ass)
		{
//echo "-".$ass["id"]."-".$a_user_id."-";
			$ilDB->replace("exc_mem_ass_status", array(
				"ass_id" => array("integer", $ass["id"]),
				"usr_id" => array("integer", $a_user_id)
				), array(
				"status" => array("text", "notgraded")
				));
		}
	}
	
	/**
	 * Create member status record for a new assignment for all participants
	 */
	static function createNewAssignmentRecords($a_ass_id, $a_exc)
	{
		global $ilDB;
		
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		$exmem = new ilExerciseMembers($a_exc);
		$mems = $exmem->getMembers();

		foreach ($mems as $mem)
		{
			$ilDB->replace("exc_mem_ass_status", array(
				"ass_id" => array("integer", $a_ass_id),
				"usr_id" => array("integer", $mem)
				), array(
				"status" => array("text", "notgraded")
				));
		}
	}

	/**
	 * Upload assignment files
	 * (from creation form)
	 */
	function uploadAssignmentFiles($a_files)
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$storage->create();
		$storage->uploadAssignmentFiles($a_files);
	}
	
}

?>
