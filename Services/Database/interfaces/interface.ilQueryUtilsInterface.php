<?php declare(strict_types=1);

/**
 * Interface ilQueryUtilsInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilQueryUtilsInterface
{

    /**
     * @param string[] $values
     */
    public function in(string $field, array $values, bool $negate = false, string $type = "") : string;


    /**
     * @param mixed $value
     */
    public function quote($value, ?string $type = null) : string;


    public function concat(array $values, bool $allow_null = true) : string;


    /**
     * @param $a_needle
     * @param $a_string
     */
    public function locate($a_needle, $a_string, int $a_start_pos = 1) : string;


    public function free(ilPDOStatement $statement) : bool;


    public function quoteIdentifier(string $identifier) : string;


    /**
     * @throws \ilDatabaseException
     */
    public function createTable(string $name, array $fields, array $options = []) : string;


    /**
     * @throws \ilDatabaseException
     */
    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true) : string;


    public function now() : string;


    public function lock(array $tables) : string;


    public function unlock() : string;


    public function createDatabase(string $name, string $charset = "utf8", string $collation = "") : string;


    public function groupConcat(string $a_field_name, string $a_seperator = ",", string $a_order = null) : string;


    /**
     * @param mixed $a_dest_type
     */
    public function cast(string $a_field_name, $a_dest_type) : string;
}
