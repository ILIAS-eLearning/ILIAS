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

namespace ILIAS\Database;

use ILIAS\Database\PDO\FieldDefinition\ForeignKeyConstraints;
use stdClass;
use ilAtomQuery;
use ilIniFile;
use ilDBStatement;
use ilDBConstants;
use ILIAS\Database\Integrity\Integrity;
use ILIAS\Database\PDO\Internal;
use ILIAS\Database\PDO\External;

/**
 * @author     Fabian Schmid <fabian@sr.solutions>
 * @deprecated This is onnly used for components which already use Component::init for bootstrapping
 */
class DBLegacyProxy implements External, Internal
{
    public function getFieldDefinition(): ?FieldDefinition
    {
        return $GLOBALS['DIC']['ilDB']->getFieldDefinition();
    }

    public function getIndexName(string $index_name_base): string
    {
        return $GLOBALS['DIC']['ilDB']->getIndexName($index_name_base);
    }

    public function getServerVersion(bool $native = false): int
    {
        return $GLOBALS['DIC']['ilDB']->getServerVersion($native);
    }

    public function queryCol(string $query, int $type = ilDBConstants::FETCHMODE_DEFAULT, int $colnum = 0): array
    {
        return $GLOBALS['DIC']['ilDB']->queryCol($query, $type, $colnum);
    }

    public function queryRow(
        string $query,
        ?array $types = null,
        int $fetchmode = ilDBConstants::FETCHMODE_DEFAULT
    ): array {
        return $GLOBALS['DIC']['ilDB']->queryRow($query, $types, $fetchmode);
    }

    public function escape(string $value, bool $escape_wildcards = false): string
    {
        return $GLOBALS['DIC']['ilDB']->escape($value, $escape_wildcards);
    }

    public function escapePattern(string $text): string
    {
        return $GLOBALS['DIC']['ilDB']->escapePattern($text);
    }

    public function migrateTableToEngine(string $table_name, string $engine = ilDBConstants::MYSQL_ENGINE_INNODB): void
    {
        $GLOBALS['DIC']['ilDB']->migrateTableToEngine($table_name, $engine);
    }

    public function migrateAllTablesToEngine(string $engine = ilDBConstants::MYSQL_ENGINE_INNODB): array
    {
        return $GLOBALS['DIC']['ilDB']->migrateAllTablesToEngine($engine);
    }

    public function supportsEngineMigration(): bool
    {
        return $GLOBALS['DIC']['ilDB']->supportsEngineMigration();
    }

    public function migrateTableCollation(
        string $table_name,
        string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4
    ): bool {
        return $GLOBALS['DIC']['ilDB']->migrateTableCollation($table_name, $collation);
    }

    public function migrateAllTablesToCollation(string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4): array
    {
        return $GLOBALS['DIC']['ilDB']->migrateAllTablesToCollation($collation);
    }

    public function supportsCollationMigration(): bool
    {
        return $GLOBALS['DIC']['ilDB']->supportsCollationMigration();
    }

    public function addUniqueConstraint(string $table, array $fields, string $name = "con"): bool
    {
        return $GLOBALS['DIC']['ilDB']->addUniqueConstraint($table, $fields, $name);
    }

