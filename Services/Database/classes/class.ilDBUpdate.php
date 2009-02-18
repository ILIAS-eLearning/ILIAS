<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Database Update class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesDatabase
*/
class ilDBUpdate
{
	/**
	* db update file
	*/
	var $DB_UPDATE_FILE;

	/**
	* current version of db
	* @var	integer	db version number
	*/
	var $currentVersion;

	/**
	* current version of file
	* @var	integer	fiel version number
	*/
	var $fileVersion;

	/**
	* constructor
	*/
	function ilDBUpdate($a_db_handler = 0,$tmp_flag = false)
	{		
		// workaround to allow setup migration
		if ($a_db_handler)
		{
			$this->db =& $a_db_handler;
			
			if ($tmp_flag)
			{
				$this->PATH = "./";
			}
			else
			{
				$this->PATH = "./";
				//$this->PATH = "../";
			}
		}
		else
		{
			global $mySetup;
			$this->db = $mySetup->db;
			$this->PATH = "./";
		}
		
		$this->getCurrentVersion();
		
		// get update file for current version
		$updatefile = $this->getFileForStep($this->currentVersion + 1);

		$this->current_file = $updatefile;
		$this->DB_UPDATE_FILE = $this->PATH."setup/sql/".$updatefile;
		
		//
		// NOTE: IF YOU SET THIS TO THE NEWEST FILE, CHANGE ALSO getFileForStep()
		//
		$this->LAST_UPDATE_FILE = $this->PATH."setup/sql/dbupdate_02.php";
   
		$this->readDBUpdateFile();
		$this->readLastUpdateFile();
		$this->readFileVersion();
	}
	
	/**
	* Get db update file name for db step
	*/
	function getFileForStep($a_version)
	{
		//
		// NOTE: IF YOU ADD A NEW FILE HERE, CHANGE ALSO THE CONSTRUCTOR
		//
		if ((int)$a_version > 864)		// last number in previous file
		{
			return "dbupdate_02.php";
		}
		else
		{
			return "dbupdate.php";
		}
	}
	
    /**
	* destructor
	* 
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

	function readLastUpdateFile()
	{
		if (!file_exists($this->LAST_UPDATE_FILE))
		{
			$this->error = "no_last_update_file";
			$this->lastfilecontent = array();
			return false;
		}
		
		$this->lastfilecontent = @file($this->LAST_UPDATE_FILE);
		return true;
	}

	function getCurrentVersion()
	{
		$GLOBALS["ilDB"] = $this->db;
		include_once './Services/Administration/classes/class.ilSetting.php';
		$set = new ilSetting();
		$this->currentVersion = (integer) $set->get("db_version");

		return $this->currentVersion;
	}

	function setCurrentVersion ($a_version)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$set = new ilSetting();
		$set->set("db_version", $a_version);
		$this->currentVersion = $a_version;
		
		return true;
	}

	function readFileVersion()
	{
		//go through filecontent and search for last occurence of <#x>
		reset($this->lastfilecontent);
		$regs = array();
		foreach ($this->lastfilecontent as $row)
		{
			if (ereg("^<#([0-9]+)>", $row, $regs))
			{
				$version = $regs[1];
			}
		}

		$this->fileVersion = (integer) $version;
		return $this->fileVersion; 
	}
	
	/**
	* Get Version of file
	*/
	function getFileVersion()
	{
		return $this->fileVersion;
	}
	
	/**
	* execute a query
	* @param	object	DB 
	* @param	string	query
	* @return	boolean
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
					$check = $this->checkQuery($q);
					if ($check === true)
					{
						$r = $db->query($q);
						if (MDB2::isError($r))
						{
							$this->error = $r->getMessage();
							return false;
						}
					}
					else
					{
						$this->error = $check;
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
		{
			echo "incomplete_statement: ".$q."<br>";
			return false;
		}
		return true;
	}

	/**
	* check query
	*/
	function checkQuery($q)
	{
		return true;
	}
	
