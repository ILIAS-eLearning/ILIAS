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
* @version	$Id: class.ilLog.php 16024 2008-02-19 13:07:07Z akill $
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
	* Log level 10: Log only fatal errors that could lead to serious problems
	* @var		integer
	* @access	public
	*/
	var $FATAL;

	/**
	* Log level 20: This is the standard log level that is set if no level is given
	* @var		integer
	* @access	public
	*/
	var $WARNING;

	/**
	* Log level 30: Logs messages and notices that are less important for system functionality like not translated language values
	* @var		integer
	* @access	public
	*/
	var $MESSAGE;

	/**
	* constructor
	* 
	* set the filename
	* 
	* @param	string
	* @return	boolean
	* @access	public
	*/
	function ilLog($a_log_path, $a_log_file, $a_tag = "", $a_enabled = true, $a_log_level = NULL)
	{
		// init vars
		$this->FATAL	 = 10;
		$this->WARNING	 = 20;
		$this->MESSAGE	 = 30;
  
        $this->default_log_level= $this->WARNING;
        $this->current_log_level = $this->setLogLevel($a_log_level);

		$this->path = ($a_log_path) ? $a_log_path : ILIAS_ABSOLUTE_PATH;
		$this->filename = ($a_log_file) ? $a_log_file : "ilias.log";
		$this->tag = ($a_tag == "") ? "unknown" : $a_tag;
		$this->enabled = (bool) $a_enabled;
		$this->setLogFormat(@date("[y-m-d H:i:s] ")."[".$this->tag."] ");
		
		$this->open();

	}
 
    /**
    * set global log level
    *
    * @access    private
    * @param     integer   log level
    * @return    integer   log level
    */
    function setLogLevel($a_log_level)
    {
        switch (strtolower($a_log_level))
        {
            case "fatal":
                return $this->FATAL;
            case "warning":
                return $this->WARNING;
            case "message":
                return $this->MESSAGE;
            default:
                return $this->default_log_level;
        }
    }

    /**
    * determine log level
    *
    * @access    private
    * @param     integer   log level
    * @return    integer   checked log level
    */
    function checkLogLevel($a_log_level)
    {
        if (empty($a_log_level))
            return $this->default_log_level;

        $level = (int) $a_log_level;
        
        if ($a_log_level != (int) $a_log_level)
            return $this->default_log_level;
        
        return $level;
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
	* if there isn't a log entry for the topic
	* 
	* @param	string
	* @access	public
	*/
	function writeLanguageLog($a_topic,$a_lang_key)
	{
		//TODO: go through logfile and search for the topic
		//only write the log if the error wasn't reported yet
		$this->write("Language (".$a_lang_key."): topic -".$a_topic."- not present",$this->MESSAGE);
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
    * this function is automatically called by class.ilErrorHandler in case of an error
    * To log manually please use $this::write
    * @access   public
    * @param    integer  error level code from PEAR_Error
    * @param    string   error message
    * @see      this::write
    */
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
	* this method logs anything you want. It appends a line to the given logfile.
	* Datetime and client id is appended automatically
	* You may set the log level in each call. Leave blank to use default log level
    * specified in ilias.ini:
    * [log]
    * level = "<level>" possible values are fatal,warning,message
    *
    * @access   public
    * @param    string   error message
    * @param    integer  log level (optional)
	*/
	function write($a_msg, $a_log_level = NULL)
	{
		if ($this->enabled and $this->current_log_level >= $this->checkLogLevel($a_log_level))
		{
			$this->open();
			
			if ($this->fp == false)
			{
				//die("Logfile: cannot open file. Please give Logfile Writepermissions.");
			}

			if (fwrite($this->fp,$this->getLogFormat().$a_msg."\n") == -1)
			{
				//die("Logfile: cannot write to file. Please give Logfile Writepermissions.");
			}
			
			// note: logStack() calls write() again, so do not make this call
			// if no log level is given
			if ($a_log_level == $this->FATAL)
			{
				$this->logStack();
			}
		}
	}
	
	public function logStack($a_message = '')
	{
 		try
 		{
 			throw new Exception($a_message);
 		}
 		catch(Exception $e)
 		{
	 		$this->write($e->getTraceAsString());
 		}
	}
	
	
	private function open()
	{
		if(!$this->fp)
		{
		    $this->fp = @fopen ($this->path."/".$this->filename, "a");
		}
	}
	
	public function __destruct()
	{
		@fclose($this->fp);
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
