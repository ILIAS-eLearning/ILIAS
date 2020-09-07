<?php
require_once('./Services/Database/classes/QueryUtils/class.ilQueryUtils.php');

/**
 * Class ilPostgresQueryUtils
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPostgresQueryUtils extends ilQueryUtils
{

    /**
     * @param $name
     * @param $fields
     * @param array $options
     * @return string
     * @throws \ilDatabaseException
     */
    public function createTable($name, $fields, $options = array())
    {
        if (!$name) {
            throw new ilDatabaseException('no valid table name specified');
        }
        if (empty($fields)) {
            throw new ilDatabaseException('no fields specified for table "' . $name . '"');
        }
        $query_fields_array = array();
        foreach ($fields as $field_name => $field) {
            $query_fields_array[] = $this->db_instance->getFieldDefinition()->getDeclaration($field['type'], $field_name, $field);
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
     * @param string $field
     * @param string[] $values
     * @param bool $negate
     * @param string $type
     * @return string
     */
    public function in($field, $values, $negate = false, $type = "")
    {
        if (count($values) == 0) {
            // BEGIN fixed mantis #0014191:
            //return " 1=2 ";		// return a false statement on empty array
            return $negate ? ' 1=1 ' : ' 1=2 ';
            // END fixed mantis #0014191:
        }
        if ($type == "") {        // untyped: used ? for prepare/execute
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
     * @param null $type
     * @return string
     */
    public function quote($value, $type = null)
    {
        return $this->db_instance->quote($value, $type);
    }


    /**
     * @param array $values
     * @param bool $allow_null
     * @return string
     */
    public function concat(array $values, $allow_null = true)
    {
        if (!count($values)) {
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
        $concat .= ') ';

        return $concat;
    }


    /**
     * @param $a_needle
     * @param $a_string
     * @param int $a_start_pos
     * @return string
     */
    public function locate($a_needle, $a_string, $a_start_pos = 1)
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


    /**
     * @param \ilPDOStatement $statement
     * @return bool
     */
    public function free(ilPDOStatement $statement)
    {
        $statement->closeCursor();

        return true;
    }


    /**
     * @param $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->db_instance->quoteIdentifier($identifier);
    }


    /**
     * @param $column
     * @param $type
     * @param string $value
     * @param bool $case_insensitive
     * @return string
     * @throws \ilDatabaseException
     */
    public function like($column, $type, $value = "?", $case_insensitive = true)
    {
        if (!in_array($type, array(
            ilDBConstants::T_TEXT,
            ilDBConstants::T_CLOB,
            "blob",
        ))
        ) {
            throw new ilDatabaseException("Like: Invalid column type '" . $type . "'.");
        }
        if ($value == "?") {
            if ($case_insensitive) {
                return "UPPER(" . $column . ") LIKE(UPPER(?))";
            } else {
                return $column . " LIKE(?)";
            }
        } else {
            if ($case_insensitive) {
                // Always quote as text
                return " UPPER(" . $column . ") LIKE(UPPER(" . $this->quote($value, 'text') . "))";
            } else {
                // Always quote as text
                return " " . $column . " LIKE(" . $this->quote($value, 'text') . ")";
            }
        }
    }


    /**
     * @return string
     */
    public function now()
    {
        return "now()";
    }


    /**
     * @param array $tables
     * @return string
     */
    public function lock(array $tables)
    {
        $lock = 'LOCK TABLES ';

        $counter = 0;
        foreach ($tables as $table) {
            if ($counter++) {
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


    /**
     * @return string
     */
    public function unlock()
    {
        return 'UNLOCK TABLES';
    }


    /**
     * @param $a_name
     * @param string $a_charset
     * @param string $a_collation
     * @return mixed
     */
    public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "")
    {
        if ($a_collation != "") {
            $sql = "CREATE DATABASE " . $a_name . " CHARACTER SET " . $a_charset . " COLLATE " . $a_collation;
        } else {
            $sql = "CREATE DATABASE " . $a_name . " CHARACTER SET " . $a_charset;
        }

        return $sql;
    }


    /**
     *
     * @param string $a_field_name
     * @param string $a_seperator
     * @param string $a_order
     * @return string
     */
    public function groupConcat($a_field_name, $a_seperator = ",", $a_order = null)
    {
        if ($a_order === null) {
            $sql = "STRING_AGG(" . $a_field_name . ", " . $this->quote($a_seperator, "text") . ")";
        } else {
            $sql = "STRING_AGG(" . $a_field_name . ", " . $this->quote($a_seperator, "text") . " ORDER BY " . $a_order . ")";
        }
        return $sql;
    }
    

    /**
     * @inheritdoc
     */
    public function cast($a_field_name, $a_dest_type)
    {
        return "CAST({$a_field_name} AS " . $this->db_instance->getFieldDefinition()->getTypeDeclaration(array("type" => $a_dest_type)) . ")";
    }
}
