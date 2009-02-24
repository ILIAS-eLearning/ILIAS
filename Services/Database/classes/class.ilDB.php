<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** @defgroup ServicesDatabase Services/Database
 */

//pear MDB2 abstraction layer
include_once ("MDB2.php");

define("DB_FETCHMODE_ASSOC", MDB2_FETCHMODE_ASSOC);
define("DB_FETCHMODE_OBJECT", MDB2_FETCHMODE_OBJECT);

//echo "-".DB_FETCHMODE_ASSOC."-";
//echo "+".DB_FETCHMODE_OBJECT."+";


/**
* Database Wrapper
*
* this class should extend PEAR::DB, add error Management
* in case of a db-error in any database query the ilDB-class raises an error
*
* @author Peter Gabriel <peter@gabriel-online.net>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesDatabase
*/
abstract class ilDB extends PEAR
{
	/**
	* error class
	* @var object error_class
	* @access private
	*/
	var $error_class;

	/**
	* database handle from pear database class.
	* @var string
	*/
	var $db;

	/**
	* database-result-object
	* @var string
	*/
	var $result;


	var $allowed_attributes = array(
			"text" => array("length", "notnull", "default", "fixed"),
			"integer" => array("length", "notnull", "default", "unsigned"),
			"float" => array("notnull", "default"),
			"date" => array("notnull", "default"),
			"time" => array("notnull", "default"),
			"timestamp" => array("notnull", "default"),
			"clob" => array("notnull", "default"),
			"blob" => array("notnull", "default")
		);

	/**
	* constructor
	*
	* set up database conncetion and the errorhandling
	*
	* @param string dsn database-connection-string for pear-db
	*/
	function ilDB()
	{
	}
	
	/**
	* Set database user
	*
	* @param	string		database user
	*/
	function setDBUser($a_user)
	{
		$this->db_user = $a_user;
	}
	
	/**
	* Get database user
	*
	* @param	string		database user
	*/
	function getDBUser()
	{
		return $this->db_user;
	}

	/**
	* Set database host
	*
	* @param	string		database host
	*/
	function setDBHost($a_host)
	{
		$this->db_host = $a_host;
	}
	
	/**
	* Get database host
	*
	* @param	string		database host
	*/
	function getDBHost()
	{
		return $this->db_host;
	}

	/**
	* Set database password
	*
	* @param	string		database password
	*/
	function setDBPassword($a_password)
	{
		$this->db_password = $a_password;
	}
	
	/**
	* Get database password
	*
	* @param	string		database password
	*/
	function getDBPassword()
	{
		return $this->db_password;
	}

	/**
	* Set database name
	*
	* @param	string		database name
	*/
	function setDBName($a_name)
	{
		$this->db_name = $a_name;
	}
	
	/**
	* Get database name
	*
	* @param	string		database name
	*/
	function getDBName()
	{
		return $this->db_name;
	}

	/**
	* Get DSN. This must be overwritten in DBMS specific class.
	*/
	abstract function getDSN();
	
	/**
	* Get DB version
	*/
	function getDBVersion()
	{
		return "Unknown";
	}

	/**
	* Get DSN. This must be overwritten in DBMS specific class.
	*/
	abstract function getDBType();

	/**
	* Get reserved words. This must be overwritten in DBMS specific class.
	* This is mainly used to check whether a new identifier can be problematic
	* because it is a reserved word. So createTable / alterTable usually check
	* these.
	*/
	abstract static function getReservedWords();

	/**
	* Init db parameters from ini file
	*/
	function initFromIniFile()
	{
		global $ilClientIniFile;
		
		if (is_object($ilClientIniFile))
		{
			$this->setDBUser($ilClientIniFile->readVariable("db", "user"));
			$this->setDBHost($ilClientIniFile->readVariable("db", "host"));
			$this->setDBPassword($ilClientIniFile->readVariable("db", "pass"));
			$this->setDBName($ilClientIniFile->readVariable("db", "name"));
		}
	}
	
	/**
	* Open the connection
	*/
	function connect($a_return_false_for_error = false)
	{
		//set up error handling
		$this->error_class = new ilErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK, array($this->error_class,'errorHandler'));
		
		//check dsn
		if ($this->getDSN() == "")
		{
			$this->raisePearError("No DSN given");
		}

		//connect to database
		$this->db = MDB2::connect($this->getDSN(),
			array("use_transactions" => true));
		if ($a_return_false_for_error && MDB2::isError($this->db))
		{
			return false;
		}
			
		$this->loadMDB2Extensions();
		
		// set empty value portability to PEAR::DB behaviour
		if (!$this->isDbError($this->db))
		{
if ($this->getDBType() == "mysql")
{
			$cur = ($this->db->getOption("portability") & MDB2_PORTABILITY_EMPTY_TO_NULL);
			$this->db->setOption("portability", $this->db->getOption("portability") - $cur);

			$cur = ($this->db->getOption("portability") & MDB2_PORTABILITY_FIX_CASE);
			$this->db->setOption("portability", $this->db->getOption("portability") - $cur);
}
		}

		//check error
		$this->handleError($this->db);
		
		// anything, that must be done to initialize the connection
		$this->initConnection();

