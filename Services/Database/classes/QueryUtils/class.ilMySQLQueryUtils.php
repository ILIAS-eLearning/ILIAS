<?php declare(strict_types=1);

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
 
/**
 * Class ilMySQLQueryUtils
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMySQLQueryUtils extends ilQueryUtils
{

    /**
     * @param string[] $values
     */
    public function in(string $field, array $values, bool $negate = false, string $type = "") : string
    {
        if (!is_array($values) || count($values) === 0) {
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

    public function locate(string $a_needle, string $a_string, int $a_start_pos = 1) : string
    {
        $locate = ' LOCATE( ';
        $locate .= $a_needle;
        $locate .= ',';
        $locate .= $a_string;
        $locate .= ',';
        $locate .= $a_start_pos;
        $locate .= ') ';

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
        $query_fields_array = [];
        $fd = $this->db_instance->getFieldDefinition();
        if ($fd !== null) {
            foreach ($fields as $field_name => $field) {
                $query_fields_array[] = $fd->getDeclaration(
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
        return "NOW()";
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

            if ($table['alias'] ?? null) {
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

    public function createDatabase(string $name, string $charset = "utf8", string $collation = "") : string
    {
        if ($collation !== "") {
            $sql = "CREATE DATABASE `" . $name . "` CHARACTER SET " . $charset . " COLLATE " . $collation;
        } else {
            $sql = "CREATE DATABASE `" . $name . "` CHARACTER SET " . $charset;
        }

        return $sql;
    }

    public function groupConcat(string $field_name, string $seperator = ",", string $order = null) : string
    {
        if ($order === null) {
            $sql = "GROUP_CONCAT(" . $field_name . " SEPARATOR " . $this->quote($seperator, "text") . ")";
        } else {
            $sql = "GROUP_CONCAT(" . $field_name . " ORDER BY " . $order . " SEPARATOR " . $this->quote(
                $seperator,
                "text"
            ) . ")";
        }
        return $sql;
    }

    /**
     * @inheritdoc
     */
    public function cast(string $a_field_name, $a_dest_type) : string
    {
        return $a_field_name;
    }
}
