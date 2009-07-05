<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once ("./Services/Database/classes/class.ilDB.php");

/**
* MySQL Database Wrapper
*
* This class extends the main ILIAS database wrapper ilDB. Only a few
* methods should be overwritten, that contain MySQL specific statements
* and methods.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilDB.php 18989 2009-02-15 12:57:19Z akill $
* @ingroup ServicesDatabase
*/
class ilDBMySQL extends ilDB
{
	/**
	* myqsl max_allowed_packet size
	* @var int
	*/
	var $max_allowed_packet_size;

	/**
	* Get DSN.
	*/
	function getDSN()
	{
		return "mysql://".$this->getDBUser().":".$this->getDBPassword().
			"@".$this->getdbHost()."/".$this->getDBName();
	}

	/**
	* Get Host DSN.
	*/
	function getHostDSN()
	{
		return "mysql://".$this->getDBUser().":".$this->getDBPassword().
			"@".$this->getdbHost();
	}

	/**
	* Get DB Type
	*/
	function getDBType()
	{
		return "mysql";
	}
	
	/**
	* Get reserved words
	*/
	static function getReservedWords()
	{
		// version: 5.1
		// url: http://dev.mysql.com/doc/refman/5.1/en/reserved-words.html
		return array(
		"ACCESSIBLE", "ADD", "ALL", "ALTER", "ANALYZE", "AND",
		"AS", "ASC", "ASENSITIVE", "BEFORE", "BETWEEN", "BIGINT",
		"BINARY", "BLOB", "BOTH", "BY", "CALL", "CASCADE",
		"CASE", "CHANGE", "CHAR", "CHARACTER", "CHECK", "COLLATE",
		"COLUMN", "CONDITION", "CONSTRAINT", "CONTINUE", "CONVERT", "CREATE",
		"CROSS", "CURRENT_DATE", "CURRENT_TIME", "CURRENT_TIMESTAMP", "CURRENT_USER", "CURSOR",
		"DATABASE", "DATABASES", "DAY_HOUR", "DAY_MICROSECOND", "DAY_MINUTE", "DAY_SECOND",
		"DEC", "DECIMAL", "DECLARE", "DEFAULT", "DELAYED", "DELETE",
		"DESC", "DESCRIBE", "DETERMINISTIC", "DISTINCT", "DISTINCTROW", "DIV",
		"DOUBLE", "DROP", "DUAL", "EACH", "ELSE", "ELSEIF",
		"ENCLOSED", "ESCAPED", "EXISTS", "EXIT", "EXPLAIN", "FALSE",
		"FETCH", "FLOAT", "FLOAT4", "FLOAT8", "FOR", "FORCE",
		"FOREIGN", "FROM", "FULLTEXT", "GRANT", "GROUP", "HAVING",
		"HIGH_PRIORITY", "HOUR_MICROSECOND", "HOUR_MINUTE", "HOUR_SECOND", "IF", "IGNORE",
		"IN", "INDEX", "INFILE", "INNER", "INOUT", "INSENSITIVE",
		"INSERT", "INT", "INT1", "INT2", "INT3", "INT4",
		"INT8", "INTEGER", "INTERVAL", "INTO", "IS", "ITERATE",
		"JOIN", "KEY", "KEYS", "KILL", "LEADING", "LEAVE",
		"LEFT", "LIKE", "LIMIT", "LINEAR", "LINES", "LOAD",
		"LOCALTIME", "LOCALTIMESTAMP", "LOCK", "LONG", "LONGBLOB", "LONGTEXT",
		"LOOP", "LOW_PRIORITY", "MASTER_SSL_VERIFY_SERVER_CERT", "MATCH", "MEDIUMBLOB", "MEDIUMINT",
		"MEDIUMTEXT", "MIDDLEINT", "MINUTE_MICROSECOND", "MINUTE_SECOND", "MOD", "MODIFIES",
		"NATURAL", "NOT", "NO_WRITE_TO_BINLOG", "NULL", "NUMERIC", "ON",
		"OPTIMIZE", "OPTION", "OPTIONALLY", "OR", "ORDER", "OUT",
		"OUTER", "OUTFILE", "PRECISION", "PRIMARY", "PROCEDURE", "PURGE",
		"RANGE", "READ", "READS", "READ_WRITE", "REAL", "REFERENCES",
		"REGEXP", "RELEASE", "RENAME", "REPEAT", "REPLACE", "REQUIRE",
		"RESTRICT", "RETURN", "REVOKE", "RIGHT", "RLIKE", "SCHEMA",
		"SCHEMAS", "SECOND_MICROSECOND", "SELECT", "SENSITIVE", "SEPARATOR", "SET",
		"SHOW", "SMALLINT", "SPATIAL", "SPECIFIC", "SQL", "SQLEXCEPTION",
		"SQLSTATE", "SQLWARNING", "SQL_BIG_RESULT", "SQL_CALC_FOUND_ROWS", "SQL_SMALL_RESULT", "SSL",
		"STARTING", "STRAIGHT_JOIN", "TABLE", "TERMINATED", "THEN", "TINYBLOB",
		"TINYINT", "TINYTEXT", "TO", "TRAILING", "TRIGGER", "TRUE",
		"UNDO", "UNION", "UNIQUE", "UNLOCK", "UNSIGNED", "UPDATE",
		"USAGE", "USE", "USING", "UTC_DATE", "UTC_TIME", "UTC_TIMESTAMP",
		"VALUES", "VARBINARY", "VARCHAR", "VARCHARACTER", "VARYING", "WHEN",
		"WHERE", "WHILE", "WITH", "WRITE", "XOR", "YEAR_MONTH",
		"ZEROFILL"
		);
	}
	
