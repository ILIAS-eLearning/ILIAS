<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Database/classes/PDO/class.ilPDOStatement.php");
require_once("./Services/Database/classes/QueryUtils/class.ilMySQLQueryUtils.php");
require_once('./Services/Database/classes/PDO/Manager/class.ilDBPdoManager.php');
require_once('./Services/Database/classes/PDO/Reverse/class.ilDBPdoReverse.php');

/**
 * Class pdoDB
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdo implements ilDBInterface {

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
	 * @var array
	 */
	protected $type_to_mysql_type = array(
		ilDBConstants::T_TEXT      => 'VARCHAR',
		ilDBConstants::T_INTEGER   => 'INT',
		ilDBConstants::T_FLOAT     => 'DOUBLE',
		ilDBConstants::T_DATE      => 'DATE',
		ilDBConstants::T_TIME      => 'TIME',
		ilDBConstants::T_DATETIME  => 'TIMESTAMP',
		ilDBConstants::T_CLOB      => 'LONGTEXT',
		ilDBConstants::T_TIMESTAMP => 'DATETIME',
	);
	/**
	 * @var string
	 */
	protected $dsn = '';
	/**
	 * @var array
	 */
	protected $additional_attributes = array(
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		PDO::ATTR_EMULATE_PREPARES         => true,
		PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
		//		PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_OBJ
		//		PDO::MYSQL_ATTR_MAX_BUFFER_SIZE => 1048576
	);


	public function connect() {
		if (!$this->getDSN()) {
			$this->generateDSN();
		}

		$this->pdo = new PDO($this->getDSN(), $this->getUsername(), $this->getPassword(), $this->additional_attributes);
		$this->manager = new ilDBPdoManager($this->pdo, $this);
		$this->reverse = new ilDBPdoReverse($this->pdo, $this);

		return ($this->pdo->errorCode() == PDO::ERR_NONE);
	}


	/**
	 * @param null $tmpClientIniFile
	 */
	public function initFromIniFile($tmpClientIniFile = null) {
		global $ilClientIniFile;
		if ($tmpClientIniFile instanceof ilIniFile) {
			$clientIniFile = $tmpClientIniFile;
		} else {
			$clientIniFile = $ilClientIniFile;
		}

		$this->setUsername($clientIniFile->readVariable("db", "user"));
		$this->setHost($clientIniFile->readVariable("db", "host"));
		$this->setPort((int)$clientIniFile->readVariable("db", "port"));
		$this->setPassword($clientIniFile->readVariable("db", "pass"));
		$this->setDbname($clientIniFile->readVariable("db", "name"));

		$this->generateDSN();
	}


	public function generateDSN() {
		$this->dsn = 'mysql:host=' . $this->getHost() . ';dbname=' . $this->getDbname() . ';charset=' . $this->getCharset();
	}


	/**
	 * @param $identifier
	 * @return string
	 */
	public function quoteIdentifier($identifier) {
		return '`' . $identifier . '`';
	}


	/**
	 * @param $table_name string
	 *
	 * @return int
	 */
	public function nextId($table_name) {
		$sequence_table_name = $table_name . '_seq';
		if ($this->tableExists($sequence_table_name)) {
			$stmt = $this->pdo->prepare("SELECT sequence FROM $sequence_table_name");
			$stmt->execute();
			$rows = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			$has_set = isset($rows['sequence']);
			$next_id = ($has_set ? ($rows['sequence'] + 1) : 1);
			if ($has_set) {
				$stmt = $this->pdo->prepare("UPDATE $sequence_table_name SET sequence = :next_id");
			} else {
				$stmt = $this->pdo->prepare("INSERT INTO $sequence_table_name (sequence) VALUES(:next_id)");
			}
			$stmt->execute(array( "next_id" => $next_id ));

			return $next_id;
		} else {
			return $this->pdo->lastInsertId("`".$table_name."`") + 1;
		}
	}


	/**
	 * experimental....
	 *
	 * @param $table_name string
	 * @param $fields     array
	 */
	public function createTable($table_name, $fields) {
		$fields_query = $this->createTableFields($fields);
		$query = "CREATE TABLE $table_name ($fields_query);";
		$this->pdo->exec($query);
	}


	/**
	 * @param $fields
	 *
	 * @return string
	 */
	protected function createTableFields($fields) {
		$query = "";
		foreach ($fields as $name => $field) {
			$type = $this->type_to_mysql_type[$field['type']];
			$length = $field['length'] ? "(" . $field['length'] . ")" : "";
			$primary = isset($field['is_primary']) && $field['is_primary'] ? "PRIMARY KEY" : "";
			$notnull = isset($field['is_notnull']) && $field['is_notnull'] ? "NOT NULL" : "";
			$sequence = isset($field['sequence']) && $field['sequence'] ? "AUTO_INCREMENT" : "";
			$query .= "$name $type $length $sequence $primary $notnull,";
		}

		return substr($query, 0, - 1);
	}


	/**
	 * @param $table_name   string
	 * @param $primary_keys array
	 */
	public function addPrimaryKey($table_name, $primary_keys) {
		$keys = implode($primary_keys);
		$this->pdo->exec("ALTER TABLE $table_name ADD PRIMARY KEY ($keys)");
	}


	/**
	 * @param $table_name
	 * @param int $start
	 */
	public function createSequence($table_name, $start = 1) {
		$this->manager->createSequence($table_name, $start);
	}


	/**
	 * @param $table_name string
	 *
	 * @return bool
	 */
	public function tableExists($table_name) {
		$result = $this->pdo->prepare("SHOW TABLES LIKE :table_name");
		$result->execute(array( 'table_name' => $table_name ));
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
	public function tableColumnExists($table_name, $column_name) {
		$statement = $this->pdo->query("SHOW COLUMNS FROM $table_name WHERE Field = '$column_name'");
		$statement != null ? $statement->closeCursor() : "";

		return $statement != null && $statement->rowCount() != 0;
	}


	/**
	 * @param $table_name  string
	 * @param $column_name string
	 * @param $attributes  array
	 */
	public function addTableColumn($table_name, $column_name, $attributes) {
		$col = array( $column_name => $attributes );
		$col_str = $this->createTableFields($col);
		$this->pdo->exec("ALTER TABLE $table_name ADD $col_str");
	}


	/**
	 * @param $table_name string
	 */
	public function dropTable($table_name) {
		$this->pdo->exec("DROP TABLE $table_name");
	}


	/**
	 * @param $old_name
	 * @param $new_name
	 *
	 * @return mixed
	 */
	public function renameTable($old_name, $new_name) {
		//TODO: implement with manager and add more validation
		$query = "RENAME TABLE " . $old_name . " TO " . $new_name . ";";
		$this->pdo->exec($query);
		return true;
	}


	/**
	 * @param $query string
	 * @return PDOStatement
	 * @throws ilDatabaseException
	 */
	public function query($query) {
		$query = $this->appendLimit($query);
		$res = $this->pdo->query($query);
		$err = $this->pdo->errorCode();
		if ($err != PDO::ERR_NONE) {
			$info = $this->pdo->errorInfo();
			$infoMessage = $info[2];
			throw new ilDatabaseException($infoMessage);
		}

		return new ilPDOStatement($res);
	}


	/**
	 * @param $query_result PDOStatement
	 *
	 * @return array
	 */
	public function fetchAll($query_result) {
		return $query_result->fetchAll($query_result);
	}


	/**
	 * @param $table_name string
	 */
	public function dropSequence($table_name) {
		$table_seq = $table_name . "_seq";
		if ($this->tableExists($table_seq)) {
			$this->pdo->exec("DROP TABLE $table_seq");
		}
	}


	/**
	 * @param $table_name  string
	 * @param $column_name string
	 */
	public function dropTableColumn($table_name, $column_name) {
		$this->pdo->exec("ALTER TABLE $table_name DROP COLUMN $column_name");
	}


	/**
	 * @param $table_name      string
	 * @param $column_old_name string
	 * @param $column_new_name string
	 */
	public function renameTableColumn($table_name, $column_old_name, $column_new_name) {
		$get_type_query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ".$this->quote($table_name, 'text')." AND COLUMN_NAME = ".$this->quote($column_old_name, 'text');
		$get_type_result = $this->query($get_type_query);
		$column_type = $this->fetchAssoc($get_type_result);

		$query = "ALTER TABLE $table_name CHANGE ".$this->quote($column_old_name, 'text')." ". $this->quote($column_new_name, 'text')." ".$column_type['COLUMN_TYPE'];
		$this->pdo->exec($query);
	}


	/**
	 * @param $table_name string
	 * @param $values
	 * @return int|void
	 */
	public function insert($table_name, $values) {
		$real = array();
		$fields = array();
		foreach ($values as $key => $val) {
			$real[] = $this->quote($val[1], $val[0]);
			$fields[] = $key;
		}
		$values = implode(",", $real);
		$fields = implode(",", $fields);
		$query = "INSERT INTO " . $table_name . " (" . $fields . ") VALUES (" . $values . ")";

		return $this->pdo->exec($query);
	}


	/**
	 * @param $query_result PDOStatement
	 *
	 * @return mixed|null
	 */
	public function fetchObject($query_result) {
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
	public function update($table_name, $values, $where) {

		$query_fields = array();
		foreach ($values as $key => $val) {
			$qval = $this->quote($val[1], $val[0]);
			$query_fields[] = "$key = $qval";
		}

		$query_where = array();
		foreach ($where as $key => $val) {
			$qval = $this->quote($val[1], $val[0]);
			$query_where[] = "$key = $qval";
		}

		$query = "UPDATE $table_name" . " SET " . implode(", ", $query_fields) . " WHERE " . implode(" AND ", $query_where);

		try {

			return $this->pdo->exec($query);
		} catch (PDOException $e) {
			echo '<pre>' . print_r($query, 1) . '</pre>';
			exit();
		}
	}


	/**
	 * @param $query string
	 * @return int
	 */
	public function manipulate($query) {
		return $this->pdo->exec($query);
	}


	/**
	 * @param $query_result PDOStatement
	 *
	 * @return mixed
	 */
	public function fetchAssoc($query_result) {
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
	public function numRows($query_result) {
		return $query_result->rowCount();
	}


	/**
	 * @param $value
	 * @param $type
	 *
	 * @return string
	 */
	public function quote($value, $type = null) {

		// see ilMDB2/Driver/Datatype/Common::quote()
		if ($value === null) {
			return 'NULL';
		}

		switch ($type) {
			case ilDBConstants::T_INTEGER:
				$pdo_type = PDO::PARAM_INT;
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
	 * @return null
	 */
	public function indexExistsByFields($table_name, $fields) {
		//TODO: implement
		return false;
	}

	/**
	 * @param $table_name
	 * @param $index_name
	 *
	 * @return null
	 */
	public function addIndex($table_name, $index_name) {
		return null;
	}


	/**
	 * @param $fetchMode int
	 * @return mixed
	 * @throws ilDatabaseException
	 */
	public function fetchRow($fetchMode = ilDBConstants::FETCHMODE_ASSOC) {
		if ($fetchMode == ilDBConstants::FETCHMODE_ASSOC) {
			return $this->fetchRowAssoc();
		} elseif ($fetchMode == ilDBConstants::FETCHMODE_OBJECT) {
			return $this->fetchRowObject();
		} else {
			throw new ilDatabaseException("No valid fetch mode given, choose ilDBConstants::FETCHMODE_ASSOC or ilDBConstants::FETCHMODE_OBJECT");
		}
	}


	private function fetchRowAssoc() {
	}


	private function fetchRowObject() {
	}


	/**
	 * @return string
	 */
	public function getDSN() {
		return $this->dsn;
	}


	/**
	 * Get DSN. This must be overwritten in DBMS specific class.
	 */
	function getDBType() {
		// TODO: Implement getDBType() method.
	}


	/**
	 * Get reserved words. This must be overwritten in DBMS specific class.
	 * This is mainly used to check whether a new identifier can be problematic
	 * because it is a reserved word. So createTable / alterTable usually check
	 * these.
	 */
	static function getReservedWords() {
		// TODO: Implement getReservedWords() method.
	}


	/**
	 * Abstraction of lock table
	 *
	 * @param $a_tables
	 * @internal param table $array definitions
	 */
	public function lockTables($a_tables) {
		// TODO: Implement lockTables() method.
	}


	/**
	 * Unlock tables locked by previous lock table calls
	 */
	public function unlockTables() {
		// TODO: Implement unlockTables() method.
	}


	/**
	 * @param $field  string
	 * @param $values array
	 * @param bool $negate
	 * @param string $type
	 * @return string
	 */
	public function in($field, $values, $negate = false, $type = "") {
		return ilMySQLQueryUtils::getInstance()->in($field, $values, $negate, $type);
	}


	/**
	 * @param string $query
	 * @param \string[] $types
	 * @param \mixed[] $values
	 * @return \PDOStatement
	 * @throws \ilDatabaseException
	 */
	public function queryF($query, $types, $values) {
		// TODO: EXTRACT FOR THIS AND ilDB.
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
	public function manipulateF($query, $types, $values) {
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
	public function useSlave($bool) {
		return false;
	}


	/**
	 * Set the Limit for the next Query.
	 *
	 * @param $limit
	 * @param $offset
	 * @deprecated Use a limit in the query.
	 */
	public function setLimit($limit, $offset = 0) {
		$this->limit = $limit;
		$this->offset = $offset;
	}


	/**
	 * Generate a like subquery.
	 *
	 * @param string $column
	 * @param string $type
	 * @param mixed $value
	 * @param bool $caseInsensitive
	 * @return string
	 */
	public function like($column, $type, $value = "?", $caseInsensitive = true) {
		// TODO: Implement like() method.

		if (!in_array($type, array(
			ilDBConstants::T_TEXT,
			ilDBConstants::T_CLOB,
			"blob",
		))
		) {
			throw new ilDatabaseException("Like: Invalid column type '" . $type . "'.");
		}
		if ($value == "?") {
			if ($caseInsensitive) {
				return "UPPER(" . $column . ") LIKE(UPPER(?))";
			} else {
				return $column . " LIKE(?)";
			}
		} else {
			if ($caseInsensitive) {
				// Always quote as text
				return " UPPER(" . $column . ") LIKE(UPPER(" . $this->quote($value, 'text') . "))";
			} else {
				// Always quote as text
				return " " . $column . " LIKE(" . $this->quote($value, 'text') . ")";
			}
		}
	}


	/**
	 * @return string the now statement
	 */
	public function now() {
		return "NOW()";
	}


	/**
	 * Replace into method.
	 *
	 * @param    string        table name
	 * @param    array         primary key values: array("field1" => array("text", $name), "field2" => ...)
	 * @param    array         other values: array("field1" => array("text", $name), "field2" => ...)
	 * @return string
	 */
	public function replace($table, $primaryKeys, $otherColumns) {
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
				$col[1] = (int)$col[1];
			}

			$values[] = $col[1];
			$field_values[$k] = $col[1];
		}

		$q = "REPLACE INTO " . $table . " (" . implode($fields, ",") . ") VALUES (" . implode($placeholders, ",") . ")";

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
	public function equals($columns, $value, $type, $emptyOrNull = false) {
		if (!$emptyOrNull || $value != "") {
			return $columns . " = " . $this->quote($value, $type);
		} else {
			return "(" . $columns . " = '' OR $columns IS NULL)";
		}
	}


	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}


	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}


	/**
	 * @return string
	 */
	public function getDbname() {
		return $this->dbname;
	}


	/**
	 * @param string $dbname
	 */
	public function setDbname($dbname) {
		$this->dbname = $dbname;
	}


	/**
	 * @return string
	 */
	public function getCharset() {
		return $this->charset;
	}


	/**
	 * @param string $charset
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
	}


	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}


	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}


	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}


	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}


	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}


	/**
	 * @param int $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}


	/**
	 * @param $user
	 */
	public function setDBUser($user) {
		$this->setUsername($user);
	}


	/**
	 * @param $port
	 */
	public function setDBPort($port) {
		$this->setPort($port);
	}


	/**
	 * @param $password
	 */
	public function setDBPassword($password) {
		$this->setPassword($password);
	}


	/**
	 * @param $host
	 */
	public function setDBHost($host) {
		$this->setHost($host);
	}


	/**
	 * @param $a_exp
	 * @return string
	 */
	public function upper($a_exp) {
		return " UPPER(" . $a_exp . ") ";
	}


	/**
	 * @param $a_exp
	 * @return string
	 */
	public function lower($a_exp) {
		return " LOWER(" . $a_exp . ") ";
	}


	/**
	 * @param $a_exp
	 * @param int $a_pos
	 * @param int $a_len
	 * @return string
	 */
	public function substr($a_exp, $a_pos = 1, $a_len = - 1) {
		$lenstr = "";
		if ($a_len > - 1) {
			$lenstr = ", " . $a_len;
		}

		return " SUBSTR(" . $a_exp . ", " . $a_pos . $lenstr . ") ";
	}


	/**
	 * @param $a_query
	 * @param null $a_types
	 * @return mixed
	 */
	public function prepareManip($a_query, $a_types = null) {
		return $this->pdo->prepare($a_query);
	}


	public function enableResultBuffering($a_status) {
		$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $a_status);
	}


	/**
	 * @param $stmt
	 * @param array $data
	 * @return bool
	 */
	public function execute($stmt, $data = array()) {
		/**
		 * @var $stmt PDOStatement
		 */
		return $stmt->execute($data);
	}


	/**
	 * @param $a_table
	 * @return \PDOStatement
	 * @throws \ilDatabaseException
	 */
	public function optimizeTable($a_table) {
		return $this->query('OPTIMIZE TABLE ' . $a_table);
	}


	/**
	 * @return bool
	 */
	public function supportsSlave() {
		return false;
	}


	/**
	 * @return bool
	 */
	public function supportsFulltext() {
		return false;
	}


	/**
	 * @return array
	 */
	public function listTables() {
		return $this->manager->listTables();
	}


	/**
	 * @param $module
	 * @return \ilDBPdoManager|\ilDBPdoReverse
	 */
	public function loadModule($module) {
		switch ($module) {
			case ilDBConstants::MODULE_MANAGER:
				return $this->manager;
			case ilDBConstants::MODULE_REVERSE:
				return $this->reverse;
		}
	}


	/**
	 * @return array
	 */
	public function getAllowedAttributes() {
		return ilDBConstants::$allowed_attributes;
	}


	/**
	 * @param $sequence
	 * @return bool
	 */
	public function sequenceExists($sequence) {
		return in_array($sequence, $this->listSequences());
	}


	/**
	 * @return array
	 */
	public function listSequences() {
		return $this->manager->listSequences();
	}


	/**
	 * @param array $values
	 * @param bool $allow_null
	 * @return string
	 */
	public function concat(array $values, $allow_null = true) {
		return ilMySQLQueryUtils::getInstance()->concat($values, $allow_null);
	}


	/**
	 * @param $query
	 * @return string
	 */
	protected function appendLimit($query) {
		if ($this->limit !== null && $this->offset !== null) {
			$query .= ' LIMIT ' . (int)$this->offset . ', ' . (int)$this->limit;
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
	public function locate($a_needle, $a_string, $a_start_pos = 1) {
		return ilMySQLQueryUtils::getInstance()->locate($a_needle, $a_string, $a_start_pos);
	}


	/**
	 * @param $table
	 * @param $a_column
	 * @param $a_attributes
	 * @return bool
	 */
	public function modifyTableColumn($table, $a_column, $a_attributes) {
		$def = $this->reverse->getTableFieldDefinition($table, $a_column);

		throw new ilDatabaseException('not yet implemented ' . __METHOD__);

		$this->handleError($def, "modifyTableColumn(" . $table . ")");

		if (is_file("./Services/Database/classes/class.ilDBAnalyzer.php")) {
			include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		} else {
			include_once("../Services/Database/classes/class.ilDBAnalyzer.php");
		}
		$analyzer = new ilDBAnalyzer();
		$best_alt = $analyzer->getBestDefinitionAlternative($def);
		$def = $def[$best_alt];
		unset($def["nativetype"]);
		unset($def["mdb2type"]);

		// check attributes
		$type = ($a_attributes["type"] != "") ? $a_attributes["type"] : $def["type"];
		foreach ($def as $k => $v) {
			if ($k != "type" && !in_array($k, $this->allowed_attributes[$type])) {
				unset($def[$k]);
			}
		}
		$check_array = $def;
		foreach ($a_attributes as $k => $v) {
			$check_array[$k] = $v;
		}
		if (!$this->checkColumnDefinition($check_array, true)) {
			$this->raisePearError("ilDB Error: modifyTableColumn(" . $table . ", " . $a_column . ")<br />" . $this->error_str);
		}

		// oracle workaround: do not set null, if null already given
		if ($this->getDbType() == "oracle") {
			if ($def["notnull"] == true
			    && ($a_attributes["notnull"] == true
			        || !isset($a_attributes["notnull"]))
			) {
				unset($def["notnull"]);
				unset($a_attributes["notnull"]);
			}
			if ($def["notnull"] == false
			    && ($a_attributes["notnull"] == false
			        || !isset($a_attributes["notnull"]))
			) {
				unset($def["notnull"]);
				unset($a_attributes["notnull"]);
			}
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

		$r = $manager->alterTable($table, $changes, false);

		return $this->handleError($r, "modifyTableColumn(" . $table . ")");
	}


	/**
	 * @param ilPDOStatement $a_st
	 * @return bool
	 */
	public function free($a_st) {
		/**
		 * @var $a_st PDOStatement
		 */
		return $a_st->closeCursor();
	}
}
