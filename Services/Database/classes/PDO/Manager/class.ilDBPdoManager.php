<?php
require_once('./Services/Database/interfaces/interface.ilDBManager.php');

/**
 * Class ilDBPdoManager
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoManager implements ilDBManager, ilDBPdoManagerInterface
{

    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var ilDBPdo
     */
    protected $db_instance;


    /**
     * ilDBPdoManager constructor.
     *
     * @param \PDO $pdo
     * @param \ilDBPdo $db_instance
     */
    public function __construct(\PDO $pdo, ilDBPdo $db_instance)
    {
        $this->pdo = $pdo;
        $this->db_instance = $db_instance;
    }


    /**
     * @var ilMySQLQueryUtils
     */
    protected $query_utils;


    /**
     * @return \ilMySQLQueryUtils
     */
    public function getQueryUtils()
    {
        if (!$this->query_utils) {
            $this->query_utils = new ilMySQLQueryUtils($this->db_instance);
        }

        return $this->query_utils;
    }


    /**
     * @return \ilDBPdo
     */
    public function getDBInstance()
    {
        return $this->db_instance;
    }


    /**
     * @param null $database
     * @return array
     */
    public function listTables($database = null)
    {
        $str = 'SHOW TABLES ' . ($database ? ' IN ' . $database : '');
        $r = $this->pdo->query($str);
        $tables = array();

        $sequence_identifier = "_seq";
        while ($data = $r->fetchColumn()) {
            if (!preg_match("/{$sequence_identifier}$/um", $data)) {
                $tables[] = $data;
            }
        }

        return $tables;
    }


    /**
     * @param $sqn
     * @param bool $check
     * @return bool|mixed
     */
    protected function fixSequenceName($sqn, $check = false)
    {
        $seq_pattern = '/^' . preg_replace('/%s/', '([a-z0-9_]+)', ilDBConstants::SEQUENCE_FORMAT) . '$/i';
        $seq_name = preg_replace($seq_pattern, '\\1', $sqn);
        if ($seq_name && !strcasecmp($sqn, $this->db_instance->getSequenceName($seq_name))) {
            return $seq_name;
        }
        if ($check) {
            return false;
        }

        return $sqn;
    }


    /**
     * @param null $database
     * @return array
     */
    public function listSequences($database = null)
    {
        $query = "SHOW TABLES";
        if (!is_null($database)) {
            $query .= " FROM $database";
        }

        $res = $this->db_instance->query($query);

        $result = array();
        while ($table_name = $this->db_instance->fetchAssoc($res)) {
            if ($sqn = $this->fixSequenceName(reset($table_name), true)) {
                $result[] = $sqn;
            }
        }
        if ($this->db_instance->options['portability']) {
            $result = array_map(($this->db_instance->options['field_case'] == CASE_LOWER ? 'strtolower' : 'strtoupper'), $result);
        }

        return $result;
    }


    /**
     * @param $table
     * @param $name
     * @param $definition
     * @return mixed
     * @throws \ilDatabaseException
     */
    public function createConstraint($table, $name, $definition)
    {
        $db = $this->db_instance;

        $table = $db->quoteIdentifier($table, true);
        $name = $db->quoteIdentifier($db->getIndexName($name), true);
        $query = "ALTER TABLE $table ADD CONSTRAINT $name";
        if (!empty($definition['primary'])) {
            $query .= ' PRIMARY KEY';
        } elseif (!empty($definition['unique'])) {
            $query .= ' UNIQUE';
        }
        $fields = array();
        foreach (array_keys($definition['fields']) as $field) {
            $fields[] = $db->quoteIdentifier($field, true);
        }
        $query .= ' (' . implode(', ', $fields) . ')';

        return $this->pdo->exec($query);
    }


    /**
     * @param $seq_name
     * @param int $start
     * @param array $options
     * @return bool
     */
    public function createSequence($seq_name, $start = 1, $options = array())
    {
        $sequence_name = $this->db_instance->quoteIdentifier($this->db_instance->getSequenceName($seq_name));
        $seqcol_name = $this->db_instance->quoteIdentifier(ilDBConstants::SEQUENCE_COLUMNS_NAME);

        $options_strings = array();

        if (!empty($options['comment'])) {
            $options_strings['comment'] = 'COMMENT = ' . $this->db_instance->quote($options['comment'], 'text');
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

        $query = "CREATE TABLE $sequence_name ($seqcol_name INT NOT NULL AUTO_INCREMENT, PRIMARY KEY ($seqcol_name))";

        if (!empty($options_strings)) {
            $query .= ' ' . implode(' ', $options_strings);
        }
        $this->pdo->exec($query);

        if ($start == 1) {
            return true;
        }

        $query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (" . ($start - 1) . ')';
        $this->pdo->exec($query);

        return true;
    }


    /**
     * @param $name
     * @param $changes
     * @param $check
     * @return bool
     * @throws \ilDatabaseException
     */
    public function alterTable($name, $changes, $check)
    {
        $db = $this->db_instance;

        foreach ($changes as $change_name => $change) {
            switch ($change_name) {
                case 'add':
                case 'remove':
                case 'change':
                case 'rename':
                case 'name':
                    break;
                default:
                    throw new ilDatabaseException('change type "' . $change_name . '" not yet supported');
            }
        }

        if ($check) {
            return true;
        }

        $query = '';
        if (!empty($changes['name'])) {
            $change_name = $db->quoteIdentifier($changes['name']);
            $query .= 'RENAME TO ' . $change_name;
        }

        if (!empty($changes['add']) && is_array($changes['add'])) {
            foreach ($changes['add'] as $field_name => $field) {
                if ($query) {
                    $query .= ', ';
                }
                $query .= 'ADD ' . $db->getFieldDefinition()->getDeclaration($field['type'], $field_name, $field);
            }
        }

        if (!empty($changes['remove']) && is_array($changes['remove'])) {
            foreach ($changes['remove'] as $field_name => $field) {
                if ($query) {
                    $query .= ', ';
                }
                $field_name = $db->quoteIdentifier($field_name);
                $query .= 'DROP ' . $field_name;
            }
        }

        $rename = array();
        if (!empty($changes['rename']) && is_array($changes['rename'])) {
            foreach ($changes['rename'] as $field_name => $field) {
                $rename[$field['name']] = $field_name;
            }
        }

        if (!empty($changes['change']) && is_array($changes['change'])) {
            foreach ($changes['change'] as $field_name => $field) {
                if ($query) {
                    $query .= ', ';
                }
                if (isset($rename[$field_name])) {
                    $old_field_name = $rename[$field_name];
                    unset($rename[$field_name]);
                } else {
                    $old_field_name = $field_name;
                }
                $old_field_name = $db->quoteIdentifier($old_field_name);
                $query .= "CHANGE $old_field_name " . $this->db_instance->getFieldDefinition()
                                                                        ->getDeclaration($field['definition']['type'], $field_name, $field['definition']);
            }
        }

        if (!empty($rename) && is_array($rename)) {
            foreach ($rename as $rename_name => $renamed_field) {
                if ($query) {
                    $query .= ', ';
                }
                $field = $changes['rename'][$renamed_field];
                $renamed_field = $db->quoteIdentifier($renamed_field);
                $query .= 'CHANGE ' . $renamed_field . ' ' . $this->db_instance->getFieldDefinition()
                                                                               ->getDeclaration($field['definition']['type'], $field['name'], $field['definition']);
            }
        }

        if (!$query) {
            return true;
        }

        $name = $db->quoteIdentifier($name, true);

        $statement = "ALTER TABLE $name $query";

        return $this->pdo->exec($statement);
    }


    /**
     * @param $name
     * @param $fields
     * @param array $options
     * @return int
     */
    public function createTable($name, $fields, $options = array())
    {
        $options['type'] = $this->db_instance->getStorageEngine();

        return $this->pdo->exec($this->getQueryUtils()->createTable($name, $fields, $options));
    }





    //
    // ilDBPdoManagerInterface
    //
    /**
     * @param $idx
     * @return string
     */
    public function getIndexName($idx)
    {
        return $this->db_instance->getIndexName($idx);
    }


    /**
     * @param $sqn
     * @return string
     */
    public function getSequenceName($sqn)
    {
        return $this->db_instance->getSequenceName($sqn);
    }


    /**
     * @param $table
     * @return array
     * @throws \ilDatabaseException
     */
    public function listTableFields($table)
    {
        $table = $this->db_instance->quoteIdentifier($table);
        $query = "SHOW COLUMNS FROM $table";
        $result = $this->db_instance->query($query);
        $return = array();
        while ($data = $this->db_instance->fetchObject($result)) {
            $return[] = $data->Field;
        }

        return $return;
    }


    /**
     * @param $table
     * @return array
     * @throws \ilDatabaseException
     */
    public function listTableConstraints($table)
    {
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';

        $db = $this->getDBInstance();
        if ($db->options['portability']) {
            if ($db->options['field_case'] == CASE_LOWER) {
                $key_name = strtolower($key_name);
                $non_unique = strtolower($non_unique);
            } else {
                $key_name = strtoupper($key_name);
                $non_unique = strtoupper($non_unique);
            }
        }

        $table = $this->db_instance->quoteIdentifier($table);
        $query = "SHOW INDEX FROM $table";
        $result_set = $this->db_instance->query($query);

        $result = array();
        while ($index_data = $this->db_instance->fetchAssoc($result_set)) {
            if (!$index_data[$non_unique]) {
                if ($index_data[$key_name] !== 'PRIMARY') {
                    $index = $this->fixIndexName($index_data[$key_name]);
                } else {
                    $index = 'PRIMARY';
                }
                if (!empty($index)) {
                    $index = strtolower($index);
                    $result[$index] = true;
                }
            }
        }

        if ($this->db_instance->options['portability']) {
            $result = array_change_key_case($result, $this->db_instance->options['field_case']);
        }

        return array_keys($result);
    }


    /**
     * @param $table
     * @return array
     * @throws \ilDatabaseException
     */
    public function listTableIndexes($table)
    {
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';
        if ($this->db_instance->options['portability']) {
            if ($this->db_instance->options['field_case'] == CASE_LOWER) {
                $key_name = strtolower($key_name);
                $non_unique = strtolower($non_unique);
            } else {
                $key_name = strtoupper($key_name);
                $non_unique = strtoupper($non_unique);
            }
        }

        $table = $this->db_instance->quoteIdentifier($table);
        $query = "SHOW INDEX FROM $table";
        $result_set = $this->db_instance->query($query);
        $indexes = array();
        while ($index_data = $this->db_instance->fetchAssoc($result_set)) {
            $indexes[] = $index_data;
        }
        $result = array();
        foreach ($indexes as $index_data) {
            if ($index_data[$non_unique] && ($index = $this->fixIndexName($index_data[$key_name]))) {
                $result[$index] = true;
            }
        }

        if ($this->db_instance->options['portability']) {
            $result = array_change_key_case($result, $this->db_instance->options['field_case']);
        }

        return array_keys($result);
    }


    /**
     * @param $idx
     * @return mixed
     */
    protected function fixIndexName($idx)
    {
        $idx_pattern = '/^' . preg_replace('/%s/', '([a-z0-9_]+)', ilDBPdoFieldDefinition::INDEX_FORMAT) . '$/i';
        $idx_name = preg_replace($idx_pattern, '\\1', $idx);
        if ($idx_name && !strcasecmp($idx, $this->db_instance->getIndexName($idx_name))) {
            return $idx_name;
        }

        return $idx;
    }


    /**
     * @param $table
     * @param $name
     * @param $definition
     * @return mixed
     */
    public function createIndex($table, $name, $definition)
    {
        $table = $this->db_instance->quoteIdentifier($table, true);
        $name = $this->db_instance->quoteIdentifier($this->db_instance->getIndexName($name), true);
        $query = "CREATE INDEX $name ON $table";
        $fields = array();
        foreach ($definition['fields'] as $field => $fieldinfo) {
            if (!empty($fieldinfo['length'])) {
                $fields[] = $this->db_instance->quoteIdentifier($field, true) . '(' . $fieldinfo['length'] . ')';
            } else {
                $fields[] = $this->db_instance->quoteIdentifier($field, true);
            }
        }
        $query .= ' (' . implode(', ', $fields) . ')';

        return $this->pdo->exec($query);
    }


    /**
     * @param $table
     * @param $name
     * @return mixed
     */
    public function dropIndex($table, $name)
    {
        $table = $this->db_instance->quoteIdentifier($table, true);
        $name = $this->db_instance->quoteIdentifier($this->db_instance->getIndexName($name), true);

        return $this->pdo->exec("DROP INDEX $name ON $table");
    }


    /**
     * @param $table_name
     * @return int
     */
    public function dropSequence($table_name)
    {
        $sequence_name = $this->db_instance->quoteIdentifier($this->db_instance->getSequenceName($table_name));

        return $this->pdo->exec("DROP TABLE $sequence_name");
    }


    /**
     * @param $name
     * @param $fields
     * @param array $options
     * @return string
     * @throws \ilDatabaseException
     */
    public function getTableCreationQuery($name, $fields, $options = array())
    {
        return $this->getQueryUtils()->createTable($name, $fields, $options);
    }


    /**
     * @param $table
     * @param $name
     * @param bool $primary
     * @return int
     */
    public function dropConstraint($table, $name, $primary = false)
    {
        $db = $this->getDBInstance();
        $table = $db->quoteIdentifier($table, true);
        if ($primary || strtolower($name) == 'primary') {
            $query = "ALTER TABLE $table DROP PRIMARY KEY";
        } else {
            $name = $db->quoteIdentifier($db->getIndexName($name), true);
            $query = "ALTER TABLE $table DROP INDEX $name";
        }

        return $this->pdo->exec($query);
    }


    /**
     * @inheritdoc
     */
    public function dropTable($name)
    {
        $db = $this->getDBInstance();

        $name = $db->quoteIdentifier($name, true);

        return $db->manipulate("DROP TABLE $name");
    }
}
