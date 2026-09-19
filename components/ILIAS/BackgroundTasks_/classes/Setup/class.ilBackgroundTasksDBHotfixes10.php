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

use ILIAS\components\Database\PDO\FieldDefinition\ForeignKeyConstraints;

class ilBackgroundTasksDBHotfixes10 implements ilDatabaseUpdateSteps
{
    private const TABLE_BUCKET = 'il_bt_bucket';
    private const TABLE_TASK = 'il_bt_task';
    private const TABLE_VALUE = 'il_bt_value';
    private const TABLE_VALUE_TO_TASK = 'il_bt_value_to_task';

    private const FOREIGN_KEY_BUCKET_ROOT_TASK = 'il_bt_bucket_fk_root_task';
    private const FOREIGN_KEY_BUCKET_CURRENT_TASK = 'il_bt_bucket_fk_current_task';
    private const FOREIGN_KEY_TASK_BUCKET = 'il_bt_task_fk_bucket';
    private const FOREIGN_KEY_VALUE_BUCKET = 'il_bt_value_fk_bucket';
    private const FOREIGN_KEY_VALUE_PARENT_TASK = 'il_bt_value_fk_parent_task';
    private const FOREIGN_KEY_VALUE_TO_TASK_TASK = 'il_bt_value_to_task_fk_task';
    private const FOREIGN_KEY_VALUE_TO_TASK_VALUE = 'il_bt_value_to_task_fk_value';
    private const FOREIGN_KEY_VALUE_TO_TASK_BUCKET = 'il_bt_value_to_task_fk_bucket';

    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $index_exists_user_id_state = $this->db->indexExistsByFields(self::TABLE_BUCKET, ['user_id', 'state']);
        if (
            $this->db->indexExistsByFields(self::TABLE_BUCKET, ['user_id'])
            && !$index_exists_user_id_state
        ) {
            $this->db->dropIndexByFields(self::TABLE_BUCKET, ['user_id']);
        }

        if (!$index_exists_user_id_state) {
            $this->db->addIndex(self::TABLE_BUCKET, ['user_id', 'state'], 'i1');
        }

        if (!$this->db->indexExistsByFields(self::TABLE_TASK, ['bucket_id'])) {
            $this->db->addIndex(self::TABLE_TASK, ['bucket_id'], 'i1');
        }

