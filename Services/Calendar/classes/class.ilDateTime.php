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

include_once('Services/Calendar/classes/class.ilDateTimeException.php');
include_once('Services/Calendar/classes/class.ilTimeZone.php');

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

define('IL_CAL_DATETIME',1);
define('IL_CAL_DATE',2);
define('IL_CAL_UNIX',3);
define('IL_CAL_FKT_DATE',4);
define('IL_CAL_FKT_GETDATE',5);

define('IL_CAL_YEAR','year');
define('IL_CAL_MONTH','month');
define('IL_CAL_WEEK','week');
define('IL_CAL_DAY','day');
define('IL_CAL_HOUR','hour');

class ilDateTime
{
	const YEAR = 'year';
	const MONTH = 'month';
	const WEEK = 'week';
	const DAY = 'day';
	const HOUR = 'hour';

	protected $log;
	
	protected $timezone = null;
	
	protected $unix = 0;
	
	
	
	/**
	 * Create new date object
	 *
	 * @access public
	 * @param mixed integer string following the format given as the second parameter
	 * @param int format of date presentation
	 * @param 
	 * 
	 * @throws ilDateTimeException
	 */
	public function __construct($a_date = null,$a_format = 0,$a_tz = '')
	{
	 	global $ilLog;
	 	
	 	$this->log = $ilLog;
	 	
	 	try
	 	{
		 	$this->timezone = ilTimeZone::_getInstance($a_tz);
		 	
		 	if(!$a_date)
		 	{
		 		$this->setDate(0,IL_CAL_UNIX);
		 	}
		 	else
		 	{
		 		$this->setDate($a_date,$a_format);
		 	}
	 	}
	 	catch(ilTimeZoneException $exc)
	 	{
	 		$this->log->write(__METHOD__.': '.$exc->getMessage());
	 		throw new ilDateTimeException('Unsupported timezone given. Timezone: '.$a_tz);
	 	}
	}
	
	/**
	 * Switch timezone
	 *
	 * @access public
	 * @param string PHP timezone identifier
	 * @throws ilDateTimeException
	 */
	public function switchTimeZone($a_timezone_identifier = '')
	{
	 	try
	 	{
	 		$this->timezone = ilTimeZone::_getInstance($a_timezone_identifier);
	 		return true;
	 	}
	 	catch(ilTimeZoneException $e)
	 	{
	 		$this->log->write('Unsupported timezone given: '.$a_timezone_identifier);
	 		throw new ilDateTimeException('Unsupported timezone given. Timezone: '.$a_timezone_identifier);
	 	}
	}
	
	/**
	 * get timezone identifier
	 *
	 * @access public
	 * 
	 */
	public function getTimeZoneIdentifier()
	{
	 	return $this->timezone->getIdentifier();
	}
	
	/**
	 * compare two dates and check start is before end 
	 * This method does not consider tz offsets.
	 * So you have to take care that both dates are defined in the the same timezone
	 *
	 * @access public
	 * @static
	 *
	 * @param object ilDateTime
	 * @param object ilDateTime
	 * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is one or more years earlier than end 
	 * @return bool 
	 */
	public static function _before(ilDateTime $start,ilDateTime $end,$a_compare_field = '')
	{
		switch($a_compare_field)
		{
			case IL_CAL_YEAR:
				return $start->get(IL_CAL_FKT_DATE,'Y') < $end->get(IL_CAL_FKT_DATE,'Y');
				
			case IL_CAL_MONTH:
				return (int) $start->get(IL_CAL_FKT_DATE,'Ym') < $end->get(IL_CAL_FKT_DATE,'Ym');
			
			case IL_CAL_DAY:
				return (int) $start->get(IL_CAL_FKT_DATE,'Ymd') < $end->get(IL_CAL_FKT_DATE,'Ymd');

			case '':
			default:
				return $start->get(IL_CAL_UNIX) < $end->get(IL_CAL_UNIX);
			
		}
	}
	
	/**
	 * Check if two date are equal
	 * 
	 * @access public
	 * @static
	 *
	 * @param object ilDateTime
	 * @param object ilDateTime
	 * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is the same years than end 
	 */
	public static function _equals(ilDateTime $start,ilDateTime $end,$a_compare_field = '')
	{
		switch($a_compare_field)
		{
			case IL_CAL_YEAR:
				return $start->get(IL_CAL_FKT_DATE,'Y') == $end->get(IL_CAL_FKT_DATE,'Y');

			case IL_CAL_MONTH:
				return (int) $start->get(IL_CAL_FKT_DATE,'Ym') == $end->get(IL_CAL_FKT_DATE,'Ym');

			case IL_CAL_DAY:
				return (int) $start->get(IL_CAL_FKT_DATE,'Ymd') == $end->get(IL_CAL_FKT_DATE,'Ymd');

			case '':
			default:
				return $start->get(IL_CAL_UNIX) == $end->get(IL_CAL_UNIX);
			
		}
	}

	/**
	 * compare two dates and check start is after end 
	 * This method does not consider tz offsets.
	 * So you have to take care that both dates are defined in the the same timezone
	 *
	 * @access public
	 * @param object ilDateTime
	 * @param object ilDateTime 
	 * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is one or more years after than end 
	 * @static
	 */
	public static function _after(ilDateTime $start,ilDateTime $end,$a_compare_field = '')
	{
		switch($a_compare_field)
		{
			case IL_CAL_YEAR:
				return $start->get(IL_CAL_FKT_DATE,'Y') > $end->get(IL_CAL_FKT_DATE,'Y');

			case IL_CAL_MONTH:
				return (int) $start->get(IL_CAL_FKT_DATE,'Ym') > $end->get(IL_CAL_FKT_DATE,'Ym');

			case IL_CAL_DAY:
				return (int) $start->get(IL_CAL_FKT_DATE,'Ymd') > $end->get(IL_CAL_FKT_DATE,'Ymd');

			case '':
			default:
				return $start->get(IL_CAL_UNIX) > $end->get(IL_CAL_UNIX);
			
		}
	}
	
