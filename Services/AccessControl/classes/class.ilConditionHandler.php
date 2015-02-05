<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	const OPERATOR_PASSED = 'passed';
	const OPERATOR_FINISHED = 'finished';
	const OPERATOR_NOT_FINISHED = 'not_finished';
	const OPERATOR_NOT_MEMBER = 'not_member';
	const OPERATOR_FAILED = 'failed';
	const OPERATOR_LP = 'learning_progress';
	
	const UNIQUE_CONDITIONS = 1;
	const SHARED_CONDITIONS = 0;
	
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
	

	private $obligatory = true;
	private $hidden_status = FALSE;

	var $conditions;
	static $cond_for_target_cache = array();
	static $cond_target_rows = array();


	/**
	* constructor
	* @access	public
	*/
	public function __construct()
	{
		global $ilDB,$lng;

		$this->db =& $ilDB;
		$this->lng =& $lng;
		$this->validation = true;
	}

	/**
	 * is reference handling optional
	 *
	 * @access public
	 * @static
	 *
	 * @param string target type ILIAS obj type
	 */
	public static function _isReferenceHandlingOptional($a_type)
	{
		switch($a_type)
		{
			case 'st':
				return true;
			
			default:
				return false;
		}
	}
	
	/**
	 * Lookup hidden status
	 * @global type $ilDB
	 * @param type $a_target_ref_id
	 */
	public static function lookupHiddenStatusByTarget($a_target_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT hidden_status FROM conditions '.
				'WHERE target_ref_id = '.$ilDB->quote($a_target_ref_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->hidden_status;
		}
		return FALSE;
	}
	
	/**
	 * In the moment it is not allowed to create preconditions on objects
	 * that are located outside of a course.
	 * Therefore, after moving an object: check for parent type 'crs'. if that fails delete preconditions 
	 *
	 * @access public
	 * @static
	 *
	 * @param int reference id of moved object
	 */
	public static function _adjustMovedObjectConditions($a_ref_id)
	{
		global $tree;
		
		if($tree->checkForParentType($a_ref_id,'crs'))
		{
			// Nothing to do
			return true;
		}
		
		// Need another implementation that has better performance
		$childs = $tree->getSubTree($tree->getNodeData($a_ref_id),false);
		$conditions = self::_getDistinctTargetRefIds();
		
		foreach(array_intersect($conditions,$childs) as $target_ref)
		{
			if(!$tree->checkForParentType($target_ref,'crs'))
			{
				self::_deleteTargetConditionsByRefId($target_ref);
			}
		}
		return true;
	}
	
	/**
	 * Get all target ref ids
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getDistinctTargetRefIds()
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT target_ref_id ref FROM conditions ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ref_ids[] = $row->ref;
		}
		return $ref_ids ? $ref_ids : array();
	}
	
	/**
	 * Delete conditions by target ref id
	 * Note: only conditions on the target type are deleted
	 * Conditions on e.g chapters are not handled.
	 *
	 * @access public
	 * @static
	 *
	 * @param int ref id of target
	 */
	public static function _deleteTargetConditionsByRefId($a_target_ref_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM conditions ".
			"WHERE target_ref_id = ".$ilDB->quote($a_target_ref_id,'integer')." ".
			"AND target_type != 'st' ";
		$res = $ilDB->manipulate($query);
		return true;
	}

	/**
	 * set reference handling type
	 *
	 * @param int 
	 * @access public
	 * 
	 */
	public function setReferenceHandlingType($a_type)
	{
		return $this->condition_reference_type = $a_type; 	 	
	}
	
	/**
	 * get reference handling type
	 *
	 * @access public
	 * 
	 */
	public function getReferenceHandlingType()
	{
	 	return (int) $this->condition_reference_type;
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
	 * Set obligatory status
	 * @param bool $a_obl 
	 */
	public function setObligatory($a_obl)
	{
		$this->obligatory = $a_obl;
	}

	/**
	 * Get obligatory status
	 * @return obligatory status
	 */
	public function getObligatory()
	{
		return (bool) $this->obligatory;
	}
	
	public function setHiddenStatus($a_status)
	{
		$this->hidden_status = $a_status;
	}
	
	public function getHiddenStatus()
	{
		return $this->hidden_status;
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
		global $objDefinition;
		
		$trigger_types =  array('crs','exc','tst','sahs', 'svy', 'lm');

		foreach($objDefinition->getPlugins() as $p_type => $p_info)
		{
			if(@include_once $p_info['location'].'/class.ilObj'.$p_info['class_name'].'Access.php')
			{
				include_once './Services/AccessControl/interfaces/interface.ilConditionHandling.php';
				$name = 'ilObj'.$p_info['class_name'].'Access';
				$refection = new ReflectionClass($name);
				if($refection->implementsInterface('ilConditionHandling'))
				{
					$trigger_types[] = $p_type;
				}
			}
		}
		
		
		$active_triggers = array();
		foreach($trigger_types as $type)
		{
			if(count($this->getOperatorsByTargetType($type)))
			{
				$active_triggers[] = $type;
			}
		}
		
		
		
		
		return $active_triggers;
	}


	/**
	 * Get operators by target type
	 * @param string $a_type
	 * @return type
	 */
	public function getOperatorsByTargetType($a_type)
	{
		global $objDefinition;
		
		switch($a_type)
		{
			case 'crsg':
				return array('not_member');
		}
		
		$class = $objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);
		$full_class = "ilObj".$class."Access";
		include_once($location."/class.".$full_class.".php");
		
		include_once './Services/AccessControl/interfaces/interface.ilConditionHandling.php';
		
		$reflection = new ReflectionClass($full_class);
		if(!$reflection->implementsInterface('ilConditionHandling'))
		{
			return array();
		}
		
		
		$operators = call_user_func(
				array($full_class, 'getConditionOperators'),
				$a_type
		);
		
		// Add operator lp 
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress())
		{
			// only if object type has lp
			include_once("Services/Object/classes/class.ilObjectLP.php");
			if(ilObjectLP::isSupportedObjectType($a_type))
			{			
				array_unshift($operators,self::OPERATOR_LP);
			}
		}
		return $operators;
	}

	/**
	* store new condition in database
	* NOT STATIC
	* @access	public
	*/
	function storeCondition()
	{
		global $ilDB;
		
		// first insert, then validate: it's easier to check for circles if the new condition is in the db table
		$next_id = $ilDB->nextId('conditions');
		$query = 'INSERT INTO conditions (condition_id,target_ref_id,target_obj_id,target_type,'.
			'trigger_ref_id,trigger_obj_id,trigger_type,operator,value,ref_handling,obligatory,hidden_status) '.
			'VALUES ('.
			$ilDB->quote($next_id,'integer').','.
			$ilDB->quote($this->getTargetRefId(),'integer').",".
			$ilDB->quote($this->getTargetObjId(),'integer').",".
			$ilDB->quote($this->getTargetType(),'text').",".
			$ilDB->quote($this->getTriggerRefId(),'integer').",".
			$ilDB->quote($this->getTriggerObjId(),'integer').",".
			$ilDB->quote($this->getTriggerType(),'text').",".
			$ilDB->quote($this->getOperator(),'text').",".
			$ilDB->quote($this->getValue(),'text').", ".
			$ilDB->quote($this->getReferenceHandlingType(),'integer').', '.
			$ilDB->quote($this->getObligatory(),'integer').', '.
			$ilDB->quote($this->getHiddenStatus(),'integer').' '.
			')';

		$res = $ilDB->manipulate($query);

		if ($this->validation && !$this->validate())
		{
			$this->deleteCondition($next_id);
			return false;
		}
		return true;
	}

	function checkExists()
	{
		global $ilDB;
		
		$query = "SELECT * FROM conditions ".
			"WHERE target_ref_id = ".$ilDB->quote($this->getTargetRefId(),'integer')." ".
			"AND target_obj_id = ".$ilDB->quote($this->getTargetObjId(),'integer')." ".
			"AND trigger_ref_id = ".$ilDB->quote($this->getTriggerRefId(),'integer')." ".
			"AND trigger_obj_id = ".$ilDB->quote($this->getTriggerObjId(),'integer')." ".
			"AND operator = ".$ilDB->quote($this->getOperator(),'text');
		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}
	/**
	* update condition
	*/
	function updateCondition($a_id)
	{
		global $ilDB;
		
		$query = "UPDATE conditions SET ".
			"target_ref_id = ".$ilDB->quote($this->getTargetRefId(),'integer').", ".
			"operator = ".$ilDB->quote($this->getOperator(),'text').", ".
			"value = ".$ilDB->quote($this->getValue(),'text').", ".
			"ref_handling = ".$this->db->quote($this->getReferenceHandlingType(),'integer').", ".
			'obligatory = '.$this->db->quote($this->getObligatory(),'integer').' '.
			"WHERE condition_id = ".$ilDB->quote($a_id,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}
	
	/**
	 * Update hidden status
	 * @global type $ilDB
	 * @param type $a_target_ref_id
	 * @param type $a_status
	 * @return boolean
	 */
	public function updateHiddenStatus($a_status)
	{
		global $ilDB;
		
		$query = 'UPDATE conditions SET '.
				'hidden_status = '.$ilDB->quote($a_status,'integer').' '.
				'WHERE target_ref_id = '.$ilDB->quote($this->getTargetRefId(),'integer');
		$ilDB->manipulate($query);
		return TRUE;
	}
	
	/**
	 * Toggle condition obligatory status
	 * 
	 * @param int $a_id
	 * @param bool $a_status
	 */
	static function updateObligatory($a_id, $a_status)
	{
		global $ilDB;
		
		$query = "UPDATE conditions SET ".
			'obligatory = '.$ilDB->quote($a_status,'integer').' '.
			"WHERE condition_id = ".$ilDB->quote($a_id,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}

	/**
	* delete all trigger and target entries
	* This method is called from ilObject::delete() if an object os removed from trash
	*/
	function delete($a_ref_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM conditions WHERE ".
			"target_ref_id = ".$ilDB->quote($a_ref_id,'integer')." ".
			"OR trigger_ref_id = ".$ilDB->quote($a_ref_id,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}
	/**
	* delete all trigger and target entries
	* This method is called from ilObject::delete() if an object is removed from trash
	*/
	function deleteByObjId($a_obj_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM conditions WHERE ".
			"target_obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"OR trigger_obj_id = ".$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}

	/**
	* delete condition
	*/
	function deleteCondition($a_id)
	{
		global $ilDB;

		$query = "DELETE FROM conditions ".
			"WHERE condition_id = ".$ilDB->quote($a_id,'integer');
		$res = $ilDB->manipulate($query);

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
			"WHERE trigger_obj_id = ".$ilDB->quote($a_trigger_id,'integer')." ".
			" AND trigger_type = ".$ilDB->quote($a_trigger_obj_type,'text');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_array['id']			= $row->condition_id;
			$tmp_array['target_ref_id'] = $row->target_ref_id;
			$tmp_array['target_obj_id'] = $row->target_obj_id;
			$tmp_array['target_type']	= $row->target_type;
			$tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
			$tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
			$tmp_array['trigger_type']	= $row->trigger_type;
			$tmp_array['operator']		= $row->operator;
			$tmp_array['value']			= $row->value;
			$tmp_array['ref_handling']  = $row->ref_handling;
			$tmp_array['obligatory']	= $row->obligatory;
			$tmp_array['hidden_status'] = $row->hidden_status;

			$conditions[] = $tmp_array;
			unset($tmp_array);
		}

		return $conditions ? $conditions : array();
	}

	/**
	* get all conditions of target object
	* @param    $a_target_ref_id    target reference id
	* @param	$a_target_obj_id	target object id
	* @param	$a_target_type		target object type (must be provided only
	*								if object is not derived from ilObject
	*								and therefore stored in object_data; this
	*								is e.g. the case for chapters (type = "st"))
	* @static
	*/
	public static function _getConditionsOfTarget($a_target_ref_id,$a_target_obj_id, $a_target_type = "")
	{
		global $ilDB, $ilBench;

		// get type if no type given
		if ($a_target_type == "")
		{
			$a_target_type = ilObject::_lookupType($a_target_obj_id);
		}

		// check conditions for target cache
		if (isset(self::$cond_for_target_cache[$a_target_ref_id.":".$a_target_obj_id.":".
			$a_target_type]))
		{
			return self::$cond_for_target_cache[$a_target_ref_id.":".$a_target_obj_id.":".
				$a_target_type];
		}

		// check rows cache
		if (isset(self::$cond_target_rows[$a_target_type.":".$a_target_obj_id]))
		{
			$rows = self::$cond_target_rows[$a_target_type.":".$a_target_obj_id];
		}
		else
		{
			// query data from db
			$query = "SELECT * FROM conditions ".
				"WHERE target_obj_id = ".$ilDB->quote($a_target_obj_id,'integer')." ".
				" AND target_type = ".$ilDB->quote($a_target_type,'text');

			$res = $ilDB->query($query);
			$rows = array();
			while ($row = $ilDB->fetchAssoc($res))
			{
				$rows[] = $row;
			}
		}

		reset($rows);
		$conditions = array();
		foreach ($rows as $row)
		{
			if ($row["ref_handling"] == self::UNIQUE_CONDITIONS)
			{
				if ($row["target_ref_id"] != $a_target_ref_id)
				{
					continue;
				}
			}
			
			$row["id"] = $row["condition_id"];
			$conditions[] = $row;
		}

		// write conditions for target cache
		self::$cond_for_target_cache[$a_target_ref_id.":".$a_target_obj_id.":".
			$a_target_type] = $conditions;

		return $conditions;
	}

	/**
	 * Preload conditions for target records
	 *
	 * @param
	 * @return
	 */
	function preloadConditionsForTargetRecords($a_type, $a_obj_ids)
	{
		global $ilDB;

		if (is_array($a_obj_ids) && count($a_obj_ids) > 0)
		{
			$res = $ilDB->query("SELECT * FROM conditions ".
				"WHERE ".$ilDB->in("target_obj_id", $a_obj_ids, false, "integer").
				" AND target_type = ".$ilDB->quote($a_type,'text'));
			$rows = array();
			while ($row = $ilDB->fetchAssoc($res))
			{
				self::$cond_target_rows[$a_type.":".$row["target_obj_id"]][]
					= $row;
			}
			// init obj ids without any record
			foreach ($a_obj_ids as $obj_id)
			{
				if (!is_array(self::$cond_target_rows[$a_type.":".$obj_id]))
				{
					self::$cond_target_rows[$a_type.":".$obj_id] = array();
				}
			}
		}
	}

	function _getCondition($a_id)
	{
		global $ilDB;

		$query = "SELECT * FROM conditions ".
			"WHERE condition_id = ".$ilDB->quote($a_id,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_array['id']			= $row->condition_id;
			$tmp_array['target_ref_id'] = $row->target_ref_id;
			$tmp_array['target_obj_id'] = $row->target_obj_id;
			$tmp_array['target_type']	= $row->target_type;
			$tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
			$tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
			$tmp_array['trigger_type']	= $row->trigger_type;
			$tmp_array['operator']		= $row->operator;
			$tmp_array['value']			= $row->value;
			$tmp_array['ref_handling']  = $row->ref_handling;
			$tmp_array['obligatory']	= $row->obligatory;
			$tmp_array['hidden_status'] = $row->hidden_status;

			return $tmp_array;
		}
		return false;
	}



	/**
	* checks wether a single condition is fulfilled
	* every trigger object type must implement a static method
	* _checkCondition($a_operator, $a_value)
	*/
	function _checkCondition($a_id,$a_usr_id = 0)
	{
		global $ilUser, $objDefinition;
		
		$a_usr_id = $a_usr_id ? $a_usr_id : $ilUser->getId();
		
		$condition = ilConditionHandler::_getCondition($a_id);
		
		// check lp 
		if($condition['operator'] == self::OPERATOR_LP)
		{
			include_once './Services/Tracking/classes/class.ilLPStatus.php';
			return ilLPStatus::_hasUserCompleted($condition['trigger_obj_id'], $a_usr_id);
		}
		
		switch($condition['trigger_type'])
		{
			case 'crsg':
				include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';
				return ilObjCourseGrouping::_checkCondition($condition['trigger_obj_id'],$condition['operator'],$condition['value'],$a_usr_id);
		}
		
		$class = $objDefinition->getClassName($condition['trigger_type']);
		$location = $objDefinition->getLocation($condition['trigger_type']);
		$full_class = "ilObj".$class."Access";
		include_once($location."/class.".$full_class.".php");

		$fullfilled = call_user_func(
				array($full_class, 'checkCondition'),
				$condition['trigger_obj_id'],
				$condition['operator'],
				$condition['value'],
				$a_usr_id
		);
		return $fullfilled;
	}

	/**
	 * Get optional conditions
	 * @param int $a_target_ref_id
	 * @param int $a_target_obj_id
	 */
	public static function getOptionalConditionsOfTarget($a_target_ref_id,$a_target_obj_id,$a_obj_type = '')
	{
		$conditions = self::_getConditionsOfTarget($a_target_ref_id,$a_target_obj_id);
		
		$opt = array();
		foreach($conditions as $con)
		{
			if($con['obligatory'])
			{
				continue;
			}
			
			$opt[] = $con;
		}
		return $opt;
	}

	/**
	 * calculate number of obligatory items
	 * @param int $a_target_ref_id
	 * @param int $a_target_obj_id
	 * @return int
	 */
	public static function calculateRequiredTriggers($a_target_ref_id,$a_target_obj_id,$a_target_obj_type = '', $a_force_update = false)
	{
		global $ilDB;

		// Get all conditions
		$all = self::_getConditionsOfTarget($a_target_ref_id,$a_target_obj_id,$a_target_obj_type);
		$opt = self::getOptionalConditionsOfTarget($a_target_ref_id, $a_target_obj_id,$a_target_obj_type);

		$set_obl = 0;
		if(isset($all[0]))
		{
			$set_obl = $all[0]['num_obligatory'];
		}
		
		// existing value is valid
		if($set_obl > 0 and
			$set_obl < count($all) and
			$set_obl > (count($all) - count($opt)  + 1))
		{
			return $set_obl;
		}
		
		if(count($opt))
		{
			$result = count($all) - count($opt) + 1;
		}
		else
		{
			$result = count($all);
		}
		if($a_force_update)
		{
			self::saveNumberOfRequiredTriggers($a_target_ref_id,$a_target_obj_id,$result);
		}
		return $result;
	}

	/**
	 * Save number of obigatory triggers
	 * @param int $a_target_ref_id
	 * @param int $a_target_obj_id
	 */
	public static function saveNumberOfRequiredTriggers($a_target_ref_id,$a_target_obj_id,$a_num)
	{
		global $ilDB;

		$query = 'UPDATE conditions '.
			'SET num_obligatory = '.$ilDB->quote($a_num,'integer').' '.
			'WHERE target_ref_id = '.$ilDB->quote($a_target_ref_id,'integer').' '.
			'AND target_obj_id = '.$ilDB->quote($a_target_obj_id,'integer');
		$ilDB->manipulate($query);
		return;
	}

	/**
	* checks wether all conditions of a target object are fulfilled
	*/
	function _checkAllConditionsOfTarget($a_target_ref_id,$a_target_id, $a_target_type = "",$a_usr_id = 0)
	{
		global $ilBench,$ilUser,$tree;
		
		$a_usr_id = $a_usr_id ? $a_usr_id : $ilUser->getId();

		$conditions = ilConditionHandler::_getConditionsOfTarget($a_target_ref_id,$a_target_id, $a_target_type);

		if(!count($conditions))
		{
			return true;
		}

		// @todo check this
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		if(ilMemberViewSettings::getInstance()->isActive())
		{
			return true;
		}

		// First check obligatory conditions
		$optional = self::getOptionalConditionsOfTarget($a_target_ref_id, $a_target_id, $a_target_type);
		$num_required = self::calculateRequiredTriggers($a_target_ref_id, $a_target_id, $a_target_type);
		$passed = 0;
		foreach($conditions as $condition)
		{
			if($tree->isDeleted($condition['trigger_ref_id']))
			{
				continue;
			}
			$check = ilConditionHandler::_checkCondition($condition['id'],$a_usr_id);

			if($check)
			{
				++$passed;
				if($passed >= $num_required)
				{
					return true;
				}
			}
			else
			{
				if(!count($optional))
				{
					return false;
				}
			}
		}
		// not all optional conditions passed
		return false;
	}

	// PRIVATE
	function validate()
	{
		global $ilDB;
		
		// check if obj_id is already assigned
		$trigger_obj =& ilObjectFactory::getInstanceByRefId($this->getTriggerRefId());
		$target_obj =& ilObjectFactory::getInstanceByRefId($this->getTargetRefId());


		$query = "SELECT * FROM conditions WHERE ".
			"trigger_ref_id = ".$ilDB->quote($trigger_obj->getId(),'integer')." ".
			"AND target_ref_id = ".$ilDB->quote($target_obj->getId(),'integer');

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
		if($this->checkCircle($this->getTargetRefId(),$target_obj->getId()))
		{
			$this->setErrorMessage($this->lng->txt('condition_circle_created'));
			
			unset($trigger_obj);
			unset($target_obj);
			return false;
		}			
		return true;
	}

	function checkCircle($a_ref_id,$a_obj_id)
	{
		foreach(ilConditionHandler::_getConditionsOfTarget($a_ref_id,$a_obj_id) as $condition)
		{
			if($condition['trigger_obj_id'] == $this->target_obj_id and $condition['operator'] == $this->getOperator())
			{
				$this->circle = true;
				break;
			}
			else
			{
				$this->checkCircle($condition['trigger_ref_id'],$condition['trigger_obj_id']);
			}
		}
		return $this->circle;
	}
}

?>
