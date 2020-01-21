<?php

/**
 * Interface ilQueryUtilsInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilQueryUtilsInterface
{

    /**
     * @param string $field
     * @param string[] $values
     * @param bool $negate
     * @param string $type
     * @return string
     */
    public function in($field, $values, $negate = false, $type = "");


    /**
     * @param mixed $value
     * @param null $type
     * @return string
     */
    public function quote($value, $type = null);


    /**
     * @param array $values
     * @param bool $allow_null
     * @return string
     */
    public function concat(array $values, $allow_null = true);


    /**
     * @param $a_needle
     * @param $a_string
     * @param int $a_start_pos
     * @return string
     */
    public function locate($a_needle, $a_string, $a_start_pos = 1);


    /**
     * @param \ilPDOStatement $statement
     * @return bool
     */
    public function free(ilPDOStatement $statement);


    /**
     * @param $identifier
     * @return string
     */
    public function quoteIdentifier($identifier);


    /**
     * @param $name
     * @param $fields
     * @param array $options
     * @return string
     * @throws \ilDatabaseException
     */
    public function createTable($name, $fields, $options = array());


    /**
     * @param $column
     * @param $type
     * @param string $value
     * @param bool $case_insensitive
     * @return string
     * @throws \ilDatabaseException
     */
    public function like($column, $type, $value = "?", $case_insensitive = true);


    /**
     * @return string
     */
    public function now();


    /**
     * @param array $tables
     * @return string
     */
    public function lock(array $tables);


    /**
     * @return string
     */
    public function unlock();


    /**
     * @param $a_name
     * @param string $a_charset
     * @param string $a_collation
     * @return mixed
     */
    public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "");
    
    
    /**
     *
     * @param string $a_field_name
     * @param string $a_seperator
     * @param string $a_order
     * @return string
     */
    public function groupConcat($a_field_name, $a_seperator = ",", $a_order = null);


    /**
     * @param string $a_field_name
     * @param mixed $a_dest_type
     * @return string
     */
    public function cast($a_field_name, $a_dest_type);
}
