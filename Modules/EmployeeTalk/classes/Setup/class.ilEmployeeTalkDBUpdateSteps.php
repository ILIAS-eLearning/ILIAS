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

namespace ILIAS\EmployeeTalk\Setup;

use ILIAS\Modules\EmployeeTalk\TalkSeries\Entity\EmployeeTalkSerieSettings;
use ilOrgUnitOperation;
use ilOrgUnitOperationContext;
use ilOrgUnitOperationContextQueries;
use ilOrgUnitOperationQueries;

/**
 * @author Nicolas Schaefli <nick@fluxlabs.ch>
 */
final class ilEmployeeTalkDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    private function useTransaction(callable $updateStep): void
    {
        try {
            if ($this->db->supportsTransactions()) {
                $this->db->beginTransaction();
            }
            $updateStep($this->db);

            if ($this->db->supportsTransactions()) {
                $this->db->commit();
            }
        } catch (\Exception $exception) {
            if ($this->db->supportsTransactions()) {
                $this->db->rollback();
            }
            throw $exception;
        }
    }

    public function step_1(): void
    {
        // removed this content in favour of a ilTreeAdminNodeAddedObjective
    }

    public function step_2(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            $etalTableName = 'etal_data';

            if (!$db->tableExists($etalTableName)) {
                $db->createTable($etalTableName, [
                    'object_id' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                    'series_id' => ['type' => 'text', 'length' => 36, 'notnull' => true, 'fixed' => true],
                    'start_date' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                    'end_date' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                    'all_day' => ['type' => 'integer', 'length' => 1, 'notnull' => true],
                    'employee' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                    'location' => ['type' => 'text', 'length' => 200, 'notnull' => false, 'fixed' => false],
                    'completed' => ['type' => 'integer', 'length' => 1, 'notnull' => true]
                ]);

                $db->addPrimaryKey($etalTableName, ['object_id']);
                $db->addIndex($etalTableName, ['series_id'], 'ser');
                $db->addIndex($etalTableName, ['employee'], 'emp');
            }
        });
    }

    public function step_3(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            $etalTableName = 'etal_data';

            if (!$db->tableColumnExists($etalTableName, 'standalone_date')) {
                $db->addTableColumn(
                    $etalTableName,
                    'standalone_date',
                    [
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => true,
                        'default' => 0
                    ]
                );
            }
        });
    }

    public function step_4(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            $this->registerNewOrgUnitOperationContext(
                $db,
                ilOrgUnitOperationContext::CONTEXT_ETAL,
                ilOrgUnitOperationContext::CONTEXT_OBJECT
            );

            $this->registerNewOrgUnitOperation(
                $db,
                ilOrgUnitOperation::OP_READ_EMPLOYEE_TALK,
                'Read Employee Talk',
                ilOrgUnitOperationContext::CONTEXT_ETAL
            );

            $this->registerNewOrgUnitOperation(
                $db,
                ilOrgUnitOperation::OP_CREATE_EMPLOYEE_TALK,
                'Create Employee Talk',
                ilOrgUnitOperationContext::CONTEXT_ETAL
            );

            $this->registerNewOrgUnitOperation(
                $db,
                ilOrgUnitOperation::OP_EDIT_EMPLOYEE_TALK,
                'Edit Employee Talk (not only own)',
                ilOrgUnitOperationContext::CONTEXT_ETAL
            );
        });
    }

    public function step_5(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            $table_name = 'etal_serie';

            if (!$db->tableExists($table_name)) {
                $db->createTable($table_name, [
                    'id' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                    'editing_locked' => ['type' => 'integer', 'length' => 1, 'notnull' => true],
                ]);

                $db->addPrimaryKey($table_name, ['id']);
            }
        });
    }

    protected function registerNewOrgUnitOperationContext(
        \ilDBInterface $db,
        string $context_name,
        string $parent_context
    ): void {
        // abort if the context already exists
        $result = $db->query('SELECT * FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($context_name, 'text'));
        if ($result->numRows()) {
            return;
        }

        // abort if the parent context does not exist
        $result = $db->query('SELECT id FROM il_orgu_op_contexts
          WHERE context = ' . $db->quote($parent_context, 'text'));
        if (!($row = $result->fetchObject())) {
            return;
        }
        $parent_context_id = (int) $row->id;

        $id = $db->nextId('il_orgu_op_contexts');
        $db->insert('il_orgu_op_contexts', [
            'id' => ['integer', $id],
            'context' => ['text', $context_name],
            'parent_context_id' => ['integer', $parent_context_id]
        ]);
    }

    protected function registerNewOrgUnitOperation(
        \ilDBInterface $db,
        string $operation_name,
        string $description,
        string $context
    ): void {
        // abort if context does not exist
        $result = $db->query('SELECT id FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($context, 'text'));
        if (!($row = $result->fetchObject())) {
            return;
        }
        $context_id = (int) $row->id;

        // abort if operation does already exist in this context
        $result = $db->query('SELECT * FROM il_orgu_operations
            WHERE context_id = ' . $db->quote($context_id, 'integer') .
            ' AND operation_string = ' . $db->quote($operation_name, 'text'));
        if ($result->numRows()) {
            return;
        }

        $id = $db->nextId('il_orgu_operations');
        $db->insert('il_orgu_operations', [
            'operation_id' => ['integer', $id],
            'operation_string' => ['text', $operation_name],
            'description' => ['text', $description],
            'list_order' => ['integer', 0],
            'context_id' => ['integer', $context_id],
        ]);
    }
}