        if (!$this->db->indexExistsByFields(self::TABLE_VALUE_TO_TASK, ['bucket_id'])) {
            $this->db->addIndex(self::TABLE_VALUE_TO_TASK, ['bucket_id'], 'i3');
        }
    }

    public function step_2(): void
    {
        $tables = [
            self::TABLE_BUCKET,
            self::TABLE_TASK,
            self::TABLE_VALUE,
            self::TABLE_VALUE_TO_TASK,
        ];

        if ($tables !== $this->filterInnoDBTables($tables)) {
            return;
        }

        $this->removeOrphanedRecords();

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_TASK_BUCKET, self::TABLE_TASK)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_TASK_BUCKET,
                ['bucket_id'],
                self::TABLE_TASK,
                ['id'],
                self::TABLE_BUCKET,
                null,
                ForeignKeyConstraints::CASCADE
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_VALUE_BUCKET, self::TABLE_VALUE)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_VALUE_BUCKET,
                ['bucket_id'],
                self::TABLE_VALUE,
                ['id'],
                self::TABLE_BUCKET,
                null,
                ForeignKeyConstraints::CASCADE
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_VALUE_PARENT_TASK, self::TABLE_VALUE)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_VALUE_PARENT_TASK,
                ['parent_task_id'],
                self::TABLE_VALUE,
                ['id'],
                self::TABLE_TASK,
                null,
                ForeignKeyConstraints::CASCADE
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_VALUE_TO_TASK_TASK, self::TABLE_VALUE_TO_TASK)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_VALUE_TO_TASK_TASK,
                ['task_id'],
                self::TABLE_VALUE_TO_TASK,
                ['id'],
                self::TABLE_TASK,
                null,
                ForeignKeyConstraints::CASCADE
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_VALUE_TO_TASK_VALUE, self::TABLE_VALUE_TO_TASK)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_VALUE_TO_TASK_VALUE,
                ['value_id'],
                self::TABLE_VALUE_TO_TASK,
                ['id'],
                self::TABLE_VALUE,
                null,
                ForeignKeyConstraints::CASCADE
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_VALUE_TO_TASK_BUCKET, self::TABLE_VALUE_TO_TASK)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_VALUE_TO_TASK_BUCKET,
                ['bucket_id'],
                self::TABLE_VALUE_TO_TASK,
                ['id'],
                self::TABLE_BUCKET,
                null,
                ForeignKeyConstraints::CASCADE
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_BUCKET_ROOT_TASK, self::TABLE_BUCKET)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_BUCKET_ROOT_TASK,
                ['root_task_id'],
                self::TABLE_BUCKET,
                ['id'],
                self::TABLE_TASK,
                null,
                ForeignKeyConstraints::SET_NULL
            );
        }

        if (!$this->db->foreignKeyExists(self::FOREIGN_KEY_BUCKET_CURRENT_TASK, self::TABLE_BUCKET)) {
            $this->db->addForeignKey(
                self::FOREIGN_KEY_BUCKET_CURRENT_TASK,
                ['current_task_id'],
                self::TABLE_BUCKET,
                ['id'],
                self::TABLE_TASK,
                null,
                ForeignKeyConstraints::SET_NULL
            );
        }
    }

    private function removeOrphanedRecords(): void
    {
        if ($this->db->tableExists(self::TABLE_VALUE_TO_TASK)) {
            $this->db->manipulate(
                'DELETE vt FROM ' . self::TABLE_VALUE_TO_TASK . ' vt'
                . ' LEFT JOIN ' . self::TABLE_BUCKET . ' b ON b.id = vt.bucket_id'
                . ' LEFT JOIN ' . self::TABLE_TASK . ' t ON t.id = vt.task_id'
                . ' LEFT JOIN ' . self::TABLE_VALUE . ' v ON v.id = vt.value_id'
                . ' WHERE b.id IS NULL OR t.id IS NULL OR v.id IS NULL'
            );
        }

        if ($this->db->tableExists(self::TABLE_VALUE)) {
            $this->db->manipulate(
                'UPDATE ' . self::TABLE_VALUE
                . ' SET parent_task_id = NULL'
                . ' WHERE parent_task_id = 0 OR has_parent_task = 0'
            );

            $this->db->manipulate(
                'DELETE v FROM ' . self::TABLE_VALUE . ' v'
                . ' LEFT JOIN ' . self::TABLE_BUCKET . ' b ON b.id = v.bucket_id'
                . ' WHERE b.id IS NULL'
            );

            $this->db->manipulate(
                'DELETE v FROM ' . self::TABLE_VALUE . ' v'
                . ' LEFT JOIN ' . self::TABLE_TASK . ' t ON t.id = v.parent_task_id'
                . ' WHERE v.parent_task_id IS NOT NULL AND t.id IS NULL'
            );
        }

        if ($this->db->tableExists(self::TABLE_TASK)) {
            $this->db->manipulate(
                'DELETE t FROM ' . self::TABLE_TASK . ' t'
                . ' LEFT JOIN ' . self::TABLE_BUCKET . ' b ON b.id = t.bucket_id'
                . ' WHERE b.id IS NULL'
            );
        }

        if ($this->db->tableExists(self::TABLE_BUCKET)) {
            $this->db->manipulate(
                'UPDATE ' . self::TABLE_BUCKET . ' b'
                . ' LEFT JOIN ' . self::TABLE_TASK . ' rt ON rt.id = b.root_task_id'
                . ' SET b.root_task_id = NULL'
                . ' WHERE b.root_task_id IS NOT NULL AND b.root_task_id != 0 AND rt.id IS NULL'
            );

            $this->db->manipulate(
                'UPDATE ' . self::TABLE_BUCKET . ' b'
                . ' LEFT JOIN ' . self::TABLE_TASK . ' ct ON ct.id = b.current_task_id'
                . ' SET b.current_task_id = NULL'
                . ' WHERE b.current_task_id IS NOT NULL AND b.current_task_id != 0 AND ct.id IS NULL'
            );
        }
    }

    /**
     * @param string[] $tables
     * @return array<string, true>
     */
    private function filterInnoDBTables(array $tables): array
    {
        $innodb_tables = [];

        foreach ($tables as $table) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            $result = $this->db->queryF(
                'SELECT ENGINE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
                [ilDBConstants::T_TEXT],
                [$table]
            );
            $row = $this->db->fetchAssoc($result);

            if (strcasecmp((string) ($row['ENGINE'] ?? ''), ilDBConstants::MYSQL_ENGINE_INNODB) === 0) {
                $innodb_tables[] = $table;
            }
        }

        return $innodb_tables;
    }
}
