<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * INTERNAL CLASS: Please do not use in consumer code.
 * Handles conditions for accesses to different ILIAS objects
 * A condition consists of four elements:
 * - a trigger object, e.g. a test or a survey question
 * - an operator, e.g. "=", "<", "passed"
 * - an (optional) value, e.g. "5"
 * - a target object, e.g. a learning module
 * If a condition is fulfilled for a certain user, (s)he may access
 * the target object. This first implementation handles only one access
 * type per object, which is usually "read" access. A possible
 * future extension may implement different access types.
 * The condition data is stored in the database table "condition"
 * (Note: This table must not be accessed directly from other classes.
 * The data should be accessed via the interface of class ilCondition.)
 *   cond_id                    INT            condition id
 *   trigger_obj_type        VARCHAR(10)    "crs" | "tst" | ...
 *   trigger_ref_id            INT            obj id of trigger object    (only exception where this is 0 are currently (5.3) course groupings this might be refactored
 *   trigger_obj_id            INT            obj id of trigger object
 *   operator                varchar(10  "=", "<", ">", ">=", "<=", "passed", "contains", ...
 *   value                    VARCHAR(10) optional value
 *   target_obj_type            VARCHAR(10)    "lm" | "frm" | "st", "lobj", ...
 *   target_obj_id            object id of target object
 *   target_ref_id            reference id of target object
 * Special current targets (5.3)
 * - learning objectives: type: "lobj"; obj_id: objective id; ref_id: ref id of course
 * - lm chapters: type: "st"; obj_id: chapter id, ref_id: ref id of learning module
 * Trigger objects are always stored with their object id (if a test has been
 * passed by a user, he doesn't need to repeat it in other contexts. But
 * target objects are usually stored with their reference id if available,
 * otherwise, if they are non-referenced objects (e.g. (survey) questions)
 * they are stored with their object id.
 * Stefan Meyer 10-08-2004
 * In addition we store the ref_id of the trigger object to allow the target object to link to the triggered object.
 * But it's not possible to assign two or more linked (same obj_id) triggered objects to a target object
 * Examples:
 * Learning module 5 may only be accessed, if test 6 has been passed:
 *   trigger_obj_type        "tst"
 *   trigger_id                6 (object id)
 *   trigger_ref_id            117
 *   operator                "passed"
 *   value
 *   target_obj_type            "lm"
 *   target_id                5 (reference id)
 * Survey question 10 should only be presented, if survey question 8
 * is answered with a value greater than 4.
 *   trigger_obj_type        "qst"
 *   trigger_id                8 (question (instance) object id)
 *   trigger_ref_id            117
 *   operator                ">"
 *   value                    "4"
 *   target_obj_type            "lm"
 *   target_id                10 (question (instance) object id)
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilConditionHandler
{
    public const OPERATOR_PASSED = 'passed';
    public const OPERATOR_FINISHED = 'finished';
    public const OPERATOR_NOT_FINISHED = 'not_finished';
    public const OPERATOR_NOT_MEMBER = 'not_member';
    public const OPERATOR_FAILED = 'failed';
    public const OPERATOR_LP = 'learning_progress';
    public const OPERATOR_ACCREDITED_OR_PASSED = 'accredited_or_passed';

    public const UNIQUE_CONDITIONS = 1;

    // conditions are used for all tree references of the target object.
    // This is currently only used for lm chapters and likely to be abandonded in the future
    public const SHARED_CONDITIONS = 0;

    public static array $cond_for_target_cache = array();
    public static array $cond_target_rows = array();

    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilObjectDefinition $objDefinition;
    protected ilTree $tree;
    protected ilLogger $logger;

    protected string $error_message = '';

    protected int $target_obj_id = 0;
    protected int $target_ref_id = 0;
    protected string $target_type = '';
    protected int $trigger_obj_id = 0;
    protected int $trigger_ref_id = 0;
    protected string $trigger_type = '';
    private int $condition_reference_type = 0;
    protected string $operator = '';
    protected string $value = '';
    protected bool $validation = true;
    private bool $circle = false;

    private bool $obligatory = true;
    private bool $hidden_status = false;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->objDefinition = $DIC['objDefinition'];
        $this->tree = $DIC->repositoryTree();
        $this->logger = $DIC->logger()->ac();
        $this->validation = true;
    }

    /**
     * @param string target type ILIAS obj type
     */
    public static function _isReferenceHandlingOptional(string $a_type): bool
    {
        return $a_type === 'st';
    }

    /**
     * Lookup hidden status (also take container control into account)
     */
    public static function lookupEffectiveHiddenStatusByTarget(int $a_target_ref_id): bool
    {
        global $DIC;

        $obj_definition = $DIC['objDefinition'];
        $tree = $DIC->repositoryTree();

        // check if parent takes over control of condition
        $parent_ref_id = $tree->getParentId($a_target_ref_id);
        $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
        $parent_type = ilObject::_lookupType($parent_obj_id);

        $class = $obj_definition->getClassName($parent_type);
        $class_name = "il" . $class . "ConditionController";
        $location = $obj_definition->getLocation($parent_type);

        // if yes, get from parent
        if ($class !== "" && is_file($location . "/class." . $class_name . ".php")) {
            /** @var ilConditionControllerInterface $controller */
            $controller = new $class_name();
            if ($controller->isContainerConditionController($parent_ref_id)) {
                return (bool) $controller->getConditionSetForRepositoryObject($a_target_ref_id)->getHiddenStatus();
            }
        }
        return self::lookupPersistedHiddenStatusByTarget($a_target_ref_id);
    }

    public static function lookupPersistedHiddenStatusByTarget(int $a_target_ref_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT hidden_status FROM conditions ' .
            'WHERE target_ref_id = ' . $ilDB->quote($a_target_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->hidden_status;
        }
        return false;
    }

    /**
     * In the moment it is not allowed to create preconditions on objects
     * that are located outside of a course.
     * Therefore, after moving an object: check for parent type 'crs'. if that fails delete preconditions
     * @todo check if something needs to be done here
     */
    public static function _adjustMovedObjectConditions(int $a_ref_id): bool
    {
        return true;
    }

    /**
     * @return int[]
     */
    protected static function _getDistinctTargetRefIds(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT DISTINCT target_ref_id ref FROM conditions ";
        $res = $ilDB->query($query);
        $ref_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = (int) $row->ref;
        }
        return $ref_ids;
    }

    /**
     * Delete conditions by target ref id
     * Note: only conditions on the target type are deleted
     * Conditions on e.g chapters are not handled.
     */
    protected static function _deleteTargetConditionsByRefId(int $a_target_ref_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM conditions " .
            "WHERE target_ref_id = " . $ilDB->quote($a_target_ref_id, 'integer') . " " .
            "AND target_type != 'st' ";
        $res = $ilDB->manipulate($query);
        return true;
    }

    public function setReferenceHandlingType(int $a_type): void
    {
        $this->condition_reference_type = $a_type;
    }

    public function getReferenceHandlingType(): int
    {
        return $this->condition_reference_type;
    }

    public function setErrorMessage(string $a_msg): void
    {
        $this->error_message = $a_msg;
    }

    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

    public function setTargetRefId(int $a_target_ref_id): void
    {
        $this->target_ref_id = $a_target_ref_id;
    }

    public function getTargetRefId(): int
    {
        return $this->target_ref_id;
    }

    public function setTargetObjId(int $a_target_obj_id): void
    {
        $this->target_obj_id = $a_target_obj_id;
    }

    public function getTargetObjId(): int
    {
        return $this->target_obj_id;
    }

    /**
     * set target object type
     */
    public function setTargetType(string $a_target_type): void
    {
        $this->target_type = $a_target_type;
    }

    /**
     * get target obj type
     */
    public function getTargetType(): string
    {
        return $this->target_type;
    }

    public function setTriggerRefId(int $a_trigger_ref_id): void
    {
        $this->trigger_ref_id = $a_trigger_ref_id;
    }

    public function getTriggerRefId(): int
    {
        return $this->trigger_ref_id;
    }

    public function setTriggerObjId(int $a_trigger_obj_id): void
    {
        $this->trigger_obj_id = $a_trigger_obj_id;
    }

    public function getTriggerObjId(): int
    {
        return $this->trigger_obj_id;
    }

    /**
     * set trigger object type
     */
    public function setTriggerType(string $a_trigger_type): void
    {
        $this->trigger_type = $a_trigger_type;
    }

    /**
     * get trigger obj type
     */
    public function getTriggerType(): string
    {
        return $this->trigger_type;
    }

    public function setOperator(string $a_operator): void
    {
        $this->operator = $a_operator;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Set obligatory status
     */
    public function setObligatory(bool $a_obl): void
    {
        $this->obligatory = $a_obl;
    }

    /**
     * Get obligatory status
     */
    public function getObligatory(): bool
    {
        return $this->obligatory;
    }

    public function setHiddenStatus(bool $a_status): void
    {
        $this->hidden_status = $a_status;
    }

    public function getHiddenStatus(): bool
    {
        return $this->hidden_status;
    }

    public function enableAutomaticValidation(bool $a_validate = true): void
    {
        $this->validation = $a_validate;
    }

    public function getTriggerTypes(): array
    {
        $trigger_types = array('crs', 'exc', 'tst', 'sahs', 'svy', 'lm', 'iass', 'prg', 'copa', 'lti', 'cmix');

        // Add operator lp trigger
        if (ilObjUserTracking::_enabledLearningProgress()) {
            // only if object type has lp
            foreach ($this->objDefinition->getAllRepositoryTypes() as $t) {
                if (ilObjectLP::isSupportedObjectType($t) && !in_array($t, $trigger_types, true)) {
                    $trigger_types[] = $t;
                }
            }
        }
        foreach ($this->objDefinition->getPlugins() as $p_type => $p_info) {
            try {
                $name = 'ilObj' . $p_info['class_name'] . 'Access';
                $reflection = new ReflectionClass($name);
                if ($reflection->implementsInterface('ilConditionHandling')) {
                    $trigger_types[] = $p_type;
                }
            } catch (ReflectionException $e) {
                $this->logger->warning('Cannot create instance for ' . $name);
                $this->logger->warning($e->getMessage());
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
     * @return string[]
     */
    public function getOperatorsByTriggerType(string $a_type): array
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];

        if ($a_type === 'crsg') {
            return ['not_member'];
        }

        $class = $objDefinition->getClassName($a_type);
        $location = $objDefinition->getLocation($a_type);
        $full_class = "ilObj" . $class . "Access";
        include_once($location . "/class." . $full_class . ".php");
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
        if (ilObjUserTracking::_enabledLearningProgress()) {
            // only if object type has lp

            if (ilObjectLP::isSupportedObjectType($a_type)) {
                array_unshift($operators, self::OPERATOR_LP);
            }
        }
        return $operators;
    }

    /**
     * store new condition in database
     */
    public function storeCondition(): bool
    {
        // first insert, then validate: it's easier to check for circles if the new condition is in the db table
        $next_id = $this->db->nextId('conditions');
        $query = 'INSERT INTO conditions (condition_id,target_ref_id,target_obj_id,target_type,' .
            'trigger_ref_id,trigger_obj_id,trigger_type,operator,value,ref_handling,obligatory,hidden_status) ' .
            'VALUES (' .
            $this->db->quote($next_id, 'integer') . ',' .
            $this->db->quote($this->getTargetRefId(), 'integer') . "," .
            $this->db->quote($this->getTargetObjId(), 'integer') . "," .
            $this->db->quote($this->getTargetType(), 'text') . "," .
            $this->db->quote($this->getTriggerRefId(), 'integer') . "," .
            $this->db->quote($this->getTriggerObjId(), 'integer') . "," .
            $this->db->quote($this->getTriggerType(), 'text') . "," .
            $this->db->quote($this->getOperator(), 'text') . "," .
            $this->db->quote($this->getValue(), 'text') . ", " .
            $this->db->quote($this->getReferenceHandlingType(), 'integer') . ', ' .
            $this->db->quote($this->getObligatory(), 'integer') . ', ' .
            $this->db->quote($this->getHiddenStatus(), 'integer') . ' ' .
            ')';

        $res = $this->db->manipulate($query);

        if ($this->validation && !$this->validate()) {
            $this->deleteCondition($next_id);
            return false;
        }
        return true;
    }

    public function checkExists(): bool
    {
        $query = "SELECT * FROM conditions " .
            "WHERE target_ref_id = " . $this->db->quote($this->getTargetRefId(), 'integer') . " " .
            "AND target_obj_id = " . $this->db->quote($this->getTargetObjId(), 'integer') . " " .
            "AND trigger_ref_id = " . $this->db->quote($this->getTriggerRefId(), 'integer') . " " .
            "AND trigger_obj_id = " . $this->db->quote($this->getTriggerObjId(), 'integer') . " " .
            "AND operator = " . $this->db->quote($this->getOperator(), 'text');
        $res = $this->db->query($query);
        return (bool) $res->numRows();
    }

    public function updateCondition(int $a_id): void
    {
        $query = "UPDATE conditions SET " .
            "target_ref_id = " . $this->db->quote($this->getTargetRefId(), 'integer') . ", " .
            "operator = " . $this->db->quote($this->getOperator(), 'text') . ", " .
            "value = " . $this->db->quote($this->getValue(), 'text') . ", " .
            "ref_handling = " . $this->db->quote($this->getReferenceHandlingType(), 'integer') . ", " .
            'obligatory = ' . $this->db->quote($this->getObligatory(), 'integer') . ' ' .
            "WHERE condition_id = " . $this->db->quote($a_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    public function updateHiddenStatus(bool $a_status): void
    {
        $query = 'UPDATE conditions SET ' .
            'hidden_status = ' . $this->db->quote($a_status, 'integer') . ' ' .
            'WHERE target_ref_id = ' . $this->db->quote($this->getTargetRefId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Toggle condition obligatory status
     */
    public static function updateObligatory(int $a_id, bool $a_status): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "UPDATE conditions SET " .
            'obligatory = ' . $ilDB->quote($a_status, 'integer') . ' ' .
            "WHERE condition_id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    /**
     * delete all trigger and target entries
     * This method is called from ilObject::delete() if an object os removed from trash
     */
    public function delete(int $a_ref_id): void
    {
        $query = "DELETE FROM conditions WHERE " .
            "target_ref_id = " . $this->db->quote($a_ref_id, 'integer') . " " .
            "OR trigger_ref_id = " . $this->db->quote($a_ref_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * delete all trigger and target entries
     * This method is called from ilObject::delete() if an object is removed from trash
     */
    public function deleteByObjId(int $a_obj_id): void
    {
        $query = "DELETE FROM conditions WHERE " .
            "target_obj_id = " . $this->db->quote($a_obj_id, 'integer') . " " .
            "OR trigger_obj_id = " . $this->db->quote($a_obj_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    public function deleteCondition(int $a_id): void
    {
        $query = "DELETE FROM conditions " .
            "WHERE condition_id = " . $this->db->quote($a_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * get all conditions of trigger object
     */
    public static function getNumberOfConditionsOfTrigger(string $a_trigger_obj_type, int $a_trigger_id): int
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
     * @return array<int, array<string, mixed>>
     */
    public static function _getPersistedConditionsOfTrigger(string $a_trigger_obj_type, int $a_trigger_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM conditions " .
            "WHERE trigger_obj_id = " . $ilDB->quote($a_trigger_id, 'integer') . " " .
            " AND trigger_type = " . $ilDB->quote($a_trigger_obj_type, 'text');

        $res = $ilDB->query($query);
        $conditions = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmp_array = [];
            $tmp_array['id'] = (int) $row->condition_id;
            $tmp_array['target_ref_id'] = (int) $row->target_ref_id;
            $tmp_array['target_obj_id'] = (int) $row->target_obj_id;
            $tmp_array['target_type'] = (string) $row->target_type;
            $tmp_array['trigger_ref_id'] = (int) $row->trigger_ref_id;
            $tmp_array['trigger_obj_id'] = (int) $row->trigger_obj_id;
            $tmp_array['trigger_type'] = (string) $row->trigger_type;
            $tmp_array['operator'] = (string) $row->operator;
            $tmp_array['value'] = (string) $row->value;
            $tmp_array['ref_handling'] = (int) $row->ref_handling;
            $tmp_array['obligatory'] = (bool) $row->obligatory;
            $tmp_array['hidden_status'] = (bool) $row->hidden_status;
            $conditions[] = $tmp_array;
        }
        return $conditions;
    }

    /**
     * get all conditions of target object (also take container control into account)
     * @todo refactor with returning new ilCondition
     * @return array<int, array<string, mixed>>
     */
    public static function _getEffectiveConditionsOfTarget(
        int $a_target_ref_id,
        int $a_target_obj_id,
        string $a_target_type = ""
    ): array {
        global $DIC;

        if ($a_target_ref_id === 0) {
            return [];
        }

        $obj_definition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        // get type if no type given
        if ($a_target_type === "") {
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
        if ($class !== "" && is_file($location . "/class." . $class_name . ".php")
            && $a_target_type === ilObject::_lookupType($a_target_ref_id, true)) {
            /** @var ilConditionControllerInterface $controller */
            $controller = new $class_name();
            if ($controller->isContainerConditionController($parent_ref_id)) {
                /** @var ilConditionSet $set */
                $set = $controller->getConditionSetForRepositoryObject($a_target_ref_id);

                // convert to old structure
                $cond = [];
                foreach ($set->getConditions() as $c) {
                    $obligatory = $set->getAllObligatory() || $c->getObligatory();
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
                        "obligatory" => $obligatory,
                        "num_obligatory" => $set->getNumObligatory(),
                        "hidden_status" => $set->getHiddenStatus()
                    );
                }
                return $cond;
            }
        }

        return self::_getPersistedConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_type);
    }

    /**
     * get all persisted conditions of target object
     * @return array<int, array<string, mixed>>
     */
    public static function _getPersistedConditionsOfTarget(
        int $a_target_ref_id,
        int $a_target_obj_id,
        string $a_target_type = ""
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // get type if no type given
        if ($a_target_type === "") {
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
                $item = [];
                $item['condition_id'] = (int) $row['condition_id'];
                $item['target_ref_id'] = (int) $row['target_ref_id'];
                $item['target_obj_id'] = (int) $row['target_obj_id'];
                $item['trigger_ref_id'] = (int) $row['trigger_ref_id'];
                $item['trigger_obj_id'] = (int) $row['trigger_obj_id'];
                $item['target_type'] = (string) $row['target_type'];
                $item['trigger_type'] = (string) $row['trigger_type'];
                $item['operator'] = (string) $row['operator'];
                $item['value'] = (string) $row['value'];
                $item['ref_handling'] = (int) $row['ref_handling'];
                $item['obligatory'] = (bool) $row['obligatory'];
                $item['num_obligatory'] = (int) $row['num_obligatory'];
                $item['hidden_status'] = (bool) $row['hidden_status'];

                $rows[] = $item;
            }
        }

        reset($rows);
        $conditions = [];
        foreach ($rows as $row) {
            if (($row["ref_handling"] == self::UNIQUE_CONDITIONS) && $row["target_ref_id"] != $a_target_ref_id) {
                continue;
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
     * @param int[] $a_obj_ids
     */
    public static function preloadPersistedConditionsForTargetRecords(string $a_type, array $a_obj_ids): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (is_array($a_obj_ids) && count($a_obj_ids) > 0) {
            $res = $ilDB->query("SELECT * FROM conditions " .
                "WHERE " . $ilDB->in("target_obj_id", $a_obj_ids, false, "integer") .
                " AND target_type = " . $ilDB->quote($a_type, 'text'));
            $rows = array();
            while ($row = $ilDB->fetchAssoc($res)) {
                $item = [];
                $item['condition_id'] = (int) $row['condition_id'];
                $item['target_ref_id'] = (int) $row['target_ref_id'];
                $item['target_obj_id'] = (int) $row['target_obj_id'];
                $item['trigger_ref_id'] = (int) $row['trigger_ref_id'];
                $item['trigger_obj_id'] = (int) $row['trigger_obj_id'];
                $item['target_type'] = (string) $row['target_type'];
                $item['trigger_type'] = (string) $row['trigger_type'];
                $item['operator'] = (string) $row['operator'];
                $item['value'] = (string) $row['value'];
                $item['ref_handling'] = (int) $row['ref_handling'];
                $item['obligatory'] = (bool) $row['obligatory'];
                $item['num_obligatory'] = (int) $row['num_obligatory'];
                $item['hidden_status'] = (bool) $row['hidden_status'];
                self::$cond_target_rows[$a_type . ":" . $row["target_obj_id"]][] = $item;
            }
            // init obj ids without any record
            foreach ($a_obj_ids as $obj_id) {
                if (!isset(self::$cond_target_rows[$a_type . ":" . $obj_id])) {
                    self::$cond_target_rows[$a_type . ":" . $obj_id] = array();
                }
            }
        }
    }

    public static function _getCondition(int $a_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM conditions " .
            "WHERE condition_id = " . $ilDB->quote($a_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $tmp_array['id'] = (int) $row->condition_id;
            $tmp_array['target_ref_id'] = (int) $row->target_ref_id;
            $tmp_array['target_obj_id'] = (int) $row->target_obj_id;
            $tmp_array['target_type'] = (string) $row->target_type;
            $tmp_array['trigger_ref_id'] = (int) $row->trigger_ref_id;
            $tmp_array['trigger_obj_id'] = (int) $row->trigger_obj_id;
            $tmp_array['trigger_type'] = (string) $row->trigger_type;
            $tmp_array['operator'] = (string) $row->operator;
            $tmp_array['value'] = (string) $row->value;
            $tmp_array['ref_handling'] = (int) $row->ref_handling;
            $tmp_array['obligatory'] = (bool) $row->obligatory;
            $tmp_array['hidden_status'] = (bool) $row->hidden_status;
            $tmp_array['num_obligatory'] = (int) $row->num_obligatory;
            return $tmp_array;
        }
        return [];
    }

    /**
     * checks wether a single condition is fulfilled
     * every trigger object type must implement a static method
     * _checkCondition($a_operator, $a_value)
     */
    public static function _checkCondition(array $condition, int $a_usr_id = 0): bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $objDefinition = $DIC['objDefinition'];
        $a_usr_id = $a_usr_id ?: $ilUser->getId();

        // check lp
        if ($condition['operator'] === self::OPERATOR_LP) {
            return ilLPStatus::_hasUserCompleted($condition['trigger_obj_id'], $a_usr_id);
        }
        switch ($condition['trigger_type']) {
            case 'crsg':
                return ilObjCourseGrouping::_checkCondition(
                    $condition['trigger_obj_id'],
                    $condition['operator'],
                    $condition['value'],
                    $a_usr_id
                );
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

    public static function getEffectiveOptionalConditionsOfTarget(
        int $a_target_ref_id,
        int $a_target_obj_id,
        string $a_obj_type = ''
    ): array {
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

    public static function getPersistedOptionalConditionsOfTarget(
        int $a_target_ref_id,
        int $a_target_obj_id,
        string $a_obj_type = ''
    ): array {
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

    public static function lookupObligatoryConditionsOfTarget(int $a_target_ref_id, int $a_target_obj_id): int
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
            $obl = (int) $row->obl;
        }
        return $obl;
    }

    public static function calculateEffectiveRequiredTriggers(
        int $a_target_ref_id,
        int $a_target_obj_id,
        string $a_target_obj_type = ''
    ): int {
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
        if ($set_obl > 0 &&
            $set_obl < count($all) &&
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

    public static function calculatePersistedRequiredTriggers(
        int $a_target_ref_id,
        int $a_target_obj_id,
        string $a_target_obj_type = '',
        bool $a_force_update = false
    ): int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Get all conditions
        $all = self::_getPersistedConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_obj_type);
        $opt = self::getPersistedOptionalConditionsOfTarget($a_target_ref_id, $a_target_obj_id, $a_target_obj_type);

        $set_obl = 0;
        if (isset($all[0])) {
            $set_obl = $all[0]['num_obligatory'];
        }

        // existing value is valid
        if ($set_obl > 0 &&
            $set_obl < count($all) &&
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

    public static function saveNumberOfRequiredTriggers(int $a_target_ref_id, int $a_target_obj_id, int $a_num): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'UPDATE conditions ' .
            'SET num_obligatory = ' . $ilDB->quote($a_num, 'integer') . ' ' .
            'WHERE target_ref_id = ' . $ilDB->quote($a_target_ref_id, 'integer') . ' ' .
            'AND target_obj_id = ' . $ilDB->quote($a_target_obj_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * checks wether all conditions of a target object are fulfilled
     * @todo check member view passthrough
     */
    public static function _checkAllConditionsOfTarget(
        int $a_target_ref_id,
        int $a_target_id,
        string $a_target_type = "",
        int $a_usr_id = 0
    ): bool {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        $logger = $DIC->logger()->ac();

        $a_usr_id = $a_usr_id ?: $ilUser->getId();
        $conditions = self::_getEffectiveConditionsOfTarget(
            $a_target_ref_id,
            $a_target_id,
            $a_target_type
        );
        if (!count($conditions)) {
            return true;
        }

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
            $check = self::_checkCondition($condition, $a_usr_id);

            if ($check) {
                ++$passed;
            } else {
                // #0027223 if condition is obligatory => return false
                if ($condition['obligatory']) {
                    return false;
                }
            }
        }
        return $passed >= $num_required;
    }

    // PRIVATE
    protected function validate(): bool
    {
        // check if obj_id is already assigned
        $trigger_obj = ilObjectFactory::getInstanceByRefId($this->getTriggerRefId());
        $target_obj = ilObjectFactory::getInstanceByRefId($this->getTargetRefId());

        if ($trigger_obj !== null && $target_obj !== null) {
            $query = "SELECT * FROM conditions WHERE " .
                "trigger_ref_id = " . $this->db->quote($trigger_obj->getRefId(), 'integer') . " " .
                "AND target_ref_id = " . $this->db->quote($target_obj->getRefId(), 'integer');

            $res = $this->db->query($query);

            if ($res->numRows() > 1) {
                $this->setErrorMessage($this->lng->txt('condition_already_assigned'));

                unset($trigger_obj, $target_obj);
                return false;
            }
            // check for circle
            $this->target_obj_id = $target_obj->getId();

            if ($this->checkCircle($this->getTargetRefId(), $target_obj->getId())) {
                $this->setErrorMessage($this->lng->txt('condition_circle_created'));

                unset($trigger_obj, $target_obj);
                return false;
            }
            return true;
        }
        return false;
    }

    protected function checkCircle(int $a_ref_id, int $a_obj_id): bool
    {
        foreach (self::_getPersistedConditionsOfTarget($a_ref_id, $a_obj_id) as $condition) {
            if ($condition['trigger_obj_id'] == $this->target_obj_id && $condition['operator'] === $this->getOperator()) {
                $this->circle = true;
                break;
            }

            $this->checkCircle($condition['trigger_ref_id'], $condition['trigger_obj_id']);
        }
        return $this->circle;
    }

    public static function cloneDependencies(int $a_src_ref_id, int $a_target_ref_id, int $a_copy_id): void
    {
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        $valid = 0;
        $conditions = self::_getPersistedConditionsOfTarget(
            $a_src_ref_id,
            ilObject::_lookupObjId($a_src_ref_id)
        );
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
                }
            }
        }
        if ($valid) {
            $tgt_obj_id = ilObject::_lookupObjId($a_target_ref_id);

            // num_obligatory
            self::calculatePersistedRequiredTriggers(
                $a_target_ref_id,
                $tgt_obj_id,
                ilObject::_lookupType($tgt_obj_id),
                true
            );
        }
    }
}
