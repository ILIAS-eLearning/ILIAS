<?php

/**
 * Class ilPDO
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.1.0
 */
abstract class ilPDO extends PDO {

	const FETCHMODE_ASSOC = 2;
	const FETCHMODE_OBJECT = 3;
	/**
	 * @var string
	 */
	protected $db_user = '';
	/**
	 * @var string
	 */
	protected $db_port = '';
	/**
	 * @var string
	 */
	protected $db_host = '';
	/**
	 * @var string
	 */
	protected $db_password = '';
	/**
	 * @var string
	 */
	protected $db_name = '';
	/**
	 * @var string
	 */
	protected $db_version = '';
	/**
	 * @var PDO
	 */
	protected $pdo = NULL;


	public function __construct() {
		//		$this->initPDO();
	}


	/**
	 * @param $a_user
	 */
	public function setDBUser($a_user) {
		$this->db_user = $a_user;
	}


	/**
	 * @return string
	 */
	public function getDBUser() {
		return $this->db_user;
	}


	/**
	 * @param $a_port
	 */
	public function setDBPort($a_port) {
		$this->db_port = $a_port;
	}


	/**
	 * @return string
	 */
	public function getDBPort() {
		return $this->db_port;
	}


	/**
	 * @param $a_host
	 */
	public function setDBHost($a_host) {
		$this->db_host = $a_host;
	}


	/**
	 * @return string
	 */
	public function getDBHost() {
		return $this->db_host;
	}


	/**
	 * @param $a_password
	 */
	public function setDBPassword($a_password) {
		$this->db_password = $a_password;
	}


	/**
	 * @return string
	 */
	public function getDBPassword() {
		return $this->db_password;
	}


	/**
	 * @param $a_name
	 */
	public function setDBName($a_name) {
		$this->db_name = $a_name;
	}


	/**
	 * @return string
	 */
	public function getDBName() {
		return $this->db_name;
	}


	/**
	 * @return string
	 */
	public function getDBVersion() {
		return "Unknown";
	}


