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

namespace ILIAS\ObjectGroup\Setup;

use ilDBConstants;
use ilObjectGroupAR;

class ilObjectGroupDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists($this->dataTableName(), 'list_presentation')) {
            $this->db->addTableColumn($this->dataTableName(), 'list_presentation', [
                'type' => 'text',
                'length' => 10,
            ]);
        }
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists($this->dataTableName(), 'tile_size')) {
            $this->db->addTableColumn($this->dataTableName(), 'tile_size', [
                'type' => 'integer',
                'notnull' => true,
                'default' => 0,
                'length' => 1,
            ]);
        }
    }

    public function step_3(): void
    {
        if (!$this->db->tableColumnExists($this->dataTableName(), 'display')) {
            $this->db->addTableColumn($this->dataTableName(), 'display', [
                'type' => ilDBConstants::T_TEXT,
                'notnull' => true,
                'default' => ilObjectGroupAR::DISPLAY_WITH_TITLE,
                'length' => 255,
            ]);
        }

        if (!$this->db->tableColumnExists($this->dataTableName(), 'toggleable_initially')) {
            $this->db->addTableColumn($this->dataTableName(), 'toggleable_initially', [
                'type' => ilDBConstants::T_TEXT,
                'notnull' => true,
                'default' => ilObjectGroupAR::DISPLAY_WITH_TITLE_AND_TOGGLEABLE_INITIALLY_OPEN,
                'length' => 255,
            ]);
        }
    }

    public function step_4(): void
    {
        $table = $this->dataTableName();

        if ($this->db->tableColumnExists($table, 'hide_title')) {
            $this->db->modifyTableColumn($table, 'hide_title', [
                'default' => ilObjectGroupDisplayMigration::MIGRATED_MARKER,
            ]);
        }

        if ($this->db->tableColumnExists($table, 'behaviour')) {
            $this->db->modifyTableColumn($table, 'behaviour', [
                'default' => ilObjectGroupDisplayMigration::MIGRATED_MARKER,
            ]);
        }
    }

    public function step_5(): void
    {
        if ($this->db->tableExists('itgr_data') && !$this->db->tableExists('obgr_data')) {
            $this->db->renameTable('itgr_data', 'obgr_data');
        }

        if ($this->db->tableExists('item_group_item') && !$this->db->tableExists('object_group_item')) {
            $this->db->renameTable('item_group_item', 'object_group_item');
        }

        $assignment_table = $this->assignmentTableName();

        if ($this->db->tableColumnExists($assignment_table, 'item_group_id')
            && !$this->db->tableColumnExists($assignment_table, 'object_group_id')) {
            $this->db->dropPrimaryKey($assignment_table);
            $this->db->renameTableColumn($assignment_table, 'item_group_id', 'object_group_id');
        }

        if ($this->db->tableColumnExists($assignment_table, 'item_ref_id')
            && !$this->db->tableColumnExists($assignment_table, 'object_ref_id')) {
            $this->db->renameTableColumn($assignment_table, 'item_ref_id', 'object_ref_id');
        }

        if ($this->db->tableExists($assignment_table)
            && $this->db->tableColumnExists($assignment_table, 'object_group_id')
            && $this->db->tableColumnExists($assignment_table, 'object_ref_id')
            && !$this->primaryKeyExists($assignment_table)) {
            $this->db->addPrimaryKey($assignment_table, ['object_group_id', 'object_ref_id']);
        }
    }

    private function primaryKeyExists(string $table): bool
    {
        $manager = $this->db->loadModule(ilDBConstants::MODULE_MANAGER);
        $reverse = $this->db->loadModule(ilDBConstants::MODULE_REVERSE);

        foreach ($manager->listTableConstraints($table) as $constraint) {
            $info = $reverse->getTableConstraintDefinition($table, $constraint);
            if ($info['primary'] ?? false) {
                return true;
            }
        }

        return false;
    }

    private function dataTableName(): string
    {
        if ($this->db->tableExists('obgr_data')) {
            return 'obgr_data';
        }

        return 'itgr_data';
    }

    private function assignmentTableName(): string
    {
        if ($this->db->tableExists('object_group_item')) {
            return 'object_group_item';
        }

        return 'item_group_item';
    }
}
