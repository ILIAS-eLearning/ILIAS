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

include_once("classes/class.ilObjectAccess.php");

/**
* Class ilObjExerciseAccess
*
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseAccess extends ilObjectAccess
{

	/**
	 * get commands
	 * 
	 * this method returns an array of all possible commands/permission combinations
	 * 
	 * example:	
	 * $commands = array
	 *	(
	 *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 *	);
	 */
	function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "show",
				"default" => true),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit")
		);
		
		return $commands;
	}
	
	function _lookupRemainingWorkingTimeString($a_obj_id)
	{
		global $ilDB, $lng;
		
		$q = "SELECT time_stamp FROM exc_data WHERE obj_id = ".
			$ilDB->quote($a_obj_id, "integer");
		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);
		
		if ($rec["time_stamp"] - time() <= 0)
		{
			$time_str = $lng->txt("exc_time_over_short");
		}
		else
		{
			$time_diff = ilUtil::int2array($rec["time_stamp"] - time(), null);
			$time_str = ilUtil::timearray2string($time_diff);
		}
		return $time_str;
	}
	
	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "exc" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
}

?>
