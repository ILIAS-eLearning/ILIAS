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

/**
* Handles conditions for accesses to different ILIAS objects
*
* A condition consists of four elements:
* - a trigger object, e.g. a test or a survey question
* - an operator, e.g. "=", "<", "passed"
* - an (optional) value, e.g. "5"
* - a target object, e.g. a learning module
*
* If a condition is fulfilled for a certain user, (s)he may access
* the target object. This first implementation handles only one access
* type per object, which is usually "read" access. A possible
* future extension may implement different access types.
*
* The condition data is stored in the database table "condition"
* (Note: This table must not be accessed directly from other classes.
* The data should be accessed via the interface of class ilCondition.)
*   cond_id					INT			condition id
*   trigger_obj_type		VARCHAR(10)	"crs" | "tst" | "qst", ...
*   trigger_id				INT			obj id of trigger object
*   operator				varchar(10  "=", "<", ">", ">=", "<=", "passed", "contains", ...
*   value					VARCHAR(10) optional value
*   target_obj_type			VARCHAR(10)	"lm" | "frm" | "st" | "pg", ...
*   target_id				object or reference id of target object
*
* Trigger objects are always stored with their object id (if a test has been
* passed by a user, he doesn't need to repeat it in other contexts. But
* target objects are usually stored with their reference id if available,
* otherwise, if they are non-referenced objects (e.g. (survey) questions)
* they are stored with their object id.
*
* Examples:
*
* Learning module 5 may only be accessed, if test 6 has been passed:
*   trigger_obj_type		"tst"
*   trigger_id				6 (object id)
*   operator				"passed"
*   value
*   target_obj_type			"lm"
*   target_id				5 (reference id)
*
* Survey question 10 should only be presented, if survey question 8
* is answered with a value greater than 4.
*   trigger_obj_type		"qst"
*   trigger_id				8 (question (instance) object id)
*   operator				">"
*   value					"4"
*   target_obj_type			"lm"
*   target_id				10 (question (instance) object id)
*
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilConditionHandler
{

	/**
	* constructor
	* @access	public
	*/
	function ilConditionHandler ()
	{
		[...]
	}

	/**
	* store new condition in database
	* @access	public
	*/
	function _storeCondition($a_target_obj_type, $a_target_id,
		$a_trigger_obj_type, $a_trigger_id, $a_operator, $a_value = "")
	{
		[...]
		return $id;
	}

	/**
	* delete condition
	*/
	function _deleteCondition($a_id)
	{
		[...]
	}

	/**
	* get all conditions of trigger object
	*/
	function _getConditionsOfTrigger($a_trigger_obj_type, $a_trigger_id)
	{
		[...]
		return $conditions;
	}

	/**
	* get all conditions of target object
	*/
	function _getConditionsOfTarget($a_target_obj_type, $a_target_id)
	{
		[...]
		return $conditions;
	}

	/**
	* checks wether a single condition is fulfilled
	* every trigger object type must implement a static method
	* _checkCondition($a_operator, $a_value)
	*/
	function _checkCondition($a_id)
	{
		[...]
		switch ($a_trigger_type)
		{
			"tst":
				return ilObjTest::_checkCondition($a_operator, $a_value);
				break;

			"qst":
				return ilObjCourse::_checkCondition($a_operator, $a_value);

			"crs":
				return ilObjCourse::_checkCondition($a_operator, $a_value);

			[...]
		}

	}

	/**
	* checks wether all conditions of a target object are fulfilled
	*/
	function _checkAllConditionsOfTarget($a_target_obj_type, $a_target_id)
	{
		[...]
		return true/false;
	}
}

?>
