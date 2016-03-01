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
			$a_type = "mysql";
		}

		switch ($a_type) {
			case "mysql":
				include_once("./Services/Database/classes/MDB2/class.ilDBMySQL.php");
				$ilDB = new ilDBMySQL();

				if ($a_inactive_mysqli === null
				    && is_object($ilClientIniFile)
				) {
					$a_inactive_mysqli = $ilClientIniFile->readVariable("db", "inactive_mysqli");
				}

				// default: use mysqli driver if not prevented by ini setting
				if (!(bool)$a_inactive_mysqli) {
					$ilDB->setSubType("mysqli");
				}

				break;

			case "innodb":
				include_once("./Services/Database/classes/MDB2/class.ilDBInnoDB.php");
				$ilDB = new ilDBInnoDB();

				if ($a_inactive_mysqli === null
				    && is_object($ilClientIniFile)
				) {
					$a_inactive_mysqli = $ilClientIniFile->readVariable("db", "inactive_mysqli");
				}

				// default: use mysqli driver if not prevented by ini setting
				if (!(bool)$a_inactive_mysqli) {
					$ilDB->setSubType("mysqli");
				}

				break;

			case "postgres":
				include_once("./Services/Database/classes/MDB2/class.ilDBPostgreSQL.php");
				$ilDB = new ilDBPostgreSQL();
				break;

			case "oracle":
				include_once("./Services/Database/classes/MDB2/class.ilDBOracle.php");
				$ilDB = new ilDBOracle();
				break;
			case "pdo-mysql-innodb":
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLInnoDB.php');
				$ilDB = new ilDBPdoMySQLInnoDB();
				break;
			case "pdo-mysql-myisam":
				require_once('./Services/Database/classes/PDO/class.ilDBPdoMySQLMyISAM.php');
				$ilDB = new ilDBPdoMySQLMyISAM();
				break;
			default:
				throw new ilDatabaseException("No viable database-type given: " . var_export($a_type, true));
		}

		return $ilDB;
	}
}
