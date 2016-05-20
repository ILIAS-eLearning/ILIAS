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
	/**
	 * @var array
	 */
	protected static $descriptions = array(
		ilDBConstants::TYPE_PDO_MYSQL_MYISAM => "MySQL 5.5.x or higher (MyISAM engine)",
		ilDBConstants::TYPE_PDO_MYSQL_INNODB => "MySQL 5.5.x or higher (InnoDB engine)",
		ilDBConstants::TYPE_PDO_POSTGRE      => "Postgres (experimental) (InnoDB engine)",
		ilDBConstants::TYPE_ORACLE           => "Oracle 10g or higher [legacy]",
		ilDBConstants::TYPE_POSTGRES         => "Postgres (experimental) [legacy]",
		ilDBConstants::TYPE_MYSQL            => "MySQL 5.0.x or higher (MyISAM engine) [legacy]",
		ilDBConstants::TYPE_INNODB           => "MySQL 5.0.x or higher (InnoDB engine) [legacy]",
	);


	/**
	 * @return array
	 */
	public static function getInstallableTypes() {
		return array(
			ilDBConstants::TYPE_PDO_MYSQL_MYISAM,
			ilDBConstants::TYPE_PDO_MYSQL_INNODB,
			ilDBConstants::TYPE_MYSQL,
			ilDBConstants::TYPE_INNODB,
		);
	}


	/**
	 * @param bool $with_descriptions
	 * @return array
	 */
	public static function getAvailableTypes($with_descriptions = true) {
		$types = array_merge(self::getSupportedTypes(), self::getLegacyTypes());
		if ($with_descriptions) {
			$return = array();
			foreach ($types as $type) {
				$return [$type] = self::$descriptions[$type];
			}
			$types = $return;
		}

		return $types;
	}


	/**
	 * @return array
	 */
	public static function getSupportedTypes() {
		return array(
			ilDBConstants::TYPE_PDO_MYSQL_MYISAM,
			ilDBConstants::TYPE_PDO_MYSQL_INNODB,
			ilDBConstants::TYPE_PDO_POSTGRE,
		);
	}


	/**
	 * @return array
	 */
	public static function getLegacyTypes() {
		return array(
			ilDBConstants::TYPE_ORACLE,
			ilDBConstants::TYPE_POSTGRES,
			ilDBConstants::TYPE_MYSQL,
			ilDBConstants::TYPE_INNODB,
		);
	}
}
