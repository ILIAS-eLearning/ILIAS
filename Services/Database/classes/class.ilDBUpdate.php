<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		$this->LAST_UPDATE_FILE = $this->PATH."setup/sql/dbupdate_04.php";
   
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
		if ((int)$a_version > 4182)		// last number in previous file
		{
			return "dbupdate_04.php";
		}
		else if ((int)$a_version > 2948)		// last number in previous file
		{
			return "dbupdate_03.php";
		}
		else if ((int)$a_version > 864)		// last number in previous file
		{
			return "dbupdate_02.php";
		}
		else
		{
			return "dbupdate.php";
		}
	}
	
	/**
	* Init Step
	*/
	function initStep($i)
	{
		// 
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
		$set = new ilSetting("common", true);
		$this->currentVersion = (integer) $set->get("db_version");
		return $this->currentVersion;
	}

	function setCurrentVersion ($a_version)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$set = new ilSetting("common", true);
		$set->set("db_version", $a_version);
		$this->currentVersion = $a_version;
		
		return true;
	}
	
	/**
	 * Set running status for a step
	 *
	 * @param	int		step number
	 */
	function setRunningStatus($a_nr)
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$set = new ilSetting("common", true);
		$set->set("db_update_running", $a_nr);
		$this->db_update_running = $a_nr;
	}
	
	/**
	 * Get running status
	 *
	 * @return	int		current runnning db step
	 */
	function getRunningStatus()
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$set = new ilSetting("common", true);
		$this->db_update_running = (integer) $set->get("db_update_running");

		return $this->db_update_running;
	}
	
	/**
	 * Clear running status
	 */
	function clearRunningStatus()
	{
		include_once './Services/Administration/classes/class.ilSetting.php';
		$set = new ilSetting("common", true);
		$set->set("db_update_running", 0);
		$this->db_update_running = 0;
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
	function applyUpdate($a_break = 0)
	{
		global $ilCtrlStructureReader, $ilMySQLAbstraction;
		
		include_once './Services/Database/classes/class.ilMySQLAbstraction.php';

		$ilMySQLAbstraction = new ilMySQLAbstraction();
		$GLOBALS['ilMySQLAbstraction'] = $ilMySQLAbstraction;
		
		$f = $this->fileVersion;
		$c = $this->currentVersion;
		
		if ($a_break > $this->currentVersion &&
			$a_break < $this->fileVersion)
		{
			$f = $a_break;
		}

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
				
				$this->initStep($i);
				
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

		if ($f < $this->fileVersion)
		{
			return true;
		}
		else
		{
			return $this->loadXMLInfo();
		}
	}
	
	function loadXMLInfo()
	{
		global $ilCtrlStructureReader;
		
		// read module and service information into db
		require_once "./setup/classes/class.ilModuleReader.php";
		require_once "./setup/classes/class.ilServiceReader.php";
		require_once "./setup/classes/class.ilCtrlStructureReader.php";

		require_once "./Services/Component/classes/class.ilModule.php";
		require_once "./Services/Component/classes/class.ilService.php";
		$modules = ilModule::getAvailableCoreModules();
		$services = ilService::getAvailableCoreServices();

		$mr = new ilModuleReader("", "", "");
		$mr->clearTables();
		foreach($modules as $module)
		{
			$mr = new ilModuleReader(ILIAS_ABSOLUTE_PATH."/Modules/".$module["subdir"]."/module.xml",
				$module["subdir"], "Modules");
			$mr->getModules();
			unset($mr);
		}

		$sr = new ilServiceReader("", "", "");
		$sr->clearTables();
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
	function applyUpdateNr($nr, $hotfix = false, $custom_update = false)
	{
		global $ilDB,$ilErr,$ilUser,$ilCtrlStructureReader,$ilModuleReader,$ilMySQLAbstraction;

		//search for desired $nr
		reset($this->filecontent);
		
		if (!$hotfix)
		{
			$this->setRunningStatus($nr);
		}

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
					$code = implode("\n", $php);
					if (eval($code) === false)
					{
						$this->error = "Parse error: ".$code;
						return false;
					}
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
		if (!$hotfix && !$custom_update)
		{
			$this->setCurrentVersion($nr);
		}
		elseif($hotfix)
		{
			$this->setHotfixCurrentVersion($nr);
		}
		elseif($custom_update)
		{
			$this->setCustomUpdatesCurrentVersion($nr);
		}
		
		if (!$hotfix && !$custom_update)
		{
			$this->clearRunningStatus();
		}
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
	
	////
	//// Hotfix handling
	////
	

	/**
	 * Get current hotfix version
	 */
	function getHotfixCurrentVersion()
	{
		$this->readHotfixInfo();
		return $this->hotfix_current_version;
	}

	/**
	 * Set current hotfix version
	 */
	function setHotfixCurrentVersion($a_version)
	{
		$this->readHotfixInfo();
		$this->hotfix_setting->set("db_hotfixes_".
			$this->hotfix_version[0]."_".$this->hotfix_version[1], $a_version);
		$this->hotfix_current_version = $a_version;
		return true;
	}

	/**
	 * Get current hotfix version
	 */
	function getHotfixFileVersion()
	{
		$this->readHotfixInfo();
		return $this->hotfix_file_version;
	}

	/**
	 * Set current hotfix version
	 */
	function readHotfixFileVersion($a_file_content)
	{
		//go through filecontent and search for last occurence of <#x>
		reset($a_file_content);
		$regs = array();
		foreach ($a_file_content as $row)
		{
			if (ereg("^<#([0-9]+)>", $row, $regs))
			{
				$version = $regs[1];
			}
		}

		return (integer) $version;
	}

	/**
	 * Get status of hotfix file
	 */
	function readHotfixInfo($a_force = false)
	{
		if ($this->hotfix_info_read && !$a_force)
		{
			return;
		}
		include_once './Services/Administration/classes/class.ilSetting.php';
		$GLOBALS["ilDB"] = $this->db;
		$this->hotfix_setting = new ilSetting("common", true);
		$ilias_version = ILIAS_VERSION_NUMERIC;
		$version_array = explode(".", $ilias_version);
		$this->hotfix_version[0] = $version_array[0];
		$this->hotfix_version[1] = $version_array[1];
		$hotfix_file = $this->PATH."setup/sql/".$this->hotfix_version[0]."_".$this->hotfix_version[1]."_hotfixes.php";
		if (is_file($hotfix_file))
		{
			$this->hotfix_content = @file($hotfix_file);
			$this->hotfix_current_version = (int) $this->hotfix_setting->get("db_hotfixes_".
				$this->hotfix_version[0]."_".$this->hotfix_version[1]);
			$this->hotfix_file_version = $this->readHotfixFileVersion($this->hotfix_content);
		}
		$this->hotfix_info_read = true;
	}
	
	/**
	 * Get status of hotfix file
	 */
	function hotfixAvailable()
	{
		$this->readHotfixInfo();
		if ($this->hotfix_file_version > $this->hotfix_current_version)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Apply hotfix
	 */
	function applyHotfix()
	{
		global $ilCtrlStructureReader, $ilMySQLAbstraction;
		
		include_once './Services/Database/classes/class.ilMySQLAbstraction.php';

		$ilMySQLAbstraction = new ilMySQLAbstraction();
		$GLOBALS['ilMySQLAbstraction'] = $ilMySQLAbstraction;
		
		$this->readHotfixInfo(true);
		
		$f = $this->getHotfixFileVersion();
		$c = $this->getHotfixCurrentVersion();
		
		if ($c < $f)
		{
			$msg = array();
			for ($i=($c+1); $i<=$f; $i++)
			{
//				$this->initStep($i);	// nothings happens here
				
				$this->filecontent = $this->hotfix_content;
				
				if ($this->applyUpdateNr($i, true) == false)
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
						"msg" => "hotfix_applied",
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

	public function getCustomUpdatesCurrentVersion()
	{
		$this->readCustomUpdatesInfo();
		return $this->custom_updates_current_version;
	}

	public function setCustomUpdatesCurrentVersion($a_version)
	{
		$this->readCustomUpdatesInfo();
		$this->custom_updates_setting->set('db_version_custom', $a_version);
		$this->custom_updates_current_version = $a_version;
		return true;
	}

	public function getCustomUpdatesFileVersion()
	{
		$this->readCustomUpdatesInfo();
		return $this->custom_updates_file_version;
	}

	public function readCustomUpdatesFileVersion($a_file_content)
	{
		//go through filecontent and search for last occurence of <#x>
		reset($a_file_content);
		$regs = array();
		foreach ($a_file_content as $row)
		{
			if (ereg("^<#([0-9]+)>", $row, $regs))
			{
				$version = $regs[1];
			}
		}

		return (integer) $version;
	}

	public function readCustomUpdatesInfo($a_force = false)
	{
		if ($this->custom_updates_info_read && !$a_force)
		{
			return;
		}
		include_once './Services/Administration/classes/class.ilSetting.php';
		$GLOBALS["ilDB"] = $this->db;
		$this->custom_updates_setting = new ilSetting();
		$custom_updates_file = $this->PATH."setup/sql/dbupdate_custom.php";
		if (is_file($custom_updates_file))
		{
			$this->custom_updates_content = @file($custom_updates_file);
			$this->custom_updates_current_version = (int) $this->custom_updates_setting->get('db_version_custom', 0);
			$this->custom_updates_file_version = $this->readCustomUpdatesFileVersion($this->custom_updates_content);
		}
		$this->custom_updates_info_read = true;
	}

	public function customUpdatesAvailable()
	{
		// trunk does not support custom updates
//		return false;
		
		$this->readCustomUpdatesInfo();
		if ($this->custom_updates_file_version > $this->custom_updates_current_version)
		{
			return true;
		}
		return false;
	}

	public function applyCustomUpdates()
	{
		global $ilCtrlStructureReader, $ilMySQLAbstraction;

		include_once './Services/Database/classes/class.ilMySQLAbstraction.php';

		$ilMySQLAbstraction = new ilMySQLAbstraction();
		$GLOBALS['ilMySQLAbstraction'] = $ilMySQLAbstraction;

		$this->readCustomUpdatesInfo(true);

		$f = $this->getCustomUpdatesFileVersion();
		$c = $this->getCustomUpdatesCurrentVersion();

		if ($c < $f)
		{
			$msg = array();
			for ($i=($c+1); $i<=$f; $i++)
			{
//				$this->initStep($i);	// nothings happens here

				$this->filecontent = $this->custom_updates_content;

				if ($this->applyUpdateNr($i, false, true) == false)
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
						"msg" => "custom_update_applied",
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
	
	/**
	 * Get update steps as string (for presentation)
	 *
	 * @return string steps from the update file
	 */
	function getUpdateSteps($a_break = 0)
	{
		global $ilCtrlStructureReader, $ilMySQLAbstraction;
		
		$str = "";
		
		$f = $this->fileVersion;
		$c = $this->currentVersion;
		
		if ($a_break > $this->currentVersion &&
			$a_break < $this->fileVersion)
		{
			$f = $a_break;
		}

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
				
				$str.= $this->getUpdateStepNr($i);
			}

		}
		return $str;
	}

	/**
	 * Get hotfix steps
	 *
	 * @return string steps from the update file
	 */
	function getHotfixSteps()
	{
		$this->readHotfixInfo(true);
		
		$str = "";
		
		$f = $this->getHotfixFileVersion();
		$c = $this->getHotfixCurrentVersion();
		
		if ($c < $f)
		{
			$msg = array();
			for ($i=($c+1); $i<=$f; $i++)
			{
				$this->filecontent = $this->hotfix_content;
				
				$str.= $this->getUpdateStepNr($i, true);
			}
		}
		
		return $str;
	}
	
	
	/**
	 * Get single update step for presentation
	 */
	function getUpdateStepNr($nr, $hotfix = false, $custom_update = false)
	{
		global $ilDB,$ilErr,$ilUser,$ilCtrlStructureReader,$ilModuleReader,$ilMySQLAbstraction;

		$str = "";
		
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
			return false;
		}

		$i++;

		//update found, now extract this update to a new array
		$update = array();
		while ($i<count($this->filecontent) && !ereg("^<#".($nr+1).">", $this->filecontent[$i]))
		{
			$str.= $this->filecontent[$i];
			$i++;
		}

		return "<pre><b><#".$nr."></b>\n".htmlentities($str)."</pre>";
	}

} // END class.DBUdate
?>
