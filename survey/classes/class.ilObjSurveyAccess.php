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

include_once("classes/class.ilObjectAccess.php");

/**
* Class ilObjSurveyAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package Survey
*/
class ilObjSurveyAccess extends ilObjectAccess
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
				if (!ilObjSurveyAccess::_lookupCreationComplete($a_obj_id) &&
					(!$rbacsystem->checkAccess('write', $a_ref_id)))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
					return false;
				}
				break;
		}

		switch ($a_cmd)
		{
			case "run":
				if (!ilObjSurveyAccess::_lookupCreationComplete($a_obj_id)
					|| !(ilObjSurveyAccess::_lookupStatus($a_obj_id) == 1))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
					return false;
				}
				break;

			case "evaluation":
				if (!ilObjSurveyAccess::_lookupCreationComplete($a_obj_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("warning_survey_not_complete"));
					return false;
				}
				// maybe an additional evaluation permission would be suitable
				if (!$rbacsystem->checkAccess('write',$a_ref_id) &&
					!ilObjSurveyAccess::_lookupEvaluationAccess($a_obj_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("no_permission"));
					return false;
				}
				break;
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
			array("permission" => "read", "cmd" => "run", "lang_var" => "svy_run",
				"default" => true),
			array("permission" => "write", "cmd" => "", "lang_var" => "edit"),
			array("permission" => "read", "cmd" => "evaluation", "lang_var" => "svy_evaluation")
		);
		
		return $commands;
	}

	//
	// object specific access related methods
	//

	/**
	* checks wether all necessary parts of the survey are given
	*/
	function _lookupCreationComplete($a_obj_id)
	{
		global $ilDB;

		$q = sprintf("SELECT * FROM survey_survey WHERE obj_fi=%s",
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
	* get evaluation access
	*/
	function _lookupEvaluationAccess($a_obj_id)
	{
		global $ilDB;

		$q = sprintf("SELECT * FROM survey_survey WHERE obj_fi=%s",
			$ilDB->quote($a_obj_id)
		);
		$result = $ilDB->query($q);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		}

		return $row->evaluation_access;
	}

	/**
	* get status
	*/
	function _lookupStatus($a_obj_id)
	{
		global $ilDB;

		$q = sprintf("SELECT * FROM survey_survey WHERE obj_fi=%s",
			$ilDB->quote($a_obj_id)
		);
		$result = $ilDB->query($q);
		if ($result->numRows() == 1) {
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		}

		return $row->status;
	}

	/**
	* get finished status
	*
	* @param	int		$a_obj_id		survey id
	*/
	function _lookupFinished($a_obj_id, $a_user_id = "")
	{
		global $ilDB, $ilUser;

		$finished = "";
		if (!strlen($a_user_id))
			$a_user_id = $ilUser->id;

		$q = sprintf("SELECT * FROM survey_survey WHERE obj_fi=%s",
				$ilDB->quote($a_obj_id)
			);
		$result = $ilDB->query($q);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
			if ($row->anonymize == 1)
			{
				$q = sprintf("SELECT * FROM survey_finished WHERE survey_fi = %s AND anonymous_id = %s",
					$ilDB->quote($row->survey_id),
					$ilDB->quote(md5($a_user_id . $row->survey_id) . "")
				);
			}
			else
			{
				$q = sprintf("SELECT * FROM survey_finished WHERE survey_fi = %s AND user_fi = %s",
					$ilDB->quote($row->survey_id),
					$ilDB->quote($a_user_id)
				);
			}
			$result = $ilDB->query($q);
			if ($result->numRows() == 1)
			{
				$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$finished = (int)$row->state;
			}
		}

		return $finished;
	}

}

?>
