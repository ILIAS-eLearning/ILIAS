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


require_once(dirname(__FILE__)."/class.ilErrorHandling.php");
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
class ilLog extends PEAR
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
	function ilLog($logfile = "")
	{
		if ($logfile=="")
			$this->filename = $this->LOGFILE;
		else
			$this->filename = $logfile;

		$this->PEAR();
		$this->error_class = new ilErrorHandling();
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
	* @param	string
	* @access	public
	*/
	function writeLanguageLog($topic)
	{
		//TODO: go through logfile and search for the topic
		//only write the log if the error wasn't reported yet
		$this->write("Language: "."topic -".$topic."- not present");
	}

	/**
	* special warning message
	* 
	* @param	string
	* @access	public
	*/
	function writeWarning($a_message)
	{
		$this->write("WARNING: ".$a_message);
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
