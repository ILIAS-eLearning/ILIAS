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

	function createCSVExport(&$settings, &$data, $filename)
	{
		$headerrow = array();
		foreach ($settings as $value)
		{
			array_push($headerrow, $this->lng->txt($value));
		}
		$separator = ";";
		$file = fopen($filename, "w");
		$formattedrow =& ilUtil::processCSVRow($headerrow, TRUE, $separator);
		fwrite($file, join ($separator, $formattedrow) ."\n");
		foreach ($data as $row) 
		{
			$csvrow = array();
			foreach ($settings as $header)
			{
				array_push($csvrow, $row[$header]);
			}
			$formattedrow =& ilUtil::processCSVRow($csvrow, TRUE, $separator);
			fwrite($file, join ($separator, $formattedrow) ."\n");
		}
		fclose($file);
	}
	
	function createExcelExport(&$settings, &$data, $filename, $a_mode)
	{
		include_once './classes/Spreadsheet/Excel/Writer.php';
		include_once ("./classes/class.ilExcelUtils.php");
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer($filename);

		// sending HTTP headers
//		$workbook->send($filename);

		// Creating a worksheet
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		$worksheet =& $workbook->addWorksheet();
		$row = 0;
		$col = 0;
		
		foreach ($settings as $value)
		{
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt($value), $a_mode), $format_title);
			$col++;
		}


		foreach ($data as $index => $rowdata) 
		{
			$row++;
			$col = 0;
			foreach ($rowdata as $rowindex => $value)
			{
				switch ($settings[$rowindex])
				{
					case "time_limit_from":
					case "time_limit_until":
						$date = strftime("%Y-%m-%d %H:%M:%S", $value);
						if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $date, $matches))
						{
							$worksheet->write($row, $col, ilUtil::excelTime($matches[1],$matches[2],$matches[3],$matches[4],$matches[5],$matches[6]), $format_datetime);
						}
						break;
					case "last_login":
					case "last_update":
					case "create_date":
					case "approve_date":
					case "agree_date":
						if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $value, $matches))
						{
							$worksheet->write($row, $col, ilUtil::excelTime($matches[1],$matches[2],$matches[3],$matches[4],$matches[5],$matches[6]), $format_datetime);
						}
						break;
					default:
						$worksheet->write($row, $col, ilExcelUtils::_convert_text($value, $a_mode));
						break;
				}
				$col++;
			}
		}
		$workbook->close();
	}
	
	function getExportSettings()
	{
		global $ilDB;
		
		$db_settings = array();
		$profile_fields =& $this->getProfileFields();
		$query = "SELECT * FROM `settings` WHERE keyword LIKE 'usr_settings_export_%' AND value = '1'";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (preg_match("/usr_settings_export_(.*)/", $row["keyword"], $setting))
			{
				array_push($db_settings, $setting[1]);
			}
		}
		$export_settings = array();
		foreach ($profile_fields as $key => $value)
		{
			if (in_array($value, $db_settings))
			{
				if (strcmp($value, "password") == 0)
				{
					array_push($export_settings, "passwd");
				}
				else
				{
					array_push($export_settings, $value);
				}
			}
		}
		array_push($export_settings, "login");
		array_push($export_settings, "last_login");
		array_push($export_settings, "last_update");
		array_push($export_settings, "create_date");
		array_push($export_settings, "i2passwd");
		array_push($export_settings, "time_limit_owner");
		array_push($export_settings, "time_limit_unlimited");
		array_push($export_settings, "time_limit_from");
		array_push($export_settings, "time_limit_until");
		array_push($export_settings, "time_limit_message");
		array_push($export_settings, "active");
		array_push($export_settings, "approve_date");
		array_push($export_settings, "agree_date");
		array_push($export_settings, "ilinc_id");
		array_push($export_settings, "client_ip");
		return $export_settings;
	}
	
	/**
	* build xml export file
	*/
	function buildExportFile($a_mode = "userfolder_export_excel_x86")
	{
		global $ilBench;
		global $log;
		global $ilDB;
		global $ilias;
		
		//get Log File
		$expDir = $this->getExportDirectory();
		$expLog = &$log;
		$expLog->delete();
		$expLog->setLogFormat("");
		$expLog->write(date("[y-m-d H:i:s] ")."Start export of user data");

		// create export directory if needed
		$this->createExportDirectory();
		
		//get data
		$settings =& $this->getExportSettings();
		$data = array();
		$query = "SELECT * FROM usr_data ORDER BY lastname, firstname";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$datarow = array();
			foreach ($settings as $key => $value)
			{
				if (strcmp($value, "language") == 0)
				{
					$query = sprintf("SELECT value FROM usr_pref WHERE usr_id = %s AND keyword = %s",
						$ilDB->quote($row["usr_id"] . ""),
						$ilDB->quote($value)
					);
					$res = $ilDB->query($query);
					if ($res->numRows() == 1)
					{
						$prefrow = $res->fetchRow(DB_FETCHMODE_ASSOC);
						$row[$value] = $this->lng->txt("lang_".$prefrow["value"]);
					}
				}
				array_push($datarow, $row[$value]);
			}
			array_push($data, $datarow);
		}

		$fullname = $expDir."/".$this->getExportFilename($a_mode);
		switch ($a_mode)
		{
			case "userfolder_export_excel_x86":
				$this->createExcelExport($settings, $data, $fullname, "latin1");
				break;
			case "userfolder_export_excel_ppc":
				$this->createExcelExport($settings, $data, $fullname, "macos");
				break;
			case "userfolder_export_csv":
				$this->createCSVExport($settings, $data, $fullname);
				break;
		}
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
	
	function &getProfileFields()
	{
		$profile_fields = array(
			"gender",
			"firstname",
			"lastname",
			"title",
			"upload",
			"password",
			"institution",
			"department",
			"street",
			"zipcode",
			"city",
			"country",
			"phone_office",
			"phone_home",
			"phone_mobile",
			"fax",
			"email",
			"hobby",
			"referral_comment",
			"matriculation",
			"language",
			"skin_style",
			"hits_per_page",
			"show_users_online"
		);
		return $profile_fields;
	}
	
} // END class.ilObjUserFolder
?>
