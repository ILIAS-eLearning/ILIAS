<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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

include_once './Services/Calendar/classes/class.ilCalendarSettings.php';

/**
* Class ilCalendarUtil
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
*
*/
class ilCalendarUtil
{
	private static $today = null;
	private static $default_calendar = array();
	static $init_done;
	
	/**
	 * check if a date is today 
	 * @param ilDateTime $date date to check
	 * @return bool
	 * @static
	 */
	 public static function _isToday($date)
	 {
		global $ilUser;
		
		
		if(!is_object(self::$today))
		{
			self::$today = new ilDateTime(time(),IL_CAL_UNIX,$ilUser->getTimeZone());
		}
		return ilDateTime::_equals(self::$today,$date,IL_CAL_DAY,$ilUser->getTimeZone());
	 }
	
	/**
	 * numeric month to string
	 *
	 * @access public
	 * @static
	 *
	 * @param int month (1-12)
	 * @param bool short or long month translation
	 */
	public static function _numericMonthToString($a_month,$a_long = true)
	{
		global $lng;
		
		$month = $a_month < 10 ? '0'.$a_month : $a_month;
		
		return 	$a_long ? $lng->txt('month_'.$month.'_long') : $lng->txt('month_'.$month.'_short');
	}
	
	/**
	 * get 
	 *
	 * @access public
	 * @static
	 *
	 * @param int day of week (0 for sunday, 1 for monday)
	 * @param bool short or long day translation
	 */
	public static function _numericDayToString($a_day,$a_long = true)
	{
		global $lng;

		$lng->loadLanguageModule('dateplaner');
		static $days = array('Su','Mo','Tu','We','Th','Fr','Sa','Su');
		
		return 	$a_long ? $lng->txt($days[$a_day].'_long') : $lng->txt($days[$a_day].'_short');
	}
	
	/**
	 * build week day list
	 *
	 * @access public
	 * @param ilDate a day in a week
	 * @param int weekstart
	 * @return ilDateList
	 * @static
	 */
	public static function _buildWeekDayList($a_day,$a_weekstart)
	{
		include_once('Services/Calendar/classes/class.ilDateList.php');
		$day_list = new ilDateList(ilDateList::TYPE_DATE);
		
		$start = clone $a_day;
		$start_info = $start->get(IL_CAL_FKT_GETDATE,'','UTC');
		$day_diff = $a_weekstart - $start_info['isoday'];
		if($day_diff == 7)
		{
			$day_diff = 0;
		}
		$start->increment(IL_CAL_DAY,$day_diff);
		$day_list->add($start);
		for($i = 1; $i < 7;$i++)
		{
			$start->increment(IL_CAL_DAY,1);
			$day_list->add($start);
		}
		return $day_list;
	}
	
	/**
	 * Build a month day list
	 *
	 * @access public
	 * @param int month
	 * @param int year
	 * @param int weekstart (0 => Sunday,1 => Monday)
	 * @return ilDateList
	 * 
	 */
	public static function _buildMonthDayList($a_month,$a_year,$weekstart)
	{
		include_once('Services/Calendar/classes/class.ilDateList.php');
		$day_list = new ilDateList(ilDateList::TYPE_DATE);
				
		$prev_month = ($a_month == 1) ? 12 : $a_month - 1;
		$prev_year = ($prev_month == 12) ? $a_year - 1 : $a_year;
		$next_month = $a_month == 12 ? 1 : $a_month + 1;
		$next_year = $a_month == 12 ? $a_year + 1 : $a_year;
		
		$days_in_month = self::_getMaxDayOfMonth($a_year,$a_month);
		$days_in_prev_month = self::_getMaxDayOfMonth($a_year,$prev_month);
		
		$week_day['year'] = $a_year;
		$week_day['mon'] = $a_month;
		$week_day['mday'] = 1;
		$week_day['hours'] = 0;
		$week_day['minutes'] = 0;
		$week_day = new ilDate($week_day,IL_CAL_FKT_GETDATE);
		
		$weekday = $week_day->get(IL_CAL_FKT_DATE,'w');
		$first_day_offset = (($weekday - $weekstart) < 0) ? 6 : $weekday - $weekstart; 


		for($i = 0;$i < 42;$i++)
		{

			if($i < $first_day_offset)
			{
				$day = $days_in_prev_month - $first_day_offset + $i + 1;
				
				$day_list->add(new ilDate(gmmktime(0,0,0,$prev_month,
					$days_in_prev_month - $first_day_offset + $i + 1,
					$prev_year),
					IL_CAL_UNIX));
			}
			elseif($i < $days_in_month + $first_day_offset)
			{
				$day = $i - $first_day_offset + 1;

								
				$day_list->add(new ilDate(gmmktime(0,0,0,$a_month,
					$i - $first_day_offset + 1,
					$a_year),
					IL_CAL_UNIX));
			}
			else
			{
				$day = $i - $days_in_month - $first_day_offset + 1;
				$day_list->add(new ilDate(gmmktime(0,0,0,$next_month,
					$i - $days_in_month - $first_day_offset + 1,
					$next_year),
					IL_CAL_UNIX));
			}
			if($i == 34 and ($day < 15 or $day == $days_in_month))
			{
				break;
			}
		}
		return $day_list;
	}	
	
