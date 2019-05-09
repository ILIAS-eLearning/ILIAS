<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilStudyProgrammeProgress.
 *
 * Represents the progress of a user for one program assignment on one node of the
 * program. 
 *
 * The user has one progress per assignment and program node in the subtree of the
 * assigned program.
 * 
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Denis Klöpfer <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilStudyProgrammeProgress
{
	
	// The progress of a user on a program node can have different status that 
	// determine how the node is taken into account for calculation of the learning
	// progress.
	
	// User needs to be successful in the node, but currently isn't.
	const STATUS_IN_PROGRESS = 1;
	// User has completed the node successfully according to the program nodes
	// mode.
	const STATUS_COMPLETED = 2;
	// User was marked as successful in the node without actually having
	// successfully completed the program node according to his mode.
	const STATUS_ACCREDITED = 3;
	// The user does not need to be successful in this node.
	const STATUS_NOT_RELEVANT = 4;
	// The user does not need to be successful in this node.
	const STATUS_FAILED = 5;

	static $STATUS = array( self::STATUS_IN_PROGRESS
						  , self::STATUS_COMPLETED
						  , self::STATUS_ACCREDITED
						  , self::STATUS_NOT_RELEVANT
						  , self::STATUS_FAILED
						  );  

	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	const DATE_FORMAT = 'Y-m-d';

	/**
	 * The id of this progress.
	 *
	 * This is superfluous, since a progress is unique per (assignment_id, prg_id,
	 * user_id)-tuple, but ActiveRecords won't cooperate and wants one primary key
	 * only. I'm sad.
	 * We set a unique constraint on the three fields in the db update to get the
	 * desired guarantees by the database.
	 * 
	 * @var int
	 */
	protected $id;

	/**
	 * The id of the assignment this progress belongs to.
	 *
	 * @var int 
	 */
	protected $assignment_id;

	/**
	 * The id of the program node this progress belongs to.
	 *
	 * @var int 
	 */
	protected $prg_id;

	/**
	 * The id of the user this progress belongs to.
	 * 
	 * @var int 
	 */

	protected $usr_id;
	/**
	 * Amount of points the user needs to achieve in the subnodes to be successful
	 * on this node. Also the amount of points a user gets by being successful on this
	 * node.
	 *
	 * @var int 
	 */
	protected $points;

	/**
	 * Amount of points the user currently has in the subnodes of this node.
	 *
	 * @var int 
	 */
	protected $points_cur;
 
	/**
	 * The status this progress is in.
	 *
	 * @var int 
	 */
	protected $status;

	/**
	 * The id of the object, that lead to the successful completion of this node.
	 * This is either a user when status is accreditted, a course object if the mode
	 * of the program node is lp_completed and the node is completed. Its null 
	 * otherwise.
	 *
	 * @var int
	 */
	protected $completion_by;
	

	/**
	 * The timestamp of the moment this progress was created or updated the
	 * last time.
	 *
	 * @var int
	 */
	protected $last_change;

	/**
	 * Id of the user who did the last manual update of the progress
	 *
	 * @var int 
	 */
	protected $last_change_by;

	/**
	 * Date of asssignment
	 *
	 * @var \DateTime
	 */
	protected $assignment_date;

	/**
	 * Date of asssignment
	 *
	 * @var \DateTime
	 */
	protected $completion_date;

	/**
	 * Date until user has to finish
	 *
	 * @var \DateTime | null
	 */
	protected $deadline;

	/**
	 * Date until which this qualification is valid.
	 *
	 * @var \DateTime |null
	 */
	protected $vq_date;


	public function __construct(int $id)
	{
		$this->id = $id;
	}

	/**
	 * Get the id of the progress.
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get the assignment, this progress belongs to.
	 */
	public function getAssignmentId() : int
	{
		return $this->assignment_id;
	}
	public function setAssignmentId(int $a_id) : ilStudyProgrammeProgress
	{
		$this->assignment_id = $a_id;
		return $this;
	}
	/**
	 * Get the id of the program node this progress belongs to.
	 */
	public function getNodeId() : int
	{
		return $this->prg_id;
	}
	public function setNodeId(int $a_id) : ilStudyProgrammeProgress
	{
		$this->prg_id = $a_id;
		return $this;
	}
	/**
	 * Get the id of the user this progress is for.
	 */
	public function getUserId() : int
	{
		return $this->usr_id;
	}
	public function setUserId(int $a_id) : ilStudyProgrammeProgress
	{
		$this->usr_id = $a_id;
		return $this;
	}
	
	/**
	 * Get the amount of points the user needs to achieve on the subnodes of this
	 * node. Also the amount of points, this node yields for the progress on the
	 * nodes above.
	 */
	public function getAmountOfPoints() : int
	{
		return $this->points;
	}
	
	/**
	 * Get the amount of points the user needs to achieve on the subnodes of this
	 * node. Also the amount of points, this node yields for the progress on the
	 * nodes above.
	 *
	 * Throws when amount of points is smaller then zero.
	 */
	public function setAmountOfPoints(int $a_points) : ilStudyProgrammeProgress
	{
		if ($a_points < 0) {
			throw new ilException("ilStudyProgrammeProgress::setAmountOfPoints: "
								 ."Expected a number >= 0 as argument, got '$a_points'");
		}
		
		$this->points = $a_points;

		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Get the amount of points the user currently has achieved on the node.
	 */
	public function getCurrentAmountOfPoints() : int
	{
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
	public function setCurrentAmountOfPoints(int $a_points) : ilStudyProgrammeProgress
	{
		if ($a_points < 0) {
			throw new ilException("ilStudyProgrammeProgress::setCurrentAmountOfPoints: "
								 ."Expected a number >= 0 as argument, got '$a_points'.");
		}
		
		$this->points_cur = $a_points;
		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Get the status the user has on this node.
	 *
	 * @return int - one of ilStudyProgrammeProgress::STATUS_*
	 */
	public function getStatus() : int
	{
		return $this->status;
	}
	
	/**
	 * Set the status of this node.
	 *
	 * Throws when status is none of ilStudyProgrammeProgress::STATUS_*. Throws when
	 * current status is STATUS_COMPLETED.
	 */
	public function setStatus(int $a_status) : ilStudyProgrammeProgress
	{
		if (!in_array($a_status, self::$STATUS)) {
			throw new ilException("ilStudyProgrammeProgress::setStatus: No status: "
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
	public function setCompletionBy(int $a_id = null) : ilStudyProgrammeProgress 
	{
		$this->completion_by = $a_id;
		$this->updateLastChange();
		return $this;
	}
	
	/**
	 * Get the id of object or user that lead to the successful completion
	 * of this node.
	 *
	 * @return int
	 */
	public function getCompletionBy()
	{
		return $this->completion_by;
	}
	/**
	 * Get the id of the user who did the last change on this assignment.
	 * 
	 * @return int
	 */	
	public function getLastChangeBy()
	{
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
	public function setLastChangeBy(int $a_usr_id = null) : ilStudyProgrammeProgress
	{
		if ($a_usr_id !== null && ilObject::_lookupType($a_usr_id) != "usr") {
			throw new ilException("ilStudyProgrammeProgress::setLastChangeBy: '$a_usr_id' "
								 ."is no id of a user.");
		}
		$this->last_change_by = $a_usr_id;
		return $this;
	}
	
	/**
	 * Get the timestamp of the last change on this progress.
	 *
	 * @return DateTime
	 */
	public function getLastChange() : DateTime
	{
		return DateTime::createFromFormat(self::DATE_TIME_FORMAT, $this->last_change);
	}

	/**
	 * Update the last change timestamp to the current time.
	 *
	 * TODO: I'm not quite sure how the semantics of the last change field
	 * should be. Should this record every change or only changes done by
	 * a user manually. The answer to this question will also tell whether
	 * this method should be called in other setters or not.
	 *
	 * @return $this
	 */
	public function updateLastChange()
	{
		$this->setLastChange(new DateTime());
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
	public function setLastChange(DateTime $a_timestamp) : ilStudyProgrammeProgress
	{
		$this->last_change = $a_timestamp->format(self::DATE_TIME_FORMAT);
		return $this;
	}

	/**
	 * Set the date of assignment.
	 */
	public function setAssignmentDate(DateTime $assignment_date) : ilStudyProgrammeProgress
	{
		$this->assignment_date = $assignment_date;
		return $this;
	}

	/**
	 * Get the date of assignment.
	 */
	public function getAssignmentDate() : DateTime
	{
		return $this->assignment_date;
	}

	/**
	 * Set the timestamp of the complition of this progress.
	 */
	public function setCompletionDate(DateTime $completion_date = null) : ilStudyProgrammeProgress
	{
		$this->completion_date = $completion_date;
		return $this;
	}

	/**
	 * Get the timestamp of the complition of this progress.
	 *
	 * @return \DateTime | null
	 */
	public function getCompletionDate()
	{
		return $this->completion_date;
	}

	/**
	 * Get the deadline of this progress.
	 *
	 * @return DateTime | null
	 */
	public function getDeadline()
	{
		return $this->deadline;
	}

	/**
	 * Set the deadline of this progress
	 *
	 * @param DateTime | null	$deadline
	 *
	 * @return $this
	 */
	public function setDeadline(DateTime $deadline = null) : ilStudyProgrammeProgress
	{
		$this->deadline = $deadline;
		return $this;
	}

	/**
	 * Set limited validity of qualification date.
	 */
	public function setValidityOfQualification(DateTime $date = null) : ilStudyProgrammeProgress
	{
		$this->vq_date = $date;
		return $this;
	}

	/**
	 * Get the limited validity of qualification date.
	 */
	public function getValidityOfQualification()
	{
		return $this->vq_date;
	}
}

?>
