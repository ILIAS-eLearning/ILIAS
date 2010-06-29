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
	
	private $title = '';
	private $description = '';
	private $location = '';
	
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
	 * Set title
	 * @param object $a_title
	 * @return 
	 */
	public function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	/**
	 * get title
	 * @return 
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * set description
	 * @param object $a_des
	 * @return 
	 */
	public function setDescription($a_des)
	{
		$this->description = $a_des;		
	}
	
	/**
	 * get description
	 * @return 
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * set location
	 * @param object $a_location
	 * @return 
	 */
	public function setLocation($a_location)
	{
		$this->location = $a_location;
	}

	/**
	 * get location 
	 * @return 
	 */
	public function getLocation()
	{
		return $this->location;
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
		$query = 'INSERT INTO booking_entry (booking_id,obj_id,title,description,location,deadline,num_bookings) '.
			"VALUES ( ".
			$ilDB->quote($this->getId(),'integer').', '.
			$ilDB->quote($this->getObjId(),'integer').', '.
			$ilDB->quote($this->getTitle(),'text').', '.
			$ilDB->quote($this->getDescription(),'text').', '.
			$ilDB->quote($this->getLocation(),'text').', '.
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
			" title = ".$ilDB->quote($this->getTitle(),'text').", ".
			" description = ".$ilDB->quote($this->getDescription(),'text').", ".
			" location = ".$ilDB->quote($this->getLocation(),'text').", ".
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
			$this->setTitle($row['title']);
			$this->setDescription($row['description']);
			$this->setLocation($row['location']);
			$this->setDeadlineHours($row['deadline']);
			$this->setNumberOfBookings($row['num_bookings']);
		}
		return true;
	}
}
?>