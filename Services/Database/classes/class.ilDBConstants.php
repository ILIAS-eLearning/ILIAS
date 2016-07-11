<?php
require_once('./Services/Database/classes/PDO/FieldDefinition/class.ilDBPdoFieldDefinition.php');
require_once('./Services/Database/classes/Atom/class.ilAtomQueryBase.php');

/**
 * Class ilDBConstants
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBConstants {

	const FETCHMODE_ASSOC = 2;
	const FETCHMODE_OBJECT = 3;
	const FETCHMODE_DEFAULT = self::FETCHMODE_ASSOC;
	// Legacy
	const TYPE_INNODB_LEGACY = 'innodb';
	const TYPE_MYSQL_LEGACY = 'mysql';
	const TYPE_POSTGRES_LEGACY = 'postgres';
	const TYPE_MYSQLI_LEGACY = 'mysqli';
	// Oracle
	const TYPE_ORACLE = 'oracle';
	// PDO
	const TYPE_PDO_MYSQL_INNODB = 'pdo-mysql-innodb';
	const TYPE_PDO_MYSQL_GALERA = 'pdo-mysql-galera';
	const TYPE_PDO_MYSQL_MYISAM = 'pdo-mysql-myisam';
	const TYPE_PDO_POSTGRE = 'pdo-postgre';
	// Locks
	const LOCK_WRITE = ilAtomQuery::LOCK_WRITE;
	const LOCK_READ = ilAtomQuery::LOCK_READ;
	// Modules
	const MODULE_MANAGER = 'Manager';
	const MODULE_REVERSE = 'Reverse';
	// Formats
	const INDEX_FORMAT = ilDBPdoFieldDefinition::INDEX_FORMAT;
	const SEQUENCE_FORMAT = ilDBPdoFieldDefinition::SEQUENCE_FORMAT;
	const SEQUENCE_COLUMNS_NAME = ilDBPdoFieldDefinition::SEQUENCE_COLUMNS_NAME;
	// Types
	const T_CLOB = ilDBPdoFieldDefinition::T_CLOB;
	const T_DATE = ilDBPdoFieldDefinition::T_DATE;
	const T_DATETIME = ilDBPdoFieldDefinition::T_DATETIME;
	const T_FLOAT = ilDBPdoFieldDefinition::T_FLOAT;
	const T_INTEGER = ilDBPdoFieldDefinition::T_INTEGER;
	const T_TEXT = ilDBPdoFieldDefinition::T_TEXT;
	const T_TIME = ilDBPdoFieldDefinition::T_TIME;
	const T_TIMESTAMP = ilDBPdoFieldDefinition::T_TIMESTAMP;
	const T_BLOB = ilDBPdoFieldDefinition::T_BLOB;
	// Engines
	const ENGINE_INNODB = 'InnoDB';
	const ENGINE_MYISAM = 'MyISAM';
	/**
	 * @var array
	 */
	protected static $descriptions = array(
		ilDBConstants::TYPE_PDO_MYSQL_MYISAM => "MySQL 5.5.x or higher (MyISAM engine)",
		ilDBConstants::TYPE_PDO_MYSQL_INNODB => "MySQL 5.5.x or higher (InnoDB engine)",
		ilDBConstants::TYPE_PDO_MYSQL_GALERA => "Galera-Cluster (experimental)",
		ilDBConstants::TYPE_PDO_POSTGRE      => "Postgres (experimental)",
		ilDBConstants::TYPE_ORACLE           => "Oracle 10g or higher [legacy]",
		ilDBConstants::TYPE_POSTGRES_LEGACY  => "Postgres (experimental) [legacy]",
		ilDBConstants::TYPE_MYSQL_LEGACY     => "MySQL 5.0.x or higher (MyISAM engine) [legacy]",
		ilDBConstants::TYPE_INNODB_LEGACY    => "MySQL 5.0.x or higher (InnoDB engine) [legacy]",
	);


	/**
	 * @return array
	 */
	public static function getInstallableTypes() {
		return array(
			ilDBConstants::TYPE_PDO_MYSQL_MYISAM,
			ilDBConstants::TYPE_PDO_MYSQL_INNODB,
			ilDBConstants::TYPE_PDO_MYSQL_GALERA,
			ilDBConstants::TYPE_MYSQL_LEGACY,
			ilDBConstants::TYPE_INNODB_LEGACY,
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
			ilDBConstants::TYPE_PDO_MYSQL_GALERA,
		);
	}


	/**
	 * @return array
	 */
	public static function getLegacyTypes() {
		return array(
			ilDBConstants::TYPE_ORACLE,
			ilDBConstants::TYPE_POSTGRES_LEGACY,
			ilDBConstants::TYPE_MYSQL_LEGACY,
			ilDBConstants::TYPE_INNODB_LEGACY,
		);
	}
}
