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
 * Class ilDBPdoManager
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoManager implements ilDBManager, ilDBPdoManagerInterface
{
    protected \PDO $pdo;
    protected \ilDBPdo $db_instance;
    protected ?\ilQueryUtils $query_utils = null;

    /**
     * ilDBPdoManager constructor.
     */
    public function __construct(\PDO $pdo, ilDBPdo $db_instance)
    {
        $this->pdo = $pdo;
        $this->db_instance = $db_instance;
    }

    public function getQueryUtils() : \ilQueryUtils
    {
        if ($this->query_utils === null) {
            $this->query_utils = new ilMySQLQueryUtils($this->db_instance);
        }

        return $this->query_utils;
    }

    public function getDBInstance() : \ilDBPdo
    {
        return $this->db_instance;
    }

    /**
     * @return int[]|string[]
     */
    public function listTables(?string $database = null) : array
    {
        $str = 'SHOW TABLES ' . ($database ? ' IN ' . $database : '');
        $r = $this->pdo->query($str);
        $tables = [];

        $sequence_identifier = "_seq";
        while ($data = $r->fetchColumn()) {
            if (!preg_match("/$sequence_identifier$/um", $data)) {
                $tables[] = $data;
            }
        }

        return $tables;
    }

    protected function fixSequenceName(string $sqn, bool $check = false) : string
    {
        $seq_pattern = '/^' . preg_replace('/%s/', '([a-z0-9_]+)', ilDBConstants::SEQUENCE_FORMAT) . '$/i';
        $seq_name = preg_replace($seq_pattern, '\\1', $sqn);
        if ($seq_name && !strcasecmp($sqn, $this->db_instance->getSequenceName($seq_name))) {
            return $seq_name;
        }

        return $sqn;
    }

    /**
     * @return string[]
     */
    public function listSequences(string $database = null) : array
    {
        $query = "SHOW TABLES LIKE '%_seq'";
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
        if ($this->db_instance->options['portability'] ?? null) {
            $result = array_map(
                ($this->db_instance->options['field_case'] === CASE_LOWER ? 'strtolower' : 'strtoupper'),
                $result
            );
        }

        return $result;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function createConstraint(string $table, string $name, array $definition) : bool
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

        return (bool) $this->pdo->exec($query);
    }

    public function createSequence(string $seq_name, int $start = 1, array $options = []) : bool
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
     * @throws \ilDatabaseException
     */
    public function alterTable(string $name, array $changes, bool $check) : bool
    {
        $db = $this->db_instance;

        foreach (array_keys($changes) as $change_name) {
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
                if ($query !== '') {
                    $query .= ', ';
                }
                $fd = $db->getFieldDefinition();
                if ($fd !== null) {
                    $query .= 'ADD ' . $fd->getDeclaration($field['type'], $field_name, $field);
                }
            }
        }

        if (!empty($changes['remove']) && is_array($changes['remove'])) {
            foreach (array_keys($changes['remove']) as $field_name) {
                if ($query !== '') {
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
                if ($query !== '') {
                    $query .= ', ';
                }
                if (isset($rename[$field_name])) {
                    $old_field_name = $rename[$field_name];
                    unset($rename[$field_name]);
                } else {
                    $old_field_name = $field_name;
                }
                $old_field_name = $db->quoteIdentifier($old_field_name);
                $fd = $this->db_instance->getFieldDefinition();
                if ($fd !== null) {
                    $query .= "CHANGE $old_field_name " . $fd
                            ->getDeclaration(
                                $field['definition']['type'],
                                $field_name,
                                $field['definition']
                            );
                }
            }
        }

        if (!empty($rename) && is_array($rename)) {
            foreach ($rename as $renamed_field) {
                if ($query !== '') {
                    $query .= ', ';
                }
                $field = $changes['rename'][$renamed_field];
                $renamed_field = $db->quoteIdentifier($renamed_field);
                $fd = $this->db_instance->getFieldDefinition();
                if ($fd !== null) {
                    $query .= 'CHANGE ' . $renamed_field . ' ' . $fd
                            ->getDeclaration(
                                $field['definition']['type'],
                                $field['name'],
                                $field['definition']
                            );
                }
            }
        }

        if ($query === '') {
            return true;
        }

        $name = $db->quoteIdentifier($name, true);

        $statement = "ALTER TABLE $name $query";

        return (bool) $this->pdo->exec($statement);
    }

    public function createTable(string $name, array $fields, array $options = array()) : bool
    {
        $options['type'] = $this->db_instance->getStorageEngine();

        return (bool) $this->pdo->exec($this->getQueryUtils()->createTable($name, $fields, $options));
    }

    public function getIndexName(string $idx) : string
    {
        return $this->db_instance->getIndexName($idx);
    }

    public function getSequenceName(string $sqn) : string
    {
        return $this->db_instance->getSequenceName($sqn);
    }

    public function listTableFields(string $table) : array
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
     * @return string[]
     */
    public function listTableConstraints(string $table) : array
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
                $index = $index_data[$key_name] !== 'PRIMARY' ? $this->fixIndexName($index_data[$key_name]) : 'PRIMARY';
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
     * @return string[]
     */
    public function listTableIndexes(string $table) : array
    {
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';
        if ($this->db_instance->options['portability'] ?? null) {
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

        if ($this->db_instance->options['portability'] ?? null) {
            $result = array_change_key_case($result, $this->db_instance->options['field_case']);
        }

        return array_keys($result);
    }

    protected function fixIndexName(string $idx) : string
    {
        $idx_pattern = '/^' . preg_replace('/%s/', '([a-z0-9_]+)', ilDBPdoFieldDefinition::INDEX_FORMAT) . '$/i';
        $idx_name = preg_replace($idx_pattern, '\\1', $idx);
        if ($idx_name && !strcasecmp($idx, $this->db_instance->getIndexName($idx_name))) {
            return $idx_name;
        }

        return $idx;
    }

    public function createIndex(string $table, string $name, array $definition) : bool
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

        return (bool) $this->pdo->exec($query);
    }

    public function dropIndex(string $table, string $name) : bool
    {
        $table = $this->db_instance->quoteIdentifier($table, true);
        $name = $this->db_instance->quoteIdentifier($this->db_instance->getIndexName($name), true);

        return (bool) $this->pdo->exec("DROP INDEX $name ON $table");
    }

    public function dropSequence(string $seq_name) : bool
    {
        $sequence_name = $this->db_instance->quoteIdentifier($this->db_instance->getSequenceName($seq_name));

        return (bool) $this->pdo->exec("DROP TABLE $sequence_name");
    }

    /**
     * @throws \ilDatabaseException
     */
    public function getTableCreationQuery(string $name, array $fields, array $options = []) : string
    {
        return $this->getQueryUtils()->createTable($name, $fields, $options);
    }

    public function dropConstraint(string $table, string $name, bool $primary = false) : bool
    {
        $db = $this->getDBInstance();
        $table = $db->quoteIdentifier($table, true);
        if ($primary || strtolower($name) === 'primary') {
            $query = "ALTER TABLE $table DROP PRIMARY KEY";
        } else {
            $name = $db->quoteIdentifier($db->getIndexName($name), true);
            $query = "ALTER TABLE $table DROP INDEX $name";
        }

        return (bool) $this->pdo->exec($query);
    }

    public function dropTable(string $name) : bool
    {
        $db = $this->getDBInstance();
        $name = $db->quoteIdentifier($name, true);

        return (bool) $this->pdo->exec("DROP TABLE $name");
    }
}