	/**
	* Initialize the database connection
	*/
	function initConnection()
	{
		// SET 'max_allowed_packet' (only possible for mysql version 4)
		$this->setMaxAllowedPacket();
		
		// NOTE: Two sourcecodes use this or a similar handling:
		// - classes/class.ilDB.php
		// - setup/classes/class.ilClient.php

		$this->query("SET NAMES utf8");
		$this->query("SET SESSION SQL_MODE = 'ONLY_FULL_GROUP_BY'");

	}

	/**
	* now()
	*
	*/
	function now()
	{
		return "now()";
	}

	/**
	* Optimize Table
	*/
	function optimizeTable($a_table)
	{
		$this->query("OPTIMIZE TABLE ".$a_table);
	}

	/**
	* get mysql version
	*/
	function getDBVersion()
	{
		return mysql_get_server_info();
	}
	

	/**
	* check wether current MySQL server is version 4.0.x or higher
	*/
	function isMysql4_0OrHigher()
	{
		$version = explode(".", $this->getDBVersion());
		if((int) $version[0] >= 4)
		{
			return true;
		}
		return false;
	}

	/**
	* check wether current MySQL server is version 4.1.x
	*/
	function isMysql4_1()
	{
		$version = explode(".", $this->getDBVersion());
		if ($version[0] == "4" && $version[1] == "1")
		{
			return true;
		}
		
		return false;
	}

	/**
	* check wether current MySQL server is version 4.1.x or higher
	*
	* NOTE: Two sourcecodes use this or a similar handling:
	* - classes/class.ilDB.php
	* - setup/classes/class.ilClient.php
	*/
	function isMysql4_1OrHigher()
	{
		$version = explode(".", $this->getDBVersion());
		if ((int)$version[0] >= 5 ||
			((int)$version[0] == 4 && (int)$version[1] >= 1))
		{
			return true;
		}
		
		return false;
	}

	/**
	* Check query size
	*/
	function checkQuerySize($a_query)
	{
		global $lang;

		if(strlen($a_query) >= $this->max_allowed_packet_size)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Set maximum allowed packet size
	*
	* todo@: This is MySQL specific and should go to a MySQL specific class.
	*/
	private function setMaxAllowedPacket()
	{
		// GET MYSQL VERSION
		$query = "SHOW VARIABLES LIKE 'version'";
		$res = $this->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$version = $row->Value;
		}

		// CHANG VALUE IF MYSQL VERSION > 4.0
		// Switched back to "SET GLOBAL ..."
		// @see http://bugs.mysql.com/bug.php?id=22891
		// smeyer 2009 07 30
		if (substr($version,0,1) == "4")
		{
			ini_get("post_max_size");
			$query = "SET GLOBAL max_allowed_packet = ".(int) ini_get("post_max_size") * 1024 * 1024;
//echo "-".$query."-";
			$this->query($query);
		}
		// STORE NEW max_size in member variable
		$query = "SHOW VARIABLES LIKE 'max_allowed_packet'";
		$res = $this->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->max_allowed_packet_size = $row->value;
		}
//echo "-".$this->max_allowed_packet_size."-";
		return true;
	}

	/**
	* Is fulltext index supported?
	*/
	function supportsFulltext()
	{
		return true;
	}

	/**
	* Add fulltext index
	*/
	function addFulltextIndex($a_table, $a_fields, $a_name = "in")
	{
		$i_name = $this->constraintName($a_table, $a_name)."_idx";
		$f_str = implode($a_fields, ",");
		$q = "ALTER TABLE $a_table ADD FULLTEXT $i_name ($f_str)";
		$this->query($q);
	}

	/**
	* Add fulltext index
	*/
	function dropFulltextIndex($a_table, $a_name)
	{
echo "h";
		$i_name = $this->constraintName($a_table, $a_name)."_idx";
		$this->query("ALTER TABLE $a_table DROP FULLTEXT $i_name");
	}

	/**
	* Is index a fulltext index?
	*/
	function isFulltextIndex($a_table, $a_name)
	{
		$set = $this->query("SHOW INDEX FROM ".$a_table);
		while ($rec = $this->fetchAssoc($set))
		{
			if ($rec["Key_name"] == $a_name && $rec["Index_type"] == "FULLTEXT")
			{
				return true;
			}
		}
	}

}
?>
