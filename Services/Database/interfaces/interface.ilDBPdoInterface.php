<?php declare(strict_types=1);

/**
 * Interface ilDBPdoInterface
 */
interface ilDBPdoInterface extends ilDBInterface
{
    public function getServerVersion(bool $native = false) : int;

    public function queryCol(string $query, int $type = ilDBConstants::FETCHMODE_DEFAULT, int $colnum = 0) : array;

    public function queryRow(
        string $query,
        ?array $types = null,
        int $fetchmode = ilDBConstants::FETCHMODE_DEFAULT
    ) : array;

    public function escape(string $value, bool $escape_wildcards = false) : string;

    public function escapePattern(string $text) : string;

    /**
     * @return array of failed tables
     */
    public function migrateAllTablesToEngine(string $engine = ilDBConstants::MYSQL_ENGINE_INNODB) : array;

    public function supportsEngineMigration() : bool;

    /**
     * @return array of failed tables
     */
    public function migrateAllTablesToCollation(string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4) : array;

    public function supportsCollationMigration() : bool;

    public function addUniqueConstraint(string $table, array $fields, string $name = "con") : bool;

    public function dropUniqueConstraint(string $table, string $name = "con") : bool;

    public function dropUniqueConstraintByFields(string $table, array $fields) : bool;

    public function checkIndexName(string $name) : bool;

    public function getLastInsertId() : int;

    public function uniqueConstraintExists(string $table, array $fields) : bool;

    public function dropPrimaryKey(string $table_name) : bool;

    /**
     * @param ilDBStatement[] $stmt
     * @return string[]
     */
    public function executeMultiple(array $stmt, array $data) : array;

    public function fromUnixtime(string $expr, bool $to_text = true) : string;

    public function unixTimestamp() : string;

    /**
     * returns the Version of the Database (e.g. MySQL 5.6)
     */
    public function getDBVersion() : string;
}
