<?php

/**
 * Class ilDBPdoFieldDefinition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdoFieldDefinition {

	const DEFAULT_DECIMAL_PLACES = 2;
	const DEFAULT_TEXT_LENGTH = 4000;
	const DEFINITION_COLUMN_NAME = "/^[a-z]+[_a-z0-9]*$/";
	const DEFINITION_TABLE_NAME = "/^[a-z]+[_a-z0-9]*$/";
	const INDEX_FORMAT = '%s_idx';
	const SEQUENCE_COLUMNS_NAME = 'sequence';
	const SEQUENCE_FORMAT = '%s_seq';
	const T_BLOB = 'blob';
	const T_CLOB = 'clob';
	const T_DATE = 'date';
	const T_DATETIME = 'datetime';
	const T_FLOAT = 'float';
	const T_INTEGER = 'integer';
	const T_TEXT = 'text';
	const T_TIME = 'time';
	const T_TIMESTAMP = 'timestamp';
	/**
	 * @var ilDBPdoFieldDefinition
	 */
	protected static $instance;
	/**
	 * @var array
	 */
	public $allowed_attributes = array(
		self::T_TEXT      => array( 'length', 'notnull', 'default', 'fixed' ),
		self::T_INTEGER   => array( 'length', 'notnull', 'default', 'unsigned' ),
		self::T_FLOAT     => array( 'notnull', 'default' ),
		self::T_DATE      => array( 'notnull', 'default' ),
		self::T_TIME      => array( 'notnull', 'default' ),
		self::T_TIMESTAMP => array( 'notnull', 'default' ),
		self::T_CLOB      => array( 'notnull', 'default' ),
		self::T_BLOB      => array( 'notnull', 'default' ),
	);
	/**
	 * @var ilDBInterface
	 */
	protected $db_instance;
	/**
	 * @var array
	 */
	protected $max_length = array(
		self::T_INTEGER => array( 1, 2, 3, 4, 8 ),
		self::T_TEXT    => 4000,
	);
	/**
	 * @var array
	 */
	protected $available_types = array(
		self::T_TEXT,
		self::T_INTEGER,
		self::T_FLOAT,
		self::T_DATE,
		self::T_TIME,
		self::T_TIMESTAMP,
		self::T_CLOB,
		self::T_BLOB,
	);
	/**
	 * @var array
	 */
	protected $reserved = array(
		"ACCESSIBLE",
		"ADD",
		"ALL",
		"ALTER",
		"ANALYZE",
		"AND",
		"AS",
		"ASC",
		"ASENSITIVE",
		"BEFORE",
		"BETWEEN",
		"BIGINT",
		"BINARY",
		"BLOB",
		"BOTH",
		"BY",
		"CALL",
		"CASCADE",
		"CASE",
		"CHANGE",
		"CHAR",
		"CHARACTER",
		"CHECK",
		"COLLATE",
		"COLUMN",
		"CONDITION",
		"CONSTRAINT",
		"CONTINUE",
		"CONVERT",
		"CREATE",
		"CROSS",
		"CURRENT_DATE",
		"CURRENT_TIME",
		"CURRENT_TIMESTAMP",
		"CURRENT_USER",
		"CURSOR",
		"DATABASE",
		"DATABASES",
		"DAY_HOUR",
		"DAY_MICROSECOND",
		"DAY_MINUTE",
		"DAY_SECOND",
		"DEC",
		"DECIMAL",
		"DECLARE",
		"DEFAULT",
		"DELAYED",
		"DELETE",
		"DESC",
		"DESCRIBE",
		"DETERMINISTIC",
		"DISTINCT",
		"DISTINCTROW",
		"DIV",
		"DOUBLE",
		"DROP",
		"DUAL",
		"EACH",
		"ELSE",
		"ELSEIF",
		"ENCLOSED",
		"ESCAPED",
		"EXISTS",
		"EXIT",
		"EXPLAIN",
		"FALSE",
		"FETCH",
		"FLOAT",
		"FLOAT4",
		"FLOAT8",
		"FOR",
		"FORCE",
		"FOREIGN",
		"FROM",
		"FULLTEXT",
		"GRANT",
		"GROUP",
		"HAVING",
		"HIGH_PRIORITY",
		"HOUR_MICROSECOND",
		"HOUR_MINUTE",
		"HOUR_SECOND",
		"IF",
		"IGNORE",
		"IN",
		"INDEX",
		"INFILE",
		"INNER",
		"INOUT",
		"INSENSITIVE",
		"INSERT",
		"INT",
		"INT1",
		"INT2",
		"INT3",
		"INT4",
		"INT8",
		"INTEGER",
		"INTERVAL",
		"INTO",
		"IS",
		"ITERATE",
		"JOIN",
		"KEY",
		"KEYS",
		"KILL",
		"LEADING",
		"LEAVE",
		"LEFT",
		"LIKE",
		"LIMIT",
		"LINEAR",
		"LINES",
		"LOAD",
		"LOCALTIME",
		"LOCALTIMESTAMP",
		"LOCK",
		"LONG",
		"LONGBLOB",
		"LONGTEXT",
		"LOOP",
		"LOW_PRIORITY",
		"MASTER_SSL_VERIFY_SERVER_CERT",
		"MATCH",
		"MEDIUMBLOB",
		"MEDIUMINT",
		"MEDIUMTEXT",
		"MIDDLEINT",
		"MINUTE_MICROSECOND",
		"MINUTE_SECOND",
		"MOD",
		"MODIFIES",
		"NATURAL",
		"NOT",
		"NO_WRITE_TO_BINLOG",
		"NULL",
		"NUMERIC",
		"ON",
		"OPTIMIZE",
		"OPTION",
		"OPTIONALLY",
		"OR",
		"ORDER",
		"OUT",
		"OUTER",
		"OUTFILE",
		"PRECISION",
		"PRIMARY",
		"PROCEDURE",
		"PURGE",
		"RANGE",
		"READ",
		"READS",
		"READ_WRITE",
		"REAL",
		"REFERENCES",
		"REGEXP",
		"RELEASE",
		"RENAME",
		"REPEAT",
		"REPLACE",
		"REQUIRE",
		"RESTRICT",
		"RETURN",
		"REVOKE",
		"RIGHT",
		"RLIKE",
		"SCHEMA",
		"SCHEMAS",
		"SECOND_MICROSECOND",
		"SELECT",
		"SENSITIVE",
		"SEPARATOR",
		"SET",
		"SHOW",
		"SMALLINT",
		"SPATIAL",
		"SPECIFIC",
		"SQL",
		"SQLEXCEPTION",
		"SQLSTATE",
		"SQLWARNING",
		"SQL_BIG_RESULT",
		"SQL_CALC_FOUND_ROWS",
		"SQL_SMALL_RESULT",
		"SSL",
		"STARTING",
		"STRAIGHT_JOIN",
		"TABLE",
		"TERMINATED",
		"THEN",
		"TINYBLOB",
		"TINYINT",
		"TINYTEXT",
		"TO",
		"TRAILING",
		"TRIGGER",
		"TRUE",
		"UNDO",
		"UNION",
		"UNIQUE",
		"UNLOCK",
		"UNSIGNED",
		"UPDATE",
		"USAGE",
		"USE",
		"USING",
		"UTC_DATE",
		"UTC_TIME",
		"UTC_TIMESTAMP",
		"VALUES",
		"VARBINARY",
		"VARCHAR",
		"VARCHARACTER",
		"VARYING",
		"WHEN",
		"WHERE",
		"WHILE",
		"WITH",
		"WRITE",
		"XOR",
		"YEAR_MONTH",
		"ZEROFILL",
	);
	/**
	 * @var
	 */
	protected $query_utils;


	/**
	 * ilDBPdoFieldDefinition constructor.
	 *
	 * @param \ilDBInterface $ilDBInterface
	 */
	public function __construct(\ilDBInterface $ilDBInterface) {
		$this->db_instance = $ilDBInterface;
	}


	/**
	 * @return \ilMySQLQueryUtils
	 */
	protected function getQueryUtils() {
		if (!$this->query_utils) {
			$this->query_utils = new ilMySQLQueryUtils($this->db_instance);
		}

		return $this->query_utils;
	}


	/**
	 * @var array
	 */
	protected $valid_default_values = array(
		'text'      => '',
		'boolean'   => true,
		'integer'   => 0,
		'decimal'   => 0.0,
		'float'     => 0.0,
		'timestamp' => '1970-01-01 00:00:00',
		'time'      => '00:00:00',
		'date'      => '1970-01-01',
		'clob'      => '',
		'blob'      => '',
	);


	/**
	 * @param $table_name
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public function checkTableName($table_name) {
		if (!preg_match(self::DEFINITION_TABLE_NAME, $table_name)) {
			throw new ilDatabaseException('Table name must only contain _a-z0-9 and must start with a-z.');
		}

		if ($this->isReserved($table_name)) {
			throw new ilDatabaseException("Invalid table name '" . $table_name . "' (Reserved Word).");
		}

		if (strtolower(substr($table_name, 0, 4)) == "sys_") {
			throw new ilDatabaseException("Invalid table name '" . $table_name . "'. Name must not start with 'sys_'.");
		}

		if (strlen($table_name) > 22) {
			throw new ilDatabaseException("Invalid table name '" . $table_name . "'. Maximum table identifer length is 22 bytes.");
		}

		return true;
	}


	/**
	 * @param $table_name
	 * @return bool
	 */
	public function isReserved($table_name) {
		return in_array(strtoupper($table_name), $this->getReserved());
	}


	/**
	 * @return array
	 */
	public function getReserved() {
		return $this->reserved;
	}


	/**
	 * @param array $reserved
	 */
	public function setReserved($reserved) {
		$this->reserved = $reserved;
	}


	/**
	 * @param $column_name
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public function checkColumnName($column_name) {
		if (!preg_match("/^[a-z]+[_a-z0-9]*$/", $column_name)) {
			throw new ilDatabaseException("Invalid column name '" . $column_name
			                              . "'. Column name must only contain _a-z0-9 and must start with a-z.");
		}

		if ($this->isReserved($column_name)) {
			throw new ilDatabaseException("Invalid column name '" . $column_name . "' (Reserved Word).");
		}

		if (strtolower(substr($column_name, 0, 4)) == "sys_") {
			throw new ilDatabaseException("Invalid column name '" . $column_name . "'. Name must not start with 'sys_'.");
		}

		if (strlen($column_name) > 30) {
			throw new ilDatabaseException("Invalid column name '" . $column_name . "'. Maximum column identifer length is 30 bytes.");
		}

		return true;
	}


	/**
	 * @param $a_name
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public function checkIndexName($a_name) {
		if (!preg_match("/^[a-z]+[_a-z0-9]*$/", $a_name)) {
			throw new ilDatabaseException("Invalid column name '" . $a_name . "'. Column name must only contain _a-z0-9 and must start with a-z.");
		}

		if ($this->isReserved($a_name)) {
			throw new ilDatabaseException("Invalid column name '" . $a_name . "' (Reserved Word).");
		}

		if (strlen($a_name) > 3) {
			throw new ilDatabaseException("Invalid index name '" . $a_name . "'. Maximum index identifer length is 3 bytes.");
		}

		return true;
	}


	/**
	 * @param $a_def
	 * @return bool
	 * @throws \ilDatabaseException
	 */
	public function checkColumnDefinition($a_def) {
		// check valid type
		if (!in_array($a_def["type"], $this->getAvailableTypes())) {
			switch ($a_def["type"]) {
				case "boolean":
					throw new ilDatabaseException("Invalid column type '" . $a_def["type"] . "'. Use integer(1) instead.");
					break;

				case "decimal":
					throw new ilDatabaseException("Invalid column type '" . $a_def["type"] . "'. Use float or integer instead.");
					break;

				default:
					throw new ilDatabaseException("Invalid column type '" . $a_def["type"] . "'. Allowed types are: "
					                              . implode(', ', $this->getAvailableTypes()));
			}
		}

		// check used attributes
		$allowed_attributes = $this->getAllowedAttributes();
		foreach ($a_def as $k => $v) {
			if ($k != "type" && !in_array($k, $allowed_attributes[$a_def["type"]])) {
				throw new ilDatabaseException("Attribute '" . $k . "' is not allowed for column type '" . $a_def["type"] . "'.");
			}
		}

		// type specific checks
		$max_length = $this->getMaxLength();
		switch ($a_def["type"]) {
			case self::T_TEXT:
				if ($a_def["length"] < 1 || $a_def["length"] > $max_length[self::T_TEXT]) {
					if (isset($a_def["length"])) {
						throw new ilDatabaseException("Invalid length '" . $a_def["length"] . "' for type text." . " Length must be >=1 and <= "
						                              . $max_length[self::T_TEXT] . ".");
					}
				}
				break;

			case self::T_INTEGER:
				if (!in_array($a_def["length"], $max_length[self::T_INTEGER])) {
					if (isset($a_def["length"])) {
						throw new ilDatabaseException("Invalid length '" . $a_def["length"] . "' for type integer." . " Length must be "
						                              . implode(', ', $max_length[self::T_INTEGER]) . " (bytes).");
					}
				}
				if ($a_def["unsigned"]) {
					throw new ilDatabaseException("Unsigned attribut must not be true for type integer.");
				}
				break;
		}

		return true;
	}


	/**
	 * @param $attribute
	 * @param $type
	 * @return bool
	 */
	public function isAllowedAttribute($attribute, $type) {
		return in_array($attribute, $this->allowed_attributes[$type]);
	}


	/**
	 * @return array
	 */
	public function getAvailableTypes() {
		return $this->available_types;
	}


	/**
	 * @param array $available_types
	 */
	public function setAvailableTypes($available_types) {
		$this->available_types = $available_types;
	}


	/**
	 * @return array
	 */
	public function getAllowedAttributes() {
		return $this->allowed_attributes;
	}


	/**
	 * @param array $allowed_attributes
	 */
	public function setAllowedAttributes($allowed_attributes) {
		$this->allowed_attributes = $allowed_attributes;
	}


	/**
	 * @return array
	 */
	public function getMaxLength() {
		return $this->max_length;
	}


	/**
	 * @param array $max_length
	 */
	public function setMaxLength($max_length) {
		$this->max_length = $max_length;
	}


	/**
	 * @return \ilDBPdo
	 */
	protected function getDBInstance() {
		return $this->db_instance;
	}


	/**
	 * @return array
	 */
	public function getValidTypes() {
		$types = $this->valid_default_values;
		$db = $this->getDBInstance();

		if (!empty($db->options['datatype_map'])) {
			foreach ($db->options['datatype_map'] as $type => $mapped_type) {
				if (array_key_exists($mapped_type, $types)) {
					$types[$type] = $types[$mapped_type];
				} elseif (!empty($db->options['datatype_map_callback'][$type])) {
					$parameter = array( 'type' => $type, 'mapped_type' => $mapped_type );
					$default = call_user_func_array($db->options['datatype_map_callback'][$type], array( &$db, __FUNCTION__, $parameter ));
					$types[$type] = $default;
				}
			}
		}

		return $types;
	}


	/**
	 * @param $types
	 * @return array|\ilDBInterface
	 * @throws \ilDatabaseException
	 */
	protected function checkResultTypes($types) {
		$types = is_array($types) ? $types : array( $types );
		foreach ($types as $key => $type) {
			if (!isset($this->valid_default_values[$type])) {
				$db = $this->getDBInstance();
				if (empty($db->options['datatype_map'][$type])) {
					throw new ilDatabaseException($type . ' for ' . $key . ' is not a supported column type');
				}
			}
		}

		return $types;
	}


	/**
	 * @param $value
	 * @param $type
	 * @param bool $rtrim
	 * @return bool|float|int|resource|string
	 * @throws \ilDatabaseException
	 */
	protected function baseConvertResult($value, $type, $rtrim = true) {
		switch ($type) {
			case 'text':
				if ($rtrim) {
					$value = rtrim($value);
				}

				return $value;
			case 'integer':
				return intval($value);
			case 'boolean':
				return !empty($value);
			case 'decimal':
				return $value;
			case 'float':
				return doubleval($value);
			case 'date':
				return $value;
			case 'time':
				return $value;
			case 'timestamp':
				return $value;
			case 'clob':
			case 'blob':
				$this->lobs[] = array(
					'buffer'    => null,
					'position'  => 0,
					'lob_index' => null,
					'endOfLOB'  => false,
					'resource'  => $value,
					'value'     => null,
					'loaded'    => false,
				);
				end($this->lobs);
				$lob_index = key($this->lobs);
				$this->lobs[$lob_index]['lob_index'] = $lob_index;

				return fopen('MDB2LOB://' . $lob_index . '@' . $this->db_index, 'r+');
		}

		throw new ilDatabaseException('attempt to convert result value to an unknown type :' . $type);
	}


	/**
	 * @param $value
	 * @param $type
	 * @param bool $rtrim
	 * @return bool|float|int|mixed|null|resource|string
	 * @throws \ilDatabaseException
	 */
	public function convertResult($value, $type, $rtrim = true) {
		if (is_null($value)) {
			return null;
		}
		$db = $this->getDBInstance();

		if (!empty($db->options['datatype_map'][$type])) {
			$type = $db->options['datatype_map'][$type];
			if (!empty($db->options['datatype_map_callback'][$type])) {
				$parameter = array( 'type' => $type, 'value' => $value, 'rtrim' => $rtrim );

				return call_user_func_array($db->options['datatype_map_callback'][$type], array( &$db, __FUNCTION__, $parameter ));
			}
		}

		return $this->baseConvertResult($value, $type, $rtrim);
	}


	/**
	 * @param $types
	 * @param $row
	 * @param bool $rtrim
	 * @return bool|float|int|mixed|null|resource|string
	 */
	public function convertResultRow($types, $row, $rtrim = true) {
		$types = $this->sortResultFieldTypes(array_keys($row), $types);
		foreach ($row as $key => $value) {
			if (empty($types[$key])) {
				continue;
			}
			$value = $this->convertResult($row[$key], $types[$key], $rtrim);

			$row[$key] = $value;
		}

		return $row;
	}

	// }}}
	// {{{ _sortResultFieldTypes()

	/**
	 * @param $columns
	 * @param $types
	 * @return array
	 */
	protected function sortResultFieldTypes($columns, $types) {
		$n_cols = count($columns);
		$n_types = count($types);
		if ($n_cols > $n_types) {
			for ($i = $n_cols - $n_types; $i >= 0; $i --) {
				$types[] = null;
			}
		}
		$sorted_types = array();
		foreach ($columns as $col) {
			$sorted_types[$col] = null;
		}
		foreach ($types as $name => $type) {
			if (array_key_exists($name, $sorted_types)) {
				$sorted_types[$name] = $type;
				unset($types[$name]);
			}
		}
		// if there are left types in the array, fill the null values of the
		// sorted array with them, in order.
		if (count($types)) {
			reset($types);
			foreach (array_keys($sorted_types) as $k) {
				if (is_null($sorted_types[$k])) {
					$sorted_types[$k] = current($types);
					next($types);
				}
			}
		}

		return $sorted_types;
	}


	/**
	 * @param $type
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|mixed
	 * @throws \ilDatabaseException
	 */
	public function getDeclaration($type, $name, $field) {
		$db = $this->getDBInstance();

		if (!empty($db->options['datatype_map'][$type])) {
			$type = $db->options['datatype_map'][$type];
			if (!empty($db->options['datatype_map_callback'][$type])) {
				$parameter = array( 'type' => $type, 'name' => $name, 'field' => $field );

				return call_user_func_array($db->options['datatype_map_callback'][$type], array( &$db, __FUNCTION__, $parameter ));
			}
			$field['type'] = $type;
		}

		if (!method_exists($this, "get{$type}Declaration")) {
			throw new ilDatabaseException('type not defined: ' . $type);
		}

		return $this->{"get{$type}Declaration"}($name, $field);
	}


	/**
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	public function getTypeDeclaration($field) {
		$db = $this->getDBInstance();

		switch ($field['type']) {
			case 'text':
				$length = !empty($field['length']) ? $field['length'] : $db->options['default_text_field_length'];
				$fixed = !empty($field['fixed']) ? $field['fixed'] : false;

				return $fixed ? ($length ? 'CHAR(' . $length . ')' : 'CHAR(' . $db->options['default_text_field_length']
				                                                     . ')') : ($length ? 'VARCHAR(' . $length . ')' : 'TEXT');
			case 'clob':
				return 'TEXT';
			case 'blob':
				return 'TEXT';
			case 'integer':
				return 'INT';
			case 'boolean':
				return 'INT';
			case 'date':
				return 'CHAR (' . strlen('YYYY-MM-DD') . ')';
			case 'time':
				return 'CHAR (' . strlen('HH:MM:SS') . ')';
			case 'timestamp':
				return 'CHAR (' . strlen('YYYY-MM-DD HH:MM:SS') . ')';
			case 'float':
				return 'TEXT';
			case 'decimal':
				return 'TEXT';
		}

		return '';
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getInternalDeclaration($name, $field) {
		$db = $this->getDBInstance();

		$name = $db->quoteIdentifier($name, true);
		$declaration_options = $db->getFieldDefinition()->getDeclarationOptions($field);

		return $name . ' ' . $this->getTypeDeclaration($field) . $declaration_options;
	}


	/**
	 * @param $field
	 * @return \ilDBPdo|string
	 * @throws \ilDatabaseException
	 */
	protected function getDeclarationOptions($field) {
		$charset = empty($field['charset']) ? '' : ' ' . $this->getCharsetFieldDeclaration($field['charset']);

		$default = '';
		if (array_key_exists('default', $field)) {
			if ($field['default'] === '') {
				$db = $this->getDBInstance();

				if (empty($field['notnull'])) {
					$field['default'] = null;
				} else {
					$valid_default_values = $this->getValidTypes();
					$field['default'] = $valid_default_values[$field['type']];
				}
				if ($field['default'] === ''
				    && ($db->options['portability'] & 32)
				) {
					$field['default'] = ' ';
				}
			}
			$default = ' DEFAULT ' . $this->quote($field['default'], $field['type']);
		} elseif (empty($field['notnull'])) {
			$default = ' DEFAULT NULL';
		}

		$notnull = empty($field['notnull']) ? '' : ' NOT NULL';
		// alex patch 28 Nov 2011 start
		if ($field['notnull'] === false) {
			$notnull = " NULL";
		}
		// alex patch 28 Nov 2011 end

		$collation = empty($field['collation']) ? '' : ' ' . $this->getCollationFieldDeclaration($field['collation']);

		return $charset . $default . $notnull . $collation;
	}


	/**
	 * @param $charset
	 * @return string
	 */
	protected function getCharsetFieldDeclaration($charset) {
		return '';
	}


	/**
	 * @param $collation
	 * @return string
	 */
	protected function getCollationFieldDeclaration($collation) {
		return '';
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|\ilDBPdo|mixed
	 * @throws \ilDatabaseException
	 */
	protected function getIntegerDeclaration($name, $field) {
		if (!empty($field['unsigned'])) {
			$db = $this->getDBInstance();

			$db->warnings[] = "unsigned integer field \"$name\" is being declared as signed integer";
		}

		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|mixed
	 * @throws \ilDatabaseException
	 */
	protected function getTextDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBPdo|string
	 */
	protected function getCLOBDeclaration($name, $field) {
		$db = $this->getDBInstance();

		$notnull = empty($field['notnull']) ? '' : ' NOT NULL';
		$name = $db->quoteIdentifier($name, true);

		return $name . ' ' . $this->getTypeDeclaration($field) . $notnull;
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBPdo|string
	 */
	protected function getBLOBDeclaration($name, $field) {
		$db = $this->getDBInstance();

		$notnull = empty($field['notnull']) ? '' : ' NOT NULL';
		$name = $db->quoteIdentifier($name, true);

		return $name . ' ' . $this->getTypeDeclaration($field) . $notnull;
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getBooleanDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getDateDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getTimestampDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getTimeDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getFloatDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $name
	 * @param $field
	 * @return \ilDBInterface|string
	 */
	protected function getDecimalDeclaration($name, $field) {
		return $this->getInternalDeclaration($name, $field);
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return \ilDBPdo|mixed
	 * @throws \ilDatabaseException
	 */
	public function compareDefinition($current, $previous) {
		$type = !empty($current['type']) ? $current['type'] : null;

		if (!method_exists($this, "compare{$type}Definition")) {
			$db = $this->getDBInstance();

			if (!empty($db->options['datatype_map_callback'][$type])) {
				$parameter = array( 'current' => $current, 'previous' => $previous );
				$change = call_user_func_array($db->options['datatype_map_callback'][$type], array( &$db, __FUNCTION__, $parameter ));

				return $change;
			}

			throw new ilDatabaseException('type "' . $current['type'] . '" is not yet supported');
		}

		if (empty($previous['type']) || $previous['type'] != $type) {
			return $current;
		}

		$change = $this->{"compare{$type}Definition"}($current, $previous);

		if ($previous['type'] != $type) {
			$change['type'] = true;
		}

		$previous_notnull = !empty($previous['notnull']) ? $previous['notnull'] : false;
		$notnull = !empty($current['notnull']) ? $current['notnull'] : false;
		if ($previous_notnull != $notnull) {
			$change['notnull'] = true;
		}

		$previous_default = array_key_exists('default', $previous) ? $previous['default'] : ($previous_notnull ? '' : null);
		$default = array_key_exists('default', $current) ? $current['default'] : ($notnull ? '' : null);
		if ($previous_default !== $default) {
			$change['default'] = true;
		}

		return $change;
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareIntegerDefinition($current, $previous) {
		$change = array();
		$previous_unsigned = !empty($previous['unsigned']) ? $previous['unsigned'] : false;
		$unsigned = !empty($current['unsigned']) ? $current['unsigned'] : false;
		if ($previous_unsigned != $unsigned) {
			$change['unsigned'] = true;
		}
		$previous_autoincrement = !empty($previous['autoincrement']) ? $previous['autoincrement'] : false;
		$autoincrement = !empty($current['autoincrement']) ? $current['autoincrement'] : false;
		if ($previous_autoincrement != $autoincrement) {
			$change['autoincrement'] = true;
		}

		return $change;
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareTextDefinition($current, $previous) {
		$change = array();
		$previous_length = !empty($previous['length']) ? $previous['length'] : 0;
		$length = !empty($current['length']) ? $current['length'] : 0;
		if ($previous_length != $length) {
			$change['length'] = true;
		}
		$previous_fixed = !empty($previous['fixed']) ? $previous['fixed'] : 0;
		$fixed = !empty($current['fixed']) ? $current['fixed'] : 0;
		if ($previous_fixed != $fixed) {
			$change['fixed'] = true;
		}

		return $change;
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareCLOBDefinition($current, $previous) {
		return $this->compareTextDefinition($current, $previous);
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareBLOBDefinition($current, $previous) {
		return $this->compareTextDefinition($current, $previous);
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareDateDefinition($current, $previous) {
		return array();
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareTimeDefinition($current, $previous) {
		return array();
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareTimestampDefinition($current, $previous) {
		return array();
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareBooleanDefinition($current, $previous) {
		return array();
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareFloatDefinition($current, $previous) {
		return array();
	}


	/**
	 * @param $current
	 * @param $previous
	 * @return array
	 */
	protected function compareDecimalDefinition($current, $previous) {
		return array();
	}


	/**
	 * @param $value
	 * @param null $type
	 * @param bool $quote
	 * @param bool $escape_wildcards
	 * @return \ilDBPdo|mixed|string
	 * @throws \ilDatabaseException
	 */
	public function quote($value, $type = null, $quote = true, $escape_wildcards = false) {
		$db = $this->getDBInstance();
		return $db->quote($value, $type);

		if (is_null($value)
		    || ($value === '' && $db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL)
		) {
			if (!$quote) {
				return null;
			}

			return 'NULL';
		}

		if (is_null($type)) {
			switch (gettype($value)) {
				case 'integer':
					$type = 'integer';
					break;
				case 'double':
					// todo: default to decimal as float is quite unusual
					// $type = 'float';
					$type = 'decimal';
					break;
				case 'boolean':
					$type = 'boolean';
					break;
				case 'array':
					$value = serialize($value);
				case 'object':
					$type = 'text';
					break;
				default:
					if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
						$type = 'timestamp';
					} elseif (preg_match('/^\d{2}:\d{2}$/', $value)) {
						$type = 'time';
					} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
						$type = 'date';
					} else {
						$type = 'text';
					}
					break;
			}
		} elseif (!empty($db->options['datatype_map'][$type])) {
			$type = $db->options['datatype_map'][$type];
			if (!empty($db->options['datatype_map_callback'][$type])) {
				$parameter = array( 'type' => $type, 'value' => $value, 'quote' => $quote, 'escape_wildcards' => $escape_wildcards );

				return call_user_func_array($db->options['datatype_map_callback'][$type], array( &$db, __FUNCTION__, $parameter ));
			}
		}

		if (!method_exists($this, "quote{$type}")) {
			throw new ilDatabaseException('type not defined: ' . $type);
		}
		$value = $this->{"quote{$type}"}($value, $quote, $escape_wildcards);
		if ($quote && $escape_wildcards && $db->string_quoting['escape_pattern']
		    && $db->string_quoting['escape'] !== $db->string_quoting['escape_pattern']
		) {
			$value .= $this->patternEscapeString();
		}

		return $value;
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return int
	 */
	protected function quoteInteger($value, $quote, $escape_wildcards) {
		return (int)$value;
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteText($value, $quote, $escape_wildcards) {
		if (!$quote) {
			return $value;
		}

		$db = $this->getDBInstance();

		$value = $db->escape($value, $escape_wildcards);

		return "'" . $value . "'";
	}


	/**
	 * @param $value
	 * @return \ilDBPdo|string
	 */
	protected function readFile($value) {
		$close = false;
		if (preg_match('/^(\w+:\/\/)(.*)$/', $value, $match)) {
			$close = true;
			if ($match[1] == 'file://') {
				$value = $match[2];
			}
			// do not try to open urls
			#$value = @fopen($value, 'r');
		}

		if (is_resource($value)) {
			$db = $this->getDBInstance();

			$fp = $value;
			$value = '';
			while (!@feof($fp)) {
				$value .= @fread($fp, $db->options['lob_buffer_length']);
			}
			if ($close) {
				@fclose($fp);
			}
		}

		return $value;
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteLOB($value, $quote, $escape_wildcards) {
		$value = $this->readFile($value);

		return $this->quoteText($value, $quote, $escape_wildcards);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteCLOB($value, $quote, $escape_wildcards) {
		return $this->quoteLOB($value, $quote, $escape_wildcards);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteBLOB($value, $quote, $escape_wildcards) {
		return $this->quoteLOB($value, $quote, $escape_wildcards);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return int
	 */
	protected function quoteBoolean($value, $quote, $escape_wildcards) {
		return ($value ? 1 : 0);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteDate($value, $quote, $escape_wildcards) {
		if ($value === 'CURRENT_DATE') {
			$db = $this->getDBInstance();

			return 'CURRENT_DATE';
		}

		return $this->quoteText($value, $quote, $escape_wildcards);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteTimestamp($value, $quote, $escape_wildcards) {
		if ($value === 'CURRENT_TIMESTAMP') {
			$db = $this->getDBInstance();

			if (isset($db->function) && is_a($db->function, 'MDB2_Driver_Function_Common')) {
				return $db->function->now('timestamp');
			}

			return 'CURRENT_TIMESTAMP';
		}

		return $this->quoteText($value, $quote, $escape_wildcards);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return \ilDBPdo|string
	 */
	protected function quoteTime($value, $quote, $escape_wildcards) {
		if ($value === 'CURRENT_TIME') {
			$db = $this->getDBInstance();

			if (isset($db->function) && is_a($db->function, 'MDB2_Driver_Function_Common')) {
				return $db->function->now('time');
			}

			return 'CURRENT_TIME';
		}

		return $this->quoteText($value, $quote, $escape_wildcards);
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return string
	 */
	protected function quoteFloat($value, $quote, $escape_wildcards) {
		if (preg_match('/^(.*)e([-+])(\d+)$/i', $value, $matches)) {
			$decimal = $this->quoteDecimal($matches[1], $quote, $escape_wildcards);
			$sign = $matches[2];
			$exponent = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
			$value = $decimal . 'E' . $sign . $exponent;
		} else {
			$value = $this->quoteDecimal($value, $quote, $escape_wildcards);
		}

		return $value;
	}


	/**
	 * @param $value
	 * @param $quote
	 * @param $escape_wildcards
	 * @return mixed|string
	 */
	protected function quoteDecimal($value, $quote, $escape_wildcards) {
		$value = (string)$value;
		$value = preg_replace('/[^\d\.,\-+eE]/', '', $value);
		if (preg_match('/[^.0-9]/', $value)) {
			if (strpos($value, ',')) {
				// 1000,00
				if (!strpos($value, '.')) {
					// convert the last "," to a "."
					$value = strrev(str_replace(',', '.', strrev($value)));
					// 1.000,00
				} elseif (strpos($value, '.') && strpos($value, '.') < strpos($value, ',')) {
					$value = str_replace('.', '', $value);
					// convert the last "," to a "."
					$value = strrev(str_replace(',', '.', strrev($value)));
					// 1,000.00
				} else {
					$value = str_replace(',', '', $value);
				}
			}
		}

		return $value;
	}


	/**
	 * @param $lob
	 * @param $file
	 * @return bool|\ilDBPdo
	 * @throws \ilDatabaseException
	 */
	public function writeLOBToFile($lob, $file) {
		$db = $this->getDBInstance();

		if (preg_match('/^(\w+:\/\/)(.*)$/', $file, $match)) {
			if ($match[1] == 'file://') {
				$file = $match[2];
			}
		}

		$fp = @fopen($file, 'wb');
		while (!@feof($lob)) {
			$result = @fread($lob, $db->options['lob_buffer_length']);
			$read = strlen($result);
			if (@fwrite($fp, $result, $read) != $read) {
				@fclose($fp);

				throw new ilDatabaseException('could not write to the output file');
			}
		}
		@fclose($fp);

		return MDB2_OK;
	}


	/**
	 * @param $lob
	 * @return bool
	 */
	protected function retrieveLOB(&$lob) {
		if (is_null($lob['value'])) {
			$lob['value'] = $lob['resource'];
		}
		$lob['loaded'] = true;

		return MDB2_OK;
	}


	/**
	 * @param $lob
	 * @param $length
	 * @return string
	 */
	protected function readLOB($lob, $length) {
		return substr($lob['value'], $lob['position'], $length);
	}


	/**
	 * @param $lob
	 * @return mixed
	 */
	protected function endOfLOB($lob) {
		return $lob['endOfLOB'];
	}


	/**
	 * @param $lob
	 * @return bool
	 */
	public function destroyLOB($lob) {
		$lob_data = stream_get_meta_data($lob);
		$lob_index = $lob_data['wrapper_data']->lob_index;
		fclose($lob);
		if (isset($this->lobs[$lob_index])) {
			$this->destroyLOBInternal($this->lobs[$lob_index]);
			unset($this->lobs[$lob_index]);
		}

		return true;
	}


	/**
	 * @param $lob
	 * @return bool
	 */
	protected function destroyLOBInternal(&$lob) {
		return true;
	}


	/**
	 * @param $array
	 * @param bool $type
	 * @return string
	 * @throws \ilDatabaseException
	 */
	public function implodeArray($array, $type = false) {
		if (!is_array($array) || empty($array)) {
			return 'NULL';
		}
		if ($type) {
			foreach ($array as $value) {
				$return[] = $this->quote($value, $type);
			}
		} else {
			$return = $array;
		}

		return implode(', ', $return);
	}


	/**
	 * @param $pattern
	 * @param null $operator
	 * @param null $field
	 * @return \ilDBPdo|string
	 * @throws \ilDatabaseException
	 */
	public function matchPattern($pattern, $operator = null, $field = null) {
		$db = $this->getDBInstance();

		$match = '';
		if (!is_null($operator)) {
			$operator = strtoupper($operator);
			switch ($operator) {
				// case insensitive
				case 'ILIKE':
					if (is_null($field)) {
						throw new ilDatabaseException('case insensitive LIKE matching requires passing the field name');
					}
					$db->loadModule('Function', null, true);
					$match = $db->function->lower($field) . ' LIKE ';
					break;
				// case sensitive
				case 'LIKE':
					$match = is_null($field) ? 'LIKE ' : $field . ' LIKE ';
					break;
				default:
					throw new ilDatabaseException('not a supported operator type:' . $operator);
			}
		}
		$match .= "'";
		foreach ($pattern as $key => $value) {
			if ($key % 2) {
				$match .= $value;
			} else {
				if ($operator === 'ILIKE') {
					$value = strtolower($value);
				}
				$escaped = $db->escape($value);
				$match .= $db->escapePattern($escaped);
			}
		}
		$match .= "'";
		$match .= $this->patternEscapeString();

		return $match;
	}


	/**
	 * @return string
	 */
	public function patternEscapeString() {
		return '';
	}


	/**
	 * @param $field
	 * @return \ilDBPdo|mixed
	 */
	public function mapNativeDatatype($field) {
		$db = $this->getDBInstance();
		$db_type = strtok($field['type'], '(), ');
		if (!empty($db->options['nativetype_map_callback'][$db_type])) {
			return call_user_func_array($db->options['nativetype_map_callback'][$db_type], array( $db, $field ));
		}

		return $this->mapNativeDatatypeInternal($field);
	}


	/**
	 * @param $field
	 * @return \ilDBPdo
	 * @throws \ilDatabaseException
	 */
	abstract protected function mapNativeDatatypeInternal($field);


	/**
	 * @param $type
	 * @return \ilDBPdo|mixed
	 */
	public function mapPrepareDatatype($type) {
		$db = $this->getDBInstance();

		if (!empty($db->options['datatype_map'][$type])) {
			$type = $db->options['datatype_map'][$type];
			if (!empty($db->options['datatype_map_callback'][$type])) {
				$parameter = array( 'type' => $type );

				return call_user_func_array($db->options['datatype_map_callback'][$type], array( &$db, __FUNCTION__, $parameter ));
			}
		}

		return $type;
	}
}

