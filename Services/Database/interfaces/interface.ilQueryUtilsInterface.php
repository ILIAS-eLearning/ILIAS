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
     * @param mixed       $value
     * @param string|null $type
     */
    public function quote($value, string $type = null) : string;


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
     * @param $column
     * @param $type
     * @throws \ilDatabaseException
     */
    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true):string;


    /**
     * @return string
     */
    public function now();


    /**
     * @return string
     */
    public function lock(array $tables);


    /**
     * @return string
     */
    public function unlock();


    /**
     * @param $a_name
     * @return mixed
     */
    public function createDatabase($a_name, string $a_charset = "utf8", string $a_collation = "");


    /**
     *
     * @return string
     */
    public function groupConcat(string $a_field_name, string $a_seperator = ",", string $a_order = null);


    /**
     * @param mixed $a_dest_type
     * @return string
     */
    public function cast(string $a_field_name, $a_dest_type);
}
