<?php

/**
 * Class ilDBConstants
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBConstants {

	const FETCHMODE_ASSOC = 2;
	const FETCHMODE_OBJECT = 3;
	const TYPE_INNODB = "innodb";
	const TYPE_MYSQL = "mysql";
	const TYPE_ORACLE = "oracle";
	const TYPE_PDO_MYSQL_INNODB = "pdo-mysql-innodb";
	const TYPE_PDO_MYSQL_MYISAM = "pdo-mysql-myisam";
	const TYPE_POSTGRES = "postgres";
	const TYPE_MYSQLI = "mysqli";
	const LOCK_WRITE = 1;
	const LOCK_READ = 2;
}
