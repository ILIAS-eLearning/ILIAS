<?php declare(strict_types=1);

/**
 * Interface ilDBManager
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilDBManager
{

    /**
     * @return string[]
     */
    public function listTables(?string $database = null) : array;

    /**
     * @return string[]
     */
    public function listSequences(?string $database = null) : array;

    public function createConstraint(string $table, string $name, array $definition) : bool;

    /**
     * @return string[]
     */
    public function listTableFields(string $table) : array;

    /**
     * @return string[]
     */
    public function listTableConstraints(string $table) : array;

    public function createSequence(string $seq_name, int $start = 1, array $options = []) : bool;

    /**
     * @return string[]
     */
    public function listTableIndexes(string $table) : array;

    public function alterTable(string $name, array $changes, bool $check) : bool;

    public function createIndex(string $table, string $name, array $definition) : bool;

    public function dropIndex(string $table, string $name) : bool;

    public function dropSequence(string $seq_name) : bool;

    public function dropConstraint(string $table, string $name, bool $primary = false) : bool;

    /**
     * @param $name string
     */
    public function dropTable(string $name) : bool;
}
