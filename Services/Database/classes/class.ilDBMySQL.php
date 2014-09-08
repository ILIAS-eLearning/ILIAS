<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	protected $slave_active = false;
	protected $use_slave = false;

	/**
	 * Supports slave
	 *
	 * @param
	 * @return
	 */
	function supportsSlave()
	{
		return true;
	}

	/**
	 * Set slave active
	 *
	 * @param bool $a_val slave active	
	 */
	function setDBSlaveActive($a_val)
	{
		$this->slave_active = $a_val;
	}
	
	/**
	 * Get slave active
	 *
	 * @return bool slave active
	 */
	function getDBSlaveActive()
	{
		return $this->slave_active;
	}
	
	/**
	 * Set slave database user
	 *
	 * @param	string		slave database user
	 */
	function setDBSlaveUser($a_user)
	{
		$this->slave_user = $a_user;
	}
	
	/**
	 * Get slave database user
	 *
	 * @param	string		slave database user
	 */
	function getDBSlaveUser()
	{
		return $this->slave_user;
	}

	/**
	 * Set slave database port
	 *
	 * @param	string		slave database port
	 */
	function setDBSlavePort($a_port)
	{
		$this->slave_port = $a_port;
	}
	
	/**
	 * Get slave database port
	 *
	 * @param	string		slave database port
	 */
	function getDBSlavePort()
	{
		return $this->slave_port;
	}

	/**
	 * Set slave database host
	 *
	 * @param	string		slave database host
	 */
	function setDBSlaveHost($a_host)
	{
		$this->slave_host = $a_host;
	}
	
	/**
	 * Get slave database host
	 *
	 * @param	string		slave database host
	 */
	function getDBSlaveHost()
	{
		return $this->slave_host;
	}

	/**
	 * Set slave database password
	 *
	 * @param	string		slave database password
	 */
	function setDBSlavePassword($a_password)
	{
		$this->slave_password = $a_password;
	}
	
	/**
	 * Get slave database password
	 *
	 * @param	string		slave database password
	 */
	function getDBSlavePassword()
	{
		return $this->slave_password;
	}

	/**
	 * Set slave database name
	 *
	 * @param	string		slave database name
	 */
	function setDBSlaveName($a_name)
	{
		$this->slave_name = $a_name;
	}
	
	/**
	 * Get slave database name
	 *
	 * @param	string		slave database name
	 */
	function getDBSlaveName()
	{
		return $this->slave_name;
	}

	/**
	 * Get DSN.
	 */
	function getDSN()
	{
		return $this->__buildDSN($this->getDBHost(), $this->getDBName(),
			$this->getDBUser(), $this->getDBPassword(), $this->getDBPort());
	}

	/**
	 * Get slave DSN.
	 */
	function getSlaveDSN()
	{
		return $this->__buildDSN($this->getDBSlaveHost(), $this->getDBSlaveName(),
			$this->getDBSlaveUser(), $this->getDBSlavePassword(), $this->getDBSlavePort());
	}

	/**
	 * Build DSN string
	 *
	 * @param
	 * @return
	 */
	protected function __buildDSN($a_host, $a_name, $a_user, $a_pass, $a_port = "")
	{
		$db_port_str = "";
		if (trim($a_port) != "")
		{
			$db_port_str = ":".$a_port;
		}
		
		$driver = $this->isMySQLi() ? "mysqli" : "mysql";      
		
		return $driver."://".$a_user.":".$a_pass.
			"@".$a_host.$db_port_str."/".$a_name;
	}
	
	protected function isMySQLi()
	{
		return ($this->getSubType() == "mysqli");
	}

	/**
	* Get Host DSN.
	*/
	function getHostDSN()
	{		
		$driver = $this->isMySQLi() ? "mysqli" : "mysql"; 
		
		return $driver."://".$this->getDBUser().":".$this->getDBPassword().
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
	 * Init db parameters from ini file
	 * @param $tmpClientIniFile	overwrite global client ini file if is set to an object 
	 */
	function initFromIniFile($tmpClientIniFile = null)
	{
		global $ilClientIniFile;
		
		parent::initFromIniFile($tmpClientIniFile);
		
		//overwrite global client ini file if local parameter is set 
		if (is_object($tmpClientIniFile))
			$clientIniFile = $tmpClientIniFile;
		else 
			$clientIniFile = $ilClientIniFile;	
			
		if (is_object($clientIniFile ))
		{
			if ($clientIniFile->readVariable("db", "slave_active") == 1)
			{
				$this->setDBSlaveActive(true);
				$this->setDBSlaveUser($clientIniFile->readVariable("db", "slave_user"));
				$this->setDBSlaveHost($clientIniFile->readVariable("db", "slave_host"));
				$this->setDBSlavePort($clientIniFile->readVariable("db", "slave_port"));
				$this->setDBSlavePassword($clientIniFile->readVariable("db", "slave_pass"));
				$this->setDBSlaveName($clientIniFile->readVariable("db", "slave_name"));
			}
		}
	}

	/**
	 * Standard way to connect to db
	 */
	function doConnect()
	{
		parent::doConnect();
		if ($this->getDBSlaveActive())
		{
			$this->slave = MDB2::connect($this->getSlaveDSN(),
				array("use_transactions" => false));
		}
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
		if (DEVMODE == 1)
		{
			$this->query("SET SESSION SQL_MODE = 'ONLY_FULL_GROUP_BY'");
		}

		$this->query("SET SESSION STORAGE_ENGINE = 'MYISAM'");
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
		if(!$this->isMySQLi())
		{
			$vers = @mysql_get_server_info();
		}
		else
		{
		    $vers = @mysqli_get_server_info($this->db->connection);
		}		
		if (trim($vers) == "")
		{
			$vers = "Unknown";
		}
		return $vers;
	}
	

	/**
	* check wether current MySQL server is version 4.0.x or higher
	*/
	function isMysql4_0OrHigher()
	{
		$version = explode(".", $this->getDBVersion());
		if((int) $version[0] < 4)
		{
			return false;
		}
		return true;
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
	protected function setMaxAllowedPacket()
	{
		$version = $this->getDBVersion();

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
		$res = $this->query($query);

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
	
	/**
	 * Lock table
	 * 
	 * E.g $ilDB->lockTable('tree',ilDB::LOCK_WRITE,'t1')
	 * @param array $a_tables
	 * @param int $a_mode
	 * @param string $a_alias
	 * @return 
	 */
	public function lockTables($a_tables)
	{
		global $ilLog;
		
		$lock = 'LOCK TABLES ';

		$counter = 0;
		foreach($a_tables as $table)
		{
			if($counter++)
			{
				$lock .= ', ';
			}
			
			if( isset($table['sequence']) && $table['sequence'] )
			{
				$tableName = $this->db->getSequenceName($table['name']);
			}
			else
			{
				$tableName = $table['name'];
			}
			
			$lock .= ($tableName.' ');
			
			if($table['alias'])
			{
				$lock .= ($table['alias'].' ');
			}

			switch($table['type'])
			{
				case ilDB::LOCK_READ:
					$lock .= ' READ ';
					break;
				
				case ilDB::LOCK_WRITE:
					$lock .= ' WRITE ';
					break;
			}
		}
		$ilLog->write(__METHOD__.': '.$lock);
		$this->query($lock);
	}
	
	/**
	 * Unlock tables
	 * @return 
	 */
	public function unlockTables()
	{
		$this->query('UNLOCK TABLES');
	}
	
	protected function getCreateTableOptions()
	{
		// InnoDB is default engine for MySQL >= 5.5
		return array('type' => 'MyISAM');
	}
	
	public function getErrorNo()
	{
		if(!$this->isMySQLi())
		{
			return mysql_errno();
		}
		else
		{
			return mysqli_errno($this->db->connection);
		}
	}
	
	public function getLastError()
	{
		if(!$this->isMySQLi())
		{
			return mysql_error();
		}
		else
		{
			return mysqli_error($this->db->connection);
		}
	}
	
	/**
	 * Query
	 *
	 * @param
	 * @return
	 */
	function query($sql, $a_handle_error = true)
	{
		if (!$this->use_slave || !$this->getDBSlaveActive())
		{
			return parent::query($sql, $a_handle_error);
		}
		
		$r = $this->slave->query($sql);
		
		if ($a_handle_error)
		{
			return $this->handleError($r, "query(".$sql.")");
		}

		return $r;

	}
	
}
?>
