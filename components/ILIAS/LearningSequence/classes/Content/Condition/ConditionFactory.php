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
     * Building this map requires reflection over all discovered condition
     * classes, which never change within a request.
     *
     * @var array<string, string>|null
     */
    private static ?array $class_map = null;

    public function __construct(
        private ilObjLearningSequenceConditionDiscover $discover,
        private ilDBInterface $db
    ) {
    }

    /**
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
     * @throws ilException|ReflectionException
     */
    public function getConditionInstanceByName(string $condition_name): AbstractCondition
    {
        $class_map = $this->getClassMap();

        foreach ([$condition_name . 'Condition', $condition_name] as $candidate) {
            if (isset($class_map[$candidate])) {
                $class = $class_map[$candidate];
                /** @var AbstractCondition $condition */
                return new $class();
            }
        }

        throw new ilException("Condition class for '{$condition_name}' not found.");
    }

    /**
     * @return array<string, string>
     * @throws ReflectionException
     */
    private function getClassMap(): array
    {
        if (self::$class_map !== null) {
            return self::$class_map;
        }

        $map = [];
        foreach ($this->discover->getAllConditions() as $class) {
            $map[new ReflectionClass($class)->getShortName()] = $class;
            $map[$class] = $class;
        }

        self::$class_map = $map;
        return $map;
    }
}
