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
* Stefan Meyer 10-08-2004
* In addition we store the ref_id of the trigger object to allow the target object to link to the triggered object.
* But it's not possible to assign two or more linked (same obj_id) triggered objects to a target object
*
* Examples:
*
* Learning module 5 may only be accessed, if test 6 has been passed:
*   trigger_obj_type		"tst"
*   trigger_id				6 (object id)
*   trigger_ref_id			117
*   operator				"passed"
*   value
*   target_obj_type			"lm"
*   target_id				5 (reference id)
*
* Survey question 10 should only be presented, if survey question 8
* is answered with a value greater than 4.
*   trigger_obj_type		"qst"
*   trigger_id				8 (question (instance) object id)
*   trigger_ref_id			117
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
	var $db;
	var $lng;
	

	var $error_message;

	var $target_obj_id;
	var $target_ref_id;
	var $target_type;
	var $trigger_obj_id;
	var $trigger_ref_id;
	var $trigger_type;
	var $operator;
	var $value;
	var $validation;

	var $conditions;


	/**
	* constructor
	* @access	public
	*/
	function ilConditionHandler()
	{
		global $ilDB,$lng;

		$this->db =& $ilDB;
		$this->lng =& $lng;
		$this->validation = true;
	}

	// SET GET
	function setErrorMessage($a_msg)
	{
		$this->error_message = $a_msg;
	}
	function getErrorMessage()
	{
		return $this->error_message;
	}

	/**
	* set target ref id
	*/
	function setTargetRefId($a_target_ref_id)
	{
		return $this->target_ref_id = $a_target_ref_id;
	}
	
	/**
	* get target ref id
	*/
	function getTargetRefId()
	{
		return $this->target_ref_id;
	}
	
	/**
	* set target object id
	*/
	function setTargetObjId($a_target_obj_id)
	{
		return $this->target_obj_id = $a_target_obj_id;
	}
	
	/**
	* get target obj id
	*/
	function getTargetObjId()
	{
		return $this->target_obj_id;
	}

	/**
	* set target object type
	*/
	function setTargetType($a_target_type)
	{
		return $this->target_type = $a_target_type;
	}
	
	/**
	* get target obj type
	*/
	function getTargetType()
	{
		return $this->target_type;
	}
	
	/**
	* set trigger ref id
	*/
	function setTriggerRefId($a_trigger_ref_id)
	{
		return $this->trigger_ref_id = $a_trigger_ref_id;
	}
	
	/**
	* get target ref id
	*/
	function getTriggerRefId()
	{
		return $this->trigger_ref_id;
	}

	/**
	* set trigger object id
	*/
	function setTriggerObjId($a_trigger_obj_id)
	{
		return $this->trigger_obj_id = $a_trigger_obj_id;
	}
	
	/**
	* get trigger obj id
	*/
	function getTriggerObjId()
	{
		return $this->trigger_obj_id;
	}

	/**
	* set trigger object type
	*/
	function setTriggerType($a_trigger_type)
	{
		return $this->trigger_type = $a_trigger_type;
	}
	
	/**
	* get trigger obj type
	*/
	function getTriggerType()
	{
		return $this->trigger_type;
	}
	
	/**
	* set operator
	*/
	function setOperator($a_operator)
	{
		return $this->operator = $a_operator;
	}
	
	/**
	* get operator
	*/
	function getOperator()
	{
		return $this->operator;
	}
	
	/**
	* set value
	*/
	function setValue($a_value)
	{
		return $this->value = $a_value;
	}
	
	/**
	* get value
	*/
	function getValue()
	{
		return $this->value;
	}
	
	/**
	* enable automated validation
	*/
	function enableAutomaticValidation($a_validate = true)
	{
		$this->validation = $a_validate;
	}

	/**
	* get all possible trigger types
	* NOT STATIC
	* @access	public
	*/
	function getTriggerTypes()
	{
		return array('crs','exc','tst','sahs');
	}


	function getOperatorsByTargetType($a_type)
	{
		switch($a_type)
		{
			case 'crs':
			case 'exc':
				return array('passed');

			case 'tst':
				return array('passed','finished','not_finished');

			case 'crsg':
				return array('not_member');

			case 'sahs':
				return array('finished');

			default:
				return array();
		}
	}

	/**
	* store new condition in database
	* NOT STATIC
	* @access	public
	*/
	function storeCondition()
	{
		// first insert, then validate: it's easier to check for circles if the new condition is in the db table
		$query = 'INSERT INTO conditions '.
			"VALUES('0','".$this->getTargetRefId()."','".$this->getTargetObjId()."','".$this->getTargetType()."','".
			$this->getTriggerRefId()."','".$this->getTriggerObjId()."','".$this->getTriggerType()."','".
			$this->getOperator()."','".$this->getValue()."')";

		$res = $this->db->query($query);

		$query = "SELECT LAST_INSERT_ID() AS last FROM conditions";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$last_id = $row->last;
		}
		
		if ($this->validation && !$this->validate())
		{
			$this->deleteCondition($last_id);
			return false;
		}
		return true;
	}

	function checkExists()
	{
		$query = "SELECT * FROM conditions ".
			"WHERE target_ref_id = '".$this->getTargetRefId()."' ".
			"AND target_obj_id = '".$this->getTargetObjId()."' ".
			"AND trigger_ref_id = '".$this->getTriggerRefId()."' ".
			"AND trigger_obj_id = '".$this->getTriggerObjId()."' ".
			"AND operator = '".$this->getOperator()."'";

		$res = $this->db->query($query);

		return $res->numRows() ? true : false;
	}
	/**
	* update condition
	*/
	function updateCondition($a_id)
	{
		$query = "UPDATE conditions SET ".
			"operator = '".$this->getOperator()."', ".
			"value = '".$this->getValue()."' ".
			"WHERE id = '".$a_id."'";

		$res = $this->db->query($query);

		return true;
	}


	/**
	* delete all trigger and target entries
	* This method is called from ilObject::delete() if an object os removed from trash
	*/
	function delete($a_ref_id)
	{
		$query = "DELETE FROM conditions WHERE ".
			"target_ref_id = '".$a_ref_id."' ".
			"OR trigger_ref_id = '".$a_ref_id."'";

		$res = $this->db->query($query);

		return true;
	}
	/**
	* delete all trigger and target entries
	* This method is called from ilObject::delete() if an object is removed from trash
	*/
	function deleteByObjId($a_obj_id)
	{
		$query = "DELETE FROM conditions WHERE ".
			"target_obj_id = '".$a_obj_id."' ".
			"OR trigger_obj_id = '".$a_obj_id."'";

		$res = $this->db->query($query);

		return true;
	}

	/**
	* delete condition
	*/
	function deleteCondition($a_id)
	{
		global $ilDB;

		$query = "DELETE FROM conditions ".
			"WHERE id = '".$a_id."'";

		$res = $ilDB->query($query);

		return true;
	}

	/**
	* get all conditions of trigger object
	* @static
	*/
	function _getConditionsOfTrigger($a_trigger_obj_type, $a_trigger_id)
	{
		global $ilDB;

		$query = "SELECT * FROM conditions ".
			"WHERE trigger_obj_id = '".$a_trigger_id."'".
			" AND trigger_type = '".$a_trigger_obj_type."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_array['id']			= $row->id;
			$tmp_array['target_ref_id'] = $row->target_ref_id;
			$tmp_array['target_obj_id'] = $row->target_obj_id;
			$tmp_array['target_type']	= $row->target_type;
			$tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
			$tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
			$tmp_array['trigger_type']	= $row->trigger_type;
			$tmp_array['operator']		= $row->operator;
			$tmp_array['value']			= $row->value;

			$conditions[] = $tmp_array;
			unset($tmp_array);
		}

		return $conditions ? $conditions : array();
	}

	/**
	* get all conditions of target object
	*
	* @param	$a_target_obj_id	target object id
	* @param	$a_target_type		target object type (must be provided only
	*								if object is not derived from ilObject
	*								and therefore stored in object_data; this
	*								is e.g. the case for chapters (type = "st"))
	* @static
	*/
	function _getConditionsOfTarget($a_target_obj_id, $a_target_type = "")
	{
		global $ilDB, $ilBench;

		$ilBench->start("ilConditionHandler", "getConditionsOfTarget");

		if ($a_target_type == "")
		{
			$a_target_type = ilObject::_lookupType($a_target_obj_id);
		}

		$query = "SELECT * FROM conditions ".
			"WHERE target_obj_id = '".$a_target_obj_id."'".
			" AND target_type = '".$a_target_type."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_array['id']			= $row->id;
			$tmp_array['target_ref_id'] = $row->target_ref_id;
			$tmp_array['target_obj_id'] = $row->target_obj_id;
			$tmp_array['target_type']	= $row->target_type;
			$tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
			$tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
			$tmp_array['trigger_type']	= $row->trigger_type;
			$tmp_array['operator']		= $row->operator;
			$tmp_array['value']			= $row->value;

			$conditions[] = $tmp_array;
			unset($tmp_array);
		}

		$ilBench->stop("ilConditionHandler", "getConditionsOfTarget");

		return $conditions ? $conditions : array();
	}

	function _getCondition($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM conditions ".
			"WHERE id = '".$a_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_array['id']			= $row->id;
			$tmp_array['target_ref_id'] = $row->target_ref_id;
			$tmp_array['target_obj_id'] = $row->target_obj_id;
			$tmp_array['target_type']	= $row->target_type;
			$tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
			$tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
			$tmp_array['trigger_type']	= $row->trigger_type;
			$tmp_array['operator']		= $row->operator;
			$tmp_array['value']			= $row->value;

			return $tmp_array;
		}
		return false;
	}



	/**
	* checks wether a single condition is fulfilled
	* every trigger object type must implement a static method
	* _checkCondition($a_operator, $a_value)
	*/
	function _checkCondition($a_id)
	{
		$condition = ilConditionHandler::_getCondition($a_id);


		switch($condition['trigger_type'])
		{
			case "tst":
				include_once './Modules/Test/classes/class.ilObjTestAccess.php';
				return ilObjTestAccess::_checkCondition($condition['trigger_obj_id'],$condition['operator'],$condition['value']);

			case "crs":
				include_once './course/classes/class.ilObjCourse.php';
				return ilObjCourse::_checkCondition($condition['trigger_obj_id'],$condition['operator'],$condition['value']);

			case 'exc':
				include_once './classes/class.ilObjExercise.php';
				return ilObjExercise::_checkCondition($condition['trigger_obj_id'],$condition['operator'],$condition['value']);

			case 'crsg':
				include_once './course/classes/class.ilObjCourseGrouping.php';
				return ilObjCourseGrouping::_checkCondition($condition['trigger_obj_id'],$condition['operator'],$condition['value']);

			case 'sahs':
				global $ilUser;

				include_once './Services/Tracking/classes/class.ilLPStatusSCORM.php';
				include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
				return in_array($ilUser->getId(),$completed = ilLPStatusSCORM::_getCompleted($condition['trigger_obj_id']));

			default:
				return false;

		}

	}

	/**
	* checks wether all conditions of a target object are fulfilled
	*/
	function _checkAllConditionsOfTarget($a_target_id, $a_target_type = "")
	{
		global $ilBench;

		foreach(ilConditionHandler::_getConditionsOfTarget($a_target_id, $a_target_type) as $condition)
		{
			$ilBench->start("ilConditionHandler", "checkCondition");
			$check = ilConditionHandler::_checkCondition($condition['id']);
			$ilBench->stop("ilConditionHandler", "checkCondition");

			if(!$check)
			{
				return false;
			}
		}
		return true;
	}

	// PRIVATE
	function validate()
	{
		// check if obj_id is already assigned
		$trigger_obj =& ilObjectFactory::getInstanceByRefId($this->getTriggerRefId());
		$target_obj =& ilObjectFactory::getInstanceByRefId($this->getTargetRefId());


		$query = "SELECT * FROM conditions WHERE ".
			"trigger_obj_id = '".$trigger_obj->getId()."' ".
			"AND target_obj_id = '".$target_obj->getId()."'";

		$res = $this->db->query($query);
		if($res->numRows() > 1)
		{
			$this->setErrorMessage($this->lng->txt('condition_already_assigned'));

			unset($trigger_obj);
			unset($target_obj);
			return false;
		}

		// check for circle
		$this->target_obj_id = $target_obj->getId();
		if($this->checkCircle($target_obj->getId()))
		{
			$this->setErrorMessage($this->lng->txt('condition_circle_created'));
			
			unset($trigger_obj);
			unset($target_obj);
			return false;
		}			
		return true;
	}

	function checkCircle($a_obj_id)
	{
		foreach(ilConditionHandler::_getConditionsOfTarget($a_obj_id) as $condition)
		{
			if($condition['trigger_obj_id'] == $this->target_obj_id and $condition['operator'] == $this->getOperator())
			{
				$this->circle = true;
				break;
			}
			else
			{
				$this->checkCircle($condition['trigger_obj_id']);
			}
		}
		return $this->circle;
	}
}

?>
