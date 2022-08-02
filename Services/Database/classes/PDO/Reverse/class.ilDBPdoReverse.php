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
 * Class ilDBPdoReverse
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoReverse implements ilDBReverse
{
    protected \PDO $pdo;
    protected \ilDBPdo $db_instance;
    protected ?\ilMySQLQueryUtils $query_utils = null;

    /**
     * ilDBPdoReverse constructor.
     */
    public function __construct(\PDO $pdo, ilDBPdo $db_instance)
    {
        $this->pdo = $pdo;
        $this->db_instance = $db_instance;
    }

    public function getQueryUtils() : \ilMySQLQueryUtils
    {
        if ($this->query_utils === null) {
            $this->query_utils = new ilMySQLQueryUtils($this->db_instance);
        }

        return $this->query_utils;
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function getTableFieldDefinition(string $table_name, string $field_name) : array
    {
        $table = $this->db_instance->quoteIdentifier($table_name);
        $query = "SHOW COLUMNS FROM $table LIKE " . $this->db_instance->quote($field_name);
        $res = $this->pdo->query($query);
        $columns = array();
        while ($data = $res->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $data;
        }

        $ilDBPdoFieldDefinition = $this->db_instance->getFieldDefinition();

        foreach ($columns as $column) {
            $column = array_change_key_case($column, CASE_LOWER);
            $column['name'] = $column['field'];
            unset($column['field']);
            $column = array_change_key_case($column, CASE_LOWER);
            if ($field_name === $column['name'] && $ilDBPdoFieldDefinition !== null) {
                [$types, $length, $unsigned, $fixed] = $ilDBPdoFieldDefinition->mapNativeDatatype($column);
                $notnull = false;
                if (empty($column['null']) || $column['null'] !== 'YES') {
                    $notnull = true;
                }
                $default = false;
                if (array_key_exists('default', $column)) {
                    $default = $column['default'];
                    if ($notnull && is_null($default)) {
                        $default = '';
                    }
                }
                $autoincrement = false;
                if (!empty($column['extra']) && $column['extra'] === 'auto_increment') {
                    $autoincrement = true;
                }

                $definition[0] = array(
                    'notnull' => $notnull,
                    'nativetype' => preg_replace('/^([a-z]+)[^a-z].*/i', '\\1', $column['type']),
                );
                if (!is_null($length)) {
                    $definition[0]['length'] = $length;
                }
                if (!is_null($unsigned)) {
                    $definition[0]['unsigned'] = $unsigned;
                }
                if (!is_null($fixed)) {
                    $definition[0]['fixed'] = $fixed;
                }
                if ($default !== false) {
                    $definition[0]['default'] = $default;
                }
                if ($autoincrement) {
                    $definition[0]['autoincrement'] = $autoincrement;
                }
                foreach ($types as $key => $type) {
                    $definition[$key] = $definition[0];
                    if ($type === 'clob' || $type === 'blob') {
                        unset($definition[$key]['default']);
                    }
                    $definition[$key]['type'] = $type;
                    $definition[$key]['mdb2type'] = $type;
                }

                return $definition;
            }
        }

        throw new ilDatabaseException('it was not specified an existing table column');
    }

    /**
     * @return array<string, array<int|string, array<string, string|int>>&array>
     * @throws \ilDatabaseException
     */
    public function getTableIndexDefinition(string $table, string $constraint_name) : array
    {
        $table = $this->db_instance->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table /*!50002 WHERE Key_name = %s */";
        $index_name_pdo = $this->db_instance->getIndexName($constraint_name);
        $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($index_name_pdo)));
        $data = $this->db_instance->fetchAssoc($result);

        if ($data) {
            $constraint_name = $index_name_pdo;
        }

        $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($constraint_name)));

        $colpos = 1;
        $definition = array();
        while (is_object($row = $result->fetchObject())) {
            $row = array_change_key_case((array) $row, CASE_LOWER);

            $key_name = $row['key_name'];

            if ($constraint_name === $key_name) {
                if (!$row['non_unique']) {
                    throw new ilDatabaseException('it was not specified an existing table index');
                }
                $column_name = $row['column_name'];
                $definition['fields'][$column_name] = array(
                    'position' => $colpos++,
                );
                if (!empty($row['collation'])) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] === 'A' ? 'ascending' : 'descending');
                }
            }
        }

        if (empty($definition['fields'])) {
            throw new ilDatabaseException('it was not specified an existing table index (index does not exist)');
        }

        return $definition;
    }

    /**
     * @return array<string, array<int|string, array<string, string|int>>&array>|array<string, bool>
     * @throws \ilDatabaseException
     */
    public function getTableConstraintDefinition(string $table, string $index_name) : array
    {
        $index_name = strtolower($index_name);
        $table = $this->db_instance->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table /*!50002 WHERE Key_name = %s */";

        if (strtolower($index_name) !== 'primary') {
            $constraint_name_pdo = $this->db_instance->getIndexName($index_name);
            $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($constraint_name_pdo)));
            $data = $this->db_instance->fetchAssoc($result);
            if ($data) {
                // apply 'idxname_format' only if the query succeeded, otherwise
                // fallback to the given $index_name, without transformation
                $index_name = strtolower($constraint_name_pdo);
            }
        }

        $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($index_name)));

        $colpos = 1;
        $definition = array();
        while (is_object($row = $result->fetchObject())) {
            $row = (array) $row;
            $row = array_change_key_case($row, CASE_LOWER);
            $key_name = $row['key_name'];
            if ($this->db_instance->options['portability'] ?? null) {
                $key_name = strtolower($key_name);
            }
            $key_name = strtolower($key_name); // FSX fix
            if ($index_name === $key_name) {
                if ($row['non_unique']) {
                    throw new ilDatabaseException(' is not an existing table constraint');
                }
                if (strtolower($row['key_name']) === 'primary') {
                    $definition['primary'] = true;
                } else {
                    $definition['unique'] = true;
                }
                $column_name = $row['column_name'];
                if ($this->db_instance->options['portability'] ?? null) {
                    if ($this->db_instance->options['field_case'] == CASE_LOWER) {
                        $column_name = strtolower($column_name);
                    } else {
                        $column_name = strtoupper($column_name);
                    }
                }
                $definition['fields'][$column_name] = array(
                    'position' => $colpos++,
                );
                if (!empty($row['collation'])) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] === 'A' ? 'ascending' : 'descending');
                }
            }
        }

        if (empty($definition['fields'])) {
            throw new ilDatabaseException(' is not an existing table constraint');
        }

        return $definition;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function getTriggerDefinition(string $trigger) : array
    {
        throw new ilDatabaseException('not yet implemented ' . __METHOD__);
    }
}
