<?php declare(strict_types = 1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgramme
 * 
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Denis Klöpfer <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilStudyProgrammeSettings{
	
	// There are two different modes the programs calculation of the learning
	// progress can run in. It is also possible, that the mode is not defined
	// yet.
	const MODE_UNDEFINED = 0;
	
	// User is successful if he collected enough points in the subnodes of
	// this node. 
	const MODE_POINTS = 1;
	// User is successful if he has the "completed" learning progress in any
	// subobject.
	const MODE_LP_COMPLETED = 2;

	static $MODES = array( self::MODE_UNDEFINED
						 , self::MODE_POINTS
						 , self::MODE_LP_COMPLETED
						 );


	// A program tree has a lifecycle during which it has three status.

	// The program is a draft, that is users won't be assigned to the program
	// already.
	const STATUS_DRAFT = 10;
	// The program is active, that is used can be assigned to it. 
	const STATUS_ACTIVE = 20;
	// The program is outdated, that is user won't be assigned to it but can
	// still complete the program.
	const STATUS_OUTDATED = 30;

	static $STATUS = array( self::STATUS_DRAFT
						  , self::STATUS_ACTIVE
						  , self::STATUS_OUTDATED
						  );

	const NO_RESTART = -1;
	const NO_VALIDITY_OF_QUALIFICATION_PERIOD = -1;

	// Defaults
	const DEFAULT_POINTS = 100;
	const DEFAULT_SUBTYPE = 0; // TODO: What should that be?
	
	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	const DATE_FORMAT = 'Y-m-d';
	/**
	 * Id of this study program and the corresponding ILIAS-object as well.
	 *
	 * @var int
	 */
	protected $obj_id;
	
	/**
	 * Timestamp of the moment the last change was made on this object or any
	 * object in the subtree of the program.
	 * 
	 * @var string 
	 */
	protected $last_change;

	/**
	 * Id of the subtype of the program object.
	 *
	 * Subtype concepts is also used in Org-Units. 
	 * 
	 * @var int 
	 */
	protected $subtype_id;

	/**
	 * Amount of points a user needs to achieve to be successful on this program node
	 * and amount of points for the completion of the parent node in the program tree
	 * as well.
	 *
	 * @var int 
	 */
	protected $points; 

	/**
	 * Mode the calculation of the learning progress on this node is run in.    
	 *
	 * @var int 
	 */
	protected $lp_mode;

	/**
	 * Lifecycle status the program is in.
	 *
	 * @var int 
	 */
	protected $status;

	/**
	 * The period a user has to finish the prg in days, before he/she automaticaly fails.
	 * @var int
	 */
	protected $deadline_period = 0;

	/**
	 * The date, before which a user has to finish the prg, before he/she automaticaly fails.
	 * @var int | DateTime
	 */
	protected $deadline_date = null;

	/**
	 * The period after which a qualification will expire after completion.
	 * @var int
	 */
	protected $validity_of_qualification_period = self::NO_VALIDITY_OF_QUALIFICATION_PERIOD;

	/**
	 * The date at which a qualification will expire after completion.
	 * @var DateTime
	 */
	protected $validity_of_qualification_date = null;

	/**
	 * The bumber of days before qualification expiress, at which a user should be booked anew.
	 * @var int
	 */
	protected $restart_period = self::NO_RESTART;

	/**
	 * Is the access control governed by positions?
	 *
	 * @var bool
	 */
	protected $access_ctrl_positions;

	public function __construct(int $a_id)
	{
		$this->obj_id = $a_id;
	}
	
	/**
	 * Get the id of the study program.
	 *
	 * @return integer
	 */
	public function getObjId() : int
	{
		return (int)$this->obj_id;
	}

	/**
	 * Return the meta-data subtype id
	 *
	 * @return int
	 */
	public function getSubtypeId() : int
	{
		return $this->subtype_id;
	}


	/**
	 * Sets the meta-data type id
	 *
	 * @param int $subtype_id
	 */
	public function setSubtypeId(int $subtype_id)
	{
		$this->subtype_id = $subtype_id;
		return $this;
	}

	/**
	 * Get the timestamp of the last change on this program or a sub program.
	 *
	 * @return DateTime
	 */
	public function getLastChange() : DateTime
	{
		return DateTime::createFromFormat(self::DATE_TIME_FORMAT,$this->last_change);
	}

	/**
	 * Update the last change timestamp to the current time.
	 *
	 * @return $this
	 */
	public function updateLastChange() : ilStudyProgrammeSettings
	{
		$this->setLastChange(new DateTime());
		return $this;
	} 

	/**
	 * Set the last change timestamp to the given time.
	 * 
	 * Throws when given time is smaller then current timestamp
	 * since that is logically impossible.
	 *
	 * @return $this
	 */
	public function setLastChange(DateTime $a_timestamp) : ilStudyProgrammeSettings
	{
		$this->last_change = $a_timestamp->format(self::DATE_TIME_FORMAT);
		return $this;
	}

	/**
	 * Set the deadline period to a given value.
	 */
	public function setDeadlinePeriod(int $period) : ilStudyProgrammeSettings
	{
		if($period < 0) {
			throw new ilException('A deadline period must be > 0');
		}
		$this->deadline_period = $period;
		$this->deadline_date = null;
		return $this;
	}

	/**
	 * Set the deadline date to a given value.
	 */
	public function setDeadlineDate(DateTime $date = null) : ilStudyProgrammeSettings
	{
		$this->deadline_date = $date;
		$this->deadline_period = 0;
		return $this;
	}

	/**
	 * Set the amount of points.
	 * 
	 * @param integer   $a_points   - larger than zero 
	 * @throws ilException
	 * @return $this
	 */
	public function setPoints(int $a_points) : ilStudyProgrammeSettings
	{
		$a_points = (int)$a_points;
		if ($a_points < 0) {
			throw new ilException("ilStudyProgramme::setPoints: Points cannot "
								 ."be smaller than zero.");
		}

		$this->points = $a_points;
		$this->updateLastChange();
		return $this;
	} 

	/**
	 * Get the amount of points
	 *
	 * @return integer  - larger than zero
	 */
	public function getPoints() : int
	{
		return (int)$this->points;
	}

	/**
	 * Set the lp mode.
	 *
	 * Throws when program is not in draft status.
	 *
	 * @param integer $a_mode       - one of self::$MODES
	 * @return $this
	 */
	public function setLPMode(int $a_mode) : ilStudyProgrammeSettings
	{
		$a_mode = (int)$a_mode;
		if (!in_array($a_mode, self::$MODES)) {
			throw new ilException("ilStudyProgramme::setLPMode: No lp mode: "
								 ."'$a_mode'");
		}
		$this->lp_mode = $a_mode;
		$this->updateLastChange();
		return $this;
	}

	/**
	 * Get the lp mode.
	 *
	 * @return integer  - one of self::$MODES
	 */
	public function getLPMode() : int
	{
		return (int)$this->lp_mode;
	}

	/**
	 * Set the status of the node.
	 *
	 * TODO: Should this throw, when one wants to go back in lifecycle? Maybe getting
	 * back to draft needs to be forbidden only?
	 *
	 * @param integer $a_status     - one of self::$STATUS
	 * @return $this
	 */
	public function setStatus(int $a_status) : ilStudyProgrammeSettings
	{
		$a_status = (int)$a_status;
		if (!in_array($a_status, self::$STATUS)) {
			throw new ilException("ilStudyProgramme::setStatus: No lp mode: "
								 ."'$a_status'");
		}
		$this->status = $a_status;
		$this->updateLastChange();
		return $this;
	}

	/**
	 * Get the status.
	 *
	 * @return integer  - one of self::$STATUS
	 */
	public function getStatus() : int
	{
		return (int)$this->status;
	}

	/**
	 * Returns the set deadline period.
	 */
	public function getDeadlinePeriod() : int
	{
		return $this->deadline_period;
	}

	/**
	 * Returns the set deadline date.
	 */
	public function getDeadlineDate()
	{
		return $this->deadline_date;
	}

	/**
	 * Set the validity of qualification period
	 */
	public function setValidityOfQualificationPeriod(int $period) : ilStudyProgrammeSettings
	{
		if($period < -1) {
			throw new ilException('invalid validity of qualification period: '.$period);
		}
		$this->validity_of_qualification_period = $period;
		$this->validity_of_qualification_date = null;
		return $this;
	}

	/**
	 * Set the validity of qualification date
	 */
	public function setValidityOfQualificationDate(DateTime $date = null) : ilStudyProgrammeSettings
	{
		$this->validity_of_qualification_period = self::NO_VALIDITY_OF_QUALIFICATION_PERIOD;
		$this->validity_of_qualification_date = $date;
		return $this;
	}

	/**
	 * Set the validity of qualification restart period
	 */
	public function setRestartPeriod(int $period) : ilStudyProgrammeSettings
	{
		if($period < -1) {
			throw new ilException('invalid restart period: '.$period);
		}
		$this->restart_period = $period;
		return $this;
	}

	/**
	 * Return restart validity of qualification period
	 */
	public function getValidityOfQualificationPeriod() : int
	{
		return $this->validity_of_qualification_period;
	}

	/**
	 * Return restart validity of qualification datetime or null, if none set.
	 */
	public function getValidityOfQualificationDate()
	{
		return $this->validity_of_qualification_date;
	}

	/**
	 * Return restart period
	 */
	public function getRestartPeriod() : int
	{
		return $this->restart_period;
	}


	/**
	 * Choose wether the corresponding prg feature access is governed by positions.
	 */
	public function setAccessControlByOrguPositions(bool $access_ctrl_positions)
	{
		$this->access_ctrl_positions = $access_ctrl_positions;
	}

	/**
	 * Is the corresponding prg feature access governed by positions?
	 */
	public function getAccessControlByOrguPositions() : bool
	{
		return $this->access_ctrl_positions;
	}
}

?>
