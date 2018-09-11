<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesDatabase Services/Database
 *
 * @deprecated only used for OracleSopport
 */

//pear MDB2 abstraction layer
require_once('./Services/Database/lib/PEAR/MDB2.php');
require_once 'Services/Database/classes/QueryUtils/class.ilMySQLQueryUtils.php';
require_once 'Services/Database/interfaces/interface.ilDBInterface.php';

//echo "-".ilDBConstants::FETCHMODE_ASSOC."-";
//echo "+".ilDBConstants::FETCHMODE_OBJECT."+";


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
abstract class ilDB extends PEAR implements ilDBInterface
{
	const LOCK_WRITE = 1;
	const LOCK_READ  = 2;
	
	
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
	
	var $sub_type;

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
	* Set database port
	*
	* @param	string		database port
	*/
	function setDBPort($a_port)
	{
		$this->db_port = $a_port;
	}
	
	/**
	* Get database port
	*
	* @param	string		database port
	*/
	function getDBPort()
	{
		return $this->db_port;
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
	static function getReservedWords(){
		return array();
	}


	/**
	 * En/disable result buffering
	 * @param bool $a_status 
	 */
	public function enableResultBuffering($a_status)
	{
		$this->db->setOption('result_buffering',$a_status);
	}

	/**
	* Init db parameters from ini file
	* @param $tmpClientIniFile	overwrite global client ini file if is set to an object 
	*/
	function initFromIniFile($tmpClientIniFile = null)
	{
		global $ilClientIniFile;
		
		//overwrite global client ini file if local parameter is set 
		if (is_object($tmpClientIniFile))
			$clientIniFile = $tmpClientIniFile;
		else 
			$clientIniFile = $ilClientIniFile;	
			
		if (is_object($clientIniFile ))
		{
			$this->setDBUser($clientIniFile ->readVariable("db", "user"));
			$this->setDBHost($clientIniFile ->readVariable("db", "host"));
			$this->setDBPort($clientIniFile ->readVariable("db", "port"));
			$this->setDBPassword($clientIniFile ->readVariable("db", "pass"));
			$this->setDBName($clientIniFile ->readVariable("db", "name"));
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
//echo $this->getDSN();
		//check dsn
		if ($this->getDSN() == "")
		{
			$this->raisePearError("No DSN given");
		}

		//connect to database
		$this->doConnect();
		
		if ($a_return_false_for_error && MDB2::isError($this->db))
		{
			return false;
		}
			
		$this->loadMDB2Extensions();
		
		// set empty value portability to PEAR::DB behaviour
		if (!$this->isDbError($this->db))
		{
			$this->db->setOption('portability', MDB2_PORTABILITY_ALL);
		}
		//check error
		$this->handleError($this->db);
		
		// anything, that must be done to initialize the connection
		$this->initConnection();

		return true;
	}
	
	/**
	* Standard way to connect to db
	*/
	function doConnect()
	{
		$this->db = MDB2::connect($this->getDSN(),
			array("use_transactions" => true));
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
	 * Supports slave
	 *
	 * @param
	 * @return
	 */
	function supportsSlave()
	{
		return false;
	}

	/**
	 * @param $feature
	 * @return bool
	 */
	public function supports($feature) {
		switch ($feature) {
			case 'transaction':
				return $this->supportsTransactions();
			case 'fulltext':
				return $this->supportsFulltext();
			case 'slave':
				return $this->supportsSlave();
			default:
				return false;
		}
	}


	/**
	 * @return bool
	 */
	public function supportsTransactions() {
		// we generally do not want ilDB to support transactions, only PDO-instances
		return false;
	}

	/**
	 * Use slave
	 *
	 * @param
	 * @return
	 */
	function useSlave($a_val = true)
	{
		if (!$this->supportsSlave())
		{
			return false;
		}
		$this->use_slave = $a_val;
	}

	/**
	* Handle MDB2 Errors
	*
	* @param	mixed 	result set or anything that is a MDB2::error if
	*					something went wrong
	*/
	function handleError($a_res, $a_info = "", $a_level = "") {
		global $ilLog;

		if (MDB2::isError($a_res)) {
			if ($a_level == "") {
				$a_level = $this->error_class->FATAL;
			}

			// :TODO: ADT (jluetzen)

			// if(!$this->exception)
			if (true) {
				// Show stack
				try {
					throw new Exception();
				} catch (Exception $e) {
					$stack = $e->getTraceAsString();
				}

				if (is_object($ilLog)) {
					$ilLog->logStack();
				}
//				$this->raisePearError("ilDB Error: " . $a_info . "<br />" . $a_res->getMessage() . "<br />" . $a_res->getUserInfo() . "<br />"
//				                      . $stack, $a_level);

				throw new ilDatabaseException("ilDB Error: " . $a_info . "<br />" . $a_res->getMessage() . "<br />" . $a_res->getUserInfo() . "<br />"
				                              . $stack, $a_level);
			}
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
	* Create database
	*/
	function createDatabase($a_name, $a_charset = "utf8", $a_collation = "")
	{
		if ($a_collation != "")
		{
			$sql = "CREATE DATABASE ".$a_name.
				" CHARACTER SET ".$a_charset.
				" COLLATE ".$a_collation;
		}
		else
		{
			$sql = "CREATE DATABASE ".$a_name.
				" CHARACTER SET ".$a_charset;
		}

		return $this->query($sql, false);
	}
	
	
	/**
	* Create a new table in the database
	*
	* @param	string		table name
	* @param	array		definition array: array("col1" => array("type" => "text", ...))
	* @param	boolean		drop table automatically, if it already exists
	*/
	function createTable($a_name, $a_definition_array, $a_drop_table = false,
		$a_ignore_erros = false)
	{
		// check table name
		if (!$this->checkTableName($a_name) && !$a_ignore_erros)
		{
			$this->raisePearError("ilDB Error: createTable(".$a_name.")<br />".
				$this->error_str);
		}
		
		// check definition array
		if (!$this->checkTableColumns($a_definition_array) && !$a_ignore_erros)
		{
			$this->raisePearError("ilDB Error: createTable(".$a_name.")<br />".
				$this->error_str);
		}

		if ($a_drop_table)
		{
			$this->dropTable($a_name, false);
		}
		
		$options = $this->getCreateTableOptions();
		
		$manager = $this->db->loadModule('Manager');
		$r = $manager->createTable($a_name, $a_definition_array, $options);

		return $this->handleError($r, "createTable(".$a_name.")");
	}
	
	/**
	 * Get options for the create table statement 
	 * 
	 * @return array
	 */
	protected function getCreateTableOptions()
	{
		return array();
	}
	
	/**
	 * Drop a table
	 *
	 * @param	string		table name
	 * @param	boolean		raise an error, if table not exists
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

		if ($this->getDBType() == "oracle")
		{
			// drop table constraints
			$constraints = $manager->listTableConstraints($a_name);
			$this->handleError($constraints, "dropTable(".$a_name."), listTableConstraints");
			foreach ($constraints as $c)
			{
				if (substr($c, 0, 4) != "sys_")
				{
					$r = $manager->dropConstraint($a_name, $c);
					$this->handleError($r, "dropTable(".$a_name."), dropConstraint");
				}
			}

			// drop table indexes
			$indexes = $manager->listTableIndexes($a_name);
			$this->handleError($indexes, "dropTable(".$a_name."), listTableIndexes");
			foreach ($indexes as $i)
			{
				$r = $manager->dropIndex($a_name, $i);
				$this->handleError($r, "dropTable(".$a_name."), dropIndex");
			}
		}

		// drop sequence
		$seqs = $manager->listSequences();
		if (in_array($a_name, $seqs))
		{
			$r = $manager->dropSequence($a_name);
			$this->handleError($r, "dropTable(".$a_name."), dropSequence");
		}
		
		// drop table
		$r = $manager->dropTable($a_name);

		return $this->handleError($r, "dropTable(".$a_name.")");
	}
	
	/**
	* Alter a table in the database
	* This method is DEPRECATED, see http://www.ilias.de/docu/goto.php?target=pg_25354_42&client_id=docu
	* PLEASE USE THE SPECIALIZED METHODS OF THIS CLASS TO CHANGE THE DB SCHEMA
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

		// oracle workaround: do not set null, if null already given
		if ($this->getDbType() == "oracle")
		{
			if ($def["notnull"] == true && ($a_attributes["notnull"] == true
				|| !isset($a_attributes["notnull"])))
			{
				unset($def["notnull"]);
				unset($a_attributes["notnull"]);
			}
			if ($def["notnull"] == false && ($a_attributes["notnull"] == false
				|| !isset($a_attributes["notnull"])))
			{
				unset($def["notnull"]);
				unset($a_attributes["notnull"]);
			}
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
		
        // The abstraction_progress is no longer used in ILIAS, see http://www.ilias.de/mantis/view.php?id=19513
        //		$query = "UPDATE abstraction_progress ".
        //			"SET table_name = ".$this->db->quote($a_new_name,'text')." ".
        //			"WHERE table_name = ".$this->db->quote($a_name,'text');
        //		$this->db->query($query);

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
		/**
		 * @var $manager MDB2_Driver_Manager_mysqli
		 */
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
	 * Check if index exists
	 * @param type $a_table
	 * @param type $a_fields
	 */
	public function indexExistsByFields($a_table, $a_fields)
	{
		$manager = $this->db->loadModule('Manager');
		$reverse = $this->db->loadModule('Reverse');
		if($manager)
		{
			foreach($manager->listTableIndexes($a_table) as $idx_name)
			{
				$def = $reverse->getTableIndexDefinition($a_table,$idx_name);
				$idx_fields = array_keys((array) $def['fields']);
				
				if($idx_fields === $a_fields)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Drop index by field(s)
	 * @param type $a_table
	 * @param type $a_fields
	 * @return boolean
	 */
	public function dropIndexByFields($a_table, $a_fields)
	{
		$manager = $this->db->loadModule('Manager');
		$reverse = $this->db->loadModule('Reverse');
		if($manager)
		{
			foreach($manager->listTableIndexes($a_table) as $idx_name)
			{
				$def = $reverse->getTableIndexDefinition($a_table,$idx_name);
				$idx_fields = array_keys((array) $def['fields']);
				
				if($idx_fields === $a_fields)
				{
					return $this->dropIndex($a_table, $idx_name);
				}
			}
		}
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
	* Add a unique constraint to a table
	*
	* @param	string		table name
	* @param	array		fields being unique
	* @param	string		index name
	*/
	function addUniqueConstraint($a_table, $a_fields, $a_name = "con")
	{
		$manager = $this->db->loadModule('Manager');
		
		// check index name
		if (!$this->checkIndexName($a_name))
		{
			$this->raisePearError("ilDB Error: addUniqueConstraint(".$a_table.",".$a_name.")<br />".
				$this->error_str);
		}
		
		$fields = array();
		foreach ($a_fields as $f)
		{
			$fields[$f] = array();
		}
		$definition = array (
			'unique' => true,
			'fields' => $fields
		);
		
		$r = $manager->createConstraint($a_table, $this->constraintName($a_table, $a_name), $definition);

		return $this->handleError($r, "addUniqueConstraint(".$a_table.")");
	}

	/**
	 * Drop a constraint from a table.
	 * Note: The constraint must have been created using MDB2
	 *
	 * @param	string		table name
	 * @param	string		constraint name
	 */
	public function dropUniqueConstraint($a_table, $a_name = "con")
	{
		$manager = $this->db->loadModule('Manager');

		$r = $manager->dropConstraint(
			$a_table, $this->constraintName($a_table, $a_name), false
		);

		return $this->handleError($r, "dropUniqueConstraint(".$a_table.")");
	}

	/**
	 * Drop constraint by field(s)
	 *
	 * @param	string		table name
	 * @param	array		fields array
	 */
	public function dropUniqueConstraintByFields($a_table, $a_fields)
	{
		if (is_file("./Services/Database/classes/class.ilDBAnalyzer.php"))
		{
			include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		}
		else
		{
			include_once("../Services/Database/classes/class.ilDBAnalyzer.php");
		}
		$analyzer = new ilDBAnalyzer();
		$cons = $analyzer->getConstraintsInformation($a_table);
		foreach ($cons as $c)
		{
			if ($c["type"] == "unique" && count($a_fields) == count($c["fields"]))
			{
				$all_in = true;
				foreach ($a_fields as $f)
				{
					if (!isset($c["fields"][$f]))
					{
						$all_in = false;
					}
				}
				if ($all_in)
				{
					return $this->dropUniqueConstraint($a_table, $c['name']);
				}
			}
		}
		return false;
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
			$this->error_str = "Invalid table name '".$a_name."'. Maximum table identifer length is 22 bytes.";
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
			$this->error_str = "Invalid column name '".$a_name."'. Maximum column identifer length is 30 bytes.";
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
			$this->error_str = "Invalid index name '".$a_name."'. Maximum index identifer length is 3 bytes.";
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
	public static function isReservedWord($a_word)
	{
		require_once('./Services/Database/classes/PDO/FieldDefinition/class.ilDBPdoMySQLFieldDefinition.php');
		global $DIC;
		$ilDBPdoMySQLFieldDefinition = new ilDBPdoMySQLFieldDefinition($DIC['ilDB']);

		return $ilDBPdoMySQLFieldDefinition->isReserved($a_word);
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
	* @return ilPDOStatement DB
	*/
	function query($sql, $a_handle_error = true)
	{
		global $ilBench;

		if (is_object($ilBench))
		{
			$ilBench->startDbBench($sql);
		}
		$r = $this->db->query($sql);
		if (is_object($ilBench))
		{
			$ilBench->stopDbBench();
		}
		
		if ($a_handle_error)
		{
			return $this->handleError($r, "query(".$sql.")");
		}

		return $r;
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
			$this->raisePearError("ilDB::queryF: Types and values must be arrays of same size. ($a_query)");
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
			$this->raisePearError("ilDB::manipulateF: types and values must be arrays of same size. ($a_query)");
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
		global $ilBench;

		if (is_object($ilBench))
		{
			$ilBench->startDbBench($sql);
		}
		$r = $this->db->exec($sql);
		if (is_object($ilBench))
		{
			$ilBench->stopDbBench();
		}

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
			
			// integer auto-typecast (this casts bool values to integer) 
			if ($col[0] == 'integer' && !is_null($col[1]))
			{
				$col[1] = (int) $col[1];
			}
			
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
			
			$this->handleError($st, "insert / prepare/execute(".$a_table.")");
			
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
			
			// integer auto-typecast (this casts bool values to integer) 
			if ($col[0] == 'integer' && !is_null($col[1]))
			{
				$col[1] = (int) $col[1];
			}

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
	* Replace into method.
	*
	* @param	string		table name
	* @param	array		primary key values: array("field1" => array("text", $name), "field2" => ...)
	* @param	array		other values: array("field1" => array("text", $name), "field2" => ...)
	*/
	function replace($a_table, $a_pk_columns, $a_other_columns)
	{
		// this is the mysql implementation
		$a_columns = array_merge($a_pk_columns, $a_other_columns);
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
			
			// integer auto-typecast (this casts bool values to integer) 
			if ($col[0] == 'integer' && !is_null($col[1]))
			{
				$col[1] = (int) $col[1];
			}
			
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
			$st = $this->db->prepare("REPLACE INTO ".$a_table." (".implode($fields,",").") VALUES (".
				implode($placeholders2,",").")", $types, MDB2_PREPARE_MANIP, $lob);
			$this->handleError($st, "insert / prepare/execute(".$a_table.")");
			$r = $st->execute($field_values);
			//$r = $this->db->extended->autoExecute($a_table, $field_values, MDB2_AUTOQUERY_INSERT, null, $types);
			$this->handleError($r, "insert / prepare/execute(".$a_table.")");
			$this->free($st);
		}
		else	// if no lobs are used, take simple manipulateF
		{
			$q = "REPLACE INTO ".$a_table." (".implode($fields,",").") VALUES (".
				implode($placeholders,",").")";
			$r = $this->manipulateF($q, $types, $values);			
		}
		return $r;
	}

	/**
	* Fetch row as associative array from result set
	*
	* @param	mixed	result set
	*/
	function fetchAssoc($a_set)
	{
		return $a_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
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
		return $a_set->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
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
			// BEGIN fixed mantis #0014191:
			//return " 1=2 ";		// return a false statement on empty array
			return $negate ? ' 1=1 ' : ' 1=2 ';
			// END fixed mantis #0014191:
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
	*/
	function now()
	{
		return "now()";
	}
	
	
	/**
	 * Abstraction of SQL function CONCAT
	 * @param array $a_values array(
	 * 	array('title','text'),
	 * 	array('description','clob'),
	 * 	array('some text','text');
	 * @param bool	$a_allow_null
	 * @return 
	 */
	public function concat(array $a_values,$a_allow_null = true)
	{
		if(!count($a_values))
		{
			return ' ';
		}

		$concat = ' CONCAT(';
		$first = true;
		foreach($a_values as $field_info)
		{
			$val = $field_info[0];
			
			if(!$first)
			{
				$concat .= ',';
			}
			
			if($a_allow_null)
			{
				$concat .= 'COALESCE(';
			}
			$concat .= $val;
			
			if($a_allow_null)
			{
				$concat .= ",''";
				$concat .= ')';
			}
			
			$first = false;
		}
		$concat .= ') ';
		return $concat;
	}
	
	/**
	 * Substring
	 *
	 * @param
	 * @return
	 */
	function substr($a_exp, $a_pos = 1, $a_len = -1)
	{
		$lenstr = "";
		if ($a_len > -1)
		{
			$lenstr = ", ".$a_len;
		}
		return " SUBSTR(".$a_exp.", ".$a_pos.$lenstr.") ";
	}
	
	/**
	 * Upper
	 *
	 * @param	string		expression
	 * @return	string		upper sql string
	 */
	function upper($a_exp)
	{
		return " UPPER(".$a_exp.") ";
	}

	/**
	 * Upper
	 *
	 * @param	string		expression
	 * @return	string		upper sql string
	 */
	function lower($a_exp)
	{
		return " LOWER(".$a_exp.") ";
	}
	
	/**
	 * Create locate string
	 * @param string $a_needle
	 * @param string $a_string
	 * @param int $a_start_pos [optional]
	 * @return 
	 */
	public function locate($a_needle,$a_string,$a_start_pos = 1)
	{
		$locate = ' LOCATE( ';
		$locate .= $a_needle;
		$locate .= ',';
		$locate .= $a_string;
		$locate .= ',';
		$locate .= $a_start_pos;
		$locate .= ') ';
		return $locate;
	}


	/**
	* Like
	*
	* @param	string		column type; must be "text" or "clob" ("blob" added for lng_data)
	*/
	function like($a_col, $a_type, $a_value = "?", $case_insensitive = true)
	{
		if (!in_array($a_type, array("text", "clob", "blob")))
		{
			$this->raisePearError("Like: Invalid column type '".$a_type."'.", $this->error_class->FATAL);
		}
		if ($a_value == "?")
		{
			if ($case_insensitive)
			{
				return "UPPER(".$a_col.") LIKE(UPPER(?))";
			}
			else
			{
				return $a_col ." LIKE(?)";
			}
		}
		else
		{
			if ($case_insensitive)
			{
				// Always quote as text
				return " UPPER(".$a_col.") LIKE(UPPER(".$this->quote($a_value, 'text')."))";
			}
			else
			{
				// Always quote as text
				return " ".$a_col." LIKE(".$this->quote($a_value, 'text').")";
			}
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
			return "(".$a_col." = '' OR $a_col IS NULL)";
		}
	}
	
	/**
	* Use this only on text fields.
	*/
	function equalsNot($a_col, $a_value, $a_type, $a_empty_or_null = false)
	{
		if (!$a_empty_or_null)
		{
			return $a_col." <> ".$this->quote($a_value, $a_type);
		}
		if ($a_value != "")
		{
			return "(".$a_col." <> ".$this->quote($a_value, $a_type). " OR ".
				$a_col." IS NULL)";
		}
		else
		{
			return "(".$a_col." <> '' AND $a_col IS NOT NULL)";
		}
	}

	/**
	* fromUnixtime (makes timestamp out of unix timestamp)
	*
	* @param	string		expression
	* @param	boolean		typecasting to text y/n
	*/
	function fromUnixtime($a_expr, $a_to_text = true)
	{
		return "FROM_UNIXTIME(".$a_expr.")";
	}
	
	/**
	* Unix timestamp
	*/
	function unixTimestamp()
	{
		return "UNIX_TIMESTAMP()";
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
	 * Checks if a unique constraint exists based on the fields of the unique constraint (not the name)
	 *
	 * @param string $a_table table name
	 * @param array $a_fields array of field names (strings)
	 * @return bool false if no unique constraint with the given fields exists
	 */
	public function uniqueConstraintExists($a_table, array $a_fields)
	{
		if (is_file("./Services/Database/classes/class.ilDBAnalyzer.php"))
		{
			include_once("./Services/Database/classes/class.ilDBAnalyzer.php");
		}
		else
		{
			include_once("../Services/Database/classes/class.ilDBAnalyzer.php");
		}
		$analyzer = new ilDBAnalyzer();
		$cons = $analyzer->getConstraintsInformation($a_table);
		foreach ($cons as $c)
		{
			if ($c["type"] == "unique" && count($a_fields) == count($c["fields"]))
			{
				$all_in = true;
				foreach ($a_fields as $f)
				{
					if (!isset($c["fields"][$f]))
					{
						$all_in = false;
					}
				}
				if ($all_in)
				{
					return true;
				}
			}
		}
		return false;
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

	/**
	* Check, whether a given sequence exists
	*
	* @param	string		sequence name
	* @return	boolean		true, if sequence exists
	*/
	function sequenceExists($a_sequence)
	{
		$sequences = $this->listSequences();
		
		if (is_array($sequences))
		{
			if (in_array($a_sequence, $sequences))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	* Get all sequences
	*
	* @return array		Array of sequence names
	*/
	function listSequences()
	{
		$manager = $this->db->loadModule('Manager');
		$r = $manager->listSequences();

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
	 * Wrapper for quote method.
	 */
	function quote($a_query, $a_type = null)
	{
		if ($a_query == "" && is_null($a_type))
		{
			$a_query = "";
		}
		
		// Performance fix
		if($a_type == 'integer' && !is_null($a_query))
		{
			return (int) $a_query;
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
	function quoteIdentifier($a_identifier, $check_option = false)
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
	 * Abstraction of lock table
	 * @param array table definitions
	 * @deprecated Use ilAtomQuery instead
	 * @return 
	 */
	abstract public function lockTables($a_tables);
	
	/**
	 * Unlock tables locked by previous lock table calls
	 * @deprecated Use ilAtomQuery instead
	 * @return 
	 */
	abstract public function unlockTables();

	
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
			$r = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
	
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
	function getRow($sql,$mode = ilDBConstants::FETCHMODE_OBJECT)
	{
		$set = $this->query($sql);
		$r = $set->fetchRow($mode);
		//$r = $this->db->getrow($sql,$mode);

		$this->handleError($r, "getRow(".$sql.")");
		
		return $r;
	} //end function

	/**
	 * @param $query_result
	 * @param int $fetch_mode
	 * @return array
	 */
	public function fetchAll($query_result, $fetch_mode = ilDBConstants::FETCHMODE_ASSOC) {
		/**
		 * @var $query_result ilPDOStatement
		 */
		$return = array();
		while ($data = $query_result->fetch($fetch_mode)) {
			$return[] = $data;
		}

		return $return;
	}
		
	/**
	 * Set sub type
	 * 
	 * @param string $a_value 
	 */
	function setSubType($a_value)
	{
		$this->sub_type = (string)$a_value;
	}
	
	/**
	 * Get sub type
	 * 
	 * @return string 
	 */
	function getSubType()
	{
		return $this->sub_type;
	}


	/**
	 * @param string $engine
	 *
	 * @return array
	 */
	public function migrateAllTablesToEngine($engine = ilDBConstants::MYSQL_ENGINE_INNODB)
	{
		return array();
	}


	/**
	 * @return bool
	 */
	public function supportsEngineMigration()
	{
		return false;
	}

	
	/**
	 * @param $table_name
	 * @return string
	 */
	public function getSequenceName($table_name) {
		return $this->db->getSequenceName($table_name);
	}


	/**
	 * @return \ilAtomQuery
	 */
	public function buildAtomQuery() {
		require_once('./Services/Database/classes/Atom/class.ilAtomQueryLock.php');

		return new ilAtomQueryLock($this);
	}


	/**
	 * @inheritdoc
	 */
	public function sanitizeMB4StringIfNotSupported($query)
	{
		if (!$this->doesCollationSupportMB4Strings()) {
			$query = preg_replace(
				'/[\x{10000}-\x{10FFFF}]/u', ilDBConstants::MB4_REPLACEMENT, $query
			);
		}

		return $query;
	}


	/**
	 * @inheritDoc
	 */
	public function doesCollationSupportMB4Strings()
	{
		return false;
	}


	/**
	 * @inheritDoc
	 */
	public function cast($a_field_name, $a_dest_type) {
		$manager = $this->db->loadModule('Manager');
		return $manager->getQueryUtils()->cast($a_field_name, $a_dest_type);
	}
}
