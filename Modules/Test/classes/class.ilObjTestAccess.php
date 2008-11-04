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
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

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
* @ingroup ModulesTest
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
	* Returns the maximum number of points available for a test pass
	*
	* Returns the maximum number of points available for a test pass
	*
	* @param boolean $random TRUE if the test is a random test, otherwise FALSE
	* @param int $test_id The test id
	* @param int $pass The test pass
	* @return int The available points for the test pass
	*/
	function _getMaxPointsForTestPass($random, $user_id, $test_id, $pass)
	{
		global $ilDB;
		$max = 0;
		if ($random)
		{
			$query = sprintf("SELECT SUM(qpl_questions.points) AS maxpoints FROM tst_test_random_question, qpl_questions, tst_active WHERE tst_active.active_id = tst_test_random_question.active_fi AND tst_test_random_question.question_fi = qpl_questions.question_id AND tst_active.test_fi = %s AND tst_test_random_question.pass = %s AND tst_active.user_fi = %s",
				$ilDB->quote($test_id . ""),
				$ilDB->quote($pass . ""),
				$ilDB->quote($user_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$max = $row["maxpoints"];
			}
		}
		else
		{
			$query = sprintf("SELECT SUM(qpl_questions.points) AS maxpoints FROM tst_test_question, qpl_questions WHERE tst_test_question.question_fi = qpl_questions.question_id AND tst_test_question.test_fi = %s",
				$ilDB->quote($test_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$max = $row["maxpoints"];
			}
		}
		return $max;
	}
	
	/**
	* Returns TRUE if the user with the user id $user_id passed the test with the object id $a_obj_id
	*
	* Returns TRUE if the user with the user id $user_id passed the test with the object id $a_obj_id
	*
	* @param int $user_id The user id
	* @param int $a_obj_id The object id
	* @return boolean TRUE if the user passed the test, FALSE otherwise
	*/
	function _isPassed($user_id, $a_obj_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tst_test_pass_result.*, tst_tests.pass_scoring, tst_tests.random_test, tst_tests.test_id FROM tst_test_pass_result, tst_active, tst_tests, object_data WHERE object_data.obj_id = tst_tests.obj_fi AND tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND object_data.obj_id = %s AND tst_test_pass_result.active_fi = tst_active.active_id ORDER BY tst_test_pass_result.pass",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($a_obj_id . "")
		);
		$result = $ilDB->query($query);
		$points = array();
		while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			array_push($points, $row);
		}
		$reached = 0;
		$max = 0;
		if ($points[0]["pass_scoring"] == 0)
		{
			$reached = $points[count($points)-1]["points"];
			$max = ilObjTestAccess::_getMaxPointsForTestPass($points[count($points)-1]["random_test"], $user_id, $points[count($points)-1]["test_id"], $points[count($points)-1]["pass"]);
		}
		else
		{
			foreach ($points as $row)
			{
				if ($row["points"] > $reached) 
				{
					$max = ilObjTestAccess::_getMaxPointsForTestPass($row["random_test"], $user_id, $row["test_id"], $row["pass"]);
					$reached = $row["points"];
				}
			}
		}
		include_once "./Modules/Test/classes/class.assMarkSchema.php";
		$percentage = (!$max) ? 0 : ($reached / $max) * 100.0;
		$mark = ASS_MarkSchema::_getMatchingMarkFromObjId($a_obj_id, $percentage);
		return ($mark["passed"]) ? TRUE : FALSE;
	}

	/**
	* check condition
	*
	* this method is called by ilConditionHandler
	*/
	function _checkCondition($a_obj_id, $a_operator, $a_value, $a_usr_id = 0)
	{
		global $ilUser;
		
		$a_usr_id = $a_usr_id ? $a_usr_id : $iUser->getId();
		
		switch($a_operator)
		{
			case 'passed':
				return ilObjTestAccess::_isPassed($a_usr_id, $a_obj_id);
				break;

			case 'finished':
				return ilObjTestAccess::_hasFinished($a_usr_id,$a_obj_id);

			case 'not_finished':
				return !ilObjTestAccess::_hasFinished($a_usr_id,$a_obj_id);

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
			array("permission" => "tst_statistics", "cmd" => "outEvaluation", "lang_var" => "tst_statistical_evaluation")
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
			$row = $result->fetchRow(MDB2_FETCHMODE_OBJECT);
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
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
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
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
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
			while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
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
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					array_push($questions, $row);
				}
			}
		}
		return $questions;
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
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
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
	* Returns the database content of a test with a given id
	*
	* Returns the database content of a test with a given id
	*
	* @param int $test_id Database id of the test
	* @return array An associative array with the contents of the tst_tests database row
	* @access public
	*/
	function &_getTestData($test_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		if (!$result->numRows())
		{
			return 0;
		}
		$test = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
		return $test;
	}
	