    public function dropUniqueConstraint(string $table, string $name = "con"): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropUniqueConstraint($table, $name);
    }

    public function dropUniqueConstraintByFields(string $table, array $fields): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropUniqueConstraintByFields($table, $fields);
    }

    public function checkIndexName(string $name): bool
    {
        return $GLOBALS['DIC']['ilDB']->checkIndexName($name);
    }

    public function getLastInsertId(): int
    {
        return $GLOBALS['DIC']['ilDB']->getLastInsertId();
    }

    public function uniqueConstraintExists(string $table, array $fields): bool
    {
        return $GLOBALS['DIC']['ilDB']->uniqueConstraintExists($table, $fields);
    }

    public function dropPrimaryKey(string $table_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropPrimaryKey($table_name);
    }

    public function executeMultiple(ilDBStatement $stmt, array $data): array
    {
        return $GLOBALS['DIC']['ilDB']->executeMultiple($stmt, $data);
    }

    public function fromUnixtime(string $expr, bool $to_text = true): string
    {
        return $GLOBALS['DIC']['ilDB']->fromUnixtime($expr, $to_text);
    }

    public function unixTimestamp(): string
    {
        return $GLOBALS['DIC']['ilDB']->unixTimestamp();
    }

    public function getDBVersion(): string
    {
        return $GLOBALS['DIC']['ilDB']->getDBVersion();
    }

    public function doesCollationSupportMB4Strings(): bool
    {
        return $GLOBALS['DIC']['ilDB']->doesCollationSupportMB4Strings();
    }

    public function sanitizeMB4StringIfNotSupported(string $query): string
    {
        return $GLOBALS['DIC']['ilDB']->sanitizeMB4StringIfNotSupported($query);
    }

    public static function getReservedWords(): array
    {
        return $GLOBALS['DIC']['ilDB']::getReservedWords();
    }

    public function initFromIniFile(?ilIniFile $ini = null): void
    {
        $GLOBALS['DIC']['ilDB']->initFromIniFile($ini);
    }

    public function connect(bool $return_false_on_error = false): ?bool
    {
        return $GLOBALS['DIC']['ilDB']->connect($return_false_on_error);
    }

    public function nextId(string $table_name): int
    {
        return $GLOBALS['DIC']['ilDB']->nextId($table_name);
    }

    public function createTable(
        string $table_name,
        array $fields,
        bool $drop_table = false,
        bool $ignore_erros = false
    ): bool {
        return $GLOBALS['DIC']['ilDB']->createTable($table_name, $fields, $drop_table, $ignore_erros);
    }

    public function addPrimaryKey(string $table_name, array $primary_keys): bool
    {
        return $GLOBALS['DIC']['ilDB']->addPrimaryKey($table_name, $primary_keys);
    }

    public function createSequence(string $table_name, int $start = 1): bool
    {
        return $GLOBALS['DIC']['ilDB']->createSequence($table_name, $start);
    }

    public function getSequenceName(string $table_name): string
    {
        return $GLOBALS['DIC']['ilDB']->getSequenceName($table_name);
    }

    public function tableExists(string $table_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->tableExists($table_name);
    }

    public function tableColumnExists(string $table_name, string $column_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->tableColumnExists($table_name, $column_name);
    }

    public function addTableColumn(string $table_name, string $column_name, array $attributes): bool
    {
        return $GLOBALS['DIC']['ilDB']->addTableColumn($table_name, $column_name, $attributes);
    }

    public function dropTable(string $table_name, bool $error_if_not_existing = true): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropTable($table_name, $error_if_not_existing);
    }

    public function renameTable(string $old_name, string $new_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->renameTable($old_name, $new_name);
    }

    public function query(string $query): ilDBStatement
    {
        return $GLOBALS['DIC']['ilDB']->query($query);
    }

    public function fetchAll(ilDBStatement $statement, int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC): array
    {
        return $GLOBALS['DIC']['ilDB']->fetchAll($statement, $fetch_mode);
    }

    public function dropSequence(string $table_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropSequence($table_name);
    }

    public function dropTableColumn(string $table_name, string $column_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropTableColumn($table_name, $column_name);
    }

    public function renameTableColumn(string $table_name, string $column_old_name, string $column_new_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->renameTableColumn($table_name, $column_old_name, $column_new_name);
    }

    public function insert(string $table_name, array $values): int
    {
        return $GLOBALS['DIC']['ilDB']->insert($table_name, $values);
    }

    public function fetchObject(ilDBStatement $query_result): ?stdClass
    {
        return $GLOBALS['DIC']['ilDB']->fetchObject($query_result);
    }

    public function update(string $table_name, array $values, array $where): int
    {
        return $GLOBALS['DIC']['ilDB']->update($table_name, $values, $where);
    }

    public function manipulate(string $query): int
    {
        return $GLOBALS['DIC']['ilDB']->manipulate($query);
    }

    public function fetchAssoc(ilDBStatement $statement): ?array
    {
        return $GLOBALS['DIC']['ilDB']->fetchAssoc($statement);
    }

    public function numRows(ilDBStatement $statement): int
    {
        return $GLOBALS['DIC']['ilDB']->numRows($statement);
    }

    public function quote($value, string $type): string
    {
        return $GLOBALS['DIC']['ilDB']->quote($value, $type);
    }

    public function addIndex(string $table_name, array $fields, string $index_name = '', bool $fulltext = false): bool
    {
        return $GLOBALS['DIC']['ilDB']->addIndex($table_name, $fields, $index_name, $fulltext);
    }

    public function indexExistsByFields(string $table_name, array $fields): bool
    {
        return $GLOBALS['DIC']['ilDB']->indexExistsByFields($table_name, $fields);
    }

    public function getDSN(): string
    {
        return $GLOBALS['DIC']['ilDB']->getDSN();
    }

    public function getDBType(): string
    {
        return $GLOBALS['DIC']['ilDB']->getDBType();
    }

    public function lockTables(array $tables): void
    {
        $GLOBALS['DIC']['ilDB']->lockTables($tables);
    }

    public function unlockTables(): void
    {
        $GLOBALS['DIC']['ilDB']->unlockTables();
    }

    public function in(string $field, array $values, bool $negate = false, string $type = ""): string
    {
        return $GLOBALS['DIC']['ilDB']->in($field, $values, $negate, $type);
    }

    public function queryF(string $query, array $types, array $values): ilDBStatement
    {
        return $GLOBALS['DIC']['ilDB']->queryF($query, $types, $values);
    }

    public function manipulateF(string $query, array $types, array $values): int
    {
        return $GLOBALS['DIC']['ilDB']->manipulateF($query, $types, $values);
    }

    public function useSlave(bool $bool): bool
    {
        return $GLOBALS['DIC']['ilDB']->useSlave($bool);
    }

    public function setLimit(int $limit, int $offset = 0): void
    {
        $GLOBALS['DIC']['ilDB']->setLimit($limit, $offset);
    }

    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true): string
    {
        return $GLOBALS['DIC']['ilDB']->like($column, $type, $value, $case_insensitive);
    }

    public function now(): string
    {
        return $GLOBALS['DIC']['ilDB']->now();
    }

    public function replace(string $table, array $primary_keys, array $other_columns): int
    {
        return $GLOBALS['DIC']['ilDB']->replace($table, $primary_keys, $other_columns);
    }

    public function equals(string $columns, $value, string $type, bool $emptyOrNull = false): string
    {
        return $GLOBALS['DIC']['ilDB']->equals($columns, $value, $type, $emptyOrNull);
    }

    public function setDBUser(string $user): void
    {
        $GLOBALS['DIC']['ilDB']->setDBUser($user);
    }

    public function setDBPort(int $port): void
    {
        $GLOBALS['DIC']['ilDB']->setDBPort($port);
    }

    public function setDBPassword(string $password): void
    {
        $GLOBALS['DIC']['ilDB']->setDBPassword($password);
    }

    public function setDBHost(string $host): void
    {
        $GLOBALS['DIC']['ilDB']->setDBHost($host);
    }

    public function upper(string $expression): string
    {
        return $GLOBALS['DIC']['ilDB']->upper($expression);
    }

    public function lower(string $expression): string
    {
        return $GLOBALS['DIC']['ilDB']->lower($expression);
    }

    public function substr(string $expression): string
    {
        return $GLOBALS['DIC']['ilDB']->substr($expression);
    }

    public function prepare(string $a_query, ?array $a_types = null, ?array $a_result_types = null): ilDBStatement
    {
        return $GLOBALS['DIC']['ilDB']->prepare($a_query, $a_types, $a_result_types);
    }

    public function prepareManip(string $a_query, ?array $a_types = null): ilDBStatement
    {
        return $GLOBALS['DIC']['ilDB']->prepareManip($a_query, $a_types);
    }

    public function enableResultBuffering(bool $a_status): void
    {
        $GLOBALS['DIC']['ilDB']->enableResultBuffering($a_status);
    }

    public function execute(ilDBStatement $stmt, array $data = []): ilDBStatement
    {
        return $GLOBALS['DIC']['ilDB']->execute($stmt, $data);
    }

    public function sequenceExists(string $sequence): bool
    {
        return $GLOBALS['DIC']['ilDB']->sequenceExists($sequence);
    }

    public function listSequences(): array
    {
        return $GLOBALS['DIC']['ilDB']->listSequences();
    }

    public function supports(string $feature): bool
    {
        return $GLOBALS['DIC']['ilDB']->supports($feature);
    }

    public function supportsFulltext(): bool
    {
        return $GLOBALS['DIC']['ilDB']->supportsFulltext();
    }

    public function supportsSlave(): bool
    {
        return $GLOBALS['DIC']['ilDB']->supportsSlave();
    }

    public function supportsTransactions(): bool
    {
        return $GLOBALS['DIC']['ilDB']->supportsTransaction();
    }

    public function listTables(): array
    {
        return $GLOBALS['DIC']['ilDB']->listTables();
    }

    public function loadModule(string $module)
    {
        return $GLOBALS['DIC']['ilDB']->loadModule($module);
    }

    public function getAllowedAttributes(): array
    {
        return $GLOBALS['DIC']['ilDB']->getAllowedAttributes();
    }

    public function concat(array $values, bool $allow_null = true): string
    {
        return $GLOBALS['DIC']['ilDB']->concat($values, $allow_null);
    }

    public function locate(string $needle, string $string, int $start_pos = 1): string
    {
        return $GLOBALS['DIC']['ilDB']->locate($needle, $string, $start_pos);
    }

    public function quoteIdentifier(string $identifier, bool $check_option = false): string
    {
        return $GLOBALS['DIC']['ilDB']->quoteIdentifier($identifier, $check_option);
    }

    public function modifyTableColumn(string $table, string $column, array $attributes): bool
    {
        return $GLOBALS['DIC']['ilDB']->modifyTableColumn($table, $column, $attributes);
    }

    public function free(ilDBStatement $a_st): void
    {
        $GLOBALS['DIC']['ilDB']->free($a_st);
    }

    public function checkTableName(string $a_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->checkTableName($a_name);
    }

    public static function isReservedWord(string $a_word): bool
    {
        return $GLOBALS['DIC']['ilDB']->isReservedWord($a_word);
    }

    public function beginTransaction(): bool
    {
        return $GLOBALS['DIC']['ilDB']->beginTransaction();
    }

    public function commit(): bool
    {
        return $GLOBALS['DIC']['ilDB']->commit();
    }

    public function rollback(): bool
    {
        return $GLOBALS['DIC']['ilDB']->rollback();
    }

    public function constraintName(string $a_table, string $a_constraint): string
    {
        return $GLOBALS['DIC']['ilDB']->constraintName($a_table, $a_constraint);
    }

    public function dropIndex(string $a_table, string $a_name = "i1"): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropIndex($a_table, $a_name);
    }

    public function createDatabase(string $a_name, string $a_charset = "utf8", string $a_collation = ""): bool
    {
        return $GLOBALS['DIC']['ilDB']->createDatabase($a_name, $a_charset, $a_collation);
    }

    public function dropIndexByFields(string $table_name, array $afields): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropIndexByFields($table_name, $afields);
    }

    public function getPrimaryKeyIdentifier(): string
    {
        return $GLOBALS['DIC']['ilDB']->getPrimaryKeyIdentifier();
    }

    public function addFulltextIndex(string $table_name, array $afields, string $a_name = 'in'): bool
    {
        return $GLOBALS['DIC']['ilDB']->addFulltextIndex($table_name, $afields, $a_name);
    }

    public function dropFulltextIndex(string $a_table, string $a_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropFulltextIndex($a_table, $a_name);
    }

    public function isFulltextIndex(string $a_table, string $a_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->isFulltextIndex($a_table, $a_name);
    }

    public function setStorageEngine(string $storage_engine): void
    {
        $GLOBALS['DIC']['ilDB']->setStorageEngine($storage_engine);
    }

    public function getStorageEngine(): string
    {
        return $GLOBALS['DIC']['ilDB']->getStorageEngine();
    }

    public function buildAtomQuery(): ilAtomQuery
    {
        return $GLOBALS['DIC']['ilDB']->buildAtomQuery();
    }

    public function groupConcat(string $a_field_name, string $a_seperator = ",", ?string $a_order = null): string
    {
        return $GLOBALS['DIC']['ilDB']->groupConcat($a_field_name, $a_seperator, $a_order);
    }

    public function cast(string $a_field_name, string $a_dest_type): string
    {
        return $GLOBALS['DIC']['ilDB']->cast($a_field_name, $a_dest_type);
    }

    public function addForeignKey(
        string $foreign_key_name,
        array $field_names,
        string $table_name,
        array $reference_field_names,
        string $reference_table,
        ?ForeignKeyConstraints $on_update = null,
        ?ForeignKeyConstraints $on_delete = null
    ): bool {
        return $GLOBALS['DIC']['ilDB']->addForeignKey(
            $foreign_key_name,
            $field_names,
            $table_name,
            $reference_field_names,
            $reference_table,
            $on_update,
            $on_delete
        );
    }

    public function dropForeignKey(string $foreign_key_name, string $table_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->dropForeignKey($foreign_key_name, $table_name);
    }

    public function foreignKeyExists(string $foreign_key_name, string $table_name): bool
    {
        return $GLOBALS['DIC']['ilDB']->foreignKeyExists($foreign_key_name, $table_name);
    }

    public function buildIntegrityAnalyser(): Integrity
    {
        return $GLOBALS['DIC']['ilDB']->buildIntegrityAnalyser();
    }

    public function primaryExistsByFields(string $table_name, array $fields): bool
    {
        return $GLOBALS['DIC']['ilDB']->primaryExistsByFields($table_name, $fields);
    }

}
