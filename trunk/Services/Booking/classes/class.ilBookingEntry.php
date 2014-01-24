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
	private $target_obj_ids = array();
	private $booking_group = 0;
	
	
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
	 * Reset booking group (in case of deletion)
	 * @global type $ilDB
	 * @param type $a_group_id
	 * @return boolean
	 */
	public static function resetGroup($a_group_id)
	{
		global $ilDB;
		
		$query = 'UPDATE booking_entry SET booking_group = '.$ilDB->quote(0,'integer').' '.
				'WHERE booking_group = '.$ilDB->quote($a_group_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Lookup bookings if user
	 * @param type $a_app_ids
	 * @param type $a_usr_id
	 */
	public static function lookupBookingsOfUser($a_app_ids, $a_usr_id, ilDateTime $start = null)
	{
		global $ilDB;
		
		$query = 'SELECT entry_id FROM booking_user '.
				'WHERE '.$ilDB->in('entry_id',$a_app_ids,false,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_usr_id,'integer');
		
		$res = $ilDB->query($query);
		
		$booked_entries = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$booked_entries[] = $row->entry_id;
		}
		return $booked_entries;
	}
	
	/**
	 * Set id
	 * @param int $a_id
	 * @return 
	 */
	protected function setId($a_id)
	{
		$this->id = (int)$a_id;
	} 
	
	/**
	 * Get id
	 * @return 
	 */
	public function getId()
	{
		return $this->id;
	}
	
	public function setBookingGroup($a_id)
	{
		$this->booking_group = $a_id;
	}
	
	public function getBookingGroup()
	{
		return $this->booking_group;
	}
	
	/**
	 * Set obj id
	 * @param int $a_id
	 * @return 
	 */
	public function setObjId($a_id)
	{
		$this->obj_id = (int)$a_id;
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
	 * @param int $a_hours
	 * @return 
	 */
	public function setDeadlineHours($a_hours)
	{
		$this->deadline = (int)$a_hours;
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
	 * @param int $a_num
	 * @return 
	 */
	public function setNumberOfBookings($a_num)
	{
		$this->num_bookings = (int)$a_num;
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
	 * set target object id
	 * @param int $a_obj_id
	 * @return
	 */
	public function setTargetObjIds($a_obj_id)
	{
		$this->target_obj_ids = $a_obj_id;
	}

	/**
	 * get target object id
	 * @return int
	 */
	public function getTargetObjIds()
	{
		return $this->target_obj_ids;
	}
	
	/**
	 * Check if target ref id is visible
	 * @param type $a_ref_id
	 */
	public function isTargetObjectVisible($a_ref_id)
	{				
		// no course/group filter
		if(!$this->getTargetObjIds())
		{			
			return true;
		}
		
		$obj_id = ilObject::_lookupObjId($a_ref_id);
		return in_array($obj_id, $this->getTargetObjIds());
	}
	
	/**
	 * Save a new booking entry
	 * @return 
	 */
	public function save()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId('booking_entry'));
		$query = 'INSERT INTO booking_entry (booking_id,obj_id,deadline,num_bookings,booking_group) '.
			"VALUES ( ".
			$ilDB->quote($this->getId(),'integer').', '.
			$ilDB->quote($this->getObjId(),'integer').', '.
			$ilDB->quote($this->getDeadlineHours(),'integer').', '.
			$ilDB->quote($this->getNumberOfBookings(),'integer').','.
			$ilDB->quote($this->getBookingGroup(),'integer').' '.
			") ";
		$ilDB->manipulate($query);
		
		foreach((array) $this->target_obj_ids as $obj_id)
		{
			$query = 'INSERT INTO booking_obj_assignment (booking_id, target_obj_id) '.
					'VALUES( '.
					$ilDB->quote($this->getId(),'integer').', '.
					$ilDB->quote($obj_id,'integer').' '.
					')';
			$ilDB->manipulate($query);
		}
		return true;
	}
	
	/**
	 * Update an existing booking entry
	 * @return 
	 */
	public function update()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return false;
		}
		
		$query = "UPDATE booking_entry SET ".
			"SET obj_id = ".$ilDB->quote($this->getObjId(),'integer').", ".
			" deadline = ".$ilDB->quote($this->getDeadlineHours(),'integer').", ".
			" num_bookings = ".$ilDB->quote($this->getNumberOfBookings(),'integer').', '.
			'booking_group = '.$ilDB->quote($this->getBookingGroup(),'integer');
		$ilDB->manipulate($query);

		// obj assignments
		$query = 'DELETE FROM booking_obj_assignment '.
				'WHERE booking_id = '.$ilDB->quote($this->getId(),'integer');
		$ilDB->manipulate($query);
		
		foreach((array) $this->target_obj_ids as $obj_id)
		{
			$query = 'INSERT INTO booking_obj_assignment (booking_id, target_obj_id) '.
					'VALUES( '.
					$ilDB->quote($this->getId(),'integer').', '.
					$ilDB->quote($obj_id,'integer').' '.
					')';
			$ilDB->manipulate($query);
		}
		return true;
	}
	
	/**
	 * Delete
	 * @return 
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM booking_entry ".
			"WHERE booking_id = ".$ilDB->quote($this->getId(),'integer');
		$ilDB->manipulate($query);
		
		$query = 'DELETE FROM booking_obj_assignment '.
				'WHERE booking_id = '.$ilDB->quote($this->getId(),'integer');
		$ilDB->manipulate($query);

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
			$this->setBookingGroup($row['booking_group']);
		}
		
		$query = 'SELECT * FROM booking_obj_assignment '.
				'WHERE booking_id = '.$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->query($query);
		
		$this->target_obj_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->target_obj_ids[] = $row->target_obj_id;
		}
		
		return true;
	}

	/**
	 * check if current (or given) user is entry owner
	 * @param	int		$a_user_id
	 * @return	bool
	 */
	public function isOwner($a_user_id = NULL)
	{
		global $ilUser;

		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}

		if($this->getObjId() == $a_user_id)
		{
			return true;
		}
		return false;
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

		$ilDB->query($q = 'DELETE FROM booking_entry WHERE '.$ilDB->in('booking_id', $used, true, 'integer'));
		$ilDB->query($q = 'DELETE FROM booking_obj_assignment WHERE '.$ilDB->in('booking_id',$used,true,'integer'));
	}

	/**
	 * Get instance by calendar entry
	 * @param	int		$id
	 * @return ilBookingEntry
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
	 * @param	array	$a_obj_ids
	 * @param	int		$a_target_obj_id
	 * @return	array
	 */
	public static function isBookable(array $a_obj_ids, $a_target_obj_id = NULL)
	{
		global $ilDB;
		
		if($a_target_obj_id)
		{
			$query = 'SELECT DISTINCT(obj_id) FROM booking_entry be '.
					'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id '.
					'WHERE '.$ilDB->in('obj_id', $a_obj_ids, false, 'integer').' '.
					'AND bo.target_obj_id = '.$ilDB->quote($a_target_obj_id,'integer');
		}
		else
		{
			$query = 'SELECT DISTINCT(obj_id) FROM booking_entry be '.
					'WHERE '.$ilDB->in('obj_id', $a_obj_ids, false, 'integer').' ';
		}

		$res = $ilDB->query($query);
		$all = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$all[] = $row->obj_id;
		}
		return $all;
	}
	
	/**
	 * Consultation hours are offered if
	 * 1) consultation hour owner is admin or tutor and no object assignment
	 * 2) object is assigned to consultation hour
	 * @param type $a_obj_ids
	 * @param type $a_user_ids
	 * @return array user ids
	 */
	public static function lookupBookableUsersForObject($a_obj_id, $a_user_ids)
	{
		global $ilDB;
		
		$query = 'SELECT be.obj_id bobj FROM booking_entry be '.
				'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id '.
				'JOIN cal_entries ce on be.booking_id = ce.context_id '.
				'JOIN cal_cat_assignments cca on ce.cal_id = cca.cal_id '.
				'JOIN cal_categories cc on cca.cat_id = cc.cat_id '.
				'WHERE '.$ilDB->in('be.obj_id', (array) $a_user_ids,false,'integer'). ' '.
				'AND '.$ilDB->in('bo.target_obj_id', (array) $a_obj_id,false,'integer'). ' '.
				'AND cc.obj_id = be.obj_id '.
				'AND cc.type = '. $ilDB->quote(ilCalendarCategory::TYPE_CH,'integer').' ';
		
		$res = $ilDB->query($query);
		
		$objs = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!in_array($row->bobj,$objs))
			{
				$objs[] = $row->bobj;
			}
		}
		
		// non filtered booking entries
		$query = 'SELECT be.obj_id bobj FROM booking_entry be '.
				'LEFT JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id '.
				'JOIN cal_entries ce on be.booking_id = ce.context_id '.
				'JOIN cal_cat_assignments cca on ce.cal_id = cca.cal_id '.
				'JOIN cal_categories cc on cca.cat_id = cc.cat_id '.
				'WHERE bo.booking_id IS NULL '.
				'AND '.$ilDB->in('be.obj_id', (array) $a_user_ids,false,'integer'). ' '.
				'AND cc.obj_id = be.obj_id '.
				'AND cc.type = '. $ilDB->quote(ilCalendarCategory::TYPE_CH,'integer').' ';
		
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!in_array($row->bobj,$objs))
			{
				$objs[] = $row->bobj;
			}
		}
				
		return $objs;
	}
	
	/**
	 * Check if object has assigned consultation hour appointments
	 * @param type $a_obj_id
	 * @param type $a_usr_id
	 */
	public static function hasObjectBookingEntries($a_obj_id, $a_usr_id)
	{
		global $ilDB;
		
		$user_restriction = '';
		if($a_usr_id)
		{
			$user_restriction = 'AND obj_id = '.$ilDB->quote($a_usr_id). ' ';
		}
		
		
		$query = 'SELECT be.booking_id FROM booking_entry be '.
				'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id '.
				'WHERE bo.target_obj_id = '.$ilDB->quote($a_obj_id,'integer').' '.
				$user_restriction;
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}
	
	public static function lookupBookingMessage($a_entry_id, $a_usr_id)
	{
		global $ilDB;
		
		$query = 'SELECT * from booking_user '.
				'WHERE entry_id = '.$ilDB->quote($a_entry_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_usr_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->booking_message;
		}
		return '';
	}
	
	/**
	 * Write booking message
	 * @param type $a_entry_id
	 * @param type $a_usr_id
	 * @param type $a_message
	 */
	public static function writeBookingMessage($a_entry_id, $a_usr_id, $a_message)
	{
		global $ilDB;
		
		$query = 'UPDATE booking_user SET '.
				'booking_message = '.$ilDB->quote($a_message,'text').' '.
				'WHERE entry_id = '.$ilDB->quote($a_entry_id,'integer').' '.
				'AND user_id = '.$ilDB->quote($a_usr_id,'integer');
		
		$GLOBALS['ilLog']->write(__METHOD__.': '.$query);
		
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * get current number of bookings
	 * @param	int	$a_entry_id
	 * @return	int
	 */
	public function getCurrentNumberOfBookings($a_entry_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT COUNT(*) AS counter FROM booking_user'.
			' WHERE entry_id = '.$ilDB->quote($a_entry_id, 'integer'));
		$row = $ilDB->fetchAssoc($set);
		return (int)$row['counter'];
	}

	/**
	 * get current bookings
	 * @param	int	$a_entry_id
	 * @return	array
	 */
	public function getCurrentBookings($a_entry_id)
	{
		global $ilDB;

		$set = $ilDB->query('SELECT user_id FROM booking_user'.
			' WHERE entry_id = '.$ilDB->quote($a_entry_id, 'integer'));
	    $res = array();
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row['user_id'];
		}
		return $res;
	}
	
	/**
	 * Lookup booked users for appointment
	 * @global type $ilDB
	 * @param type $a_app_id
	 * @return type
	 */
	public static function lookupBookingsForAppointment($a_app_id)
	{
		global $ilDB;

		$query = 'SELECT user_id FROM booking_user '.
				'WHERE entry_id = '.$ilDB->quote($a_app_id, 'integer');
		$res = $ilDB->query($query);
		
		$users = array();
		while($row = $ilDB->fetchObject($res))
		{
			$users[] = $row->user_id;
		}
		return $users;
	}
	
	/**
	 * Lookup booking for an object and user
	 * @param type $a_obj_id
	 * @param type $a_usr_id
	 * @return array
	 */
	public static function lookupBookingsForObject($a_obj_id, $a_usr_id)
	{
		global $ilDB;
		

		$query = 'SELECT bu.user_id, starta, enda FROM booking_user bu '.
				'JOIN cal_entries ca ON entry_id = ca.cal_id '.
				'JOIN booking_entry be ON context_id = booking_id '.
				'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id '.
				'WHERE bo.target_obj_id = '.$ilDB->quote($a_obj_id,'integer').' '.
				'AND be.obj_id = '.$ilDB->quote($a_usr_id).' '.
				'ORDER BY starta';
		$res = $ilDB->query($query);
		
		$bookings = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$dt = new ilDateTime($row->starta,IL_CAL_DATETIME,  ilTimeZone::UTC);
			$dt_end = new ilDateTime($row->enda,IL_CAL_DATETIME, ilTimeZone::UTC);
			$bookings[$row->user_id][] = array(
				'dt' => $dt->get(IL_CAL_UNIX),
				'dtend' => $dt_end->get(IL_CAL_UNIX),
				'owner' => $a_usr_id);
					
		}
		return $bookings;
	}

	/**
	 * Lookup bookings for own and managed consultation hours of an object
	 * @param type $a_obj_id
	 * @param type $a_usr_id
	 * @return array
	 */
	public static function lookupManagedBookingsForObject($a_obj_id,$a_usr_id)
	{
		$bookings = self::lookupBookingsForObject($a_obj_id, $a_usr_id);
		include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourUtils.php';
		foreach(ilConsultationHourUtils::lookupManagedUsers($a_usr_id) as $managed_user_id)
		{
			foreach(self::lookupBookingsForObject($a_obj_id, $managed_user_id) as $booked_user => $booking)
			{
				$fullname = ilObjUser::_lookupFullname($managed_user_id);
				foreach($booking as $booking_entry)
				{
					$booking_entry['explanation'] = '('.$fullname.')';
					$bookings[$booked_user][] = $booking_entry;
				}
			}
		}
		return $bookings;
	}


		/**
	 * get current number of bookings
	 * @param	int		$a_entry_id
	 * @param	int		$a_user_id
	 * @return	bool
	 */
	public function hasBooked($a_entry_id, $a_user_id = NULL)
	{
		global $ilUser, $ilDB;

		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}

		$query = 'SELECT COUNT(*) AS counter FROM booking_user'.
			' WHERE entry_id = '.$ilDB->quote($a_entry_id, 'integer').
			' AND user_id = '.$ilDB->quote($a_user_id, 'integer');
		$set = $ilDB->query($query);
	    $row = $ilDB->fetchAssoc($set);
		
		return (bool) $row['counter'];
	}

	/**
	 * get current number of bookings
	 * @param	int		$a_entry_id (calendar entry)
	 * @param	bool	$a_check_current_user
	 * @return	bool
	 */
	public function isBookedOut($a_entry_id, $a_check_current_user = false)
	{
		global $ilUser;

		if($this->getNumberOfBookings() == $this->getCurrentNumberOfBookings($a_entry_id))
		{
			// check against current user
			if($a_check_current_user)
			{
				if($this->hasBooked($a_entry_id))
				{
					return false;
				}
		        if($ilUser->getId() == $this->getObjId())
				{
					return false;
				}
			}
			return true;
		}

		$deadline = $this->getDeadlineHours();
		if($deadline)
		{
			include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
			$entry = new ilCalendarEntry($a_entry_id);
			if(time()+($deadline*60*60) > $entry->getStart()->get(IL_CAL_UNIX))
			{
				return true;
			}
		}	
		return false;
	}
	
	/**
	 * Check if a calendar appointment is bookable for a specific user
	 * @param type $a_cal_entry_id
	 * @param type $a_user_id
	 * @return bool
	 */
	public function isAppointmentBookableForUser($a_app_id, $a_user_id)
	{
		// #12025
		if($a_user_id == ANONYMOUS_USER_ID)
		{
			return false;
		}
		
		// Check max bookings
		if($this->getNumberOfBookings() <= $this->getCurrentNumberOfBookings($a_app_id))
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': Number of bookings exceeded');
			return false;
		}

		// Check deadline
		$dead_limit = new ilDateTime(time(),IL_CAL_UNIX);
		$dead_limit->increment(IL_CAL_HOUR,$this->getDeadlineHours());
		
		include_once 'Services/Calendar/classes/class.ilCalendarEntry.php';
		$entry = new ilCalendarEntry($a_app_id);
		if(ilDateTime::_after($dead_limit, $entry->getStart()))
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': Deadline reached');
			return false;
		}
		
		// Check group restrictions
		if(!$this->getBookingGroup())
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': No booking group');
			return true;
		}
		include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourAppointments.php';
		$group_apps = ilConsultationHourAppointments::getAppointmentIdsByGroup(
				$this->getObjId(),
				$this->getBookingGroup()
		);
		
		// Number of bookings in group
		$bookings = self::lookupBookingsOfUser($group_apps, $a_user_id);
		
		include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroups.php';
		#$GLOBALS['ilLog']->write(__METHOD__.': '.ilConsultationHourGroups::lookupMaxBookings($this->getBookingGroup()));
		
		if(count($bookings) >= ilConsultationHourGroups::lookupMaxBookings($this->getBookingGroup()))
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': Personal booking limit reached');
			return false;
		}
		#$GLOBALS['ilLog']->write(__METHOD__.': Is bookable!');
		return true;
	}

	/**
	 * book calendar entry for user
	 * @param	int	$a_entry_id
	 * @param	int	$a_user_id
	 */
	public function book($a_entry_id, $a_user_id = false)
	{
		global $ilUser, $ilDB;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}

		if(!$this->hasBooked($a_entry_id, $a_user_id))
		{
			$ilDB->manipulate('INSERT INTO booking_user (entry_id, user_id, tstamp)'.
				' VALUES ('.$ilDB->quote($a_entry_id, 'integer').','.
				$ilDB->quote($a_user_id, 'integer').','.$ilDB->quote(time(), 'integer').')');

			include_once 'Services/Calendar/classes/class.ilCalendarMailNotification.php';
			$mail = new ilCalendarMailNotification();
			$mail->setAppointmentId($a_entry_id);
			$mail->setRecipients(array($a_user_id));
			$mail->setType(ilCalendarMailNotification::TYPE_BOOKING_CONFIRMATION);
			$mail->send();
		}
		return true;
	}

	/**
	 * cancel calendar booking for user
	 * @param	int	$a_entry_id
	 * @param	int	$a_user_id
	 */
	public function cancelBooking($a_entry_id, $a_user_id = false)
	{
		global $ilUser, $ilDB;

		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}

		// @todo do not send mails about past consultation hours
		$entry = new ilCalendarEntry($a_entry_id);
		
		$past = ilDateTime::_before($entry->getStart(), new ilDateTime(time(),IL_CAL_UNIX));
		if($this->hasBooked($a_entry_id, $a_user_id) && !$past)
		{
			include_once 'Services/Calendar/classes/class.ilCalendarMailNotification.php';
			$mail = new ilCalendarMailNotification();
			$mail->setAppointmentId($a_entry_id);
			$mail->setRecipients(array($a_user_id));
			$mail->setType(ilCalendarMailNotification::TYPE_BOOKING_CANCELLATION);
			$mail->send();
		}
		$this->deleteBooking($a_entry_id,$a_user_id);
		return true;
	}
	
	/**
	 * Delete booking
	 * @global type $ilDB
	 * @param type $a_entry_id
	 * @param type $a_user_id
	 * @return boolean
	 */
	public function deleteBooking($a_entry_id, $a_user_id)
	{
		global $ilDB;
	
		$query = 'DELETE FROM booking_user ' .
			'WHERE entry_id = '.$ilDB->quote($a_entry_id, 'integer').' '.
			'AND user_id = '.$ilDB->quote($a_user_id, 'integer');
		$ilDB->manipulate($query);
		return true;
	}
}

?>