/**
* Calculates the number of questions in a test
*
* Calculates the number of questions in a test
*
* @return int The number of questions in the test
* @access public
*/
function _getQuestionCount($test_id)
{
	global $ilDB;

	$num = 0;

	$test =& ilObjTestAccess::_getTestData($test_id);

	if ($test["random_test"] == 1)
	{
		if ($test["random_question_count"] > 0)
		{
			$num = $test["random_question_count"];
		}
		else
		{
			$query = sprintf("SELECT SUM(num_of_q) AS questioncount FROM tst_test_random WHERE test_fi = %s ORDER BY test_random_id",
				$ilDB->quote($test_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
				$num = $row["questioncount"];
			}
		}
	}
	else
	{
		$query = sprintf("SELECT test_question_id FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($test_id . "")
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
			$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
			if ($row["fixed_participants"])
			{
				$query = sprintf("SELECT * FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
					$ilDB->quote($row["test_id"] . ""),
					$ilDB->quote($a_user_id . "")
				);
				$result = $ilDB->query($query);
				if ($result->numRows())
				{
					$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
					if (trim($row['clientip']) != "")
					{
						$row['clientip'] = preg_replace("/[^0-9.?*,:]+/","",$row['clientip']);
						$row['clientip'] = str_replace(".","\\.",$row['clientip']);
						$row['clientip'] = str_replace(Array("?","*",","), Array("[0-9]","[0-9]*","|"), $row['clientip']);
						if (!preg_match("/^".$row['clientip']."$/", $_SERVER["REMOTE_ADDR"])) 
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
	* Retrieves a participant name from active id
	*
	* @param integer $active_id Active ID of the participant
	* @return string The output name of the user
	* @access public
	*/
	function _getParticipantData($active_id)
	{
		global $lng, $ilDB;

		$statement = $ilDB->prepare("SELECT user_fi, test_fi FROM tst_active WHERE active_id = ?",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$active_id
			)
		);
		$row = $ilDB->fetchAssoc($result);
		$user_id = $row["user_fi"];
		$test_id = $row["test_fi"];

		$statement = $ilDB->prepare("SELECT obj_fi FROM tst_tests WHERE test_id = ?",
			array(
				"integer"
			)
		);
		$result = $ilDB->execute($statement, 
			array(
				$test_id
			)
		);
		$row = $ilDB->fetchAssoc($result);
		$obj_id = $row["obj_fi"];
		
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$is_anonymous = ilObjTest::_lookupAnonymity($obj_id);
		
		include_once './Services/User/classes/class.ilObjUser.php';
		$uname = ilObjUser::_lookupName($user_id);

		$name = "";
		if (strlen($uname["firstname"].$uname["lastname"]) == 0)
		{
			$name = $lng->txt("deleted_user");
		}
		else
		{
			if ($user_id == ANONYMOUS_USER_ID)
			{
				$name = $lastname;
			}
			else
			{
				$name = trim($uname["lastname"] . ", " . $uname["firstname"] . " " .  $uname["title"]);
			}
			if ($is_anonymous)
			{
				$name = $lng->txt("anonymous");
			}
		}
		return $name;
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
		global $ilDB;

		$passed_users = array();
		$query = sprintf("SELECT tst_active.user_fi FROM tst_active, tst_tests WHERE tst_tests.test_id = tst_active.test_fi AND tst_tests.obj_fi = %s",
			$ilDB->quote($a_obj_id . "")
		);
		$userresult = $ilDB->query($query);
		if ($userresult->numRows())
		{
			while ($userrow = $userresult->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$user_id = $userrow["user_fi"];
				$query = sprintf("SELECT tst_test_pass_result.*, tst_tests.pass_scoring, tst_tests.random_test, tst_tests.test_id FROM tst_test_pass_result, tst_active, tst_tests, object_data WHERE object_data.obj_id = tst_tests.obj_fi AND tst_active.test_fi = tst_tests.test_id AND tst_active.user_fi = %s AND object_data.obj_id = %s AND tst_test_pass_result.active_fi = tst_active.active_id ORDER BY tst_test_pass_result.pass",
					$ilDB->quote($user_id . ""),
					$ilDB->quote($a_obj_id . "")
				);
				$result = $ilDB->query($query);
				$points = array();
				while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					array_push($points, $row);
				}
				$reached = 0;
				$max = 0;
				if ($points[0]["pass_scoring"] == 0)
				{
					$reached = $points[count($points)-1]["points"];
					$max = ilObjTestAccess::_getMaxPointsForTestPass($points[count($points)-1]["random_test"], $user_id, $points[count($points)-1]["test_id"], $points[count($points)-1]["pass"]);
				}
				else
				{
					foreach ($points as $row)
					{
						if ($row["points"] > $reached) 
						{
							$max = ilObjTestAccess::_getMaxPointsForTestPass($row["random_test"], $user_id, $row["test_id"], $row["pass"]);
							$reached = $row["points"];
						}
					}
				}
				include_once "./Modules/Test/classes/class.assMarkSchema.php";
				$percentage = (!$max) ? 0 : ($reached / $max) * 100.0;
				$mark = ASS_MarkSchema::_getMatchingMarkFromObjId($a_obj_id, $percentage);
				array_push($passed_users, 
					array(
						"user_id" => $user_id,
						"max_points" => $max,
						"reached_points" => $reached,
						"mark_short" => $mark["short_name"],
						"mark_official" => $mark["official_name"],
						"passed" => ($mark["passed"]) ? TRUE : FALSE,
						"failed" => (!$mark["passed"]) ? TRUE : FALSE
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
