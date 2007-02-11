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


/**
* Class ilCalendarUtil
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
*/
class ilCalendarUtil
{
	static $init_done;
	
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
		$tpl->addBlockFile("CALENDAR_LANG_JAVASCRIPT", "calendar_javascript", "tpl.calendar.html");
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
}
?>
