<?php

/**
 * Class ilDBPdoFieldDefinition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoFieldDefinition {

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
	protected $ilDBInterface;
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
	 * ilDBPdoFieldDefinition constructor.
	 *
	 * @param \ilDBInterface $ilDBInterface
	 */
	protected function __construct(\ilDBInterface $ilDBInterface) {
		$this->ilDBInterface = $ilDBInterface;
	}


	/**
	 * @param \ilDBInterface $ilDBInterface
	 * @return \ilDBPdoFieldDefinition
	 */
	public static function getInstance(ilDBInterface $ilDBInterface) {
		if (empty(self::$instance)) {
			self::$instance = new self($ilDBInterface);
		}

		return self::$instance;
	}


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
	 * @param $type
	 * @param $field_name
	 * @param array $field_info
	 * @return string
	 */
	public function getDeclaration($type, $field_name, array $field_info) {
		$query = $field_name . ' ' . $this->getTypeDeclaration($type, $field_info);

		switch ($type) {
			case self::T_INTEGER:
				$default = $autoinc = '';
				if (!empty($field_info['autoincrement'])) {
					$autoinc = ' AUTO_INCREMENT PRIMARY KEY';
				} elseif (array_key_exists('default', $field_info)) {
					if ($field_info['default'] === '') {
						$field_info['default'] = empty($field_info['notnull']) ? null : 0;
					}
					$default = ' DEFAULT ' . $this->ilDBInterface->quote($field_info['default'], self::T_INTEGER);
				} elseif (empty($field_info['notnull'])) {
					$default = ' DEFAULT NULL';
				}

				$notnull = empty($field_info['notnull']) ? '' : ' NOT NULL';
				$unsigned = empty($field_info['unsigned']) ? '' : ' UNSIGNED';

				$declaration_options = $unsigned . $default . $notnull . $autoinc;

				break;

			case self::T_CLOB:
			case self::T_BLOB:
				$declaration_options = '';
				break;

			default:
				$declaration_options = $this->getDeclarationOptions($field_info);
				break;
		}

		$field_declaration = $query . $declaration_options;

		return $field_declaration;
	}


	/**
	 * @param $type
	 * @param array $field
	 * @return string
	 * @TODO refactor all SQL to ilMySQLQueryUtils
	 */
	public function getTypeDeclaration($type, array $field) {
		switch ($type) {
			case 'text':
				if (empty($field['length']) && array_key_exists('default', $field)) {
					$field['length'] = self::DEFAULT_TEXT_LENGTH;
				}
				$length = !empty($field['length']) ? $field['length'] : false;
				$fixed = !empty($field['fixed']) ? $field['fixed'] : false;

				return $fixed ? ($length ? 'CHAR(' . $length . ')' : 'CHAR(255)') : ($length ? 'VARCHAR(' . $length . ')' : 'TEXT');
			case 'clob':
				if (!empty($field['length'])) {
					$length = $field['length'];
					if ($length <= 255) {
						return 'TINYTEXT';
					} elseif ($length <= 65532) {
						return 'TEXT';
					} elseif ($length <= 16777215) {
						return 'MEDIUMTEXT';
					}
				}

				return 'LONGTEXT';
			case 'blob':
				if (!empty($field['length'])) {
					$length = $field['length'];
					if ($length <= 255) {
						return 'TINYBLOB';
					} elseif ($length <= 65532) {
						return 'BLOB';
					} elseif ($length <= 16777215) {
						return 'MEDIUMBLOB';
					}
				}

				return 'LONGBLOB';
			case 'integer':
				if (!empty($field['length'])) {
					$length = $field['length'];
					if ($length <= 1) {
						return 'TINYINT';
					} elseif ($length == 2) {
						return 'SMALLINT';
					} elseif ($length == 3) {
						return 'MEDIUMINT';
					} elseif ($length == 4) {
						return 'INT';
					} elseif ($length > 4) {
						return 'BIGINT';
					}
				}

				return 'INT';
			case 'boolean':
				return 'TINYINT(1)';
			case 'date':
				return 'DATE';
			case 'time':
				return 'TIME';
			case 'timestamp':
				return 'DATETIME';
			case 'float':
				return 'DOUBLE';
			case 'decimal':
				$length = !empty($field['length']) ? $field['length'] : 18;
				$scale = !empty($field['scale']) ? $field['scale'] : self::DEFAULT_DECIMAL_PLACES;

				return 'DECIMAL(' . $length . ',' . $scale . ')';
		}

		return '';
	}


	/**
	 * @param array $field
	 * @return string
	 * @TODO refactor all SQL to ilMySQLQueryUtils
	 */
	protected function getDeclarationOptions(array $field) {
		// Charset
		$charset = empty($field['charset']) ? '' : ' CHARACTER SET ' . $field['charset'];

		// Default value
		$default = '';
		if (array_key_exists('default', $field)) {
			if ($field['default'] === '') {

				if (empty($field['notnull'])) {
					$field['default'] = null;
				} else {
					$field['default'] = null;
					//					$valid_default_values = $this->getValidTypes();
					//					$field['default'] = $valid_default_values[$field['type']];
				}
			}
			$default = ' DEFAULT ' . ilMySQLQueryUtils::getInstance($this->ilDBInterface)->quote($field['default'], $field['type']);
		} elseif (empty($field['notnull'])) {//} && $field['notnull'] !== false) {
			$default = ' DEFAULT NULL';
		}

		// Not null
		$notnull = empty($field['notnull']) ? '' : ' NOT NULL';

		if ($field['notnull'] === false) {
			$notnull = " NULL";
		}

		// Collation
		$collation = empty($field['collation']) ? '' : ' COLLATE ' . $field['collation'];

		//var_dump($charset . $default . $notnull . $collation); // FSX
		return $charset . $default . $notnull . $collation;
	}


	/**
	 * @param $type
	 * @param array $field
	 * @return string
	 * @deprecated
	 * @TODO refactor all SQL to ilMySQLQueryUtils
	 */
	protected function getTypeDeclarationMySQL($type, array $field) {
		switch ($type) {
			case self::T_TEXT:
				$length = !empty($field['length']) ? $field['length'] : self::DEFAULT_TEXT_LENGTH;
				$fixed = !empty($field['fixed']) ? $field['fixed'] : false;

				return $fixed ? ($length ? 'CHAR(' . $length . ')' : 'CHAR(' . self::DEFAULT_TEXT_LENGTH . ')') : ($length ? 'VARCHAR(' . $length
				                                                                                                             . ')' : 'TEXT');
			case self::T_CLOB:
				return 'TEXT';
			case self::T_BLOB:
				return 'TEXT';
			case self::T_INTEGER:
				return 'INT';
			case 'boolean':
				return 'INT';
			case self::T_DATE:
				return 'CHAR (' . strlen('YYYY-MM-DD') . ')';
			case self::T_TIME:
				return 'CHAR (' . strlen('HH:MM:SS') . ')';
			case self::T_TIMESTAMP:
				return 'CHAR (' . strlen('YYYY-MM-DD HH:MM:SS') . ')';
			case self::T_FLOAT:
				return 'DOUBLE';
			case 'decimal':
				return 'TEXT';
		}

		return '';
	}


	/**
	 * @param $field
	 * @return array
	 * @throws \ilDatabaseException
	 */
	public function mapNativeDatatype($field) {
		$db_type = strtolower($field['type']);
		$db_type = strtok($db_type, '(), ');
		if ($db_type == 'national') {
			$db_type = strtok('(), ');
		}
		if (!empty($field['length'])) {
			$length = strtok($field['length'], ', ');
			$decimal = strtok(', ');
		} else {
			$length = strtok('(), ');
			$decimal = strtok('(), ');
		}
		$type = array();
		$unsigned = $fixed = null;
		switch ($db_type) {
			case 'tinyint':
				$type[] = 'integer';
				$type[] = 'boolean';
				if (preg_match('/^(is|has)/', $field['name'])) {
					$type = array_reverse($type);
				}
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				$length = 1;
				break;
			case 'smallint':
				$type[] = 'integer';
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				$length = 2;
				break;
			case 'mediumint':
				$type[] = 'integer';
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				$length = 3;
				break;
			case 'int':
			case 'integer':
				$type[] = 'integer';
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				$length = 4;
				break;
			case 'bigint':
				$type[] = 'integer';
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				$length = 8;
				break;
			case 'tinytext':
			case 'mediumtext':
			case 'longtext':
			case 'text':
			case 'text':
			case 'varchar':
				$fixed = false;
			case 'string':
			case 'char':
				$type[] = 'text';
				if ($length == '1') {
					$type[] = 'boolean';
					if (preg_match('/^(is|has)/', $field['name'])) {
						$type = array_reverse($type);
					}
				} elseif (strstr($db_type, 'text')) {
					$type[] = 'clob';
					if ($decimal == 'binary') {
						$type[] = 'blob';
					}
				}
				if ($fixed !== false) {
					$fixed = true;
				}
				break;
			case 'enum':
				$type[] = 'text';
				preg_match_all('/\'.+\'/U', $field['type'], $matches);
				$length = 0;
				$fixed = false;
				if (is_array($matches)) {
					foreach ($matches[0] as $value) {
						$length = max($length, strlen($value) - 2);
					}
					if ($length == '1' && count($matches[0]) == 2) {
						$type[] = 'boolean';
						if (preg_match('/^(is|has)/', $field['name'])) {
							$type = array_reverse($type);
						}
					}
				}
				$type[] = 'integer';
			case 'set':
				$fixed = false;
				$type[] = 'text';
				$type[] = 'integer';
				break;
			case 'date':
				$type[] = 'date';
				$length = null;
				break;
			case 'datetime':
			case 'timestamp':
				$type[] = 'timestamp';
				$length = null;
				break;
			case 'time':
				$type[] = 'time';
				$length = null;
				break;
			case 'float':
			case 'double':
			case 'real':
				$type[] = 'float';
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				break;
			case 'unknown':
			case 'decimal':
			case 'numeric':
				$type[] = 'decimal';
				$unsigned = preg_match('/ unsigned/i', $field['type']);
				if ($decimal !== false) {
					$length = $length . ',' . $decimal;
				}
				break;
			case 'tinyblob':
			case 'mediumblob':
			case 'longblob':
			case 'blob':
				$type[] = 'blob';
				$length = null;
				break;
			case 'binary':
			case 'varbinary':
				$type[] = 'blob';
				break;
			case 'year':
				$type[] = 'integer';
				$type[] = 'date';
				$length = null;
				break;
			default:
				throw new ilDatabaseException('unknown database attribute type: ' . $db_type);
		}

		if ((int)$length <= 0) {
			$length = null;
		}

		return array( $type, $length, $unsigned, $fixed );
	}
}
