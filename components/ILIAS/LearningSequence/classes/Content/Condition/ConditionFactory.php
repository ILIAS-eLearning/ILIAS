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

use ilException;
use ilDBInterface;
use ReflectionClass;
use ReflectionException;

class ConditionFactory
{
    /**
     * Maps the short class name of a condition to its fully qualified name.
     *
     * @var array<string, string>|null
     */
    private ?array $class_map = null;

    /**
     * @var array<int, int[]> cache for the condition-ids per item ref_id
     */
    private array $condition_ids_by_item = [];

    public function __construct(
        private ConditionTypes $condition_types,
        private ilDBInterface $db
    ) {
    }

    /**
     * @return int[]
     */
    public function getConditionIdsForItem(int $item_ref_id): array
    {
        if (isset($this->condition_ids_by_item[$item_ref_id])) {
            return $this->condition_ids_by_item[$item_ref_id];
        }

        $query = $this->db->queryF(
            'SELECT condition_id FROM lso_conditions WHERE obj_ref_id = %s',
            ['integer'],
            [$item_ref_id],
        );

        $conditions = [];
        while ($record = $this->db->fetchAssoc($query)) {
            $conditions[] = (int) $record['condition_id'];
        }

        $this->condition_ids_by_item[$item_ref_id] = $conditions;
        return $conditions;
    }

    /**
     * Loads the condition-ids for many items with a single query instead of
     * one query per item. Subsequent calls to getConditionIdsForItem() for
     * the given items are served from the cache.
     *
     * @param int[] $item_ref_ids
     * @return array<int, int[]> condition-ids per item ref_id
     */
    public function preloadConditionIdsForItems(array $item_ref_ids): array
    {
        $item_ref_ids = array_values(array_unique(array_map('intval', $item_ref_ids)));
        $missing = array_values(array_filter(
            $item_ref_ids,
            fn(int $ref_id): bool => !isset($this->condition_ids_by_item[$ref_id])
        ));

        if ($missing !== []) {
            foreach ($missing as $ref_id) {
                $this->condition_ids_by_item[$ref_id] = [];
            }

            $query = $this->db->query(
                'SELECT condition_id, obj_ref_id FROM lso_conditions WHERE '
                . $this->db->in('obj_ref_id', $missing, false, 'integer')
            );
            while ($record = $this->db->fetchAssoc($query)) {
                $this->condition_ids_by_item[(int) $record['obj_ref_id']][] = (int) $record['condition_id'];
            }
        }

        $result = [];
        foreach ($item_ref_ids as $ref_id) {
            $result[$ref_id] = $this->condition_ids_by_item[$ref_id];
        }
        return $result;
    }

    /**
     * @throws UnknownConditionTypeException if the type of the condition is not contributed
     * @throws ilException|ReflectionException
     */
    public function getConditionInstanceById(int $condition_id): AbstractCondition
    {
        $query = $this->db->queryF(
            'SELECT * FROM lso_conditions AS c JOIN lso_condition_types AS t ON c.type_id = t.type_id WHERE condition_id = %s',
            ['integer'],
            [$condition_id]
        );
        $record = $this->db->fetchAssoc($query);

        if (empty($record)) {
            throw new ilException("Condition with ID {$condition_id} not found.");
        }

        $condition = $this->getConditionInstanceByName($record['condition_name']);
        $condition->setConditionId($condition_id);
        $condition->read();

        return $condition;
    }

    /**
     * Create a new instance for creating a condition
     *
     * @throws ilException
     * @throws ReflectionException
     */
    public function getNewConditionInstance(
        int $lso_ref_id,
        int $item_ref_id,
        int $type_id,
        ?string $subtype = null
    ): AbstractCondition {
        $query = $this->db->queryF(
            'SELECT condition_name FROM lso_condition_types WHERE type_id = %s',
            ['integer'],
            [$type_id]
        );
        $record = $this->db->fetchAssoc($query);

        if (empty($record)) {
            throw new ilException("Condition type with ID {$type_id} not found.");
        }

        $condition = $this->getConditionInstanceByName($record['condition_name']);
        $condition->setLsoRefId($lso_ref_id);
        $condition->setObjRefId($item_ref_id);

        if ($subtype !== null && $condition instanceof SubtypeAwareInterface) {
            $condition->setSubtype($subtype);
        }

        return $condition;
    }

    /**
     * @throws UnknownConditionTypeException if no contributed condition type has this name
     * @throws ReflectionException
     */
    public function getConditionInstanceByName(string $condition_name): AbstractCondition
    {
        $class_map = $this->getClassMap();

        foreach ([$condition_name . 'Condition', $condition_name] as $candidate) {
            if (isset($class_map[$candidate])) {
                $class = $class_map[$candidate];
                /** @var AbstractCondition $condition */
                $condition = new $class();
                $condition->setConditionFactory($this);
                return $condition;
            }
        }

        throw new UnknownConditionTypeException($condition_name);
    }

    /**
     * @return array<string, string>
     * @throws ReflectionException
     */
    private function getClassMap(): array
    {
        if ($this->class_map !== null) {
            return $this->class_map;
        }

        $map = [];
        foreach ($this->condition_types->getAllConditions() as $class) {
            $map[new ReflectionClass($class)->getShortName()] = $class;
            $map[$class] = $class;
        }

        $this->class_map = $map;
        return $map;
    }
}
