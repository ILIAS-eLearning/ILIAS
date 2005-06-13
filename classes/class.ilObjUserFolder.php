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
* Class ilObjUserFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjUserFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjUserFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "usrf";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* copy all properties and subobjects of a userfolder.
	* DISABLED
	* @access	public
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		// DISABLED
		return false;

		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// put here userfolder specific stuff

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete userfolder and all related data	
	* DISABLED
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// DISABLED
		return false;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		// put here userfolder specific stuff
		
		// always call parent delete function at the end!!
		return true;
	}
	

	function getExportFilename($a_mode = "userfolder_export_excel_x86")
	{
		$filename = "";
		//$settings = $this->ilias->getAllSettings();
		//$this->inst_id = $settings["inst_id"];
		$inst_id = IL_INST_ID;

		$date = time();

		switch($a_mode)
		{
			case "userfolder_export_excel_x86":				
				$filename = $date."__".$inst_id."__x86_usrf.xls";
				break;
			case "userfolder_export_excel_ppc":				
				$filename = $date."__".$inst_id."__ppc_usrf.xls";
				break;
			case "userfolder_export_csv":				
				$filename = $date."__".$inst_id."__csv_usrf.csv";
				break;
		}
		return $filename;
	}
	
	
/**
* Get the location of the export directory for the user accounts
* 
* Get the location of the export directory for the user accounts
*
* @access	public
*/
	function getExportDirectory()
	{
		$export_dir = ilUtil::getDataDir()."/usrf_data/export";

		return $export_dir;
	}

/**
* Get a list of the already exported files in the export directory
* 
* Get a list of the already exported files in the export directory
*
* @return array A list of file names
* @access	public
*/
	function getExportFiles()
	{
		$dir = $this->getExportDirectory();
		
		// quit if export dir not available
		if (!@is_dir($dir) or
			!is_writeable($dir))
		{
			return array();
		}

		// open directory
		$dir = dir($dir);

		// initialize array
		$file = array();

		// get files and save the in the array
		while ($entry = $dir->read())
		{
			if ($entry != "." and
				$entry != ".." and
				preg_match("/^[0-9]{10}_{2}[0-9]+_{2}([a-z0-9]{3})_usrf\.[a-z]{1,3}\$/", $entry, $matches))
			{
				$filearray["filename"] = $entry;
				$filearray["filesize"] = filesize($this->getExportDirectory()."/".$entry);
				array_push($file, $filearray);
			}
		}

		// close import directory
		$dir->close();

		// sort files
		sort ($file);
		reset ($file);

		return $file;
	}

	
	/**
	* build xml export file
	*/
	function buildExportFile($a_mode = "userfolder_export_excel_x86")
	{
		return;
		global $ilBench;
		global $log;
		//get Log File
		$expDir = $this->getExportDirectory();
		$expLog = &$log;
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start export of user data");

		// create export directory if needed
		$this->createExportDirectory();
		
		//get data
		$data = array("abc", "def");
		//...
		//...
		$fullname = $expDir."/".$this->getExportFilename($a_mode);
		$file = fopen($fullname, "w");
		foreach ($data as $row) {
			fwrite($file, join (";",$row)."\n");
		}
		fclose($file);
		
		// end
		$expLog->write(date("[y-m-d H:i:s] ")."Finished export of user data");
	
		return $fullname;	
	}
	

	/**
	* creates data directory for export files
	* (data_dir/usrf_data/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		if (!@is_dir($this->getExportDirectory()))
		{
			$usrf_data_dir = ilUtil::getDataDir()."/usrf_data";
			ilUtil::makeDir($usrf_data_dir);
			if(!is_writable($usrf_data_dir))
			{
				$this->ilias->raiseError("Userfolder data directory (".$usrf_data_dir
					.") not writeable.",$this->ilias->error_obj->MESSAGE);
			}
			
			// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
			$export_dir = $usrf_data_dir."/export";
			ilUtil::makeDir($export_dir);
			if(!@is_dir($export_dir))
			{
				$this->ilias->raiseError("Creation of Userfolder Export Directory failed.",$this->ilias->error_obj->MESSAGE);
			}
		}
	}
	
} // END class.ilObjUserFolder
?>
