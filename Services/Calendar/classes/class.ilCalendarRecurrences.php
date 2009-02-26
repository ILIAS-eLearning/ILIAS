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

include_once('Services/Calendar/classes/class.ilCalendarRecurrence.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarRecurrences
{
	/**
	 * get all recurrences of an appointment
	 *
	 * @access public
	 * @param int cal_id
	 * @return array array of ilCalendarRecurrence
	 * @static
	 */
	public static function _getRecurrences($a_cal_id)
	{
		global $ilDB;
		
		$query = "SELECT rule_id FROM cal_recurrence_rules ".
			"WHERE cal_id = ".$ilDB->quote($a_cal_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$recurrences[] = new ilCalendarRecurrence($row->rule_id);
		}
		return $recurrences ? $recurrences : array();
	}
	
	/**
	 * get first recurrence
	 *
	 * @access public
	 * @param int cal id
	 * @return
	 * @static
	 */
	public static function _getFirstRecurrence($a_cal_id)
	{
		if($recs = self::_getRecurrences($a_cal_id))
		{
			return $recs[0];
		}
		$new_rec = new ilCalendarRecurrence();
		$new_rec->setEntryId($a_cal_id);
		return $new_rec;
	}

}
?>