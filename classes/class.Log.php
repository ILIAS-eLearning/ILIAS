<?php
require_once(dirname(__FILE__)."/class.ErrorHandling.php");
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
class Log extends PEAR
{

	/**
	 * logfile
	 * @var string
	 * @access private
	 */
	var $LOGFILE = "application.log";

	/**
	* error handling
	*/
	var $error_class;	
	/**
	 * constructor
	 * 
	 * set the filename
	 * 
	 * @param string
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

		$this->PEAR();
		$this->error_class = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK, array($this->error_class,'errorHandler'));
	
		//TODO: check logfile accessable, creatable, writable and so on
		return true;
	}

	
	/**
	* special language checking routine
	* 
	* only add a log entry to the logfile
	* if there isn't a logentry for the topic
	* 
	* @param string
	* @access public
	*/
	function writeLanguageLog($topic)
	{
		//TODO: go through logfile and search for the topic
		//only write the log if the error wasn't reported yet
		$this->write("Language: "."topic -".$topic."- not present");
	}
	
	/**
	* logging 
	* 
	* this method logs anything you want. It appends a line to the given logfile:
	* date: message
	*
	* @param string
	* @access public
	*/
	function write($msg)
	{
		$fp = @fopen ($this->filename, "a");
		if ($fp == false)
		{
			$this->raiseError("Logfile: cannot open file. Please give Logfile Writepermissions.",$this->error_class->WARNING);
		}
		if (fwrite($fp,date("[y-m-d H:i] ").$msg."\n") == -1)
		{
			$this->raiseError("Logfile: cannot write to file. Please give Logfile Writepermissions.",$this->error_class->WARNING);
		}
		fclose($fp);
	}

} //class
?>