	/**
	 * increment
	 *
	 * @access public
	 * @param int type
	 * @param int count
	 * 
	 */
	public function increment($a_type,$a_count = 1)
	{
		$count_str = $a_count > 0 ? ('+'.$a_count.' ') : ($a_count.' ');

		$this->timezone->switchTZ();
		switch($a_type)
		{
			case self::YEAR:
				$this->unix = strtotime($count_str.'year',$this->unix);
				break;				

			case self::MONTH:
				$this->unix = strtotime($count_str.'month',$this->unix);
				break;
				
			case self::WEEK:
				$this->unix = strtotime($count_str.'week',$this->unix);
				break;
				
			case self::DAY:
				$this->unix = strtotime($count_str.'day',$this->unix);
				break;
				
			case self::HOUR:
				$this->unix = strtotime($count_str.'hour',$this->unix);
				break;
				
		}
		$this->timezone->restoreTZ();	 	
		return $this->unix;
	}
	
	/**
	 * get unix time
	 *
	 * @access public
	 * 
	 */
	public function getUnixTime()
	{
	 	return $this->unix;
	}
	

	/**
	 * get UTC offset
	 *
	 * @access public
	 * @return offset to utc in seconds 
	 */
	public function getUTCOffset()
	{
	 	$this->timezone->switchTZ();
	 	// TODO: calculate UTC offset of given date
	 	$offset = mktime(0,0,0,2,1,1970) - gmmktime(0,0,0,2,1,1970);
	 	$this->timezone->restoreTZ();
	 	return $offset; 	
	}
	
	/**
	 * set date
	 *
	 * @access public
	 * @param mixed date
	 * @param int format
	 * 
	 */
	public function setDate($a_date,$a_format)
	{
	 	switch($a_format)
	 	{
	 		case IL_CAL_UNIX:
				$this->unix = $a_date;
				break;
				
			case IL_CAL_DATETIME:
				
				if(preg_match('/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2})(\.\d+)?(Z|[\+\-]\d{2}:?\d{2})?)$/i',$a_date,$d_parts) === false)
				{
					$this->log->write(__METHOD__.': Cannot parse date: '.$a_date);
					throw new ilDateTimeException('Cannot parse date.');
				}
				
				$this->timezone->switchTZ();
				$this->unix = mktime(
					isset($d_parts[5]) ? $d_parts[5] : 0, 
					isset($d_parts[6]) ? $d_parts[6] : 0,
					isset($d_parts[7]) ? $d_parts[7] : 0,
					$d_parts[2],
					$d_parts[3],
					$d_parts[1]);
				$this->timezone->restoreTZ();
				break;

			case IL_CAL_DATE:
				// Pure dates are not timezone sensible.
				$timezone = ilTimeZone::_getInstance('UTC');
				$timezone->switchTZ();
				$unix = strtotime($a_date);
				$timezone->restoreTZ();
				if(!$unix or $unix == false)
				{
					$this->log->write(__METHOD__.': Cannot parse date: '.$a_date);
					return false;					
				}
				$this->unix = $unix;
				break;
				
			case IL_CAL_FKT_GETDATE:
				// Format like getdate parameters
				$this->timezone->switchTZ();
				$this->unix = mktime(
					$a_date['hours'],
					$a_date['minutes'],
					$a_date['seconds'],
					$a_date['mon'],
					$a_date['mday'],
					$a_date['year']);
				$this->timezone->restoreTZ();
				break;
	 	}
	 	return true;
	}
	
	/**
	 * get formatted date 
	 *
	 * @access public
	 * @param int format type
	 * @param string format string
	 * @param string a specific timezone
	 */
	public function get($a_format,$a_format_str = '',$a_tz = '')
	{
		if($a_tz)
		{
			try
			{
				$timezone = ilTimeZone::_getInstance($a_tz);
			}
			catch(ilTimeZoneException $exc)
			{
				$this->log->write(__METHOD__.': Invalid timezone given. Timezone: '.$a_tz);
			}
		}
		else
		{
			$timezone = $this->timezone;
		}

	 	switch($a_format)
	 	{
	 		case IL_CAL_UNIX:
	 			$date = $this->getUnixTime();
	 			break;
	 		case IL_CAL_DATE:
			 	$timezone->switchTZ();
				$date = date('Y-m-d',$this->getUnixTime());
				$timezone->restoreTZ();
				break;
			case IL_CAL_DATETIME:
			 	$timezone->switchTZ();
				$date = date('Y-m-d H:i:s',$this->getUnixTime());
				$timezone->restoreTZ();
				break;
			case IL_CAL_FKT_DATE:
			 	$timezone->switchTZ();
				$date = date($a_format_str,$this->getUnixTime());
				$timezone->restoreTZ();
				break;
				
			case IL_CAL_FKT_GETDATE:
				$timezone->switchTZ();
				$date = getdate($this->getUnixTime());
				$timezone->restoreTZ();

				// add iso 8601 week day number (Sunday = 7)
				$date['isoday'] = $date['wday'] == 0 ? 7 : $date['wday'];
				break;
	 	}
		return $date;
	}
	
	/**
	 * to string
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __toString()
	{
		return $this->get(IL_CAL_DATETIME,'','UTC').'<br>';
	}
}
?>