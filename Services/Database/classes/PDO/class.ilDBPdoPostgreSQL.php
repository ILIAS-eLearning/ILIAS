<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Database/classes/PDO/Manager/class.ilDBPdoManagerPostgres.php');
require_once('class.ilDBPdo.php');
require_once('./Services/Database/classes/PDO/FieldDefinition/class.ilDBPdoPostgresFieldDefinition.php');
require_once('./Services/Database/classes/PDO/Reverse/class.ilDBPdoReversePostgres.php');

/**
 * Class ilDBPdoPostgreSQL
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoPostgreSQL extends ilDBPdo implements ilDBInterface {

	const POSTGRE_STD_PORT = 5432;
	/**
	 * @var int
	 */
	protected $port = self::POSTGRE_STD_PORT;
	/**
	 * @var array
	 */
	protected $additional_attributes = array(
		PDO::ATTR_EMULATE_PREPARES => true,
		PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
	);
	/**
	 * @var string
	 */
	protected $storage_engine = null;
	/**
	 * @var ilDBPdoManagerPostgres
	 */
	protected $manager;


	public function generateDSN() {
		if (!$this->getPort()) {
			$this->setPort(self::POSTGRE_STD_PORT);
		}
		$this->dsn = 'pgsql:host=' . $this->getHost() . ';port=' . $this->getPort() . ';dbname=' . $this->getDbname() . ';user='
		             . $this->getUsername() . ';password=' . $this->getPassword() . '';
	}


	/**
	 * @param bool $return_false_for_error
	 * @return bool
	 * @throws \Exception
	 */
	public function connect($return_false_for_error = false) {
		$this->generateDSN();
		try {
			$this->pdo = new PDO($this->getDSN(), $this->getUsername(), $this->getPassword(), $this->additional_attributes);
			$this->manager = new ilDBPdoManagerPostgres($this->pdo, $this);
			$this->reverse = new ilDBPdoReversePostgres($this->pdo, $this);
			$this->field_definition = new ilDBPdoPostgresFieldDefinition($this);
		} catch (Exception $e) {
			$this->error_code = $e->getCode();
			if ($return_false_for_error) {
				return false;
			}
			throw $e;
		}

		return ($this->pdo->errorCode() == PDO::ERR_NONE);
	}


	/**
	 * Primary key identifier
	 */
	function getPrimaryKeyIdentifier() {
		return "pk";
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
	public function supportsTransactions() {
		return true;
	}


	/**
	 * Replace into method.
	 *
	 * @param    string        table name
	 * @param    array         primary key values: array("field1" => array("text", $name), "field2" => ...)
	 * @param    array         other values: array("field1" => array("text", $name), "field2" => ...)
	 */
	public function replace($a_table, $a_pk_columns, $a_other_columns) {
		$a_columns = array_merge($a_pk_columns, $a_other_columns);
		$fields = array();
		$field_values = array();
		$placeholders = array();
		$types = array();
		$values = array();
		$lobs = false;
		$lob = array();
		$val_field = array();
		$a = array();
		$b = array();
		foreach ($a_columns as $k => $col) {
			if ($col[0] == 'clob' or $col[0] == 'blob') {
				$val_field[] = $this->quote($col[1], 'text') . " " . $k;
			} else {
				$val_field[] = $this->quote($col[1], $col[0]) . " " . $k;
			}
			$fields[] = $k;
			$placeholders[] = "%s";
			$placeholders2[] = ":$k";
			$types[] = $col[0];
			$values[] = $col[1];
			$field_values[$k] = $col[1];
			if ($col[0] == "blob" || $col[0] == "clob") {
				$lobs = true;
				$lob[$k] = $k;
			}
			$a[] = "a." . $k;
			$b[] = "b." . $k;
		}
		$abpk = array();
		$aboc = array();
		$delwhere = array();
		foreach ($a_pk_columns as $k => $col) {
			$abpk[] = "a." . $k . " = b." . $k;
			$delwhere[] = $k . " = " . $this->quote($col[1], $col[0]);
		}
		foreach ($a_other_columns as $k => $col) {
			$aboc[] = "a." . $k . " = b." . $k;
		}
		//		if ($lobs)	// lobs -> use prepare execute (autoexecute broken in PEAR 2.4.1)
		//		{
		$this->manipulate("DELETE FROM " . $a_table . " WHERE " . implode($delwhere, " AND "));
		$this->insert($a_table, $a_columns);

		return true;
	}


	/**
	 * @param array $a_tables
	 * @return bool
	 */
	public function lockTables($a_tables) {
		global $ilLog;

		$locks = array();

		$counter = 0;
		foreach ($a_tables as $table) {
			$lock = 'LOCK TABLE ';

			$lock .= ($table['name'] . ' ');

			switch ($table['type']) {
				case ilDBConstants::LOCK_READ:
					$lock .= ' IN SHARE MODE ';
					break;

				case ilDBConstants::LOCK_WRITE:
					$lock .= ' IN EXCLUSIVE MODE ';
					break;
			}

			$locks[] = $lock;
		}

		// @TODO use and store a unique identifier to allow nested lock/unlocks
		$this->beginTransaction();
		foreach ($locks as $lock) {
			$this->query($lock);
		}

		return true;
	}


	/**
	 * @throws \ilDatabaseException
	 */
	public function unlockTables() {
		$this->commit();
	}


	public function getStorageEngine() {
		return null;
	}


	public function setStorageEngine($storage_engine) {
		return false;
	}
	//
	//
	//

	/**
	 * @param string $table_name
	 * @return mixed
	 * @throws \ilDatabaseException
	 */
	public function nextId($table_name) {
		$sequence_name = $table_name . '_seq';
		$query = "SELECT NEXTVAL('$sequence_name')";
		$result = $this->query($query, 'integer');
		$data = $result->fetchObject();

		return $data->nextval;
	}
	

	/**
	 * @param $table_name
	 * @param bool $error_if_not_existing
	 * @return int
	 */
	public function dropTable($table_name, $error_if_not_existing = false) {
		try {
			$this->pdo->exec("DROP TABLE $table_name");
		} catch (PDOException $PDOException) {
			if ($error_if_not_existing) {
				throw $PDOException;
			}

			return false;
		}

		return true;
	}


	/**
	 * @param $identifier
	 * @param bool $check_option
	 * @return mixed
	 */
	public function quoteIdentifier($identifier, $check_option = false) {
		return $identifier;
	}


	/**
	 * @param string $table_name
	 * @return bool
	 */
	public function tableExists($table_name) {
		$tables = $this->listTables();

		if (is_array($tables)) {
			if (in_array($table_name, $tables)) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param $query
	 * @return string
	 */
	protected function appendLimit($query) {
		if ($this->limit !== null && $this->offset !== null) {
			$query .= ' LIMIT ' . (int)$this->limit . ' OFFSET ' . (int)$this->offset;
			$this->limit = null;
			$this->offset = null;

			return $query;
		}

		return $query;
	}


	/**
	 * @param $table_name  string
	 * @param $column_name string
	 *
	 * @return bool
	 */
	public function tableColumnExists($table_name, $column_name) {
		return in_array($column_name, $this->manager->listTableFields($table_name));
	}


	/**
	 * @param $a_name
	 * @param $a_new_name
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public function renameTable($a_name, $a_new_name) {
		// check table name
		try {
			$this->checkTableName($a_new_name);
		} catch (ilDatabaseException $e) {
			throw new ilDatabaseException("ilDB Error: renameTable(" . $a_name . "," . $a_new_name . ")<br />" . $e->getMessage());
		}

		$this->manager->alterTable($a_name, array( "name" => $a_new_name ), false);

		return true;
	}
}

