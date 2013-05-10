<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourGroups
{

	/**
	 * Get a all groups of an user
	 * @param int $a_user_id
	 * @return array
	 */
	public static function getGroupsOfUser($a_user_id)
	{
		global $ilDB;
		
		$query = 'SELECT grp_id FROM cal_ch_group '.
				'WHERE usr_id = '.$ilDB->quote($a_user_id,'integer');
		$res = $ilDB->query($query);
		$groups = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
			$groups[] = new ilConsultationHourGroup($row->grp_id);
		}
		return $groups;
	}

	/**
	 * Get number of consultation hour groups
	 * @global type $ilDB
	 * @param type $a_user_id
	 * @return int
	 */
	public static function getCountGroupsOfUser($a_user_id)
	{
		global $ilDB;
		
		$query = 'SELECT COUNT(grp_id) num FROM cal_ch_group '.
				'WHERE usr_id = '.$ilDB->quote($a_user_id,'integer').' '.
				'GROUP BY grp_id';
		
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		return (int) $row->num;
	}
	
	/**
	 * Lookup number of assigned appointments
	 */
	public static function lookupAssignedAppointments()
	{
		global $ilDB;
		
		//@todo
	}
	
	/**
	 * Lookup group title
	 */
	public static function lookupTitle($a_group_id)
	{
		global $ilDB;
		
		$query = 'SELECT title from cal_ch_group '.
				'WHERE grp_id = '.$ilDB->quote($a_group_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->title;
		}
		return '';
	}

	/**
	 * Lookup max number of bookings for group
	 * @global type $ilDB
	 * @param type $a_group_id
	 * @return int
	 */
	public static function lookupMaxBookings($a_group_id)
	{
		global $ilDB;
		
		$query = 'SELECT multiple_assignments from cal_ch_group '.
				'WHERE grp_id = '.$ilDB->quote($a_group_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->multiple_assignments;
		}
		return 0;
	}


	/**
	 * Get group selection options
	 * @param type $a_user_id
	 */
	public static function getGroupSelectOptions($a_user_id)
	{
		global $lng;
		
		$groups = self::getGroupsOfUser($a_user_id);
		if(!count($groups))
		{
			return array();
		}
		$options = array();
		foreach($groups as $group)
		{
			$options[(string) $group->getGroupId()] = $group->getTitle();
		}
		asort($options,SORT_STRING);
		$sorted_options = array();
		$sorted_options[0] = $lng->txt('cal_ch_grp_no_assignment');
		foreach($options as $key => $opt)
		{
			$sorted_options[$key] = $opt;
		}
		return $sorted_options;
	}
}
?>
