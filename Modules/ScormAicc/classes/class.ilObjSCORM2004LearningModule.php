<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


require_once "classes/class.ilObject.php";
//require_once "./Modules/ScormAicc/classes/class.ilObjSCORMValidator.php";
require_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

/**
* Class ilObjSCORMLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjSCORMLearningModule.php 13123 2007-01-29 13:57:16Z smeyer $
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModule extends ilObjSAHSLearningModule
{
	var $validator;
//	var $meta_data;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSCORM2004LearningModule($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "sahs";
		parent::ilObject($a_id,$a_call_by_reference);
	}


	/**
	* Validate all XML-Files in a SCOM-Directory
	*
	* @access       public
	* @return       boolean true if all XML-Files are wellfomred and valid
	*/
	function validate($directory)
	{
		//$this->validator =& new ilObjSCORMValidator($directory);
		//$returnValue = $this->validator->validate();
		return true;
	}

	function getValidationSummary()
	{
		if(is_object($this->validator))
		{
			return $this->validator->getSummary();
		}
		return "";
	}

	function getTrackingItems()
	{
		return ilObjSCORMLearningModule::_getTrackingItems($this->getId());
	}


	/**
	* get all tracking items of scorm object
	* @access static
	*/
	function _getTrackingItems($a_obj_id)
	{
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");
		$tree = new ilSCORMTree($a_obj_id);
		$root_id = $tree->readRootId();

		$items = array();
		$childs = $tree->getSubTree($tree->getNodeData($root_id));
		foreach($childs as $child)
		{
			if($child["type"] == "sit")
			{
				include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
				$sc_item =& new ilSCORMItem($child["obj_id"]);
				if ($sc_item->getIdentifierRef() != "")
				{
					$items[count($items)] =& $sc_item;
				}
			}
		}

		return $items;
	}

	/**
	* read manifest file
	* @access	public
	*/
	function readObject()
	{
		
		// the seems_utf8($str) function
		include_once("include/inc.utf8checker.php");
		$needs_convert = false;

		// convert imsmanifest.xml file in iso to utf8 if needed
		// include_once("include/inc.convertcharset.php");
		$manifest_file = $this->getDataDirectory()."/imsmanifest.xml";

		// check if manifestfile exists and space left on device...
		$check_for_manifest_file = is_file($manifest_file);

		
			
		// if no manifestfile
		if (!$check_for_manifest_file)
		{
			$this->ilias->raiseError($this->lng->txt("Manifestfile $manifest_file not found!"), $this->ilias->error_obj->MESSAGE);
			return;
		}

		
		if ($check_for_manifest_file)
		{
			$manifest_file_array = file($manifest_file);
			
			foreach($manifest_file_array as $mfa)
			{
					
				if (!seems_not_utf8($mfa))
				{
					$needs_convert = true;
					break;
				}
			}
						
			
							
			// to copy the file we need some extraspace, counted in bytes *2 ... we need 2 copies....
			$estimated_manifest_filesize = filesize($manifest_file) * 2;
			
			// i deactivated this, because it seems to fail on some windows systems (see bug #1795)
			//$check_disc_free = disk_free_space($this->getDataDirectory()) - $estimated_manifest_filesize;
			$check_disc_free = 2;
		}

		
	
		// if $manifest_file needs to be converted to UTF8
		if ($needs_convert)
		{
			// if file exists and enough space left on device
			if ($check_for_manifest_file && ($check_disc_free > 1))
			{

				// create backup from original
				if (!copy($manifest_file, $manifest_file.".old"))
				{
					echo "Failed to copy $manifest_file...<br>\n";
				}

				// read backupfile, convert each line to utf8, write line to new file
				// php < 4.3 style
				$f_write_handler = fopen($manifest_file.".new", "w");
				$f_read_handler = fopen($manifest_file.".old", "r");
				while (!feof($f_read_handler))
				{
					$zeile = fgets($f_read_handler);
					//echo mb_detect_encoding($zeile);
					fputs($f_write_handler, utf8_encode($zeile));
				}
				fclose($f_read_handler);
				fclose($f_write_handler);

				// copy new utf8-file to imsmanifest.xml
				if (!copy($manifest_file.".new", $manifest_file))
				{
					echo "Failed to copy $manifest_file...<br>\n";
				}

				if (!@is_file($manifest_file))
				{
					$this->ilias->raiseError($this->lng->txt("cont_no_manifest"),
					$this->ilias->error_obj->WARNING);
				}
			}
			else
			{
				// gives out the specific error

				if (!($check_disc_free > 1))
					$this->ilias->raiseError($this->lng->txt("Not enough space left on device!"),$this->ilias->error_obj->MESSAGE);
					return;
			}

		}
		else
		{
			// check whether file starts with BOM (that confuses some sax parsers, see bug #1795)
			$hmani = fopen($manifest_file, "r");
			$start = fread($hmani, 3);
			if (strtolower(bin2hex($start)) == "efbbbf")
			{
				$f_write_handler = fopen($manifest_file.".new", "w");
				while (!feof($hmani))
				{
					$n = fread($hmani, 900);
					fputs($f_write_handler, $n);
				}
				fclose($f_write_handler);
				fclose($hmani);

				// copy new utf8-file to imsmanifest.xml
				if (!copy($manifest_file.".new", $manifest_file))
				{
					echo "Failed to copy $manifest_file...<br>\n";
				}
			}
			else
			{
				fclose($hmani);
			}
		}

		//validate the XML-Files in the SCORM-Package
		if ($_POST["validate"] == "y")
		{
			if (!$this->validate($this->getDataDirectory()))
			{
				$this->ilias->raiseError("<b>Validation Error(s):</b><br>".$this->getValidationSummary(),
					$this->ilias->error_obj->WARNING);
			}
		}
			
		// start SCORM 2004 package parser - call by by bridge 
		include_once ("./Modules/Scorm2004/classes/ilSCORM13PackageBridge.php");
		$newPack = new ilSCORM13PackageBridge();
		return $newPack->il_import($this->getDataDirectory(),$this->getId());
	}


	/**
	* get all tracked items of current user
	*/
	function getTrackedItems()
	{
		global $ilUser, $ilDB, $ilUser;

		$query = "SELECT DISTINCT sco_id FROM scorm_tracking WHERE".
			" obj_id = ".$ilDB->quote($this->getId());

		$sco_set = $ilDB->query($query);

		$items = array();
		while($sco_rec = $sco_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
			$sc_item =& new ilSCORMItem($sco_rec["sco_id"]);
			if ($sc_item->getIdentifierRef() != "")
			{
				$items[count($items)] =& $sc_item;
			}
		}

		return $items;
	}

	function getTrackingDataPerUser($a_sco_id, $a_user_id)
	{
		global $ilDB;

		$query = "SELECT * FROM scorm_tracking WHERE".
			" obj_id = ".$ilDB->quote($this->getId()).
			" AND sco_id = ".$ilDB->quote($a_sco_id).
			" AND user_id = ".$ilDB->quote($a_user_id).
			" ORDER BY lvalue";
		$data_set = $ilDB->query($query);

		$data = array();
		while($data_rec = $data_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data[] = $data_rec;
		}

		return $data;
	}

	function getTrackingDataAgg($a_sco_id)
	{
		global $ilDB;

		// get all users with any tracking data
		$query = "SELECT DISTINCT user_id FROM scorm_tracking WHERE".
			" obj_id = ".$ilDB->quote($this->getId()).
			" AND sco_id = ".$ilDB->quote($a_sco_id);
			//" ORDER BY user_id, lvalue";
		$user_set = $ilDB->query($query);

		$data = array();
		while($user_rec = $user_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$query = "SELECT * FROM scorm_tracking WHERE".
				" obj_id = ".$ilDB->quote($this->getId()).
				" AND sco_id = ".$ilDB->quote($a_sco_id).
				" AND user_id =".$ilDB->quote($user_rec["user_id"]).
				" AND (lvalue =".$ilDB->quote("cmi.core.lesson_status").
				" OR lvalue =".$ilDB->quote("cmi.core.total_time").
				" OR lvalue =".$ilDB->quote("cmi.core.score.raw").")";
			$data_set = $ilDB->query($query);
			$score = $time = $status = "";
			while($data_rec = $data_set->fetchRow(DB_FETCHMODE_ASSOC))
			{
				switch($data_rec["lvalue"])
				{
					case "cmi.core.lesson_status":
						$status = $data_rec["rvalue"];
						break;

					case "cmi.core.total_time":
						$time = $data_rec["rvalue"];
						break;

					case "cmi.core.score.raw":
						$score = $data_rec["rvalue"];
						break;
				}
			}

			$data[] = array("user_id" => $user_rec["user_id"],
				"score" => $score, "time" => $time, "status" => $status);
		}

		return $data;
	}

} // END class.ilObjSCORMLearningModule
?>
