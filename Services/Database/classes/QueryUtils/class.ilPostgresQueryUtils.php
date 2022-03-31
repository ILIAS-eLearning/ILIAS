<?php declare(strict_types=1);

/**
 * Class ilPostgresQueryUtils
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPostgresQueryUtils extends ilQueryUtils
{

    /**
     * @throws \ilDatabaseException
     */
    public function createTable(string $name, array $fields, array $options = []) : string
    {
        if ($name === '') {
            throw new ilDatabaseException('no valid table name specified');
        }
        if (empty($fields)) {
            throw new ilDatabaseException('no fields specified for table "' . $name . '"');
        }
        $query_fields_array = array();
        $fd = $this->db_instance->getFieldDefinition();
        if ($fd !== null) {
            foreach ($fields as $field_name => $field) {
                $query_fields_array[] = $this->db_instance->getFieldDefinition()->getDeclaration(
                    $field['type'],
                    $field_name,
                    $field
                );
            }
        }

        $query_fields = implode(', ', $query_fields_array);

        if (!empty($options['primary'])) {
            $query_fields .= ', PRIMARY KEY (' . implode(', ', array_keys($options['primary'])) . ')';
        }

        $query = "CREATE  TABLE $name ($query_fields)";

        $options_strings = array();

        if (!empty($options['comment'])) {
            $options_strings['comment'] = 'COMMENT = ' . $this->quote($options['comment'], 'text');
        }

        if (!empty($options['charset'])) {
            $options_strings['charset'] = 'DEFAULT CHARACTER SET ' . $options['charset'];
            if (!empty($options['collate'])) {
                $options_strings['charset'] .= ' COLLATE ' . $options['collate'];
            }
        }

        $type = false;
        if (!empty($options['type'])) {
            $type = $options['type'];
        }
        if ($type) {
            $options_strings[] = "ENGINE = $type";
        }

        if (!empty($options_strings)) {
            $query .= ' ' . implode(' ', $options_strings);
        }

        return $query;
    }

    /**
     * @param string[] $values
     */
    public function in(string $field, array $values, bool $negate = false, string $type = "") : string
    {
        if (count($values) === 0) {
            // BEGIN fixed mantis #0014191:
            //return " 1=2 ";		// return a false statement on empty array
            return $negate ? ' 1=1 ' : ' 1=2 ';
            // END fixed mantis #0014191:
        }
        if ($type === "") {        // untyped: used ? for prepare/execute
            $str = $field . (($negate) ? " NOT" : "") . " IN (?" . str_repeat(",?", count($values) - 1) . ")";
        } else {                    // typed, use values for query/manipulate
            $str = $field . (($negate) ? " NOT" : "") . " IN (";
            $sep = "";
            foreach ($values as $v) {
                $str .= $sep . $this->quote($v, $type);
                $sep = ",";
            }
            $str .= ")";
        }

        return $str;
    }

    /**
     * @param mixed $value
     */
    public function quote($value, ?string $type = null) : string
    {
        return $this->db_instance->quote($value, $type);
    }

    public function concat(array $values, bool $allow_null = true) : string
    {
        if (count($values) === 0) {
            return ' ';
        }

        $concat = ' CONCAT(';
        $first = true;
        foreach ($values as $field_info) {
            $val = $field_info[0];

            if (!$first) {
                $concat .= ',';
            }

            if ($allow_null) {
                $concat .= 'COALESCE(';
            }
            $concat .= $val;

            if ($allow_null) {
                $concat .= ",''";
                $concat .= ')';
            }

            $first = false;
        }

        return $concat . ') ';
    }

    /**
     * @param $a_needle
     * @param $a_string
     */
    public function locate($a_needle, $a_string, int $a_start_pos = 1) : string
    {
        $locate = ' STRPOS(SUBSTR(';
        $locate .= $a_string;
        $locate .= ', ';
        $locate .= $a_start_pos;
        $locate .= '), ';
        $locate .= $a_needle;
        $locate .= ') + ';
        $locate .= --$a_start_pos;

        return $locate;
    }

    public function free(ilPDOStatement $statement) : bool
    {
        $statement->closeCursor();

        return true;
    }

    public function quoteIdentifier(string $identifier) : string
    {
        return $this->db_instance->quoteIdentifier($identifier);
    }

    /**
     * @param $column
     * @param $type
     * @return string|void
     * @throws \ilDatabaseException
     */
    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true) : string
    {
        if (!in_array($type, array(
            ilDBConstants::T_TEXT,
            ilDBConstants::T_CLOB,
            "blob",
        ), true)
        ) {
            throw new ilDatabaseException("Like: Invalid column type '" . $type . "'.");
        }
        if ($value === "?") {
            if ($case_insensitive) {
                return "UPPER(" . $column . ") LIKE(UPPER(?))";
            }

            return $column . " LIKE(?)";
        }

        if ($case_insensitive) {
            // Always quote as text
            return " UPPER(" . $column . ") LIKE(UPPER(" . $this->quote($value, 'text') . "))";
        }

        // Always quote as text
        return " " . $column . " LIKE(" . $this->quote($value, 'text') . ")";
    }

    public function now() : string
    {
        return "now()";
    }

    public function lock(array $tables) : string
    {
        $lock = 'LOCK TABLES ';

        $counter = 0;
        foreach ($tables as $table) {
            if ($counter++ !== 0) {
                $lock .= ', ';
            }

            if (isset($table['sequence']) && $table['sequence']) {
                $table_name = $this->db_instance->getSequenceName($table['name']);
            } else {
                $table_name = $table['name'];
            }

            $lock .= ($table_name . ' ');

            if ($table['alias']) {
                $lock .= ($table['alias'] . ' ');
            }

            switch ($table['type']) {
                case ilDBConstants::LOCK_READ:
                    $lock .= ' READ ';
                    break;

                case ilDBConstants::LOCK_WRITE:
                    $lock .= ' WRITE ';
                    break;
            }
        }

        return $lock;
    }

    public function unlock() : string
    {
        return 'UNLOCK TABLES';
    }

    public function createDatabase($a_name, string $a_charset = "utf8", string $a_collation = "") : string
    {
        if ($a_collation !== "") {
            $sql = "CREATE DATABASE " . $a_name . " CHARACTER SET " . $a_charset . " COLLATE " . $a_collation;
        } else {
            $sql = "CREATE DATABASE " . $a_name . " CHARACTER SET " . $a_charset;
        }

        return $sql;
    }

    public function groupConcat(string $a_field_name, string $a_seperator = ",", string $a_order = null) : string
    {
        if ($a_order === null) {
            $sql = "STRING_AGG(" . $a_field_name . ", " . $this->quote($a_seperator, "text") . ")";
        } else {
            $sql = "STRING_AGG(" . $a_field_name . ", " . $this->quote(
                $a_seperator,
                "text"
            ) . " ORDER BY " . $a_order . ")";
        }
        return $sql;
    }

    /**
     * @inheritdoc
     */
    public function cast(string $a_field_name, $a_dest_type) : string
    {
        $fd = $this->db_instance->getFieldDefinition();
        if ($fd !== null) {
            return "CAST($a_field_name AS " . $this->db_instance->getFieldDefinition()->getTypeDeclaration(array("type" => $a_dest_type)) . ")";
        }
        return "";
    }
}
