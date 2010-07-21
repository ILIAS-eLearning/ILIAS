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
	const STATUS_RETURNED = 4;
	const STATUS_CANCELLED = 5;
	const STATUS_BLOCKED = 6;

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
	 * Get id
	 * @return	int
	 */
	function getId()
	{
		return $this->id;
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
		if($this->isValidStatus((int)$a_status))
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
	static function isValidStatus($a_status)
	{
		if(in_array($a_status, array(self::STATUS_RESERVED, self::STATUS_IN_USE,
			self::STATUS_OVERDUE, self::STATUS_RETURNED, self::STATUS_CANCELLED,
			self::STATUS_BLOCKED)))
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

		$this->id = $ilDB->nextId('booking_reservation');
		
		return $ilDB->manipulate('INSERT INTO booking_reservation'.
			' (booking_reservation_id,user_id,object_id,date_from,date_to,status)'.
			' VALUES ('.$ilDB->quote($this->id, 'integer').','.$ilDB->quote($this->getUserId(), 'integer').
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
	 * Check if any of given objects are bookable
	 * @param	array	$a_ids
	 * @param	int		$a_from
	 * @param	int		$a_to
	 * @return	int
	 */
	static function getAvailableObject(array $a_ids, $a_from, $a_to)
	{
		global $ilDB;

		$from = $ilDB->quote($a_from, 'integer');
		$to = $ilDB->quote($a_to, 'integer');
		
		$set = $ilDB->query('SELECT object_id'.
			' FROM booking_reservation'.
			' WHERE '.$ilDB->in('object_id', $a_ids, '', 'integer').
			' AND status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').
			' AND ((date_from <= '.$from.' AND date_to >= '.$from.')'.
			' OR (date_from <= '.$to.' AND date_to >= '.$to.')'.
			' OR (date_from >= '.$from.' AND date_to <= '.$to.'))');
		$blocked = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$blocked[] = $row['object_id'];
		}
		$available = array_diff($a_ids, $blocked);
		if(sizeof($available))
		{
			return array_shift($available);
		}
	}

	/**
	 * Get details about object reservation
	 * @param	int	$a_object_id
	 * @return	array
	 */
	static function getCurrentOrUpcomingReservation($a_object_id)
    {
		global $ilDB;

		$now = $ilDB->quote(time(), 'integer');

		$ilDB->setLimit(1);
		$set = $ilDB->query('SELECT user_id, status, date_from, date_to'.
			' FROM booking_reservation'.
			' WHERE ((date_from <= '.$now.' AND date_to >= '.$now.')'.
			' OR date_from > '.$now.')'.
			' AND status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').
			' AND object_id = '.$ilDB->quote($a_object_id, 'integer').
			' ORDER BY date_from');
		$row = $ilDB->fetchAssoc($set);
		return $row;
	}

	/**
	 * List all reservations
	 * @param	int		$a_limit
	 * @param	int		$a_offset
	 * @param	array	$a_offset
	 * @return	array
	 */
	static function getList($a_limit = 10, $a_offset = 0, array $filter)
	{
		global $ilDB;

		$sql = 'SELECT r.*,o.title'.
			' FROM booking_reservation r'.
			' JOIN booking_object o ON (o.booking_object_id = r.object_id)';

		$where = array();
		if($filter['type'])
		{
			$where[] = 'type_id = '.$ilDB->quote($filter['type'], 'integer');
		}
		if($filter['status'])
		{
			$where[] = 'status = '.$ilDB->quote($filter['status'], 'integer');
		}
		if($filter['from'])
		{
			$where[] = 'date_from >= '.$ilDB->quote($filter['from'], 'integer');
		}
		if($filter['to'])
		{
			$where[] = 'date_to <= '.$ilDB->quote($filter['to'], 'integer');
		}
		if(sizeof($where))
		{
			$sql .= ' WHERE '.implode(' AND ', $where);
		}

		$sql .= ' ORDER BY date_from DESC';

		$ilDB->setLimit($a_limit, $a_offset);
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Batch update reservation status
	 * @param	array	$a_ids
	 * @param	int		$a_status
	 * @return	bool
	 */
	static function changeStatus(array $a_ids, $a_status)
	{
		global $ilDB;

		if(self::isValidStatus($a_status))
		{
			return $ilDB->manipulate('UPDATE booking_reservation'.
				' SET status = '.$ilDB->quote($a_status, 'integer').
				' WHERE '.$ilDB->in('booking_reservation_id', $a_ids, '', 'integer'));

		}
	}
}

?>