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

/**
* Class for date presentation
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar 
*/
class ilDatePresentation
{
	public static $today = null;
	public static $tomorrow = null;
	public static $yesterday = null;
	
	/**
	 * Format a date
	 * 
	 * @access public
	 * @param object $date ilDate or ilDateTime
	 * @return string date presentation in user specific timezone and language 
	 * @static
	 */
	public static function formatDate(ilDateTime $date)
	{
		global $lng,$ilUser;
		
		$has_time = !is_a($date,'ilDate');
		
		// Converting pure dates to user timezone might return wrong dates
		if($has_time)
		{
			$date_info = $date->get(IL_CAL_FKT_GETDATE,'',$ilUser->getUserTimeZone());	
		}
		else
		{
			$date_info = $date->get(IL_CAL_FKT_GETDATE,'','UTC');
		}
		

		if(self::isToday($date))
		{
			$date_str = $lng->txt('today');
		}
		elseif(self::isTomorrow($date))
		{
			$date_str = $this->lng->txt('tomorrow');
		}
		elseif(self::isYesterday($date))
		{
			$date_str = $this->lng->txt('yesterday');
		}
		else
		{
			include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');
			$date_str = $date_info['mday'].'. '.
				ilCalendarUtil::_numericMonthToString($date_info['mon'],false).' '.
				$date_info['year'];
		}
		
		if(!$has_time)
		{
			return $date_str;
		}	
		
		return $date_str.', '.$date_info['hours'].':'.sprintf('%02d',$date_info['minutes']);		
	}



	/**
	 * Check if date is "today"
	 *
	 * @access public
	 * @param object ilDateTime DateTime object to check
	 * @return bool
	 * @static
	 */
	public static function isToday(ilDateTime $date)
	{
		if(!is_object(self::$today))
		{
			self::$today = new ilDateTime(time(),IL_CAL_UNIX,'UTC');
		}
		
		return ilDateTime::_equals(self::$today,$date,IL_CAL_DAY);
	}
	
	/**
	 * Check if date is yesterday
	 *
	 * @access public
	 * @param object ilDateTime DateTime object to check
	 * @return bool
	 * @static
	 */
	public static function isYesterday(ilDateTime $date)
	{
		if(!is_object(self::$yesterday))
		{
			self::$yesterday = new ilDateTime(time(),IL_CAL_UNIX,'UTC');
			self::$yesterday->increment(IL_CAL_DAY,-1);
		}
		
		return ilDateTime::_equals(self::$yesterday,$date,IL_CAL_DAY);
	}

	/**
	 * Check if date is tomorrow
	 *
	 * @access public
	 * @param object ilDateTime DateTime object to check
	 * @return bool
	 * @static
	 */
	public static function isTomorrow(ilDateTime $date)
	{
		if(!is_object(self::$tomorrow))
		{
			self::$tomorrow = new ilDateTime(time(),IL_CAL_UNIX,'UTC');
			self::$tomorrow->increment(IL_CAL_DAY,1);
		}
		
		return ilDateTime::_equals(self::$tomorrow,$date,IL_CAL_DAY);
	}

}
?>