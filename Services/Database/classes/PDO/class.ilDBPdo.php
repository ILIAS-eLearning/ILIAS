<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Database/interfaces/interface.ilDBInterface.php");
require_once("Services/Database/classes/PDO/class.ilPDOStatement.php");
require_once("Services/Database/classes/QueryUtils/class.ilMySQLQueryUtils.php");
require_once("Services/Database/exceptions/exception.ilDatabaseException.php");
require_once('./Services/Database/classes/class.ilDBConstants.php');

/**
 * Class pdoDB
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 * TODO: Quote, Oursource QueryBuilder stuff.
 */
class ilDBPdo implements ilDBInterface {

	const T_TEXT = 'text';
	const T_INTEGER = 'integer';
	const T_FLOAT = 'float';
	const T_DATE = 'date';
	const T_TIME = 'time';
	const T_DATETIME = 'datetime';
	const T_CLOB = 'clob';
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
	 * @var array
	 */
	protected $type_to_mysql_type = array(
		self::T_TEXT     => 'VARCHAR',
		self::T_INTEGER  => 'INT',
		self::T_FLOAT    => 'DOUBLE',
		self::T_DATE     => 'DATE',
		self::T_TIME     => 'TIME',
		self::T_DATETIME => 'TIMESTAMP',
		self::T_CLOB     => 'LONGTEXT',
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
		//		PDO::MYSQL_ATTR_MAX_BUFFER_SIZE => 1048576
	);


	public function connect() {
		if (!$this->getDSN()) {
			$this->generateDSN();
		}

		$this->pdo = new PDO($this->getDSN(), $this->getUsername(), $this->getPassword(), $this->additional_attributes);
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
			return $this->pdo->lastInsertId($table_name) + 1;
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
			$length = $field['length'];
			$primary = isset($field['is_primary']) && $field['is_primary'] ? "PRIMARY KEY" : "";
			$notnull = isset($field['is_notnull']) && $field['is_notnull'] ? "NOT NULL" : "";
			$sequence = isset($field['sequence']) && $field['sequence'] ? "AUTO_INCREMENT" : "";
			$query .= "$name $type ($length) $sequence $primary $notnull,";
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
	 * @param $table_name string
	 */
	public function createSequence($table_name) {
		//TODO
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
		$this->pdo->exec("ALTER TABLE $$table_name ADD $$col_str");
	}


	/**
	 * @param $table_name string
	 */
	public function dropTable($table_name) {
		$this->pdo->exec("DROP TABLE $table_name");
	}


	/**
	 * @param $query string
	 * @return PDOStatement
	 * @throws ilDatabaseException
	 */
	public function query($query) {
		$res = $this->pdo->query($query);
		//        $err = $this->pdo->errorInfo();
		$err = $this->pdo->errorCode();
		if ($err != '00000') {
			$info = $this->pdo->errorInfo();
			$infoMessage = $info[2];
			echo "$query";
			exit;
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
		$this->pdo->exec("ALTER TABLE $$table_name DROP COLUMN $column_name");
	}


	/**
	 * @param $table_name      string
	 * @param $column_old_name string
	 * @param $column_new_name string
	 */
	public function renameTableColumn($table_name, $column_old_name, $column_new_name) {
		$this->pdo->exec("alter table $table_name change $column_old_name $column_new_name");
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
		$this->pdo->exec($query);
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
		
		$query = "UPDATE $table_name".
			" SET ".implode(", ", $query_fields).
			" WHERE ".implode(" AND ", $query_where);
		
		try {

			$this->pdo->exec($query);
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
		$this->pdo->exec($query);
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
		switch ($type) {
			case self::T_INTEGER:
				$pdo_type = PDO::PARAM_INT;
				break;
			case self::T_FLOAT:
				$pdo_type = PDO::PARAM_INT;
				break;
			case self::T_TEXT:
			default:
				$pdo_type = PDO::PARAM_STR;
				break;
		}

		return $this->pdo->quote($value, $pdo_type);
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
	function fetchRow($fetchMode = DB_FETCHMODE_ASSOC) {
		if ($fetchMode == DB_FETCHMODE_ASSOC) {
			return $this->fetchRowAssoc();
		} elseif ($fetchMode == DB_FETCHMODE_OBJECT) {
			return $this->fetchRowObject();
		} else {
			throw new ilDatabaseException("No valid fetch mode given, choose DB_FETCHMODE_ASSOC or DB_FETCHMODE_OBJECT");
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
	 * @param $query  string
	 * @param $types  string[]
	 * @param $values mixed[]
	 * @return \ilDBStatement
	 */
	public function queryF($query, $types, $values) {
		// TODO: EXTRACT FOR THIS AND ilDB.

		if (!is_array($types) || !is_array($values) || count($types) != count($values)) {
			$this->raisePearError("ilDB::queryF: Types and values must be arrays of same size. ($query)");
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
		//TODO
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
			self::T_TEXT,
			self::T_CLOB,
			"blob",
		))
		) {
			throw new ilDatabaseException("Like: Invalid column type '" . $type . "'.");
			//			$this->raisePearError("Like: Invalid column type '" . $type . "'.", $this->error_class->FATAL);
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
		return "now()";
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
		// TODO: Implement replace() method.

		// this is the mysql implementation
		$a_columns = array_merge($primaryKeys, $otherColumns);
		$fields = array();
		$field_values = array();
		$placeholders = array();
		$types = array();
		$values = array();
		$lobs = false;
		$lob = array();
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
			if ($col[0] == "blob" || $col[0] == "clob") {
				$lobs = true;
				$lob[$k] = $k;
			}
		}
		if ($lobs)    // lobs -> use prepare execute (autoexecute broken in PEAR 2.4.1)
		{
			$st = $this->db->prepare("REPLACE INTO " . $table . " (" . implode($fields, ",") . ") VALUES (" . implode($placeholders2, ",")
			                         . ")", $types, MDB2_PREPARE_MANIP, $lob);
			$this->handleError($st, "insert / prepare/execute(" . $table . ")");
			$r = $st->execute($field_values);
			//$r = $this->db->extended->autoExecute($a_table, $field_values, MDB2_AUTOQUERY_INSERT, null, $types);
			$this->handleError($r, "insert / prepare/execute(" . $table . ")");
			$this->free($st);
		} else    // if no lobs are used, take simple manipulateF
		{
			$q = "REPLACE INTO " . $table . " (" . implode($fields, ",") . ") VALUES (" . implode($placeholders, ",") . ")";
			$r = $this->manipulateF($q, $types, $values);
		}

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
	 * @throws \ilDatabaseException
	 */
	public function optimizeTable($a_table) {
		$this->query('OPTIMIZE TABLE ' . $a_table);
	}
}

?>
