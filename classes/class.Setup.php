<?php
/**
* Setup class
*
* class to setup ILIAS first and maintain the ini-settings and the database
*
* @author Peter Gabriel <pgabriel@databay.de>
* @package ILIAS
* @access public
* @version $Id$
*/

include_once("./classes/class.IniFile.php");
include_once("./classes/class.util.php");
include_once("DB.php");

class Setup
{
	var $INI_FILE = "./ilias.ini";
	var $SQL_FILE = "./sql/ilias3.sql";

    /**
    *  database connector
    *  @var string
    *  @access public
	*/
    var $dsn = "";

    /**
    *  database handle
    *  @var object
    *  @access private
    */
    var $db = "";
	
    /**
	 *  ini-object
	 *  @var object
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
	* @param void
    * @return boolean
    */
    function Setup()
    {
		//initialize default values in case of error
		$default["db"]["type"] = "mysql";
		$default["db"]["host"] = "your host";
		$default["db"]["user"] = "your db username";
		$default["db"]["pass"] = "";
		$default["db"]["name"] = "your db name (i.e. ilias3)";

		$default["session"]["save_db"] = "true";

		$default["auth"]["table"] = "user_data";
		$default["auth"]["usercol"] = "login";
		$default["auth"]["passcol"] = "passwd";

		$default["language"]["default"] = "en";

		$default["error"]["debug"] = "false";
		$default["error"]["haltonerror"] = "false";

		$default["layout"]["TABLE_BGCOLOR"] = "#DCDCFF";
		$default["layout"]["TABLE_BORDER"] = "1";
		$default["layout"]["TABLE_CELLSPACING"] = "0";
		$default["layout"]["TABLE_CELLPADDING"] = "5";

		// get settings from ini file
		$this->ini = new IniFile($this->INI_FILE);
		//check for error
		if ($this->ini->ERROR != "")
		{
			$this->error = $this->ini->ERROR;
		}
		
		if ($this->error == "file_does_not_exist")
		{
			//try to write
			$this->ini->setContent($default);
			if ($this->ini->save() == false)
				$this->error = $this->ini->ERROR;
		}

		$this->setDbType($this->ini->readVariable("db","type"));
		$this->setDbHost($this->ini->readVariable("db","host"));
		$this->setDbName($this->ini->readVariable("db","name"));
		$this->setDbUser($this->ini->readVariable("db","user"));
		$this->setDbPass($this->ini->readVariable("db","pass"));

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

		// set tplPath
		$this->tplPath = TUtil::setPathStr($this->ini->readVariable("server","tpl_path"));

		return true;
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
			 "/".$this->dbname;
		 
		 $this->db = DB::connect($this->dsn,true);

		 if (DB::isError($this->db)) {
			 $this->error_msg = $this->db->getMessage();
			 $this->error = "not_connected_to_db";
		 }
	 }

    /**
    * destructor
	* 
	* @param void
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
	 */
	function setDbType($str)
	{
		$this->dbType = $str;
	}
	
	/**
	 * set the host
	 */
	function setDbHost($str)
	{
		$this->dbHost = $str;
	}

	/**
	 * set the name of database
	 */
	function setDbName($str)
	{
		$this->dbName = $str;
	}

	/**
	 * set the user
	 */
	function setDbUser($str)
	{
		$this->dbUser = $str;
	}

	/**
	 * set the password
	 */
	function setDbPass($str)
	{
		$this->dbPass = $str;
	}

    /**
	 * execute a query
	 * @param string $str query
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
		
        //connect to databasehost
		$dsn = $this->dbType."://".$this->dbUser.":".$this->dbPass."@".$this->dbHost;
		
		$db = DB::connect($dsn);

		if (DB::isError($db))
		{
			$this->error_msg = $db->getMessage();
			$this->error = "data_invalid";
			return false;
		}
		$db->disconnect();

		//try to connect to database
		$db = DB::connect($dsn."/".$this->dbName);
		if (DB::isError($db)==false)
		{
			$this->error = "database_exists";
			$db->disconnect();
			return false;
		}
		
		//create database
		$db = DB::connect($dsn);
		if (DB::isError($db))
		{
			$this->error_msg = $this->db->getMessage();
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
		$db = DB::connect($dsn."/".$this->dbName);
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
		
		//database things were okay - now change inifile
		$this->ini->setVariable("db", "host", $this->dbHost);
		$this->ini->setVariable("db", "name", $this->dbName);
		$this->ini->setVariable("db", "user", $this->dbUser);
		$this->ini->setVariable("db", "pass", $this->dbPass);
		if ($this->ini->save()==false)
		{
			$this->error_msg = "cannot_write";
			return false;
		}
		
		//everything went okay
		return true;
		
	} //function
	
} //class Setup
?>