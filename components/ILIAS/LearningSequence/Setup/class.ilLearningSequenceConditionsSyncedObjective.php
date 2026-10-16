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

namespace ILIAS\LearningSequence\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use ilDBInterface;
use ilDatabaseInitializedObjective;
use ilDatabaseUpdateStepsExecutedObjective;
use ilLoggerFactory;
use ilLogger;
use ReflectionClass;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;

/**
 * Objective to ensure all Learning Sequence Conditions are registered in the database.
 */
class ilLearningSequenceConditionsSyncedObjective implements Objective
{
    public function __construct(
        protected ?ilObjLearningSequenceConditionDiscover $discoverer = null
    ) {
    }

    protected function getDiscoverer(): ilObjLearningSequenceConditionDiscover
    {
        if ($this->discoverer === null) {
            $this->discoverer = new ilObjLearningSequenceConditionDiscover();
        }
        return $this->discoverer;
    }

    public function getLabel(): string
    {
        return "Ensure all Learning Sequence Conditions are registered in Database";
    }

    public function isFulfilled(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        if (!$db instanceof ilDBInterface) {
            return true;
        }

        if (!$db->tableExists('lso_condition_types') || !$db->tableExists('lso_conditions')) {
            return false;
        }

        $discoverer = $this->getDiscoverer();
        $conditions = $discoverer->getAllConditions();

        $expected_names = [];
        $expected_tables = [];

        foreach ($conditions as $class) {
            $name = AbstractCondition::getIdentifierForClass($class);
            if ($name === '') {
                continue;
            }

            $expected_names[] = $name;

            $res = $db->queryF(
                "SELECT type_id FROM lso_condition_types WHERE condition_name = %s",
                ['text'],
                [$name]
            );
            if (!$db->fetchAssoc($res)) {
                return false;
            }
            /** @var AbstractCondition $class */
            $table_definitions = $class::migrate();
            foreach ($table_definitions as $def) {
                $expected_tables[] = $def->tableName;
                if (!$db->tableExists($def->tableName)) {
                    return false;
                }
            }
        }
        $res = $db->query("SELECT condition_name FROM lso_condition_types");
        while ($row = $db->fetchAssoc($res)) {
            if (!in_array($row['condition_name'], $expected_names)) {
                return false;
            }
        }
        $all_tables = $db->listTables();
        foreach ($all_tables as $table) {
            if (str_starts_with($table, "lso_c_")) {
                if (!in_array($table, $expected_tables)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        if (!$db instanceof ilDBInterface) {
            return $environment;
        }

        if (!$db->tableExists('lso_condition_types')) {
            $fields = [
                'type_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'condition_name' => [
                    'type' => 'text',
                    'length' => 255,
                    'notnull' => true
                ]
            ];
            $db->createTable('lso_condition_types', $fields);
            $db->addPrimaryKey('lso_condition_types', ["type_id"]);
            $db->createSequence('lso_condition_types');
        }

        if (!$db->tableExists('lso_conditions')) {
            $fields = [
                'condition_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'lso_ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'obj_ref_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ],
                'type_id' => [
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ]
            ];
            $db->createTable('lso_conditions', $fields);
            $db->addPrimaryKey('lso_conditions', ["condition_id"]);
            $db->createSequence('lso_conditions');
        }

        $discoverer = $this->getDiscoverer();
        $conditions = $discoverer->getAllConditions();

        $expected_names = [];
        $expected_tables = [];

        foreach ($conditions as $class) {
            $name = AbstractCondition::getIdentifierForClass($class);
            if ($name === '') {
                continue;
            }

            $expected_names[] = $name;

            $res = $db->queryF(
                "SELECT type_id FROM lso_condition_types WHERE condition_name = %s",
                ['text'],
                [$name]
            );
            if (!$db->fetchAssoc($res)) {
                $next_id = $db->nextId("lso_condition_types");
                $db->insert("lso_condition_types", [
                    "type_id" => ["integer", $next_id],
                    "condition_name" => ["text", $name]
                ]);
            }
            /** @var AbstractCondition $class */
            $table_definitions = $class::migrate();
            foreach ($table_definitions as $def) {
                $table_name = $def->tableName;
                $expected_tables[] = $table_name;
                if (!$db->tableExists($table_name)) {
                    $db->createTable($table_name, $def->fields);
                    if (count($def->primaryKeys) > 0) {
                        $db->addPrimaryKey($table_name, $def->primaryKeys);
                    }
                    if ($def->hasSequence) {
                        $db->createSequence($table_name);
                    }
                }
            }
        }
        $res = $db->query("SELECT condition_name FROM lso_condition_types");
        $names_in_db = [];
        while ($row = $db->fetchAssoc($res)) {
            $names_in_db[] = $row['condition_name'];
        }

        foreach ($names_in_db as $name_in_db) {
            if (!in_array($name_in_db, $expected_names)) {
                $res_type = $db->queryF(
                    "SELECT type_id FROM lso_condition_types WHERE condition_name = %s",
                    ['text'],
                    [$name_in_db]
                );
                $row_type = $db->fetchAssoc($res_type);
                if ($row_type) {
                    $type_id = (int) $row_type['type_id'];
                    $db->manipulateF(
                        "DELETE FROM lso_conditions WHERE type_id = %s",
                        ['integer'],
                        [$type_id]
                    );
                }

                $db->manipulateF(
                    "DELETE FROM lso_condition_types WHERE condition_name = %s",
                    ['text'],
                    [$name_in_db]
                );
            }
        }
        $all_tables = $db->listTables();
        foreach ($all_tables as $table) {
            if (str_starts_with($table, "lso_c_")) {
                if (!in_array($table, $expected_tables)) {
                    if ($db->sequenceExists($table)) {
                        $db->dropSequence($table);
                    }
                    $db->dropTable($table);
                }
            }
        }

        return $environment;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }

    public function isNotable(): bool
    {
        return true;
    }
}
