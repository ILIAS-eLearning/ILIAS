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
        });
    }

    public function step_3(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            $etalTableName = 'etal_data';

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
        });
    }

    public function step_4(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            ilOrgUnitOperationContextQueries::registerNewContext(
                ilOrgUnitOperationContext::CONTEXT_ETAL,
                ilOrgUnitOperationContext::CONTEXT_OBJECT
            );

            ilOrgUnitOperationQueries::registerNewOperation(
                ilOrgUnitOperation::OP_READ_EMPLOYEE_TALK,
                'Read Employee Talk',
                ilOrgUnitOperationContext::CONTEXT_ETAL
            );

            ilOrgUnitOperationQueries::registerNewOperation(
                ilOrgUnitOperation::OP_CREATE_EMPLOYEE_TALK,
                'Create Employee Talk',
                ilOrgUnitOperationContext::CONTEXT_ETAL
            );

            ilOrgUnitOperationQueries::registerNewOperation(
                ilOrgUnitOperation::OP_EDIT_EMPLOYEE_TALK,
                'Edit Employee Talk (not only own)',
                ilOrgUnitOperationContext::CONTEXT_ETAL
            );
        });
    }

    public function step_5(): void
    {
        $this->useTransaction(function (\ilDBInterface $db) {
            EmployeeTalkSerieSettings::updateDB(); // Please do not use updateDB in core!
        });
    }
}
