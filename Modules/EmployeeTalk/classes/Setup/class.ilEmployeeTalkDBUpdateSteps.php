<?php declare(strict_types=1);

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

use ilOrgUnitOperationContextQueries;
use ilOrgUnitOperationContext;
use ilOrgUnitOperationQueries;
use ilOrgUnitOperation;
use ilTree;
use ILIAS\Modules\EmployeeTalk\TalkSeries\Entity\EmployeeTalkSerieSettings;
use ilUtil;

/**
 * @author Nicolas Schaefli <nick@fluxlabs.ch>
 */
final class ilEmployeeTalkDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    private function useTransaction(callable $updateStep): void
    {
        try {
            if ($this->db->supportsTransactions()) {
                $this->db->beginTransaction();
            }

            $updateStep();

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

    public function step_1() : void
    {
        $this->useTransaction(function () {
            // create object data entry
            $id = $this->db->nextId("object_data");
            $this->db->manipulateF(
                "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) " .
                "VALUES (%s, %s, %s, %s, %s, %s, %s)",
                array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
                array($id, "tala", "__TalkTemplateAdministration", "Talk Templates", -1, ilUtil::now(), ilUtil::now())
            );

            // create object reference entry
            $ref_id = $this->db->nextId('object_reference');
            $res = $this->db->manipulateF(
                "INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
                array("integer", "integer"),
                array($ref_id, $id)
            );

            // put in tree
            $tree = new ilTree(ROOT_FOLDER_ID);
            $tree->insertNode($ref_id, SYSTEM_FOLDER_ID);
        });
    }

    public function step_2() : void
    {
        $this->useTransaction(function () {
            $etalTableName = 'etal_data';

            $this->db->createTable($etalTableName, [
                'object_id' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                'series_id' => ['type' => 'text', 'length' => 36, 'notnull' => true, 'fixed' => true],
                'start_date' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                'end_date' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                'all_day' => ['type' => 'integer', 'length' => 1, 'notnull' => true],
                'employee' => ['type' => 'integer', 'length' => 8, 'notnull' => true],
                'location' => ['type' => 'text', 'length' => 200, 'notnull' => false, 'fixed' => false],
                'completed' => ['type' => 'integer', 'length' => 1, 'notnull' => true]
            ]);

            $this->db->addPrimaryKey($etalTableName, ['object_id']);
            $this->db->addIndex($etalTableName, ['series_id'], 'ser');
            $this->db->addIndex($etalTableName, ['employee'], 'emp');
        });
    }

    public function step_3() : void
    {
        $this->useTransaction(function () {
            $etalTableName = 'etal_data';

            $this->db->addTableColumn(
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

    public function step_4() : void
    {
        $this->useTransaction(function () {
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

    public function step_5() : void
    {
        $this->useTransaction(function () {
            EmployeeTalkSerieSettings::updateDB();
        });
    }
}
