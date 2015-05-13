<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
include_once './Services/AccessControl/interfaces/interface.ilConditionHandling.php';

/**
* Class ilObjExerciseAccess
*
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseAccess extends ilObjectAccess implements ilConditionHandling
{
	
	/**
	 * Get possible conditions operators
	 */
	public static function getConditionOperators()
	{
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
		return array(
			ilConditionHandler::OPERATOR_PASSED,
			ilConditionHandler::OPERATOR_FAILED
		);
	}
	
	
	/**
	 * check condition 
	 * @param type $a_exc_id
	 * @param type $a_operator
	 * @param type $a_value
	 * @param type $a_usr_id
	 * @return boolean
	 */
	public static function checkCondition($a_exc_id,$a_operator,$a_value,$a_usr_id)
	{
		include_once './Services/AccessControl/classes/class.ilConditionHandler.php';
		include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
		
		switch($a_operator)
		{
			case ilConditionHandler::OPERATOR_PASSED:
				if (ilExerciseMembers::_lookupStatus($a_exc_id, $a_usr_id) == "passed")
				{
					return true;
				}
				else
				{
					return false;
				}
				break;
				
			case ilConditionHandler::OPERATOR_FAILED:
				return ilExerciseMembers::_lookupStatus($a_exc_id,$a_usr_id) == 'failed';

			default:
				return true;
		}
		return true;
	}
	

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
			array("permission" => "read", "cmd" => "showOverview", "lang_var" => "show",
				"default" => true),
			array("permission" => "write", "cmd" => "listAssignments", "lang_var" => "edit_assignments"),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
		);
		
		return $commands;
	}
	
	function _lookupRemainingWorkingTimeString($a_obj_id)
	{
		global $ilDB;
		
		// #14077 - mind peer deadline, too
		
		$dl = null;
		$cnt = array();
		
		$q = "SELECT id, time_stamp, peer_dl".
			" FROM exc_assignment WHERE exc_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND (time_stamp > ".$ilDB->quote(time(), "integer").
			" OR (peer_dl > ".$ilDB->quote(time(), "integer").
			" AND peer > ".$ilDB->quote(0, "integer")."))";
		$set = $ilDB->query($q);
		while($row = $ilDB->fetchAssoc($set))
		{			
			if($row["time_stamp"] > time() && 
				($row["time_stamp"] < $dl || !$dl))
			{
				$dl = $row["time_stamp"];
			}
			if($row["peer_dl"] > time() && 
				($row["peer_dl"] < $dl || !$dl))
			{
				$dl = $row["peer_dl"];
			}
			$cnt[$row["id"]] = true;			
		}
		
		if($dl)
		{
			$time_diff = ilUtil::int2array($dl - time(), null);
			$dl = ilUtil::timearray2string($time_diff);
		}
		
		return array(
			"mtime" => $dl,
			"cnt" => sizeof($cnt)
		);
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
