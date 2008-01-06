<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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



//pear DB abstraction layer
//require_once ("DB.php");
require_once ("MDB2.php");

/**
* Database Wrapper
*
* this class should extend PEAR::DB, add error Management
* in case of a db-error in any database query the ilDBx-class raises an error
*
* @author Peter Gabriel <peter@gabriel-online.net>
*
* @version $Id$
* @access public
*/

class ilDBx extends PEAR
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

	/**
	* myqsl max_allowed_packet size
	* @var int
	*/
	var $max_allowed_packet_size;


	/**
	* constructor
	*
	* set up database conncetion and the errorhandling
	*
	* @param string dsn database-connection-string for pear-db
	*/
	function ilDBx($dsn)
	{
		//call parent constructor
		$parent = get_parent_class($this);
		$this->$parent();

		//set up error handling
		$this->error_class = new ilErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK, array($this->error_class,'errorHandler'));

		//check dsn
		if ($dsn=="")
			$this->raiseError("no DSN given", $this->error_class->FATAL);

		$this->dsn = $dsn;

		//connect to database
		//$this->db = DB::connect($this->dsn, true);
		$this->db = MDB2::connect($this->dsn);
		
		// set empty value portability to PEAR::DB behaviour
		$cur = ($this->db->getOption("portability") & MDB2_PORTABILITY_EMPTY_TO_NULL);
		$this->db->setOption("portability", $this->db->getOption("portability") - $cur);

		//check error
		if (DB::isError($this->db)) {
			$this->raiseError($this->db->getMessage(), $this->error_class->FATAL);
		}

		// SET 'max_allowed_packet' (only possible for mysql version 4)
		$this->setMaxAllowedPacket();
		
		// NOTE: Three sourcecodes use this or a similar handling:
		// - classes/class.ilDBx.php
		// - calendar/classes/class.ilCalInterface.php->setNames
		// - setup/classes/class.ilClient.php
		if ($this->isMysql4_1OrHigher())
		{
			$this->query("SET NAMES utf8");
			$this->query("SET SESSION SQL_MODE = ''");
		}

		return true;
	} //end constructor

	/**
	* destructor
	*/
	function _ilDBx(){
		//$this->db->disconnect();
	} //end destructor

	/**
	* disconnect from database
	*/
	function disconnect()
	{
//		$this->db->disconnect();
	}

	/**
	* query 
	* 
	* this is the wrapper itself. query a string, and return the resultobject,
	* or in case of an error, jump to errorpage
	* 
	* @param string
	* @return object DB
	*/
	function query($sql)
	{
		$r = $this->db->query($sql);

		if (DB::isError($r))
		{
			$err = "<br>Details: ".mysql_error();
			$this->raiseError($r->getMessage()."<br><font size=-1>SQL: ".$sql.$err."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $r;
		}
	} //end function


	/**
	* getOne 
	* 
	* this is the wrapper itself. Runs a query and returns the first column of the first row
	* or in case of an error, jump to errorpage
	* 
	* @param string
	* @return object DB
	*/
	function getOne($sql)
	{
		$r = $this->db->getOne($sql);

		if (DB::isError($r))
		{
			$this->raiseError($r->getMessage()."<br><font size=-1>SQL: ".$sql."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $r;
		}
	} //end function


	/**
	* wrapper for quote method
	*/
	function quote($a_query, $null_as_empty_string = true)
	{
		if ($null_as_empty_string)
		{
			// second test against 0 is crucial for MDB2
			//if ($a_query == "" && $a_query !== 0)
			if ($a_query == "")
			{
				$a_query = "";
			}
		}

		if (method_exists($this->db, "quoteSmart"))
		{
			return $this->db->quoteSmart($a_query);
		}
		else
		{
			return $this->db->quote($a_query);
		}
	}
	
	/**
	* Wrapper to find number of rows affected by a data changing query
	*
	* @return integer  number of rows
	*/
	function affectedRows()
	{
		return $this->db->affectedRows();
	}

	/**
	* getrow
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

		if (DB::isError($r))
		{
			$this->raiseError($r->getMessage()."<br><font size=-1>SQL: ".$sql."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $r;
		}
	} //end function


	/**
	* get last insert id
	*/
	function getLastInsertId()
	{
		$r = $this->query("SELECT LAST_INSERT_ID()");
		$row = $r->fetchRow();

		return $row[0] ? $row[0] : false;
	}

	/**
	* Wrapper for Pear prepare
	* @param String query
	* @return resource
	*/
	function prepare($query)
	{
		return $this->db->prepare($query);
	}

	/**
	* Wrapper for Pear executeMultiple
	* @param resource (statement from prepare)
	* @param array multidim array of data
	* @return mixed a new DB_result/DB_OK  or a DB_Error, if fail
	*/
	function executeMultiple($stmt,$data)
	{
		$res = $this->db->executeMultiple($stmt,$data);

		if (DB::isError($res))
		{
			$this->raiseError($res->getMessage()."<br><font size=-1>SQL: ".$data."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $res;
		}
	}

	/**
	* Wrapper for Pear executeMultiple
	* @param resource (statement from prepare)
	* @param array multidim array of data
	* @return mixed a new DB_result/DB_OK  or a DB_Error, if fail
	*/
	function execute($stmt,$data)
	{
		$res = $this->db->execute($stmt,$data);

		if (DB::isError($res))
		{
			$this->raiseError($res->getMessage()."<br><font size=-1>SQL: ".$data."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $res;
		}
	}

	/**
	* Wrapper for Pear autoExecute
	* @param string tablename
	* @param array fields values
	* @param int DB_AUTOQUERY_INSERT or DB_AUTOQUERY_UPDATE
	* @param string where condition (e.g. "obj_id = '7' AND ref_id = '5'")
	* @return mixed a new DB_result/DB_OK  or a DB_Error, if fail
	*/
	function autoExecute($a_tablename,$a_fields,$a_mode = DB_AUTOQUERY_INSERT,$a_where = false)
	{
		$this->db->loadModule('Extended');
		$res = $this->db->autoExecute($a_tablename,$a_fields,$a_mode,$a_where);

		if (DB::isError($res))
		{
			$this->raiseError($res->getMessage()."<br><font size=-1>SQL: ".$data."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $res;
		}
	}
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

	


	// PRIVATE
	function setMaxAllowedPacket()
	{

		// GET MYSQL VERSION
		$query = "SHOW VARIABLES LIKE 'version'";
		$res = $this->db->query($query);
		if(DB::isError($res))
		{
			$this->raiseError($res->getMessage()."<br><font size=-1>SQL: ".$query."</font>", $this->error_class->FATAL);
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$version = $row->Value;
		}

		// CHANG VALUE IF MYSQL VERSION > 4.0
		if(substr($version,0,1) == "4")
		{
			ini_get("post_max_size");
			$query = "SET GLOBAL max_allowed_packet = ".(int) ini_get("post_max_size") * 1024 * 1024;
			$this->db->query($query);
			if(DB::isError($res))
			{
				$this->raiseError($res->getMessage()."<br><font size=-1>SQL: ".$query."</font>", $this->error_class->FATAL);
			}
		}
		// STORE NEW max_size in member variable
		$query = "SHOW VARIABLES LIKE 'max_allowed_packet'";
		if(DB::isError($res))
		{
			$this->raiseError($res->getMessage()."<br><font size=-1>SQL: ".$query."</font>", $this->error_class->FATAL);
		}
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->max_allowed_packet_size = $row->Value;
		}
		#var_dump("<pre>",$this->max_allowed_packet_size,"<pre>");
		return true;
	}

	/**
	* Lock existing table
	* @param array (tablename => lock type READ, WRITE, READ LOCAL or LOW_PRIORITY) e.g array('tree' => 'WRITE')
	* @return boolean
	*/
	function _lockTables($a_table_params)
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
	function _unlockTables()
	{
		global $ilDB;
		
		$ilDB->query('UNLOCK TABLES');

		return true;
	}
	
	/**
	* get mysql version
	*/
	function getMySQLVersion()
	{
		return mysql_get_server_info();
	}
	
	/**
	* check wether current MySQL server is version 4.1.x
	*/
	function isMysql4_1()
	{
		$version = explode(".", $this->getMysqlVersion());
		if ($version[0] == "4" && $version[1] == "1")
		{
			return true;
		}
		
		return false;
	}

	/**
	* check wether current MySQL server is version 4.1.x or higher
	*
	* NOTE: Three sourcecodes use this or a similar handling:
	* - classes/class.ilDBx.php
	* - calendar/classes/class.ilCalInterface.php->setNames
	* - setup/classes/class.ilClient.php
	*/
	function isMysql4_1OrHigher()
	{
		$version = explode(".", $this->getMysqlVersion());
		if ((int)$version[0] >= 5 ||
			((int)$version[0] == 4 && (int)$version[1] >= 1))
		{
			return true;
		}
		
		return false;
	}

	/**
	* check wether current MySQL server is version 4.0.x or higher
	*/
	function isMysql4_0OrHigher()
	{
		$version = explode(".", $this->getMysqlVersion());
		if((int) $version[0] >= 4)
		{
			return true;
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
		$column_visibility = FALSE;
		$query = "SHOW COLUMNS FROM `$a_table`";
		$res = $this->db->query($query);
		if ($res->numRows())
		{
			while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (strcmp($data["Field"], $a_column_name) == 0)
				{
					$column_visibility = TRUE;
				}
			}
		}
		return $column_visibility;
	}

} //end Class
?>
