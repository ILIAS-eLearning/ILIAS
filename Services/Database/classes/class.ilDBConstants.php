<?php
require_once('./Services/Database/classes/PDO/class.ilDBPdoFieldDefinition.php');

/**
 * Class ilDBConstants
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBConstants {

	const FETCHMODE_ASSOC = 2;
	const FETCHMODE_OBJECT = 3;
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
	const MODULE_MANAGER = 'Manager';
	const MODULE_REVERSE = 'Reverse';
	const INDEX_FORMAT = ilDBPdoFieldDefinition::INDEX_FORMAT;
	const SEQUENCE_FORMAT = ilDBPdoFieldDefinition::SEQUENCE_FORMAT;
	const SEQUENCE_COLUMNS_NAME = ilDBPdoFieldDefinition::SEQUENCE_COLUMNS_NAME;
	const T_CLOB = ilDBPdoFieldDefinition::T_CLOB;
	const T_DATE = ilDBPdoFieldDefinition::T_DATE;
	const T_DATETIME = ilDBPdoFieldDefinition::T_DATETIME;
	const T_FLOAT = ilDBPdoFieldDefinition::T_FLOAT;
	const T_INTEGER = ilDBPdoFieldDefinition::T_INTEGER;
	const T_TEXT = ilDBPdoFieldDefinition::T_TEXT;
	const T_TIME = ilDBPdoFieldDefinition::T_TIME;
	const T_TIMESTAMP = ilDBPdoFieldDefinition::T_TIMESTAMP;
	const T_BLOB = ilDBPdoFieldDefinition::T_BLOB;
}
