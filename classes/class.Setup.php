<?php

include_once("./classes/class.IniFile.php");
include_once("./classes/class.util.php");
include_once("DB.php");

/**
* Setup class
*
* class to setup ILIAS first and maintain the ini-settings and the database
*
* @author Peter Gabriel <pgabriel@databay.de>
* @package application
* @access public
* @version $Id$
*/
class Setup
{
	/**
	 * ini file
	 * @var string
	 * @access private
	 */
	var $INI_FILE = "./ilias.ini";
	
	/**
	 * sql-template-file
	 * @var string
	 * @access private
	 */
	var $SQL_FILE = "./sql/ilias3.sql";
	
    /**
	 * default ini file
	 * @access private
	 * @var string
	 */
	var $DEFAULT_INI_FILE = "./ilias.master.ini";
	
    /**
	 *  database connector
	 *  @var string
	 *  @access public
	*/
    var $dsn = "";
	
    /**
	 *  database handle
	 *  @var object DB
	 *  @access private
	 */
    var $db;
	
    /**
	 *  ini-object
	 *  @var object IniFile
	 *  @access private
	 */
	var $ini;
	
	/**
	 * default array for ini-file
	 * @var array
     * @access private
	 */
	var $default;
	
	/**
    * constructor
    * @return boolean
    */

    function getDefaults()
    {
	//default values are in $DEFAULTINIFILE
	//NOTE: please don't use any brackets
	$this->default = parse_ini_file("./ilias.master.ini", true);
	
	//build list of databasetypes
		$this->dbTypes = array();
		$this->dbTypes["mysql"] = "MySQL";
		$this->dbTypes["pgsql"] = "PostgreSQL";
		$this->dbTypes["ibase"] = "InterBase";
		$this->dbTypes["msql"] = "Mini SQL";
		$this->dbTypes["mssql"] = " Microsoft SQL Server";
		$this->dbTypes["oci8"] = "Oracle 7/8/8i";
		$this->dbTypes["odbc"] = "ODBC (Open Database Connectivity)";
		$this->dbTypes["sybase"] = "SyBase";
		$this->dbTypes["ifx"] = "Informix";
		$this->dbTypes["fbsql"] = "FrontBase";
    }

    /**
	* constructor
	*/
	function Setup()
    {
		$this->ini = new IniFile($this->INI_FILE);
    }

	/**
	* try to read the ini file
	*/
    function readIniFile()
    {
		// get settings from ini file
		$this->ini = new IniFile($this->INI_FILE);
		$this->ini->read();
		//check for error
		if ($this->ini->ERROR != "")
		{
			$this->error = $this->ini->ERROR;
			return false;
		}
		
		//here only dbsetting are interesting
		$this->setDbType($this->ini->readVariable("db","type"));
		$this->setDbHost($this->ini->readVariable("db","host"));
		$this->setDbName($this->ini->readVariable("db","name"));
		$this->setDbUser($this->ini->readVariable("db","user"));
		$this->setDbPass($this->ini->readVariable("db","pass"));

		$this->setDSN();
		
		// set tplPath
		$this->tplPath = TUtil::setPathStr($this->ini->readVariable("server","tpl_path"));

		return true;
    }

	/**
	* set the dsns
	*/
	function setDSN()
	{
		$this->dsn_host = $this->dbType."://".$this->dbUser.":".$this->dbPass."@".$this->dbHost;
		$this->dsn = $this->dbType."://".$this->dbUser.":".$this->dbPass."@".$this->dbHost."/".$this->dbName;
	}
	
    /**
	 * connect
	 */
     function connect()
	 {
		 // build dsn of database connection and connect
		 $this->dsn = $this->dbtype.
			 "://".$this->dbuser.
			 ":".$this->dbpass.
			 "@".$this->dbhost.

		 $this->db = DB::connect($this->dsn,true);

		 if (DB::isError($this->db)) {
			 $this->error_msg = $this->db->getMessage();
			 $this->error = "not_connected_to_db";
			 return false;
		 }

		 return true;
	 }

    /**
    * destructor
	* 
    * @return boolean
    */
    function _Setup()
	{
		if ($this->readVariable("db","type") != "")
		{
			$this->db->disconnect();
        }
		return true;
    }

	/**
	 * set the databasetype
	 * @param string
	 */
	function setDbType($str)
	{
		$this->dbType = $str;
		$this->setDSN();
	}
	
	/**
	 * set the host
	 * @param string
	 */
	function setDbHost($str)
	{
		$this->dbHost = $str;
		$this->setDSN();
	}

	/**
	 * set the name of database
	 * @param string
	 */
	function setDbName($str)
	{
		$this->dbName = $str;
		$this->setDSN();
	}

