<?php
// include pear
include_once("DB.php");

/**
* Database Update class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* @package application
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
   
		$this->readDBUpdateFile();
		$this->getFileVersion();
		$this->getCurrentVersion();
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
	$this->currentVersion = $ilias->getSettingsInt("db_version");

	return $this->currentVersion;
}

function getFileVersion()
{
	//go through filecontent and search for last occurence of <#x>
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
				if (DB::isError($r))
				{
					$this->error = $r->getMessage();
					return false;
				}
				unset($q);
			} //if
			else
			{
				$q .= " ".$sql[$i];
			} //else
		} //if
	} //for
	if ($q != "")
		echo "incomplete_statement: ".$q."<br>";
	return true;
}

function applyUpdate()
{
	$f = $this->fileVersion;
	$c = $this->currentVersion;

	if ($c < $f)
	{
		$msg = array();
		for ($i=($c+1); $i<=$f; $i++)
		{
			if ($this->applyUpdateNr($i)==false)
			{
				$msg[] = array(
					"msg" => "update_error: ".$this->error,
					"nr" => $i
				);
				$this->updateMsg = $msg;
				return false;
			}
			else
			{
				$msg[] = array(
					"msg" => "update_applied",
					"nr" => $i
				);
			}
		}
		$this->updateMsg = $msg;
	}
	else
	{
		$this->updateMsg = "no_changes";
	}
	return true;
}

/**
 * apply an update
 * @param int nr number what patch to apply
 * @return bool
 * @access private
 */
function applyUpdateNr($nr)
{
	global $ilias;

	//search for desired $nr
	reset($this->filecontent);
	//init
	$i = 0;
    //go through filecontent
	while (!ereg("^<#".$nr.">", $this->filecontent[$i]) && $i<count($this->filecontent))
	{
		$i++;
	}

	//update not found
	if ($i == count($this->filecontent))
	{
		$this->error = "update_not_found";
		return false;
	}

	$i++;
	//update found, now extract this update to a new array
	$update = array();
	while ($i<count($this->filecontent) && !ereg("^<#".($nr+1).">", $this->filecontent[$i]))
	{
		$update[] = trim($this->filecontent[$i]);
		$i++;
	}

	//now you have the update, now process it
	$sql = array();
	$php = array();
	$mode = "sql";
	foreach ($update as $row)
	{
		if (ereg("<\?php", $row))
		{
			if (count($sql)>0)
			{
				if ($this->execQuery($this->db, implode("\n", $sql))==false)
				{	
					$this->error = "dump_error: ".$this->error;
					return false;
				}
				$sql = array();
			}
			$mode = "php";
		}
		elseif (ereg("\?>", $row))
		{
			if (count($php)>0)
			{
				eval(implode("\n", $php));
				$php = array();
			}
			$mode = "sql";

		}
		else
		{
			if ($mode == "sql")
			{
				$sql[] = $row;
			}

			if ($mode == "php")
			{
				$php[] = $row;
			}
		} //else
	} //foreach

	if ($mode=="sql" && count($sql)>0)
	{
		if ($this->execQuery($this->db, implode("\n", $sql))==false)
		{  
			$this->error = "dump_error: ".$this->error;
			return false;
		}
	}

	//increase db_Version number
	$ilias->setSettingsInt("db_version", $nr);
	$this->currentVersion = $ilias->getSettingsInt("db_version");
	
	return true;
	
}

function getDBVersionStatus()
{
	if ($this->fileVersion > $this->currentVersion)
		return "database_needs_update";
	else
		return "database_is_uptodate";
}

} //class
?>