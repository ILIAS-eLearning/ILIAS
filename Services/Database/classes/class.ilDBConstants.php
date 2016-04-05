<?php

/**
 * Class ilDBConstants
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBConstants {

	const FETCHMODE_ASSOC = 2;
	const FETCHMODE_OBJECT = 3;
	const MODULE_MANAGER = 'Manager';
	const MODULE_REVERSE = 'Reverse';
	const TYPE_INNODB = 'innodb';
	const TYPE_MYSQL = 'mysql';
	const TYPE_ORACLE = 'oracle';
	const TYPE_PDO_MYSQL_INNODB = 'pdo-mysql-innodb';
	const TYPE_PDO_MYSQL_MYISAM = 'pdo-mysql-myisam';
	const TYPE_PDO_POSTGRE = 'pdo-postgre';
	const TYPE_POSTGRES = 'postgres';
	const TYPE_MYSQLI = 'mysqli';
	const LOCK_WRITE = 1;
	const LOCK_READ = 2;
	const T_CLOB = 'clob';
	const T_DATE = 'date';
	const T_DATETIME = 'datetime';
	const T_FLOAT = 'float';
	const T_INTEGER = 'integer';
	const T_TEXT = 'text';
	const T_TIME = 'time';
	const T_TIMESTAMP = 'timestamp';
	const T_BLOB = 'blob';
	const INDEX_FORMAT = '%s_idx';
	const SEQUENCE_FORMAT = '%s_seq';
	const SEQUENCE_COLUMNS_NAME = 'sequence';
	/**
	 * @var array
	 */
	public static $allowed_attributes = array(
		self::T_TEXT      => array( 'length', 'notnull', 'default', 'fixed' ),
		self::T_INTEGER   => array( 'length', 'notnull', 'default', 'unsigned' ),
		self::T_FLOAT     => array( 'notnull', 'default' ),
		self::T_DATE      => array( 'notnull', 'default' ),
		self::T_TIME      => array( 'notnull', 'default' ),
		self::T_TIMESTAMP => array( 'notnull', 'default' ),
		self::T_CLOB      => array( 'notnull', 'default' ),
		self::T_BLOB      => array( 'notnull', 'default' ),
	);
}