	/**
	* Init Javascript Calendar.
	*/
	static function initJSCalendar()
	{
		global $tpl, $lng;
		
		if (self::$init_done == "done")
		{
			return;
		}
		
		$lng->loadLanguageModule("jscalendar");
		$tpl->addBlockFile("CALENDAR_LANG_JAVASCRIPT", "calendar_javascript", "tpl.calendar.html",
			"Services/Calendar");
		$tpl->setCurrentBlock("calendar_javascript");
		$tpl->setVariable("FULL_SUNDAY", $lng->txt("l_su"));
		$tpl->setVariable("FULL_MONDAY", $lng->txt("l_mo"));
		$tpl->setVariable("FULL_TUESDAY", $lng->txt("l_tu"));
		$tpl->setVariable("FULL_WEDNESDAY", $lng->txt("l_we"));
		$tpl->setVariable("FULL_THURSDAY", $lng->txt("l_th"));
		$tpl->setVariable("FULL_FRIDAY", $lng->txt("l_fr"));
		$tpl->setVariable("FULL_SATURDAY", $lng->txt("l_sa"));
		$tpl->setVariable("SHORT_SUNDAY", $lng->txt("s_su"));
		$tpl->setVariable("SHORT_MONDAY", $lng->txt("s_mo"));
		$tpl->setVariable("SHORT_TUESDAY", $lng->txt("s_tu"));
		$tpl->setVariable("SHORT_WEDNESDAY", $lng->txt("s_we"));
		$tpl->setVariable("SHORT_THURSDAY", $lng->txt("s_th"));
		$tpl->setVariable("SHORT_FRIDAY", $lng->txt("s_fr"));
		$tpl->setVariable("SHORT_SATURDAY", $lng->txt("s_sa"));
		$tpl->setVariable("FULL_JANUARY", $lng->txt("l_01"));
		$tpl->setVariable("FULL_FEBRUARY", $lng->txt("l_02"));
		$tpl->setVariable("FULL_MARCH", $lng->txt("l_03"));
		$tpl->setVariable("FULL_APRIL", $lng->txt("l_04"));
		$tpl->setVariable("FULL_MAY", $lng->txt("l_05"));
		$tpl->setVariable("FULL_JUNE", $lng->txt("l_06"));
		$tpl->setVariable("FULL_JULY", $lng->txt("l_07"));
		$tpl->setVariable("FULL_AUGUST", $lng->txt("l_08"));
		$tpl->setVariable("FULL_SEPTEMBER", $lng->txt("l_09"));
		$tpl->setVariable("FULL_OCTOBER", $lng->txt("l_10"));
		$tpl->setVariable("FULL_NOVEMBER", $lng->txt("l_11"));
		$tpl->setVariable("FULL_DECEMBER", $lng->txt("l_12"));
		$tpl->setVariable("SHORT_JANUARY", $lng->txt("s_01"));
		$tpl->setVariable("SHORT_FEBRUARY", $lng->txt("s_02"));
		$tpl->setVariable("SHORT_MARCH", $lng->txt("s_03"));
		$tpl->setVariable("SHORT_APRIL", $lng->txt("s_04"));
		$tpl->setVariable("SHORT_MAY", $lng->txt("s_05"));
		$tpl->setVariable("SHORT_JUNE", $lng->txt("s_06"));
		$tpl->setVariable("SHORT_JULY", $lng->txt("s_07"));
		$tpl->setVariable("SHORT_AUGUST", $lng->txt("s_08"));
		$tpl->setVariable("SHORT_SEPTEMBER", $lng->txt("s_09"));
		$tpl->setVariable("SHORT_OCTOBER", $lng->txt("s_10"));
		$tpl->setVariable("SHORT_NOVEMBER", $lng->txt("s_11"));
		$tpl->setVariable("SHORT_DECEMBER", $lng->txt("s_12"));
		$tpl->setVariable("ABOUT_CALENDAR", $lng->txt("about_calendar"));
		$tpl->setVariable("ABOUT_CALENDAR_LONG", $lng->txt("about_calendar_long"));
		$tpl->setVariable("ABOUT_TIME_LONG", $lng->txt("about_time"));
		$tpl->setVariable("PREV_YEAR", $lng->txt("prev_year"));
		$tpl->setVariable("PREV_MONTH", $lng->txt("prev_month"));
		$tpl->setVariable("GO_TODAY", $lng->txt("go_today"));
		$tpl->setVariable("NEXT_MONTH", $lng->txt("next_month"));
		$tpl->setVariable("NEXT_YEAR", $lng->txt("next_year"));
		$tpl->setVariable("SEL_DATE", $lng->txt("select_date"));
		$tpl->setVariable("DRAG_TO_MOVE", $lng->txt("drag_to_move"));
		$tpl->setVariable("PART_TODAY", $lng->txt("part_today"));
		$tpl->setVariable("DAY_FIRST", $lng->txt("day_first"));
		$tpl->setVariable("CLOSE", $lng->txt("close"));
		$tpl->setVariable("TODAY", $lng->txt("today"));
		$tpl->setVariable("TIME_PART", $lng->txt("time_part"));
		$tpl->setVariable("DEF_DATE_FORMAT", $lng->txt("def_date_format"));
		$tpl->setVariable("TT_DATE_FORMAT", $lng->txt("tt_date_format"));
		$tpl->setVariable("WK", $lng->txt("wk"));
		$tpl->setVariable("TIME", $lng->txt("time"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("CalendarJS");
		$tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", "./Services/Calendar/js/calendar.js");
		$tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", "./Services/Calendar/js/calendar-setup.js");
		$tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", "./Services/Calendar/css/calendar.css");
		$tpl->parseCurrentBlock();
		
		self::$init_done = "done";
	}
	
	public static function getZoneInfoFile($a_tz)
	{
		if(!array_key_exists($a_tz, self::_getShortTimeZoneList()))
		{
			return '';
		}
		$timezone_filename = str_replace('/', '_', $a_tz);
		$timezone_filename .= '.ics';
		return './Services/Calendar/zoneinfo/'.$timezone_filename;
	}


	/**
	 * get short timezone list
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _getShortTimeZoneList()
	{
		return array(
				'Pacific/Samoa' => 'GMT-11: Midway Islands, Samoa', 
				'US/Hawaii' => 'GMT-10:00: Hawaii, Polynesia', 
				'US/Alaska' => 'GMT-9:00: Alaska', 
				'America/Los_Angeles' => 'GMT-8:00: Tijuana, Los Angeles, Seattle, Vancouver', 
				'US/Arizona' => 'GMT-7:00: Arizona', 
				'America/Chihuahua' => 'GMT-7:00: Chihuahua, La Paz, Mazatlan', 
				'America/Denver' => 'GMT-7:00: Arizona, Denver, Salt Lake City, Calgary', 
				'America/Chicago' => 'GMT-6:00: Chicago, Dallas, Kansas City, Winnipeg', 
				'America/Monterrey' => 'GMT-6:00: Guadalajara, Mexico City, Monterrey', 
				'Canada/Saskatchewan' => 'GMT-6:00: Saskatchewan', 
				'US/Central' => 'GMT-6:00: Central America', 
				'America/Bogota' => 'GMT-5:00: Bogota, Lima, Quito', 
				'US/East-Indiana' => 'GMT-5:00: East-Indiana', 
				'America/New_York' => 'GMT-5:00: New York, Miami, Atlanta, Detroit, Toronto', 
				'Canada/Atlantic' => 'GMT-4:00: Atlantic (Canada)', 
				'America/La_Paz' => 'GMT-4:00: Carcas, La Paz', 
				'America/Santiago' => 'GMT-4:00: Santiago', 
				'Canada/Newfoundland' => 'GMT-3:00: Newfoundland', 
				'Brazil/East' => 'GMT-3:00: Sao Paulo', 
				'America/Argentina/Buenos_Aires' => 'GMT-3:00: Buenes Aires, Georgtown', 
				'Etc/GMT+3' => 'GMT-3:00: Greenland, Uruguay, Surinam', 
				'Atlantic/Cape_Verde' => 'GMT-2:00: Cape Verde, Greenland, South Georgia', 
				'Atlantic/Azores' => 'GMT-1:00: Azores', 
				'Africa/Casablanca' => 'GMT+0:00: Casablanca, Monrovia', 
				'Europe/London' => 'GMT+0:00: Dublin, Edinburgh, Lisbon, London', 
				'Europe/Berlin' => 'GMT+1:00: Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna', 
				'Europe/Belgrade' => 'GMT+1:00: Belgrade, Bratislava, Budapest, Ljubljana, Prague', 
				'Europe/Paris' => 'GMT+1:00: Brussels, Copenhagen, Paris, Madrid', 
				'Europe/Sarajevo' => 'GMT+1:00: Sarajevo, Skopje, Warsaw, Zagreb', 
				'Africa/Lagos' => 'GMT+1:00: West-Central Africa', 
				'Europe/Athens' => 'GMT+2:00: Athens, Beirut, Istanbul, Minsk', 
				'Europe/Bucharest' => 'GMT+2:00: Bucharest', 
				'Africa/Harare' => 'GMT+2:00: Harare, Pratoria', 
				'Europe/Helsinki' => 'GMT+2:00: Helsinki, Kiev, Riga, Sofia, Tallinn, Vilnius', 
				'Asia/Jerusalem' => 'GMT+2:00: Jerusalem', 
				'Africa/Cairo' => 'GMT+2:00: Cairo', 
				'Asia/Baghdad' => 'GMT+3:00: Baghdad', 
				'Asia/Kuwait' => 'GMT+3:00: Kuwait, Riyadh', 
				'Europe/Moscow' => 'GMT+3:00: Moscow, Saint Petersburg', 
				'Africa/Nairobi' => 'GMT+3:00: Nairobi',
				'Asia/Tehran' => 'GMT+3:30: Tehran',
				'Asia/Muscat' => 'GMT+4:00: Abu Dhabi, Muscat', 
				'Asia/Baku' => 'GMT+4:00: Baku, Tbilisi, Erivan', 
				'Asia/Kabul' => 'GMT+4:00: Kabul', 
				'Asia/Karachi' => 'GMT+5:00: Islamabad, Karachi, Taschkent', 
				'Asia/Yekaterinburg' => 'GMT+5:00: Yekaterinburg',
				'Asia/Calcutta' => 'GMT+5:30: New Dehli',
				'Asia/Katmandu' => 'GMT+5:45: Katmandu',
				'Asia/Novosibirsk' => 'GMT+6:00: Almaty, Novosibirsk', 
				'Asia/Dhaka' => 'GMT+6:00: Astana, Dhaka', 
				'Asia/Rangoon' => 'GMT+6:00: Sri Jayawardenepura, Rangoon', 
				'Asia/Jakarta' => 'GMT+7:00: Bangkok, Hanoi, Jakarta', 
				'Asia/Krasnoyarsk' => 'GMT+7:00: Krasnoyarsk', 
				'Asia/Irkutsk' => 'GMT+8:00: Irkutsk, Ulan Bator', 
				'Asia/Singapore' => 'GMT+8:00: Kuala Lumpour, Singapore', 
				'Asia/Hong_Kong' => 'GMT+8:00: Beijing, Chongqing, Hong kong, Urumchi', 
				'Australia/Perth' => 'GMT+8:00: Perth', 
				'Asia/Taipei' => 'GMT+8:00: Taipei', 
				'Asia/Yakutsk' => 'GMT+9:00: Yakutsk', 
				'Asia/Tokyo' => 'GMT+9:00: Osaka, Sapporo, Tokyo', 
				'Asia/Seoul' => 'GMT+9:00: Seoul, Darwin, Adelaide', 
				'Australia/Brisbane' => 'GMT+10:00: Brisbane', 
				'Australia/Sydney' => 'GMT+10:00: Canberra, Melbourne, Sydney', 
				'Pacific/Guam' => 'GMT+10:00: Guam, Port Moresby', 
				'Australia/Hobart' => 'GMT+10:00: Hobart', 
				'Asia/Vladivostok' => 'GMT+10:00: Vladivostok', 
				'Asia/Magadan' => 'GMT+11:00: Salomon Islands, New Caledonia, Magadan', 
				'Pacific/Auckland' => 'GMT+12:00: Auckland, Wellington', 
				'Pacific/Fiji' => 'GMT+12:00: Fiji, Kamchatka, Marshall-Islands'); 
	}
	
	
	/**
	 * check if a given year is a leap year
	 *
	 * @access public
	 * @param int year 
	 * @return bool 
	 */
	public static function _isLeapYear($a_year)
	{
		$is_leap = false;
		
		if($a_year % 4 == 0)
		{
			$is_leap = true;
			if($a_year % 100 == 0)
			{
				$is_leap = false;
				if($a_year % 400)
				{
					return true;
				}
			}
		}
		return $is_leap;
	}
	
	/**
	 * get max day of month
	 * 2008,2 => 29
	 *
	 * @access public
	 * @param int year
	 * @param int month
	 * @return
	 */
	public static function _getMaxDayOfMonth($a_year,$a_month)
	{
		if(@function_exists('cal_days_in_month'))
		{
			return cal_days_in_month(CAL_GREGORIAN,$a_month,$a_year);
		}
		$months = array(0,31,
				self::_isLeapYear($a_year) ? 29 : 28,
				31,30,31,30,31,31,30,31,30,31);
		return $months[(int) $a_month];
	}
	
	/**
	 * Calculate best font color from html hex color code
	 *
	 * @access public
	 * @param string hex value of color
	 * @return string #ffffff or #000000
	 * @static
	 */
	public static function calculateFontColor($a_html_color_code)
	{
		if(strpos($a_html_color_code,'#') !== 0 or strlen($a_html_color_code) != 7)
		{
			return '#000000';
		}
		
		// http://en.wikipedia.org/wiki/Luminance_(relative)
		$lum = round(hexdec(substr($a_html_color_code, 1, 2))*0.2126+
			hexdec(substr($a_html_color_code, 3, 2))*0.7152+
			hexdec(substr($a_html_color_code, 5, 2))*0.0722);		
		
		return ($lum <= 128) ? "#FFFFFF" : "#000000";
		
		/*
		$hex = str_replace('#','0x',$a_html_color_code);		
		return hexdec($hex) > 8000000 ? '#000000' : '#FFFFFF';		 
		*/
	}
	
	/**
	 * Get hour selectio depending on user specific hour format.
	 * @return 
	 * @param int $a_format
	 */
	public static function getHourSelection($a_format)
	{
		switch($a_format)
		{
			case ilCalendarSettings::TIME_FORMAT_24:
				for($i = 0; $i < 24; $i++)
				{
					$options[$i] = sprintf('%02d:00',$i);
				}
				break;
				
			case ilCalendarSettings::TIME_FORMAT_12:
				for($i = 0; $i < 24; $i++)
				{
					$options[$i] = date('h a',mktime($i,0,0,1,1,2000));
				}
				break;
		}
		return $options ? $options : array();			
		
	}

	/**
	 * Init the default calendar for given type and user
	 * @param int $a_type_id
	 * @param int $a_usr_id
	 * @param string $a_title
	 * @param bool $create
	 * @return
	 */
	public static function initDefaultCalendarByType($a_type_id, $a_usr_id, $a_title, $a_create = false)
	{
		global $ilDB;

		if(isset(self::$default_calendar[$a_usr_id]) and isset(self::$default_calendar[$a_usr_id][$a_type_id]))
		{
			return self::$default_calendar[$a_usr_id][$a_type_id];
		}

		include_once './Services/Calendar/classes/class.ilCalendarCategory.php';

		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE obj_id = ".$ilDB->quote($a_usr_id,'integer')." ".
			"AND type = ".$ilDB->quote($a_type_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return self::$default_calendar[$a_usr_id][$a_type_id] = new ilCalendarCategory($row->cat_id);
		}

		if(!$a_create)
		{
			return null;
		}

		// Create default calendar
		self::$default_calendar[$a_usr_id][$a_type_id] = new ilCalendarCategory();
		self::$default_calendar[$a_usr_id][$a_type_id]->setType($a_type_id);
		self::$default_calendar[$a_usr_id][$a_type_id]->setColor(ilCalendarCategory::DEFAULT_COLOR);
		self::$default_calendar[$a_usr_id][$a_type_id]->setTitle($a_title);
		self::$default_calendar[$a_usr_id][$a_type_id]->setObjId($a_usr_id);
		self::$default_calendar[$a_usr_id][$a_type_id]->add();

		return self::$default_calendar[$a_usr_id][$a_type_id];
	}
}
?>