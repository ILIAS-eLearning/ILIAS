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

include_once './Services/Calendar/classes/class.ilCalendarRecurrenceExclusion.php';

/** 
* calendar exclusions
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/
class ilCalendarRecurrenceExclusions
{
	
	/**
	 * Read exclusion dates
	 * @param object $a_cal_id
	 * @return 
	 */
	public static function getExclusionDates($a_cal_id)
	{
		global $ilDB;
		
		$query = "SELECT excl_id FROM cal_rec_exclusion ".
			"WHERE cal_id = ".$ilDB->quote($a_cal_id,'integer');
		$res = $ilDB->query($query);
		$exclusions = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$exclusions[] = new ilCalendarRecurrenceExclusion($row->excl_id);
		}
		return $exclusions;
	}
	
	/**
	 * Delete exclusion dates of calendar entry
	 * @param integer $a_cal_id
	 * @return 
	 */
	public static function delete($a_cal_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_rec_exclusion ".
			"WHERE cal_id = ".$ilDB->quote($a_cal_id,'integer');
		$ilDB->manipulate($query);
	}
}
?>