<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Booking definition
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesBooking
*/
class ilBookingEntry
{
	private $id = 0;
	private $obj_id = 0;
	
	private $deadline = 0;
	private $num_bookings = 1; 
	
	
	/**
	 * Constructor
	 */
	public function __construct($a_booking_id = 0)
	{
		$this->setId($a_booking_id);
		if($this->getId())
		{
			$this->read();
		}
	}
	
	/**
	 * Set id
	 * @param object $a_id
	 * @return 
	 */
	protected function setId($a_id)
	{
		$this->id = $a_id;
	} 
	
	/**
	 * Get id
	 * @return 
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set obj id
	 * @param object $a_id
	 * @return 
	 */
	public function setObjId($a_id)
	{
		$this->obj_id = $a_id;
	}
	
	/**
	 * get obj id
	 * @return 
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}
	
	/**
	 * set deadline hours
	 * @param object $a_hours
	 * @return 
	 */
	public function setDeadlineHours($a_hours)
	{
		$this->deadline = $a_hours;
	}
	
	/**
	 * get deadline hours
	 * @return 
	 */
	public function getDeadlineHours()
	{
		return $this->deadline;
	}
	
	/**
	 * set number of bookings
	 * @param object $a_num
	 * @return 
	 */
	public function setNumberOfBookings($a_num)
	{
		$this->num_bookings = $a_num;
	}
	
	/**
	 * get number of bookings
	 * @return 
	 */
	public function getNumberOfBookings()
	{
		return $this->num_bookings;
	}
	
	/**
	 * Save a new booking entry
	 * @return 
	 */
	public function save()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId('booking_entry'));
		$query = 'INSERT INTO booking_entry (booking_id,obj_id,deadline,num_bookings) '.
			"VALUES ( ".
			$ilDB->quote($this->getId(),'integer').', '.
			$ilDB->quote($this->getObjId(),'integer').', '.
			$ilDB->quote($this->getDeadlineHours(),'integer').', '.
			$ilDB->quote($this->getNumberOfBookings(),'integer').
			") ";
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Update an existing booking entry
	 * @return 
	 */
	public function update()
	{
		if(!$this->getId())
		{
			return false;
		}
		
		$query = "UPDATE booking_entry SET ".
			"SET obj_id = ".$ilDB->quote($this->getObjId(),'integer').", ".
			" deadline = ".$ilDB->quote($this->getDeadlineHours(),'integer').", ".
			" num_bookings = ".$ilDB->quote($this->getNumberOfBookings(),'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Delete
	 * @return 
	 */
	public function delete()
	{
		$query = "DELETE FROM booking_entry ".
			"WHERE booking_id = ".$ilDB->quote($this->getId(),'integer');
		$ilDB->manipulate();
		return true;
	}

	/**
	 * Read settings from db
	 * @return 
	 */
	protected function read()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return false;
		}
		
		$query = "SELECT * FROM booking_entry ".
			"WHERE booking_id = ".$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->setObjId($row['obj_id']);
			$this->setDeadlineHours($row['deadline']);
			$this->setNumberOfBookings($row['num_bookings']);
		}
		return true;
	}


	/**
	 * Remove unused booking entries
	 */
	public static function removeObsoleteEntries()
    {
		global $ilDB;

		$set = $ilDB->query('SELECT DISTINCT(context_id) FROM cal_entries e'.
			' JOIN cal_cat_assignments a ON (e.cal_id = a.cal_id)'.
			' JOIN cal_categories c ON (a.cat_id = c.cat_id) WHERE c.type = '.$ilDB->quote(ilCalendarCategory::TYPE_CH, 'integer'));
		$used = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$used[] = $row['context_id'];
		}

		return $ilDB->query('DELETE FROM booking_entry WHERE '.$ilDB->in('booking_id', $used, true, 'integer'));
	}

	/**
	 * Get instance by calendar entry
	 * @param	int		$id
	 * @return self 
	 */
	public static function getInstanceByCalendarEntryId($a_id)
	{
		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		$cal_entry = new ilCalendarEntry($a_id);
		$booking_id = $cal_entry->getContextId();
		if($booking_id)
		{
			return new self($booking_id);
		}
	}

	/**
	 * Which objects are bookable?
	 *
	 * @param	array	$users
	 * @return	array
	 */
	public static function isBookable(array $a_obj_ids)
	{
		global $ilDB;

		if(sizeof($a_obj_ids))
		{
			$set = $ilDB->query('SELECT DISTINCT(obj_id) FROM booking_entry'.
				' WHERE '.$ilDB->in('obj_id', $a_obj_ids, false, 'integer'));
			$all = array();
			while($row = $ilDB->fetchAssoc($set))
			{
				$all[] = $row['obj_id'];
			}
			return $all;
		}
	}

	/**
	 * get current number of bookings
	 * @return
	 */
	public function getCurrentNumberOfBookings()
	{
		// :TODO:
		return 0;
	}
}
?>