<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * INTERNAL CLASS: Please do not use in consumer code.
 *
 *
 *
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
*   trigger_obj_type		VARCHAR(10)	"crs" | "tst" | ...
*   trigger_ref_id			INT			obj id of trigger object	(only exception where this is 0 are currently (5.3) course groupings this might be refactored
*   trigger_obj_id			INT			obj id of trigger object
*   operator				varchar(10  "=", "<", ">", ">=", "<=", "passed", "contains", ...
*   value					VARCHAR(10) optional value
*   target_obj_type			VARCHAR(10)	"lm" | "frm" | "st", "lobj", ...
*   target_obj_id			object id of target object
*   target_ref_id			reference id of target object
 *
 * Special current targets (5.3)
 * - learning objectives: type: "lobj"; obj_id: objective id; ref_id: ref id of course
 * - lm chapters: type: "st"; obj_id: chapter id, ref_id: ref id of learning module
 *
 *
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
    const OPERATOR_ACCREDITED_OR_PASSED = 'accredited_or_passed';
    
    const UNIQUE_CONDITIONS = 1;
    const SHARED_CONDITIONS = 0;			// conditions are used for all tree references of the target object
    // this is currently only used for lm chapters and likely to be abandonded in the future
    
    public $db;
    public $lng;
    

    public $error_message;

    public $target_obj_id;
    public $target_ref_id;
    public $target_type;
    public $trigger_obj_id;
    public $trigger_ref_id;
    public $trigger_type;
    public $operator;
    public $value;
    public $validation;
    

    private $obligatory = true;
    private $hidden_status = false;

    public $conditions;
    public static $cond_for_target_cache = array();
    public static $cond_target_rows = array();


    /**
    * constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];

        $this->db = &$ilDB;
        $this->lng = &$lng;
        $this->validation = true;
    }

    public static function resetCache() : void
    {
        self::$cond_for_target_cache = [];
        self::$cond_target_rows = [];
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
        switch ($a_type) {
            case 'st':
                return true;
            
            default:
                return false;
        }
    }

    /**
     * Lookup hidden status (also take container control into account)
     * @param int $a_target_ref_id
     * @return bool
     */
    public static function lookupEffectiveHiddenStatusByTarget($a_target_ref_id)
    {
        global $DIC;

        $obj_definition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        // check if parent takes over control of condition
        $parent_ref_id = $tree->getParentId($a_target_ref_id);
        $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
        $parent_type = ilObject::_lookupType($parent_obj_id);

        $class = $obj_definition->getClassName($parent_type);
        $class_name = "il" . $class . "ConditionController";
        $location = $obj_definition->getLocation($parent_type);

        // if yes, get from parent
        if ($class != "" && is_file($location . "/class." . $class_name . ".php")) {
            /** @var ilConditionControllerInterface $controller */
            $controller = new $class_name();
            if ($controller->isContainerConditionController($parent_ref_id)) {
                $set = $controller->getConditionSetForRepositoryObject($a_target_ref_id);
                return $set->getHiddenStatus();
            }
        }

        return self::lookupPersistedHiddenStatusByTarget($a_target_ref_id);
    }

    /**
     * Lookup persistedhidden status
     * @param int $a_target_ref_id
     * @return bool
     */
    public static function lookupPersistedHiddenStatusByTarget($a_target_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT hidden_status FROM conditions ' .
                'WHERE target_ref_id = ' . $ilDB->quote($a_target_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->hidden_status;
        }
        return false;
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
        global $DIC;

        return true;

        $tree = $DIC['tree'];
        
        if ($tree->checkForParentType($a_ref_id, 'crs')) {
            // Nothing to do
            return true;
        }
        
        // Need another implementation that has better performance
        $childs = $tree->getSubTree($tree->getNodeData($a_ref_id), false);
        $conditions = self::_getDistinctTargetRefIds();
        
        foreach (array_intersect($conditions, $childs) as $target_ref) {
            if (!$tree->checkForParentType($target_ref, 'crs')) {
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
    protected static function _getDistinctTargetRefIds()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT target_ref_id ref FROM conditions ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
    protected static function _deleteTargetConditionsByRefId($a_target_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM conditions " .
            "WHERE target_ref_id = " . $ilDB->quote($a_target_ref_id, 'integer') . " " .
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
    public function setErrorMessage($a_msg)
    {
        $this->error_message = $a_msg;
    }
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
    * set target ref id
    */
    public function setTargetRefId($a_target_ref_id)
    {
        return $this->target_ref_id = $a_target_ref_id;
    }
    
    /**
    * get target ref id
    */
    public function getTargetRefId()
    {
        return $this->target_ref_id;
    }
    
    /**
    * set target object id
    */
    public function setTargetObjId($a_target_obj_id)
    {
        return $this->target_obj_id = $a_target_obj_id;
    }
    
    /**
    * get target obj id
    */
    public function getTargetObjId()
    {
        return $this->target_obj_id;
    }

    /**
    * set target object type
    */
    public function setTargetType($a_target_type)
    {
        return $this->target_type = $a_target_type;
    }
    
    /**
    * get target obj type
    */
    public function getTargetType()
    {
        return $this->target_type;
    }
    
    /**
    * set trigger ref id
    */
    public function setTriggerRefId($a_trigger_ref_id)
    {
        return $this->trigger_ref_id = $a_trigger_ref_id;
    }
    
    /**
    * get target ref id
    */
    public function getTriggerRefId()
    {
        return $this->trigger_ref_id;
    }

    /**
    * set trigger object id
    */
    public function setTriggerObjId($a_trigger_obj_id)
    {
        return $this->trigger_obj_id = $a_trigger_obj_id;
    }
    
    /**
    * get trigger obj id
    */
    public function getTriggerObjId()
    {
        return $this->trigger_obj_id;
    }

    /**
    * set trigger object type
    */
    public function setTriggerType($a_trigger_type)
    {
        return $this->trigger_type = $a_trigger_type;
    }
    
    /**
    * get trigger obj type
    */
    public function getTriggerType()
    {
        return $this->trigger_type;
    }
    
    /**
    * set operator
    */
    public function setOperator($a_operator)
    {
        return $this->operator = $a_operator;
    }
    
    /**
    * get operator
    */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
    * set value
    */
    public function setValue($a_value)
    {
        return $this->value = $a_value;
    }
    
    /**
    * get value
    */
    public function getValue()
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
    public function enableAutomaticValidation($a_validate = true)
    {
        $this->validation = $a_validate;
    }

    /**
    * get all possible trigger types
    * NOT STATIC
    * @access	public
    */
    public function getTriggerTypes()
    {
        global $DIC;

        /** @var ilObjectDefinition $objDefinition */
        $objDefinition = $DIC['objDefinition'];
        
        $trigger_types = array('crs','exc','tst','sahs', 'svy', 'lm', 'iass', 'prg', 'copa', 'lti', 'cmix');

        // Add operator lp trigger
        if (ilObjUserTracking::_enabledLearningProgress()) {
            // only if object type has lp
            foreach ($objDefinition->getAllRepositoryTypes() as $t) {
                if (ilObjectLP::isSupportedObjectType($t)) {
                    if (!in_array($t, $trigger_types)) {
                        $trigger_types[] = $t;
                    }
                }
            }
        }

        foreach ($objDefinition->getPlugins() as $p_type => $p_info) {
            if (@include_once $p_info['location'] . '/class.ilObj' . $p_info['class_name'] . 'Access.php') {
                include_once './Services/Conditions/interfaces/interface.ilConditionHandling.php';
                $name = 'ilObj' . $p_info['class_name'] . 'Access';
                $reflection = new ReflectionClass($name);
                if ($reflection->implementsInterface('ilConditionHandling')) {
                    $trigger_types[] = $p_type;
                }
            }
        }
        
        
        $active_triggers = array();
        foreach ($trigger_types as $type) {
            if (count($this->getOperatorsByTriggerType($type))) {
                $active_triggers[] = $type;
            }
        }
        
        
        
        
        return $active_triggers;
    }


    /**
     * Get operators by trigger type
     * @param string $a_type
     * @return string[]
     */
    public function getOperatorsByTriggerType($a_type)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        
        switch ($a_type) {
            case 'crsg':
                return array('not_member');
        }
        
        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "Access";
        include_once($location . "/class." . $full_class . ".php");
        
        include_once './Services/Conditions/interfaces/interface.ilConditionHandling.php';
        
        $reflection = new ReflectionClass($full_class);
        if ($reflection->implementsInterface('ilConditionHandling')) {
            $operators = call_user_func(
                array($full_class, 'getConditionOperators'),
                $a_type
            );
        } else {
            $operators = [];
        }
        
        // Add operator lp
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (ilObjUserTracking::_enabledLearningProgress()) {
            // only if object type has lp
            include_once("Services/Object/classes/class.ilObjectLP.php");
            if (ilObjectLP::isSupportedObjectType($a_type)) {
                array_unshift($operators, self::OPERATOR_LP);
            }
        }
        return $operators;
    }

    /**
    * store new condition in database
    */
    public function storeCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // first insert, then validate: it's easier to check for circles if the new condition is in the db table
        $next_id = $ilDB->nextId('conditions');
        $query = 'INSERT INTO conditions (condition_id,target_ref_id,target_obj_id,target_type,' .
            'trigger_ref_id,trigger_obj_id,trigger_type,operator,value,ref_handling,obligatory,hidden_status) ' .
            'VALUES (' .
            $ilDB->quote($next_id, 'integer') . ',' .
            $ilDB->quote($this->getTargetRefId(), 'integer') . "," .
            $ilDB->quote($this->getTargetObjId(), 'integer') . "," .
            $ilDB->quote($this->getTargetType(), 'text') . "," .
            $ilDB->quote($this->getTriggerRefId(), 'integer') . "," .
            $ilDB->quote($this->getTriggerObjId(), 'integer') . "," .
            $ilDB->quote($this->getTriggerType(), 'text') . "," .
            $ilDB->quote($this->getOperator(), 'text') . "," .
            $ilDB->quote($this->getValue(), 'text') . ", " .
            $ilDB->quote($this->getReferenceHandlingType(), 'integer') . ', ' .
            $ilDB->quote($this->getObligatory(), 'integer') . ', ' .
            $ilDB->quote($this->getHiddenStatus(), 'integer') . ' ' .
            ')';

        $res = $ilDB->manipulate($query);

        if ($this->validation && !$this->validate()) {
            $this->deleteCondition($next_id);
            return false;
        }
        return true;
    }

    public function checkExists()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM conditions " .
            "WHERE target_ref_id = " . $ilDB->quote($this->getTargetRefId(), 'integer') . " " .
            "AND target_obj_id = " . $ilDB->quote($this->getTargetObjId(), 'integer') . " " .
            "AND trigger_ref_id = " . $ilDB->quote($this->getTriggerRefId(), 'integer') . " " .
            "AND trigger_obj_id = " . $ilDB->quote($this->getTriggerObjId(), 'integer') . " " .
            "AND operator = " . $ilDB->quote($this->getOperator(), 'text');
        $res = $ilDB->query($query);

        return $res->numRows() ? true : false;
    }
    /**
    * update condition
    */
    public function updateCondition($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE conditions SET " .
            "target_ref_id = " . $ilDB->quote($this->getTargetRefId(), 'integer') . ", " .
            "operator = " . $ilDB->quote($this->getOperator(), 'text') . ", " .
            "value = " . $ilDB->quote($this->getValue(), 'text') . ", " .
            "ref_handling = " . $this->db->quote($this->getReferenceHandlingType(), 'integer') . ", " .
            'obligatory = ' . $this->db->quote($this->getObligatory(), 'integer') . ' ' .
            "WHERE condition_id = " . $ilDB->quote($a_id, 'integer');
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
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'UPDATE conditions SET ' .
                'hidden_status = ' . $ilDB->quote($a_status, 'integer') . ' ' .
                'WHERE target_ref_id = ' . $ilDB->quote($this->getTargetRefId(), 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Toggle condition obligatory status
     *
     * @param int $a_id
     * @param bool $a_status
     */
    public static function updateObligatory($a_id, $a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "UPDATE conditions SET " .
            'obligatory = ' . $ilDB->quote($a_status, 'integer') . ' ' .
            "WHERE condition_id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
    * delete all trigger and target entries
    * This method is called from ilObject::delete() if an object os removed from trash
    */
    public function delete($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM conditions WHERE " .
            "target_ref_id = " . $ilDB->quote($a_ref_id, 'integer') . " " .
            "OR trigger_ref_id = " . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }
    /**
    * delete all trigger and target entries
    * This method is called from ilObject::delete() if an object is removed from trash
    */
    public function deleteByObjId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM conditions WHERE " .
            "target_obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "OR trigger_obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
    * delete condition
    */
    public function deleteCondition($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM conditions " .
            "WHERE condition_id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }

    /**
     * get all conditions of trigger object
     * @static
     * @param string $a_trigger_obj_type
     * @param int $a_trigger_id
     * @return int
     * @throws ilDatabaseException
     */
    public static function getNumberOfConditionsOfTrigger($a_trigger_obj_type, $a_trigger_id)
    {
        global $DIC;
        $db = $DIC->database();

        $query = 'select count(*) num from conditions ' .
            'where trigger_obj_id = ' . $db->quote($a_trigger_id, ilDBConstants::T_INTEGER) . ' ' .
            'and trigger_type = ' . $db->quote($a_trigger_obj_type, ilDBConstants::T_TEXT);
        $res = $db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return (int) $row->num;
    }

    /**
     * Get all persisted conditions of trigger object
     * Note: This only gets persisted conditions NOT (dynamic) conditions send by the parent container logic.
     */
    public static function _getPersistedConditionsOfTrigger($a_trigger_obj_type, $a_trigger_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM conditions " .
            "WHERE trigger_obj_id = " . $ilDB->quote($a_trigger_id, 'integer') . " " .
            " AND trigger_type = " . $ilDB->quote($a_trigger_obj_type, 'text');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmp_array['id'] = $row->condition_id;
            $tmp_array['target_ref_id'] = $row->target_ref_id;
            $tmp_array['target_obj_id'] = $row->target_obj_id;
            $tmp_array['target_type'] = $row->target_type;
            $tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
            $tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
            $tmp_array['trigger_type'] = $row->trigger_type;
            $tmp_array['operator'] = $row->operator;
            $tmp_array['value'] = $row->value;
            $tmp_array['ref_handling'] = $row->ref_handling;
            $tmp_array['obligatory'] = $row->obligatory;
            $tmp_array['hidden_status'] = $row->hidden_status;

            $conditions[] = $tmp_array;
            unset($tmp_array);
        }

        return $conditions ? $conditions : array();
    }

    /**
     * get all conditions of target object (also take container control into account)
     * @param int $a_target_ref_id
     * @param int $a_target_obj_id
     * @param string $a_target_type
     * @return array
     */
    public static function _getEffectiveConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_type = "")
    {
        global $DIC;

        $obj_definition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        // get type if no type given
        if ($a_target_type == "") {
            $a_target_type = ilObject::_lookupType($a_target_obj_id);
        }

        // check if parent takes over control of condition
        $parent_ref_id = $tree->getParentId($a_target_ref_id);
        $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
        $parent_type = ilObject::_lookupType($parent_obj_id);

        $class = $obj_definition->getClassName($parent_type);
        $class_name = "il" . $class . "ConditionController";
        $location = $obj_definition->getLocation($parent_type);

        // if yes, get from parent
        if ($class != "" && is_file($location . "/class." . $class_name . ".php")
            && $a_target_type == ilObject::_lookupType($a_target_ref_id, true)) {
            /** @var ilConditionControllerInterface $controller */
            $controller = new $class_name();
            if ($controller->isContainerConditionController($parent_ref_id)) {
                /** @var ilConditionSet $set */
                $set = $controller->getConditionSetForRepositoryObject($a_target_ref_id);

                // convert to old structure
                $cond = array();
                foreach ($set->getConditions() as $c) {
                    $obligatory = $set->getAllObligatory()
                        ? true
                        : $c->getObligatory();
                    $trigger = $c->getTrigger();
                    $cond[] = array(
                        "target_ref_id" => $a_target_ref_id,
                        "target_obj_id" => $a_target_obj_id,
                        "target_type" => $a_target_type,
                        "trigger_ref_id" => $trigger->getRefId(),
                        "trigger_obj_id" => $trigger->getObjId(),
                        "trigger_type" => $trigger->getType(),
                        "operator" => $c->getOperator(),
                        "value" => $c->getValue(),
                        "ref_handling" => 1,
                        "obligatory" => (int) $obligatory,
                        "num_obligatory" => $set->getNumObligatory(),
                        "hidden_status" => (int) $set->getHiddenStatus()
                    );
                }
                return $cond;
            }
        }

        return self::_getPersistedConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_type);
    }

    /**
     * get all persisted conditions of target object
     * @param int $a_target_ref_id
     * @param int $a_target_obj_id
     * @param string $a_target_type
     * @return array
     */
    public static function _getPersistedConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_type = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // get type if no type given
        if ($a_target_type == "") {
            $a_target_type = ilObject::_lookupType($a_target_obj_id);
        }

        // check conditions for target cache
        if (isset(self::$cond_for_target_cache[$a_target_ref_id . ":" . $a_target_obj_id . ":" .
            $a_target_type])) {
            return self::$cond_for_target_cache[$a_target_ref_id . ":" . $a_target_obj_id . ":" .
                $a_target_type];
        }

        // check rows cache
        if (isset(self::$cond_target_rows[$a_target_type . ":" . $a_target_obj_id])) {
            $rows = self::$cond_target_rows[$a_target_type . ":" . $a_target_obj_id];
        } else {
            // query data from db
            $query = "SELECT * FROM conditions " .
                "WHERE target_obj_id = " . $ilDB->quote($a_target_obj_id, 'integer') . " " .
                " AND target_type = " . $ilDB->quote($a_target_type, 'text');

            $res = $ilDB->query($query);
            $rows = array();
            while ($row = $ilDB->fetchAssoc($res)) {
                $rows[] = $row;
            }
        }

        reset($rows);
        $conditions = array();
        foreach ($rows as $row) {
            if ($row["ref_handling"] == self::UNIQUE_CONDITIONS) {
                if ($row["target_ref_id"] != $a_target_ref_id) {
                    continue;
                }
            }
            
            $row["id"] = $row["condition_id"];
            $conditions[] = $row;
        }

        // write conditions for target cache
        self::$cond_for_target_cache[$a_target_ref_id . ":" . $a_target_obj_id . ":" .
            $a_target_type] = $conditions;

        return $conditions;
    }

    /**
     * Preload conditions for target records
     *
     * @param
     * @return
     */
    public static function preloadPersistedConditionsForTargetRecords($a_type, $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (is_array($a_obj_ids) && count($a_obj_ids) > 0) {
            $res = $ilDB->query("SELECT * FROM conditions " .
                "WHERE " . $ilDB->in("target_obj_id", $a_obj_ids, false, "integer") .
                " AND target_type = " . $ilDB->quote($a_type, 'text'));
            $rows = array();
            while ($row = $ilDB->fetchAssoc($res)) {
                self::$cond_target_rows[$a_type . ":" . $row["target_obj_id"]][]
                    = $row;
            }
            // init obj ids without any record
            foreach ($a_obj_ids as $obj_id) {
                if (!is_array(self::$cond_target_rows[$a_type . ":" . $obj_id])) {
                    self::$cond_target_rows[$a_type . ":" . $obj_id] = array();
                }
            }
        }
    }

    public static function _getCondition($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM conditions " .
            "WHERE condition_id = " . $ilDB->quote($a_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmp_array['id'] = $row->condition_id;
            $tmp_array['target_ref_id'] = $row->target_ref_id;
            $tmp_array['target_obj_id'] = $row->target_obj_id;
            $tmp_array['target_type'] = $row->target_type;
            $tmp_array['trigger_ref_id'] = $row->trigger_ref_id;
            $tmp_array['trigger_obj_id'] = $row->trigger_obj_id;
            $tmp_array['trigger_type'] = $row->trigger_type;
            $tmp_array['operator'] = $row->operator;
            $tmp_array['value'] = $row->value;
            $tmp_array['ref_handling'] = $row->ref_handling;
            $tmp_array['obligatory'] = $row->obligatory;
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
    public static function _checkCondition($condition, $a_usr_id = 0)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $objDefinition = $DIC['objDefinition'];

        $a_usr_id = $a_usr_id ? $a_usr_id : $ilUser->getId();
        
        //$condition = ilConditionHandler::_getCondition($a_id);
        
        // check lp
        if ($condition['operator'] == self::OPERATOR_LP) {
            include_once './Services/Tracking/classes/class.ilLPStatus.php';
            return ilLPStatus::_hasUserCompleted($condition['trigger_obj_id'], $a_usr_id);
        }
        
        switch ($condition['trigger_type']) {
            case 'crsg':
                include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';
                return ilObjCourseGrouping::_checkCondition($condition['trigger_obj_id'], $condition['operator'], $condition['value'], $a_usr_id);
        }
        
        $class = $objDefinition->getClassName($condition['trigger_type']);
        $location = $objDefinition->getLocation($condition['trigger_type']);
        $full_class = "ilObj" . $class . "Access";
        include_once($location . "/class." . $full_class . ".php");

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
     * @param string $a_obj_type
     * @return array
     */
    public static function getEffectiveOptionalConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_obj_type = '')
    {
        $conditions = self::_getEffectiveConditionsOfTarget($a_target_ref_id, $a_target_obj_id);
        
        $opt = array();
        foreach ($conditions as $con) {
            if ($con['obligatory']) {
                continue;
            }
            
            $opt[] = $con;
        }
        return $opt;
    }
    
    /**
     * Get optional conditions
     * @param int $a_target_ref_id
     * @param int $a_target_obj_id
     * @param string $a_obj_type
     * @return array
     */
    public static function getPersistedOptionalConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_obj_type = '')
    {
        $conditions = self::_getPersistedConditionsOfTarget($a_target_ref_id, $a_target_obj_id);

        $opt = array();
        foreach ($conditions as $con) {
            if ($con['obligatory']) {
                continue;
            }

            $opt[] = $con;
        }
        return $opt;
    }


    /**
     * Lookup obligatory conditions of target
     * @param type $a_target_ref_id
     * @param type $a_target_obj_id
     */
    public static function lookupObligatoryConditionsOfTarget($a_target_ref_id, $a_target_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT max(num_obligatory) obl from conditions WHERE ' .
            'target_ref_id = ' . $ilDB->quote($a_target_ref_id, 'integer') . ' ' .
            'AND target_obj_id = ' . $ilDB->quote($a_target_obj_id, 'integer') . ' ' .
            'GROUP BY (num_obligatory)';
        $res = $ilDB->query($query);
        
        $obl = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obl = $row->obl;
        }
        return $obl;
    }

    /**
     * calculate number of obligatory items
     * @param int $a_target_ref_id
     * @param int $a_target_obj_id
     * @return int
     */
    public static function calculateEffectiveRequiredTriggers($a_target_ref_id, $a_target_obj_id, $a_target_obj_type = '')
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Get all conditions
        $all = self::_getEffectiveConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_obj_type);
        $opt = self::getEffectiveOptionalConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_obj_type);

        $set_obl = 0;
        if (isset($all[0])) {
            $set_obl = $all[0]['num_obligatory'];
        }

        // existing value is valid
        if ($set_obl > 0 and
            $set_obl < count($all) and
            $set_obl > (count($all) - count($opt) + 1)) {
            return $set_obl;
        }

        if (count($opt)) {
            $result = count($all) - count($opt) + 1;
        } else {
            $result = count($all);
        }
        return $result;
    }

    /**
     * calculate number of obligatory items
     * @param int $a_target_ref_id
     * @param int $a_target_obj_id
     * @return int
     */
    public static function calculatePersistedRequiredTriggers($a_target_ref_id, $a_target_obj_id, $a_target_obj_type = '', $a_force_update = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Get all conditions

        self::resetCache();
        $all = self::_getPersistedConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_obj_type);
        $opt = self::getPersistedOptionalConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_obj_type);

        $set_obl = 0;
        if (isset($all[0])) {
            $set_obl = $all[0]['num_obligatory'];
        }
        
        // existing value is valid
        if ($set_obl > 0 and
            $set_obl < count($all) and
            $set_obl > (count($all) - count($opt) + 1)) {
            return $set_obl;
        }
        
        if (count($opt)) {
            $result = count($all) - count($opt) + 1;
        } else {
            $result = count($all);
        }
        if ($a_force_update) {
            self::saveNumberOfRequiredTriggers($a_target_ref_id, $a_target_obj_id, $result);
        }
        return $result;
    }

    /**
     * Save number of obigatory triggers
     * @param int $a_target_ref_id
     * @param int $a_target_obj_id
     */
    public static function saveNumberOfRequiredTriggers($a_target_ref_id, $a_target_obj_id, $a_num)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'UPDATE conditions ' .
            'SET num_obligatory = ' . $ilDB->quote($a_num, 'integer') . ' ' .
            'WHERE target_ref_id = ' . $ilDB->quote($a_target_ref_id, 'integer') . ' ' .
            'AND target_obj_id = ' . $ilDB->quote($a_target_obj_id, 'integer');
        $ilDB->manipulate($query);
        return;
    }

    /**
    * checks wether all conditions of a target object are fulfilled
    */
    public static function _checkAllConditionsOfTarget($a_target_ref_id, $a_target_id, $a_target_type = "", $a_usr_id = 0)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        $logger = $DIC->logger()->ac();
        
        $a_usr_id = $a_usr_id ? $a_usr_id : $ilUser->getId();

        $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($a_target_ref_id, $a_target_id, $a_target_type);

        if (!count($conditions)) {
            return true;
        }

        // @todo check this
        include_once './Services/Container/classes/class.ilMemberViewSettings.php';
        if (ilMemberViewSettings::getInstance()->isActive()) {
            return true;
        }

        // First check obligatory conditions
        $optional = self::getEffectiveOptionalConditionsOfTarget($a_target_ref_id, $a_target_id, $a_target_type);
        $num_required = self::calculateEffectiveRequiredTriggers($a_target_ref_id, $a_target_id, $a_target_type);
        $passed = 0;
        foreach ($conditions as $condition) {
            if ($tree->isDeleted($condition['trigger_ref_id'])) {
                continue;
            }
            $check = ilConditionHandler::_checkCondition($condition, $a_usr_id);

            if ($check) {
                ++$passed;
            } else {
                // #0027223 if condition is obligatory => return false
                if ($condition['obligatory']) {
                    return false;
                }
            }
        }
        if ($passed >= $num_required) {
            return true;
        }

        // not all optional conditions passed
        return false;
    }

    // PRIVATE
    protected function validate()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // check if obj_id is already assigned
        $trigger_obj = &ilObjectFactory::getInstanceByRefId($this->getTriggerRefId());
        $target_obj = &ilObjectFactory::getInstanceByRefId($this->getTargetRefId());


        $query = "SELECT * FROM conditions WHERE " .
            "trigger_ref_id = " . $ilDB->quote($trigger_obj->getRefId(), 'integer') . " " .
            "AND target_ref_id = " . $ilDB->quote($target_obj->getRefId(), 'integer');

        $res = $this->db->query($query);
        if ($res->numRows() > 1) {
            $this->setErrorMessage($this->lng->txt('condition_already_assigned'));

            unset($trigger_obj);
            unset($target_obj);
            return false;
        }

        // check for circle
        $this->target_obj_id = $target_obj->getId();
        if ($this->checkCircle($this->getTargetRefId(), $target_obj->getId())) {
            $this->setErrorMessage($this->lng->txt('condition_circle_created'));
            
            unset($trigger_obj);
            unset($target_obj);
            return false;
        }
        return true;
    }

    protected function checkCircle($a_ref_id, $a_obj_id)
    {
        foreach (ilConditionHandler::_getPersistedConditionsOfTarget($a_ref_id, $a_obj_id) as $condition) {
            if ($condition['trigger_obj_id'] == $this->target_obj_id and $condition['operator'] == $this->getOperator()) {
                $this->circle = true;
                break;
            } else {
                $this->checkCircle($condition['trigger_ref_id'], $condition['trigger_obj_id']);
            }
        }
        return $this->circle;
    }
    
    public static function cloneDependencies($a_src_ref_id, $a_target_ref_id, $a_copy_id)
    {
        include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        $valid = 0;
        $conditions = ilConditionHandler::_getPersistedConditionsOfTarget($a_src_ref_id, ilObject::_lookupObjId($a_src_ref_id));
        foreach ($conditions as $con) {
            if ($mappings[$con['trigger_ref_id']]) {
                $newCondition = new ilConditionHandler();

                $target_obj = ilObject::_lookupObjId($a_target_ref_id);
                $target_typ = ilObject::_lookupType($target_obj);

                $newCondition->setTargetRefId($a_target_ref_id);
                $newCondition->setTargetObjId($target_obj);
                $newCondition->setTargetType($target_typ);

                $trigger_ref = $mappings[$con['trigger_ref_id']];
                $trigger_obj = ilObject::_lookupObjId($trigger_ref);
                $trigger_typ = ilObject::_lookupType($trigger_obj);

                $newCondition->setTriggerRefId($trigger_ref);
                $newCondition->setTriggerObjId($trigger_obj);
                $newCondition->setTriggerType($trigger_typ);
                $newCondition->setOperator($con['operator']);
                $newCondition->setValue($con['value']);
                $newCondition->setReferenceHandlingType($con['ref_handling']);
                $newCondition->setObligatory($con['obligatory']);
                
                // :TODO: not sure about this
                $newCondition->setHiddenStatus(self::lookupPersistedHiddenStatusByTarget($a_src_ref_id));
                
                if ($newCondition->storeCondition()) {
                    $valid++;

                    //Copy num_obligatory, to be checked below
                    self::saveNumberOfRequiredTriggers(
                        $a_target_ref_id,
                        $target_obj,
                        $con['num_obligatory']
                    );
                }
            }
        }
        if ($valid) {
            $tgt_obj_id = ilObject::_lookupObjId($a_target_ref_id);
            
            // num_obligatory
            self::calculatePersistedRequiredTriggers($a_target_ref_id, $tgt_obj_id, ilObject::_lookupType($tgt_obj_id), true);
        }
    }
}
