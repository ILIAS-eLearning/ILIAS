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

namespace ILIAS\Test\Setup;

use ILIAS\Database\PDO\FieldDefinition\ForeignKeyConstraints;

class Test11DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    use TestSettingsSetup;

    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'mailnotification')) {
            $this->db->dropTableColumn('tst_tests', 'mailnotification');
        }
        if ($this->db->tableColumnExists('tst_tests', 'mailnottype')) {
            $this->db->dropTableColumn('tst_tests', 'mailnottype');
        }
    }

    public function step_2(): void
    {
        // 1. Create table schema
        if (!$this->db->tableExists('tst_test_settings')) {
            // Create table and sequence table
            $this->db->createTable('tst_test_settings', ['id' => ['type' => \ilDBConstants::T_INTEGER]]);
            $this->db->createSequence('tst_test_settings');
            $this->db->addPrimaryKey('tst_test_settings', ['id']);

            // Create table columns
            foreach (self::SETTINGS_COLUMNS as $key => $value) {
                [$column_def] = $value;

                // No columns should be nullable, except those with NULL by default
                if (!isset($column_def['notnull'])) {
                    $column_def['notnull'] = !$this->columnIsNullable($column_def);
                }

                $this->db->addTableColumn('tst_test_settings', $key, $column_def);
            }
        }

        // 2. Create a foreign key column in tst_tests
        if (!$this->db->tableColumnExists('tst_tests', 'settings_id')) {
            $this->db->addTableColumn(
                'tst_tests',
                'settings_id',
                ['type' => \ilDBConstants::T_INTEGER, 'default' => null, 'notnull' => false],
            );
            $this->db->addForeignKey(
                'test_settings_fkey',
                ['settings_id'],
                'tst_tests',
                ['id'],
                'tst_test_settings',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT,
            );
        }

        // 3. Create a foreign key column and new columns in tst_test_defaults
        if (!$this->db->tableColumnExists('tst_test_defaults', 'settings_id')) {
            $this->db->addTableColumn(
                'tst_test_defaults',
                'settings_id',
                ['type' => \ilDBConstants::T_INTEGER, 'default' => null, 'notnull' => false],
            );
            $this->db->addForeignKey(
                'test_default_fkey',
                ['settings_id'],
                'tst_test_defaults',
                ['id'],
                'tst_test_settings',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT
            );
        }
        if (!$this->db->tableColumnExists('tst_test_defaults', 'description')) {
            $this->db->addTableColumn(
                'tst_test_defaults',
                'description',
                ['type' => \ilDBConstants::T_TEXT, 'length' => 4000, 'default' => null],
            );
        }
        if (!$this->db->tableColumnExists('tst_test_defaults', 'author')) {
            $this->db->addTableColumn(
                'tst_test_defaults',
                'author',
                ['type' => \ilDBConstants::T_TEXT, 'length' => 255, 'default' => null],
            );
        }

        // 4. Create tst_defaults_marks table to store marks reference
        if (!$this->db->tableExists('tst_defaults_marks')) {
            $this->db->createTable(
                'tst_defaults_marks',
                [
                    'defaults_id' => ['type' => \ilDBConstants::T_INTEGER],
                    'mark_id' => ['type' => \ilDBConstants::T_INTEGER],
                ],
            );
            $this->db->addPrimaryKey('tst_defaults_marks', ['defaults_id', 'mark_id']);

            $this->db->addForeignKey(
                'test_default_fkey2',
                ['defaults_id '],
                'tst_defaults_marks',
                ['test_defaults_id'],
                'tst_test_defaults',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT
            );

            $this->db->addForeignKey(
                'test_mark_fkey',
                ['mark_id '],
                'tst_defaults_marks',
                ['mark_id'],
                'tst_mark',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::CASCADE
            );
        }
    }
}
