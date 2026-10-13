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

namespace ILIAS\LearningSequence\Content\Condition;

class ConditionHandler
{
    protected ?\ilDBInterface $db = null;
    protected ?\ilLanguage $lng = null;
    protected ilObjLearningSequenceConditionDiscover $discoverer;
    private ConditionFactory $condition_factory;

    public function __construct(?\ilDBInterface $db = null)
    {
        global $DIC;
        $this->db = $db;
        if ($this->db === null && isset($DIC) && $DIC instanceof \ILIAS\DI\Container) {
            $this->db = $DIC->database();
        }
        if (isset($DIC) && $DIC instanceof \ILIAS\DI\Container) {
            $this->lng = $DIC->language();
        }
        $this->discoverer = new ilObjLearningSequenceConditionDiscover();
        $this->condition_factory = new ConditionFactory(
            $this->discoverer,
            $this->db,
        );
    }

    /**
     * @return array[]
     */
    public function getInputConditionsByRefId(int $lso_ref_id, int $obj_ref_id): array
    {
        $db_conditions = $this->getConditionsFromDb($lso_ref_id, $obj_ref_id, $this->discoverer->getAllInputConditions());
        return $this->formatConditionsForGui($db_conditions);
    }

    /**
     * @return array[]
     */
    public function getOutputConditionsByRefId(int $lso_ref_id, int $obj_ref_id): array
    {
        $db_conditions = $this->getConditionsFromDb($lso_ref_id, $obj_ref_id, $this->discoverer->getAllOutputConditions());
        return $this->formatConditionsForGui($db_conditions);
    }

    /**
     * Deletes all conditions attached to an object that is being removed from
     * the Learning Sequence, regardless of the current operation mode.
     *
     * For every condition found for the deleted object it delegates to the
     * concrete condition instance's public delete() method, which removes both
     * the condition-specific payload (deleteConditionData()) and the generic
     * entry in lso_conditions. If the concrete condition can no longer be
     * resolved, the generic lso_conditions entry is removed as a fallback.
     *
     * @param int $lso_ref_id     ref_id of the Learning Sequence object
     * @param int $obj_ref_id     ref_id of the deleted object
     */
    public function deleteConditionsByRefId(int $lso_ref_id, int $obj_ref_id): void
    {
        if ($this->db === null) {
            return;
        }

        $res = $this->db->queryF(
            "SELECT condition_id, type_id FROM lso_conditions WHERE lso_ref_id = %s AND obj_ref_id = %s",
            ['integer', 'integer'],
            [$lso_ref_id, $obj_ref_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $condition_id = (int) $row['condition_id'];

            try {
                $condition = $this->condition_factory->getConditionInstanceById($condition_id);
                $condition->delete();
            } catch (\Throwable $e) {
                // If the concrete condition can no longer be resolved we still
                // remove the generic lso_conditions entry as a fallback.
                $this->deleteCondition($condition_id);
            }
        }
    }

    /**
     * Deletes all conditions that belong to a Learning Sequence itself.
     *
     * Besides the generic rows in lso_conditions this also clears all
     * condition-specific payload rows from every lso_c_* table by condition_id.
     */
    public function deleteConditionsByLSORefId(int $lso_ref_id): void
    {
        if ($this->db === null) {
            return;
        }

        $condition_ids = [];
        $res = $this->db->queryF(
            'SELECT condition_id FROM lso_conditions WHERE lso_ref_id = %s',
            ['integer'],
            [$lso_ref_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $condition_ids[] = (int) $row['condition_id'];
        }

        if ($condition_ids !== []) {
            foreach ($this->db->listTables() as $table) {
                if (!str_starts_with((string) $table, 'lso_c_')) {
                    continue;
                }

                $this->db->manipulate(
                    'DELETE FROM ' . $table . ' WHERE ' . $this->db->in('condition_id', $condition_ids, false, 'integer')
                );
            }
        }

        $this->db->manipulateF(
            'DELETE FROM lso_conditions WHERE lso_ref_id = %s',
            ['integer'],
            [$lso_ref_id]
        );
    }

    /**
     * Deletes a single condition entry from lso_conditions by its condition id.
     *
     * @param int $condition_id id of the condition to delete
     */
    public function deleteCondition(int $condition_id): void
    {
        if ($this->db === null) {
            return;
        }

        $this->db->manipulateF(
            'DELETE FROM lso_conditions WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
    }

    /**
     * @param array[] $db_conditions
     * @return array[]
     */
    protected function formatConditionsForGui(array $db_conditions): array
    {
        if ($this->db === null) {
            return [];
        }

        $condition_factory = new ConditionFactory($this->discoverer, $this->db);
        $result = [];
        foreach ($db_conditions as $db_cond) {
            $type_id = $db_cond['type_id'];
            $res = $this->db->queryF(
                "SELECT condition_name FROM lso_condition_types WHERE type_id = %s",
                ['integer'],
                [$type_id]
            );
            $row = $this->db->fetchAssoc($res);
            if ($row) {
                $class = $this->discoverer->getConditionByName($row['condition_name']);
                if ($class) {
                    try {
                        $condition = $condition_factory->getConditionInstanceById((int) $db_cond['condition_id']);
                        $result[] = [
                            'title' => $this->lng->txt((string) $condition->getName()),
                            'value' => $condition instanceof SubtypeAwareInterface
                                ? $condition->getSubtypeLabel($condition->getSubtype())
                                : '',
                            'internal_name' => $row['condition_name']
                        ];
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return array[]
     */
    protected function getConditionsFromDb(int $lso_ref_id, int $obj_ref_id, array $available_classes): array
    {
        if ($this->db === null) {
            return [];
        }

        $names = [];
        foreach ($available_classes as $class) {
            try {
                $reflection = new \ReflectionClass($class);
                if ($reflection->isInstantiable()) {
                    $names[] = AbstractCondition::getIdentifierForClass($class);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        if (count($names) === 0) {
            return [];
        }

        $query = "SELECT c.* 
                  FROM lso_conditions c
                  INNER JOIN lso_condition_types t ON c.type_id = t.type_id
                  WHERE c.lso_ref_id = %s AND c.obj_ref_id = %s AND " . $this->db->in('t.condition_name', $names, false, 'text');

        $res = $this->db->queryF($query, ['integer', 'integer'], [$lso_ref_id, $obj_ref_id]);
        $result = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $result[] = $row;
        }
        return $result;
    }
}
