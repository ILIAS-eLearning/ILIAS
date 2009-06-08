<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Base class for the interface to an external virus scanner
*
* This class is abstract and needs to be extended for actual scanners
* Only scanFile() and cleanFile() need to be redefined 
* Child Constructors should call ilVirusScanner()
* Scan and Clean are independent and may work on different files
* Logging and message generation are generic
*
* @author	Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id$
* 
*/
class ilVirusScanner
{
	/**
	* type of the virus scanner ("simulate", "sophos", "antivir")
	* should be set in child constructors
	* @var string
	* @access private
	*/
	var $type;
	
	/**
	* can scan zip files (true, false)
	* should be set in child classes
	* @var boolean
	* @access private
	*/
	var $scanZipFiles;

	/**
	* Path of external scanner command
	* @var string 
	* @access private
	*/
	var $scanCommand;

	/**
	* Path of external cleaner command
	* @var string 
	* @access private
	*/
	var $cleanCommand;

	/**
	* path of the scanned file including the name
	* @var string
	* @access private
	*/
	var $scanFilePath;

	/**
	* original name of the scanned file (e.g. if uploaded)
	* @var string 
	* @access private
	*/
	var $scanFileOrigName;

	/**
	* path of the scanned file including the name
	* @var string
	* @access private
	*/
	var $cleanFilePath;

	/**
	* original name of the cleaned file (e.g. if uploaded)
	* @var string
	* @access private
	*/
	var $cleanFileOrigName;

	/**
	* the scanned file is infected
	* @var boolean
	* @access private
	*/
	var $scanFileIsInfected;

	/**
	* the clean file could be cleaned
	* @var boolean
	* @access private
	*/
	var $cleanFileIsCleaned;

	/**
	* ouptput message of external scanner
	* @var string 
	* @access private
	*/
	var $scanResult;
	
	/**
	* ouptput message of external cleaner
	* @var string
	* @access private
	*/
	var $cleanResult;
	
	/**
	* Ilias object
	* @var object 
	* @access private
	*/
	var $ilias;

	/**
	* Language object
	* @var object Language
	* @access private
	*/
	var $lng;

	/**
	* Log object
	* @var object 
	* @access private
	*/
	var $log;

	/**
	* Constructor
	* @access	public
	*/
	function ilVirusScanner($a_scancommand, $a_cleancommand)
	{
		global $ilias, $lng, $log;

		$this->ilias = & $ilias;
		$this->lng = & $lng;
		$this->log = & $log;
		$this->scanCommand = $a_scancommand;
		$this->cleanCommand = $a_cleancommand;
		
		$this->type = "simulate";
		$this->scanZipFiles = false;
	}

	/**
	* scan a file for viruses
	*
	* needs to be redefined in child classes
	* here it simulates a scan
	* "infected.txt" or "cleanable.txt" are expected to be infected
	*
	* @param	string	path of file to scan
	* @param	string	original name of the file to scan
	* @return   string  virus message (empty if not infected)
	* @access	public
	*/
	function scanFile($a_filepath, $a_origname = "")
	{
		// This function needs to be redefined in child classes.
		// It should:
		// - call the external scanner for a_filepath
		// - set scanFilePath to a_filepath
		// - set scanFileOrigName to a_origname
		// - set scanFileIsInfected according the scan result
		// - set scanResult to the scanner output message
		// - call logScanResult() if file is infected
		// - return the output message, if file is infected
		// - return an empty string, if file is not infected
		
		$this->scanFilePath = $a_filepath;
		$this->scanFileOrigName = $a_origname;
		
		if ($a_origname == "infected.txt" or $a_origname == "cleanable.txt")
		{
			$this->scanFileIsInfected = true;
			$this->scanResult =
				"FILE INFECTED: [". $a_filepath. "] (VIRUS: simulated)";
			$this->logScanResult();
			return $this->scanResult;
		}
		else
		{
			$this->scanFileIsInfected = false;
			$this->scanResult = "";
			return "";
		}
	}
	
	
	/**
	* clean an infected file
	*
	* needs to be redefined in child classes
	* here it simulates a clean
	* "cleanable.txt" is expected to be cleanable
	*
	* @param	string	path of file to check
	* @param	string	original name of the file to clean
	* @return   string  clean message (empty if not cleaned)
	* @access	public
	*/
	function cleanFile($a_filepath, $a_origname = "")
	{
		// This function needs to be redefined in child classes
		// It should:
		// - call the external cleaner
		// - set cleanFilePath to a_filepath
		// - set cleanFileOrigName to a_origname
		// - set cleanFileIsCleaned according the clean result
		// - set cleanResult to the cleaner output message
		// - call logCleanResult in any case
		// - return the output message, if file is cleaned
		// - return an empty string, if file is not cleaned

		$this->cleanFilePath = $a_filepath;
		$this->cleanFileOrigName = $a_origname;

		if ($a_origname == "cleanable.txt")
		{
			$this->cleanFileIsCleaned = true;
			$this->cleanResult =
				"FILE CLEANED: [". $a_filepath. "] (VIRUS: simulated)";
			$this->logCleanResult();
			return $this->cleanResult;
		}
		else
		{
			$this->cleanFileIsCleaned = false;
			$this->cleanResult =
				"FILE NOT CLEANED: [". $a_filepath. "] (VIRUS: simulated)";
			$this->logCleanResult();
			return "";
		}
	}
	
