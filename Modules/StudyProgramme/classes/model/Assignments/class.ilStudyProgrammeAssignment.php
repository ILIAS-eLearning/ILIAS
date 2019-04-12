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
	/**
	 * Id of this assignment.
	 *
	 * @var int
	 * 
	 * @con_is_primary  true
	 * @con_sequence    true
	 * @con_is_unique   true
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $id;
 
	/**
	 * The id of the user that is assigned. 
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  true 
	 */
	protected $usr_id;

	/**
	 * Root node of the program tree, the user was assigned to. Could be a subtree of
	 * a larger program. This is the object id of the program.
	 * 
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  true
	 */
	protected $root_prg_id;


	/**
	 * Timestamp of the moment of the assignment to or last update of the program.
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   timestamp 
	 * @con_is_notnull  true 
	 */
	protected $last_change; 

	/**
	 * Id of user who did the assignment to or last update of the program.
	 *
	 * @var int 
	 * 
	 * @con_has_field   true
	 * @con_fieldtype   integer 
	 * @con_length      4
	 * @con_is_notnull  true 
	 */
	protected $last_change_by;
	

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
	public function setLastChangeBy(int $usr_id) : ilStudyProgrammeAssignment
	{
		if (ilObject::_lookupType($usr_id) != "usr") {
			throw new ilException("ilStudyProgrammeAssignment::setLastChangeBy: '$usr_id' "
								 ."is no id of a user.");
		}
		$this->last_change_by = $usr_id;
		return $this;
	}
	
	/**
	 * Get the timestamp of the last change on this program or a sub program.
	 *
	 * @return ilDateTime
	 */
	public function getLastChange() : ilDateTime
	{
		return new ilDateTime($this->last_change, IL_CAL_DATETIME);
	}

	/**
	 * Update the last change timestamp to the current time.
	 *
	 * @return $this
	 */
	public function updateLastChange() : ilStudyProgrammeAssignment
	{
		$this->setLastChange(new ilDateTime(ilUtil::now(), IL_CAL_DATETIME)); 
		return $this;
	}

	/**
	 * Set the last change timestamp to the given time.
	 * 
	 * Throws when given time is smaller then current timestamp
	 * since that is logically impossible.
	 * 
	 * @throws ilException
	 * @return $this
	 */
	public function setLastChange(ilDateTime $timestamp) : ilStudyProgrammeAssignment
	{
		if (ilDateTime::_before($timestamp, $this->getLastChange())) {
			throw new ilException("ilStudyProgrammeAssignment::setLastChange: Given "
								 ."timestamp is before current timestamp. That "
								 ."is logically impossible.");
		}
		
		$this->last_change = $timestamp->get(IL_CAL_DATETIME);
		return $this;
	}
}

?>
