<?php
require_once('./Services/Database/interfaces/interface.ilDBReverse.php');

/**
 * Class ilDBPdoReverse
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoReverse implements ilDBReverse
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
     * ilDBPdoReverse constructor.
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
     * @param $table_name
     * @param $field_name
     * @return array
     */
    public function getTableFieldDefinition($table_name, $field_name)
    {
        $return = array();

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
            //			if ($this->db_instance->options['portability']) {
            //				if ($this->db_instance->options['field_case'] == CASE_LOWER) {
            //					$column['name'] = strtolower($column['name']);
            //				} else {
            //					$column['name'] = strtoupper($column['name']);
            //				}
            //			} else {
            $column = array_change_key_case($column, CASE_LOWER);
            //			}
            if ($field_name == $column['name']) {
                $mapped_datatype = $ilDBPdoFieldDefinition->mapNativeDatatype($column);

                list($types, $length, $unsigned, $fixed) = $mapped_datatype;
                $notnull = false;
                if (empty($column['null']) || $column['null'] !== 'YES') {
                    $notnull = true;
                }
                $default = false;
                if (array_key_exists('default', $column)) {
                    $default = $column['default'];
                    if (is_null($default) && $notnull) {
                        $default = '';
                    }
                }
                $autoincrement = false;
                if (!empty($column['extra']) && $column['extra'] == 'auto_increment') {
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
                if ($autoincrement !== false) {
                    $definition[0]['autoincrement'] = $autoincrement;
                }
                foreach ($types as $key => $type) {
                    $definition[$key] = $definition[0];
                    if ($type == 'clob' || $type == 'blob') {
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
     * @param $table
     * @param $index_name
     * @return array
     * @throws \ilDatabaseException
     */
    public function getTableIndexDefinition($table, $index_name)
    {
        $table = $this->db_instance->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table /*!50002 WHERE Key_name = %s */";
        $index_name_pdo = $this->db_instance->getIndexName($index_name);
        $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($index_name_pdo)));
        $data = $this->db_instance->fetchAssoc($result);

        if ($data) {
            $index_name = $index_name_pdo;
        }

        $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($index_name)));

        $colpos = 1;
        $definition = array();
        while (is_object($row = $result->fetchObject())) {
            $row = array_change_key_case((array) $row, CASE_LOWER);

            $key_name = $row['key_name'];


            if ($index_name == $key_name) {
                if (!$row['non_unique']) {
                    throw new ilDatabaseException('it was not specified an existing table index');
                }
                $column_name = $row['column_name'];
                $definition['fields'][$column_name] = array(
                    'position' => $colpos++,
                );
                if (!empty($row['collation'])) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A' ? 'ascending' : 'descending');
                }
            }
        }

        if (empty($definition['fields'])) {
            throw new ilDatabaseException('it was not specified an existing table index (index does not exist)');
        }

        return $definition;
    }


    /**
     * @param $table
     * @param $constraint_name
     * @return array
     * @throws \ilDatabaseException
     */
    public function getTableConstraintDefinition($table, $constraint_name)
    {
        $constraint_name = strtolower($constraint_name);
        $table = $this->db_instance->quoteIdentifier($table, true);
        $query = "SHOW INDEX FROM $table /*!50002 WHERE Key_name = %s */";

        if (strtolower($constraint_name) != 'primary') {
            $constraint_name_pdo = $this->db_instance->getIndexName($constraint_name);
            $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($constraint_name_pdo)));
            $data = $this->db_instance->fetchAssoc($result);
            if ($data) {
                // apply 'idxname_format' only if the query succeeded, otherwise
                // fallback to the given $index_name, without transformation
                $constraint_name = strtolower($constraint_name_pdo);
            }
        }

        $result = $this->db_instance->query(sprintf($query, $this->db_instance->quote($constraint_name)));

        $colpos = 1;
        $definition = array();
        while (is_object($row = $result->fetchObject())) {
            $row = (array) $row;
            $row = array_change_key_case($row, CASE_LOWER);
            $key_name = $row['key_name'];
            if ($this->db_instance->options['portability']) {
                if ($this->db_instance->options['field_case'] == CASE_LOWER) {
                    $key_name = strtolower($key_name);
                } else {
                    $key_name = strtolower($key_name);
                }
            }
            $key_name = strtolower($key_name); // FSX fix
            if ($constraint_name == $key_name) {
                if ($row['non_unique']) {
                    throw new ilDatabaseException(' is not an existing table constraint');
                }
                if ($row['key_name'] == 'PRIMARY') {
                    $definition['primary'] = true;
                } else {
                    $definition['unique'] = true;
                }
                $column_name = $row['column_name'];
                if ($this->db_instance->options['portability']) {
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
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A' ? 'ascending' : 'descending');
                }
            }
        }

        if (empty($definition['fields'])) {
            throw new ilDatabaseException(' is not an existing table constraint');
        }

        return $definition;
    }


    /**
     * @param $trigger
     * @return array|void
     * @throws \ilDatabaseException
     */
    public function getTriggerDefinition($trigger)
    {
        throw new ilDatabaseException('not yet implemented ' . __METHOD__);
    }
}
