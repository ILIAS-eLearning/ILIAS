<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking category
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingType
{
	protected $id;			// int
	protected $title;		// string
	protected $pool_id;		// int
	protected $schedule_id; // int

	/**
	 * Constructor
	 *
	 * if id is given will read dataset from db
	 *
	 * @param	int	$a_id
	 */
	function __construct($a_id = NULL)
	{
		$this->id = (int)$a_id;
		$this->read();
	}

	/**
	 * Set title
	 * @param	string	$a_title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get title
	 * @return	string
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set booking pool id (aka parent obj ref id)
	 * @param	int	$a_type_id
	 */
	function setPoolId($a_pool_id)
	{
		$this->pool_id = (int)$a_pool_id;
	}

	/**
	 * Get booking pool id
	 * @return	int
	 */
	function getPoolId()
	{
		return $this->pool_id;
	}

	/**
	 * Set booking schedule id
	 * @param	int	$a_schedule_id
	 */
	function setScheduleId($a_schedule_id)
	{
		$this->schedule_id = (int)$a_schedule_id;
	}

	/**
	 * Get booking schedule id
	 * @return	int
	 */
	function getScheduleId()
	{
		return $this->schedule_id;
	}

	/**
	 * Get dataset from db
	 */
	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT title,pool_id,schedule_id'.
				' FROM booking_type'.
				' WHERE booking_type_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setTitle($row['title']);
			$this->setPoolId($row['pool_id']);
			$this->setScheduleId($row['schedule_id']);
		}
	}

	/**
	 * Create new entry in db
	 * @return	bool
	 */
	function save()
	{
		global $ilDB;

		$id = $ilDB->nextId('booking_type');

		return $ilDB->query('INSERT INTO booking_type (booking_type_id,title,pool_id,'.
			'schedule_id) VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getTitle(), 'text').
			','.$ilDB->quote($this->getPoolId(), 'integer').','.$ilDB->quote($this->getScheduleId(), 'integer').')');
	}

	/**
	 * Update entry in db
	 * @return	bool
	 */
	function update()
	{
		global $ilDB;

		$set = $ilDB->query('SELECT schedule_id'.
				' FROM booking_type'.
				' WHERE booking_type_id = '.$ilDB->quote($this->id, 'integer'));
	    $schedule = $ilDB->fetchAssoc($set);
		$schedule = $schedule["schedule_id"];

		if(($this->getScheduleId() && !$schedule) ||
			(!$this->getScheduleId() && $schedule))
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			ilBookingObject::updateSchedule($this->id, $schedule);
		}

		return $ilDB->query('UPDATE booking_type'.
			' SET title = '.$ilDB->quote($this->getTitle(), 'text').
			', pool_id = '.$ilDB->quote($this->getPoolId(), 'integer').
			', schedule_id = '.$ilDB->quote($this->getScheduleId(), 'integer').
			' WHERE booking_type_id = '.$ilDB->quote($this->id, 'integer'));
	}

	/**
	 * Get list of booking types for given pool
	 * @param	int	$a_pool_id
	 * @return	array
	 */
	static function getList($a_pool_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT booking_type.title, booking_type_id,booking_type.schedule_id,'.
			' CASE WHEN type_id IS NULL THEN 0 ELSE COUNT(*) END AS counter'.
			' FROM booking_type'.
			' LEFT JOIN booking_object ON (type_id = booking_type_id)'.
			' WHERE pool_id = '.$ilDB->quote($a_pool_id, 'integer').
			' GROUP BY booking_type_id,booking_type.title,type_id,booking_type.schedule_id'.
			' ORDER BY booking_type.title');
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Delete single entry
	 * @return bool
	 */
	function delete()
	{
		global $ilDB;
		
		if($this->id)
		{
			return $ilDB->query('DELETE FROM booking_type'.
				' WHERE booking_type_id = '.$ilDB->quote($this->id, 'integer'));
		}
	}
}

?>