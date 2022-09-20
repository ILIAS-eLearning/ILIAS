<?php

declare(strict_types=1);

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

/**
 * Storage for ilLSPostConditions
 */
class ilLSPostConditionDB
{
    public const TABLE_NAME = 'post_conditions';
    public const STD_ALWAYS_OPERATOR = 'always';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return ilLSPostCondition[]
     */
    public function select(array $ref_ids): array
    {
        if ($ref_ids === []) {
            return [];
        }

        $data = [];
        $query =
              "SELECT ref_id, condition_operator, value" . PHP_EOL
            . "FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id IN (" . implode(',', $ref_ids) . ")" . PHP_EOL
        ;

        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            $data[$row['ref_id']] = [$row['condition_operator'], (int) $row['value']];
        }

        $conditions = [];
        foreach ($ref_ids as $ref_id) {
            //always-condition, standard
            $op = self::STD_ALWAYS_OPERATOR;
            $value = null;

            //if from db: proper values
            if (array_key_exists($ref_id, $data)) {
                list($op, $value) = $data[$ref_id];
            }
            $conditions[] = new \ilLSPostCondition($ref_id, $op, $value);
        }
        return $conditions;
    }

    public function delete(array $ref_ids, ilDBInterface $db = null): void
    {
        if ($ref_ids === []) {
            return;
        }

        if (is_null($db)) {
            $db = $this->db;
        }

        $query =
              "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id IN (" . implode(',', $ref_ids) . ")" . PHP_EOL
        ;

        $db->manipulate($query);
    }

    protected function insert(array $ls_post_conditions, ilDBInterface $db): void
    {
        foreach ($ls_post_conditions as $condition) {
            $values = [
                "ref_id" => ["integer", $condition->getRefId()],
                "condition_operator" => ["text", $condition->getConditionOperator()]
            ];
            $db->insert(static::TABLE_NAME, $values);
        }
    }

    /**
     * @param ilLSPostCondition[]
     */
    public function upsert(array $ls_post_conditions): void
    {
        if ($ls_post_conditions === []) {
            return;
        }

        $ref_ids = array_map(
            fn (ilLSPostCondition $condition) => $condition->getRefId(),
            $ls_post_conditions
        );

        $ilAtomQuery = $this->db->buildAtomQuery();
        $ilAtomQuery->addTableLock(static::TABLE_NAME);
        $ilAtomQuery->addQueryCallable(
            function (ilDBInterface $db) use ($ref_ids, $ls_post_conditions): void {
                $this->delete($ref_ids, $db);
                $this->insert($ls_post_conditions, $db);
            }
        );
        $ilAtomQuery->run();
    }
}
