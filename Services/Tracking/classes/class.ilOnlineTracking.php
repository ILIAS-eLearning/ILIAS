<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Class ilOnlineTracking
*
* @author Stefan Meyer <smeyer@databay.de>
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

		$st = $ilDB->prepare("SELECT * FROM ut_online WHERE usr_id = ?",
			array("integer"));
		$res = $ilDB->execute($st, array($a_user_id));
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

		$st = $ilDB->prepare("SELECT * FROM ut_online WHERE usr_id = ?",
			array("integer"));
		$res = $ilDB->execute($st, array($a_user_id));
		
		if ($ilDB->fetchAssoc($res))
		{
			return false;
		}
		$st = $ilDB->prepareManip("INSERT INTO ut_online (usr_id, access_time) VALUES (?,?)",
			array("integer", "integer"));
		$ilDB->execute($st, array($a_user_id, time()));

		return true;
	}

	function _updateAccess($a_usr_id)
	{
		global $ilDB,$ilias;

		$st = $ilDB->prepare("SELECT * FROM ut_online WHERE usr_id = ?",
			array("integer"));
		$res = $ilDB->execute($st, array($a_user_id));

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
			$st = $ilDB->prepareManip("UPDATE ut_online SET online_time = online_time + ?, ".
				"access_time = ? WHERE usr_id = ?", array("integer", "integer", "integer"));
			$ilDB->execute($st, array($diff, time(), $a_usr_id));
		}
		else
		{
			$st = $ilDB->prepareManip("UPDATE ut_online SET ".
				"access_time = ? WHERE usr_id = ?", array("integer", "integer"));
			$ilDB->execute($st, array(time(), $a_usr_id));
		}
		return true;
	}
}
?>