<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Calendar/interfaces/interface.ilDatePeriod.php');
include_once('./Services/Calendar/classes/class.ilDate.php');
/**
* class ilSessionAppointment
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* @version $Id$
* 
* @ingroup ModulesSession
*/
class ilSessionAppointment implements ilDatePeriod
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	protected $start = null;
	protected $end = null;

	// cat-tms-patch start
	/**
	 * @var int
	 */
	protected $days_offset = null;
	// cat-tms-patch end

	var $starting_time = null;
	var $ending_time = null;

	/**
	 * Consructor
	 * @param int $a_appointment_id
	 */
	public function __construct($a_appointment_id = null)
	{
		// cat-tms-patch start
		global $ilErr,$ilDB,$lng,$tree,$DIC;

		$this->ilErr = $ilErr;
		$this->db  = $ilDB;
		$this->lng = $lng;

		$this->g_user = $DIC->user();
		$this->appointment_id = $a_appointment_id;
		$this->__read();
		// cat-tms-patch end
	}
	
	/**
	 * lookup appointment
	 *
	 * @access public
	 * @param int obj_id
	 * @static
	 */
	public static function _lookupAppointment($a_obj_id)
	{
		global $ilDB,$DIC;
		$user = $DIC->user();;
		
		$query = "SELECT * FROM event_appointment ".
			"WHERE event_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$info['fullday'] = $row->fulltime;
			
			// cat-tms-patch start
			$date = new ilDateTime($row->e_start,IL_CAL_DATETIME,'UTC');
			$info['start'] =  $date->getUnixTime();
			$date = new ilDateTime($row->e_end,IL_CAL_DATETIME,'UTC');
			$info['end'] = $date->getUnixTime();
			// cat-tms-patch end
			return $info;
		}
		return array();
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * @return array
	 * @static
	 */
	public static function lookupNextSessionByCourse($a_ref_id)
	{
		global $tree,$ilDB;
		
		
		$sessions = $tree->getChildsByType($a_ref_id,'sess');
		$obj_ids = array();
		foreach($sessions as $tree_data)
		{
			$obj_ids[] = $tree_data['obj_id'];
		}
		if(!count($obj_ids))
		{
			return false;
		}

		// Try to read the next sessions within the next 24 hours
		$now = new ilDate(time(),IL_CAL_UNIX);
		$tomorrow = clone $now;
		$tomorrow->increment(IL_CAL_DAY,2);
		
		$query = "SELECT event_id FROM event_appointment ".
			"WHERE e_start > ".$ilDB->quote($now->get(IL_CAL_DATE,'timestamp')).' '.
			"AND e_start < ".$ilDB->quote($tomorrow->get(IL_CAL_DATE,'timestamp')).' '.
			"AND ".$ilDB->in('event_id',$obj_ids,false,'integer').' '.
			"ORDER BY e_start ";
			
		$event_ids = array();
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$event_ids[] = $row->event_id;
		}
		
		if(count($event_ids))
		{
			return $event_ids;
		}
		
		// Alternativ: get next event.	
		$query = "SELECT event_id FROM event_appointment ".
			"WHERE e_start > ".$ilDB->now()." ".
			"AND ".$ilDB->in('event_id',$obj_ids,false,'integer')." ".
			"ORDER BY e_start ";
		$ilDB->setLimit(1);
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$event_id = $row->event_id;
		}
		return isset($event_id) ? array($event_id) : array();
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function lookupLastSessionByCourse($a_ref_id)
	{
		global $tree,$ilDB;
		
		$sessions = $tree->getChildsByType($a_ref_id,'sess');
		$obj_ids = array();
		foreach($sessions as $tree_data)
		{
			$obj_ids[] = $tree_data['obj_id'];
		}
		if(!count($obj_ids))
		{
			return false;
		}
		$query = "SELECT event_id FROM event_appointment ".
			"WHERE e_start < ".$ilDB->now()." ".
			"AND ".$ilDB->in('event_id',$obj_ids,false,'integer')." ".
			"ORDER BY e_start DESC ";
		$ilDB->setLimit(1);
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$event_id = $row->event_id;
		}
		return isset($event_id) ? $event_id : 0;
	}

	// Interface methods
	/**
	 * is fullday
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function isFullday()
	{
		return $this->enabledFullTime();
	}

	// cat-tms-patch start
	/**
	 * Get days offset
	 *
	 * @return int | null
	 */
	public function getDaysOffset()
	{
		return $this->days_offset;
	}

	/**
	 * Set days offset
	 *
	 * @param 	int|null 		$value
	 * @return 	void
	 */
	public function setDaysOffset($value)
	{
		assert('is_int($value) || is_null($value)');
		$this->days_offset = $value;
	}
	// cat-tms-patch end

	/**
	 * get start
	 *
	 * @access public
	 * @param
	 * @return ilDateTime
	 */
	public function getStart()
	{
		return $this->start ? $this->start : $this->start = new ilDateTime(date('Y-m-d').' 08:00:00',IL_CAL_DATETIME);
	}
	
	/**
	 * set start
	 *
	 * @access public
	 * @param object $data ilDateTime
	 */
	public function setStart($a_start)
	{
		$this->start = $a_start;
	}
	
	/**
	 * get end
	 *
	 * @access public
	 * @return ilDateTime
	 */
	public function getEnd()
	{
		return $this->end ? $this->end : $this->end = new ilDateTime(date('Y-m-d').' 16:00:00',IL_CAL_DATETIME);
	}
	
	/**
	 * set end
	 *
	 * @access public
	 * @param object $date ilDateTime
	 * @return
	 */
	public function setEnd($a_end)
	{
		$this->end = $a_end;
	}

	function setAppointmentId($a_appointment_id)
	{
		$this->appointment_id = $a_appointment_id;
	}
	function getAppointmentId()
	{
		return $this->appointment_id;
	}

	function setSessionId($a_session_id)
	{
		$this->session_id = $a_session_id;
	}
	function getSessionId()
	{
		return $this->session_id;
	}

	function setStartingTime($a_starting_time)
	{
		$this->starting_time = $a_starting_time;
		$this->start = new ilDateTime($this->starting_time,IL_CAL_UNIX);
		
	}
	function getStartingTime()
	{
		return isset($this->starting_time) ? $this->starting_time : mktime(8,0,0,date('n',time()),date('d',time()),date('Y',time()));
	}
	
	function setEndingTime($a_ending_time)
	{
		$this->ending_time = $a_ending_time;
		$this->end = new ilDateTime($this->ending_time,IL_CAL_UNIX);
	}
	function getEndingTime()
	{
		return isset($this->ending_time) ? $this->ending_time : mktime(16,0,0,date('n',time()),date('d',time()),date('Y',time()));
	}

	function toggleFullTime($a_status)
	{
		$this->fulltime = $a_status;
	}
	function enabledFullTime()
	{
		return $this->fulltime;
	}

	function formatTime()
	{
		return ilSessionAppointment::_timeToString($this->getStartingTime(),$this->getEndingTime());
	}

	function _timeToString($start,$end)
	{
		global $ilUser,$lng;

		$start = date($this->lng->txt('lang_timeformat_no_sec'),$start);
		$end = date($this->lng->txt('lang_timeformat_no_sec'),$end);
		
		return $start.' - '. $end;
	}

	public static function _appointmentToString($start,$end,$fulltime)
	{
		global $lng;

		if($fulltime)
		{
			return ilDatePresentation::formatPeriod(
				new ilDate($start,IL_CAL_UNIX),
				#new ilDate($end,IL_CAL_UNIX)).' ('.$lng->txt('event_full_time_info').')';
				new ilDate($end,IL_CAL_UNIX));
		}
		else
		{
			return ilDatePresentation::formatPeriod(
				new ilDateTime($start,IL_CAL_UNIX),
				new ilDateTime($end,IL_CAL_UNIX));
		}
	}

	function appointmentToString()
	{
		return ilSessionAppointment::_appointmentToString($this->getStartingTime(),$this->getEndingTime(),$this->enabledFullTime());
	}
	
	/**
	 * clone appointment
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function cloneObject($new_id)
	{
		$new_app = new ilSessionAppointment();
		$new_app->setSessionId($new_id);
		$new_app->setStartingTime($this->getStartingTime());
		$new_app->setEndingTime($this->getEndingTime());
		$new_app->toggleFullTime($this->isFullday());
		// cat-tms-patch start
		$new_app->setDaysOffset($this->getDaysOffset());
		// cat-tms-patch end
		$new_app->create();
		return $new_app;
	}

	// cat-tms-patch start
	function create()
	{
		global $ilDB;
		if(!$this->getSessionId())
		{
			return false;
		}
		$next_id = $ilDB->nextId('event_appointment');

		$days_offset = $this->getDaysOffset();
		if($days_offset === null) {
			$days_offset = -1;
		}

		$query = "INSERT INTO event_appointment (appointment_id,event_id,e_start,e_end,fulltime,days_offset) ".
			"VALUES( ".
			$ilDB->quote($next_id,'integer').", ".
			$ilDB->quote($this->getSessionId() ,'integer').", ".
			$ilDB->quote($this->start->get(IL_CAL_DATETIME,'','UTC'), 'timestamp').", ".
			$ilDB->quote($this->end->get(IL_CAL_DATETIME,'','UTC'),'timestamp').", ".
			$ilDB->quote($this->enabledFullTime() ,'integer').", ".
			$ilDB->quote($days_offset ,'integer').
			")";
		// cat-tms-patch end

		$this->appointment_id = $next_id;
		$res = $ilDB->manipulate($query);
		
		return true;
	}

	function update()
	{
		global $ilDB;
		
		if(!$this->getSessionId())
		{
			return false;
		}
		// cat-tms patch start
		if ($this->days_offset == null) {
			$days_offset = "-1";
		}
		else {
			$days_offset = $ilDB->quote($this->days_offset, "integer");
		}
		$query = "UPDATE event_appointment ".
			"SET event_id = ".$ilDB->quote($this->getSessionId() ,'integer').", ".
			"e_start = ".$ilDB->quote($this->getStart()->get(IL_CAL_DATETIME,'','UTC') ,'timestamp').", ".
			"e_end = ".$ilDB->quote($this->getEnd()->get(IL_CAL_DATETIME,'','UTC'), 'timestamp').", ".
			"fulltime = ".$ilDB->quote($this->enabledFullTime() ,'integer').", ".
			"days_offset = $days_offset ".
			"WHERE appointment_id = ".$ilDB->quote($this->getAppointmentId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
		// cat-tms patch end
		return true;
	}

	function delete()
	{
		return ilSessionAppointment::_delete($this->getAppointmentId());
	}

	public static function _delete($a_appointment_id)
	{
		global $ilDB;

		$query = "DELETE FROM event_appointment ".
			"WHERE appointment_id = ".$ilDB->quote($a_appointment_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}

	public static function _deleteBySession($a_event_id)
	{
		global $ilDB;

		$query = "DELETE FROM event_appointment ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}

	public static function _readAppointmentsBySession($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event_appointment ".
			"WHERE event_id = ".$ilDB->quote($a_event_id ,'integer')." ".
			"ORDER BY starting_time";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$appointments[] = new ilSessionAppointment($row->appointment_id);
		}
		return is_array($appointments) ? $appointments : array();
	}
			
	function validate()
	{
		if($this->starting_time > $this->ending_time)
		{
			$this->ilErr->appendMessage($this->lng->txt('event_etime_smaller_stime'));
			return false;
		}
		return true;
	}

	// PRIVATE
	function __read()
	{
		global $ilDB,$DIC;
		$user = $DIC->user();
		if(!$this->getAppointmentId())
		{
			return null;
		}

		$query = "SELECT * FROM event_appointment ".
			"WHERE appointment_id = ".$ilDB->quote($this->getAppointmentId() ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->setSessionId($row->event_id);
			$this->toggleFullTime($row->fulltime);
			
			if($this->isFullday())
			{
				$this->start = new ilDate($row->e_start,IL_CAL_DATETIME);
				$this->end = new ilDate($row->e_end,IL_CAL_DATETIME);
			}
			// cat-tms patch start
			else
			{
				$this->start = new ilDateTime($row->e_start,IL_CAL_DATETIME,'UTC');
				$this->end = new ilDateTime($row->e_end,IL_CAL_DATETIME,'UTC');
			}
			$this->starting_time = $this->start->getUnixTime();
			$this->ending_time = $this->end->getUnixTime();
			if ($row->days_offset == -1) {
				$this->days_offset = null;
			}
			else {
				$this->days_offset = (int)$row->days_offset;
			}
			// cat-tms patch end
		}
		return true;
	}

}
?>