	/**
	* Apply update
	*/
	function applyUpdate()
	{
		global $ilCtrlStructureReader, $ilMySQLAbstraction;
		
		include_once './Services/Database/classes/class.ilMySQLAbstraction.php';

		$ilMySQLAbstraction = new ilMySQLAbstraction();
		$GLOBALS['ilMySQLAbstraction'] = $ilMySQLAbstraction;
		
		$f = $this->fileVersion;
		$c = $this->currentVersion;

		if ($c < $f)
		{
			$msg = array();
			for ($i=($c+1); $i<=$f; $i++)
			{
				// check wether next update file must be loaded
				if ($this->current_file != $this->getFileForStep($i))
				{
					$this->DB_UPDATE_FILE = $this->PATH."setup/sql/".$this->getFileForStep($i);
					$this->readDBUpdateFile();
				}
				
				if ($this->applyUpdateNr($i) == false)
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

		return $this->loadXMLInfo();
	}
	
	function loadXMLInfo()
	{
		global $ilCtrlStructureReader;
		
		// read module and service information into db
		require_once "./setup/classes/class.ilModuleReader.php";
		require_once "./setup/classes/class.ilServiceReader.php";
		require_once "./setup/classes/class.ilCtrlStructureReader.php";

		chdir("..");
		require_once "./Services/Component/classes/class.ilModule.php";
		require_once "./Services/Component/classes/class.ilService.php";
		$modules = ilModule::getAvailableCoreModules();
		$services = ilService::getAvailableCoreServices();
		chdir("./setup");

		ilModuleReader::clearTables();
		foreach($modules as $module)
		{
			$mr = new ilModuleReader(ILIAS_ABSOLUTE_PATH."/Modules/".$module["subdir"]."/module.xml",
				$module["subdir"], "Modules");
			$mr->getModules();
			unset($mr);
		}

		ilServiceReader::clearTables();
		foreach($services as $service)
		{
			$sr = new ilServiceReader(ILIAS_ABSOLUTE_PATH."/Services/".$service["subdir"]."/service.xml",
				$service["subdir"], "Services");
			$sr->getServices();
			unset($sr);
		}
		
		$ilCtrlStructureReader->readStructure();

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
		global $ilDB,$ilErr,$ilUser,$ilCtrlStructureReader,$ilModuleReader,$ilMySQLAbstraction;

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
					if ($this->execQuery($this->db, implode("\n", $sql)) == false)
					{
						$this->error = $this->error;
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

		if ($mode == "sql" && count($sql) > 0)
		{
			if ($this->execQuery($this->db, implode("\n", $sql)) == false)
			{
				$this->error = "dump_error: ".$this->error;
				return false;
			}
		}
	
		//increase db_Version number
		$this->setCurrentVersion($nr);
		//$this->currentVersion = $ilias->getSetting("db_version");
		
		return true;
		
	}
	
	function getDBVersionStatus()
	{
		if ($this->fileVersion > $this->currentVersion)
			return false;
		else
			return true;
	}
	
	function getTables()
	{
		$a = array();
	
		$query = "SHOW TABLES";	
		$res = $this->db->query($query);
		while ($row = $res->fetchRow())
		{
			$status = $this->getTableStatus($row[0]);
			$a[] = array(
				"name" => $status["Table"],
				"table" => $row[0],
				"status" => $status["Msg_text"]
			);
		}
		return $a;
	}
	
	function getTableStatus($table)
	{
		$a = array();
	
		$query = "ANALYZE TABLE ".$table;	
		$res = $this->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row;
	}
	
	function optimizeTables($tables)
	{
		$msg = array();
		foreach ($_POST["tables"] as $key => $value)
		{
			$query = "OPTIMIZE TABLE ".$key;	
			$res = $this->db->query($query);
			$msg[] = "table $key: ok";
		}	
		return $msg;
	}

} // END class.DBUdate
?>
