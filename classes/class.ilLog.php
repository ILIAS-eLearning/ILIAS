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

/**
* logging
*
* this class provides a logging feature to the application
* this class is easy to use.
* call the constructor with e.g.
* $log = new Log();
* you can give a filename if you want, else the defaultfilename is used.
*
* @author	Peter Gabriel <pgabriel@databay.de>
* @version	$Id$
* @package	application
*/

class ilLog
{

	/**
	* logfile
	* @var		string
	* @access	private
	*/
	var $path;
	var $filename;
	var $tag;
	var $log_format;

	/**
	* constructor
	* 
	* set the filename
	* 
	* @param	string
	* @return	boolean
	* @access	public
	*/
	function ilLog($a_log_path, $a_log_file, $a_tag = "", $a_enabled = true)
	{
		$this->path = ($a_log_path) ? $a_log_path : ILIAS_ABSOLUTE_PATH;
		$this->filename = ($a_log_file) ? $a_log_file : "ilias.log";
		$this->tag = ($a_tag == "") ? "unknown" : $a_tag;
		$this->enabled = (bool) $a_enabled;
		$this->setLogFormat(date("[y-m-d H:i] ")."[".$this->tag."] ");

	}
	
	function setLogFormat($a_format)
	{
		$this->log_format = $a_format;
	}
	
	function getLogFormat()
	{
		return $this->log_format;
	}

	function setPath($a_str)
	{
		$this->path = $a_str;
	}

	function setFilename($a_str)
	{
		$this->filename = $a_str;
	}

	function setTag($a_str)
	{
		$this->tag = $a_str;
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
	function writeLanguageLog($a_topic,$a_lang_key)
	{
		//TODO: go through logfile and search for the topic
		//only write the log if the error wasn't reported yet
		$this->write("Language (".$a_lang_key."): topic -".$a_topic."- not present");
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
	
	function logError($a_code,$a_msg)
	{
		switch ($a_code)
		{
			case "3":
				return; // don't log messages
				$error_level = "message";
				break;

			case "2":
				$error_level = "warning";
				break;

			case "1":
				$error_level = "fatal";
				break;
				
			default:
				$error_level = "unknown";
				break;
		}
		
		$this->write("ERROR (".$error_level."): ".$a_msg);	
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
		if ($this->enabled)
		{
			$fp = @fopen ($this->path."/".$this->filename, "a");

			if ($fp == false)
			{
				die("Logfile: cannot open file. Please give Logfile Writepermissions.");
			}
	//var_dump($this->getLogFormat());exit;
			if (fwrite($fp,$this->getLogFormat().$msg."\n") == -1)
			{
				die("Logfile: cannot write to file. Please give Logfile Writepermissions.");
			}

			fclose($fp);
		}
	}

	/**
	* delete logfile
	*/
	function delete()
	{
		if (@is_file($this->path."/".$this->filename))
		{
			@unlink($this->path."/".$this->filename);
		}
	}
} // END class.ilLog
?>
