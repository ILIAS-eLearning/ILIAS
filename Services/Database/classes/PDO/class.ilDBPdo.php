<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class pdoDB
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdo implements ilDBInterface, ilDBPdoInterface
{
    const FEATURE_TRANSACTIONS = 'transactions';
    const FEATURE_FULLTEXT = 'fulltext';
    const FEATURE_SLAVE = 'slave';
    /**
     * @var string
     */
    protected $host = '';
    /**
     * @var string
     */
    protected $dbname = '';
    /**
     * @var string
     */
    protected $charset = 'utf8';
    /**
     * @var string
     */
    protected $username = '';
    /**
     * @var string
     */
    protected $password = '';
    /**
     * @var int
     */
    protected $port = 3306;
    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var ilDBPdoManager
     */
    protected $manager;
    /**
     * @var ilDBPdoReverse
     */
    protected $reverse;
    /**
     * @var int
     */
    protected $limit = null;
    /**
     * @var int
     */
    protected $offset = null;
    /**
     * @var string
     */
    protected $storage_engine = 'MyISAM';
    /**
     * @var string
     */
    protected $dsn = '';
    /**
     * @var array
     */
    protected $attributes = array(
        //		PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    );
    /**
     * @var string
     */
    protected $db_type = '';
    /**
     * @var int
     */
    protected $error_code = 0;
    /**
     * @var ilDBPdoFieldDefinition
     */
    protected $field_definition;


    /**
     * @param bool $return_false_for_error
     * @return bool
     * @throws \Exception
     */
    public function connect($return_false_for_error = false)
    {
        $this->generateDSN();
        try {
            $options = $this->getAttributes();
            $this->pdo = new PDO($this->getDSN(), $this->getUsername(), $this->getPassword(), $options);
            $this->initHelpers();
            $this->initSQLMode();
        } catch (Exception $e) {
            $this->error_code = $e->getCode();
            if ($return_false_for_error) {
                return false;
            }
            throw $e;
        }

        return ($this->pdo->errorCode() == PDO::ERR_NONE);
    }


    abstract public function initHelpers();


    protected function initSQLMode()
    {
    }


    /**
     * @return array
     */
    protected function getAttributes()
    {
        $options = $this->attributes;
        foreach ($this->getAdditionalAttributes() as $k => $v) {
            $options[$k] = $v;
        }

        return $options;
    }


    /**
     * @return array
     */
    protected function getAdditionalAttributes()
    {
        return array();
    }


    /**
     * @return ilDBPdoFieldDefinition
     */
    public function getFieldDefinition()
    {
        return $this->field_definition;
    }


    /**
     * @param ilDBPdoFieldDefinition $field_definition
     */
    public function setFieldDefinition($field_definition)
    {
        $this->field_definition = $field_definition;
    }


    /**
     * @param $a_name
     * @param string $a_charset
     * @param string $a_collation
     * @return ilPDOStatement|false
     * @throws \ilDatabaseException
     */
    public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "")
    {
        $this->setDbname(null);
        $this->generateDSN();
        $this->connect(true);
        try {
            return $this->query($this->manager->getQueryUtils()->createDatabase($a_name, $a_charset, $a_collation));
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * @return int
     */
    public function getLastErrorCode()
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo->errorCode();
        }

        return $this->error_code;
    }


    /**
     * @param null $tmpClientIniFile
     */
    public function initFromIniFile($tmpClientIniFile = null)
    {
        global $DIC;

        if ($tmpClientIniFile instanceof ilIniFile) {
            $clientIniFile = $tmpClientIniFile;
        } else {
            $ilClientIniFile = null;
            if ($DIC->offsetExists('ilClientIniFile')) {
                $clientIniFile = $DIC['ilClientIniFile'];
            } else {
                throw new InvalidArgumentException('$tmpClientIniFile is not an instance of ilIniFile');
            }
        }

        $this->setUsername($clientIniFile->readVariable("db", "user"));
        $this->setHost($clientIniFile->readVariable("db", "host"));
        $this->setPort((int) $clientIniFile->readVariable("db", "port"));
        $this->setPassword($clientIniFile->readVariable("db", "pass"));
        $this->setDbname($clientIniFile->readVariable("db", "name"));
        $this->setDBType($clientIniFile->readVariable("db", "type"));

        $this->generateDSN();
    }


    public function generateDSN()
    {
        $port = $this->getPort() ? ";port=" . $this->getPort() : "";
        $dbname = $this->getDbname() ? ';dbname=' . $this->getDbname() : '';
        $host = $this->getHost();
        $charset = ';charset=' . $this->getCharset();
        $this->dsn = 'mysql:host=' . $host . $port . $dbname . $charset;
    }


    /**
     * @param $identifier
     * @return string
     */
    public function quoteIdentifier($identifier, $check_option = false)
    {
        return '`' . $identifier . '`';
    }


    /**
     * @param $table_name string
     *
     * @return int
     */
    public function nextId($table_name)
    {
        $sequence_table_name = $table_name . '_seq';

        $last_insert_id = $this->pdo->lastInsertId($table_name);
        if ($last_insert_id) {
            //			return $last_insert_id;
        }

        if ($this->tableExists($sequence_table_name)) {
            $stmt = $this->pdo->prepare("SELECT sequence FROM $sequence_table_name");
            $stmt->execute();
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $next_id = $rows['sequence'] + 1;
            $stmt = $this->pdo->prepare("DELETE FROM $sequence_table_name");
            $stmt->execute(array("next_id" => $next_id));
            $stmt = $this->pdo->prepare("INSERT INTO $sequence_table_name (sequence) VALUES (:next_id)");
            $stmt->execute(array("next_id" => $next_id));

            return $next_id;
        }

        return 1;
    }


    /**
     * @param $table_name
     * @param $fields
     * @param bool $drop_table
     * @param bool $ignore_erros
     * @return mixed
     * @throws \ilDatabaseException
     */
    public function createTable($table_name, $fields, $drop_table = false, $ignore_erros = false)
    {
        // check table name
        if (!$this->checkTableName($table_name) && !$ignore_erros) {
            throw new ilDatabaseException("ilDB Error: createTable(" . $table_name . ")");
        }

        // check definition array
        if (!$this->checkTableColumns($fields) && !$ignore_erros) {
            throw new ilDatabaseException("ilDB Error: createTable(" . $table_name . ")");
        }

        if ($drop_table) {
            $this->dropTable($table_name, false);
        }

        return $this->manager->createTable($table_name, $fields, array());
    }


    /**
     * @param $a_cols
     * @return bool
     */
    protected function checkTableColumns($a_cols)
    {
        foreach ($a_cols as $col => $def) {
            if (!$this->checkColumn($col, $def)) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param $a_col
     * @param $a_def
     * @return bool
     */
    protected function checkColumn($a_col, $a_def)
    {
        if (!$this->checkColumnName($a_col)) {
            return false;
        }

        if (!$this->checkColumnDefinition($a_def)) {
            return false;
        }

        return true;
    }


    /**
     * @param $a_def
     * @param bool $a_modify_mode
     * @return bool
     */
    protected function checkColumnDefinition($a_def, $a_modify_mode = false)
    {
        return $this->field_definition->checkColumnDefinition($a_def);
    }


    /**
     * @param $a_name
     * @return bool
     */
    public function checkColumnName($a_name)
    {
        return $this->field_definition->checkColumnName($a_name);
    }


    /**
     * @param string $table_name
     * @param array $primary_keys
     * @return bool
     * @throws \ilDatabaseException
     */
    public function addPrimaryKey($table_name, $primary_keys)
    {
        assert(is_array($primary_keys));

        $fields = array();
        foreach ($primary_keys as $f) {
            $fields[$f] = array();
        }
        $definition = array(
            'primary' => true,
            'fields' => $fields,
        );
        $this->manager->createConstraint($table_name, $this->constraintName($table_name, $this->getPrimaryKeyIdentifier()), $definition);

        return true;
    }


    /**
     * @param $table_name
     * @param $fields
     * @return bool|mixed
     * @throws \ilDatabaseException
     */
    public function dropIndexByFields($table_name, $fields)
    {
        foreach ($this->manager->listTableIndexes($table_name) as $idx_name) {
            $def = $this->reverse->getTableIndexDefinition($table_name, $idx_name);
            $idx_fields = array_keys((array) $def['fields']);

            if ($idx_fields === $fields) {
                return $this->dropIndex($table_name, $idx_name);
            }
        }

        return false;
    }


    /**
     * @return string
     */
    public function getPrimaryKeyIdentifier()
    {
        return "PRIMARY";
    }


    /**
     * @param $table_name
     * @param int $start
     */
    public function createSequence($table_name, $start = 1)
    {
        $this->manager->createSequence($table_name, $start);
    }


    /**
     * @param $table_name string
     *
     * @return bool
     */
    public function tableExists($table_name)
    {
        $result = $this->pdo->prepare("SHOW TABLES LIKE :table_name");
        $result->execute(array('table_name' => $table_name));
        $return = $result->rowCount();
        $result->closeCursor();

        return $return > 0;
    }


    /**
     * @param $table_name  string
     * @param $column_name string
     *
     * @return bool
     */
    public function tableColumnExists($table_name, $column_name)
    {
        $fields = $this->loadModule(ilDBConstants::MODULE_MANAGER)->listTableFields($table_name);

        $in_array = in_array($column_name, $fields);

        return $in_array;
    }


    /**
     * @param string $table_name
     * @param string $column_name
     * @param array $attributes
     * @return bool
     * @throws \ilDatabaseException
     */
    public function addTableColumn($table_name, $column_name, $attributes)
    {
        if (!$this->checkColumnName($column_name)) {
            throw new ilDatabaseException("ilDB Error: addTableColumn(" . $table_name . ", " . $column_name . ")");
        }
        if (!$this->checkColumnDefinition($attributes)) {
            throw new ilDatabaseException("ilDB Error: addTableColumn(" . $table_name . ", " . $column_name . ")");
        }

        $changes = array(
            "add" => array(
                $column_name => $attributes,
            ),
        );

        return $this->manager->alterTable($table_name, $changes, false);
    }


    /**
     * @param $table_name
     * @param bool $error_if_not_existing
     * @return bool
     * @throws \ilDatabaseException
     */
    public function dropTable($table_name, $error_if_not_existing = true)
    {
        $ilDBPdoManager = $this->loadModule(ilDBConstants::MODULE_MANAGER);
        $tables = $ilDBPdoManager->listTables();
        $table_exists = in_array($table_name, $tables);
        if (!$table_exists && $error_if_not_existing) {
            throw new ilDatabaseException("Table {$table_name} does not exist");
        }

        // drop sequence
        $sequences = $ilDBPdoManager->listSequences();
        if (in_array($table_name, $sequences)) {
            $ilDBPdoManager->dropSequence($table_name);
        }

        // drop table
        if ($table_exists) {
            $ilDBPdoManager->dropTable($table_name);
        }

        return true;
    }


    /**
     * @param $query string
     *
     * @return ilPDOStatement
     * @throws ilDatabaseException
     */
    public function query($query)
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];

        $query = $this->appendLimit($query);

        try {
            if ($ilBench instanceof ilBenchmark) {
                $ilBench->startDbBench($query);
            }
            $res = $this->pdo->query($query);
            if ($ilBench instanceof ilBenchmark) {
                $ilBench->stopDbBench();
            }
        } catch (PDOException $e) {
            throw new ilDatabaseException($e->getMessage() . ' QUERY: ' . $query);
        }

        $err = $this->pdo->errorCode();
        if ($err != PDO::ERR_NONE) {
            $info = $this->pdo->errorInfo();
            $info_message = $info[2];
            throw new ilDatabaseException($info_message . ' QUERY: ' . $query);
        }

        return new ilPDOStatement($res);
    }


    /**
     * @param $query_result
     * @param int $fetch_mode
     * @return array
     */
    public function fetchAll($query_result, $fetch_mode = ilDBConstants::FETCHMODE_ASSOC)
    {
        /**
         * @var $query_result ilPDOStatement
         */
        $return = array();
        while ($data = $query_result->fetch($fetch_mode)) {
            $return[] = $data;
        }

        return $return;
    }


    /**
     * @param $table_name string
     */
    public function dropSequence($table_name)
    {
        $this->manager->dropSequence($table_name);
    }


    /**
     * @param string $table_name
     * @param string $column_name
     * @return bool
     * @throws \ilDatabaseException
     */
    public function dropTableColumn($table_name, $column_name)
    {
        $changes = array(
            "remove" => array(
                $column_name => array(),
            ),
        );

        return $this->manager->alterTable($table_name, $changes, false);
    }


    /**
     * @param string $table_name
     * @param string $column_old_name
     * @param string $column_new_name
     * @return bool
     * @throws \ilDatabaseException
     */
    public function renameTableColumn($table_name, $column_old_name, $column_new_name)
    {
        // check table name
        if (!$this->checkColumnName($column_new_name)) {
            throw new ilDatabaseException("ilDB Error: renameTableColumn(" . $table_name . "," . $column_old_name . "," . $column_new_name . ")");
        }

        $def = $this->reverse->getTableFieldDefinition($table_name, $column_old_name);

        $analyzer = new ilDBAnalyzer($this);
        $best_alt = $analyzer->getBestDefinitionAlternative($def);
        $def = $def[$best_alt];
        unset($def["nativetype"]);
        unset($def["mdb2type"]);

        $f["definition"] = $def;
        $f["name"] = $column_new_name;

        $changes = array(
            "rename" => array(
                $column_old_name => $f,
            ),
        );

        return $this->manager->alterTable($table_name, $changes, false);
    }


    /**
     * @param $table_name string
     * @param $values
     * @return int|void
     */
    public function insert($table_name, $values)
    {
        $real = array();
        $fields = array();
        foreach ($values as $key => $val) {
            $real[] = $this->quote($val[1], $val[0]);
            $fields[] = $this->quoteIdentifier($key);
        }
        $values = implode(",", $real);
        $fields = implode(",", $fields);
        $query = "INSERT INTO " . $table_name . " (" . $fields . ") VALUES (" . $values . ")";

        $query = $this->sanitizeMB4StringIfNotSupported($query);

        return $this->pdo->exec($query);
    }


    /**
     * @param $query_result ilPDOStatement
     *
     * @return mixed|null
     */
    public function fetchObject($query_result)
    {
        $res = $query_result->fetchObject();
        if ($res == null) {
            $query_result->closeCursor();

            return null;
        }

        return $res;
    }


    /**
     * @param $table_name string
     * @param $values     array
     * @param $where      array
     * @return int|void
     */
    public function update($table_name, $columns, $where)
    {
        $fields = array();
        $field_values = array();
        $placeholders = array();
        $placeholders_full = array();
        $types = array();
        $values = array();
        $lobs = false;
        $lob = array();
        foreach ($columns as $k => $col) {
            $field_value = $col[1];
            $fields[] = $k;
            $placeholders[] = "%s";
            $placeholders_full[] = ":$k";
            $types[] = $col[0];

            if ($col[0] == "blob" || $col[0] == "clob" || $col[0] == 'text') {
                $field_value = $this->sanitizeMB4StringIfNotSupported($field_value);
            }

            // integer auto-typecast (this casts bool values to integer)
            if ($col[0] == 'integer' && !is_null($field_value)) {
                $field_value = (int) $field_value;
            }

            $values[] = $field_value;
            $field_values[$k] = $field_value;
            if ($col[0] == "blob" || $col[0] == "clob") {
                $lobs = true;
                $lob[$k] = $k;
            }
        }

        if ($lobs) {
            $q = "UPDATE " . $table_name . " SET ";
            $lim = "";
            foreach ($fields as $k => $field) {
                $q .= $lim . $field . " = " . $placeholders_full[$k];
                $lim = ", ";
            }
            $q .= " WHERE ";
            $lim = "";
            foreach ($where as $k => $col) {
                $q .= $lim . $k . " = " . $this->quote($col[1], $col[0]);
                $lim = " AND ";
            }

            $r = $this->prepareManip($q, $types);
            $this->execute($r, $field_values);
            $this->free($r);
        } else {
            foreach ($where as $k => $col) {
                $types[] = $col[0];
                $values[] = $col[1];
                $field_values[$k] = $col;
            }
            $q = "UPDATE " . $table_name . " SET ";
            $lim = "";
            foreach ($fields as $k => $field) {
                $q .= $lim . $this->quoteIdentifier($field) . " = " . $placeholders[$k];
                $lim = ", ";
            }
            $q .= " WHERE ";
            $lim = "";
            foreach ($where as $k => $col) {
                $q .= $lim . $k . " = %s";
                $lim = " AND ";
            }

            $r = $this->manipulateF($q, $types, $values);
        }

        return $r;
    }



    /**
     * @param string $query
     * @return bool|int
     * @throws \ilDatabaseException
     */
    public function manipulate($query)
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        try {
            $query = $this->sanitizeMB4StringIfNotSupported($query);
            if ($ilBench instanceof ilBenchmark) {
                $ilBench->startDbBench($query);
            }
            $r = $this->pdo->exec($query);
            if ($ilBench instanceof ilBenchmark) {
                $ilBench->stopDbBench();
            }
        } catch (PDOException $e) {
            throw new ilDatabaseException($e->getMessage() . ' QUERY: ' . $query);
        }

        return $r;
    }


    /**
     * @param $query_result ilPDOStatement
     *
     * @return mixed
     */
    public function fetchAssoc($query_result)
    {
        $res = $query_result->fetch(PDO::FETCH_ASSOC);
        if ($res == null) {
            $query_result->closeCursor();

            return null;
        }

        return $res;
    }


    /**
     * @param $query_result PDOStatement
     *
     * @return int
     */
    public function numRows($query_result)
    {
        return $query_result->rowCount();
    }


    /**
     * @param $value
     * @param $type
     *
     * @return mixed
     */
    public function quote($value, $type = null)
    {
        if ($value === null) {
            return 'NULL';
        }

        $pdo_type = PDO::PARAM_STR;
        switch ($type) {
            case ilDBConstants::T_TIMESTAMP:
            case ilDBConstants::T_DATETIME:
            case ilDBConstants::T_DATE:
                if ($value === '') {
                    return 'NULL';
                }
                break;
            case ilDBConstants::T_INTEGER:
                $value = (int) $value;

                return $value;
                break;
            case ilDBConstants::T_FLOAT:
                $pdo_type = PDO::PARAM_INT;
                break;
            case ilDBConstants::T_TEXT:
            default:
                $pdo_type = PDO::PARAM_STR;
                break;
        }

        return $this->pdo->quote($value, $pdo_type);
    }


    /**
     * @param string $table_name
     * @param array $fields
     *
     * @return bool
     */
    public function indexExistsByFields($table_name, $fields)
    {
        foreach ($this->manager->listTableIndexes($table_name) as $idx_name) {
            $def = $this->reverse->getTableIndexDefinition($table_name, $idx_name);
            $idx_fields = array_keys((array) $def['fields']);

            if ($idx_fields === $fields) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $table_name
     * @param array $fields
     * @param $index_name
     * @return null
     */
    public function addIndex($table_name, $fields, $index_name = '', $fulltext = false)
    {
        assert(is_array($fields));
        $this->field_definition->checkIndexName($index_name);

        $definition_fields = array();
        foreach ($fields as $f) {
            $definition_fields[$f] = array();
        }
        $definition = array(
            'fields' => $definition_fields,
        );

        if (!$fulltext) {
            $this->manager->createIndex($table_name, $this->constraintName($table_name, $index_name), $definition);
        } else {
            if ($this->supportsFulltext()) {
                $this->addFulltextIndex($table_name, $fields, $index_name); // TODO
            }
        }

        return true;
    }


    /**
     * @param $a_table
     * @param $a_fields
     * @param string $a_name
     * @throws \ilDatabaseException
     * @return bool
     */
    public function addFulltextIndex($a_table, $a_fields, $a_name = "in")
    {
        $i_name = $this->constraintName($a_table, $a_name) . "_idx";
        $f_str = implode(",", $a_fields);
        $q = "ALTER TABLE $a_table ADD FULLTEXT $i_name ($f_str)";
        $this->query($q);
    }


    /**
     * Drop fulltext index
     */
    public function dropFulltextIndex($a_table, $a_name)
    {
        $i_name = $this->constraintName($a_table, $a_name) . "_idx";
        $this->query("ALTER TABLE $a_table DROP FULLTEXT $i_name");
    }


    /**
     * Is index a fulltext index?
     */
    public function isFulltextIndex($a_table, $a_name)
    {
        $set = $this->query("SHOW INDEX FROM " . $a_table);
        while ($rec = $this->fetchAssoc($set)) {
            if ($rec["Key_name"] == $a_name && $rec["Index_type"] == "FULLTEXT") {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $index_name_base
     * @return string
     */
    public function getIndexName($index_name_base)
    {
        return sprintf(ilDBPdoFieldDefinition::INDEX_FORMAT, preg_replace('/[^a-z0-9_\$]/i', '_', $index_name_base));
    }


    /**
     * @param $table_name
     * @return string
     */
    public function getSequenceName($table_name)
    {
        return sprintf(ilDBPdoFieldDefinition::SEQUENCE_FORMAT, preg_replace('/[^a-z0-9_\$.]/i', '_', $table_name));
    }


    /**
     * Determine contraint name by table name and constraint name.
     * In MySQL these are "unique" per table
     */
    public function constraintName($a_table, $a_constraint)
    {
        return $a_constraint;
    }


    /**
     * @return string
     */
    public function getDSN()
    {
        return $this->dsn;
    }


    /**
     * @return string
     */
    public function getDBType()
    {
        return $this->db_type;
    }


    /**
     * @param string $type
     */

    public function setDBType($type)
    {
        $this->db_type = $type;
    }


    /**
     * @return array
     * @deprecated use
     */
    public static function getReservedWords()
    {
        global $DIC;
        $ilDB = $DIC->database();

        /**
         * @var $ilDB ilDBPdo
         */
        return $ilDB->getFieldDefinition()->getReservedMysql();
    }


    /**
     * @deprecated Use ilAtomQuery instead
     * @param array $tables
     */
    public function lockTables($tables)
    {
        assert(is_array($tables));

        $lock = $this->manager->getQueryUtils()->lock($tables);
        global $DIC;
        $ilLogger = $DIC->logger()->root();
        if ($ilLogger instanceof ilLogger) {
            $ilLogger->log('ilDB::lockTables(): ' . $lock);
        }

        $this->pdo->exec($lock);
    }


    /**
     * @deprecated Use ilAtomQuery instead
     * @throws \ilDatabaseException
     */
    public function unlockTables()
    {
        $this->pdo->exec($this->manager->getQueryUtils()->unlock());
    }


    /**
     * @param $field  string
     * @param $values array
     * @param bool $negate
     * @param string $type
     * @return string
     */
    public function in($field, $values, $negate = false, $type = "")
    {
        return $this->manager->getQueryUtils()->in($field, $values, $negate, $type);
    }


    /**
     * @param string $query
     * @param \string[] $types
     * @param \mixed[] $values
     * @return ilPDOStatement
     * @throws \ilDatabaseException
     */
    public function queryF($query, $types, $values)
    {
        if (!is_array($types) || !is_array($values) || count($types) != count($values)) {
            throw new ilDatabaseException("ilDB::queryF: Types and values must be arrays of same size. ($query)");
        }
        $quoted_values = array();
        foreach ($types as $k => $t) {
            $quoted_values[] = $this->quote($values[$k], $t);
        }
        $query = vsprintf($query, $quoted_values);

        return $this->query($query);
    }


    /**
     * @param $query  string
     * @param $types  string[]
     * @param $values mixed[]
     * @return string
     * @throws ilDatabaseException
     */
    public function manipulateF($query, $types, $values)
    {
        if (!is_array($types) || !is_array($values) || count($types) != count($values)) {
            throw new ilDatabaseException("ilDB::manipulateF: types and values must be arrays of same size. ($query)");
        }
        $quoted_values = array();
        foreach ($types as $k => $t) {
            $quoted_values[] = $this->quote($values[$k], $t);
        }
        $query = vsprintf($query, $quoted_values);

        return $this->manipulate($query);
    }


    /**
     * @param $bool
     * @return bool
     *
     * TODO
     */
    public function useSlave($bool)
    {
        return false;
    }


    /**
     * Set the Limit for the next Query.
     *
     * @param $limit
     * @param $offset
     */
    public function setLimit($limit, $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }


    /**
     * @param string $column
     * @param string $type
     * @param string $value
     * @param bool $case_insensitive
     * @return string
     * @throws \ilDatabaseException
     */
    public function like($column, $type, $value = "?", $case_insensitive = true)
    {
        return $this->manager->getQueryUtils()->like($column, $type, $value, $case_insensitive);
    }


    /**
     * @return string the now statement
     */
    public function now()
    {
        return $this->manager->getQueryUtils()->now();
    }


    /**
     * Replace into method.
     *
     * @param    string        table name
     * @param    array         primary key values: array("field1" => array("text", $name), "field2" => ...)
     * @param    array         other values: array("field1" => array("text", $name), "field2" => ...)
     * @return string
     */
    public function replace($table, $primaryKeys, $otherColumns)
    {
        $a_columns = array_merge($primaryKeys, $otherColumns);
        $fields = array();
        $field_values = array();
        $placeholders = array();
        $types = array();
        $values = array();

        foreach ($a_columns as $k => $col) {
            $fields[] = $k;
            $placeholders[] = "%s";
            $placeholders2[] = ":$k";
            $types[] = $col[0];

            // integer auto-typecast (this casts bool values to integer)
            if ($col[0] == 'integer' && !is_null($col[1])) {
                $col[1] = (int) $col[1];
            }

            $values[] = $col[1];
            $field_values[$k] = $col[1];
        }

        $q = "REPLACE INTO " . $table . " (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")";

        $r = $this->manipulateF($q, $types, $values);

        return $r;
    }


    /**
     * @param $columns
     * @param $value
     * @param $type
     * @param bool $emptyOrNull
     * @return string
     */
    public function equals($columns, $value, $type, $emptyOrNull = false)
    {
        if (!$emptyOrNull || $value != "") {
            return $columns . " = " . $this->quote($value, $type);
        } else {
            return "(" . $columns . " = '' OR $columns IS NULL)";
        }
    }


    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }


    /**
     * @return string
     */
    public function getDbname()
    {
        return $this->dbname;
    }


    /**
     * @param string $dbname
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }


    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }


    /**
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }


    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }


    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }


    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }


    /**
     * @param $user
     */
    public function setDBUser($user)
    {
        $this->setUsername($user);
    }


    /**
     * @param $port
     */
    public function setDBPort($port)
    {
        $this->setPort($port);
    }


    /**
     * @param $password
     */
    public function setDBPassword($password)
    {
        $this->setPassword($password);
    }


    /**
     * @param $host
     */
    public function setDBHost($host)
    {
        $this->setHost($host);
    }


    /**
     * @param $a_exp
     * @return string
     */
    public function upper($a_exp)
    {
        return " UPPER(" . $a_exp . ") ";
    }


    /**
     * @param $a_exp
     * @return string
     */
    public function lower($a_exp)
    {
        return " LOWER(" . $a_exp . ") ";
    }


    /**
     * @param $a_exp
     * @param int $a_pos
     * @param int $a_len
     * @return string
     */
    public function substr($a_exp, $a_pos = 1, $a_len = -1)
    {
        $lenstr = "";
        if ($a_len > -1) {
            $lenstr = ", " . $a_len;
        }

        return " SUBSTR(" . $a_exp . ", " . $a_pos . $lenstr . ") ";
    }


    /**
     * @param $query
     * @param null $types
     * @return \ilPDOStatement
     */
    public function prepareManip($query, $types = null)
    {
        return new ilPDOStatement($this->pdo->prepare($query));
    }


    /**
     * @param $query
     * @param null $types
     * @param null $result_types
     * @return \ilPDOStatement
     */
    public function prepare($query, $types = null, $result_types = null)
    {
        return new ilPDOStatement($this->pdo->prepare($query));
    }


    /**
     * @param $a_status
     */
    public function enableResultBuffering($a_status)
    {
        $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $a_status);
    }


    /**
     * @param $stmt
     * @param array $data
     * @throws ilDatabaseException
     * @return ilDBStatement
     */
    public function execute($stmt, $data = array())
    {
        /**
         * @var $stmt ilPDOStatement
         */
        $result = $stmt->execute($data);
        if ($result === false) {
            throw new ilDatabaseException(implode(', ', $stmt->errorInfo()), $stmt->errorCode());
        }
        return $stmt;
    }


    /**
     * @return bool
     */
    public function supportsSlave()
    {
        return false;
    }


    /**
     * @return bool
     */
    public function supportsFulltext()
    {
        return false;
    }


    /**
     * @return bool
     */
    public function supportsTransactions()
    {
        return false;
    }


    /**
     * @param $feature
     * @return bool
     */
    public function supports($feature)
    {
        switch ($feature) {
            case self::FEATURE_TRANSACTIONS:
                return $this->supportsTransactions();
            case self::FEATURE_FULLTEXT:
                return $this->supportsFulltext();
            case self::FEATURE_SLAVE:
                return $this->supportsSlave();
            default:
                return false;
        }
    }


    /**
     * @return array
     */
    public function listTables()
    {
        return $this->manager->listTables();
    }


    /**
     * @param $module
     * @return \ilDBPdoManager|\ilDBPdoReverse
     */
    public function loadModule($module)
    {
        switch ($module) {
            case ilDBConstants::MODULE_MANAGER:
                return $this->manager;
            case ilDBConstants::MODULE_REVERSE:
                return $this->reverse;
        }
    }


    /**
     * @inheritdoc
     */
    public function getAllowedAttributes()
    {
        return $this->field_definition->getAllowedAttributes();
    }


    /**
     * @param $sequence
     * @return bool
     */
    public function sequenceExists($sequence)
    {
        return in_array($sequence, $this->listSequences());
    }


    /**
     * @return array
     */
    public function listSequences()
    {
        return $this->manager->listSequences();
    }


    /**
     * @param array $values
     * @param bool $allow_null
     * @return string
     */
    public function concat(array $values, $allow_null = true)
    {
        return $this->manager->getQueryUtils()->concat($values, $allow_null);
    }


    /**
     * @param $query
     * @return string
     */
    protected function appendLimit($query)
    {
        if ($this->limit !== null && $this->offset !== null) {
            $query .= ' LIMIT ' . (int) $this->offset . ', ' . (int) $this->limit;
            $this->limit = null;
            $this->offset = null;

            return $query;
        }

        return $query;
    }


    /**
     * @param $a_needle
     * @param $a_string
     * @param int $a_start_pos
     * @return string
     */
    public function locate($a_needle, $a_string, $a_start_pos = 1)
    {
        return $this->manager->getQueryUtils()->locate($a_needle, $a_string, $a_start_pos);
    }


    /**
     * @param $table
     * @param $a_column
     * @param $a_attributes
     * @return bool
     * @throws \ilDatabaseException
     */
    public function modifyTableColumn($table, $a_column, $a_attributes)
    {
        $def = $this->reverse->getTableFieldDefinition($table, $a_column);

        $analyzer = new ilDBAnalyzer($this);
        $best_alt = $analyzer->getBestDefinitionAlternative($def);
        $def = $def[$best_alt];
        unset($def["nativetype"]);
        unset($def["mdb2type"]);

        // check attributes
        $ilDBPdoFieldDefinition = $this->field_definition;

        $type = ($a_attributes["type"] != "") ? $a_attributes["type"] : $def["type"];
        foreach ($def as $k => $v) {
            if ($k != "type" && !$ilDBPdoFieldDefinition->isAllowedAttribute($k, $type)) {
                unset($def[$k]);
            }
        }
        $check_array = $def;
        foreach ($a_attributes as $k => $v) {
            $check_array[$k] = $v;
        }
        if (!$this->checkColumnDefinition($check_array, true)) {
            throw new ilDatabaseException("ilDB Error: modifyTableColumn(" . $table . ", " . $a_column . ")");
        }

        foreach ($a_attributes as $a => $v) {
            $def[$a] = $v;
        }

        $a_attributes["definition"] = $def;

        $changes = array(
            "change" => array(
                $a_column => $a_attributes,
            ),
        );

        return $this->manager->alterTable($table, $changes, false);
    }


    /**
     * @param ilPDOStatement $a_st
     * @return bool
     */
    public function free($a_st)
    {
        /**
         * @var $a_st PDOStatement
         */
        return $a_st->closeCursor();
    }


    /**
     * @param $a_name
     * @param $a_new_name
     * @return bool
     * @throws \ilDatabaseException
     */
    public function renameTable($a_name, $a_new_name)
    {
        // check table name
        try {
            $this->checkTableName($a_new_name);
        } catch (ilDatabaseException $e) {
            throw new ilDatabaseException("ilDB Error: renameTable(" . $a_name . "," . $a_new_name . ")<br />" . $e->getMessage());
        }

        $this->manager->alterTable($a_name, array("name" => $a_new_name), false);

        // The abstraction_progress is no longer used in ILIAS, see http://www.ilias.de/mantis/view.php?id=19513
        //		$query = "UPDATE abstraction_progress " . "SET table_name = " . $this->quote($a_new_name, 'text') . " " . "WHERE table_name = "
        //		         . $this->quote($a_name, 'text');
        //		$this->pdo->query($query);

        return true;
    }


    /**
     * @param $a_name
     * @return bool
     * @throws \ilDatabaseException
     */
    public function checkTableName($a_name)
    {
        return $this->field_definition->checkTableName($a_name);
    }


    /**
     * @param $a_word
     * @return bool
     */
    public static function isReservedWord($a_word)
    {
        require_once('./Services/Database/classes/PDO/FieldDefinition/class.ilDBPdoMySQLFieldDefinition.php');
        global $DIC;
        $ilDBPdoMySQLFieldDefinition = new ilDBPdoMySQLFieldDefinition($DIC->database());

        return $ilDBPdoMySQLFieldDefinition->isReserved($a_word);
    }


    /**
     * @return bool
     * @throws \ilDatabaseException
     */
    public function beginTransaction()
    {
        if (!$this->supports(self::FEATURE_TRANSACTIONS)) {
            throw new ilDatabaseException("ilDB::beginTransaction: Transactions are not supported.");
        }

        return $this->pdo->beginTransaction();
    }


    /**
     * @return bool
     * @throws \ilDatabaseException
     */
    public function commit()
    {
        if (!$this->supports(self::FEATURE_TRANSACTIONS)) {
            throw new ilDatabaseException("ilDB::beginTransaction: Transactions are not supported.");
        }

        return $this->pdo->commit();
    }


    /**
     * @return bool
     * @throws \ilDatabaseException
     */
    public function rollback()
    {
        if (!$this->supports(self::FEATURE_TRANSACTIONS)) {
            throw new ilDatabaseException("ilDB::beginTransaction: Transactions are not supported.");
        }

        return $this->pdo->rollBack();
    }


    /**
     * @param $a_table
     * @param string $a_name
     * @return mixed
     */
    public function dropIndex($a_table, $a_name = "i1")
    {
        return $this->manager->dropIndex($a_table, $a_name);
    }


    /**
     * @param $storage_engine
     */
    public function setStorageEngine($storage_engine)
    {
        $this->storage_engine = $storage_engine;
    }


    /**
     * @return string
     */
    public function getStorageEngine()
    {
        return $this->storage_engine;
    }


    /**
     * @param $query
     * @param int $type
     * @param int $colnum
     * @return array
     */
    public function queryCol($query, $type = PDO::FETCH_ASSOC, $colnum = 0)
    {
        switch ($type) {
            case ilDBConstants::FETCHMODE_ASSOC:
                $type = PDO::FETCH_ASSOC;
                break;
            case ilDBConstants::FETCHMODE_OBJECT:
                $type = PDO::FETCH_OBJ;
                break;
            default:
                $type = PDO::FETCH_ASSOC;
                break;
        }

        return $this->pdo->query($query, PDO::FETCH_ASSOC)->fetchAll(PDO::FETCH_COLUMN, $colnum);
    }


    /**
     * @param $query
     * @param null $types
     * @param int $fetchmode
     * @return arary
     */
    public function queryRow($query, $types = null, $fetchmode = ilDBConstants::FETCHMODE_DEFAULT)
    {
        switch ($fetchmode) {
            case ilDBConstants::FETCHMODE_ASSOC:
                $type = PDO::FETCH_ASSOC;
                break;
            case ilDBConstants::FETCHMODE_OBJECT:
                $type = PDO::FETCH_OBJ;
                break;
            default:
                $type = PDO::FETCH_ASSOC;
                break;
        }

        return $this->pdo->query($query, $type)->fetch();
    }


    /**
     * @param bool $native
     * @return string
     */
    public function getServerVersion($native = false)
    {
        return $this->pdo->query('SELECT VERSION()')->fetchColumn();
    }


    /**
     * @param $value
     * @param bool $escape_wildcards
     * @return string
     */
    public function escape($value, $escape_wildcards = false)
    {
        return $value;
    }


    /**
     * @param $text
     * @return string
     */
    public function escapePattern($text)
    {
        return $text;
    }


    /**
     * @param string $engine
     * @return array
     */
    public function migrateAllTablesToEngine($engine = ilDBConstants::MYSQL_ENGINE_INNODB)
    {
        return array();
    }


    /**
     * @inheritDoc
     */
    public function migrateAllTablesToCollation($collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4)
    {
        return array();
    }


    /**
     * @inheritDoc
     */
    public function supportsCollationMigration()
    {
        return false;
    }


    /**
     * @return bool
     */
    public function supportsEngineMigration()
    {
        return false;
    }


    /**
     * @param $name
     * @return bool
     * @throws \ilDatabaseException
     */
    public function checkIndexName($name)
    {
        return $this->getFieldDefinition()->checkIndexName($name);
    }


    /**
     * @param $table
     * @param $fields
     * @param string $name
     * @return bool
     * @throws \ilDatabaseException
     */
    public function addUniqueConstraint($table, $fields, $name = "con")
    {
        assert(is_array($fields));
        $manager = $this->manager;

        // check index name
        if (!$this->checkIndexName($name)) {
            throw new ilDatabaseException("ilDB Error: addUniqueConstraint(" . $table . "," . $name . ")");
        }

        $fields_corrected = array();
        foreach ($fields as $f) {
            $fields_corrected[$f] = array();
        }
        $definition = array(
            'unique' => true,
            'fields' => $fields_corrected,
        );

        return $manager->createConstraint($table, $this->constraintName($table, $name), $definition);
    }


    /**
     * @param $a_table
     * @param string $a_name
     * @return mixed
     */
    public function dropUniqueConstraint($a_table, $a_name = "con")
    {
        return $this->manager->dropConstraint($a_table, $this->constraintName($a_table, $a_name), false);
    }


    /**
     * @param $a_table
     * @param $a_fields
     * @return bool|mixed
     */
    public function dropUniqueConstraintByFields($a_table, $a_fields)
    {
        $analyzer = new ilDBAnalyzer();
        $cons = $analyzer->getConstraintsInformation($a_table);
        foreach ($cons as $c) {
            if ($c["type"] == "unique" && count($a_fields) == count($c["fields"])) {
                $all_in = true;
                foreach ($a_fields as $f) {
                    if (!isset($c["fields"][$f])) {
                        $all_in = false;
                    }
                }
                if ($all_in) {
                    return $this->dropUniqueConstraint($a_table, $c['name']);
                }
            }
        }

        return false;
    }


    /**
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }


    /**
     * @return \ilAtomQuery
     */
    public function buildAtomQuery()
    {
        require_once('./Services/Database/classes/Atom/class.ilAtomQueryLock.php');

        return new ilAtomQueryLock($this);
    }


    /**
     * @param $table
     * @param array $fields
     * @return bool
     */
    public function uniqueConstraintExists($table, array $fields)
    {
        require_once('./Services/Database/classes/class.ilDBAnalyzer.php');
        $analyzer = new ilDBAnalyzer();
        $cons = $analyzer->getConstraintsInformation($table);
        foreach ($cons as $c) {
            if ($c["type"] == "unique" && count($fields) == count($c["fields"])) {
                $all_in = true;
                foreach ($fields as $f) {
                    if (!isset($c["fields"][$f])) {
                        $all_in = false;
                    }
                }
                if ($all_in) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * @param $table_name
     * @return bool
     */
    public function dropPrimaryKey($table_name)
    {
        return $this->manager->dropConstraint($table_name, "PRIMARY", true);
    }


    /**
     * @param $stmt
     * @param $a_data
     */
    public function executeMultiple($stmt, $a_data)
    {
        for ($i = 0, $j = count($a_data); $i < $j; $i++) {
            $stmt->execute($a_data[$i]);
        }
    }


    /**
     * @param $a_expr
     * @param bool $a_to_text
     * @return string
     */
    public function fromUnixtime($a_expr, $a_to_text = true)
    {
        return "FROM_UNIXTIME(" . $a_expr . ")";
    }


    /**
     * @return string
     */
    public function unixTimestamp()
    {
        return "UNIX_TIMESTAMP()";
    }


    /**
     * Generate an insert, update or delete query and call prepare() and execute() on it
     *
     * @param string $tablename of the table
     * @param array $fields ($key=>$value) where $key is a field name and $value its value
     * @param int $mode of query to build
     *                          ilDBConstants::AUTOQUERY_INSERT
     *                          ilDBConstants::AUTOQUERY_UPDATE
     *                          ilDBConstants::AUTOQUERY_DELETE
     *                          ilDBConstants::AUTOQUERY_SELECT
     * @param bool $where (in case of update and delete queries, this string will be put after the sql WHERE statement)
     *
     * @deprecated Will be removed in ILIAS 5.3
     * @return bool
     */
    public function autoExecute($tablename, $fields, $mode = ilDBConstants::AUTOQUERY_INSERT, $where = false)
    {
        $fields_values = (array) $fields;
        if ($mode == ilDBConstants::AUTOQUERY_INSERT) {
            if (!empty($fields_values)) {
                $keys = $fields_values;
            } else {
                $keys = array();
            }
        } else {
            $keys = array_keys($fields_values);
        }
        $params = array_values($fields_values);
        if (empty($params)) {
            $query = $this->buildManipSQL($tablename, $keys, $mode, $where);
            $result = $this->pdo->query($query);
        } else {
            $stmt = $this->autoPrepare($tablename, $keys, $mode, $where, $types, $result_types);
            $this->execute($stmt);
            $this->free($stmt);
            $result = $stmt;
        }

        return $result;
    }


    /**
     * @param $table
     * @param $table_fields
     * @param int $mode
     * @param bool $where
     * @param null $types
     * @param bool $result_types
     * @return string
     */
    protected function autoPrepare($table, $table_fields, $mode = ilDBConstants::AUTOQUERY_INSERT, $where = false, $types = null, $result_types = ilDBConstants::PREPARE_MANIP)
    {
        $query = $this->buildManipSQL($table, $table_fields, $mode, $where);

        return $this->prepare($query, $types, $result_types);
    }


    /**
     * @param $table
     * @param $table_fields
     * @param $mode
     * @param bool $where
     * @return string
     * @throws \ilDatabaseException
     */
    protected function buildManipSQL($table, $table_fields, $mode, $where = false)
    {
        if ($this->options['quote_identifier']) {
            $table = $this->quoteIdentifier($table);
        }

        if (!empty($table_fields) && $this->options['quote_identifier']) {
            foreach ($table_fields as $key => $field) {
                $table_fields[$key] = $this->quoteIdentifier($field);
            }
        }

        if ($where !== false && !is_null($where)) {
            if (is_array($where)) {
                $where = implode(' AND ', $where);
            }
            $where = ' WHERE ' . $where;
        }

        switch ($mode) {
            case ilDBConstants::AUTOQUERY_INSERT:
                if (empty($table_fields)) {
                    throw new ilDatabaseException('Insert requires table fields');
                }
                $cols = implode(', ', $table_fields);
                $values = '?' . str_repeat(', ?', (count($table_fields) - 1));

                return 'INSERT INTO ' . $table . ' (' . $cols . ') VALUES (' . $values . ')';
                break;
            case ilDBConstants::AUTOQUERY_UPDATE:
                if (empty($table_fields)) {
                    throw new ilDatabaseException('Update requires table fields');
                }
                $set = implode(' = ?, ', $table_fields) . ' = ?';
                $sql = 'UPDATE ' . $table . ' SET ' . $set . $where;

                return $sql;
                break;
            case ilDBConstants::AUTOQUERY_DELETE:
                $sql = 'DELETE FROM ' . $table . $where;

                return $sql;
                break;
            case ilDBConstants::AUTOQUERY_SELECT:
                $cols = !empty($table_fields) ? implode(', ', $table_fields) : '*';
                $sql = 'SELECT ' . $cols . ' FROM ' . $table . $where;

                return $sql;
                break;
        }

        throw new ilDatabaseException('Syntax error');
    }


    /**
     * @return string
     * @throws ilDatabaseException
     */
    public function getDBVersion()
    {
        $d = $this->fetchObject($this->query("SELECT VERSION() AS version"));

        return ($d->version ? $d->version : 'Unknown');
    }


    /**
     * @inheritdoc
     */
    public function sanitizeMB4StringIfNotSupported($query)
    {
        if (!$this->doesCollationSupportMB4Strings()) {
            $query_replaced = preg_replace(
                '/[\x{10000}-\x{10FFFF}]/u',
                ilDBConstants::MB4_REPLACEMENT,
                $query
            );
            if (!empty($query_replaced)) {
                return $query_replaced;
            }
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function doesCollationSupportMB4Strings()
    {
        return false;
    }


    /**
     * @inheritdoc
     */
    public function groupConcat($a_field_name, $a_seperator = ",", $a_order = null)
    {
        return $this->manager->getQueryUtils()->groupConcat($a_field_name, $a_seperator, $a_order);
    }

    /**
     * @inheritdoc
     */
    public function cast($a_field_name, $a_dest_type)
    {
        return $this->manager->getQueryUtils()->cast($a_field_name, $a_dest_type);
    }
}
