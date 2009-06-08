<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilOnlineTracking
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
* Stores total online time of users
*
*/

class ilOnlineTracking
{
	// Static
	function _getOnlineTime($a_user_id)
	{
		global $ilDB;

		$res = $ilDB->query("SELECT * FROM ut_online WHERE usr_id = ".
			$ilDB->quote($a_user_id, "integer"));
		while ($row = $ilDB->fetchObject($res))
		{
			$access_time = $row->access_time;
			$online_time = $row->online_time;
		}
		return (int) $online_time;
	}

		

	function _addUser($a_user_id)
	{
		global $ilDB;

		$res = $ilDB->query("SELECT * FROM ut_online WHERE usr_id = ".
			$ilDB->quote($a_user_id, "integer"));
		
		if ($ilDB->fetchAssoc($res))
		{
			return false;
		}
		$ilDB->manipulate(sprintf("INSERT INTO ut_online (usr_id, access_time) VALUES (%s,%s)",
			$ilDB->quote($a_user_id, "integer"),
			$ilDB->quote(time(), "integer")));

		return true;
	}

	function _updateAccess($a_usr_id)
	{
		global $ilDB,$ilias;

		$res = $ilDB->query("SELECT * FROM ut_online WHERE usr_id = ".
			$ilDB->quote($a_usr_id, "integer"));

		if (!$ilDB->fetchAssoc($res))
		{
			return false;
		}
		while ($row = $ilDB->fetchObject($res))
		{
			$access_time = $row->access_time;
			$online_time = $row->online_time;
		}
		
		$time_span = (int) $ilias->getSetting("tracking_time_span",300);

		if(($diff = time() - $access_time) <= $time_span)
		{
			$ilDB->manipulate(sprintf("UPDATE ut_online SET online_time = online_time + %s, ".
				"access_time = %s WHERE usr_id = %s",
				$ilDB->quote($diff, "integer"),
				$ilDB->quote(time(), "integer"),
				$ilDB->quote($a_usr_id, "integer")));
		}
		else
		{
			$ilDB->manipulate(sprintf("UPDATE ut_online SET ".
				"access_time = %s WHERE usr_id = %s",
				$ilDB->quote(time(), "integer"),
				$ilDB->quote($a_usr_id, "integer")));
		}
		return true;
	}
}
?>