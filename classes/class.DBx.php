<?php

//pear DB abstraction layer
require_once ("DB.php");

/**
* Database Wrapper
*
* this class should extend PEAR::DB, add error Management
* in case of a db-error in any database query the DBx-class raises an error
* 
* @author Peter Gabriel <peter@gabriel-online.net>
* 
* @version $Id$
* @package application
* @access public
*/
class DBx extends PEAR
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
	* constructor
	* 
	* set up database conncetion and the errorhandling
	* 
	* @param string dsn database-connection-string for pear-db
	*/
	function DBx($dsn)
	{
		//call parent constructor
		$parent = get_parent_class($this);
		$this->$parent();


		//set up error handling
		$this->error_class = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK, array($this->error_class,'errorHandler'));

		//check dsn
		if ($dsn=="")
			$this->raiseError("no DSN given", $this->error_class->FATAL);

		$this->dsn = $dsn;

		//connect to database 	
		$this->db = DB::connect($this->dsn, true);

		//check error
		if (DB::isError($this->db)) {
			$this->raiseError($this->db->getMessage(), $this->error_class->FATAL);
		}
		
		return true;
	} //end constructor

	/**
	* destructor
	*/
	function _DBx() {
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
			$this->raiseError($r->getMessage()."<br><font size=-1>SQL: ".$sql."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $r;
		}
	} //end function

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
		$r = $this->db->getrow($sql,$mode);
		
		if (DB::isError($r))
		{
			$this->raiseError($r->getMessage()."<br><font size=-1>SQL: ".$sql."</font>", $this->error_class->FATAL);
		}
		else
		{
			return $r;
		}
	} //end function	
} //end Class
?>