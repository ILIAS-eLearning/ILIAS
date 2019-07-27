<?php
namespace ILIAS\AssessmentQuestion\Example\Config;


use ilDBConstants;
use \PDO;
use ilDBInterface;

class SQLiteDB implements ilDBInterface {

	const SQLITE_PRIMARY_KEY = 'PRIMARY KEY';

	const SQLITE_INTEGER = 'INTEGER';
	const SQLITE_TEXT = 'TEXT';
	const SQLITE_BLOB = 'BLOB';
	const SQLITE_REAL = 'REAL'; //DOUBLE, DOUBLE PRECISION, FLOAT
	const SQLITE_NUMERIC = 'NUMERIC'; //NUMERIC, DECIMAL(10,5), BOOLEAN, DATE, DATETIME


	/**
	 * @var self|null
	 */
	protected static $instance = null;
	/**
	 * @var PDO
	 */
	protected $memory_db;

	/**
	 * @return self
	 */
	public static function getInstance(): self {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	private function __construct() {
		$this->createDB();
		$this->createTables();
	}

	protected function createDB() {
		// Create new database in memory
		$this->memory_db = new PDO('sqlite::memory:');
		// Set errormode to exceptions
		$this->memory_db->setAttribute(PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION);
	}

	protected function createTables() {

		// Create table messages
		$this->memory_db->exec("CREATE TABLE IF NOT EXISTS events (
                    aggregate_id ".self::SQLITE_TEXT." ".self::SQLITE_PRIMARY_KEY." ,
                    type ".self::SQLITE_TEXT.",
                    created_at ".self::SQLITE_NUMERIC.",
                    data ".self::SQLITE_TEXT.")");
	}


	public function prepare($stmt, $a_types = null, $a_result_types = null) {
		return $this->memory_db->prepare($stmt);
	}


	/**
	 * @param \PDOStatement $stmt
	 * @param array $input_parameters
	 */
	public function execute($stmt,$input_parameters = []): void {
		$stmt->execute($input_parameters);
	}


	public function doesCollationSupportMB4Strings() {
		// TODO: Implement doesCollationSupportMB4Strings() method.
	}


	public function sanitizeMB4StringIfNotSupported($query) {
		// TODO: Implement sanitizeMB4StringIfNotSupported() method.
	}


	static function getReservedWords() {
		// TODO: Implement getReservedWords() method.
	}


	public function initFromIniFile($tmpClientIniFile = null) {
		// TODO: Implement initFromIniFile() method.
	}


	public function connect($return_false_on_error = false) {
		// TODO: Implement connect() method.
	}


	public function nextId($table_name) {
		// TODO: Implement nextId() method.
	}


	public function createTable($table_name, $fields, $drop_table = false, $ignore_erros = false) {
		// TODO: Implement createTable() method.
	}


	public function addPrimaryKey($table_name, $primary_keys) {
		// TODO: Implement addPrimaryKey() method.
	}


	public function createSequence($table_name, $start = 1) {
		// TODO: Implement createSequence() method.
	}


	public function getSequenceName($table_name) {
		// TODO: Implement getSequenceName() method.
	}


	public function tableExists($table_name) {
		// TODO: Implement tableExists() method.
	}


	public function tableColumnExists($table_name, $column_name) {
		// TODO: Implement tableColumnExists() method.
	}


	public function addTableColumn($table_name, $column_name, $attributes) {
		// TODO: Implement addTableColumn() method.
	}


	public function dropTable($table_name, $error_if_not_existing = true) {
		// TODO: Implement dropTable() method.
	}


	public function renameTable($old_name, $new_name) {
		// TODO: Implement renameTable() method.
	}


	public function query($query) {
		// TODO: Implement query() method.
	}


	public function fetchAll($query_result, $fetch_mode = ilDBConstants::FETCHMODE_ASSOC) {
		// TODO: Implement fetchAll() method.
	}


	public function dropSequence($table_name) {
		// TODO: Implement dropSequence() method.
	}


	public function dropTableColumn($table_name, $column_name) {
		// TODO: Implement dropTableColumn() method.
	}


	public function renameTableColumn($table_name, $column_old_name, $column_new_name) {
		// TODO: Implement renameTableColumn() method.
	}


	public function insert($table_name, $values) {
		// TODO: Implement insert() method.
	}


	public function fetchObject($query_result) {
		// TODO: Implement fetchObject() method.
	}


	public function update($table_name, $values, $where) {
		// TODO: Implement update() method.
	}


	public function manipulate($query) {
		// TODO: Implement manipulate() method.
	}


	public function fetchAssoc($query_result) {
		// TODO: Implement fetchAssoc() method.
	}


	public function numRows($query_result) {
		// TODO: Implement numRows() method.
	}


	public function quote($value, $type) {
		// TODO: Implement quote() method.
	}


	public function addIndex($table_name, $fields, $index_name = '', $fulltext = false) {
		// TODO: Implement addIndex() method.
	}


	public function indexExistsByFields($table_name, $fields) {
		// TODO: Implement indexExistsByFields() method.
	}


	public function getDSN() {
		// TODO: Implement getDSN() method.
	}


	public function getDBType() {
		// TODO: Implement getDBType() method.
	}


	public function lockTables($tables) {
		// TODO: Implement lockTables() method.
	}


	public function unlockTables() {
		// TODO: Implement unlockTables() method.
	}


	public function in($field, $values, $negate = false, $type = "") {
		// TODO: Implement in() method.
	}


	public function queryF($query, $types, $values) {
		// TODO: Implement queryF() method.
	}


	public function manipulateF($query, $types, $values) {
		// TODO: Implement manipulateF() method.
	}


	public function useSlave($bool) {
		// TODO: Implement useSlave() method.
	}


	public function setLimit($limit, $offset) {
		// TODO: Implement setLimit() method.
	}


	public function like($column, $type, $value = "?", $case_insensitive = true) {
		// TODO: Implement like() method.
	}


	public function now() {
		// TODO: Implement now() method.
	}


	public function replace($table, $primaryKeys, $otherColumns) {
		// TODO: Implement replace() method.
	}


	public function equals($columns, $value, $type, $emptyOrNull = false) {
		// TODO: Implement equals() method.
	}


	public function setDBUser($user) {
		// TODO: Implement setDBUser() method.
	}


	public function setDBPort($port) {
		// TODO: Implement setDBPort() method.
	}


	public function setDBPassword($password) {
		// TODO: Implement setDBPassword() method.
	}


	public function setDBHost($host) {
		// TODO: Implement setDBHost() method.
	}


	public function upper($a_exp) {
		// TODO: Implement upper() method.
	}


	public function lower($a_exp) {
		// TODO: Implement lower() method.
	}


	public function substr($a_exp) {
		// TODO: Implement substr() method.
	}


	public function prepareManip($a_query, $a_types = null) {
		// TODO: Implement prepareManip() method.
	}


	public function enableResultBuffering($a_status) {
		// TODO: Implement enableResultBuffering() method.
	}


	public function sequenceExists($sequence) {
		// TODO: Implement sequenceExists() method.
	}


	public function listSequences() {
		// TODO: Implement listSequences() method.
	}


	public function supports($feature) {
		// TODO: Implement supports() method.
	}


	public function supportsFulltext() {
		// TODO: Implement supportsFulltext() method.
	}


	public function supportsSlave() {
		// TODO: Implement supportsSlave() method.
	}


	public function supportsTransactions() {
		// TODO: Implement supportsTransactions() method.
	}


	public function listTables() {
		// TODO: Implement listTables() method.
	}


	public function loadModule($module) {
		// TODO: Implement loadModule() method.
	}


	public function getAllowedAttributes() {
		// TODO: Implement getAllowedAttributes() method.
	}


	public function concat(array $values, $allow_null = true) {
		// TODO: Implement concat() method.
	}


	public function locate($a_needle, $a_string, $a_start_pos = 1) {
		// TODO: Implement locate() method.
	}


	public function quoteIdentifier($identifier, $check_option = false) {
		// TODO: Implement quoteIdentifier() method.
	}


	public function modifyTableColumn($table, $column, $attributes) {
		// TODO: Implement modifyTableColumn() method.
	}


	public function free($a_st) {
		// TODO: Implement free() method.
	}


	public function checkTableName($a_name) {
		// TODO: Implement checkTableName() method.
	}


	public static function isReservedWord($a_word) {
		// TODO: Implement isReservedWord() method.
	}


	public function beginTransaction() {
		// TODO: Implement beginTransaction() method.
	}


	public function commit() {
		// TODO: Implement commit() method.
	}


	public function rollback() {
		// TODO: Implement rollback() method.
	}


	public function constraintName($a_table, $a_constraint) {
		// TODO: Implement constraintName() method.
	}


	public function dropIndex($a_table, $a_name = "i1") {
		// TODO: Implement dropIndex() method.
	}


	public function createDatabase($a_name, $a_charset = "utf8", $a_collation = "") {
		// TODO: Implement createDatabase() method.
	}


	public function dropIndexByFields($table_name, $afields) {
		// TODO: Implement dropIndexByFields() method.
	}


	public function getPrimaryKeyIdentifier() {
		// TODO: Implement getPrimaryKeyIdentifier() method.
	}


	public function addFulltextIndex($table_name, $afields, $a_name = 'in') {
		// TODO: Implement addFulltextIndex() method.
	}


	public function dropFulltextIndex($a_table, $a_name) {
		// TODO: Implement dropFulltextIndex() method.
	}


	public function isFulltextIndex($a_table, $a_name) {
		// TODO: Implement isFulltextIndex() method.
	}


	public function setStorageEngine($storage_engine) {
		// TODO: Implement setStorageEngine() method.
	}


	public function getStorageEngine() {
		// TODO: Implement getStorageEngine() method.
	}


	public function buildAtomQuery() {
		// TODO: Implement buildAtomQuery() method.
	}


	public function groupConcat($a_field_name, $a_seperator = ",", $a_order = null) {
		// TODO: Implement groupConcat() method.
	}


	public function cast($a_field_name, $a_dest_type) {
		// TODO: Implement cast() method.
	}
}