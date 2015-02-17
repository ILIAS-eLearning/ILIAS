<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(dirname(__FILE__)."/../../../../Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilTrainingProgrammeProgress.
 *
 * Represents the progress of a user for one program assignment on one node of the
 * program. 
 *
 * The user has one progress per assignment and program node in the subtree of the
 * assigned program.
 * 
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilTrainingProgrammeProgress extends ActiveRecord {
	
	// The progress of a user on a program node can have different status that 
	// determine how the node is taken into account for calculation of the learning
	// progress.
	
	// User needs to be successfull in the node, but currently isn't.
	const STATUS_PROGRESS = 1;
	// User has completed the node successfully according to the program nodes
	// mode.
	const STATUS_COMPLETED = 2;
	// User was marked as successfull in the node without actually having
	// successfully completed the program node according to his mode.
	const STATUS_ACCREDITED = 3;
	// The user does not need to be successfull in this node.
	const STATUS_NOT_RELEVANT = 4;

	static $STATUS = array( STATUS_PROGRESS
						  , STATUS_COMPLETED
						  , STATUS_ACCREDITED
						  , STATUS_NOT_RELEVANT
						  );  

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return "prg_usr_progress";
	}

	/**
	 * The id of the assignment this progress belongs to.
	 *
	 * @var int 
	 * 
	 * @con_is_primary  true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $assignment_id;

	/**
	 * The id of the program node this progress belongs to.
	 *
	 * @var int 
	 * 
	 * @con_is_primary  true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $prg_id;

	/**
	 * The id of the user this progress belongs to.
	 * 
	 * @var int 
	 * 
	 * @con_is_primary  true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */

	protected $usr_id;
	/**
	 * Amount of points the user needs to achieve in the subnodes to be successfull
	 * on this node. Also the amount of points a user gets by being successfull on this
	 * node.
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  true 
	 */
	protected $points;

	/**
	 * Amount of points the user currently has in the subnodes of this node.
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  true 
	 */
	protected $points_cur;
 
	/**
	 * The status this progress is in.
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      1
	 * @con_is_notnull  true 
	 */
	protected $status;

	/**
	 * The id of the object, that lead to the successfull completion of this node.
	 * This is either a user when status is accreditted, a course object if the mode
	 * of the program node is lp_completed and the node is completed. Its null 
	 * otherwise.
	 *
	 * @var int
	 *
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  false 
	 */
	protected $completion_by;
	

	/**
	 * The timestamp of the moment this progress was created or updated the
	 * last time.
	 *
	 * @var int
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   timestamp 
	 * @con_is_notnull  true
	 */
	protected $last_change;

	/**
	 * Id of user who did the assignment that lead to creation of this progress
	 * or the update of the progress the last time.
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  true 
	 */
	protected $last_change_by;
	
	
	/**
	 * Create a new progress object for a given program node and assignment.
	 *
	 * @return ilTrainingProgrammeProgress
	 */
	static public function createFor( ilTrainingProgramme $a_prg
								    , ilTrainingProgrammeAssignment $a_ass) {
	}
	
	/**
	 * Get the assignment, this progress belongs to.
	 *
	 * @return ilTrainingProgrammeAssignment.
	 */
	public function getAssignmentId() {
		return $this->assignment_id;
	}
	
	protected function setAssignmentId($a_id) {
		$this->assignment_id = $a_id;
		return $this;
	}
	
	/**
	 * Get the id of the program node this progress belongs to.
	 *
	 * @return int
	 */
	public function getNodeId() {
		return $this->prg_id;
	}
	
	protected function setNodeId($a_id) {
		$this->prg_id = $a_id;
		return $this;
	}
	
	/**
	 * Get the id of the user this progress is for.
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->usr_id;
	}
	
	protected function setUserId($a_id) {
		$this->usr_id = $a_id;
		return $this;
	}
	
	/**
	 * Get the amount of points the user needs to achieve on the subnodes of this
	 * node. Also the amount of points, this node yields for the progress on the
	 * nodes above.
	 *
	 * @return int
	 */
	public function getAmountOfPoints() {
		return $this->points;
	}
	
	/**
	 * Get the amount of points the user needs to achieve on the subnodes of this
	 * node. Also the amount of points, this node yields for the progress on the
	 * nodes above.
	 *
	 * Throws when amount of points is smaller then zero.
	 *
	 * @throws ilException
	 * @return $this
	 */
	public function setAmountOfPoints($a_points) {
		if (!is_numeric($a_points) || $a_points < 0) {
			throw new ilException("ilTrainingProgrammeProgress::setAmountOfPoints: "
								 ."Expected a number > 0 as argument.");
		}
		
		$this->points = (int)$a_points;
		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Get the amount of points the user currently has achieved on the node.
	 *
	 * @return int
	 */
	public function getCurrentAmountOfPoints() {
		return $this->points_cur;
	}
	
	/**
	 * Set the amount of points the user currently has achieved on this node.
	 *
	 * Throw when amount of points is smaller then zero.
	 *
	 * @throws ilException
	 * @return $this
	 */
	public function setCurrentAmountOfPoints($a_points) {
		if (!is_numeric($a_points) || $a_points < 0) {
			throw new ilException("ilTrainingProgrammeProgress::setCurrentAmountOfPoints: "
								 ."Expected a number > 0 as argument.");
		}
		
		$this->points_cur = (int)$a_points;
		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Get the status the user has on this node.
	 *
	 * @return int - one of ilTrainingProgramme::STATUS_*
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Set the status of this node.
	 *
	 * Throws when status is none of ilTrainingProgramme::STATUS_*. Throws when
	 * completion_by is set and status is STATUS_PROGRESS.
	 * 
	 * @throws ilException
	 * @param  $a_status int - one of ilTrainingProgramme::STATUS_*
	 * @return $this
	 */
	public function setStatus($a_status) {
		$a_status = (int)$a_status;
		if (!in_array($a_status, self::$STATUS)) {
			throw new ilException("ilTrainingProgrammeProgress::setStatus: No status: "
								 ."'$a_status'");
		}
		$this->status = $a_status;
		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Set the completion_by field.
	 *
	 * @param $a_id int | null
	 * @return $this
	 */
	public function setCompletionBy($a_id) {
		if ($a_id !== null) {
			$a_id = (int)$a_id;
		}
		$this->completion_by = $a_id;
		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Get the id of object or user that lead to the successfull completion
	 * of this node.
	 *
	 * @return int
	 */
	public function getCompletionBy() {
		return $this->completion_by;
	}
	/**
	 * Get the id of the user who did the last change on this assignment.
	 * 
	 * @return int
	 */	
	public function getLastChangeBy() {
		return $this->last_change_by;
	}
	
	/**
	 * Set the id of the user who did the last change on this progress.
	 * 
	 * Throws when $a_usr_id is not the id of a user.
	 * 
	 * @throws ilException
	 * @return $this
	 */
	public function setLastChangeBy($a_usr_id) {
		if (ilObject::_lookupType($a_usr_id) != "usr") {
			throw new ilException("ilTrainingProgrammeProgress::setLastChangeBy: '$a_usr_id' "
								 ."is no id of a user.");
		}
		$this->last_change_by = $a_usr_id;
		return $this;
	}
	
	/**
	 * Get the timestamp of the last change on this progress.
	 *
	 * @return ilDateTime
	 */
	public function getLastChange() {
		return new ilDateTime($this->last_change, IL_CAL_DATETIME);
	}

	/**
	 * Update the last change timestamp to the current time.
	 *
	 * @return $this
	 */
	public function updateLastChange() {
		$this->setLastChange(new ilDateTime(ilUtil::now(), IL_CAL_DATETIME)); 
		return $this;
	}

	/**
	 * Set the last change timestamp to the given time.
	 * 
	 * Throws when given time is smaller then current timestamp since that is 
	 * logically impossible.
	 * 
	 * @throws ilException
	 * @return $this
	 */
	public function setLastChange(ilDateTime $a_timestamp) {
		if (ilDateTime::_before($a_timestamp, $this->getLastChange())) {
			throw new ilException("ilTrainingProgrammeProgress::setLastChange: Given "
								 ."timestamp is before current timestamp. That "
								 ."is logically impossible.");
		}
		
		$this->last_change = $a_timestamp->get(IL_CAL_DATETIME);
		return $this;
	}
}

?>
