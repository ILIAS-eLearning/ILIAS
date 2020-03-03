<?php
require_once 'Services/Database/interfaces/interface.ilQueryUtils.php';

/**
 * Class ilQueryUtils
 *
 */
abstract class ilQueryUtils implements ilQueryUtilsInterface
{

    /**
     * @var \ilDBPdo
     */
    protected $db_instance;


    /**
     * ilMySQLQueryUtils constructor.
     *
     * @param \ilDBInterface $ilDBInterface
     */
    public function __construct(ilDBInterface $ilDBInterface)
    {
        $this->db_instance = $ilDBInterface;
    }


    /**
     * @param string $field
     * @param string[] $values
     * @param bool $negate
     * @param string $type
     * @return string
     */
    abstract public function in($field, $values, $negate = false, $type = "");


    /**
     * @param mixed $value
     * @param null $type
     * @return string
     */
    abstract public function quote($value, $type = null);


    /**
     * @param array $values
     * @param bool $allow_null
     * @return string
     */
    abstract public function concat(array $values, $allow_null = true);


    /**
     * @param $a_needle
     * @param $a_string
     * @param int $a_start_pos
     * @return string
     */
    abstract public function locate($a_needle, $a_string, $a_start_pos = 1);


    /**
     * @param \ilPDOStatement $statement
     * @return bool
     */
    abstract public function free(ilPDOStatement $statement);


    /**
     * @param $identifier
     * @return string
     */
    abstract public function quoteIdentifier($identifier);


    /**
     * @param $name
     * @param $fields
     * @param array $options
     * @return string
     * @throws \ilDatabaseException
     */
    abstract public function createTable($name, $fields, $options = array());


    /**
     * @param $column
     * @param $type
     * @param string $value
     * @param bool $case_insensitive
     * @return string
     * @throws \ilDatabaseException
     */
    abstract public function like($column, $type, $value = "?", $case_insensitive = true);


    /**
     * @return string
     */
    abstract public function now();


    /**
     * @param array $tables
     * @return string
     */
    abstract public function lock(array $tables);


    /**
     * @return string
     */
    abstract public function unlock();


    /**
     * @param $a_name
     * @param string $a_charset
     * @param string $a_collation
     * @return mixed
     */
    abstract public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "");
    

    /**
     * @inheritdoc
     */
    abstract public function groupConcat($a_field_name, $a_seperator = ",", $a_order = null);

    
    /**
     * @inheritdoc
     */
    abstract public function cast($a_field_name, $a_dest_type);
}
