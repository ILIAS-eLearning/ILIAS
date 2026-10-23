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
use ILIAS\LearningSequence\Content\Condition\ConditionTypes;
use ilDBInterface;
use ilDatabaseInitializedObjective;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;

/**
 * Objective to ensure all Learning Sequence Conditions are registered in the database.
 *
 * Condition types or tables which are not contributed (anymore) are left untouched: a
 * type that is missing for whatever reason must never lead to the loss of the conditions
 * configured by the users.
 */
class ilLearningSequenceConditionsSyncedObjective implements Objective
{
    public function __construct(
        protected ConditionTypes $condition_types
    ) {
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

        $conditions = $this->condition_types->getAllConditions();

        foreach ($conditions as $class) {
            $name = AbstractCondition::getIdentifierForClass($class);
            if ($name === '') {
                continue;
            }

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
                if (!$db->tableExists($def->tableName)) {
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

        $conditions = $this->condition_types->getAllConditions();

        foreach ($conditions as $class) {
            $name = AbstractCondition::getIdentifierForClass($class);
            if ($name === '') {
                continue;
            }

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