	/**
	 * @param bool $a_status
	 */
	public function enableResultBuffering($a_status) {
		$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $a_status);
	}


	/**
	 * @description Get DSN. This must be overwritten in DBMS specific class.
	 */
	abstract function getDSN();


	/**
	 * @param $table_name string
	 *
	 * @return int
	 */
	public function nextId($table_name) {
		if ($this->tableExists($table_name . '_seq')) {
			$table_seq = $table_name . '_seq';
			$stmt = $this->pdo->prepare("SELECT * FROM $table_seq");
			$stmt->execute();
			$rows = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			return count($rows) ? 0 : $rows['seq'];
		} else {
			//            return $this->pdo->lastInsertId($table_name) + 1;
			return 0;
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
		$result->execute(array( ':table_name' => $table_name ));
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
		$statement != NULL ? $statement->closeCursor() : "";

		return $statement != NULL && $statement->rowCount() != 0;
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
	 *
	 * @return \PDOStatement
	 */
	public function query($query) {
		$res = $this->pdo->query($query);
		$err = $this->pdo->errorInfo();

		return $res;
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
	 */
	public function insert($table_name, $values) {
		$real = array();
		foreach ($values as $val) {
			$real[] = $this->quote($val[1], $val[0]);
		}
		$values = implode(",", $real);
		$this->pdo->exec("INSERT INTO $table_name VALUES ($values)");
	}


	/**
	 * @param $query_result PDOStatement
	 *
	 * @return mixed|null
	 */
	public function fetchObject($query_result) {
		$res = $query_result->fetchObject();
		if ($res == NULL) {
			$query_result->closeCursor();

			return NULL;
		}

		return $res;
	}


	/**
	 * @param $table_name string
	 * @param $values     array
	 * @param $where      array
	 */
	public function update($table_name, $values, $where) {
		$query = "UPDATE $table_name SET ";
		foreach ($values as $key => $val) {
			$qval = $this->quote($val[1], $val[0]);
			$query .= "$key=$qval,";
		}
		$query = substr($query, 0, - 1) . " WHERE ";
		foreach ($where as $key => $val) {
			$qval = $this->quote($val[1], $val[0]);
			$query .= "$key=$qval,";
		}
		$query = substr($query, 0, - 1);
		$this->pdo->exec($query);
	}


	/**
	 * @param $query string
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
		if ($res == NULL) {
			$query_result->closeCursor();

			return NULL;
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
	public function quote($value, $type) {
		//TODO TYPE SENSITIVE.
		return $this->pdo->quote($value);
	}


	/**
	 * @param $table_name
	 * @param $index_name
	 *
	 * @return null
	 */
	public function addIndex($table_name, $index_name) {
		return NULL;
	}


	/**
	 * @param ilIniFile $tmpClientIniFile
	 */
	public function initFromIniFile(ilIniFile $tmpClientIniFile = NULL) {
		global $ilClientIniFile;

		if ($tmpClientIniFile instanceof ilIniFile) {
			$clientIniFile = $tmpClientIniFile;
		} else {
			$clientIniFile = $ilClientIniFile;
		}

		if ($clientIniFile instanceof ilIniFile) {
			$this->setDBUser($clientIniFile->readVariable("db", "user"));
			$this->setDBHost($clientIniFile->readVariable("db", "host"));
			$this->setDBPort($clientIniFile->readVariable("db", "port"));
			$this->setDBPassword($clientIniFile->readVariable("db", "pass"));
			$this->setDBName($clientIniFile->readVariable("db", "name"));
		}
	}


	/**
	 * @param bool $a_return_false_for_error
	 *
	 * @return bool
	 */
	public function connect($a_return_false_for_error = false) {

		//	$dsn = 'mysql:host=localhost;dbname=trunk;charset=utf8';
		$return = $this->pdo = new PDO($this->getDSN(), $this->getDBUser(), $this->getDBPassword());

		if ($a_return_false_for_error AND $return) {
			return false;
		} else {
			return true;
		}
	}


	public function disconnect() {
		unset($this->pdo);
	}


	protected function initConnection() {
	}


	/**
	 * @return bool
	 */
	function getHostDSN() {
		return false;
	}


	function connectHost() {
		//		return parent::connectHost(); // TODO: Change the autogenerated stub
	}


	protected function initHostConnection() {
		//		parent::initHostConnection(); // TODO: Change the autogenerated stub
	}


	/**
	 * @return bool
	 */
	public function supportsFulltext() {
		return false;
	}


	/**
	 * @return bool
	 */
	public function supportsSlave() {
		return false;
	}


	/**
	 * @param bool $a_val
	 *
	 * @return bool
	 */
	public function useSlave($a_val = true) {
		if (!$this->supportsSlave()) {
			return false;
		}
		$this->use_slave = $a_val;
	}


	/**
	 * @var bool
	 */
	protected $use_slave = false;


	/**
	 * @param        $a_res
	 * @param string $a_info
	 * @param string $a_level
	 */
	public function handleError($a_res, $a_info = "", $a_level = "") {
	}


	public function raisePearError($a_message, $a_level = "") {
		//		parent::raisePearError($a_message, $a_level); // TODO: Change the autogenerated stub
	}


	protected function loadMDB2Extensions() {
		define('DB_AUTOQUERY_SELECT', MDB2_AUTOQUERY_SELECT);
		define('DB_AUTOQUERY_INSERT', MDB2_AUTOQUERY_INSERT);
		define('DB_AUTOQUERY_UPDATE', MDB2_AUTOQUERY_UPDATE);
		define('DB_AUTOQUERY_DELETE', MDB2_AUTOQUERY_DELETE);
	}


	/**
	 * @param $a_res
	 *
	 * @return bool
	 */
	static public function isDbError($a_res) {
		return false;
	}


	/**
	 * @param        $a_name
	 * @param string $a_charset
	 * @param string $a_collation
	 *
	 * @return PDOStatement
	 */
	public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "") {
		if ($a_collation != "") {
			$sql = "CREATE DATABASE " . $a_name . " CHARACTER SET " . $a_charset . " COLLATE " . $a_collation;
		} else {
			$sql = "CREATE DATABASE " . $a_name . " CHARACTER SET " . $a_charset;
		}

		return $this->query($sql, false);
	}


	/**
	 * @return array
	 */
	protected function getCreateTableOptions() {
		return array();
	}


	function alterTable($a_name, $a_changes) {
		//		return parent::alterTable($a_name, $a_changes); // TODO: Change the autogenerated stub
	}


	function modifyTableColumn($a_table, $a_column, $a_attributes) {
		//		return parent::modifyTableColumn($a_table, $a_column, $a_attributes); // TODO: Change the autogenerated stub
	}


	function renameTable($a_name, $a_new_name) {
		//		return parent::renameTable($a_name, $a_new_name); // TODO: Change the autogenerated stub
	}


	function getPrimaryKeyIdentifier() {
		//		return parent::getPrimaryKeyIdentifier(); // TODO: Change the autogenerated stub
	}


	function dropPrimaryKey($a_table) {
		//		return parent::dropPrimaryKey($a_table); // TODO: Change the autogenerated stub
	}


	/**
	 * @param        $a_table
	 * @param        $a_fields
	 * @param string $a_name
	 *
	 * @return bool
	 */
	function addFulltextIndex($a_table, $a_fields, $a_name = "in") {
		return false;
	}


	/**
	 * @param $a_table
	 * @param $a_name
	 *
	 * @return bool
	 */
	function isFulltextIndex($a_table, $a_name) {
		return false;
	}


	public function indexExistsByFields($a_table, $a_fields) {
		//		return parent::indexExistsByFields($a_table, $a_fields); // TODO: Change the autogenerated stub
	}


	public function dropIndexByFields($a_table, $a_fields) {
		//		return parent::dropIndexByFields($a_table, $a_fields); // TODO: Change the autogenerated stub
	}


	function dropIndex($a_table, $a_name = "in") {
		//		return parent::dropIndex($a_table, $a_name); // TODO: Change the autogenerated stub
	}


	function addUniqueConstraint($a_table, $a_fields, $a_name = "con") {
		//		return parent::addUniqueConstraint($a_table, $a_fields, $a_name); // TODO: Change the autogenerated stub
	}


	function checkTableName($a_name) {
		//		return parent::checkTableName($a_name); // TODO: Change the autogenerated stub
	}


	function checkTableColumns($a_cols) {
		return parent::checkTableColumns($a_cols); // TODO: Change the autogenerated stub
	}


	function checkColumn($a_col, $a_def) {
		return parent::checkColumn($a_col, $a_def); // TODO: Change the autogenerated stub
	}


	function checkColumnDefinition($a_def, $a_modify_mode = false) {
		return parent::checkColumnDefinition($a_def, $a_modify_mode); // TODO: Change the autogenerated stub
	}


	function checkColumnName($a_name) {
		return parent::checkColumnName($a_name); // TODO: Change the autogenerated stub
	}


	function checkIndexName($a_name) {
		return parent::checkIndexName($a_name); // TODO: Change the autogenerated stub
	}


	function getAllowedAttributes() {
		return parent::getAllowedAttributes(); // TODO: Change the autogenerated stub
	}


	function constraintName($a_table, $a_constraint) {
		return parent::constraintName($a_table, $a_constraint); // TODO: Change the autogenerated stub
	}


	static function isReservedWord($a_word) {
		return parent::isReservedWord($a_word); // TODO: Change the autogenerated stub
	}


	function queryF($a_query, $a_types, $a_values) {
		return parent::queryF($a_query, $a_types, $a_values); // TODO: Change the autogenerated stub
	}


	function manipulateF($a_query, $a_types, $a_values) {
		return parent::manipulateF($a_query, $a_types, $a_values); // TODO: Change the autogenerated stub
	}


	function logStatement($sql) {
		parent::logStatement($sql); // TODO: Change the autogenerated stub
	}


	function setLimit($a_limit, $a_offset = 0) {
		parent::setLimit($a_limit, $a_offset); // TODO: Change the autogenerated stub
	}


	function prepare($a_query, $a_types = NULL, $a_result_types = NULL) {
		return parent::prepare($a_query, $a_types, $a_result_types); // TODO: Change the autogenerated stub
	}


	function prepareManip($a_query, $a_types = NULL) {
		return parent::prepareManip($a_query, $a_types); // TODO: Change the autogenerated stub
	}


	function execute($a_stmt, $a_data = NULL) {
		return parent::execute($a_stmt, $a_data); // TODO: Change the autogenerated stub
	}


	function executeMultiple($a_stmt, $a_data) {
		return parent::executeMultiple($a_stmt, $a_data); // TODO: Change the autogenerated stub
	}


	function replace($a_table, $a_pk_columns, $a_other_columns) {
		return parent::replace($a_table, $a_pk_columns, $a_other_columns); // TODO: Change the autogenerated stub
	}


	function free($a_st) {
		return parent::free($a_st); // TODO: Change the autogenerated stub
	}


	function in($a_field, $a_values, $negate = false, $a_type = "") {
		return parent::in($a_field, $a_values, $negate, $a_type); // TODO: Change the autogenerated stub
	}


	function addTypesToArray($a_arr, $a_type, $a_cnt) {
		return parent::addTypesToArray($a_arr, $a_type, $a_cnt); // TODO: Change the autogenerated stub
	}


	function now() {
		return parent::now(); // TODO: Change the autogenerated stub
	}


	public function concat($a_values, $a_allow_null = true) {
		return parent::concat($a_values, $a_allow_null); // TODO: Change the autogenerated stub
	}


	function substr($a_exp, $a_pos = 1, $a_len = - 1) {
		return parent::substr($a_exp, $a_pos, $a_len); // TODO: Change the autogenerated stub
	}


	function upper($a_exp) {
		return parent::upper($a_exp); // TODO: Change the autogenerated stub
	}


	function lower($a_exp) {
		return parent::lower($a_exp); // TODO: Change the autogenerated stub
	}


	public function locate($a_needle, $a_string, $a_start_pos = 1) {
		return parent::locate($a_needle, $a_string, $a_start_pos); // TODO: Change the autogenerated stub
	}


	function like($a_col, $a_type, $a_value = "?", $case_insensitive = true) {
		return parent::like($a_col, $a_type, $a_value, $case_insensitive); // TODO: Change the autogenerated stub
	}


	function equals($a_col, $a_value, $a_type, $a_empty_or_null = false) {
		return parent::equals($a_col, $a_value, $a_type, $a_empty_or_null); // TODO: Change the autogenerated stub
	}


	function equalsNot($a_col, $a_value, $a_type, $a_empty_or_null = false) {
		return parent::equalsNot($a_col, $a_value, $a_type, $a_empty_or_null); // TODO: Change the autogenerated stub
	}


	function fromUnixtime($a_expr, $a_to_text = true) {
		return parent::fromUnixtime($a_expr, $a_to_text); // TODO: Change the autogenerated stub
	}


	function unixTimestamp() {
		return parent::unixTimestamp(); // TODO: Change the autogenerated stub
	}


	function optimizeTable($a_table) {
		parent::optimizeTable($a_table); // TODO: Change the autogenerated stub
	}


	function uniqueConstraintExists($a_table, $a_fields) {
		return parent::uniqueConstraintExists($a_table, $a_fields); // TODO: Change the autogenerated stub
	}


	function listTables() {
		return parent::listTables(); // TODO: Change the autogenerated stub
	}


	function sequenceExists($a_sequence) {
		return parent::sequenceExists($a_sequence); // TODO: Change the autogenerated stub
	}


	function listSequences() {
		return parent::listSequences(); // TODO: Change the autogenerated stub
	}


	function quoteIdentifier($a_identifier) {
		return parent::quoteIdentifier($a_identifier); // TODO: Change the autogenerated stub
	}


	function beginTransaction() {
		return parent::beginTransaction(); // TODO: Change the autogenerated stub
	}


	function commit() {
		return parent::commit(); // TODO: Change the autogenerated stub
	}


	function rollback() {
		return parent::rollback(); // TODO: Change the autogenerated stub
	}


	function autoExecute($a_tablename, $a_fields, $a_mode = MDB2_AUTOQUERY_INSERT, $a_where = false) {
		return parent::autoExecute($a_tablename, $a_fields, $a_mode, $a_where); // TODO: Change the autogenerated stub
	}


	function getLastInsertId() {
		return parent::getLastInsertId(); // TODO: Change the autogenerated stub
	}


	function getOne($sql) {
		return parent::getOne($sql); // TODO: Change the autogenerated stub
	}


	function getRow($sql, $mode = DB_FETCHMODE_OBJECT) {
		return parent::getRow($sql, $mode); // TODO: Change the autogenerated stub
	}


	function setSubType($a_value) {
		$this->sub_type = (string)$a_value;
	}


	function getSubType() {
		return $this->sub_type;
	}
}


?>
