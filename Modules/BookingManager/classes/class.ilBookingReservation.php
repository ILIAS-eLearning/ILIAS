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
	protected $group_id;	// int

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
	 * Set group id
	 * @param	int	$a_group_id
	 */
	function setGroupId($a_group_id)
	{
		$this->group_id = $a_group_id;
	}

	/**
	 * Get group id
	 * @return	int
	 */
	function getGroupId()
	{
		return $this->group_id;
	}

	/**
	 * Get dataset from db
	 */
	protected function read()
	{
		global $ilDB;
		
		if($this->id)
		{
			$set = $ilDB->query('SELECT *'.
				' FROM booking_reservation'.
				' WHERE booking_reservation_id = '.$ilDB->quote($this->id, 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setUserId($row['user_id']);
			$this->setObjectId($row['object_id']);
			$this->setFrom($row['date_from']);
			$this->setTo($row['date_to']);
			$this->setStatus($row['status']);
			$this->setGroupId($row['group_id']);
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
			' (booking_reservation_id,user_id,object_id,date_from,date_to,status,group_id)'.
			' VALUES ('.$ilDB->quote($this->id, 'integer').
			','.$ilDB->quote($this->getUserId(), 'integer').
			','.$ilDB->quote($this->getObjectId(), 'integer').
			','.$ilDB->quote($this->getFrom(), 'integer').
			','.$ilDB->quote($this->getTo(), 'integer').
			','.$ilDB->quote($this->getStatus(), 'integer').
			','.$ilDB->quote($this->getGroupId(), 'integer').')');
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

		/* there can only be 1
		if($this->getStatus() == self::STATUS_IN_USE)
		{
			$ilDB->manipulate('UPDATE booking_reservation'.
			' SET status = '.$ilDB->quote(NULL, 'integer').
			' WHERE object_id = '.$ilDB->quote($this->getObjectId(), 'integer').
			' AND status = '.$ilDB->quote(self::STATUS_IN_USE, 'integer'));
		}
		*/
		
		return $ilDB->manipulate('UPDATE booking_reservation'.
			' SET object_id = '.$ilDB->quote($this->getObjectId(), 'text').
			', user_id = '.$ilDB->quote($this->getUserId(), 'integer').
			', date_from = '.$ilDB->quote($this->getFrom(), 'integer').
			', date_to = '.$ilDB->quote($this->getTo(), 'integer').
			', status = '.$ilDB->quote($this->getStatus(), 'integer').
			', group_id = '.$ilDB->quote($this->getGroupId(), 'integer').
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
	 * Get next group id	
	 * @return int
	 */
	public static function getNewGroupId()
	{
		global $ilDB;
		
		return $ilDB->nextId('booking_reservation_group');
	}

	/**
	 * Check if any of given objects are bookable
	 * @param	array	$a_ids
	 * @param	int		$a_from
	 * @param	int		$a_to
	 * @param	int		$a_return_single
	 * @return	int
	 */
	static function getAvailableObject(array $a_ids, $a_from, $a_to, $a_return_single = true, $a_return_counter = false)
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
		$blocked = $counter = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			if($row['cnt'] >= $nr_map[$row['object_id']])
			{
				$blocked[] = $row['object_id'];
			}
			else if($a_return_counter)
			{
				$counter[$row['object_id']] = (int)$nr_map[$row['object_id']]-(int)$row['cnt'];
			}
		}		
		$available = array_diff($a_ids, $blocked);
		if(sizeof($available))
		{
			if($a_return_counter)
			{
				foreach($a_ids as $id)
				{
					if(!isset($counter[$id]))
					{
						$counter[$id] = (int)$nr_map[$id];
					}
				}
				return $counter;
			}
			else if($a_return_single)
			{
				return array_shift($available);
			}
			else
			{
				return $available;
			}
		}
	}
	
	static function isObjectAvailableNoSchedule($a_obj_id)
	{
		global $ilDB;
		
		$all = ilBookingObject::getNrOfItemsForObjects(array($a_obj_id));
		$all = (int)$all[$a_obj_id];
		
		$set = $ilDB->query('SELECT COUNT(*) cnt'.
			' FROM booking_reservation r'.
			' JOIN booking_object o ON (o.booking_object_id = r.object_id)'.
			' WHERE (status IS NULL OR status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').')'.
			' AND r.object_id = '.$ilDB->quote($a_obj_id, 'integer'));		
		$cnt = $ilDB->fetchAssoc($set);
		$cnt = (int)$cnt['cnt'];
		
		return (bool)($all-$cnt); // #11864
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
	
	static function getObjectReservationForUser($a_object_id, $a_user_id, $a_multi = false)
	{
		global $ilDB;
		
		$set = $ilDB->query('SELECT booking_reservation_id FROM booking_reservation'.
			' WHERE user_id = '.$ilDB->quote($a_user_id, 'integer').
			' AND object_id = '.$ilDB->quote($a_object_id, 'integer').
			' AND (status <> '.$ilDB->quote(self::STATUS_CANCELLED, 'integer').
			' OR STATUS IS NULL)');
		if(!$a_multi)
		{
			$row = $ilDB->fetchAssoc($set);
			return $row['booking_reservation_id'];
		}
		else
		{
			$res = array();
			while($row = $ilDB->fetchAssoc($set))
			{
				$res[] = $row['booking_reservation_id'];
			}
			return $res;
		}
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
	 * List all reservations by date
	 * @param	bool	$a_has_schedule
	 * @param	array	$a_object_ids
	 * @param	string	$a_order_field
	 * @param	string	$a_order_direction
	 * @param	int		$a_offset
	 * @param	int		$a_limit
	 * @param	array	$filter
	 * @return	array
	 */
	static function getListByDate($a_has_schedule, array $a_object_ids, $a_order_field, $a_order_direction, $a_offset, $a_limit, array $filter = null)
	{		
		global $ilDB;
		
		$res = array();
		
		$sql = 'SELECT r.*, o.title'.
			' FROM booking_reservation r'.
			' JOIN booking_object o ON (o.booking_object_id = r.object_id)';

		$where = array($ilDB->in('object_id', $a_object_ids, '', 'integer'));		
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
		if($a_has_schedule)
		{
			if($filter['from'])
			{
				$where[] = 'date_from >= '.$ilDB->quote($filter['from'], 'integer');
			}
			if($filter['to'])
			{
				$where[] = 'date_to <= '.$ilDB->quote($filter['to'], 'integer');
			}
			if($filter['user_id'])
			{
				$where[] = 'user_id = '.$ilDB->quote($filter['user_id'], 'integer');
			}					
		}
		/*
		if($a_group_id)
		{
			$where[] = 'group_id = '.$ilDB->quote(substr($a_group_id, 1), 'integer');
		}		 
		*/		
		if(sizeof($where))
		{
			$sql .= ' WHERE '.implode(' AND ', $where);		
		}
		
		if($a_has_schedule)
		{			
			$sql .= ' ORDER BY date_from DESC';			
		}
				
		$set = $ilDB->query($sql);			
		while($row = $ilDB->fetchAssoc($set))
		{								
			$obj_id = $row["object_id"];
			$user_id = $row["user_id"];
						
			if($a_has_schedule)
			{
				$slot = $row["date_from"]."_".$row["date_to"];		
				$idx = $obj_id."_".$user_id."_".$slot;										
			}
			else
			{
				$idx = $obj_id."_".$user_id;
			}
			
			if($a_has_schedule && $filter["slot"])
			{
				$slot_idx = date("w",  $row["date_from"])."_".date("H:i", $row["date_from"]).
					"-".date("H:i", $row["date_to"]+1);
				if($filter["slot"] != $slot_idx)
				{
					continue;
				}
			}
			
			if(!isset($res[$idx]))
			{								
				$res[$idx] = array(					
					"object_id" => $obj_id
					,"title" => $row["title"]
					,"user_id" => $user_id
					,"counter" => 1						
					,"user_name" => ilObjUser::_lookupFullName($user_id)					
				);
				
				if($a_has_schedule)
				{
					$res[$idx]["booking_reservation_id"] = $idx;
					$res[$idx]["date"] = date("Y-m-d", $row["date_from"]);
					$res[$idx]["slot"] = date("H:i", $row["date_from"])." - ".
						date("H:i", $row["date_to"]+1);
					$res[$idx]["week"] = date("W",  $row["date_from"]);				
					$res[$idx]["weekday"] = date("w",  $row["date_from"]);				
					$res[$idx]["can_be_cancelled"] = ($row["status"] != self::STATUS_CANCELLED &&
						$row["date_from"] > time());					
				}
				else
				{
					$res[$idx]["booking_reservation_id"] = $row["booking_reservation_id"];
					$res[$idx]["status"] = $row["status"];
					$res[$idx]["can_be_cancelled"] = ($row["status"] != self::STATUS_CANCELLED);					
				}
			}
			else
			{
				$res[$idx]["counter"]++;
			}
		}				
		
		$size = sizeof($res);
		
		// order		
		$numeric = in_array($a_order_field, array("counter", "date", "week", "weekday"));		
		$res = ilUtil::sortArray($res, $a_order_field, $a_order_direction, $numeric);
				
		// offset/limit		
		$res = array_splice($res, $a_offset, $a_limit);
		
		return array("data"=>$res, "counter"=>$size);
	}
	
	/**
	 * Get all users who have reservations for object(s)
	 * 
	 * @param array $a_object_ids
	 * @return array
	 */
	public static function getUserFilter(array $a_object_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT ud.usr_id,ud.lastname,ud.firstname,ud.login".
			" FROM usr_data ud ".
			" LEFT JOIN booking_reservation r ON (r.user_id = ud.usr_id)".
			" WHERE ud.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer").
			" AND ".$ilDB->in("r.object_id", $a_object_ids, "", "integer").
			" ORDER BY ud.lastname,ud.firstname";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{			
			$res[$row["usr_id"]] = $row["lastname"].", ".$row["firstname"].
				" (".$row["login"].")";
		}
				
		return $res;		
	}
	
	/**
	 * List all reservations
	 * @param	array	$a_object_ids
	 * @param	int		$a_limit
	 * @param	int		$a_offset
	 * @param	array	$filter
	 * @param	array	$a_group_id
	 * @return	array
	 */
	/*
	static function getGroupedList($a_object_ids, $a_limit = 10, $a_offset = 0, array $filter = null, $a_group_id = null)
	{
		global $ilDB;
		
		// CURRENTLY UNUSED!!!
		return;
		
		// find matching groups / reservations
		
		$sql = 'SELECT booking_reservation_id, group_id'.
			' FROM booking_reservation';

		$where = array($ilDB->in('object_id', $a_object_ids, '', 'integer'));		
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
		if($filter['user_id'])
		{
			$where[] = 'user_id = '.$ilDB->quote($filter['user_id'], 'integer');
		}		
		if($a_group_id)
		{
			$where[] = 'group_id = '.$ilDB->quote(substr($a_group_id, 1), 'integer');
		}		
		if(sizeof($where))
		{
			$sql .= ' WHERE '.implode(' AND ', $where);		
		}
		
		$grp_ids = $rsv_ids = array();
		$set = $ilDB->query($sql);			
		while($row = $ilDB->fetchAssoc($set))
		{	
			if($row["group_id"])
			{
				$grp_ids[] = $row["group_id"];
			}			
			else 
			{
				$rsv_ids[] = $row["booking_reservation_id"];
			}			
		}				
		
		$res = array();
		
		// get complete groups (and/or reservations)
		
		if($grp_ids || $rsv_ids)
		{		
			$grp_ids = array_unique($grp_ids);
			
			// if result is on last page, reduce limit to entries on last page
			$max_page = sizeof($grp_ids)+sizeof($rsv_ids);
			$max_page = min($a_limit, $max_page-$a_offset);
			
			$sql = 'SELECT r.*,o.title'.
				' FROM booking_reservation r'.
				' JOIN booking_object o ON (o.booking_object_id = r.object_id)';
			
			$where = array();			
			if($grp_ids)
			{
				$where[] = $ilDB->in('group_id', $grp_ids, '', 'integer');
			}
			if($rsv_ids)
			{
				$where[] = $ilDB->in('booking_reservation_id', $rsv_ids, '', 'integer');
			}

			$sql .= ' WHERE ('.implode(' OR ', $where).')'.
				' ORDER BY date_from DESC, booking_reservation_id DESC';
			
			$set = $ilDB->query($sql);
			$grps = array();
			$counter = 0;		
			while($row = $ilDB->fetchAssoc($set))
			{							
				if($row["group_id"] && !$a_group_id)
				{										
					if(!isset($grps[$row["group_id"]]))
					{
						$grps[$row["group_id"]] = 1;
						$counter++;
					}
					else
					{
						$grps[$row["group_id"]]++;		
					}
				}
				else
				{				
					$counter++;
				}								
	
				if($a_group_id || 					
					($counter > $a_offset && 
						(sizeof($res) < $max_page ||
							// if group is current page we have to get all group entries, regardless of booking period
							($row["group_id"] && isset($res["g".$row["group_id"]])))))
				{
					if($row["group_id"] && !$a_group_id)
					{						
						$group_id = "g".$row["group_id"];
						$res[$group_id]["group_id"] = $group_id;
						$res[$group_id]["details"][] = $row;
					}
					else
					{
						unset($row["group_id"]);
						$res[] = $row;
					}				
				}					
			}
		}
		
		include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
	 
		foreach($res as $idx => $item)
		{
			if(isset($item["details"]))
			{
				$res[$idx]["date_from"] = null;
				$res[$idx]["date_to"] = null;	
	 
				$weekdays = $week_counter = array();
				$recur = $last = 0;			
				
				foreach($item["details"] as $detail)
				{
					// same for each item
					$res[$idx]["user_id"] = $detail["user_id"];
					$res[$idx]["object_id"] = $detail["object_id"];
					$res[$idx]["title"] = $detail["title"];
					$res[$idx]["booking_reservation_id"] = $detail["booking_reservation_id"];
					
					// recurrence/weekdays 
					$sortkey = date("wHi", $detail["date_from"])."_".date("wHi", $detail["date_to"]);				
					$weekdays[$sortkey] = ilCalendarUtil::_numericDayToString(date("w", $detail["date_from"]), false).
						", ".date("H:i", $detail["date_from"]).
						" - ".date("H:i", $detail["date_to"]);		
					
					if($detail["status"] != self::STATUS_CANCELLED)
					{
						$week_counter[$sortkey][date("WHi", $detail["date_from"])."_".date("WHi", $detail["date_to"])]++;
					}
					else if(!isset($week_counter[$sortkey][date("WHi", $detail["date_from"])."_".date("WHi", $detail["date_to"])]))
					{
						$week_counter[$sortkey][date("WHi", $detail["date_from"])."_".date("WHi", $detail["date_to"])] = 0;
					}
					
					if($last && $last-$detail["date_to"] > $recur)
					{
						$recur = $last-$detail["date_to"];
					}					
					
					// min/max period
					if(!$res[$idx]["date_from"] || $detail["date_from"] < $res[$idx]["date_from"])
					{
						$res[$idx]["date_from"] = $detail["date_from"];
					}
					if(!$res[$idx]["date_to"] || $detail["date_to"] > $res[$idx]["date_to"])
					{
						$res[$idx]["date_to"] = $detail["date_to"];
					}			
					
					$last = $detail["date_to"];
				}
				
				if(sizeof($item["details"]) > 1)
				{			
					$weekdays = array_unique($weekdays);					
					ksort($weekdays);
					
					foreach($weekdays as $week_id => $weekday)
					{
						$min = min($week_counter[$week_id]);
						$max = max($week_counter[$week_id]);
						if($min == $max)
						{
							$weekdays[$week_id] .= " (".$min.")";
						}
						else
						{
							$weekdays[$week_id] .= " (".$min."-".$max.")";
						}
					}					
					
					
					$res[$idx]["weekdays"] = array_values($weekdays);
					if($recur)
					{
						if(date("YW", $res[$idx]["date_to"]) != date("YW", $res[$idx]["date_from"]))
						{
							$recur = ceil(($recur/(60*60*24))/7); 
						}
						else
						{
							$recur = 0;
						}
					}
					$res[$idx]["recurrence"] = (int)$recur;	
	 					
					$res[$idx]["booking_reservation_id"] = $idx;								
					$res[$idx]["title"] .= " (".sizeof($item["details"]).")";
					
				}
				else
				{
					// undo grouping
					$res[$idx] = array_pop($item["details"]);
					unset($res[$idx]["group_id"]);
				}				
			}			
		}
		
		$res = ilUtil::sortArray($res, "date_from", "desc", true);
		
		return array('data'=>$res, 'counter'=>$counter);
	}
	*/

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
	
	function getCalendarEntry()
	{
		global $ilDB;
		
		include_once 'Services/Calendar/classes/class.ilCalendarCategory.php';
		
		$set = $ilDB->query("SELECT ce.cal_id FROM cal_entries ce".
			" JOIN cal_cat_assignments cca ON ce.cal_id = cca.cal_id".
			" JOIN cal_categories cc ON cca.cat_id = cc.cat_id".
			" JOIN booking_reservation br ON ce.context_id  = br.booking_reservation_id".
			" WHERE cc.obj_id = ".$ilDB->quote($this->getUserId(),'integer').
			" AND br.user_id = ".$ilDB->quote($this->getUserId(),'integer').
			" AND cc.type = ".$ilDB->quote(ilCalendarCategory::TYPE_BOOK,'integer').
			" AND ce.context_id = ".$ilDB->quote($this->getId(), 'integer'));
		$row = $ilDB->fetchAssoc($set);
		return $row["cal_id"];		
	}
	
	/**
	 * Get reservation ids from aggregated id for cancellation
	 * 
	 * @param int $a_obj_id
	 * @param int $a_user_id
	 * @param int $a_from
	 * @param int $a_to
	 * @return array
	 */
	public static function getCancelDetails($a_obj_id, $a_user_id, $a_from, $a_to)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT booking_reservation_id".
			" FROM booking_reservation".
			" WHERE object_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND date_from = ".$ilDB->quote($a_from, "integer").
			" AND date_to = ".$ilDB->quote($a_to, "integer").
			" AND (status IS NULL".
			" OR status <> ".$ilDB->quote(self::STATUS_CANCELLED, "integer").")";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["booking_reservation_id"];
		}
		
		return $res;
	}
}

?>