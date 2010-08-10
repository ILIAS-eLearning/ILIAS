<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/class.ilCalendarEntry.php';

/**
* Consultation hour appointments
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilConsultationHourAppointments
{
	
	/**
	 * Get all appointment ids
	 * @param object $a_user_id
	 * @param int $a_context_id
	 * @param string $a_start
	 * @param int $a_type
	 * @return 
	 */
	public static function getAppointmentIds($a_user_id, $a_context_id = NULL, $a_start = NULL, $a_type = NULL)
	{
		global $ilDB;

		if(!$a_type)
		{
			include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
			$a_type = ilCalendarCategory::TYPE_CH;
		}
		
		$query = "SELECT ce.cal_id FROM cal_entries ce ".
			"JOIN cal_cat_assignments cca ON ce.cal_id = cca.cal_id ".
			"JOIN cal_categories cc ON cca.cat_id = cc.cat_id ".
			"WHERE obj_id = ".$ilDB->quote($a_user_id,'integer')." ".
			"AND type = ".$ilDB->quote($a_type);

		if($a_context_id)
		{
			$query .= " AND context_id = ".$ilDB->quote($a_context_id, 'integer');
		}
		if($a_start)
		{
			$query .= " AND starta = ".$ilDB->quote($a_start->get(IL_CAL_DATETIME, '', 'UTC'), 'text');
		}

		$res = $ilDB->query($query);
		$entries = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = $row->cal_id;
		}
		return $entries;
	}
	
	/**
	 * Get all appointments
	 * @return 
	 */
	public static function getAppointments($a_user_id)
	{
		$entries = array();
		foreach(self::getAppointmentIds($a_user_id) as $app_id)
		{
			$entries[] = new ilCalendarEntry($app_id);
		}
		return $entries;
	}
}
?>