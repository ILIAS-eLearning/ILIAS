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
 * Class pdoDB
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdo implements ilDBInterface, ilDBPdoInterface
{
    public array $options = [];
    const FEATURE_TRANSACTIONS = 'transactions';
    const FEATURE_FULLTEXT = 'fulltext';
    const FEATURE_SLAVE = 'slave';
    protected string $host = '';
    protected string $dbname = '';
    protected string $charset = 'utf8';
    protected string $username = '';
    protected string $password = '';
    protected int $port = 3306;
    protected ?PDO $pdo = null;
    protected ilDBPdoManager $manager;
    protected ilDBPdoReverse $reverse;
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected string $storage_engine = 'InnoDB';
    protected string $dsn = '';
    /**
     * @var int[]
     */
    protected array $attributes = array(
        //		PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    );
    protected string $db_type = '';
    protected int $error_code = 0;
    protected ?\ilDBPdoFieldDefinition $field_definition = null;

    /**
     * @throws \Exception
     */
    public function connect(bool $return_false_for_error = false) : ?bool
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

        return ($this->pdo->errorCode() === PDO::ERR_NONE);
    }

    abstract public function initHelpers() : void;

    protected function initSQLMode() : void
    {
    }

    protected function getAttributes() : array
    {
        $options = $this->attributes;
        foreach ($this->getAdditionalAttributes() as $k => $v) {
            $options[$k] = $v;
        }

        return $options;
    }

    protected function getAdditionalAttributes() : array
    {
        return array();
    }

    public function getFieldDefinition() : ?\ilDBPdoFieldDefinition
    {
        return $this->field_definition;
    }

    public function setFieldDefinition(\ilDBPdoFieldDefinition $field_definition) : void
    {
        $this->field_definition = $field_definition;
    }

    public function createDatabase(string $a_name, string $a_charset = "utf8", string $a_collation = "") : bool
    {
        $this->setDbname('');
        $this->generateDSN();
        $this->connect(true);
        try {
            $this->query($this->manager->getQueryUtils()->createDatabase($a_name, $a_charset, $a_collation));
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return string|int|null
     */
    public function getLastErrorCode()
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo->errorCode();
        }

        return $this->error_code;
    }

    public function initFromIniFile(?ilIniFile $ini = null) : void
    {
        global $DIC;

        if ($ini instanceof ilIniFile) {
            $clientIniFile = $ini;
        } elseif ($DIC->offsetExists('ilClientIniFile')) {
            $clientIniFile = $DIC['ilClientIniFile'];
        } else {
            throw new InvalidArgumentException('$tmpClientIniFile is not an instance of ilIniFile');
        }

        $this->setUsername($clientIniFile->readVariable("db", "user"));
        $this->setHost($clientIniFile->readVariable("db", "host"));
        $this->setPort((int) $clientIniFile->readVariable("db", "port"));
        $this->setPassword((string) $clientIniFile->readVariable("db", "pass"));
        $this->setDbname($clientIniFile->readVariable("db", "name"));
        $this->setDBType($clientIniFile->readVariable("db", "type"));

        $this->generateDSN();
    }

    public function generateDSN()
    {
        $port = $this->getPort() !== 0 ? ";port=" . $this->getPort() : "";
        $dbname = $this->getDbname() !== '' ? ';dbname=' . $this->getDbname() : '';
        $host = $this->getHost();
        $charset = ';charset=' . $this->getCharset();
        $this->dsn = 'mysql:host=' . $host . $port . $dbname . $charset;
    }

    public function quoteIdentifier(string $identifier, bool $check_option = false) : string
    {
        return '`' . $identifier . '`';
    }

    /**
     * @param $table_name string
     */
    abstract public function nextId(string $table_name) : int;

    /**
     * @throws \ilDatabaseException
     */
    public function createTable(
        string $table_name,
        array $fields,
        bool $drop_table = false,
        bool $ignore_erros = false
    ) : bool {
        // check table name
        if (!$ignore_erros && !$this->checkTableName($table_name)) {
            throw new ilDatabaseException("ilDB Error: createTable(" . $table_name . ")");
        }

        // check definition array
        if (!$ignore_erros && !$this->checkTableColumns($fields)) {
            throw new ilDatabaseException("ilDB Error: createTable(" . $table_name . ")");
        }

        if ($drop_table) {
            $this->dropTable($table_name, false);
        }

        return $this->manager->createTable($table_name, $fields, array());
    }

    protected function checkTableColumns(array $a_cols) : bool
    {
        foreach ($a_cols as $col => $def) {
            if (!$this->checkColumn($col, $def)) {
                return false;
            }
        }

        return true;
    }

    protected function checkColumn(string $a_col, array $a_def) : bool
    {
        if (!$this->checkColumnName($a_col)) {
            return false;
        }
        return $this->checkColumnDefinition($a_def);
    }

    protected function checkColumnDefinition(array $a_def, bool $a_modify_mode = false) : bool
    {
        return $this->field_definition->checkColumnDefinition($a_def);
    }

    public function checkColumnName(string $a_name) : bool
    {
        return $this->field_definition->checkColumnName($a_name);
    }

    /**
     * @throws \ilDatabaseException
     */
    public function addPrimaryKey(string $table_name, array $primary_keys) : bool
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
        $this->manager->createConstraint(
            $table_name,
            $this->constraintName($table_name, $this->getPrimaryKeyIdentifier()),
            $definition
        );

        return true;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function dropIndexByFields(string $table_name, array $fields) : bool
    {
        foreach ($this->manager->listTableIndexes($table_name) as $idx_name) {
            $def = $this->reverse->getTableIndexDefinition($table_name, $idx_name);
            $idx_fields = array_keys($def['fields']);

            if ($idx_fields === $fields) {
                return $this->dropIndex($table_name, $idx_name);
            }
        }

        return false;
    }

    public function getPrimaryKeyIdentifier() : string
    {
        return "PRIMARY";
    }

    public function createSequence(string $table_name, int $start = 1) : bool
    {
        $this->manager->createSequence($table_name, $start);
        return true;
    }

    public function tableExists(string $table_name) : bool
    {
        $result = $this->pdo->prepare("SHOW TABLES LIKE :table_name");
        $result->execute(array('table_name' => $table_name));
        $return = $result->rowCount();
        $result->closeCursor();

        return $return > 0;
    }

    public function tableColumnExists(string $table_name, string $column_name) : bool
    {
        $fields = $this->loadModule(ilDBConstants::MODULE_MANAGER)->listTableFields($table_name);

        return in_array($column_name, $fields, true);
    }

    /**
     * @throws \ilDatabaseException
     */
    public function addTableColumn(string $table_name, string $column_name, array $attributes) : bool
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
     * @throws \ilDatabaseException
     */
    public function dropTable(string $table_name, bool $error_if_not_existing = true) : bool
    {
        $ilDBPdoManager = $this->loadModule(ilDBConstants::MODULE_MANAGER);
        $tables = $ilDBPdoManager->listTables();
        $table_exists = in_array($table_name, $tables);
        if (!$table_exists && $error_if_not_existing) {
            throw new ilDatabaseException("Table $table_name does not exist");
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
     * @throws ilDatabaseException
     */
    public function query(string $query) : ilDBStatement
    {
        global $DIC;
        $ilBench = $DIC['ilBench'] ?? null;

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
            throw new ilDatabaseException($e->getMessage() . ' QUERY: ' . $query, (int) $e->getCode());
        }

        $err = $this->pdo->errorCode();
        if ($err !== PDO::ERR_NONE) {
            $info = $this->pdo->errorInfo();
            $info_message = $info[2];
            throw new ilDatabaseException($info_message . ' QUERY: ' . $query);
        }

        return new ilPDOStatement($res);
    }

    public function fetchAll(ilDBStatement $statement, int $fetch_mode = ilDBConstants::FETCHMODE_ASSOC) : array
    {
        $return = [];
        while ($data = $statement->fetch($fetch_mode)) {
            $return[] = $data;
        }

        return $return;
    }

    public function dropSequence(string $table_name) : bool
    {
        $this->manager->dropSequence($table_name);
        return true;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function dropTableColumn(string $table_name, string $column_name) : bool
    {
        $changes = array(
            "remove" => array(
                $column_name => array(),
            ),
        );

        return $this->manager->alterTable($table_name, $changes, false);
    }

    /**
     * @throws \ilDatabaseException
     */
    public function renameTableColumn(string $table_name, string $column_old_name, string $column_new_name) : bool
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

    public function insert(string $table_name, array $values) : int
    {
        $real = array();
        $fields = array();
        foreach ($values as $key => $val) {
            $real[] = $this->quote($val[1], $val[0]);
            $fields[] = $this->quoteIdentifier($key);
        }
        $values_string = implode(",", $real);
        $fields_string = implode(",", $fields);
        $query = "INSERT INTO " . $this->quoteIdentifier($table_name) . " (" . $fields_string . ") VALUES (" . $values_string . ")";

        $query = $this->sanitizeMB4StringIfNotSupported($query);

        return (int) $this->pdo->exec($query);
    }

    public function fetchObject(ilDBStatement $query_result) : ?stdClass
    {
        $res = $query_result->fetchObject();
        if ($res === null) {
            $query_result->closeCursor();

            return null;
        }

        return $res;
    }

    public function update(string $table_name, array $columns, array $where) : int
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

            if (($col[0] === "blob" || $col[0] === "clob" || $col[0] === 'text') && is_string($field_value)) {
                $field_value = $this->sanitizeMB4StringIfNotSupported($field_value);
            }

            // integer auto-typecast (this casts bool values to integer)
            if ($col[0] === 'integer' && !is_null($field_value)) {
                $field_value = (int) $field_value;
            }

            $values[] = $field_value;
            $field_values[$k] = $field_value;
            if ($col[0] === "blob" || $col[0] === "clob") {
                $lobs = true;
            }
        }

        if ($lobs) {
            $q = "UPDATE " . $this->quoteIdentifier($table_name) . " SET ";
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

            $num_affected_rows = $r->rowCount();

            $this->free($r);
        } else {
            foreach ($where as $k => $col) {
                $types[] = $col[0];
                $values[] = $col[1];
                $field_values[$k] = $col;
            }
            $q = "UPDATE " . $this->quoteIdentifier($table_name) . " SET ";
            $lim = "";
            foreach ($fields as $k => $field) {
                $q .= $lim . $this->quoteIdentifier($field) . " = " . $placeholders[$k];
                $lim = ", ";
            }
            $q .= " WHERE ";
            $lim = "";
            foreach (array_keys($where) as $k) {
                $q .= $lim . $k . " = %s";
                $lim = " AND ";
            }

            $num_affected_rows = $this->manipulateF($q, $types, $values);
        }
        
        return $num_affected_rows;
    }

    /**
     * @throws ilDatabaseException
     */
    public function manipulate(string $query) : int
    {
        global $DIC;
        $ilBench = $DIC['ilBench'] ?? null;
        try {
            $query = $this->sanitizeMB4StringIfNotSupported($query);
            if ($ilBench instanceof ilBenchmark) {
                $ilBench->startDbBench($query);
            }
            $num_affected_rows = $this->pdo->exec($query);
            if ($ilBench instanceof ilBenchmark) {
                $ilBench->stopDbBench();
            }
        } catch (PDOException $e) {
            throw new ilDatabaseException($e->getMessage() . ' QUERY: ' . $query, (int) $e->getCode());
        }

        return (int) $num_affected_rows;
    }

    public function fetchAssoc(ilDBStatement $statement) : ?array
    {
        $res = $statement->fetch(PDO::FETCH_ASSOC);
        if ($res === null || $res === false) {
            $statement->closeCursor();

            return null;
        }

        return $res;
    }

    public function numRows(ilDBStatement $statement) : int
    {
        return $statement->rowCount();
    }

    public function quote($value, ?string $type = null) : string
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
                if ($value === $this->now()) {
                    return $value;
                }
                $value = (string) $value;
                break;
            case ilDBConstants::T_INTEGER:
                return (string) (int) $value;
            case ilDBConstants::T_FLOAT:
                $pdo_type = PDO::PARAM_INT;
                $value = (string) $value;
                break;
            case ilDBConstants::T_TEXT:
            default:
                $value = (string) $value;
                $pdo_type = PDO::PARAM_STR;
                break;
        }

        return $this->pdo->quote((string) $value, $pdo_type);
    }

    public function indexExistsByFields(string $table_name, array $fields) : bool
    {
        foreach ($this->manager->listTableIndexes($table_name) as $idx_name) {
            $def = $this->reverse->getTableIndexDefinition($table_name, $idx_name);
            $idx_fields = array_keys($def['fields']);

            if ($idx_fields === $fields) {
                return true;
            }
        }

        return false;
    }

    public function addIndex(string $table_name, array $fields, string $index_name = '', bool $fulltext = false) : bool
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
        } elseif ($this->supportsFulltext()) {
            $this->addFulltextIndex($table_name, $fields, $index_name);
            // TODO
        }

        return true;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function addFulltextIndex(string $table, array $fields, string $a_name = "in") : bool
    {
        $i_name = $this->constraintName($table, $a_name) . "_idx";
        $f_str = implode(",", $fields);
        $q = "ALTER TABLE $table ADD FULLTEXT $i_name ($f_str)";
        $this->query($q);
        return true;
    }

    /**
     * Drop fulltext index
     */
    public function dropFulltextIndex(string $a_table, string $a_name) : bool
    {
        $i_name = $this->constraintName($a_table, $a_name) . "_idx";
        $this->query("ALTER TABLE $a_table DROP FULLTEXT $i_name");
        return true;
    }

    /**
     * Is index a fulltext index?
     */
    public function isFulltextIndex(string $a_table, string $a_name) : bool
    {
        $set = $this->query("SHOW INDEX FROM " . $a_table);
        while ($rec = $this->fetchAssoc($set)) {
            if ($rec["Key_name"] === $a_name && $rec["Index_type"] === "FULLTEXT") {
                return true;
            }
        }

        return false;
    }

    public function getIndexName(string $index_name_base) : string
    {
        return sprintf(ilDBPdoFieldDefinition::INDEX_FORMAT, preg_replace('/[^a-z0-9_\$]/i', '_', $index_name_base));
    }

    public function getSequenceName(string $table_name) : string
    {
        return sprintf(ilDBPdoFieldDefinition::SEQUENCE_FORMAT, preg_replace('/[^a-z0-9_\$.]/i', '_', $table_name));
    }

    /**
     * Determine contraint name by table name and constraint name.
     * In MySQL these are "unique" per table
     */
    public function constraintName(string $a_table, string $a_constraint) : string
    {
        return $a_constraint;
    }

    public function getDSN() : string
    {
        return $this->dsn;
    }

    public function getDBType() : string
    {
        return $this->db_type;
    }

    public function setDBType(string $type) : void
    {
        $this->db_type = $type;
    }

    /**
     * @return string[]
     *
     * @deprecated use
     */
    public static function getReservedWords() : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        /**
         * @var $ilDB ilDBPdo
         */
        $fd = $ilDB->getFieldDefinition();
        if ($fd !== null) {
            return $fd->getReservedMysql();
        }
        return [];
    }

    /**
     * @deprecated Use ilAtomQuery instead
     */
    public function lockTables(array $tables) : void
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
     * @throws \ilDatabaseException
     * @deprecated Use ilAtomQuery instead
     */
    public function unlockTables() : void
    {
        $this->pdo->exec($this->manager->getQueryUtils()->unlock());
    }

    public function in(string $field, array $values, bool $negate = false, string $type = "") : string
    {
        return $this->manager->getQueryUtils()->in($field, $values, $negate, $type);
    }

    /**
     * @param string[] $types
     * @throws \ilDatabaseException
     */
    public function queryF(string $query, array $types, array $values) : ilDBStatement
    {
        if (!is_array($types) || !is_array($values) || count($types) !== count($values)) {
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
     * @param string[] $types
     * @throws ilDatabaseException
     */
    public function manipulateF(string $query, array $types, array $values) : int
    {
        if (!is_array($types) || !is_array($values) || count($types) !== count($values)) {
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
     * TODO
     */
    public function useSlave(bool $bool) : bool
    {
        return false;
    }

    /**
     * Set the Limit for the next Query.
     */
    public function setLimit(int $limit, int $offset = 0) : void
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function like(string $column, string $type, string $value = "?", bool $case_insensitive = true) : string
    {
        return $this->manager->getQueryUtils()->like($column, $type, $value, $case_insensitive);
    }

    /**
     * @return string the now statement
     */
    public function now() : string
    {
        return $this->manager->getQueryUtils()->now();
    }

    public function replace(string $table, array $primary_keys, array $other_columns) : int
    {
        $a_columns = array_merge($primary_keys, $other_columns);
        $fields = [];
        $placeholders = [];
        $types = [];
        $values = [];

        foreach ($a_columns as $k => $col) {
            $fields[] = $k;
            $placeholders[] = "%s";
            $placeholders2[] = ":$k";
            $types[] = $col[0];

            // integer auto-typecast (this casts bool values to integer)
            if ($col[0] === 'integer' && !is_null($col[1])) {
                $col[1] = (int) $col[1];
            }

            $values[] = $col[1];
        }

        $q = "REPLACE INTO " . $table . " (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")";

        return $this->manipulateF($q, $types, $values);
    }

    /**
     * @param mixed $value
     */
    public function equals(string $columns, $value, string $type, bool $emptyOrNull = false) : string
    {
        if (!$emptyOrNull || $value != "") {
            return $columns . " = " . $this->quote($value, $type);
        }

        return "(" . $columns . " = '' OR $columns IS NULL)";
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function setHost(string $host) : void
    {
        $this->host = $host;
    }

    public function getDbname() : string
    {
        return $this->dbname;
    }

    public function setDbname(string $dbname) : void
    {
        $this->dbname = $dbname;
    }

    public function getCharset() : string
    {
        return $this->charset;
    }

    public function setCharset(string $charset) : void
    {
        $this->charset = $charset;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function setUsername(string $username) : void
    {
        $this->username = $username;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword(string $password) : void
    {
        $this->password = $password;
    }

    public function getPort() : int
    {
        return $this->port;
    }

    public function setPort(int $port) : void
    {
        $this->port = $port;
    }

    public function setDBUser(string $user) : void
    {
        $this->setUsername($user);
    }

    public function setDBPort(int $port) : void
    {
        $this->setPort($port);
    }

    public function setDBPassword(string $password) : void
    {
        $this->setPassword($password);
    }

    public function setDBHost(string $host) : void
    {
        $this->setHost($host);
    }

    /**
     * @param string $a_exp
     */
    public function upper(string $expression) : string
    {
        return " UPPER(" . $expression . ") ";
    }

    /**
     * @param string $a_exp
     */
    public function lower(string $expression) : string
    {
        return " LOWER(" . $expression . ") ";
    }

    public function substr(string $a_exp, int $a_pos = 1, int $a_len = -1) : string
    {
        $lenstr = "";
        if ($a_len > -1) {
            $lenstr = ", " . $a_len;
        }
        return " SUBSTR(" . $a_exp . ", " . $a_pos . $lenstr . ") ";
    }

    public function prepareManip(string $query, ?array $types = null) : ilDBStatement
    {
        return new ilPDOStatement($this->pdo->prepare($query));
    }

    public function prepare(string $query, ?array $types = null, ?array $result_types = null) : ilDBStatement
    {
        return new ilPDOStatement($this->pdo->prepare($query));
    }

    public function enableResultBuffering(bool $a_status) : void
    {
        $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $a_status);
    }

    /**
     * @throws ilDatabaseException
     */
    public function execute(ilDBStatement $stmt, array $data = []) : ilDBStatement
    {
        /**
         * @var $stmt ilPDOStatement
         */
        $result = $stmt->execute($data);
        if ($result === false) {//This may not work since execute returns an object
            throw new ilDatabaseException(implode(', ', $stmt->errorInfo()), (int) $stmt->errorCode());
        }
        return $stmt;
    }

    public function supportsSlave() : bool
    {
        return false;
    }

    public function supportsFulltext() : bool
    {
        return false;
    }

    public function supportsTransactions() : bool
    {
        return false;
    }

    public function supports(string $feature) : bool
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
     * @return int[]|string[]
     */
    public function listTables() : array
    {
        return $this->manager->listTables();
    }

    /**
     * @return \ilDBPdoManager|\ilDBPdoReverse
     */
    public function loadModule(string $module)
    {
        switch ($module) {
            case ilDBConstants::MODULE_MANAGER:
                return $this->manager;
            case ilDBConstants::MODULE_REVERSE:
                return $this->reverse;
        }
        throw new LogicException('module "' . $module . '" not available');
    }

    /**
     * @inheritdoc
     */
    public function getAllowedAttributes() : array
    {
        return $this->field_definition->getAllowedAttributes();
    }

    public function sequenceExists(string $sequence) : bool
    {
        return in_array($sequence, $this->listSequences(), true);
    }

    public function listSequences() : array
    {
        return $this->manager->listSequences();
    }

    public function concat(array $values, bool $allow_null = true) : string
    {
        return $this->manager->getQueryUtils()->concat($values, $allow_null);
    }

    protected function appendLimit(string $query) : string
    {
        if ($this->limit !== null && $this->offset !== null) {
            $query .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
            $this->limit = null;
            $this->offset = null;

            return $query;
        }

        return $query;
    }

    public function locate(string $needle, string $string, int $start_pos = 1) : string
    {
        return $this->manager->getQueryUtils()->locate($needle, $string, $start_pos);
    }

    /**
     * @throws \ilDatabaseException
     */
    public function modifyTableColumn(string $table, string $column, array $attributes) : bool
    {
        $def = $this->reverse->getTableFieldDefinition($table, $column);

        $analyzer = new ilDBAnalyzer($this);
        $best_alt = $analyzer->getBestDefinitionAlternative($def);
        $def = $def[$best_alt];
        unset($def["nativetype"], $def["mdb2type"]);

        // check attributes
        $ilDBPdoFieldDefinition = $this->field_definition;

        $type = $attributes["type"] ?: $def["type"];

        foreach (array_keys($def) as $k) {
            if ($k !== "type" && !$ilDBPdoFieldDefinition->isAllowedAttribute($k, $type)) {
                unset($def[$k]);
            }
        }
        $check_array = $def;
        foreach ($attributes as $k => $v) {
            $check_array[$k] = $v;
        }
        if (!$this->checkColumnDefinition($check_array, true)) {
            throw new ilDatabaseException("ilDB Error: modifyTableColumn(" . $table . ", " . $column . ")");
        }

        foreach ($attributes as $a => $v) {
            $def[$a] = $v;
        }

        $attributes["definition"] = $def;

        $changes = array(
            "change" => array(
                $column => $attributes,
            ),
        );

        return $this->manager->alterTable($table, $changes, false);
    }

    public function free(ilDBStatement $a_st) : void
    {
        $a_st->closeCursor();
    }

    /**
     * @throws \ilDatabaseException
     */
    public function renameTable(string $name, string $new_name) : bool
    {
        // check table name
        try {
            $this->checkTableName($new_name);
        } catch (ilDatabaseException $e) {
            throw new ilDatabaseException(
                "ilDB Error: renameTable(" . $name . "," . $new_name . ")<br />" . $e->getMessage(),
                $e->getCode()
            );
        }

        $this->manager->alterTable($name, ["name" => $new_name], false);
        if ($this->sequenceExists($name)) {
            $this->manager->alterTable(
                $this->getSequenceName($name),
                ["name" => $this->getSequenceName($new_name)],
                false
            );
        }
        // The abstraction_progress is no longer used in ILIAS, see http://www.ilias.de/mantis/view.php?id=19513
        //		$query = "UPDATE abstraction_progress " . "SET table_name = " . $this->quote($a_new_name, 'text') . " " . "WHERE table_name = "
        //		         . $this->quote($a_name, 'text');
        //		$this->pdo->query($query);

        return true;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function checkTableName(string $a_name) : bool
    {
        return $this->field_definition->checkTableName($a_name);
    }

    public static function isReservedWord(string $a_word) : bool
    {
        global $DIC;
        return (new ilDBPdoMySQLFieldDefinition($DIC->database()))->isReserved($a_word);
    }

    /**
     * @throws \ilDatabaseException
     */
    public function beginTransaction() : bool
    {
        if (!$this->supports(self::FEATURE_TRANSACTIONS)) {
            throw new ilDatabaseException("ilDB::beginTransaction: Transactions are not supported.");
        }

        return $this->pdo->beginTransaction();
    }

    /**
     * @throws \ilDatabaseException
     */
    public function commit() : bool
    {
        if (!$this->supports(self::FEATURE_TRANSACTIONS)) {
            throw new ilDatabaseException("ilDB::beginTransaction: Transactions are not supported.");
        }

        return $this->pdo->commit();
    }

    /**
     * @throws \ilDatabaseException
     */
    public function rollback() : bool
    {
        if (!$this->supports(self::FEATURE_TRANSACTIONS)) {
            throw new ilDatabaseException("ilDB::beginTransaction: Transactions are not supported.");
        }

        return $this->pdo->rollBack();
    }

    public function dropIndex(string $a_table, string $a_name = "i1") : bool
    {
        return $this->manager->dropIndex($a_table, $a_name);
    }

    public function setStorageEngine(string $storage_engine) : void
    {
        $this->storage_engine = $storage_engine;
    }

    public function getStorageEngine() : string
    {
        return $this->storage_engine;
    }

    public function queryCol(string $query, int $type = PDO::FETCH_ASSOC, int $colnum = 0) : array
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

    public function queryRow(
        string $query,
        ?array $types = null,
        int $fetchmode = ilDBConstants::FETCHMODE_DEFAULT
    ) : array {
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

    public function getServerVersion(bool $native = false) : int
    {
        return $this->pdo->query('SELECT VERSION()')->fetchColumn();
    }

    public function escape(string $value, bool $escape_wildcards = false) : string
    {
        return $value;
    }

    public function escapePattern(string $text) : string
    {
        return $text;
    }

    public function migrateAllTablesToEngine(string $engine = ilDBConstants::MYSQL_ENGINE_INNODB) : array
    {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function migrateAllTablesToCollation(string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4) : array
    {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function supportsCollationMigration() : bool
    {
        return false;
    }

    public function supportsEngineMigration() : bool
    {
        return false;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function checkIndexName(string $name) : bool
    {
        $fd = $this->getFieldDefinition();
        if ($fd !== null) {
            return $fd->checkIndexName($name);
        }
        return false;
    }

    /**
     * @throws \ilDatabaseException
     */
    public function addUniqueConstraint(string $table, array $fields, string $name = "con") : bool
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

    public function dropUniqueConstraint(string $table, string $name = "con") : bool
    {
        return $this->manager->dropConstraint($table, $this->constraintName($table, $name), false);
    }

    public function dropUniqueConstraintByFields(string $table, array $fields) : bool
    {
        $analyzer = new ilDBAnalyzer();
        $cons = $analyzer->getConstraintsInformation($table);
        foreach ($cons as $c) {
            if ($c["type"] === "unique" && count($fields) === count($c["fields"])) {
                $all_in = true;
                foreach ($fields as $f) {
                    if (!isset($c["fields"][$f])) {
                        $all_in = false;
                    }
                }
                if ($all_in) {
                    return $this->dropUniqueConstraint($table, $c['name']);
                }
            }
        }

        return false;
    }

    public function getLastInsertId() : int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function buildAtomQuery() : ilAtomQuery
    {
        return new ilAtomQueryLock($this);
    }

    public function uniqueConstraintExists(string $table, array $fields) : bool
    {
        $analyzer = new ilDBAnalyzer();
        $cons = $analyzer->getConstraintsInformation($table);
        foreach ($cons as $c) {
            if ($c["type"] === "unique" && count($fields) === count($c["fields"])) {
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

    public function dropPrimaryKey(string $table_name) : bool
    {
        return $this->manager->dropConstraint($table_name, "PRIMARY", true);
    }

    public function executeMultiple(array $stmt, array $data) : array
    {
        foreach ($stmt as $k => $s) {
            $s->execute($data[$k]);
        }
        return [];
    }

    public function fromUnixtime(string $expr, bool $to_text = true) : string
    {
        return "FROM_UNIXTIME(" . $expr . ")";
    }

    public function unixTimestamp() : string
    {
        return "UNIX_TIMESTAMP()";
    }


    /**
     * @throws ilDatabaseException
     */
    public function getDBVersion() : string
    {
        $d = $this->fetchObject($this->query("SELECT VERSION() AS version"));

        if ($d !== null && $d->version) {
            return $d->version;
        }
        return 'Unknown';
    }

    /**
     * @inheritdoc
     */
    public function sanitizeMB4StringIfNotSupported(string $query) : string
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
    public function doesCollationSupportMB4Strings() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function groupConcat(string $a_field_name, string $a_seperator = ",", ?string $a_order = null) : string
    {
        return $this->manager->getQueryUtils()->groupConcat($a_field_name, $a_seperator, $a_order);
    }

    /**
     * @inheritdoc
     */
    public function cast(string $a_field_name, string $a_dest_type) : string
    {
        return $this->manager->getQueryUtils()->cast($a_field_name, $a_dest_type);
    }
}
