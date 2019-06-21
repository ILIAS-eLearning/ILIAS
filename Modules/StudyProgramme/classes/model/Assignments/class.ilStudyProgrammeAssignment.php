<?php declare(strict_types = 1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Class ilStudyProgrammeAssignment.
 *
 * Represents one assignment of the user to a program tree.
 *
 * One user can have multiple assignments to the same tree. This makes it possible
 * to represent programs that need to be accomplished periodically as well.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilStudyProgrammeAssignment
{
	const NO_RESTARTED_ASSIGNMENT = -1;

	const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	const DATE_FORMAT = 'Y-m-d';

	const AUTO_ASSIGNED_BY_ROLE = -1;
	const AUTO_ASSIGNED_BY_ORGU = -2;
	const AUTO_ASSIGNED_BY_COURSE = -3;
	const AUTO_ASSIGNED_BY_GROUP = -4;

	/**
	 * Id of this assignment.
	 *
	 * @var int

	 */
	protected $id;

	/**
	 * The id of the user that is assigned.
	 *
	 * @var int
	 */
	protected $usr_id;

	/**
	 * Root node of the program tree, the user was assigned to. Could be a subtree of
	 * a larger program. This is the object id of the program.
	 *
	 * @var int
	 */
	protected $root_prg_id;


	/**
	 * Timestamp of the moment of the assignment to or last update of the program.
	 *
	 * @var int
	 */
	protected $last_change;

	/**
	 * Id of user who did the assignment to or last update of the program.
	 *
	 * @var int
	 */
	protected $last_change_by;

	/**
	 * The date at which the user will be assigned to root prg anew.
	 *
	 * @var DateTime | null
	 */
	protected $restart_date;

	/**
	 * The id of the assignment which was intiated due to expiring
	 * progress at this assignment.
	 *
	 * @var int
	 */
	protected $restarted_asssignment_id = self::NO_RESTARTED_ASSIGNMENT;


	public function __construct(int $id)
	{
		$this->id = $id;
	}

	/**
	 * Get the id of the assignment.
	 *
	 * @return int
	 */
	public function getId() : int
	{
		return $this->id;
	}

	/**
	 * Get the object id of the program the user was assigned to.
	 *
	 * @return int
	 */
	public function getRootId() : int
	{
		return $this->root_prg_id;
	}

	public function setRootId(int $id) : ilStudyProgrammeAssignment
	{
		$this->root_prg_id = $id;
		return $this;
	}

	/**
	 * Get the id of the user who is assigned.
	 *
	 * @return int
	 */
	public function getUserId() : int
	{
		return $this->usr_id;
	}

	public function setUserId(int $usr_id) : ilStudyProgrammeAssignment
	{
		$this->usr_id = $usr_id;
		return $this;
	}

	/**
	 * Get the id of the user who did the last change on this assignment.
	 *
	 * @return int
	 */
	public function getLastChangeBy() : int
	{
		return $this->last_change_by;
	}

	/**
	 * Set the id of the user who did the last change on this assignment.
	 *
	 * Throws when $a_usr_id is not the id of a user.
	 *
	 * @throws ilException
	 * @return $this
	 */
	public function setLastChangeBy(int $assingned_by_id) : ilStudyProgrammeAssignment
	{

		if (ilObject::_lookupType($assingned_by_id) != "usr" &&
			! in_array($assingned_by_id, [
				AUTO_ASSIGNED_BY_ROLE,
				AUTO_ASSIGNED_BY_ORGU,
				AUTO_ASSIGNED_BY_COURSE,
				AUTO_ASSIGNED_BY_GROUP
			])
		) {
			throw new ilException("ilStudyProgrammeAssignment::setLastChangeBy: '$assingned_by_id' "
								 ."is neither a user's id nor a valid membership source.");
		}
		$this->last_change_by = $assingned_by_id;
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
	public function updateLastChange() : ilStudyProgrammeAssignment
	{
		$this->setLastChange(new DateTime());
		return $this;
	}

	/**
	 * Set the last change timestamp to the given time.
	 *
	 * @return $this
	 */
	public function setLastChange(DateTime $timestamp) : ilStudyProgrammeAssignment
	{
		$this->last_change = $timestamp->format(self::DATE_TIME_FORMAT);
		return $this;
	}

	/**
	 * Set the date, at which the user is to be reassigned to the programme
	 */
	public function setRestartDate(DateTime $date = null) : ilStudyProgrammeAssignment
	{
		$this->restart_date = $date;
		return $this;
	}

	/**
	 * Get the date, at which the user is to be reassigned to the programme
	 *
	 * @return DateTime | null
	 */
	public function getRestartDate()
	{
		return $this->restart_date;
	}

	/**
	 * Set the date, at which the user was be reassigned to the programme
	 */
	public function setRestartedAssignmentId(int $id) : ilStudyProgrammeAssignment
	{
		$this->restarted_asssignment_id = $id;
		return $this;
	}

	/**
	 * Get the date, at which the user was reassigned to the programme
	 *
	 * @return int
	 */
	public function getRestartedAssignmentId() : int
	{
		return $this->restarted_asssignment_id;
	}
}

?>
