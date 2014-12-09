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
	const TYPE_UPLOAD_TEAM = 4;
	const TYPE_TEXT = 5;
	
	const TEAM_LOG_CREATE_TEAM = 1;
	const TEAM_LOG_ADD_MEMBER = 2;
	const TEAM_LOG_REMOVE_MEMBER = 3;
	const TEAM_LOG_ADD_FILE = 4;
	const TEAM_LOG_REMOVE_FILE = 5;
	
	const FEEDBACK_DATE_DEADLINE = 1;
	const FEEDBACK_DATE_SUBMISSION = 2;
	
	protected $id;
	protected $exc_id;
	protected $type;
	protected $start_time;
	protected $deadline;
	protected $instruction;
	protected $title;
	protected $mandatory;
	protected $order_nr;
	protected $peer;
	protected $peer_min;
	protected $peer_dl;
	protected $peer_file;
	protected $peer_personal;
	protected $feedback_file;
	protected $feedback_cron;
	protected $feedback_date;
	
	/**
	 * Constructor
	 */
	function __construct($a_id = 0)
	{
		$this->setType(self::TYPE_UPLOAD);
		$this->setFeedbackDate(self::FEEDBACK_DATE_DEADLINE);
		
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
			
			if($this->type == self::TYPE_UPLOAD_TEAM)
			{
				$this->setPeerReview(false);
			}
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
		if(in_array((int)$a_value, array(self::TYPE_UPLOAD, self::TYPE_BLOG, 
			self::TYPE_PORTFOLIO, self::TYPE_UPLOAD_TEAM, self::TYPE_TEXT)))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Toggle peer review
	 * 
	 * @param bool $a_value
	 */
	function setPeerReview($a_value)
	{
		$this->peer = (bool)$a_value;
	}
	
	/**
	 * Get peer review status
	 * 
	 * @return bool 
	 */
	function getPeerReview()
	{
		return (bool)$this->peer;
	}
	
	/**
	 * Set peer review minimum
	 * 
	 * @param int $a_value
	 */
	function setPeerReviewMin($a_value)
	{
		$this->peer_min = (int)$a_value;
	}
	
	/**
	 * Get peer review minimum
	 * 
	 * @return int 
	 */
	function getPeerReviewMin()
	{
		return (int)$this->peer_min;
	}
	
	/**
	 * Set peer review deadline (timestamp)
	 *
	 * @param	int		deadline (timestamp)
	 */
	function setPeerReviewDeadline($a_val)
	{
		$this->peer_dl = $a_val;
	}
	
	/**
	 * Get peer review deadline (timestamp)
	 *
	 * @return	int		deadline (timestamp)
	 */
	function getPeerReviewDeadline()
	{
		return $this->peer_dl;
	}
	
	/**
	 * Set peer review file upload
	 *
	 * @param	bool
	 */
	function setPeerReviewFileUpload($a_val)
	{
		$this->peer_file = (bool)$a_val;
	}
	
	/**
	 * Get peer review file upload status
	 *
	 * @return	bool
	 */
	function hasPeerReviewFileUpload()
	{
		return $this->peer_file;
	}
	
	/**
	 * Set peer review personalized
	 *
	 * @param	bool
	 */
	function setPeerReviewPersonalized($a_val)
	{
		$this->peer_personal = (bool)$a_val;
	}
	
	/**
	 * Get peer review personalized status
	 *
	 * @return	bool
	 */
	function hasPeerReviewPersonalized()
	{
		return $this->peer_personal;
	}
	
	/**
	 * Set (global) feedback file
	 * 
	 * @param string $a_value
	 */
	function setFeedbackFile($a_value)
	{
		$this->feedback_file = (string)$a_value;
	}
	
	/**
	 * Get (global) feedback file
	 * 
	 * @return int 
	 */
	function getFeedbackFile()
	{
		return (string)$this->feedback_file;
	}
	
	/**
	 * Toggle (global) feedback file cron
	 * 
	 * @param bool $a_value
	 */
	function setFeedbackCron($a_value)
	{
		$this->feedback_cron = (string)$a_value;
	}
	
	/**
	 * Get (global) feedback file cron status
	 * 
	 * @return int 
	 */
	function hasFeedbackCron()
	{
		return (bool)$this->feedback_cron;
	}
	
	/**
	 * Set (global) feedback file availability date
	 * 
	 * @param int $a_value
	 */
	function setFeedbackDate($a_value)
	{
		$this->feedback_date = (int)$a_value;
	}
	
	/**
	 * Get (global) feedback file availability date
	 * 
	 * @return int 
	 */
	function getFeedbackDate()
	{
		return (int)$this->feedback_date;
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
			$this->setPeerReview($rec["peer"]);
			$this->setPeerReviewMin($rec["peer_min"]);
			$this->setPeerReviewDeadline($rec["peer_dl"]);
			$this->setPeerReviewFileUpload($rec["peer_file"]);
			$this->setPeerReviewPersonalized($rec["peer_prsl"]);
			$this->setFeedbackFile($rec["fb_file"]);
			$this->setFeedbackDate($rec["fb_date"]);
			$this->setFeedbackCron($rec["fb_cron"]);
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
			"type" => array("integer", $this->getType()),
			"peer" => array("integer", $this->getPeerReview()),
			"peer_min" => array("integer", $this->getPeerReviewMin()),
			"peer_dl" => array("integer", $this->getPeerReviewDeadline()),
			"peer_file" => array("integer", $this->hasPeerReviewFileUpload()),
			"peer_prsl" => array("integer", $this->hasPeerReviewPersonalized()),
			"fb_file" => array("text", $this->getFeedbackFile()),
			"fb_date" => array("integer", $this->getFeedbackDate()),
			"fb_cron" => array("integer", $this->hasFeedbackCron()))
			);
		$this->setId($next_id);
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		ilExAssignment::createNewAssignmentRecords($next_id, $exc);
		
		$this->handleCalendarEntries("create");
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
			"type" => array("integer", $this->getType()),
			"peer" => array("integer", $this->getPeerReview()),
			"peer_min" => array("integer", $this->getPeerReviewMin()),
			"peer_dl" => array("integer", $this->getPeerReviewDeadline()),
			"peer_file" => array("integer", $this->hasPeerReviewFileUpload()),
			"peer_prsl" => array("integer", $this->hasPeerReviewPersonalized()),
			"fb_file" => array("text", $this->getFeedbackFile()),
			"fb_date" => array("integer", $this->getFeedbackDate()),
			"fb_cron" => array("integer", $this->hasFeedbackCron())
			),
			array(
			"id" => array("integer", $this->getId()),
			));
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		
		$this->handleCalendarEntries("update");
	}
	
	/**
	 * Delete assignment
	 */
	function delete()
	{
		global $ilDB;
		
		$this->deleteFeedbackFile();
		
		$ilDB->manipulate("DELETE FROM exc_assignment WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		
		$this->handleCalendarEntries("delete");
	}
	
	function deleteFeedbackFile()
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$path = $storage->getGlobalFeedbackPath();
		ilUtil::delDir($path);				
	}	
	
	function handleFeedbackFileUpload(array $a_file)
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$path = $storage->getGlobalFeedbackPath();
		ilUtil::delDir($path, true);
		if(@move_uploaded_file($a_file["tmp_name"], $path."/".$a_file["name"]))
		{
			$this->setFeedbackFile($a_file["name"]);		
			return true;
		}
		return false;
	}
	
	function getFeedbackFilePath()
	{
		$file = $this->getFeedbackFile();
		if($file)
		{
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
			$path = $storage->getGlobalFeedbackPath();
			return $path."/".$file;
		}
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
				"type" => $rec["type"],
				"peer" => $rec["peer"],
				"peer_min" => $rec["peer_min"],
				"peer_dl" => $rec["peer_dl"],
				"peer_file" => $rec["peer_file"],
				"peer_prsl" => $rec["peer_prsl"],
				"fb_file" => $rec["fb_file"],
				"fb_date" => $rec["fb_date"],
				"fb_cron" => $rec["fb_cron"],
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
			$new_ass->setPeerReview($d["peer"]);
			$new_ass->setPeerReviewMin($d["peer_min"]);
			$new_ass->setPeerReviewDeadline($d["peer_dl"]);
			$new_ass->setPeerReviewFileUpload($d["peer_file"]);
			$new_ass->setPeerReviewPersonalized($d["peer_prsl"]);
			$new_ass->setFeedbackFile($d["fb_file"]);
			$new_ass->setFeedbackDate($d["fb_date"]);
			$new_ass->setFeedbackCron($d["fb_cron"]);
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
		// #13741 - is only used for mark
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

		// #13741
		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET sent = %s, sent_time = %s ".
			" WHERE ass_id = %s AND usr_id = %s ",
			array("integer", "timestamp", "integer", "integer"),
			array((int) $a_status, ($a_status ? ilUtil::now() : null),
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
		
		// first upload => notification on submission?
		if($a_status &&
			!self::lookupStatusReturnedOfUser($a_ass_id, $a_user_id))
		{
			$set = $ilDB->query("SELECT fb_cron, fb_date, fb_file".
				" FROM exc_assignment".
				" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
			$row = $ilDB->fetchAssoc($set);
			if($row["fb_cron"] &&
				$row["fb_file"] &&
				$row["fb_date"] == self::FEEDBACK_DATE_SUBMISSION)
			{
				ilExAssignment::sendFeedbackNotifications($a_ass_id, $a_user_id);
			}
		}

		// #13741
		$ilDB->manipulateF("UPDATE exc_mem_ass_status".
			" SET returned = %s".
			" WHERE ass_id = %s AND usr_id = %s",
			array("integer", "integer", "integer"),
			array((int) $a_status, $a_ass_id, $a_user_id));
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

		// #13741
		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET feedback = %s, feedback_time = %s ".
			" WHERE ass_id = %s AND usr_id = %s",
			array("integer", "timestamp", "integer", "integer"),
			array((int) $a_status, ($a_status ? ilUtil::now() : null),
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
		
		$user_ids = self::getTeamMembersByAssignmentId($a_ass_id, $a_user_id);
		if(!$user_ids)
		{
			$user_ids = array($a_user_id);
		}	
		
		$result = $ilDB->query("SELECT returned_id FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND ".$ilDB->in("user_id", $user_ids, "", "integer"));
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
	function getDeliveredFiles($a_exc_id, $a_ass_id, $a_user_id, $a_filter_empty_filename = false)
	{
		global $ilDB;
		
		$user_ids = self::getTeamMembersByAssignmentId($a_ass_id, $a_user_id);
		if(!$user_ids)
		{
			$user_ids = array($a_user_id);
		}		

		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		
		$result = $ilDB->query("SELECT * FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND ".$ilDB->in("user_id", $user_ids, "", "integer"));
		
		$delivered_files = array();
		if ($ilDB->numRows($result))
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				if($a_filter_empty_filename && !$row["filename"])
				{
					continue;
				}
				$row["owner_id"] = $row["user_id"];
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
			$team_id = self::getTeamIdByAssignment($a_ass_id, $a_user_id);
			if($team_id)
			{
				// #11733
				$user_ids = self::getTeamMembersByAssignmentId($a_ass_id, $a_user_id);			
				if(!$user_ids)
				{
					return;
				}
			}
			else
			{
				$user_ids = array($a_user_id);
			}
		
			$result = $ilDB->query("SELECT * FROM exc_returned".
				" WHERE ".$ilDB->in("returned_id", $file_id_array, false, "integer").
				" AND ".$ilDB->in("user_id", $user_ids, "", "integer"));	
			
			if ($ilDB->numRows($result))
			{
				$result_array = array();
				while ($row = $ilDB->fetchAssoc($result))
				{
					$row["timestamp"] = $row["ts"];
					array_push($result_array, $row);
				}
				
				// delete the entries in the database
				$ilDB->manipulate("DELETE FROM exc_returned".
					" WHERE ".$ilDB->in("returned_id", $file_id_array, false, "integer").
					" AND ".$ilDB->in("user_id", $user_ids, "", "integer"));

				// delete the files
				foreach ($result_array as $key => $value)
				{
					if($value["filename"])
					{
						if($team_id)
						{
							ilExAssignment::writeTeamLog($team_id, 
								ilExAssignment::TEAM_LOG_REMOVE_FILE, $value["filetitle"]);
						}
						
						$filename = $fs->getAbsoluteSubmissionPath().
							"/".$value["user_id"]."/".basename($value["filename"]);
						unlink($filename);
					}
				}
			}
		}
	}
	
	/**
	 * Delete all delivered files of user
	 *
	 * @param int $a_exc_id excercise id
	 * @param int $a_user_id user id
	 */
	static function deleteAllDeliveredFilesOfUser($a_exc_id, $a_user_id)
	{
		global $ilDB;
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		
		$delete_ids = array();
		
		// get the files and...
		$set = $ilDB->query("SELECT * FROM exc_returned ".
			" WHERE obj_id = ".$ilDB->quote($a_exc_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ass = new self($rec["ass_id"]);
			if($ass->getType() == self::TYPE_UPLOAD_TEAM)
			{
				// switch upload to other team member
				$team = self::getTeamMembersByAssignmentId($ass->getId(), $a_user_id);
				if(sizeof($team) > 1)
				{
					$new_owner = array_pop($team);
					while($new_owner == $a_user_id && sizeof($team))
					{
						$new_owner = array_pop($team);
					}					
					
					$ilDB->manipulate("UPDATE exc_returned".
						" SET user_id = ".$ilDB->quote($new_owner, "integer").
						" WHERE returned_id = ".$ilDB->quote($rec["returned_id"], "integer")
						);	
					
					// no need to delete
					continue;
				}
			}
			
			$delete_ids[] = $rec["returned_id"];
									
			$fs = new ilFSStorageExercise($a_exc_id, $rec["ass_id"]);
			
			// ...delete files
			$filename = $fs->getAbsoluteSubmissionPath().
				"/".$a_user_id."/".basename($rec["filename"]);
			if (is_file($filename))
			{
				unlink($filename);
			}
		}
		
		// delete exc_returned records
		if($delete_ids)
		{
			$ilDB->manipulate("DELETE FROM exc_returned".
				" WHERE ".$ilDB->in("returned_id", $delete_ids, "", "integer"));
		}
		
		// delete il_exc_team records
		$ass_ids = array();
		foreach(self::getAssignmentDataOfExercise($a_exc_id) as $item)
		{							
			self::updateStatusOfUser($item["id"], $a_user_id, "notgraded"); // #14900
			
			$ass_ids[] = $item["id"];
		}		
		if($ass_ids)
		{
			$ilDB->manipulate($d = "DELETE FROM il_exc_team WHERE ".
				"user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ".$ilDB->in("ass_id", $ass_ids, "", "integer")
				);
		}
	}
	
	
	/**
	 * was: deliverReturnedFiles($a_member_id, $a_only_new = false)
	 */
	function deliverReturnedFiles($a_exc_id, $a_ass_id, $a_user_id, $a_only_new = false, $a_peer_review_mask_filename = false)
	{
		global $ilUser, $ilDB;
		
		// #11000 / #11785
		$is_team = true;
		$user_ids = self::getTeamMembersByAssignmentId($a_ass_id, $a_user_id);
		if(!$user_ids)
		{
			$is_team = false;
			$user_ids = array($a_user_id);			
		}		
		
		// get last download time
		$and_str = "";
		if ($a_only_new)
		{
			$q = "SELECT download_time FROM exc_usr_tutor WHERE ".
				" ass_id = ".$ilDB->quote($a_ass_id, "integer")." AND ".
				$ilDB->in("usr_id", $user_ids, "", "integer")." AND ".
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

		foreach($user_ids as $user_id)
		{
			ilExAssignment::updateTutorDownloadTime($a_exc_id, $a_ass_id, $user_id);
		}
		
		if($a_peer_review_mask_filename)
		{
			// process peer review sequence id
			$peer_id = null;
			foreach($this->ass->getPeerReviewsByGiver($ilUser->getId()) as $idx => $item)
			{
				if($item["peer_id"] == $a_user_id)
				{
					$peer_id = $idx+1;
					break;
				}
			}
		}

		$query = "SELECT * FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND ".$ilDB->in("user_id", $user_ids, "", "integer").
			$and_str;
		
		$result = $ilDB->query($query);
		$count = $ilDB->numRows($result);
		if ($count == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			
			switch(self::lookupType($a_ass_id))
			{
				case self::TYPE_BLOG:
				case self::TYPE_PORTFOLIO:
					$row["filetitle"] = ilObjUser::_lookupName($row["user_id"]);
					$row["filetitle"] = ilObject::_lookupTitle($a_exc_id)." - ".
						self::lookupTitle($a_ass_id)." - ".
						$row["filetitle"]["firstname"]." ".
						$row["filetitle"]["lastname"]." (".
						$row["filetitle"]["login"].").zip";
					break;

				default:
					break;
			}		
			
			if($a_peer_review_mask_filename)
			{
				$suffix = array_pop(explode(".", $row["filetitle"]));
				$row["filetitle"] = self::lookupTitle($a_ass_id)."_peer".$peer_id.".".$suffix;							
			}
			
			ilExAssignment::downloadSingleFile($a_exc_id, $a_ass_id, $row["user_id"],
				$row["filename"], $row["filetitle"]);
		}
		else if ($count > 0)
		{
			$array_files = array();
			$seq = 0;
			while ($row = $ilDB->fetchAssoc($result))
			{				
				$src = basename($row["filename"]);				
				if($a_peer_review_mask_filename)
				{									
					$suffix = array_pop(explode(".", $src));
					$tgt = self::lookupTitle($a_ass_id)."_peer".$peer_id.
						"_".(++$seq).".".$suffix;				
					
					$array_files[$row["user_id"]][] = array($src, $tgt);
				}
				else
				{
					$array_files[$row["user_id"]][] = $src;
				}				
			}			
			ilExAssignment::downloadMultipleFiles($a_exc_id, $a_ass_id, $array_files, 
				($is_team ? null : $a_user_id), $is_team);
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
			//  #11785
			$is_team = true;
			$user_ids = self::getTeamMembersByAssignmentId($a_ass_id, $a_user_id);
			if(!$user_ids)
			{
				$is_team = false;
				$user_ids = array($a_user_id);
			}		
			
			$result = $ilDB->query("SELECT * FROM exc_returned WHERE ".
				$ilDB->in("returned_id", $array_file_id, false, "integer").
				" AND ".$ilDB->in("user_id", $user_ids, "", "integer"));
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
					// blog/portfolio submission
					if(is_numeric($array_found[0]["filetitle"]))
					{						
						$ass = new ilExAssignment($array_found[0]["ass_id"]);
						if($ass->getType() == ilExAssignment::TYPE_BLOG || 
							$ass->getType() == ilExAssignment::TYPE_PORTFOLIO)
						{
							$user_data = ilObjUser::_lookupName($array_found[0]["user_id"]);
							$array_found[0]["filetitle"] = ilObject::_lookupTitle($array_found[0]["obj_id"])." - ".
								$ass->getTitle()." - ".
								$user_data["firstname"]." ".
								$user_data["lastname"]." (".
								$user_data["login"].").zip";
						}
					}
					
					ilExAssignment::downloadSingleFile($a_exc_id, $a_ass_id, $array_found[0]["user_id"],
						$array_found[0]["filename"], $array_found[0]["filetitle"]);
				}
				else
				{
					$filenames = array();
					foreach ($array_found as $value)
					{
						$filenames[$value["user_id"]][] = basename($value["filename"]);
					}
					ilExAssignment::downloadMultipleFiles($a_exc_id, $a_ass_id, 
						$filenames, ($is_team ? null : $a_user_id), $is_team);
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
		$a_user_id, $a_multi_user = false)
	{				
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fs = new ilFSStorageExercise($a_exc_id, $a_ass_id);
		
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
		if ($a_user_id > 0 && !$a_multi_user)
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
		$parsed_files = $duplicates = array();
		foreach ($array_filenames as $user_id => $files)
		{
			$pathname = $fs->getAbsoluteSubmissionPath()."/".$user_id;

			foreach($files as $filename)
			{
				// peer review masked filenames, see deliverReturnedFiles()
				if(is_array($filename))
				{
					$newFilename = $filename[1];
					$filename = $filename[0];
				}
				else
				{
					// remove timestamp
					$newFilename = trim($filename);
					$pos = strpos($newFilename , "_");
					if ($pos !== false)
					{				
						$newFilename = substr($newFilename, $pos + 1);
					}
					// #11070
					$chkName = strtolower($newFilename);
					if(array_key_exists($chkName, $duplicates))
					{
						$suffix = strrpos($newFilename, ".");						
						$newFilename = substr($newFilename, 0, $suffix).
							" (".(++$duplicates[$chkName]).")".
							substr($newFilename, $suffix);
					}
					else
					{
						$duplicates[$chkName] = 1;
					}
				}
				$newFilename = $tmpdir.DIRECTORY_SEPARATOR.$deliverFilename.DIRECTORY_SEPARATOR.$newFilename;
				// copy to temporal directory
				$oldFilename =  $pathname.DIRECTORY_SEPARATOR.$filename;
				if (!copy ($oldFilename, $newFilename))
				{
					echo 'Could not copy '.$oldFilename.' to '.$newFilename;
				}
				touch($newFilename, filectime($oldFilename));
				$parsed_files[] =  ilUtil::escapeShellArg($deliverFilename.DIRECTORY_SEPARATOR.basename($newFilename)); 
			}
		}				
		
		chdir($tmpdir);
		$zipcmd = $zip." ".ilUtil::escapeShellArg($tmpzipfile)." ".join($parsed_files, " ");

		exec($zipcmd);
		ilUtil::delDir($tmpdir);
		
		chdir($cdir);
		ilUtil::deliverFile($tmpzipfile, $orgDeliverFilename.".zip", "", false, true);
		exit;
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
		
		$ass_type = self::lookupType($a_ass_id);

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
			//$directory = ilUtil::getASCIIFilename(trim($userName["lastname"])."_".trim($userName["firstname"]));
			$directory = ilUtil::getASCIIFilename(trim($userName["lastname"])."_".
				trim($userName["firstname"])."_".trim($userName["login"])."_".$userName["user_id"]);
			/*if (array_key_exists($directory, $cache))
			{
				// first try is to append the login;
				$directory = ilUtil::getASCIIFilename($directory."_".trim(ilObjUser::_lookupLogin($id)));
				if (array_key_exists($directory, $cache)) {
					// second and secure: append the user id as well.
					$directory .= "_".$id;
				}
			}*/

			$cache[$directory] = $directory;
			ilUtil::makeDir ($directory);
			$sourcefiles = scandir($sourcedir);
			$duplicates = array();
			foreach ($sourcefiles as $sourcefile) {
				if ($sourcefile == "." || $sourcefile == "..")
				{
					continue;
				}
			
				$targetfile = trim(basename($sourcefile));
				$pos = strpos($targetfile, "_");
				if ($pos !== false)
				{						
					$targetfile= substr($targetfile, $pos + 1);
				}
				
				// #14536 
				if(array_key_exists($targetfile, $duplicates))
				{
					$suffix = strrpos($targetfile, ".");						
					$targetfile = substr($targetfile, 0, $suffix).
						" (".(++$duplicates[$targetfile]).")".
						substr($targetfile, $suffix);				
				}
				else
				{
					$duplicates[$targetfile] = 1;
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
					
					// blogs and portfolios are stored as zip and have to be unzipped
					if($ass_type == ilExAssignment::TYPE_PORTFOLIO || 
						$ass_type == ilExAssignment::TYPE_BLOG)
					{
						ilUtil::unzip($targetfile);
						unlink($targetfile);
					}					
				}

			}
		}
		
		$tmpfile = ilUtil::ilTempnam();
		$tmpzipfile = $tmpfile . ".zip";
		// Safe mode fix
		$zipcmd = $zip." -r ".ilUtil::escapeShellArg($tmpzipfile)." .";
		exec($zipcmd);
		ilUtil::delDir($tmpdir);

		$assTitle = ilExAssignment::lookupTitle($a_ass_id)."_".$a_ass_id;
		chdir($cdir);
		ilUtil::deliverFile($tmpzipfile, (strlen($assTitle) == 0
			? strtolower($lng->txt("exc_assignment"))
			: $assTitle). ".zip", "", false, true);
	}

	/**
	 * was: setNoticeForMember($a_member_id,$a_notice)
	 */
	function updateNoticeForUser($a_ass_id, $a_user_id, $a_notice)
	{
		global $ilDB;
		
		// #12181 / #13741
		$ilDB->manipulate("UPDATE exc_mem_ass_status".
			" SET notice = ".$ilDB->quote($a_notice, "text").		
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
			" AND ".$ilDB->equalsNot("notice", $a_notice, "text", true));
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
		
		// team upload?
		$user_ids = self::getTeamMembersByAssignmentId($a_ass_id, $a_user_id);
		if(!$user_ids)
		{
			$user_ids = array($a_user_id);
		}
		
		$ilDB->setLimit(1);

		$q = "SELECT obj_id,user_id,ts FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND ".$ilDB->in("user_id", $user_ids, "", "integer").
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
		
		// team upload?
		$user_ids = self::getTeamMembersByAssignmentId($ass_id, $member_id);
		if(!$user_ids)
		{
			$user_ids = array($member_id);
		}

  		$q="SELECT exc_mem_ass_status.status_time, exc_returned.ts ".
			"FROM exc_mem_ass_status, exc_returned ".
			"WHERE exc_mem_ass_status.status_time < exc_returned.ts ".
			"AND NOT exc_mem_ass_status.status_time IS NULL ".
			"AND exc_returned.ass_id = exc_mem_ass_status.ass_id ".
			"AND exc_returned.user_id = exc_mem_ass_status.usr_id ".
			"AND exc_returned.ass_id=".$ilDB->quote($ass_id, "integer").
			" AND ".$ilDB->in("exc_returned.user_id", $user_ids, "", "integer");

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
		
		// team upload?
		$user_ids = self::getTeamMembersByAssignmentId($ass_id, $member_id);
		if(!$user_ids)
		{
			$user_ids = array($member_id);
		}

  		$q = "SELECT exc_returned.returned_id AS id ".
			"FROM exc_usr_tutor, exc_returned ".
			"WHERE exc_returned.ass_id = exc_usr_tutor.ass_id ".
			" AND exc_returned.user_id = exc_usr_tutor.usr_id ".
			" AND exc_returned.ass_id = ".$ilDB->quote($ass_id, "integer").
			" AND ".$ilDB->in("exc_returned.user_id", $user_ids, "", "integer").
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
	
	//
	// TEAM UPLOAD
	// 
	
	/**
	 * Get team id for member id
	 * 
	 * team will be created if no team yet
	 * 
	 * @param int $a_user_id
	 * @param bool $a_create_on_demand
	 * @return int 
	 */
	function getTeamId($a_user_id, $a_create_on_demand = false)
	{
		global $ilDB;
		
		$sql = "SELECT id FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($sql);
		$row = $ilDB->fetchAssoc($set);
		$id = $row["id"];
		
		if(!$id && $a_create_on_demand)
		{
			$id = $ilDB->nextId("il_exc_team");
			
			$fields = array("id" => array("integer", $id),
				"ass_id" => array("integer", $this->getId()),
				"user_id" => array("integer", $a_user_id));			
			$ilDB->insert("il_exc_team", $fields);		
			
			self::writeTeamLog($id, self::TEAM_LOG_CREATE_TEAM);						
			self::writeTeamLog($id, self::TEAM_LOG_ADD_MEMBER, 
				ilObjUser::_lookupFullname($a_user_id));
		}
		
		return $id;
	}
	
	/**
	 * Get members of assignment team
	 * 
	 * @param int $a_team_id 
	 * @return array
	 */
	function getTeamMembers($a_team_id)
	{
		global $ilDB;
		
		$ids = array();
		
		$sql = "SELECT user_id".
			" FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer").
			" AND id = ".$ilDB->quote($a_team_id, "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$ids[] = $row["user_id"];
		}
		
		return $ids;
	}
	
	/**
	 * Get members for all teams of assignment
	 * 
	 * @return array 
	 */
	function getMembersOfAllTeams()
	{
		global $ilDB;
		
		$ids = array();
		
		$sql = "SELECT user_id".
			" FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$ids[] = $row["user_id"];
		}
		
		return $ids;
	}
	
	/**
	 * Add new member to team
	 * 
	 * @param int $a_team_id
	 * @param int $a_user_id 
	 * @param int $a_exc_ref_id 
	 */
	function addTeamMember($a_team_id, $a_user_id, $a_exc_ref_id = null)
	{
		global $ilDB;
		
		$members = $this->getTeamMembers($a_team_id);
		if(!in_array($a_user_id, $members))
		{
			$fields = array("id" => array("integer", $a_team_id),
				"ass_id" => array("integer", $this->getId()),
				"user_id" => array("integer", $a_user_id));			
			$ilDB->insert("il_exc_team", $fields);		
			
			if($a_exc_ref_id)
			{
				$this->sendNotification($a_exc_ref_id, $a_user_id, "add");
			}
			
			self::writeTeamLog($a_team_id, self::TEAM_LOG_ADD_MEMBER, 
				ilObjUser::_lookupFullname($a_user_id));
		}									
	}
	
	/**
	 * Remove member from team
	 * 
	 * @param int $a_team_id
	 * @param int $a_user_id 
	 * @param int $a_exc_ref_id 
	 */
	function removeTeamMember($a_team_id, $a_user_id, $a_exc_ref_id)
	{
		global $ilDB;
		
		$sql = "DELETE FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer").
			" AND id = ".$ilDB->quote($a_team_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");			
		$ilDB->manipulate($sql);		
	
		$this->sendNotification($a_exc_ref_id, $a_user_id, "rmv");
		
		self::writeTeamLog($a_team_id, self::TEAM_LOG_REMOVE_MEMBER, 
			ilObjUser::_lookupFullname($a_user_id));
	}
	
	/**
	 * Find team members by assignment and team member
	 * 
	 * @param int $a_ass_id
	 * @param int $a_user_id
	 * @return array 
	 */
	public static function getTeamMembersByAssignmentId($a_ass_id, $a_user_id)
	{
		global $ilDB;
		
		$ids = array();
		
		$team_id = self::getTeamIdByAssignment($a_ass_id, $a_user_id);			
		if($team_id)
		{
			$set = $ilDB->query("SELECT user_id".
				" FROM il_exc_team".
				" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
				" AND id = ". $ilDB->quote($team_id, "integer"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$ids[] = $row["user_id"];
			}	
		}		
		
		return $ids;
	}
	
	/**
	 * Find team by assignment
	 * 
	 * @param int $a_ass_id
	 * @param int $a_user_id
	 * @return int 
	 */
	public static function getTeamIdByAssignment($a_ass_id, $a_user_id)
	{
		global $ilDB;
		
		$result = $ilDB->query("SELECT type".
			" FROM exc_assignment".
			" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
		$type = $ilDB->fetchAssoc($result);
		
		if($type["type"] == self::TYPE_UPLOAD_TEAM)
		{			
			$set = $ilDB->query("SELECT id".
				" FROM il_exc_team".
				" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer"));
			$team_id = $ilDB->fetchAssoc($set);
			return $team_id["id"];
		}
	}
	
	/**
	 * Get team structure for assignment 
	 * 
	 * @param int $a_ass_id
	 * @return array 
	 */
	public static function getAssignmentTeamMap($a_ass_id)
	{
		global $ilDB;
		
		$map = array();
		
		$sql = "SELECT * FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$map[$row["user_id"]] = $row["id"];
		}
		
		return $map;
	}

	/**
	 * Add entry to team log
	 * 
	 * @param int $a_team_id
	 * @param int $a_action
	 * @param string $a_details 
	 */
	public static function writeTeamLog($a_team_id, $a_action, $a_details = null)
	{
		global $ilDB, $ilUser;
		
		$fields = array(
			"team_id" => array("integer", $a_team_id),
			"user_id" => array("integer", $ilUser->getId()),
			"action" => array("integer", $a_action),
			"details" => array("text", $a_details),
			"tstamp" => array("integer", time())
		);
		
		$ilDB->insert("il_exc_team_log", $fields);
	}
	
	/**
	 * Get all log entries for team
	 * 
	 * @param int $a_team_id
	 * @return array 
	 */
	public static function getTeamLog($a_team_id)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT * FROM il_exc_team_log".
			" WHERE team_id = ".$ilDB->quote($a_team_id, "integer").
			" ORDER BY tstamp DESC";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}					
	
	/**
	 * Send notification about team status
	 * 
	 * @param int $a_exc_ref_id
	 * @param int $a_user_id
	 * @param string $a_action
	 */
	public function sendNotification($a_exc_ref_id, $a_user_id, $a_action)
	{
		global $ilUser;
		
		// no need to notify current user
		if($ilUser->getId() == $a_user_id)
		{
			return;
		}		
				
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();
		$ntf->setLangModules(array("exc"));
		$ntf->setRefId($a_exc_ref_id);
		$ntf->setChangedByUserId($ilUser->getId());
		$ntf->setSubjectLangId('exc_team_notification_subject_'.$a_action);
		$ntf->setIntroductionLangId('exc_team_notification_body_'.$a_action);
		$ntf->addAdditionalInfo("exc_assignment", $this->getTitle());	
		$ntf->setGotoLangId('exc_team_notification_link');				
		$ntf->setReasonLangId('exc_team_notification_reason');				
		$ntf->sendMail(array($a_user_id));		
	}
	
	public static function getDownloadedFilesInfoForTableGUIS($a_parent_obj, $a_exercise_id, $a_ass_type, $a_ass_id, $a_user_id, $a_parent_cmd = null)
	{
		global $lng, $ilCtrl;
		
		$result = array();
		$result["files"]["count"] = "---";
		
		$ilCtrl->setParameter($a_parent_obj, "ass_id", $a_ass_id);
		
		// submission:
		// see if files have been resubmmited after solved
		$last_sub =	self::getLastSubmission($a_ass_id, $a_user_id);
		if ($last_sub)
		{
			$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
		}
		else
		{
			$last_sub = "---";
		}
		/* #13741 - status_time has been reduced to grading (mark/status)
		if (ilExAssignment::lookupUpdatedSubmission($a_ass_id, $a_user_id) == 1) 
		{
			$last_sub = "<b>".$last_sub."</b>";
		}		 
		*/		
		$result["last_submission"]["txt"] = $lng->txt("exc_last_submission");
		$result["last_submission"]["value"] = $last_sub;
		
		// assignment type specific
		switch($a_ass_type)
		{			
			case ilExAssignment::TYPE_UPLOAD_TEAM:
				// data is merged by team - see above
				// fallthrough
				
			case ilExAssignment::TYPE_UPLOAD:
				// nr of submitted files
				$result["files"]["txt"] = $lng->txt("exc_files_returned");		
				$sub_cnt = count(ilExAssignment::getDeliveredFiles($a_exercise_id, $a_ass_id, $a_user_id));
				$new = ilExAssignment::lookupNewFiles($a_ass_id, $a_user_id);
				if (count($new) > 0)
				{
					$sub_cnt.= " ".sprintf($lng->txt("cnt_new"),count($new));
				}
				$result["files"]["count"] = $sub_cnt;

				// download command
				$ilCtrl->setParameter($a_parent_obj, "member_id", $a_user_id);
				
				if ($sub_cnt > 0)
				{
					$result["files"]["download_url"] = 
						$ilCtrl->getLinkTarget($a_parent_obj, "downloadReturned");
									
					if (count($new) <= 0)
					{
						$result["files"]["download_txt"] = $lng->txt("exc_download_files");
					}
					else
					{
						$result["files"]["download_txt"] = $lng->txt("exc_download_all");
					}
					
					// download new files only
					if (count($new) > 0)
					{
						$result["files"]["download_new_url"] = 
							$ilCtrl->getLinkTarget($a_parent_obj, "downloadNewReturned");
						
						$result["files"]["download_new_txt"] = $lng->txt("exc_download_new");						
					}
				}
				break;
				
			case ilExAssignment::TYPE_BLOG:				
				$result["files"]["txt"] =$lng->txt("exc_blog_returned");				
				$blogs = ilExAssignment::getDeliveredFiles($a_exercise_id, $a_ass_id, $a_user_id);
				if($blogs)
				{
					$blogs = array_pop($blogs);					
					if($blogs && substr($blogs["filename"], -1) != "/")
					{
						$result["files"]["count"] = 1;
						
						$ilCtrl->setParameter($a_parent_obj, "member_id", $a_user_id);
						$result["files"]["download_url"] = 
							$ilCtrl->getLinkTarget($a_parent_obj, "downloadReturned");
						$ilCtrl->setParameter($a_parent_obj, "member_id", "");
						
						$result["files"]["download_txt"] = $lng->txt("exc_download_files");						
					}
				}
				break;
				
			case ilExAssignment::TYPE_PORTFOLIO:
				$result["files"]["txt"] = $lng->txt("exc_portfolio_returned");				
				$portfolios = ilExAssignment::getDeliveredFiles($a_exercise_id, $a_ass_id, $a_user_id);
				if($portfolios)
				{
					$portfolios = array_pop($portfolios);									
					if($portfolios && substr($portfolios["filename"], -1) != "/")
					{	
						$result["files"]["count"] = 1;
						
						$ilCtrl->setParameter($a_parent_obj, "member_id", $a_user_id);
						$result["files"]["download_url"] = 
							$ilCtrl->getLinkTarget($a_parent_obj, "downloadReturned");		
						$ilCtrl->setParameter($a_parent_obj, "member_id", "");
						
						$result["files"]["download_txt"] = $lng->txt("exc_download_files");						
					}
				}
				break;
				
			case ilExAssignment::TYPE_TEXT:
				$result["files"]["txt"] = $lng->txt("exc_files_returned_text");
				$files = ilExAssignment::getDeliveredFiles($a_exercise_id, $a_ass_id, $a_user_id);
				if($files)
				{
					$result["files"]["count"] = 1;
					
					$files = array_shift($files);
					if(trim($files["atext"]))
					{					
						// #11397
						if($a_parent_cmd)
						{
							$ilCtrl->setParameter($a_parent_obj, "grd", (($a_parent_cmd == "members") ? 1 : 2));
						}
						$ilCtrl->setParameter($a_parent_obj, "member_id", $a_user_id);		
						$result["files"]["download_url"] =
							$ilCtrl->getLinkTarget($a_parent_obj, "showAssignmentText");												
						$ilCtrl->setParameter($a_parent_obj, "member_id", "");
						$ilCtrl->setParameter($a_parent_obj, "grd", "");
						
						$result["files"]["download_txt"] = $lng->txt("exc_text_assignment_show");						
					}
				}
				break;
		}
		
		return $result;
	}
	
	public function hasPeerReviewGroups()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT count(*) cnt".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer"));
		$cnt = $ilDB->fetchAssoc($set);
		return (bool)$cnt["cnt"];
	}
	
	protected function getValidPeerReviewUsers()
	{
		global $ilDB;
		
		$user_ids = array();
		
		// returned / assigned ?!
		$set = $ilDB->query("SELECT DISTINCT(user_id)".
			" FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$user_ids[] = $row["user_id"];
		}
		
		return $user_ids;
	}
	
	protected function initPeerReviews()
	{
		global $ilDB;
		
		// only if assignment is through
		if(!$this->getDeadline() || $this->getDeadline() > time())
		{
			return false;
		}
		
		if(!$this->hasPeerReviewGroups())
		{
			$user_ids = $this->getValidPeerReviewUsers();
			
			// forever alone
			if(sizeof($user_ids) < 2)
			{
				return false;
			}
			
			$rater_ids = $user_ids;
			$matrix = array();

			$max = min(sizeof($user_ids)-1, $this->getPeerReviewMin());			
			for($loop = 0; $loop < $max; $loop++)
			{				
				$run_ids = array_combine($user_ids, $user_ids);
				
				foreach($rater_ids as $rater_id)
				{
					$possible_peer_ids = $run_ids;
					
					// may not rate himself
					unset($possible_peer_ids[$rater_id]);
					
					// already has linked peers
					if(isset($matrix[$rater_id]))
					{
						$possible_peer_ids = array_diff($possible_peer_ids, $matrix[$rater_id]);
						if(sizeof($possible_peer_ids))
						{
							$peer_id = array_rand($possible_peer_ids);
							$matrix[$rater_id][] = $peer_id;	
						}
					}
					// 1st peer
					else
					{
						if(sizeof($possible_peer_ids)) // #14947
						{
							$peer_id = array_rand($possible_peer_ids);
							$matrix[$rater_id] = array($peer_id);	
						}
					}
					
					unset($run_ids[$peer_id]);
				}
			}	
			
			foreach($matrix as $rater_id => $peer_ids)
			{
				foreach($peer_ids as $peer_id)
				{
					$ilDB->manipulate("INSERT INTO exc_assignment_peer".
						" (ass_id, giver_id, peer_id)".
						" VALUES (".$ilDB->quote($this->getId(), "integer").
						", ".$ilDB->quote($rater_id, "integer").
						", ".$ilDB->quote($peer_id, "integer").")");					
				}
			}
			
		}
		return true;
	}
	
	public function resetPeerReviewFileUploads()
	{		
		if($this->hasPeerReviewFileUpload())
		{
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
			$storage->deletePeerReviewUploads();
		}
	}
	
	public function resetPeerReviews()
	{
		global $ilDB;
		
		if($this->hasPeerReviewGroups())
		{
			// ratings					
			foreach($this->getAllPeerReviews(false) as $peer_id => $reviews)
			{
				foreach($reviews as $giver_id => $review)
				{					
					ilRating::resetRatingForUserAndObject($this->getId(), "ass", 
						$peer_id, "peer", $giver_id);
				}
			}
			
			// files
			$this->resetPeerReviewFileUploads();
			
			// peer groups
			$ilDB->manipulate("DELETE FROM exc_assignment_peer".
				" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer"));						
		}
	}
	
	public function validatePeerReviewGroups()
	{
		if($this->hasPeerReviewGroups())
		{			
			include_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";
			$all_exc = ilExerciseMembers::_getMembers($this->getExerciseId());
			$all_valid = $this->getValidPeerReviewUsers(); // only returned
			
			$peer_ids = $invalid_peer_ids = $invalid_giver_ids = $all_reviews = array();
			foreach($this->getAllPeerReviews(false) as $peer_id => $reviews)
			{
				$peer_ids[] = $peer_id;
				
				if(!in_array($peer_id, $all_valid) ||
					!in_array($peer_id, $all_exc))
				{
					$invalid_peer_ids[] = $peer_id;
				}
				foreach($reviews as $giver_id => $review)
				{
					if(!in_array($giver_id, $all_valid) ||
						!in_array($peer_id, $all_exc))
					{
						$invalid_giver_ids[] = $giver_id;
					}
					else 
					{
						$valid = (trim($review[0]) || $review[1]);					
						$all_reviews[$peer_id][$giver_id] = $valid;						
					}
				}
			}			
			$invalid_giver_ids = array_unique($invalid_giver_ids);
			
			$missing_user_ids = array();
			foreach($all_valid as $user_id)
			{
				// a missing peer is also a missing giver
				if(!in_array($user_id, $peer_ids))
				{
					$missing_user_ids[] = $user_id;
				}
			}
			
			$not_returned_ids = array();
			foreach($all_exc as $user_id)
			{				
				if(!in_array($user_id, $all_valid))
				{
					$not_returned_ids[] = $user_id;
				}
			}
						
			return array(
				"invalid" => (sizeof($missing_user_ids) || 
					sizeof($invalid_peer_ids) || 
					sizeof($invalid_giver_ids)),
				"missing_user_ids" => $missing_user_ids, 
				"not_returned_ids" => $not_returned_ids,
				"invalid_peer_ids" => $invalid_peer_ids, 
				"invalid_giver_ids" => $invalid_giver_ids,
				"reviews" => $all_reviews);
		}
	}
	
	public function getPeerReviewsByGiver($a_user_id)
	{
		global $ilDB;
		
		$res = array();
		
		if($this->initPeerReviews())
		{			
			$set = $ilDB->query("SELECT *".
				" FROM exc_assignment_peer".
				" WHERE giver_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ass_id = ".$ilDB->quote($this->getId(), "integer").
				" ORDER BY peer_id");
			while($row = $ilDB->fetchAssoc($set))
			{
				$res[] = $row;
			}
		}				
		
		return $res;
	}
	
	protected static function validatePeerReview(array $a_data, $a_rating = null)
	{							
		$valid = false;		
		
		// comment
		if(trim($a_data["pcomment"]))
		{
			$valid = true;
		}
		
		// rating
		if(!$valid)
		{
			if($a_rating === null)
			{			
				include_once './Services/Rating/classes/class.ilRating.php';		
				$valid = (bool)round(ilRating::getRatingForUserAndObject($a_data["ass_id"], 
					"ass", $a_data["peer_id"], "peer", $a_data["giver_id"]));				
			}
			else if($a_rating)
			{
				$valid = true;
			}
		}

		// file(s) 
		if(!$valid) 
		{
			$ass = new self($a_data["ass_id"]);			
			$valid = (bool)sizeof($ass->getPeerUploadFiles($a_data["peer_id"], $a_data["giver_id"]));
		}
		
		return $valid;
	}
	
	public function getPeerReviewsByPeerId($a_user_id, $a_only_valid = false)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE peer_id = ".$ilDB->quote($a_user_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->getId(), "integer").
			" ORDER BY peer_id");
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!$a_only_valid || 
				self::validatePeerReview($row))
			{				
				$res[] = $row;
			}
		}						
		
		return $res;
	}
	
	public function getAllPeerReviews($a_validate = true)
	{
		global $ilDB;
		
		$res = array();

		include_once './Services/Rating/classes/class.ilRating.php';
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer").
			" ORDER BY peer_id");
		while($row = $ilDB->fetchAssoc($set))
		{
			$rating = round(ilRating::getRatingForUserAndObject($this->getId(), 
					"ass", $row["peer_id"], "peer", $row["giver_id"]));		
			
			if(!$a_validate ||
				self::validatePeerReview($row, $rating))
			{
				$res[$row["peer_id"]][$row["giver_id"]] = array($row["pcomment"], $rating);
			}
		}						
		
		return $res;		
	}
	
	public function hasPeerReviewAccess($a_peer_id)
	{
		global $ilDB, $ilUser;
		
		$set = $ilDB->query("SELECT ass_id".
			" FROM exc_assignment_peer".			
			" WHERE giver_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND peer_id = ".$ilDB->quote($a_peer_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->getId(), "integer"));
		$row = $ilDB->fetchAssoc($set);
		return (bool)$row["ass_id"];		
	}
	
	public function updatePeerReviewTimestamp($a_peer_id)
	{
		global $ilDB, $ilUser;
		
		$ilDB->manipulate("UPDATE exc_assignment_peer".
			" SET tstamp = ".$ilDB->quote(ilUtil::now(), "timestamp").
			" WHERE giver_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND peer_id = ".$ilDB->quote($a_peer_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->getId(), "integer"));
	}
	
	public function getPeerUploadFiles($a_peer_id, $a_giver_id)
	{
		if(!$this->hasPeerReviewFileUpload())
		{
			return array();
		}
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$path = $storage->getPeerReviewUploadPath($a_peer_id, $a_giver_id);			
		return glob($path."/*.*");			
	}
	
	public function updatePeerReviewComment($a_peer_id, $a_comment)
	{
		global $ilDB, $ilUser;
		
		$sql = "UPDATE exc_assignment_peer".
			" SET tstamp = ".$ilDB->quote(ilUtil::now(), "timestamp").
			",pcomment  = ".$ilDB->quote(trim($a_comment), "text").
			" WHERE giver_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND peer_id = ".$ilDB->quote($a_peer_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->getId(), "integer");
		
		$ilDB->manipulate($sql);
	}
	
	public static function countGivenFeedback($a_ass_id)
	{
		global $ilDB, $ilUser;
		
		$cnt = 0;
		
		include_once './Services/Rating/classes/class.ilRating.php';
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND giver_id = ".$ilDB->quote($ilUser->getId(), "integer"));			
		while($row = $ilDB->fetchAssoc($set))
		{
			if(self::validatePeerReview($row))
			{
				$cnt++;
			}			
		}
		
		return $cnt;
	}
	
	public static function getNumberOfMissingFeedbacks($a_ass_id, $a_min)
	{
		global $ilDB;
		
		// check if number of returned assignments is lower than assignment peer min
		$set = $ilDB->query("SELECT COUNT(DISTINCT(user_id)) cnt".
			" FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer"));
		$cnt = $ilDB->fetchAssoc($set);
		$cnt = (int)$cnt["cnt"];
		
		// forever alone
		if($cnt < 2)
		{
			return;
		}
				
		$a_min = min($cnt-1, $a_min);
				
		return max(0, $a_min-self::countGivenFeedback($a_ass_id));		
	}
	
	public static function getPendingFeedbackNotifications()
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT id,fb_file FROM exc_assignment".
			" WHERE fb_cron = ".$ilDB->quote(1, "integer").
			" AND fb_date = ".$ilDB->quote(self::FEEDBACK_DATE_DEADLINE, "integer").
			" AND time_stamp IS NOT NULL".
			" AND time_stamp > ".$ilDB->quote(0, "integer").			
			" AND time_stamp < ".$ilDB->quote(time(), "integer").
			" AND fb_cron_done = ".$ilDB->quote(0, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			if(trim($row["fb_file"]))
			{
				$res[] = $row["id"];			
			}
		}		
	
		return $res;
	}
	
	public function sendFeedbackNotifications($a_ass_id, $a_user_id = null)
	{
		global $ilDB;
		
		$ass = new self($a_ass_id);
		
		// valid assignment?
		if(!$ass->hasFeedbackCron() || !$ass->getFeedbackFile())
		{
			return false;
		}		
		
		if(!$a_user_id)
		{
			// already done?
			$set = $ilDB->query("SELECT fb_cron_done".
				" FROM exc_assignment".
				" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
			$row = $ilDB->fetchAssoc($set);
			if($row["fb_cron_done"])
			{
				return false;
			}
		}
		
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();
		$ntf->setLangModules(array("exc"));
		$ntf->setObjId($ass->getExerciseId());
		$ntf->setSubjectLangId("exc_feedback_notification_subject");
		$ntf->setIntroductionLangId("exc_feedback_notification_body");
		$ntf->addAdditionalInfo("exc_assignment", $ass->getTitle());
		$ntf->setGotoLangId("exc_feedback_notification_link");		
		$ntf->setReasonLangId("exc_feedback_notification_reason");	
		
		if(!$a_user_id)
		{
			include_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";
			$ntf->sendMail(ilExerciseMembers::_getMembers($ass->getExerciseId()));
						
			$ilDB->manipulate("UPDATE exc_assignment".
				" SET fb_cron_done = ".$ilDB->quote(1, "integer").
				" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
		}
		else
		{		
			$ntf->sendMail(array($a_user_id));
		}
		
		return true;		
	}
	
	////
	//// Multi-Feedback
	////

	/**
	 * Create member status record for a new assignment for all participants
	 */
	function sendMultiFeedbackStructureFile()
	{
		global $ilDB;
		
		
		// send and delete the zip file
		$deliverFilename = trim(str_replace(" ", "_", $this->getTitle()."_".$this->getId()));
		$deliverFilename = ilUtil::getASCIIFilename($deliverFilename);
		$deliverFilename = "multi_feedback_".$deliverFilename;

		$exc = new ilObjExercise($this->getExerciseId(), false);
		
		$cdir = getcwd();
		
		// create temporary directoy
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		$mfdir = $tmpdir."/".$deliverFilename;
		ilUtil::makeDir($mfdir);
		
		// create subfolders <lastname>_<firstname>_<id> for each participant
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		$exmem = new ilExerciseMembers($exc);
		$mems = $exmem->getMembers();

		foreach ($mems as $mem)
		{
			$name = ilObjUser::_lookupName($mem);
			$subdir = $name["lastname"]."_".$name["firstname"]."_".$name["login"]."_".$name["user_id"];
			$subdir = ilUtil::getASCIIFilename($subdir);
			ilUtil::makeDir($mfdir."/".$subdir);
		}
		
		// create the zip file
		chdir($tmpdir);
		$tmpzipfile = $tmpdir."/multi_feedback.zip";
		ilUtil::zip($tmpdir, $tmpzipfile, true);
		chdir($cdir);
		

		ilUtil::deliverFile($tmpzipfile, $deliverFilename.".zip", "", false, true);
	}
	
	/**
	 * Upload multi feedback file
	 *
	 * @param array 
	 * @return
	 */
	function uploadMultiFeedbackFile($a_file)
	{
		global $lng, $ilUser;
		
		include_once("./Modules/Exercise/exceptions/class.ilExerciseException.php");
		if (!is_file($a_file["tmp_name"]))
		{
			throw new ilExerciseException($lng->txt("exc_feedback_file_could_not_be_uploaded"));
		}
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());
		ilUtil::delDir($mfu, true);
		ilUtil::moveUploadedFile($a_file["tmp_name"], "multi_feedback.zip", $mfu."/"."multi_feedback.zip");
		ilUtil::unzip($mfu."/multi_feedback.zip", true);
		$subdirs = ilUtil::getDir($mfu);
		$subdir = "notfound";
		foreach ($subdirs as $s => $j)
		{
			if ($j["type"] == "dir" && substr($s, 0, 14) == "multi_feedback")
			{
				$subdir = $s;
			}
		}

		if (!is_dir($mfu."/".$subdir))
		{
			throw new ilExerciseException($lng->txt("exc_no_feedback_dir_found_in_zip"));
		}

		return true;
	}
	
	/**
	 * Get multi feedback files (of uploader)
	 *
	 * @param int $a_user_id user id of uploader
	 * @return array array of user files (keys: lastname, firstname, user_id, login, file)
	 */
	function getMultiFeedbackFiles($a_user_id = 0)
	{
		global $ilUser;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$mf_files = array();
		
		// get members
		$exc = new ilObjExercise($this->getExerciseId(), false);
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		$exmem = new ilExerciseMembers($exc);
		$mems = $exmem->getMembers();

		// read mf directory
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());

		// get subdir that starts with multi_feedback
		$subdirs = ilUtil::getDir($mfu);
		$subdir = "notfound";
		foreach ($subdirs as $s => $j)
		{
			if ($j["type"] == "dir" && substr($s, 0, 14) == "multi_feedback")
			{
				$subdir = $s;
			}
		}
		
		$items = ilUtil::getDir($mfu."/".$subdir);
		foreach ($items as $k => $i)
		{
			// check directory
			if ($i["type"] == "dir" && !in_array($k, array(".", "..")))
			{
				// check if valid member id is given
				$parts = explode("_", $i["entry"]);
				$user_id = (int) $parts[count($parts) - 1];
				if (in_array($user_id, $mems))
				{
					// read dir of user
					$name = ilObjUser::_lookupName($user_id);
					$files = ilUtil::getDir($mfu."/".$subdir."/".$k);
					foreach ($files as $k2 => $f)
					{
						// append files to array
						if ($f["type"] == "file" && substr($k2, 0, 1) != ".")
						{
							$mf_files[] = array(
								"lastname" => $name["lastname"],
								"firstname" => $name["firstname"],
								"login" => $name["login"],
								"user_id" => $name["user_id"],
								"full_path" => $mfu."/".$subdir."/".$k."/".$k2,
								"file" => $k2);
						}
					}
				}
			}
		}
		return $mf_files;
	}
	
	/**
	 * Clear multi feedback directory
	 *
	 * @param array 
	 * @return
	 */
	function clearMultiFeedbackDirectory()
	{
		global $lng, $ilUser;
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());
		ilUtil::delDir($mfu);
	}
	
	/**
	 * Save multi feedback files
	 *
	 * @param
	 * @return
	 */
	function saveMultiFeedbackFiles($a_files)
	{			
		$exc = new ilObjExercise($this->getExerciseId(), false);
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fstorage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$fstorage->create();
		
		$team_map = array();
		
		$mf_files = $this->getMultiFeedbackFiles();
		foreach ($mf_files as $f)
		{			
			$user_id = $f["user_id"];
			$file_path = $f["full_path"];				
			$file_name = $f["file"];
			
			// if checked in confirmation gui
			if ($a_files[$user_id][md5($file_name)] != "")
			{			
				// #14294 - team assignment
				if ($this->getType() == ilExAssignment::TYPE_UPLOAD_TEAM)
				{
					// just once for each user
					if (!array_key_exists($user_id, $team_map))
					{									
						$team_id = $this->getTeamId($user_id);
						$team_map[$user_id]["team_id"] = "t".$team_id;	

						$team_map[$user_id]["noti_rec_ids"] = array();
						foreach ($this->getTeamMembers($team_id) as $team_user_id)
						{				
							$team_map[$user_id]["noti_rec_ids"][] = $team_user_id;
						}		
					}

					$feedback_id = $team_map[$user_id]["team_id"];
					$noti_rec_ids = $team_map[$user_id]["noti_rec_ids"];
				}
				else
				{
					$feedback_id = $user_id;
					$noti_rec_ids = array($user_id);
				}			
				
				if ($feedback_id)
				{
					$fb_path = $fstorage->getFeedbackPath($feedback_id);
					$target = $fb_path."/".$file_name;
					if (is_file($target))
					{
						unlink($target);
					}
					// rename file
					rename($file_path, $target);
										
					if ($noti_rec_ids)
					{
						$exc->sendFeedbackFileNotification($file_name, $noti_rec_ids,
							(int) $this->getId());
					}
				}				
			}
		}
		
		$this->clearMultiFeedbackDirectory();
	}
	
	/**
	 * Handle calendar entries for deadline(s)
	 * 
	 * @param string $a_event
	 */
	protected function handleCalendarEntries($a_event)
	{		
		global $ilAppEventHandler;
		
		$dl_id = $this->getId()."0";
		$fbdl_id = $this->getId()."1";
		
		$context_ids = array($dl_id, $fbdl_id);		
		$apps = array();
		
		if($a_event != "delete")
		{										
			include_once "Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php";
			
			if($this->getDeadline())
			{					
				$app = new ilCalendarAppointmentTemplate($dl_id);
				$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$app->setSubtitle("cal_exc_deadline");
				$app->setTitle($this->getTitle());				
				$app->setFullday(false);
				$app->setStart(new ilDateTime($this->getDeadline(), IL_CAL_UNIX));			
				
				$apps[] = $app;
			}

			if($this->getPeerReview() &&
				$this->getPeerReviewDeadline())
			{
				$app = new ilCalendarAppointmentTemplate($fbdl_id);
				$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$app->setSubtitle("cal_exc_peer_review_deadline");
				$app->setTitle($this->getTitle());				
				$app->setFullday(false);
				$app->setStart(new ilDateTime($this->getPeerReviewDeadline(), IL_CAL_UNIX));
				
				$apps[] = $app;
			}		
			
		}			
				
		include_once "Modules/Exercise/classes/class.ilObjExercise.php";
		$exc = new ilObjExercise($this->getExerciseId(), false);
		
		$ilAppEventHandler->raise('Modules/Exercise',
			$a_event.'Assignment',
			array(
			'object' => $exc,
			'obj_id' => $exc->getId(),			
			'context_ids' => $context_ids,
			'appointments' => $apps));		
	}
	
	public static function getAdoptableTeamAssignments($a_exercise_id, $a_exclude_ass_id = null, $a_user_id = null)
	{
		$res = array();
		
		$data = ilExAssignment::getAssignmentDataOfExercise($a_exercise_id);
		foreach($data as $row)
		{
			if($a_exclude_ass_id && $row["id"] == $a_exclude_ass_id)
			{
				continue;
			}
			
			if($row["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$map = ilExAssignment::getAssignmentTeamMap($row["id"]);
				
				if($a_user_id && !array_key_exists($a_user_id, $map))
				{
					continue;
				}
				
				if(sizeof($map))
				{		
					$user_team = null;
					if($a_user_id)
					{
						$user_team_id = $map[$a_user_id];
						$user_team = array();
						foreach($map as $user_id => $team_id)
						{
							if($user_id != $a_user_id && 
								$user_team_id == $team_id)
							{
								$user_team[] = $user_id;
							}
						}							
					}
					
					if(!$a_user_id ||
						sizeof($user_team))
					{
						$res[$row["id"]] = array(
							"title" => $row["title"],
							"teams" => sizeof(array_flip($map)),
						);
						
						if($a_user_id)
						{
							$res[$row["id"]]["user_team"] = $user_team;
						}
					}					
				}
			}			
		}
		
		return ilUtil::sortArray($res, "title", "asc", false, true);
	}
	
	public function adoptTeams($a_source_ass_id, $a_user_id = null, $a_exc_ref_id = null)
	{
		$teams = array();
		
		$old_team = null;
		foreach(self::getAssignmentTeamMap($a_source_ass_id) as $user_id => $team_id)
		{			
			$teams[$team_id][] = $user_id;
						
			if($a_user_id && $user_id == $a_user_id)
			{
				$old_team = $team_id;
			}		
		}
		
		if($a_user_id)
		{
			// no existing team (in source) or user already in team (in current)
			if(!$old_team || $this->getTeamId($a_user_id))
			{
				return;
			}
		}
		
		$current_map = self::getAssignmentTeamMap($this->getId());
		
		foreach($teams as $team_id => $user_ids)
		{
			if(!$old_team || $team_id == $old_team)
			{
				// only not assigned users
				$missing = array();
				foreach($user_ids as $user_id)
				{
					if(!array_key_exists($user_id, $current_map))
					{
						$missing[] = $user_id;
					}
				}
				
				if(sizeof($missing))
				{
					// create new team
					$first = array_shift($missing);			
					$team_id = $this->getTeamId($first, true);		

					if($a_exc_ref_id)
					{	
						// getTeamId() does NOT send notification
						$this->sendNotification($a_exc_ref_id, $first, "add");
					}					

					foreach($missing as $user_id)
					{
						$this->addTeamMember($team_id, $user_id, $a_exc_ref_id);
					}		
				}
			}
		}
	}
}

?>