		return true;
	}
	
	/**
	* Disconnect
	*/
	function disconnect()
	{
		$this->db->disconnect();
	}
	
	//
	// General and MDB2 related functions
	//

	/**
	* Initialize the database connection
	*/
	protected function initConnection()
	{
	}
	
	/**
	* Should return a valid value, if host connections are possible
	* (connectHost) to create a new database from scratch
	*
	* @return	string		host dsn (similar to dsn WITHOUT a specific database name)
	*/
	function getHostDSN()
	{
		return false;
	}

	/**
	* Sets up a host connection only (no specific database used). This is optional
	* during the setup procudure to create databases from scratch.
	*/
	function connectHost()
	{
		//set up error handling
		$this->error_class = new ilErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK, array($this->error_class,'errorHandler'));
		
		//check dsn
		if ($this->getHostDSN() == "")
		{
			$this->raisePearError("No Host DSN given");
		}

		//connect to database
		$this->db = MDB2::connect($this->getHostDSN(),
			array("use_transactions" => true));
		if ($a_return_false_for_error && MDB2::isError($this->db))
		{
			return false;
		}
			
		$this->loadMDB2Extensions();
		
		// set empty value portability to PEAR::DB behaviour
		if (!$this->isDbError($this->db))
		{
			$cur = ($this->db->getOption("portability") & MDB2_PORTABILITY_EMPTY_TO_NULL);
			$this->db->setOption("portability", $this->db->getOption("portability") - $cur);

			$cur = ($this->db->getOption("portability") & MDB2_PORTABILITY_FIX_CASE);
			$this->db->setOption("portability", $this->db->getOption("portability") - $cur);
		}

		//check error
		$this->handleError($this->db);
		
		// anything, that must be done to initialize the connection
		$this->initHostConnection();

		return true;
	}
	
	/**
	* Initialize the host connection (no specific database)
	*/
	protected function initHostConnection()
	{
	}

	function supportsFulltext()
	{
		return false;
	}

	/**
	* Handle MDB2 Errors
	*
	* @param	mixed 	result set or anything that is a MDB2::error if
	*					something went wrong
	*/
	function handleError($a_res, $a_info = "", $a_level = "")
	{
		if (MDB2::isError($a_res))
		{
			if ($a_level == "")
			{
				$a_level = $this->error_class->FATAL;
			}

			$this->raisePearError("ilDB Error: ".$a_info."<br />".
				$a_res->getMessage()."<br />".$a_res->getUserInfo(), $a_level);
		}
		
		return $a_res;
	}
	
	/**
	* Raise an error
	*/
	function raisePearError($a_message, $a_level = "")
	{
		if ($a_level == "")
		{
			$a_level = $this->error_class->FATAL;
		}
//echo "<br>-ilDB:raising-$a_message-$a_level-";
		$this->raiseError($a_message, $a_level);
	}
	
	/**
	 * load additional mdb2 extensions and set their constants 
	 *
	 * @access protected
	 */
	protected function loadMDB2Extensions()
	{
		if (!$this->isDbError($this->db))
		{
			$this->db->loadModule('Extended');
			define('DB_AUTOQUERY_SELECT',MDB2_AUTOQUERY_SELECT);
			define('DB_AUTOQUERY_INSERT',MDB2_AUTOQUERY_INSERT);
			define('DB_AUTOQUERY_UPDATE',MDB2_AUTOQUERY_UPDATE);
			define('DB_AUTOQUERY_DELETE',MDB2_AUTOQUERY_DELETE);
		}
	}

	/**
	* Check error
	*/
	static function isDbError($a_res)
	{
		return MDB2::isError($a_res);
	}

	//
	// Data Definition Methods
	//
	
	/**
	* Create a new table in the database
	*
	* @param	string		table name
	* @param	array		definition array: array("col1" => array("type" => "text", ...))
	* @param	boolean		drop table automatically, if it already exists
	*/
	function createTable($a_name, $a_definition_array, $a_drop_table = false)
	{
		// (removed options; should only be activated restricted, if necessary
		if ($a_options == "")
		{
			$a_options = array();
		}
		
		// check table name
		if (!$this->checkTableName($a_name))
		{
			$this->raisePearError("ilDB Error: createTable(".$a_name.")<br />".
				$this->error_str);
		}
		
		// check definition array
		if (!$this->checkTableColumns($a_definition_array))
		{
			$this->raisePearError("ilDB Error: createTable(".$a_name.")<br />".
				$this->error_str);
		}

		if ($a_drop_table)
		{
			$this->dropTable($a_name, false);
		}
		
		$manager = $this->db->loadModule('Manager');
		$r = $manager->createTable($a_name, $a_definition_array, $a_options);

		return $this->handleError($r, "createTable(".$a_name.")");
	}
	
	/**
	* Drop a table
	*
	* @param	string		table name
	* @param	boolean		raise an error, if table already exists
	*/
	function dropTable($a_name, $a_error_if_not_existing = true)
	{
		if (!$a_error_if_not_existing)
		{
			$tables = $this->listTables();
			if (!in_array($a_name, $tables))
			{
				return;
			}
		}
		
		$manager = $this->db->loadModule('Manager');
		$r = $manager->dropTable($a_name);

		return $this->handleError($r, "dropTable(".$a_name.")");
	}
	
	/**
	* Alter a table in the database
	*/
	function alterTable($a_name, $a_changes)
	{
		if ($a_options == "")
		{
			$a_options = array();
		}
		
		$manager = $this->db->loadModule('Manager');
		$r = $manager->alterTable($a_name, $a_changes, false);

		return $this->handleError($r, "alterTable(".$a_name.")");
	}

	/**
	* Add table column
	* Use this only on aleady "abstracted" tables.
	*
	* @param	string		table name
	* @param	string		column name
	* @param	array		attributes array("length" => 10, "default" => "t")
	*/
	function addTableColumn($a_table, $a_column, $a_attributes)
	{

		$manager = $this->db->loadModule('Manager');

		if (!$this->checkColumnName($a_column))
		{
			$this->raisePearError("ilDB Error: addTableColumn(".$a_table.", ".$a_column.")<br />".
				$this->error_str);
		}
		if (!$this->checkColumnDefinition($a_attributes))
		{
			$this->raisePearError("ilDB Error: addTableColumn(".$a_table.", ".$a_column.")<br />".
				$this->error_str);
		}
		
		$changes = array(
			"add" => array(
				$a_column => $a_attributes
				)
			);

		$r = $manager->alterTable($a_table, $changes, false);

		return $this->handleError($r, "addTableColumn(".$a_table.", ".$a_column.")");
	}

	/**
	* Drop table column
	* Use this only on aleady "abstracted" tables.
	*
	* @param	string		table name
	* @param	string		column name
	*/
	function dropTableColumn($a_table, $a_column)
	{

		$manager = $this->db->loadModule('Manager');

		$changes = array(
			"remove" => array(
				$a_column => array()
				)
			);

		$r = $manager->alterTable($a_table, $changes, false);

		return $this->handleError($r, "dropTableColumn(".$a_table.", ".$a_column.")");
	}
	
	/**
	* Modify a table column
	* Use this only on aleady "abstracted" tables.
	*
	* @param	string		table name
	* @param	string		column name
	* @param	array		attributes to be changed, e.g. array("length" => 10, "default" => "t")
	*/
	function modifyTableColumn($a_table, $a_column, $a_attributes)
	{
		$manager = $this->db->loadModule('Manager');
		$reverse = $this->db->loadModule('Reverse');
		$def = $reverse->getTableFieldDefinition($a_table, $a_column);
		
		$this->handleError($def, "modifyTableColumn(".$a_table.")");

		if (is_file("./Services/Database/classes/class.ilDBAnalyzer.php"))
		{
			include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		}
		else
		{
			include_once("../Services/Database/classes/class.ilDBAnalyzer.php");
		}
		$analyzer = new ilDBAnalyzer();
		$best_alt = $analyzer->getBestDefinitionAlternative($def);
		$def = $def[$best_alt];
		unset($def["nativetype"]);
		unset($def["mdb2type"]);

		// check attributes
		$type = ($a_attributes["type"] != "")
			? $a_attributes["type"]
			: $def["type"];
		foreach ($def as $k => $v)
		{
			if ($k != "type" && !in_array($k, $this->allowed_attributes[$type]))
			{
				unset($def[$k]);
			}
		}
		$check_array = $def;
		foreach ($a_attributes as $k => $v)
		{
			$check_array[$k] = $v;
		}
		if (!$this->checkColumnDefinition($check_array, true))
		{
			$this->raisePearError("ilDB Error: modifyTableColumn(".$a_table.", ".$a_column.")<br />".
				$this->error_str);
		}

		foreach ($a_attributes as $a => $v)
		{
			$def[$a] = $v;
		}
		
		$a_attributes["definition"] = $def;
		
		$changes = array(
			"change" => array(
				$a_column => $a_attributes
				)
			);

		$r = $manager->alterTable($a_table, $changes, false);

		return $this->handleError($r, "modifyTableColumn(".$a_table.")");
	}

	/**
	* Rename a table column
	* Use this only on aleady "abstracted" tables.
	*
	* @param	string		table name
	* @param	string		old column name
	* @param	string		new column name
	*/
	function renameTableColumn($a_table, $a_column, $a_new_column)
	{
		// check table name
		if (!$this->checkColumnName($a_new_column))
		{
			$this->raisePearError("ilDB Error: renameTableColumn(".$a_table.",".$a_column.",".$a_new_column.")<br />".
				$this->error_str);
		}

		$manager = $this->db->loadModule('Manager');
		$reverse = $this->db->loadModule('Reverse');
		$def = $reverse->getTableFieldDefinition($a_table, $a_column);
		
		$this->handleError($def, "renameTableColumn(".$a_table.",".$a_column.",".$a_new_column.")");

		if (is_file("./Services/Database/classes/class.ilDBAnalyzer.php"))
		{
			include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		}
		else
		{
			include_once("../Services/Database/classes/class.ilDBAnalyzer.php");
		}

		$analyzer = new ilDBAnalyzer();
		$best_alt = $analyzer->getBestDefinitionAlternative($def);
		$def = $def[$best_alt];
		unset($def["nativetype"]);
		unset($def["mdb2type"]);
		
		$f["definition"] = $def;
		$f["name"] = $a_new_column;
		
		$changes = array(
			"rename" => array(
				$a_column => $f
				)
			);

		$r = $manager->alterTable($a_table, $changes, false);

		return $this->handleError($r, "renameTableColumn(".$a_table.",".$a_column.",".$a_new_column.")");
	}

	/**
	* Rename a table
	*
	* @param	string		old table name
	* @param	string		new table name
	*/
	function renameTable($a_name, $a_new_name)
	{
		// check table name
		if (!$this->checkTableName($a_new_name))
		{
			$this->raisePearError("ilDB Error: renameTable(".$a_name.",".$a_new_name.")<br />".
				$this->error_str);
		}

		$manager = $this->db->loadModule('Manager');
		$r = $manager->alterTable($a_name, array("name" => $a_new_name), false);

		return $this->handleError($r, "renameTable(".$a_name.",".$a_new_name.")");
	}

	/**
	* Add a primary key to a table
	*
	* @param	string		table name
	* @param	array		fields for primary key
	* @param	string		key name
	*/
	function addPrimaryKey($a_table, $a_fields)
	{
		$manager = $this->db->loadModule('Manager');
		
		$fields = array();
		foreach ($a_fields as $f)
		{
			$fields[$f] = array();
		}
		$definition = array (
			'primary' => true,
			'fields' => $fields
		);
		$r = $manager->createConstraint($a_table,
			$this->constraintName($a_table, $this->getPrimaryKeyIdentifier()), $definition);

		return $this->handleError($r, "addPrimaryKey(".$a_table.")");
	}
	
	/**
	* Primary key identifier
	*/
	function getPrimaryKeyIdentifier()
	{
		return "PRIMARY";
	}
	
	/**
	* Drop a primary key from a table
	*
	* @param	string		table name
	* @param	string		key name
	*/
	function dropPrimaryKey($a_table)
	{
		$manager = $this->db->loadModule('Manager');
		
		$r = $manager->dropConstraint($a_table,
			$this->constraintName($a_table, $this->getPrimaryKeyIdentifier()), true);

		return $this->handleError($r, "dropPrimaryKey(".$a_table.")");
	}

	/**
	* Add an index to a table
	*
	* @param	string		table name
	* @param	array		fields for index
	* @param	string		index name
	*/
	function addIndex($a_table, $a_fields, $a_name = "in", $a_fulltext = false)
	{
		$manager = $this->db->loadModule('Manager');
		
		// check index name
		if (!$this->checkIndexName($a_name))
		{
			$this->raisePearError("ilDB Error: addIndex(".$a_table.",".$a_name.")<br />".
				$this->error_str);
		}
		
		$fields = array();
		foreach ($a_fields as $f)
		{
			$fields[$f] = array();
		}
		$definition = array (
			'fields' => $fields
		);
		
		if (!$a_fulltext)
		{
			$r = $manager->createIndex($a_table, $this->constraintName($a_table, $a_name), $definition);
		}
		else
		{
			if ($this->supportsFulltext())
			{
				$this->addFulltextIndex($a_table, $a_fields, $a_name);
			}
		}

		return $this->handleError($r, "addIndex(".$a_table.")");
	}

	/**
	* Add fulltext index
	*/
	function addFulltextIndex($a_table, $a_fields, $a_name = "in")
	{
		return false;
	}

	/**
	* Is index a fulltext index?
	*/
	function isFulltextIndex($a_table, $a_name)
	{
		return false;
	}
	
	/**
	* Drop an index from a table.
	* Note: The index must have been created using MDB2
	*
	* @param	string		table name
	* @param	string		index name
	*/
	function dropIndex($a_table, $a_name = "in")
	{
		$manager = $this->db->loadModule('Manager');
		
		if (!$this->isFulltextIndex($a_table, $a_name))
		{
			$r = $manager->dropIndex($a_table, $this->constraintName($a_table, $a_name));
		}
		else
		{
			$this->dropFulltextIndex($a_table, $a_name);
		}

		return $this->handleError($r, "dropIndex(".$a_table.")");
	}

	/**
	* Create a sequence for a table
	*/
	function createSequence($a_table_name, $a_start = 1)
	{
		$manager = $this->db->loadModule('Manager');
		
		$r = $manager->createSequence($a_table_name, $a_start);

		return $this->handleError($r, "createSequence(".$a_table_name.")");
	}
	
	
	/**
	* Drop a sequence for a table
	*/
	function dropSequence($a_table_name)
	{
		$manager = $this->db->loadModule('Manager');
		
		$r = $manager->dropSequence($a_table_name);

		return $this->handleError($r, "dropSequence(".$a_table_name.")");
	}

	/**
	* Check whether a table name is valid
	*
	* @param	string		$a_name
	*/
	function checkTableName($a_name)
	{
		if (!preg_match ("/^[a-z]+[_a-z0-9]*$/", $a_name))
		{
			$this->error_str = "Table name must only contain _a-z0-9 and must start with a-z.";
			return false;
		}
		
		if ($this->isReservedWord($a_name))
		{
			$this->error_str = "Invalid table name '".$a_name."' (Reserved Word).";
			return false;
		}

		if (strtolower(substr($a_name, 0, 4)) == "sys_")
		{
			$this->error_str = "Invalid table name '".$a_name."'. Name must not start with 'sys_'.";
			return false;
		}

		if (strlen($a_name) > 22)
		{
			$this->error_str = "Invalid column name '".$a_name."'. Maximum column identifer lenght is 22 bytes.";
			return false;
		}
		
		return true;
	}
	
	/**
	* Check table columns definition
	*
	* @param	array		definition array
	*/
	function checkTableColumns($a_cols)
	{
		foreach ($a_cols as $col => $def)
		{
			if (!$this->checkColumn($col, $def))
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	* Check column definition
	*/
	function checkColumn($a_col, $a_def)
	{
		if (!$this->checkColumnName($a_col))
		{
			return false;
		}

		if (!$this->checkColumnDefinition($a_def))
		{
			return false;
		}
		
		return true;
	}
	
	/**
	* Check whether a column definition is valid
	*
	* @param	array		definition array
	*/
	function checkColumnDefinition($a_def, $a_modify_mode = false)
	{
		// check valid type
		if (!in_array($a_def["type"], array("text", "integer", "float", "date", "time", "timestamp", "clob", "blob")))
		{
			switch ($a_def["type"])
			{
				case "boolean":
					$this->error_str = "Invalid column type '".$a_def["type"]."'. Use integer(1) instead.";
					break;

				case "decimal":
					$this->error_str = "Invalid column type '".$a_def["type"]."'. Use float or integer instead.";
					break;
					
				default:
					$this->error_str = "Invalid column type '".$a_def["type"]."'. Allowed types are: ".
						"text, integer, float, date, time, timestamp, clob and blob.";
			}
		}
		
		// check used attributes
		$allowed_attributes = $this->allowed_attributes;
		
		foreach ($a_def as $k => $v)
		{
			if ($k != "type" && !in_array($k, $allowed_attributes[$a_def["type"]]))
			{
				$this->error_str = "Attribute '".$k."' is not allowed for column type '".$a_def["type"]."'.";
				return false;
			}
		}
		
		// type specific checks
		switch ($a_def["type"])
		{
			case "text":
				if ($a_def["length"] < 1 || $a_def["length"] > 4000)
				{
					if (!$a_modify_mode || isset($a_def["length"]))
					{
						$this->error_str = "Invalid length '".$a_def["length"]."' for type text.".
							" Length must be >=1 and <= 4000.";
						return false;
					}
				}
				break;

			case "integer":
				if (!in_array($a_def["length"], array(1, 2, 3, 4, 8)))
				{
					if (!$a_modify_mode || isset($a_def["length"]))
					{
						$this->error_str = "Invalid length '".$a_def["length"]."' for type integer.".
							" Length must be 1, 2, 3, 4 or 8 (bytes).";
						return false;
					}
				}
				if ($a_def["unsigned"])
				{
					$this->error_str = "Unsigned attribut must not be true for type integer.";
					return false;
				}
				break;
		}
		
		return true;
	}
	
	/**
	* Check whether a column name is valid
	*
	* @param	string		$a_name
	*/
	function checkColumnName($a_name)
	{
		if (!preg_match ("/^[a-z]+[_a-z0-9]*$/", $a_name))
		{
			$this->error_str = "Invalid column name '".$a_name."'. Column name must only contain _a-z0-9 and must start with a-z.";
			return false;
		}
		
		if ($this->isReservedWord($a_name))
		{
			$this->error_str = "Invalid column name '".$a_name."' (Reserved Word).";
			return false;
		}
		
		if (strtolower(substr($a_name, 0, 4)) == "sys_")
		{
			$this->error_str = "Invalid column name '".$a_name."'. Name must not start with 'sys_'.";
			return false;
		}

		if (strlen($a_name) > 30)
		{
			$this->error_str = "Invalid column name '".$a_name."'. Maximum column identifer lenght is 30 bytes.";
			return false;
		}

		return true;
	}

	/**
	* Check whether an index name is valid
	*
	* @param	string		$a_name
	*/
	function checkIndexName($a_name)
	{
		if (!preg_match ("/^[a-z]+[_a-z0-9]*$/", $a_name))
		{
			$this->error_str = "Invalid column name '".$a_name."'. Column name must only contain _a-z0-9 and must start with a-z.";
			return false;
		}
		
		if ($this->isReservedWord($a_name))
		{
			$this->error_str = "Invalid column name '".$a_name."' (Reserved Word).";
			return false;
		}
		
		if (strlen($a_name) > 3)
		{
			$this->error_str = "Invalid index name '".$a_name."'. Maximum index identifer lenght is 3 bytes.";
			return false;
		}

		return true;
	}

	function getAllowedAttributes()
	{
		return $this->allowed_attributes;
	}
	
	/**
	* Determine contraint name by table name and constraint name.
	* In MySQL these are "unique" per table, but they
	* must be "globally" unique in oracle. (so this one is overwritten there)
	*/
	function constraintName($a_table, $a_constraint)
	{
		return $a_constraint;
	}

	/**
	* Checks whether a word is a reserved word in one
	* of the supported databases
	*/
	static function isReservedWord($a_word)
	{
		include_once("./Services/Database/classes/class.ilDBMySQL.php");
		$mysql_reserved_words = ilDBMySQL::getReservedWords();
		if (in_array(strtoupper($a_word), $mysql_reserved_words))
		{
			return true;
		}
		include_once("./Services/Database/classes/class.ilDBOracle.php");
		$oracle_reserved_words = ilDBOracle::getReservedWords();
		if (in_array(strtoupper($a_word), $oracle_reserved_words))
		{
			return true;
		}
		include_once("./Services/Database/classes/class.ilDBPostreSQL.php");
		$postgres_reserved_words = ilDBPostgreSQL::getReservedWords();
		if (in_array(strtoupper($a_word), $postgres_reserved_words))
		{
			return true;
		}
	}
	
	//
	// Data query and manupilation functions
	//
	
	/**
	* Query
	*
	* Example:
	* - "SELECT * FROM data"
	*
	* For multiple similar queries/manipulations you may use prepare() and execute().
	*
	* @param string
	* @return object DB
	*/
	function query($sql)
	{
		$r = $this->db->query($sql);

//		$this->logStatement($sql);

		return $this->handleError($r, "query(".$sql.")");
	}

	/**
	* Formatted query (for SELECTS). Use %s as placeholder!
	*
	* @param		string		query
	* @param		array		type array
	* @param		arraay		value array
	*/
	function queryF($a_query, $a_types, $a_values)
	{
		if (!is_array($a_types) || !is_array($a_values) ||
			count($a_types) != count($a_values))
		{
			$this->raisePearError("ilDB::queryF: types and values must be arrays of same size.");
		}
		$quoted_values = array();
		foreach($a_types as $k => $t)
		{
			$quoted_values[] = $this->quote($a_values[$k], $t);
		}
		$query = vsprintf($a_query, $quoted_values);
		
		return $this->query($query);
	}
	
	/**
	* Formatted manupulate (for DELETE, UPDATE, INSERT). Use %s as placeholder!
	*
	* @param		string		query
	* @param		array		type array
	* @param		arraay		value array
	*/
	function manipulateF($a_query, $a_types, $a_values)
	{
		if (!is_array($a_types) || !is_array($a_values) ||
			count($a_types) != count($a_values))
		{
			$this->raisePearError("ilDB::queryF: types and values must be arrays of same size.");
		}
		$quoted_values = array();
		foreach($a_types as $k => $t)
		{
			$quoted_values[] = $this->quote($a_values[$k], $t);
		}
		$query = vsprintf($a_query, $quoted_values);
		
		return $this->manipulate($query);
	}

	/**
	* Helper function, should usually not be called
	*/
	function logStatement($sql)
	{
		$pos1 = strpos(strtolower($sql), "from ");
		$table = "";
		if ($pos1 > 0)
		{
			$tablef = substr($sql, $pos1+5);
			$pos2 = strpos(strtolower($tablef), " ");
			if ($pos2 > 0)
			{
				$table =substr($tablef, 0, $pos2);
			}
			else
			{
				$table = $tablef;
			}
		}
		if (trim($table) != "")
		{
			if (!is_array($this->ttt) || !in_array($table, $this->ttt))
			{
				echo "<br>".$table;
				$this->ttt[] = $table;
			}
		}
		else
		{
			echo "<br><b>".$sql."</b>";
		}
	}
	
	/**
	* Set limit and offset for a query
	*/
	function setLimit($a_limit, $a_offset = 0)
	{
		$this->db->setLimit($a_limit, $a_offset);
	}
	
	/**
	* Get next ID for an index
	*/
	function nextId($a_table_name)
	{
		// we do not create missing sequences automatically here
		// otherwise misspelled statements result in additional tables
		// please create sequences explicitly in the db update script
		$r = $this->db->nextId($a_table_name, false);

		return $this->handleError($r, "nextId(".$a_table_name.")");
	}

	/**
	* Data manipulation. This statement should be used for DELETE,  UPDATE
	* and INSERT statements.
	*
	* Example:
	* - "DELETE * FROM data WHERE id =".$ilDB->quote($id, "integer");
	*
	* @param	string		DML string
	* @return	int			affected rows
	*/
	function manipulate($sql)
	{
		$r = $this->db->exec($sql);

		return $this->handleError($r, "manipulate(".$sql.")");
	}

	/**
	* Prepare a query (SELECT) statement to be used with execute.
	*
	* @param	String	Query String
	* @param	Array	Placeholder Types
	*
	* @return	Resource handle for the prepared query on success, a MDB2 error on failure.
	*/
	function prepare($a_query, $a_types = null, $a_result_types = null)
	{		
		$res = $this->db->prepare($a_query, $a_types, $a_result_types);
		
		return $this->handleError($res, "prepare(".$a_query.")");
	}

	/**
	* Prepare a data manipulation statement to be used with execute.
	*
	* @param	String	Query String
	* @param	Array	Placeholder Types
	*
	* @return	Resource handle for the prepared query on success, a MDB2 error on failure.
	*/
	function prepareManip($a_query, $a_types = null)
	{
		$res = $this->db->prepare($a_query, $a_types, MDB2_PREPARE_MANIP);
		
		return $this->handleError($res, "prepareManip(".$a_query.")");
	}

	/**
	* Execute a query statement prepared by either prepare() or prepareManip()
	*
	* @param	object		Resource handle of the prepared query.
	* @param	array		Array of data (to be used for placeholders)
	*
	* @return	mixed		A result handle or MDB2_OK on success, a MDB2 error on failure
	*/
	function execute($a_stmt, $a_data = null)
	{
		$res = $a_stmt->execute($a_data);

		return $this->handleError($res, "execute(".$a_stmt->query.")");
	}

	/**
	* Execute a query statement prepared by either prepare() or prepareManip()
	* with multiple data arrays.
	*
	* @param	object		Resource handle of the prepared query.
	* @param	array		Array of array of data (to be used for placeholders)
	*
	* @return	mixed		A result handle or MDB2_OK on success, a MDB2 error on failure
	*/
	function executeMultiple($a_stmt, $a_data)
	{
		$res = $this->db->extended->executeMultiple($a_stmt,$a_data);

		return $this->handleError($res, "executeMultiple(".$a_stmt->query.")");
	}
	
	/**
	* Convenient method for standard insert statements, example field array:
	*
	* array("field1" => array("text", $name),
	*		"field2" => array("integer", $id))
	*/
	function insert($a_table, $a_columns)
	{
		$fields = array();
		$field_values = array();
		$placeholders = array();
		$types = array();
		$values = array();
		$lobs = false;
		$lob = array();
		foreach ($a_columns as $k => $col)
		{
			$fields[] = $k;
			$placeholders[] = "%s";
			$placeholders2[] = ":$k";
			$types[] = $col[0];
			$values[] = $col[1];
			$field_values[$k] = $col[1];
			if ($col[0] == "blob" || $col[0] == "clob")
			{
				$lobs = true;
				$lob[$k] = $k;
			}
		}
		if ($lobs)	// lobs -> use prepare execute (autoexecute broken in PEAR 2.4.1)
		{
			$st = $this->db->prepare("INSERT INTO ".$a_table." (".implode($fields,",").") VALUES (".
				implode($placeholders2,",").")", $types, MDB2_PREPARE_MANIP, $lob);
			$r = $st->execute($field_values);
			
			//$r = $this->db->extended->autoExecute($a_table, $field_values, MDB2_AUTOQUERY_INSERT, null, $types);
			$this->handleError($r, "insert / prepare/execute(".$a_table.")");
			$this->free($st);
		}
		else	// if no lobs are used, take simple manipulateF
		{
			$q = "INSERT INTO ".$a_table." (".implode($fields,",").") VALUES (".
				implode($placeholders,",").")";
			$r = $this->manipulateF($q, $types, $values);
		}
		return $r;
	}
	
	/**
	* Convenient method for standard update statements, example field array:
	*
	* array("field1" => array("text", $name),				// will use "%s"
	*		"field2" => array("integer", $id),				// will use "%s"
	*
	* Example where array: array("id" => array("integer", $id))
	*/
	function update($a_table, $a_columns, $a_where)
	{
		$fields = array();
		$field_values = array();
		$placeholders = array();
		$types = array();
		$values = array();
		$lobs = false;
		$lob = array();
		foreach ($a_columns as $k => $col)
		{
			$fields[] = $k;
			$placeholders[] = "%s";
			$placeholders2[] = ":$k";
			$types[] = $col[0];
			$values[] = $col[1];
			$field_values[$k] = $col[1];
			if ($col[0] == "blob" || $col[0] == "clob")
			{
				$lobs = true;
				$lob[$k] = $k;
			}
		}
		
		if ($lobs)
		{
			$q = "UPDATE ".$a_table." SET ";
			$lim = "";
			foreach ($fields as $k => $field)
			{
				$q.= $lim.$field." = ".$placeholders2[$k];
				$lim = ", ";
			}
			$q.= " WHERE ";
			$lim = "";
			foreach ($a_where as $k => $col)
			{
				$q.= $lim.$k." = ".$this->quote($col[1], $col[0]);
				$lim = " AND ";
			}
			$st = $this->db->prepare($q, $types, MDB2_PREPARE_MANIP, $lob);
			$r = $st->execute($field_values);
			
			//$r = $this->db->extended->autoExecute($a_table, $field_values, MDB2_AUTOQUERY_INSERT, null, $types);
			$this->handleError($r, "update / prepare/execute(".$a_table.")");
			$this->free($st);
		}
		else
		{
			foreach ($a_where as $k => $col)
			{
				$types[] = $col[0];
				$values[] = $col[1];
				$field_values[$k] = $col;
			}
			$q = "UPDATE ".$a_table." SET ";
			$lim = "";
			foreach ($fields as $k => $field)
			{
				$q.= $lim.$field." = ".$placeholders[$k];
				$lim = ", ";
			}
			$q.= " WHERE ";
			$lim = "";
			foreach ($a_where as $k => $col)
			{
				$q.= $lim.$k." = %s";
				$lim = " AND ";
			}
			
			$r = $this->manipulateF($q, $types, $values);
		}
		return $r;
	}

	/**
	* Fetch row as associative array from result set
	*
	* @param	object	result set
	*/
	function fetchAssoc($a_set)
	{
		return $a_set->fetchRow(DB_FETCHMODE_ASSOC);
	}
	
	/**
	* Free a statement / result set
	*/
	function free($a_st)
	{
		return $a_st->free();
	}

	/**
	* Fetch row as object from result set
	*
	* @param	object	result set
	*/
	function fetchObject($a_set)
	{
		return $a_set->fetchRow(DB_FETCHMODE_OBJECT);
	}

	/**
	* Fetch row as associative array from result set
	*
	* @param	object	result set
	*/
	function numRows($a_set)
	{
		return $a_set->numRows();
	}

	//
	// function and clauses abstraction
	//
	
	/**
	* Get abstract in-clause for given array.
	* Returns an array "field_name IN (?,?,?,...)" depending on the size of the array
	*
	* Example:
	*	$ids = array(10,12,18);
	*	$st = $ilDB->prepare("SELECT * FROM table ".
	*		"WHERE ".$ilDB->in("id", $ids),
	*		$ilDB->addTypesToArray($types, "integer", count($ids)));
	*	$set = $ilDB->execute($st, $ids);
	*/
	function in($a_field, $a_values, $negate = false, $a_type = "")
	{
		if (count($a_values) == 0)
		{
			return " 1=2 ";		// return a false statement on empty array
		}
		if ($a_type == "")		// untyped: used ? for prepare/execute
		{
			$str = $a_field.(($negate) ? " NOT" : "")." IN (?".str_repeat(",?", count($a_values) - 1).")";
		}
		else					// typed, use values for query/manipulate
		{
			$str = $a_field.(($negate) ? " NOT" : "")." IN (";
			$sep = "";
			foreach ($a_values as $v)
			{
				$str.= $sep.$this->quote($v, $a_type);
				$sep = ",";
			}
			$str.= ")";
		}
		
		return $str;
	}
	
	/**
	* Adds a type x times to an array
	*/
	function addTypesToArray($a_arr, $a_type, $a_cnt)
	{
		if (!is_array($a_arr))
		{
			$a_arr = array();
		}
		if ($a_cnt > 0)
		{
			$type_arr = array_fill(0, $a_cnt, $a_type);
		}
		else
		{
			$type_arr = array();
		}
		return array_merge($a_arr, $type_arr);
	}
	
	/**
	* now()
	*
	* @todo: oracle version
	*/
	function now()
	{
		return "now()";
	}
	
	/**
	* Like
	*
	* @param	string		column type; must be "text" or "clob" ("blob" added for lng_data)
	*/
	function like($a_col, $a_type, $a_value = "?")
	{
		if (!in_array($a_type, array("text", "clob", "blob")))
		{
			$this->raisePearError("Like: Invalid column type '".$a_type."'.", $this->error_class->FATAL);
		}
		if ($a_value == "?")
		{
			return $a_col." LIKE(?)";
		}
		else
		{
			return $a_col." LIKE(".$this->quote($a_value, $a_type).")";
		}
	}

	/**
	* Use this only on text fields.
	*/
	function equals($a_col, $a_value, $a_type, $a_empty_or_null = false)
	{
		if (!$a_empty_or_null || $a_value != "")
		{
			return $a_col." = ".$this->quote($a_value, $a_type);
		}
		else
		{
			return "(".$a_col." = ".$this->quote($a_value, "text")." OR $a_col IS NULL)";
		}
	}
	
	/**
	* Optimize Table
	*/
	function optimizeTable($a_table)
	{
		// needs to be overwritten in DBMS specific class
		// if necessary and possible
	}
	
	//
	// Schema related functions
	//
	
	/**
	* Check, whether a given table exists
	*
	* @param	string		table name
	* @return	boolean		true, if table exists
	*/
	function tableExists($a_table)
	{
		$tables = $this->listTables();

		if (is_array($tables))
		{
			if (in_array($a_table, $tables))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	* Checks for the existence of a table column
	*
	* @param string $a_table The table name which should be examined
	* @param string $a_column_name The name of the column
	* @return boolean TRUE if the table column exists, FALSE otherwise
	*/
	function tableColumnExists($a_table, $a_column_name)
	{
		
		$column_visibility = false;
		$manager = $this->db->loadModule('Manager');
		$r = $manager->listTableFields($a_table);

		if (!MDB2::isError($r))
		{
			foreach($r as $field)
			{
				if ($field == $a_column_name)
				{
					$column_visibility = true;
				}
			}
		}

		return $column_visibility;
	}
	
	/**
	* Get all tables
	*
	* @return array		Array of table names
	*/
	function listTables()
	{
		$manager = $this->db->loadModule('Manager');
		$r = $manager->listTables();

		if (!MDB2::isError($r))
		{
			return $r;
		}
		
		return false;
	}

	
	//
	// Quote Functions
	//
	
	/**
	* Wrapper for quote method. Deprecated, use prepare/prepareManip instead.
	*/
	function quote($a_query, $a_type = null)
	{
		if ($a_query == "" && is_null($a_type))
		{
			$a_query = "";
		}
		
		if ($a_type == "blob" || $a_type == "clob")
		{
			$this->raisePearError("ilDB::quote: Quoting not allowed on type '".$a_type."'. Please use ilDB->insert and ilDB->update to write clobs.", $this->error_class->FATAL);
		}

		return $this->db->quote($a_query, $a_type);
	}
	
	/**
	* Quote table and field names.
	*
	* Note: IF POSSIBLE, THIS METHOD SHOULD NOT BE USED. Rename
	* your table or field, if it conflicts with a reserved word.
	*
	*/
	function quoteIdentifier($a_identifier)
	{
		return $this->db->quoteIdentifier($a_identifier);
	}

	//
	// Transaction and Locking methods
	//
	
	/**
	* Begin Transaction. Please note that we currently do not use savepoints.
	*
	* @return	boolean		MDB2_OK on success
	*/
	function beginTransaction()
	{
		if (!$this->db->supports('transactions'))
		{
			$this->raisePearError("ilDB::beginTransaction: Transactions are not supported.", $this->error_class->FATAL);
		}
		$res = $this->db->beginTransaction();
		
		return $this->handleError($res, "beginTransaction()");
	}
	
	/**
	* Commit a transaction
	*/
	function commit()
	{
		$res = $this->db->commit();
		
		return $this->handleError($res, "commit()");
	}

	/**
	* Rollback a transaction
	*/
	function rollback()
	{
		$res = $this->db->rollback();
		
		return $this->handleError($res, "rollback()");
	}

	/**
	* Lock existing table
	* @param array (tablename => lock type READ, WRITE, READ LOCAL or LOW_PRIORITY) e.g array('tree' => 'WRITE')
	* @return boolean
	*/
	static function _lockTables($a_table_params)
	{
		global $ilDB;
		
		$lock_str = 'LOCK TABLES ';
		$counter = 0;
		foreach($a_table_params as $table_name => $type)
		{
			$lock_str .= $counter++ ? ',' : '';
			$lock_str .= $table_name.' '.$type;
		}

		$ilDB->query($lock_str);

		return true;
	}
	
	/**
	* unlock tables
	*/
	static function _unlockTables()
	{
		global $ilDB;
		
		$ilDB->query('UNLOCK TABLES');

		return true;
	}
	
	
//
//
// Older functions. Must be checked.
//
//

	/**
	* Wrapper for Pear autoExecute
	* @param string tablename
	* @param array fields values
	* @param int MDB2_AUTOQUERY_INSERT or MDB2_AUTOQUERY_UPDATE
	* @param string where condition (e.g. "obj_id = '7' AND ref_id = '5'")
	* @return mixed a new DB_result/DB_OK  or a DB_Error, if fail
	*/
	function autoExecute($a_tablename,$a_fields,$a_mode = MDB2_AUTOQUERY_INSERT,$a_where = false)
	{
		$res = $this->db->autoExecute($a_tablename,$a_fields,$a_mode,$a_where);

		return $this->handleError($res, "autoExecute(".$a_tablename.")");
	}

//
//
// Deprecated functions.
//
//

	/**
	* Get last insert id
	*/
	function getLastInsertId()
	{
		$res = $this->db->lastInsertId();
		if(MDB2::isError($res))
		{
			return false;
		}
		return $res;
	}

	/**
	* getOne. DEPRECATED. Should not be used anymore.
	* 
	* this is the wrapper itself. Runs a query and returns the first column of the first row
	* or in case of an error, jump to errorpage
	* 
	* @param string
	* @return object DB
	*/
	function getOne($sql)
	{
		//$r = $this->db->getOne($sql);
		$set = $this->db->query($sql);
		
		$this->handleError($set, "getOne(".$sql.")");
		
		if (!MDB2::isError($set))
		{
			$r = $set->fetchRow(DB_FETCHMODE_ASSOC);
	
			return $r[0];
		}
	}

	/**
	* getRow. DEPRECATED. Should not be used anymore
	*
	* this is the wrapper itself. query a string, and return the resultobject,
	* or in case of an error, jump to errorpage
	*
	* @param string
	* @return object DB
	*/
	function getRow($sql,$mode = DB_FETCHMODE_OBJECT)
	{
		$set = $this->query($sql);
		$r = $set->fetchRow($mode);
		//$r = $this->db->getrow($sql,$mode);

		$this->handleError($r, "getRow(".$sql.")");
		
		return $r;
	} //end function

} //end Class
?>
