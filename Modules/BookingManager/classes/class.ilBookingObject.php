<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * a bookable ressource
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingObject
{
	protected $id;			// int
	protected $pool_id;		// int
	protected $title;		// string
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
	 * Set object title
	 * @param	string	$a_title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get object title
	 * @return	string
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set booking pool id
	 * @param	int	$a_pool_id
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
				' FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
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

		if($this->id)
		{
			return false;
		}

		$id = $ilDB->nextId('booking_object');

		return $ilDB->manipulate('INSERT INTO booking_object'.
			' (booking_object_id,title,pool_id,schedule_id)'.
			' VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getTitle(), 'text').
			','.$ilDB->quote($this->getPoolId(), 'integer').','.$ilDB->quote($this->getScheduleId(), 'integer').')');
	}

	/**
	 * Update entry in db
	 * @return	bool
	 */
	function update()
	{
		global $ilDB;

		if(!$this->id)
		{
			return false;
		}

		// pool cannot change
		return $ilDB->manipulate('UPDATE booking_object'.
			' SET title = '.$ilDB->quote($this->getTitle(), 'text').
			', schedule_id = '.$ilDB->quote($this->getScheduleId(), 'integer').
			' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
	}

	/**
	 * Get list of booking objects for given type	 
	 * @param	int	$a_pool_id
	 * @return	array
	 */
	static function getList($a_pool_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT booking_object_id,title,schedule_id'.
			' FROM booking_object'.
			' WHERE pool_id = '.$ilDB->quote($a_pool_id, 'integer').
			' ORDER BY title');
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
			return $ilDB->manipulate('DELETE FROM booking_object'.
				' WHERE booking_object_id = '.$ilDB->quote($this->id, 'integer'));
		}
	}
}

?>