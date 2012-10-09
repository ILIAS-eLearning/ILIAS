<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once('./Services/Calendar/classes/class.ilDate.php');
include_once './Services/Calendar/classes/class.ilCalendarRecurrenceExclusions.php';
include_once './Services/Calendar/interfaces/interface.ilCalendarRecurrenceCalculation.php';

/** 
* Model of calendar entry recurrcences
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

define('IL_CAL_FREQ_DAILY','DAILY');
define('IL_CAL_FREQ_WEEKLY','WEEKLY');
define('IL_CAL_FREQ_MONTHLY','MONTHLY');
define('IL_CAL_FREQ_YEARLY','YEARLY');

class ilCalendarRecurrence implements ilCalendarRecurrenceCalculation
{
	const REC_RECURRENCE = 0;
	const REC_EXCLUSION = 1;
	
	const FREQ_DAILY = 'DAILY';
	const FREQ_WEEKLY = 'WEEKLY';
	const FREQ_MONTHLY = 'MONTHLY';
	const FREQ_YEARLY = 'YEARLY';
	
	
	protected $db;
	
	private $recurrence_id;
	private $cal_id;
	private $recurrence_type;
	
	private $freq_type = '';
	private $freq_until_type; 
	private $freq_until_date = null;
	private $freq_until_count;
	
	private $interval = 0;
	private $byday = '';
	private $byweekno = '';
	private $bymonth = '';
	private $bymonthday = '';
	private $byyearday = '';
	private $bysetpos = '';
	private $weekstart = '';
	
	private $exclusion_dates = array();
	
	private $timezone = 'Europe/Berlin';

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int recurrence id
	 * 
	 */
	public function __construct($a_rec_id = 0)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->recurrence_id = $a_rec_id;
	 	if($a_rec_id)
	 	{
	 		$this->read();
	 	}
	}
	
	/**
	 * delete
	 *
	 * @access public
	 * @param int appointment id
	 * @return
	 * @static
	 */
	public static function _delete($a_cal_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_recurrence_rules ".
			"WHERE cal_id = ".$ilDB->quote($a_cal_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
		ilCalendarRecurrenceExclusions::delete($a_cal_id);
	}
	
	public function toICal($a_user_id)
	{
		$ical = 'RRULE:';
		$ical .= ('FREQ='.$this->getFrequenceType());
		
		if($this->getInterval())
		{
			$ical .= (';INTERVAL='.$this->getInterval());
		}
		if($this->getFrequenceUntilCount())
		{
			$ical .= (';COUNT='.$this->getFrequenceUntilCount());
		}
		elseif($this->getFrequenceUntilDate())
		{
			$ical .= (';UNTIL='.$this->getFrequenceUntilDate()->get(IL_CAL_FKT_DATE,'Ymd'));
		}
		if($this->getBYMONTH())
		{
			$ical .= (';BYMONTH='.$this->getBYMONTH());
		}
		if($this->getBYWEEKNO())
		{
			$ical .= (';BYWEEKNO='.$this->getBYWEEKNO());
		}
		if($this->getBYYEARDAY())
		{
			$ical .= (';BYYEARDAY='.$this->getBYYEARDAY());
		}
		if($this->getBYMONTHDAY())
		{
			$ical .= (';BYMONTHDAY='.$this->getBYMONTHDAY());
		}
		if($this->getBYDAY())
		{
			$ical .= (';BYDAY='.$this->getBYDAY());
		}
		if($this->getBYSETPOS())
		{
			$ical .= (';BYSETPOS='.$this->getBYSETPOS());
		}

		// Required in outlook
		if($this->getBYDAY())
		{
			include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
			include_once './Services/Calendar/classes/class.ilCalendarSettings.php';
			$us = ilCalendarUserSettings::_getInstanceByUserId($a_user_id);
			if($us->getWeekStart() == ilCalendarSettings::WEEK_START_MONDAY)
			{
				$ical .= (';WKST=MO');
			}
			else
			{
				$ical .= (';WKST=SU');
			}
		}

		return $ical;
	}
	
	
	/**
	 * reset all settings
	 *
	 * @access public
	 * @return
	 */
	public function reset()
	{
		$this->setBYDAY('');
		$this->setBYMONTHDAY('');
		$this->setBYMONTH('');
		$this->setBYSETPOS('');
		$this->setBYWEEKNO('');
		$this->setBYYEARDAY('');
		$this->setFrequenceType('');
		$this->setInterval(1);
		$this->setFrequenceUntilCount(0);
		
		return true;
	}
	
	/**
	 * get recurrence id 
	 *
	 * @access public
	 * @return
	 */
	public function getRecurrenceId()
	{
		return $this->recurrence_id;
	}
	
	
	/**
	 * set cal id
	 *
	 * @access public
	 * @param int calendar entry id
	 * 
	 */
	public function setEntryId($a_id)
	{
	 	$this->cal_id = $a_id;
	}
	
	/**
	 * set type of recurrence
	 *
	 * @access public
	 * @param int REC_RECURRENCE or REC_EXLUSION defines whther the current object is a recurrence an exclusion pattern
	 * 
	 */
	public function setRecurrence($a_type)
	{
	 	$this->recurrence_type = $a_type;
	}
	
	/**
	 * is recurrence
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isRecurrence()
	{
	 	return $this->recurrence_type == self::REC_RECURRENCE;
	}
	
	/**
	 * set frequence type
	 *
	 * @access public
	 * @param int FREQUENCE_TYPE e.g MONTHLY, WEEKLY ...
	 * 
	 */
	public function setFrequenceType($a_type)
	{
	 	$this->freq_type = $a_type;
	}
	
	/**
	 * get freq type
	 *
	 * @access public
	 * 
	 */
	public function getFrequenceType()
	{
	 	return $this->freq_type;
	}
	
	/**
	 * get until date
	 *
	 * @access public
	 * 
	 */
	public function getFrequenceUntilDate()
	{
	 	return is_object($this->freq_until_date) ? $this->freq_until_date : null;
	}
	
	/**
	 * set freq until date
	 *
	 * @access public
	 * 
	 */
	public function setFrequenceUntilDate(ilDateTime $a_date = null)
	{
	 	$this->freq_until_date = $a_date;
	}
	
	/**
	 * set frequence count
	 *
	 * @access public
	 * @param int count
	 * 
	 */
	public function setFrequenceUntilCount($a_count)
	{
	 	$this->freq_until_count = $a_count;
	}
	
	/**
	 * get frequence until count
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getFrequenceUntilCount()
	{
	 	return $this->freq_until_count;
	}
	
	/**
	 * set interval
	 *
	 * @access public
	 * @param int interval
	 * 
	 */
	public function setInterval($a_interval)
	{
	 	$this->interval = $a_interval;
	}
	
	/**
	 * get interval
	 *
	 * @access public
	 * 
	 */
	public function getInterval()
	{
	 	return $this->interval ? $this->interval : 1;
	}
	
	/**
	 * set by day
	 *
	 * @access public
	 * @param string byday rule
	 * 
	 */
	public function setBYDAY($a_byday)
	{
	 	$this->byday = $a_byday;
	}
	
	/**
	 * get BYDAY
	 *
	 * @access public
	 * 
	 */
	public function getBYDAY()
	{
	 	return $this->byday;
	}
	
	/**
	 * get BYDAY list
	 *
	 * @access public
	 * @return
	 */
	public function getBYDAYList()
	{
		if(!trim($this->getBYDAY()))
		{
			return array();
		}
		foreach(explode(',',$this->getBYDAY()) as $byday)
		{
			$bydays[] = trim($byday);
		}
		return $bydays ? $bydays : array();
	}
	
	/**
	 * set by day
	 *
	 * @access public
	 * @param string byday rule
	 * 
	 */
	public function setBYWEEKNO($a_byweekno)
	{
	 	$this->byweekno = $a_byweekno;
	}
	
	/**
	 * get byweekno list
	 *
	 * @access public
	 * 
	 */
	public function getBYWEEKNOList()
	{
	 	if(!trim($this->getBYWEEKNO()))
	 	{
	 		return array();
	 	}
	 	foreach(explode(',',$this->getBYWEEKNO()) as $week_num)
	 	{
	 		$weeks[] = (int) $week_num;
	 	}
	 	return $weeks ? $weeks : array();
	}
	
	
	/**
	 * get BYDAY
	 *
	 * @access public
	 * 
	 */
	public function getBYWEEKNO()
	{
	 	return $this->byweekno;
	}
	
	/**
	 * set by day
	 *
	 * @access public
	 * @param string byday rule
	 * 
	 */
	public function setBYMONTH($a_by)
	{
	 	$this->bymonth = $a_by;
	}
	
	/**
	 * get BYDAY
	 *
	 * @access public
	 * 
	 */
	public function getBYMONTH()
	{
	 	return $this->bymonth;
	}
	
	/**
	 * get bymonth list
	 *
	 * @access public
	 * 
	 */
	public function getBYMONTHList()
	{
	 	if(!trim($this->getBYMONTH()))
	 	{
	 		return array();
	 	}
	 	foreach(explode(',',$this->getBYMONTH()) as $month_num)
	 	{
	 		$months[] = (int) $month_num;
	 	}
	 	return $months ? $months : array();
	}
	
	/**
	 * set by day
	 *
	 * @access public
	 * @param string byday rule
	 * 
	 */
	public function setBYMONTHDAY($a_by)
	{
	 	$this->bymonthday = $a_by;
	}
	
	/**
	 * get BYDAY
	 *
	 * @access public
	 * 
	 */
	public function getBYMONTHDAY()
	{
	 	return $this->bymonthday;
	}
	
	/**
	 * get BYMONTHDAY list
	 *
	 * @access public
	 */
	public function getBYMONTHDAYList()
	{
	 	if(!trim($this->getBYMONTHDAY()))
	 	{
	 		return array();
	 	}
	 	foreach(explode(',',$this->getBYMONTHDAY()) as $month_num)
	 	{
	 		$months[] = (int) $month_num;
	 	}
	 	return $months ? $months : array();
	
	}
	
	
	/**
	 * set by day
	 *
	 * @access public
	 * @param string byday rule
	 * 
	 */
	public function setBYYEARDAY($a_by)
	{
	 	$this->byyearday = $a_by;
	}
	
	/**
	 * get BYDAY
	 *
	 * @access public
	 * 
	 */
	public function getBYYEARDAY()
	{
	 	return $this->byyearday;
	}
	
	/**
	 * get BYYEARDAY list
	 *
	 * @access public
	 * 
	 */
	public function getBYYEARDAYList()
	{
	 	if(!trim($this->getBYYEARDAY()))
	 	{
	 		return array();
	 	}
	 	foreach(explode(',',$this->getBYYEARDAY()) as $year_day)
	 	{
	 		$days[] = (int) $year_day;
	 	}
	 	return $days ? $days : array();
	}
	
	/**
	 * set by day
	 *
	 * @access public
	 * @param string byday rule
	 * 
	 */
	public function setBYSETPOS($a_by)
	{
	 	$this->bysetpos = $a_by;
	}
	
	/**
	 * get BYDAY
	 *
	 * @access public
	 * 
	 */
	public function getBYSETPOS()
	{
	 	return $this->bysetpos;
	}
	
	/**
	 * get bysetpos list
	 *
	 * @access public
	 * 
	 */
	public function getBYSETPOSList()
	{
	 	if(!trim($this->getBYSETPOS()))
	 	{
	 		return array();
	 	}
	 	foreach(explode(',',$this->getBYSETPOS()) as $pos)
	 	{
	 		$positions[] = (int) $pos;
	 	}
	 	return $positions ? $positions : array();
	}
	
	
	/**
	 * set weekstart
	 *
	 * @access public
	 * @param string weekstart
	 * 
	 */
	public function setWeekstart($a_start)
	{
	 	$this->weekstart = $a_start;
	}
	
	/**
	 * get weekstart
	 *
	 * @access public
	 * 
	 */
	public function getWeekstart()
	{
	 	return $this->weekstart;
	}
	
	/**
	 * get timezone
	 *
	 * @access public
	 * 
	 */
	public function getTimeZone()
	{
	 	return $this->timezone;
	}
	
	/**
	 * set timezone 
	 *
	 * @access public
	 * @param string timezone
	 * 
	 */
	public function setTimeZone($a_tz)
	{
	 	$this->timezone = $a_tz;
	}
	
	/**
	 * Get exclusion dates
	 * @return 
	 */
	public function getExclusionDates()
	{
		return (array) $this->exclusion_dates;
	}
	
	/**
	 * validate
	 *
	 * @access public
	 * @return bool
	 */
	public function validate()
	{
		$valid_frequences = array(IL_CAL_FREQ_DAILY,IL_CAL_FREQ_WEEKLY,IL_CAL_FREQ_MONTHLY,IL_CAL_FREQ_YEARLY);
		if(!in_array($this->getFrequenceType(),$valid_frequences))
		{
			return false;
		}
		if($this->getFrequenceUntilCount() < 0)
		{
			return false;
		}
		if($this->getInterval() <= 0)
		{
			return false;
		}
		return true;
	}
	
	
	/**
	 * save
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
		global $ilDB;

	 	$until_date = is_null($this->getFrequenceUntilDate()) ? 
	 		null : 
	 		$this->getFrequenceUntilDate()->get(IL_CAL_DATETIME,'','UTC');
	 	$next_id = $ilDB->nextId('cal_recurrence_rules');
	 	
	 	$query = "INSERT INTO cal_recurrence_rules (rule_id,cal_id,cal_recurrence,freq_type,freq_until_date,freq_until_count,intervall, ".
			"byday,byweekno,bymonth,bymonthday,byyearday,bysetpos,weekstart) ".
			"VALUES( ".
			$ilDB->quote($next_id,'integer').", ".
	 		$this->db->quote($this->cal_id ,'integer').", ".
	 		$ilDB->quote(1,'integer').", ".
	 		$ilDB->quote((string) $this->getFrequenceType() ,'text').", ".
	 		$this->db->quote($until_date,'timestamp').", ".
			$this->db->quote((int) $this->getFrequenceUntilCount() ,'integer').", ".
			$this->db->quote((int) $this->getInterval() ,'integer').", ".
			$this->db->quote((string) $this->getBYDAY() ,'text').", ".
			$this->db->quote((string) $this->getBYWEEKNO() ,'text').", ".
			$this->db->quote((string) $this->getBYMONTH() ,'text').", ".
			$this->db->quote((string) $this->getBYMONTHDAY() ,'text').", ".
			$this->db->quote((string) $this->getBYYEARDAY() ,'text').", ".
			$this->db->quote((string) $this->getBYSETPOS() ,'text').", ".
			$this->db->quote((string) $this->getWeekstart() ,'text')." ".
			")";
		$res = $ilDB->manipulate($query);
		$this->recurrence_id = $next_id;
		return true;
	}
	
	/**
	 * save
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	global $ilDB;
	 	
	 	$until_date = is_null($this->getFrequenceUntilDate()) ? 
	 		null : 
	 		$this->getFrequenceUntilDate()->get(IL_CAL_DATETIME,'','UTC');

	 	$query = "UPDATE cal_recurrence_rules SET ".
	 		"cal_id = ".$this->db->quote($this->cal_id ,'integer').", ".
	 		"cal_recurrence = 1,".
	 		"freq_type = ".$this->db->quote($this->getFrequenceType() ,'text').", ".
	 		"freq_until_date = ".$this->db->quote($until_date ,'timestamp').", ".
			"freq_until_count = ".$this->db->quote($this->getFrequenceUntilCount() ,'integer').", ".
			"intervall = ".$this->db->quote($this->getInterval() ,'integer').", ".
			"byday = ".$this->db->quote($this->getBYDAY() ,'text').", ".
			"byweekno = ".$this->db->quote($this->getBYWEEKNO() ,'text').", ".
			"bymonth = ".$this->db->quote($this->getBYMONTH() ,'text').", ".
			"bymonthday = ".$this->db->quote($this->getBYMONTHDAY() ,'text').", ".
			"byyearday = ".$this->db->quote($this->getBYYEARDAY() ,'text').", ".
			"bysetpos = ".$this->db->quote($this->getBYSETPOS() ,'text').", ".
			"weekstart = ".$this->db->quote($this->getWeekstart() ,'text')." ".
			"WHERE rule_id = ".$this->db->quote($this->recurrence_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * delete
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM cal_recurrence_rules ".
	 		"WHERE rule_id = ".$this->db->quote($this->recurrence_id ,'integer');
	 	$res = $ilDB->manipulate($query);
	 	return true;
	}

	/**
	 * Read entry
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM cal_recurrence_rules ".
	 		"WHERE rule_id = ".$this->db->quote($this->recurrence_id ,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->cal_id = $row->cal_id;
	 		$this->recurrence_type = $row->cal_recurrence;
	 		$this->freq_type = $row->freq_type;
	 		
	 		if($row->freq_until_date != null)
	 		{
		 		$this->freq_until_date = new ilDateTime($row->freq_until_date,IL_CAL_DATETIME,'UTC');
	 		}
	 		$this->freq_until_count = $row->freq_until_count;
	 		$this->interval = $row->intervall;
	 		$this->byday = $row->byday;
	 		$this->byweekno = $row->byweekno;
	 		$this->bymonth = $row->bymonth;
	 		$this->bymonthday = $row->bymonthday;
	 		$this->byyearday = $row->byyearday;
	 		$this->bysetpos = $row->bysetpos;
	 		$this->weekstart = $row->week_start;
	 	}
		
		$this->exclusion_dates = ilCalendarRecurrenceExclusions::getExclusionDates($this->cal_id);
	}
}


?>