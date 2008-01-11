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
	const FORMAT_DATETIME = 1;
	const FORMAT_DATE = 2;
	const FORMAT_UNIX = 3;

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
		 		$this->setDate(time(),self::FORMAT_UNIX);
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
				
				if(preg_match('/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2}))$/i',$a_date,$d_parts) === false)
				{
					$this->log->write(__METHOD__.': Cannot parse date: '.$a_date);
					#throw new ilDateTimeException('Cannot parse date.');
					return false;
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
				$unix = strtotime($a_date);
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
}
?>