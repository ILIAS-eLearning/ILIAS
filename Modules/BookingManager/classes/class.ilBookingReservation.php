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

	const STATUS_IN_USE = 2;
	const STATUS_CANCELLED = 5;

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
	 * Set booking user id
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
		if($a_status === NULL)
		{
			$this->status = NULL;
		}
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
		if(in_array($a_status, array(self::STATUS_IN_USE, self::STATUS_CANCELLED)))
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

		// there can only be 1
		if($this->getStatus() == self::STATUS_IN_USE)
		{
			$ilDB->manipulate('UPDATE booking_reservation'.
			' SET status = '.$ilDB->quote(NULL, 'integer').
			' WHERE object_id = '.$ilDB->quote($this->getObjectId(), 'integer').
			' AND status = '.$ilDB->quote(self::STATUS_IN_USE, 'integer'));
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
	 * @param	int		$a_return_single
	 * @return	int
	 */
	static function getAvailableObject(array $a_ids, $a_from, $a_to, $a_return_single = true)
	{
		global $ilDB;				
		
		$nr_map = ilBookingObject::getNrOfItemsForObjects($a_ids);
		
		$from = $ilDB->quote($a_from, 'integer');
		$to = $ilDB->quote($a_to, 'integer');
		
		$set = $ilDB->query('SELECT count(*) cnt, object_id'.
			' FROM booking_reservation'.
			' WHERE '.$ilDB->in('object_id', $a_ids, '', 'integer').
			' AND (status IS NULL OR status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').')'.
			' AND ((date_from <= '.$from.' AND date_to >= '.$from.')'.
			' OR (date_from <= '.$to.' AND date_to >= '.$to.')'.
			' OR (date_from >= '.$from.' AND date_to <= '.$to.'))'.
			' GROUP BY object_id');
		$blocked = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			if($row['cnt'] >= $nr_map[$row['object_id']])
			{
				$blocked[] = $row['object_id'];
			}
		}
		$available = array_diff($a_ids, $blocked);
		if(sizeof($available))
		{
			if($a_return_single)
			{
				return array_shift($available);
			}
			else
			{
				return $available;
			}
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
			' AND (status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').
			' OR STATUS IS NULL) AND object_id = '.$ilDB->quote($a_object_id, 'integer').
			' ORDER BY date_from');
		$row = $ilDB->fetchAssoc($set);
		return $row;
	}
	
	static function getObjectReservationForUser($a_object_id, $a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query('SELECT booking_reservation_id FROM booking_reservation'.
			' WHERE user_id = '.$ilDB->quote($a_user_id, 'integer').
			' AND object_id = '.$ilDB->quote($a_object_id, 'integer').
			' AND (status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').
			' OR STATUS IS NULL)');
		$row = $ilDB->fetchAssoc($set);
		return $row['booking_reservation_id'];
	}

	/**
	 * List all reservations
	 * @param	array	$a_object_ids
	 * @param	int		$a_limit
	 * @param	int		$a_offset
	 * @param	array	$a_offset
	 * @return	array
	 */
	static function getList($a_object_ids, $a_limit = 10, $a_offset = 0, array $filter)
	{
		global $ilDB;

		$sql = 'SELECT r.*,o.title'.
			' FROM booking_reservation r'.
			' JOIN booking_object o ON (o.booking_object_id = r.object_id)';

		$count_sql = 'SELECT COUNT(*) AS counter'.
			' FROM booking_reservation r'.
			' JOIN booking_object o ON (o.booking_object_id = r.object_id)';
		
		$where = array($ilDB->in('r.object_id', $a_object_ids, '', 'integer'));		
		if($filter['status'])
		{
			if($filter['status'] > 0)
			{
				$where[] = 'status = '.$ilDB->quote($filter['status'], 'integer');
			}
			else
			{
				$where[] = '(status != '.$ilDB->quote(-$filter['status'], 'integer').
					' OR status IS NULL)';
			}
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
			$count_sql .= ' WHERE '.implode(' AND ', $where);
		}

		$set = $ilDB->query($count_sql);
		$row = $ilDB->fetchAssoc($set);
		$counter = $row['counter'];

		$sql .= ' ORDER BY date_from DESC, booking_reservation_id DESC';
		
		$ilDB->setLimit($a_limit, $a_offset);
		$set = $ilDB->query($sql);
		$res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}

		return array('data'=>$res, 'counter'=>$counter);
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