	/**
	 * set the user
	 * @param string
	 */
	function setDbUser($str)
	{
		$this->dbUser = $str;
		$this->setDSN();
	}

	/**
	 * set the password
	 * @param string
	 */
	function setDbPass($str)
	{
		$this->dbPass = $str;
		$this->setDSN();
	}

    /**
	 * execute a query
	 * @param string 
	 * @param string
	 * @return bool true
	 */
	function execQuery($db,$str)
	{
		$sql = explode("\n",trim($str));
		for ($i=0; $i<count($sql); $i++)
		{
			$sql[$i] = trim($sql[$i]);
			if ($sql[$i] != "" && substr($sql[$i],0,1)!="#")
			{
				//take line per line, until last char is ";"
				if (substr($sql[$i],-1)==";")
				{
					//query is complete
					$q .= " ".substr($sql[$i],0,-1);
					$r = $db->query($q);
					if ($r == false)
						return false;
					unset($q);
				} //if
				else
				{
					$q .= " ".$sql[$i];
				} //else
			} //if
		} //for
		return true;
	}


	/**
	* check database connection
	*/
	function checkDatabaseHost()
	{
        //connect to databasehost
		$db = DB::connect($this->dsn_host);

		if (DB::isError($db))
		{
			$this->error_msg = $db->getMessage();
			$this->error = "data_invalid";
			return false;
		}
		
		return true;
	}
	
	/**
	* check database connection
	*/
	function checkDatabaseExists()
	{
		//try to connect to database
		$db = DB::connect($this->dsn);
		if (DB::isError($db))
		{
			return false;
		}
		else
			return true;
	}
	
	/**
	 * set the database data
	*/
    function installDatabase()
	{
		//check parameters
		if ($this->dbType=="" || $this->dbHost=="" || $this->dbName=="" || $this->dbUser=="")
		{
			$this->error = "empty_fields";
			return false;
		}

		if ($this->checkDatabaseHost() == false)
		{
			$this->error = "no_connection_to_host";
			return false;		
		}

		if ($this->checkDatabaseExists() == true)
		{
			$this->error = "database_exists";
			return false;
		}

		//create database
		$db = DB::connect($this->dsn_host);
		if (DB::isError($db))
		{
			$this->error_msg = $db->getMessage();
			$this->error = "connection_failed";
			return false;
		}

		$sql = "CREATE DATABASE ".$this->dbName;
		$r = $db->query($sql);

		if (DB::isError($r))
		{
			$this->error = "create_database_failed";
			$this->error_msg = $r->getMessage();
			return false;
		}
		
		//database is created, now disconnect and reconnect
		$db->disconnect();
		$db = DB::connect($this->dsn);
		if (DB::isError($db))
		{
			$this->error = "creation_of_database_failed";
			$db->disconnect();
			return false;
		}
		
		//take sql dump an put it in
		$q = file($this->SQL_FILE);
		$q = implode("\n",$q);
		if ($this->execQuery($db,$q)==false)
		{
			$this->error_msg = "dump_error";
			return false;
		}
	    return true;
    }

	/**
	* check if inifile exists
	*/
    function checkIniFileExists()
    {
		$a = file_exists($this->INI_FILE);
		return $a;		
    }
    
	/**
	* check for writable rootdirectory
	* @return boolean
	*/
    function checkRootWritable()
    {
		clearstatcache();
		return is_writable(".");
    }	
	
	/**
	* write the ini file
	*/
    function writeIniFile()
    {		
		//write inifile
		//overwrite with defaults
		$this->getDefaults();
		$this->ini->GROUPS = $this->default;
		
		//no overwrite the defaults with submitted values
		$this->ini->setVariable("db", "host", $this->dbHost);
		$this->ini->setVariable("db", "name", $this->dbName);
		$this->ini->setVariable("db", "user", $this->dbUser);
		$this->ini->setVariable("db", "pass", $this->dbPass);
		
		//try to write the file
		if ($this->ini->write()==false)
		{
			$this->error_msg = "cannot_write";
			return false;
		}
		
		//everything went okay
		return true;
		
	} //function


	/**
	* check for writable langdirectory
	* @return boolean
	*/
	function checkLangWritable()
	{
		clearstatcache();
		return is_writable("./lang");
	}
	
	/**
	* preliminaries
	* 
	* check if different things are ok for setting up ilias
	* @return boolean
	*/
	function preliminaries()
	{
		$a = array();
		$a["root"] = $this->checkRootWritable();
		$a["lang"] = $this->checkLangWritable();
		$a["db"] = $this->checkDatabaseExists();
		
		//return value
		return $a;
	}
	
} //class Setup
?>