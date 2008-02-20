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

class ilDateTime
{
	const YEAR = 'year';
	const MONTH = 'month';
	const WEEK = 'week';
	const DAY = 'day';
	
	const FORMAT_DATETIME = 1;
	const FORMAT_DATE = 2;
	const FORMAT_UNIX = 3;
	
	const FORMAT_FKT_DATE = 4;

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
		 		$this->setDate(0,self::FORMAT_UNIX);
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
	 * compare two dates and check start is before end 
	 *
	 * @access public
	 * @static
	 *
	 * @param object ilDateTime
	 * @param object ilDateTime
	 * @return bool 
	 */
	public static function _before(ilDateTime $start,ilDateTime $end)
	{
		return $start->get(self::FORMAT_UNIX) < $end->get(self::FORMAT_UNIX);
	}
	
	/**
	 * increment date
	 *
	 * @access public
	 * @static
	 *
	 * @param int unix time
	 * @param int type DATE,YEAR,MONTH or WEEK
	 * @param int count
	 */
	public static function _increment($unix_start,$a_type,$a_count = 1)
	{
		$count_str = $a_count > 0 ? ('+'.$a_count.' ') : ('-'.$a_count.' ');

		switch($a_type)
		{
			case self::YEAR:
				return strtotime($count_str.'year',$unix_start);
				
			case self::MONTH:
				return strtotime($count_str.'month',$unix_start);
								
			case self::WEEK:
				return strtotime($count_str.'week',$unix_start);
				
			case self::DAY:
				return strtotime($count_str.'day',$unix_start);
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
		$count_str = $a_count > 0 ? ('+'.$a_count.' ') : ('-'.$a_count.' ');

		switch($a_type)
		{
			case self::YEAR:
				return $this->unix = strtotime($count_str.'year',$this->unix);
				
			case self::MONTH:
				return $this->unix = strtotime($count_str.'month',$this->unix);
				
			case self::WEEK:
				return $this->unix = strtotime($count_str.'week',$this->unix);
				
			case self::DAY:
				return $this->unix = strtotime($count_str.'day',$this->unix);
				
		}
	 	
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
	 	return mktime(0,0,0,2,1,1970) - gmmktime(0,0,0,2,1,1970);
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
	 		case self::FORMAT_UNIX:
				$this->unix = $a_date;
				break;
				
			case self::FORMAT_DATETIME:
				
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

			case self::FORMAT_DATE:
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
	 		case self::FORMAT_UNIX:
	 			$date = $this->getUnixTime();
	 			break;
	 		case self::FORMAT_DATE:
			 	$timezone->switchTZ();
				$date = date('Y-m-d',$this->getUnixTime());
				$timezone->restoreTZ();
				break;
			case self::FORMAT_DATETIME:
			 	$timezone->switchTZ();
				$date = date('Y-m-d H:i:s',$this->getUnixTime());
				$timezone->restoreTZ();
				break;
			case self::FORMAT_FKT_DATE:
			 	$timezone->switchTZ();
				$date = date($a_format_str,$this->getUnixTime());
				$timezone->restoreTZ();
				break;	
	 	}
		return $date;
	}
}
?>