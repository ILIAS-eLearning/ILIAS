<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once "./classes/class.ilObjectAccess.php";

/**
* Class ilObjTestAccess
*
* This class contains methods that check object specific conditions
* for accessing test objects.
*
* @author	Helmut Schottmueller <hschottm@tzi.de>
* @author 	Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package AccessControl
*/
class ilObjTestAccess extends ilObjectAccess
{
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here.
	*
	* @param	string		$a_cmd		command (not permission!)
	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id	reference id
	* @param	int			$a_obj_id	object id
	* @param	int			$a_user_id	user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $lng, $rbacsystem, $ilAccess;
		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "visible":
				if (!ilObjTestAccess::_lookupCreationComplete($a_obj_id) &&
					(!$rbacsystem->checkAccess('write', $a_ref_id)))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("tst_warning_test_not_complete"));
					return false;
				}
				break;
		}
		switch ($a_cmd)
		{
			case "eval_a":
			case "eval_stat":
				if (!ilObjTestAccess::_lookupCreationComplete($a_obj_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("tst_warning_test_not_complete"));
					return false;
				}
				break;

		}

		return true;
	}

	/**
	* check condition
	*
	* this method is called by ilConditionHandler
	*/
	function _checkCondition($a_obj_id, $a_operator, $a_value)
	{
		global $ilias;

		switch($a_operator)
		{
			case 'passed':
				$result = ilObjTestAccess::_getTestResult($ilias->account->getId(), $a_exc_id);
				if ($result["passed"] == 1)
				{
					return true;
				}
				else
				{
					return false;
				}
				break;

			case 'finished':
				return ilObjTestAccess::_hasFinished($ilias->account->getId(),$a_exc_id);

			case 'not_finished':
				return !ilObjTestAccess::_hasFinished($ilias->account->getId(),$a_exc_id);

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
			array("permission" => "read", "cmd" => "run", "lang_var" => "tst_run",
				"default" => true),
			array("permission" => "write", "cmd" => "", "lang_var" => "edit"),
			array("permission" => "write", "cmd" => "eval_a", "lang_var" => "tst_anon_eval"),
			array("permission" => "write", "cmd" => "eval_stat", "lang_var" => "tst_statistical_evaluation")
		);
		
		return $commands;
	}

	//
	// object specific access related methods
	//

	/**
	* checks wether all necessary parts of the test are given
	*/
	function _lookupCreationComplete($a_obj_id)
	{
		global $ilDB;

		$q = sprintf("SELECT * FROM tst_tests WHERE obj_fi=%s",
			$ilDB->quote($a_obj_id)
		);
		$result = $ilDB->query($q);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		}

		if (!$row->complete)
		{
			return false;
		}

		return true;
	}

/**
* Returns information if a specific user has finished a test
*
* @param integer $user_id Database id of the user
* @param integer test obj_id
* @return bool
* @access public
* @static
*/
	function _hasFinished($a_user_id,$a_obj_id)
	{
		global $ilDB;

		$query = sprintf("SELECT active_id FROM tst_active WHERE user_fi = %s AND test_fi = %s AND tries > '0'",
			$ilDB->quote($a_user_id . ""),
			$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id) . "")
		);
		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

