<?php declare(strict_types=1);

/**
 * Class ilQueryUtils
 */
abstract class ilQueryUtils implements ilQueryUtilsInterface
{
    protected \ilDBInterface $db_instance;

    /**
     * ilMySQLQueryUtils constructor.
     */
    public function __construct(ilDBInterface $ilDBInterface)
    {
        $this->db_instance = $ilDBInterface;
    }

    /**
     * @param string[] $values
     */
    abstract public function in(string $field, array $values, bool $negate = false, string $type = "") : string;

    /**
     * @param mixed $value
     */
    abstract public function quote($value, ?string $type = null) : string;

    abstract public function concat(array $values, bool $allow_null = true) : string;

    abstract public function locate($a_needle, $a_string, int $a_start_pos = 1) : string;

    abstract public function free(ilPDOStatement $statement) : bool;

    abstract public function quoteIdentifier(string $identifier) : string;

    /**
     * @throws \ilDatabaseException
     */
    abstract public function createTable(string $name, array $fields, array $options = []) : string;

    /**
     * @throws \ilDatabaseException
     */
    abstract public function like(
        string $column,
        string $type,
        string $value = "?",
        bool $case_insensitive = true
    ) : string;

    abstract public function now() : string;

    abstract public function lock(array $tables) : string;

    abstract public function unlock() : string;

    abstract public function createDatabase(string $name, string $charset = "utf8", string $collation = "") : string;

    abstract public function groupConcat(string $a_field_name, string $a_seperator = ",", string $a_order = null) : string;

    /**
     * @inheritdoc
     */
    abstract public function cast(string $a_field_name, $a_dest_type) : string;
}
