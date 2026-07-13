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

use ILIAS\Database\PDO\FieldDefinition\ForeignKeyConstraints;

/**
 * Interface ilDBManager
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBManager
{
    /**
     * @return string[]
     */
    public function listTables(?string $database = null): array;

    /**
     * @return string[]
     */
    public function listSequences(?string $database = null): array;

    public function createConstraint(string $table, string $name, array $definition): bool;

    /**
     * @return string[]
     */
    public function listTableFields(string $table): array;

    /**
     * @return string[]
     */
    public function listTableConstraints(string $table): array;

    public function createSequence(string $seq_name, int $start = 1, array $options = []): bool;

    /**
     * @return string[]
     */
    public function listTableIndexes(string $table): array;

    public function alterTable(string $name, array $changes, bool $check): bool;

    public function createIndex(string $table, string $name, array $definition): bool;

    public function dropIndex(string $table, string $name): bool;

    public function dropSequence(string $seq_name): bool;

    public function dropConstraint(string $table, string $name, bool $primary = false): bool;

    /**
     * @param $name string
     */
    public function dropTable(string $name): bool;

    public function getQueryUtils(): \ilQueryUtilsInterface;

    public function createTable(string $name, array $fields, array $options = []): bool;

    /**
     * @param string[] $field_names
     * @param string[] $reference_field_names
     */
    public function addForeignKey(
        string $foreign_key_name,
        array $field_names,
        string $table_name,
        array $reference_field_names,
        string $reference_table,
        ?ForeignKeyConstraints $on_update = null,
        ?ForeignKeyConstraints $on_delete = null
    ): bool;

    public function foreignKeyExists(string $foreign_key_name, string $table_name): bool;

    public function dropForeignKey(string $foreign_key_name, string $table_name): bool;
}
