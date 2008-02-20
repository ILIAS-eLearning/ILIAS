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

include_once("./classes/class.ilDBUpdate.php");

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
		$a_db_handler = 0,$tmp_flag = false)
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
			" WHERE component_type = ".$this->db->quote($this->ctype).
			" AND component_name = ".$this->db->quote($this->cname).
			" AND slot_id = ".$this->db->quote($this->slot_id).
			" AND name = ".$this->db->quote($this->pname);
		$set = $this->db->query($q);
		$rec = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->currentVersion = (int) $rec["db_version"];

		return $this->currentVersion;
	}

	/**
	* Set current DB version
	*/
	function setCurrentVersion($a_version)
	{
		$q = "UPDATE il_plugin SET db_version = ".$this->db->quote($a_version).
			" WHERE component_type = ".$this->db->quote($this->ctype).
			" AND component_name = ".$this->db->quote($this->cname).
			" AND slot_id = ".$this->db->quote($this->slot_id).
			" AND name = ".$this->db->quote($this->pname);
		$this->db->query($q);
		$this->currentVersion = $a_version;
		return true;
	}

	function loadXMLInfo()
	{
		// to do: reload control structure information for plugin
		return true;
	}

} // END class.DBUdate
?>
