<?php
// include pear
include_once("DB.php");

/**
* Database Update class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* @package ilias-core
*/
class DBUpdate
{

	/**
	 * db update file
	 */
	var $DB_UPDATE_FILE = "./sql/dbupdate.php";

	/**
	 * constructor
	 */
	function DBUpdate()
	{
	    global $ilias;
		$this->db = $ilias->db;	
   
		return $this->readDBUpdateFile();
	}
	
    /**
	 * destructor
	 * 
	 * @param void
	 * @return boolean
	 */
function _DBUpdate()
{
	$this->db->disconnect();
}


function readDBUpdateFile()
{
	if (!file_exists($this->DB_UPDATE_FILE))
	{
		$this->error = "no_db_update_file";
		$this->filecontent = array();
		return false;
	}
	
	$this->filecontent = @file($this->DB_UPDATE_FILE);
	return true;
}

function getCurrentVersion()
{
	global $ilias;

	//read settingskey from settingstable
	$this->currentVersion = $ilias->readSettingsInt("db_version");

	return $this->currentVersion;
}

function getFileVersion()
{
	//go through filecontent and search for last occurence of <#xxx>
	reset($this->filecontent);
	$regs = array();
	foreach ($this->filecontent as $row)
	{
		if (ereg("^<#([0-9]+)>", $row, $regs))
		{
			$version = $regs[1];
		}
	}

	$this->fileVersion = $version;
	return $this->fileVersion; 
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


function applyUpdate($nr)
{
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


} //class
?>