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

include_once "./classes/class.ilObjectAccess.php";
include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Class ilObjTestAccess
*
* This class contains methods that check object specific conditions
* for accessing test objects.
*
* @author	Helmut Schottmueller <helmut.schottmueller@mac.com>
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
			case "read":
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
				include_once "./assessment/classes/class.ilObjTest.php";
				$test_id = ilObjTest::_getTestIDFromObjectID($a_obj_id);
				$active = ilObjTest::_getActiveTestUser($ilias->account->getId(), $test_id);
				if(!is_object($active))
				{
					return false;
				}
				$result = ilObjTestAccess::_getTestResult($active->active_id);
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
				return ilObjTestAccess::_hasFinished($ilias->account->getId(),$a_obj_id);

			case 'not_finished':
				return !ilObjTestAccess::_hasFinished($ilias->account->getId(),$a_obj_id);

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
			array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "tst_run",
				"default" => true),
			array("permission" => "write", "cmd" => "", "lang_var" => "edit"),
			array("permission" => "write", "cmd" => "evalAllUsers", "lang_var" => "tst_statistical_evaluation")
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
	
	function &_getTestQuestions($active_id, $pass = NULL)
	{
		if (is_null($pass))
		{
			$pass = 0;
		}
		$questions = array();
		
		global $ilDB;
		$query = sprintf("SELECT test_fi FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$test_id = "";
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$test_id = $row["test_fi"];
		}
		else
		{
			return $questions;
		}
		$query = sprintf("SELECT qpl_questions.question_id, qpl_questions.points FROM qpl_questions, tst_test_question WHERE tst_test_question.question_fi = qpl_questions.question_id AND tst_test_question.test_fi = %s ORDER BY tst_test_question.sequence",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			// standard test
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($questions, $row);
			}
		}
		else
		{
			// random test
			$query = sprintf("SELECT qpl_questions.question_id, qpl_questions.points FROM qpl_questions, tst_test_random_question WHERE tst_test_random_question.question_fi = qpl_questions.question_id AND tst_test_random_question.active_fi = %s AND tst_test_random_question.pass = %s ORDER BY tst_test_random_question.sequence",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($questions, $row);
				}
			}
		}
		return $questions;
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
	function &_getTestResult($active_id, $pass = NULL) 
	{
		global $ilDB;
		
		$test_result = array();
		$query = sprintf("SELECT tst_mark.*, tst_tests.* FROM tst_mark, tst_tests, tst_active WHERE tst_mark.test_fi = tst_tests.test_id AND tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s ORDER BY tst_mark.minimum_level",
			$ilDB->quote($active_id . "")
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
			
			$questions =& ilObjTestAccess::_getTestQuestions($active_id, $pass);
			$max_points = 0;
			$reached_points = 0;
			foreach ($questions as $row)
			{
				include_once "./assessment/classes/class.assQuestion.php";
				$preached = assQuestion::_getReachedPoints($active_id, $row["question_id"], $pass);
				$max_points += $row["points"];
				$reached_points += $preached;
			}
			switch ($test_result["marks"][0]["score_cutting"])
			{
				case 0: // SCORE_CUT_QUESTION
					break;
				case 1: // SCORE_CUT_TEST
					if ($reached_points < 0) $reached_points = 0;
					break;
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
			$test_result["passed"] = $test_result["mark"]["passed"];
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
		
		$query = sprintf("SELECT tst_tests.* FROM tst_tests WHERE tst_tests.obj_fi = %s",
			$ilDB->quote($a_test_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["fixed_participants"])
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

/**
* Returns an array containing the users who passed the test
* 
* Returns an array containing the users who passed the test
*
* @return array An array containing the users who passed the test.
*         Format of the values of the resulting array:
*           array(
*             "user_id"        => user ID,
*             "max_points"     => maximum available points in the test
*             "reached_points" => maximum reached points of the user
*             "mark_short"     => short text of the passed mark
*             "mark_official"  => official text of the passed mark
*           )
* @access public
*/
	function &_getPassedUsers($a_obj_id)
	{
		include_once 'assessment/classes/class.ilObjTest.php';

		global $ilDB;
		
		$passed_users = array();
/*		$query = sprintf("SELECT tst_active.* FROM tst_active, tst_tests ".
						 "WHERE tst_tests.obj_fi = %s AND tst_active.tries > 0 ".
						 "AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($a_obj_id . "")
		);*/
		$query = sprintf("SELECT tst_active.* FROM tst_active, tst_tests ".
						 "WHERE tst_tests.obj_fi = %s ".
						 "AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($a_obj_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$tries = $row['tries'];
				$user_id = $row["user_fi"];
				$test_id = $row["test_fi"];
				$active_id = $row["active_id"];
				include_once "./assessment/classes/class.ilObjTest.php";
				$pass = ilObjTest::_getResultPass($active_id);
				$testres =& ilObjTestAccess::_getTestResult($active_id, $pass);
				array_push($passed_users, 
						   array(
							   "user_id" => $user_id,
							   "max_points" => $testres["max_points"],
							   "reached_points" => $testres["reached_points"],
							   "mark_short" => $testres["mark"]["short_name"],
							   "mark_official" => $testres["mark"]["official_name"],
							   "passed" => (bool) $testres['passed'],
							   "failed" => ($tries and !$testres['passed'])
							   )
					);
			}
		}
		return $passed_users;
	}

	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "tst" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}

}

?>
