<?php

/**
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
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Conditions\Export;

use ilCondition;
use ilConditionFactory;
use ilConditionSet;
use ilConditionTrigger;
use ilDBConstants;
use ilDBInterface;
use ilImportMapping;

class Repository
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function writeByInfos(
        InfoCollection $infos,
    ): void {
        $values = [];
        foreach ($infos as $info) {
            foreach ($info->getConditionSet()->getConditions() as $condition) {
                $next_id = $this->db->nextId('conditions');
                $value_str = '(' . $this->db->quote($next_id, ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($info->getReferenceId(), ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($info->getObjectId(), ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($info->getObjectType(), ilDBConstants::T_TEXT) . ', ';
                $value_str .= $this->db->quote($condition->getTrigger()->getRefId(), ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($condition->getTrigger()->getObjId(), ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($condition->getTrigger()->getType(), ilDBConstants::T_TEXT) . ', ';
                $value_str .= $this->db->quote($condition->getOperator(), ilDBConstants::T_TEXT) . ', ';
                $value_str .= $this->db->quote($condition->getValue(), ilDBConstants::T_TEXT) . ', ';
                $value_str .= $this->db->quote(0, ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($condition->getObligatory(), ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($info->getConditionSet()->getNumObligatory(), ilDBConstants::T_INTEGER) . ', ';
                $value_str .= $this->db->quote($info->getConditionSet()->getHiddenStatus(), ilDBConstants::T_INTEGER) . ')';
                $values[] = $value_str;
            }
        }
        if (count($values) === 0) {
            return;
        }
        $query = 'INSERT INTO conditions(condition_id, target_ref_id, target_obj_id, target_type, trigger_ref_id, trigger_obj_id, trigger_type, operator, value, ref_handling, obligatory, num_obligatory, hidden_status) VALUES ' . implode(', ', $values);
        $this->db->manipulate($query);
    }

    /**
     * @param int[] $object_ids
     */
    public function getInfosByObjectIds(
        array $object_ids
    ): InfoCollection {
        $infos = [];
        $query = 'SELECT * FROM conditions WHERE ' . $this->db->in('target_obj_id', $object_ids, false, ilDBConstants::T_INTEGER);
        $result = $this->db->query($query);
        $data = [];
        while ($row = $result->fetchAssoc()) {
            $obj_id = (int) $row['target_obj_id'];
            if (!isset($data[$obj_id])) {
                $data[$obj_id] = [
                    'object_id' => $obj_id,
                    'ref_id' => (int) $row['target_ref_id'],
                    'target_type' => $row['target_type'],
                    'num_obligatory' => (int) $row['num_obligatory'],
                    'hidden_status' => (bool) $row['hidden_status'],
                    'preconditions' => [],
                    'all_obligatory' => true
                ];
            }
            $data[$obj_id]['all_obligatory'] = $data[$obj_id]['all_obligatory'] && ((bool) $row['obligatory']);
            $condition_trigger = new ilConditionTrigger(
                (int) $row['trigger_ref_id'],
                (int) $row['trigger_obj_id'],
                $row['trigger_type']
            );
            $condition = (new ilCondition(
                $condition_trigger,
                $row['operator'],
                $row['value']
            ))
                ->withId((int) $row['condition_id'])
                ->withObligatory((bool) $row['obligatory']);
            $data[$obj_id]['preconditions'][] = $condition;
        }
        $info_collection = new InfoCollection();
        foreach ($data as $data_entry) {
            $condition_set = (new ilConditionSet($data_entry['preconditions']))
                ->withHiddenStatus($data_entry['hidden_status'])
                ->withNumObligatory($data_entry['num_obligatory']);
            if ($data_entry['all_obligatory']) {
                $condition_set = $condition_set->withAllObligatory();
            }
            $info_collection = $info_collection->withInfo((new Info())
                ->withObjectId($data_entry['object_id'])
                ->withReferenceId($data_entry['ref_id'])
                ->withObjectType($data_entry['target_type'])
                ->withConditionSet($condition_set));
        }
        return $info_collection;
    }

    public function updateCourseValuesResultRangePercentage(
        int $course_ref_id,
        ilImportMapping $mapping
    ): void {
        $query = 'SELECT condition_id, value FROM conditions WHERE operator = "result_range_percentage" AND target_ref_id = ' . $this->db->quote($course_ref_id, ilDBConstants::T_INTEGER);
        $new_values = [];
        $result = $this->db->query($query);
        while ($row = $result->fetchAssoc()) {
            $value = unserialize($row['value']);
            $condition_id = (int) $row['condition_id'];
            if (!isset($value['objective'])) {
                continue;
            }
            $value['objective'] = $mapping->getMapping(
                'components/ILIAS/Course',
                'objectives',
                $value['objective']
            );
            $new_values[$condition_id] = serialize($value);
        }
        if (count($new_values) === 0) {
            return;
        }
        foreach ($new_values as $condition_id => $new_value) {
            $query = 'UPDATE conditions SET value = ' . $this->db->quote($new_value, ilDBConstants::T_TEXT) . ' WHERE condition_id = ' . $this->db->quote($condition_id, ilDBConstants::T_INTEGER);
            $this->db->manipulate($query);
        }
    }
}
