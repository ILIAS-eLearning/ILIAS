<?php

/**
* logging
*
* this class provides a logging feature to the application
* this class is easy to use.
* call the constructor with e.g.
* $log = new Log();
* you can give a filename if you want, else the defaultfilename is used.
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* @package application
*/
class Log
{

	/**
	 * logfile
	 * @var string
	 * @access private
	 */
	var $LOGFILE = "application.log";
	
	/**
	 * constructor
	 * 
	 * set the filename
	 * 
	 * @return boolean 
	 * @access public
	 * @author Peter Gabriel <pgabriel@databay.de>
	 * @version 1.0
	 */
	function Log($logfile = "")
	{
		if ($logfile=="")
			$this->filename = $this->LOGFILE;
		else
			$this->filename = $logfile;
	
		//TODO: check logfile accessable, creatable, writable and so on
		return true;
	}

	/**
	* logging 
	* 
	* this method logs anything you want. It appends a line to the given logfile:
	* date: message
	*/
	function write($msg)
	{
		$fp = fopen ($this->filename, "a");
		fwrite($fp,date("[y-m-d H:i] ").$msg."\n");
		fclose($fp);
	}

} //class
?>