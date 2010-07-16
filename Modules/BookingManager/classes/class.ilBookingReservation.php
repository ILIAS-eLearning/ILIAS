<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * a booking reservation
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingReservation
{
	protected $id;			// int
	protected $object_id;	// int
	protected $user_id;		// int
	protected $from;		// timestamp
	protected $to;			// timestamp
	protected $status;		// status

	const STATUS_RESERVED = 1;
	const STATUS_IN_USE = 2;
	const STATUS_OVERDUE = 3;
	const STATUS_BLOCKED = 4;

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
		$this->setStatus(self::STATUS_RESERVED);
		$this->read();
	}

	/**
	 * Set object id
	 * @param	int	$a_object_id
	 */
	function setObjectId($a_object_id)
	{
		$this->object_id = $a_object_id;
	}

	/**
	 * Get object id
	 * @return	int
	 */
	function getObjectId()
	{
		return $this->object_id;
	}

	/**
	 * Set user type id
	 * @param	int	$a_user_id
	 */
	function setUserId($a_user_id)
	{
		$this->user_id = (int)$a_user_id;
	}

	/**
	 * Get booking user id
	 * @return	int
	 */
	function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Set booking from date
	 * @param	int	$a_from
	 */
	function setFrom($a_from)
	{
		$this->from = (int)$a_from;
	}

	/**
	 * Get booking from date
	 * @return	int
	 */
	function getFrom()
	{
		return $this->from;
	}

	/**
	 * Set booking to date
	 * @param	int	$a_to
	 */
	function setTo($a_to)
	{
		$this->to = (int)$a_to;
	}

	/**
	 * Get booking to date
	 * @return	int
	 */
	function getTo()
	{
		return $this->to;
	}

	/**
	 * Set booking status
	 * @param	int	$a_status
	 */
	function setStatus($a_status)
	{
		if($this->isValidStatus((int)$status))
		{
			$this->status = (int)$a_status;
		}
	}

	/**
	 * Get booking status
	 * @return	int
	 */
	function getStatus()
	{
		return $this->status;
	}

	/**
	 * Check if given status is valid
	 * @param	int	$a_status
	 * @return	bool
	 */
	function isValidStatus($a_status)
	{
		if(in_array($a_status, array(self::STATUS_RESERVED, self::STATUS_IN_USE,
			self::STATUS_OVERDUE, self::STATUS_BLOCKED)))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get dataset from db
	 */
	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT object_id,user_id,date_from,date_to,status'.
				' FROM booking_reservation'.
				' WHERE booking_reservation_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setUserId($row['user_id']);
			$this->setObjectId($row['object_id']);
			$this->setFrom($row['date_from']);
			$this->setTo($row['date_to']);
			$this->setStatus($row['status']);
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

		$id = $ilDB->nextId('booking_reservation');

		return $ilDB->manipulate('INSERT INTO booking_reservation'.
			' (booking_reservation_id,user_id,object_id,date_from,date_to,status)'.
			' VALUES ('.$ilDB->quote($id, 'integer').','.$ilDB->quote($this->getUserId(), 'integer').
			','.$ilDB->quote($this->getObjectId(), 'integer').','.$ilDB->quote($this->getFrom(), 'integer').
			','.$ilDB->quote($this->getTo(), 'integer').','.$ilDB->quote($this->getStatus(), 'integer').')');
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

		return $ilDB->manipulate('UPDATE booking_reservation'.
			' SET object_id = '.$ilDB->quote($this->getObjectId(), 'text').
			', user_id = '.$ilDB->quote($this->getUserId(), 'integer').
			', date_from = '.$ilDB->quote($this->getFrom(), 'integer').
			', date_to = '.$ilDB->quote($this->getTo(), 'integer').
			', status = '.$ilDB->quote($this->getStatus(), 'integer').
			' WHERE booking_reservation_id = '.$ilDB->quote($this->id, 'integer'));
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
			return $ilDB->manipulate('DELETE FROM booking_reservation'.
				' WHERE booking_reservation_id = '.$ilDB->quote($this->id, 'integer'));
		}
	}

	/**
	 *
	 * @param array $ids
	 * @param <type> $from
	 * @param <type> $to
	 * 
	 */
	static function getAvailableObject(array $ids, $from, $to)
	{
		return array_pop($ids);
	}
}

?>