	/**
	* returns wether file has been cleaned successfully or not
	*
	* @return	boolean		true, if last clean operation has been successful
	*/
	function fileCleaned()
	{
		return $this->cleanFileIsCleaned;
	}
	
	/**
	* write the result of the last scan to the log
	*
	* @access	public
	*/
	function logScanResult()
	{
		$mess = "Virus Scanner (". $this->type. ")";
		if ($this->scanFileOrigName)
		{
		 	$mess .= " (File " . $this->scanFileOrigName . ")";
		}
		$mess .= ": " . ereg_replace("(\r|\n)+", "; ", $this->scanResult);

		$this->log->write($mess);
	}
	
	/**
	* write the result of the last clean to the log
	*
	* @access	public
	*/
	function logCleanResult()
	{
		$mess = "Virus Cleaner (". $this->type. ")";
		if ($this->cleanFileOrigName)
		{
		 	$mess .= " (File ". $this->cleanFileOrigName. ")";
		}
		$mess .= ": " . ereg_replace("(\r|\n)+", "; ", $this->cleanResult);

		$this->log->write($mess);
	}

	/**
	* get the pure output of the external scan
	*
	* @return   string
	* @access	public
	*/
	function getScanResult()
	{
		return $this->scanResult;
	}
	
	/**
	* get the pure output of the external scan
	*
	* @return   string
	* @access	public
	*/
	function getCleanResult()
	{
		return $this->cleanResult;
	}

	/**
	* get a located message with the result from the last scan
	*
	* @return   string
	* @access	public
	*/
	function getScanMessage()
	{
		if ($this->scanFileIsInfected)
		{
			$ret = sprintf($this->lng->txt("virus_infected"), $this->scanFileOrigName);
		}
		else
		{
			$ret = sprintf($this->lng->txt("virus_not_infected"),$this->scanFileOrigName);
		}
		
		if ($this->scanResult)
		{
			$ret .= " ". $this->lng->txt("virus_scan_message")
				 . "<br />"
				 . str_replace($this->scanFilePath, $this->scanFileOrigName,
								nl2br($this->scanResult));
		}
		return $ret;
	}

	/**
	* get a located message with the result from the last clean
	*
	* @return   string
	* @access	public
	*/
	function getCleanMessage()
	{
		if ($this->cleanFileIsCleaned)
		{
			$ret = sprintf($this->lng->txt("virus_cleaned"), $this->cleanFileOrigName);
		}
		else
		{
			$ret = sprintf($this->lng->txt("virus_not_cleaned"),$this->cleanFileOrigName);
		}

		if ($this->cleanResult)
		{
			$ret .= " ". $this->lng->txt("virus_clean_message")
				 . "<br />"
				 . str_replace($this->cleanFilePath, $this->cleanFileOrigName,
								nl2br($this->cleanResult));
		}
		return $ret;
	}
	
	/**
	* get info if class can scan ZIP files
	*
	* @return   boolean
	* @access	public
	*/
	function getScanZipFiles()
	{
		return $this->scanZipFiles;
	}
}
?>