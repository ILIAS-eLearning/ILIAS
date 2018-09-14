<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBWrapperFactory
 *
 * DB Wrapper Factory. Delivers a DB wrapper object depending on given
 * DB type and DSN.
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 *
 * @ingroup ServicesDatabase
 */
class ilDBWrapperFactory {

	/**
	 * @param $a_type
	 * @return ilDBInterface
	 * @throws ilDatabaseException
	 */
	static public function getWrapper($a_type) {
		global $DIC;
		$ilClientIniFile = null;
		if($DIC->offsetExists('ilClientIniFile')) {
			/**
			 * @var $ilClientIniFile ilIniFile
			 */
			$ilClientIniFile = $DIC['ilClientIniFile'];
		}

		if ($a_type == "" && $ilClientIniFile instanceof ilIniFile) {
			$a_type = $ilClientIniFile->readVariable("db", "type");
		}
		if ($a_type == "") {
			$a_type = ilDBConstants::TYPE_INNODB;
		}

		// For legacy code
//		if (!defined('DB_FETCHMODE_ASSOC')) {
//			define("DB_FETCHMODE_ASSOC", ilDBConstants::FETCHMODE_ASSOC);
//		}
//		if (!defined('DB_FETCHMODE_OBJECT')) {
//			define("DB_FETCHMODE_OBJECT", ilDBConstants::FETCHMODE_OBJECT);
//		}

		switch ($a_type) {
			case ilDBConstants::TYPE_POSTGRES:
			case ilDBConstants::TYPE_PDO_POSTGRE:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoPostgreSQL.php');
				$ilDB = new ilDBPdoPostgreSQL();
				break;
			case ilDBConstants::TYPE_ORACLE:
				include_once("./Services/Database/classes/MDB2/class.ilDBOracle.php");
				$ilDB = new ilDBOracle();
				break;
			case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
			case ilDBConstants::TYPE_INNODB:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLInnoDB.php');
				$ilDB = new ilDBPdoMySQLInnoDB();
				break;
			case ilDBConstants::TYPE_PDO_MYSQL_MYISAM:
			case ilDBConstants::TYPE_MYSQL:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLMyISAM.php');
				$ilDB = new ilDBPdoMySQLMyISAM();
				break;
			case ilDBConstants::TYPE_GALERA:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLGalera.php');
				$ilDB = new ilDBPdoMySQLGalera();
				break;
//			case 'postgres-legacy':
//				require_once('./Services/Database/classes/MDB2/class.ilDBPostgreSQL.php');
//				$ilDB = new ilDBPostgreSQL();
//				break;
//			case 'mysql-legacy':
//				require_once('./Services/Database/classes/MDB2/class.ilDBMySQL.php');
//				$ilDB = new ilDBMySQL();
//				break;
//			case 'innodb-legacy':
//				require_once('./Services/Database/classes/MDB2/class.ilDBInnoDB.php');
//				$ilDB = new ilDBInnoDB();
//				break;
			default:
				throw new ilDatabaseException("No viable database-type given: " . var_export($a_type, true));
		}

		return $ilDB;
	}
}
