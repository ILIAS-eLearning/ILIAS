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
	public static function getAppointmentIds($a_user_id, $a_context_id = NULL, $a_start = NULL, $a_type = NULL, $a_check_owner = true)
	{
		global $ilDB;

		if(!$a_type)
		{
			include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
			$a_type = ilCalendarCategory::TYPE_CH;
		}
		$owner = ' ';
		if($a_check_owner)
		{
			$owner = " AND be.obj_id = ".$ilDB->quote($a_user_id,'integer');
		}

		$query = "SELECT ce.cal_id FROM cal_entries ce".
			" JOIN cal_cat_assignments cca ON ce.cal_id = cca.cal_id".
			" JOIN cal_categories cc ON cca.cat_id = cc.cat_id".
			" JOIN booking_entry be ON ce.context_id  = be.booking_id".
			" WHERE cc.obj_id = ".$ilDB->quote($a_user_id,'integer').
			$owner.
			" AND cc.type = ".$ilDB->quote($a_type,'integer');

		
		if($a_context_id)
		{
			$query .= " AND ce.context_id = ".$ilDB->quote($a_context_id, 'integer');
		}
		if($a_start)
		{
			$query .= " AND ce.starta = ".$ilDB->quote($a_start->get(IL_CAL_DATETIME, '', 'UTC'), 'text');
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
	 * Get appointment ids by consultation hour group
	 * @param type $a_user_id
	 * @param type $a_ch_group_id
	 * @param ilDateTime $start
	 */
	public static function getAppointmentIdsByGroup($a_user_id, $a_ch_group_id, ilDateTime $start = null)
	{
		global $ilDB;
		
		// @todo check start time
		
		include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
		$type = ilCalendarCategory::TYPE_CH;
		
		$start_limit = '';
		if($start instanceof ilDateTime)
		{
			$start_limit = 'AND ce.starta >= '.$ilDB->quote($start->get(IL_CAL_DATETIME,'','UTC'),'timestamp');
		}
		
		$query = 'SELECT ce.cal_id FROM cal_entries ce '.
				'JOIN cal_cat_assignments ca ON ce.cal_id = ca.cal_id '.
				'JOIN cal_categories cc ON ca.cat_id = cc.cat_id '.
				'JOIN booking_entry be ON ce.context_id = be.booking_id '.
				'WHERE cc.obj_id = '.$ilDB->quote($a_user_id,'integer').' '.
				'AND cc.type = '.$ilDB->quote($type,'integer').' '.
				'AND be.booking_group = '.$ilDB->quote($a_ch_group_id,'integer').' '.
				$start_limit.' '.
				'ORDER BY ce.starta ';
		$res = $ilDB->query($query);
		$app_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$app_ids[] = $row->cal_id;
		}
		return $app_ids;
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

	/**
	 * Get consultation hour manager for current user
	 * @param	string	$a_as_name
	 * @return	int | string
	 */
	public static function getManager($a_as_name = false)
	{
		global $ilDB, $ilUser;
		
		$set = $ilDB->query('SELECT admin_id FROM cal_ch_settings'.
			' WHERE user_id = '.$ilDB->quote($ilUser->getId(), 'integer'));
		$row = $ilDB->fetchAssoc($set);
		if($row && $row['admin_id'])
		{
			if($a_as_name)
			{
				return ilObjUser::_lookupLogin($row['admin_id']);
			}
			return (int)$row['admin_id'];
		}
	}

	/**
	 * Set consultation hour manager for current user
	 * @param	string	$a_user_name
	 * @return bool
	 */
	public static function setManager($a_user_name)
	{
		global $ilDB, $ilUser;

		$user_id = false;
		if($a_user_name)
		{
			$user_id = ilObjUser::_loginExists($a_user_name);
			if(!$user_id)
			{
				return false;
			}
		}

		$ilDB->manipulate('DELETE FROM cal_ch_settings'.
				' WHERE user_id = '.$ilDB->quote($ilUser->getId(), 'integer'));
		
		if($user_id && $user_id != $ilUser->getId())
		{
			$ilDB->manipulate('INSERT INTO cal_ch_settings (user_id, admin_id)'.
					' VALUES ('.$ilDB->quote($ilUser->getId(), 'integer').','.
					$ilDB->quote($user_id, 'integer').')');
		}

		return true;
	}

	/**
	 * Get all managed consultation hours users for current users
	 * @return array
	 */
	public static function getManagedUsers()
	{
		global $ilDB, $ilUser;

		$all = array();
		$set = $ilDB->query('SELECT user_id FROM cal_ch_settings'.
			' WHERE admin_id = '.$ilDB->quote($ilUser->getId(), 'integer'));
		while($row = $ilDB->fetchAssoc($set))
		{
			$all[$row['user_id']] = ilObjUser::_lookupLogin($row['user_id']);
		}
		return $all;
	}
}
?>