<?php

class pdoDB {

	/**
	 * @var PDO
	 */
	protected $pdo;
	protected static $staticPbo;
	/**
	 * @var array
	 */
	protected $type_to_mysql_type = array(
		'text' => 'VARCHAR',
		'integer' => 'INT',
		'float' => 'DOUBLE',
		'date' => 'DATE',
		'time' => 'TIME',
		'datetime' => 'TIMESTAMP',
		'clob' => 'LONGTEXT',

	);


	public function __construct() {
		$this->pdo = new PDO('mysql:host=localhost;dbname=test_db;charset=utf8', 'travis', '');
		$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$attr = PDO::MYSQL_ATTR_USE_BUFFERED_QUERY;
	}


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
}

?>
