<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Database/exceptions/exception.ilDatabaseException.php");
require_once('./Services/Database/classes/class.ilDBConstants.php');
require_once("./Services/Database/interfaces/interface.ilDBInterface.php");
require_once('./Services/Database/interfaces/interface.ilDBManager.php');
require_once('./Services/Database/interfaces/interface.ilDBReverse.php');

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
	 * @param null $a_inactive_mysqli
	 * @return ilDBInterface
	 * @throws ilDatabaseException
	 */
	static public function getWrapper($a_type, $a_inactive_mysqli = null) {
		global $ilClientIniFile;
		/**
		 * @var $ilClientIniFile ilIniFile
		 */

		if ($a_type == "" && is_object($ilClientIniFile)) {
			$a_type = $ilClientIniFile->readVariable("db", "type");
		}
		if ($a_type == "") {
			$a_type = ilDBConstants::TYPE_PDO_MYSQL_MYISAM;
		}

		switch ($a_type) {
			case ilDBConstants::TYPE_MYSQL_LEGACY:
				include_once("./Services/Database/classes/MDB2/class.ilDBMySQL.php");
				$ilDB = new ilDBMySQL();

				if ($a_inactive_mysqli === null
				    && is_object($ilClientIniFile)
				) {
					$a_inactive_mysqli = $ilClientIniFile->readVariable("db", "inactive_mysqli");
				}

				// default: use mysqli driver if not prevented by ini setting
				if (!(bool)$a_inactive_mysqli) {
					$ilDB->setSubType(ilDBConstants::TYPE_MYSQLI_LEGACY);
				}

				break;

			case ilDBConstants::TYPE_INNODB_LEGACY:
				require_once("./Services/Database/classes/MDB2/class.ilDBInnoDB.php");
				$ilDB = new ilDBInnoDB();

				if ($a_inactive_mysqli === null
				    && is_object($ilClientIniFile)
				) {
					$a_inactive_mysqli = $ilClientIniFile->readVariable("db", "inactive_mysqli");
				}

				// default: use mysqli driver if not prevented by ini setting
				if (!(bool)$a_inactive_mysqli) {
					$ilDB->setSubType(ilDBConstants::TYPE_MYSQLI_LEGACY);
				}

				break;

			case ilDBConstants::TYPE_POSTGRES_LEGACY:
				include_once("./Services/Database/classes/MDB2/class.ilDBPostgreSQL.php");
				$ilDB = new ilDBPostgreSQL();
				break;
			case  ilDBConstants::TYPE_PDO_POSTGRE:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoPostgreSQL.php');
				$ilDB = new ilDBPdoPostgreSQL();
				break;
			case ilDBConstants::TYPE_ORACLE:
				include_once("./Services/Database/classes/MDB2/class.ilDBOracle.php");
				$ilDB = new ilDBOracle();
				break;
			case ilDBConstants::TYPE_PDO_MYSQL_INNODB:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLInnoDB.php');
				$ilDB = new ilDBPdoMySQLInnoDB();
				break;
			case ilDBConstants::TYPE_PDO_MYSQL_MYISAM:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLMyISAM.php');
				$ilDB = new ilDBPdoMySQLMyISAM();
				break;
			case ilDBConstants::TYPE_PDO_MYSQL_GALERA:
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLGalera.php');
				$ilDB = new ilDBPdoMySQLGalera();
				break;
			default:
				throw new ilDatabaseException("No viable database-type given: " . var_export($a_type, true));
		}

		return $ilDB;
	}
}