/**
* Returns the ILIAS test id for a given object id
* 
* Returns the ILIAS test id for a given object id
*
* @param integer $object_id The object id
* @return mixed The ILIAS test id or FALSE if the query was not successful
* @access public
*/
	function _getTestIDFromObjectID($object_id)
	{
		global $ilDB;
		$test_id = FALSE;
		$query = sprintf("SELECT test_id FROM tst_tests WHERE obj_fi = %s",
			$ilDB->quote($object_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$test_id = $row["test_id"];
		}
		return $test_id;
	}
	
/**
* Calculates the results of a test for a given user
* 
* Calculates the results of a test for a given user and
* returns the failed/passed status
*
* @return array An array containing the test results for the given user
* @access public
*/
	function &_getTestResult($user_id, $test_obj_id) 
	{
		global $ilDB;
		
		$test_result = array();
		$query = sprintf("SELECT tst_mark.*, tst_tests.* FROM tst_mark, tst_tests WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.obj_fi = %s ORDER BY tst_mark.minimum_level",
			$ilDB->quote($test_obj_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$test_result["marks"] = array();
			$min_passed_percentage = 100;
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (($row["passed"] == 1) && ($row["minimum_level"] < $min_passed_percentage))
				{
					$min_passed_percentage = $row["minimum_level"];
				}
				array_push($test_result["marks"], $row);
			}
			// count points
			$query = sprintf("SELECT qpl_questions.*, tst_test_result.points AS reached_points FROM qpl_questions, tst_test_result WHERE qpl_questions.question_id = tst_test_result.question_fi AND tst_test_result.test_fi = %s AND tst_test_result.user_fi = %s",
				$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($test_obj_id) . ""),
				$ilDB->quote($user_id . "")
			);
			$result = $ilDB->query($query);
			$max_points = 0;
			$reached_points = 0;
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$max_points += $row["points"];
				switch ($tst_marks[0]["count_system"])
				{
					case 0: // COUNT_PARTIAL_SOLUTIONS
						$reached_points += $row["reached_points"];
						break;
					case 1: // COUNT_CORRECT_SOLUTIONS
						if ($row["reached_points"] == $row["points"])
						{
							$reached_points += $row["reached_points"];
						}
						break;
				}
			}
			$test_result["max_points"] = $max_points;
			$test_result["reached_points"] = $reached_points;
			// calculate the percentage of the reached points
			$solved = 0;
			if ($max_points > 0)
			{
				$solved = ($reached_points / $max_points) * 100.0;
			}
			// get the mark for the reached points
			$mark_percentage = 0;
			$mark_value = null;
			foreach ($test_result["marks"] as $key => $value)
			{
				if (($value["minimum_level"] <= $solved) && ($mark_percentage < $value["minimum_level"]))
				{
					$mark_percentage = $value["minimum_level"];
					$mark_value = $value;
				}
			}
			$test_result["mark"] = $mark_value;
			// get the passed state
			$test_result["passed"] = $test_result["mark"][passed];
		}
		return $test_result;
	}

/**
* Returns true, if a test is complete for use
*
* Returns true, if a test is complete for use
*
* @return boolean True, if the test is complete for use, otherwise false
* @access public
*/
	function _isComplete($a_obj_id)
	{
		global $ilDB;
		
		$test_id = ilObjTestAccess::_getTestIDFromObjectID($a_obj_id);
		$query = sprintf("SELECT tst_mark.*, tst_tests.* FROM tst_tests, tst_mark WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.test_id = %s",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		$found = $result->numRows();
		if ($found)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			// check for at least: title, author and minimum of 1 mark step
			if ((strlen($row["title"])) &&
				(strlen($row["author"])) &&
				($found))
			{
				// check also for minmum of 1 question
				if (ilObjTestAccess::_getQuestionCount($test_id) > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		$test = new ilObjTest($obj_id, false);
		$test->loadFromDb();
		if (($test->getTitle()) and ($test->author) and (count($test->mark_schema->mark_steps)) and (count($test->questions)))
		{
			return true;
		} 
			else 
		{
			return false;
		}
	}
	
/**
* Calculates the number of questions in a test
*
* Calculates the number of questions in a test
*
* @return int The number of questions in the test
* @access public
*/
	function _getQuestionCount($a_test_id)
	{
		global $ilDB;

		$num = 0;
		
		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($a_test_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows != 1) return 0;
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		if ($row["random_test"] == 1)
		{
			if ($row["random_question_count"] > 0)
			{
				$num = $row["random_question_count"];
			}
				else
			{
				$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s ORDER BY test_random_id",
					$ilDB->quote($a_test_id . "")
				);
				$result = $ilDB->query($query);
				if ($result->numRows())
				{
					while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
					{
						$num += $row["num_of_q"];
					}
				}
			}
		}
		else
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s",
				$ilDB->quote($a_test_id . "")
			);
			$result = $ilDB->query($query);
			$num = $result->numRows();
		}
		return $num;
	}
	
/**
* Checks if a user is allowd to run an online exam
*
* Checks if a user is allowd to run an online exam
*
* @return mixed true if the user is allowed to run the online exam or if the test isn't an online exam, an alert message if the test is an online exam and the user is not allowed to run it
* @access public
*/
	function _lookupOnlineTestAccess($a_test_id, $a_user_id)
	{
		global $ilDB, $lng;
		
		$test_result = array();
		$query = sprintf("SELECT tst_tests.* FROM tst_tests WHERE tst_tests.obj_fi = %s",
			$ilDB->quote($a_test_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["test_type_fi"] == 4)
			{
				$query = sprintf("SELECT * FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
					$ilDB->quote($row["test_id"] . ""),
					$ilDB->quote($a_user_id . "")
				);
				$result = $ilDB->query($query);
				if ($result->numRows())
				{
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					if (strcmp($row["clientip"],"")!=0 && strcmp($row["clientip"],$_SERVER["REMOTE_ADDR"])!=0)
					{
						return $lng->txt("tst_user_wrong_clientip");
					}
					else
					{
						return true;
					}
				}
				else
				{
					return $lng->txt("tst_user_not_invited");
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			return true;
		}
	}

}

?>
