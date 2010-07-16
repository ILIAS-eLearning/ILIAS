<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* registration for calendar appointments
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarRegistration
{
	private $appointment_id = 0;
	
	private $registered = array();
	
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($a_appointment_id)
	{
		$this->appointment_id = $a_appointment_id;
		
		$this->read();
	}
	
	/**
	 * Delete all user registrations
	 * @param object $a_usr_id
	 * @return 
	 */
	public static function deleteByUser($a_usr_id)
	{
		global $ilDB;

	 	$query = "DELETE FROM cal_registrations ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id,'integer');
		$ilDB->manipulate($query);
	}
	
	public static function deleteByAppointment($a_cal_id)
	{
		global $ilDB;

	 	$query = "DELETE FROM cal_registrations ".
			"WHERE cal_id = ".$ilDB->quote($a_cal_id,'integer');
		$ilDB->manipulate($query);
	}
	
	/**
	 * Get appoinmtent id
	 * @return int app_id
	 */
	public function getAppointmentId()
	{
		return $this->appointment_id;
	}
	
	/**
	 * Get all registered users
	 * @return 
	 */
	public function getRegisteredUsers()
	{
		return (array) $this->registered;
	}
	
	/**
	 * Check if one user is registered
	 * @param object $a_usr_id
	 * @return bool
	 */
	public function isRegistered($a_usr_id)
	{
		return in_array($a_usr_id, (array) $this->registered);
	}
	
	/**
	 * Register one user
	 * @param int $a_usr_id
	 * @return 
	 */
	public function register($a_usr_id)
	{
		global $ilDB;
		
		$this->unregister($a_usr_id);
		
		$query = "INSERT INTO cal_registrations (cal_id,usr_id) ".
			"VALUES ( ".
			$ilDB->quote($this->getAppointmentId(),'integer').", ".
			$ilDB->quote($a_usr_id,'integer').
			")";
		$ilDB->manipulate($query);
		
		$this->registered[] = $a_usr_id;
		return true;
	}
	
	/**
	 * unregister one user
	 * @param int $a_usr_id
	 * @return 
	 */
	public function unregister($a_usr_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_registrations ".
			"WHERE cal_id = ".$ilDB->quote($this->getAppointmentId(),'integer').' '.
			"AND usr_id = ".$ilDB->quote($a_usr_id,'integer');
		$res = $ilDB->manipulate($query);
	}
	
	
	/**
	 * Read registration
	 * @return 
	 */
	protected function read()
	{
		global $ilDB;

		if(!$this->getAppointmentId())
		{
			return false;
		}
		
		$query = "SELECT * FROM cal_registrations WHERE cal_id = ".$ilDB->quote($this->getAppointmentId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->registered[] = $row->usr_id;
		}
	}
}
?>