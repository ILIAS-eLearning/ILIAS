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

/**
 * Consultation hour utility functions
 * 
 * @ilCtrl_Calls: ilConsultationHoursGUI:
 */
class ilConsultationHourUtils
{
	
	private static $default_calendar = null;
	
	
	/**
	 * Init the default calendar for personal consultation hours
	 * @param int $a_usr_id
	 * @param bool $create
	 * @return 
	 */
	public static function initDefaultCalendar($a_usr_id,$a_create = false)
	{
		global $ilDB,$lng;
		
		if(isset(self::$default_calendar))
		{
			return self::$default_calendar;
		}	
		
		include_once './Services/Calendar/classes/class.ilCalendarCategory.php';		

		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE obj_id = ".$ilDB->quote($a_usr_id,'integer')." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_CH,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return self::$default_calendar = new ilCalendarCategory($row->cat_id);
		}
		
		if(!$a_create)
		{
			return null;
		}
		
		// Create default calendar
		self::$default_calendar = new ilCalendarCategory();
		self::$default_calendar->setType(ilCalendarCategory::TYPE_CH);
		self::$default_calendar->setColor(ilCalendarCategory::DEFAULT_COLOR);
		self::$default_calendar->setTitle($lng->txt('cal_ch_personal_ch'));
		self::$default_calendar->setObjId($a_usr_id);
		self::$default_calendar->add();
		
		return self::$default_calendar;
	}
}
