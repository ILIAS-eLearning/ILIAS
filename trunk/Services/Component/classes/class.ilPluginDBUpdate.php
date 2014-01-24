<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Database/classes/class.ilDBUpdate.php");

/**
* Database Update class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id: class.ilDBUpdate.php 15875 2008-02-03 13:56:32Z akill $
*/
class ilPluginDBUpdate extends ilDBUpdate
{
	/**
	* constructor
	*/
	function ilPluginDBUpdate($a_ctype, $a_cname, $a_slot_id, $a_pname,
		$a_db_handler, $tmp_flag, $a_db_prefix)
	{
		$this->db_prefix = $a_db_prefix;
		
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
				$this->PATH = "../";
			}
		}
		else
		{
			global $mySetup;
			$this->db = $mySetup->db;
			$this->PATH = "./";
		}
		
		$this->ctype = $a_ctype;
		$this->cname = $a_cname;
		$this->slot_id = $a_slot_id;
		$this->pname = $a_pname;
		
		include_once("./Services/Component/classes/class.ilPluginSlot.php");
		$this->slot_name = ilPluginSlot::lookupSlotName($this->ctype,
			$this->cname, $this->slot_id);
		
		$this->getCurrentVersion();
		
		// get update file for current version
		$updatefile = $this->getFileForStep($this->currentVersion + 1);

		$this->current_file = $updatefile;
		$this->DB_UPDATE_FILE = $this->PATH.
			ilPlugin::getDBUpdateScriptName($this->ctype, $this->cname,
			$this->slot_name, $this->pname);

		//
		// NOTE: multiple update files for plugins are not supported yet
		//
		$this->LAST_UPDATE_FILE = $this->PATH.
			ilPlugin::getDBUpdateScriptName($this->ctype, $this->cname,
			$this->slot_name, $this->pname);

		$this->readDBUpdateFile();
		$this->readLastUpdateFile();
		$this->readFileVersion();
	}
	
	/**
	* Get db update file name for db step
	*/
	function getFileForStep($a_version)
	{
		return "dbupdate.php";
	}
	
    /**
	* destructor
	* 
	* @return boolean
	*/
	function _DBUpdate()
	{
		// this may be used in setup!?
//		$this->db->disconnect();
	}

	/**
	* Get current DB version
	*/
	function getCurrentVersion()
	{
		$q = "SELECT db_version FROM il_plugin ".
			" WHERE component_type = ".$this->db->quote($this->ctype, "text").
			" AND component_name = ".$this->db->quote($this->cname, "text").
			" AND slot_id = ".$this->db->quote($this->slot_id, "text").
			" AND name = ".$this->db->quote($this->pname, "text");
		$set = $this->db->query($q);
		$rec = $this->db->fetchAssoc($set);

		$this->currentVersion = (int) $rec["db_version"];

		return $this->currentVersion;
	}

	/**
	* Set current DB version
	*/
	function setCurrentVersion($a_version)
	{
		$q = "UPDATE il_plugin SET db_version = ".$this->db->quote((int) $a_version, "integer").
			" WHERE component_type = ".$this->db->quote($this->ctype, "text").
			" AND component_name = ".$this->db->quote($this->cname, "text").
			" AND slot_id = ".$this->db->quote($this->slot_id, "text").
			" AND name = ".$this->db->quote($this->pname, "text");
		$this->db->manipulate($q);
		$this->currentVersion = $a_version;
		return true;
	}

	function loadXMLInfo()
	{
		// to do: reload control structure information for plugin
		return true;
	}
	
	/**
	* This is a very simple check. Could be done better.
	*/
	function checkQuery($q)
	{
		if ((is_int(stripos($q, "create table")) || is_int(stripos($q, "alter table")) ||
			is_int(stripos($q, "drop table")))
			&& !is_int(stripos($q, $this->db_prefix)))
		{
			return "Plugin may only create or alter tables that use prefix ".
				$this->db_prefix;
		}
		else
		{
			return true;
		}
	}


} // END class.DBUdate
?>
