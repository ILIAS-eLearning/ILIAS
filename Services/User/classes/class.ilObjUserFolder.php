<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjUserFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";

define('USER_FOLDER_ID',7);

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
				$filename = $date."__".$inst_id."__xls_usrf.xls";
				break;
			case "userfolder_export_csv":
				$filename = $date."__".$inst_id."__csv_usrf.csv";
				break;
			case "userfolder_export_xml":
				$filename = $date."__".$inst_id."__xml_usrf.xml";
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

	function escapeXML($value)
	{
		$value = str_replace("&", "&amp;", $value);
		$value = str_replace("<", "&lt;", $value);
		$value = str_replace(">", "&gt;", $value);
		return $value;
	}

	function createXMLExport(&$settings, &$data, $filename)
	{
		include_once './Services/User/classes/class.ilUserDefinedData.php';
		include_once './Services/User/classes/class.ilObjUser.php';

		global $rbacreview;
		global $ilDB;
		global $log;

		$file = fopen($filename, "w");

		if (is_array($data))
		{
			  	include_once './Services/User/classes/class.ilUserXMLWriter.php';

			  	$xmlWriter = new ilUserXMLWriter();
			  	$xmlWriter->setObjects($data);
			  	$xmlWriter->setSettings($settings);
				$xmlWriter->setAttachRoles (true);

				if($xmlWriter->start())
				{
					fwrite($file, $xmlWriter->getXML());
				}
		}
	}


	/**
	 * Get all exportable user defined fields
	 */
	function getUserDefinedExportFields()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$udf_obj =& ilUserDefinedFields::_getInstance();

		$udf_ex_fields = array();
		foreach($udf_obj->getDefinitions() as $definition)
		{
			if ($definition["export"] != FALSE)
			{
				$udf_ex_fields[] = array("name" => $definition["field_name"],
					"id" => $definition["field_id"]);
			}
		}

		return $udf_ex_fields;
	}

	function createCSVExport(&$settings, &$data, $filename)
	{

		// header
		$headerrow = array();
		$udf_ex_fields = $this->getUserDefinedExportFields();
		foreach ($settings as $value)	// standard fields
		{
			array_push($headerrow, $this->lng->txt($value));
		}
		foreach ($udf_ex_fields as $f)	// custom fields
		{
			array_push($headerrow, $f["name"]);
		}

		$separator = ";";
		$file = fopen($filename, "w");
		$formattedrow =& ilUtil::processCSVRow($headerrow, TRUE, $separator);
		fwrite($file, join ($separator, $formattedrow) ."\n");
		foreach ($data as $row)
		{
			$csvrow = array();
			foreach ($settings as $header)	// standard fields
			{
				// multi-text
				if(is_array($row[$header]))
				{
					$row[$header] = implode(", ", $row[$header]);
				}
				
				array_push($csvrow, $row[$header]);
			}

			// custom fields
			reset($udf_ex_fields);
			if (count($udf_ex_fields) > 0)
			{
				include_once("./Services/User/classes/class.ilUserDefinedData.php");
				$udf = new ilUserDefinedData($row["usr_id"]);
				foreach ($udf_ex_fields as $f)	// custom fields
				{
					array_push($csvrow, $udf->get("f_".$f["id"]));
				}
			}

			$formattedrow =& ilUtil::processCSVRow($csvrow, TRUE, $separator);
			fwrite($file, join ($separator, $formattedrow) ."\n");
		}
		fclose($file);
	}

	function createExcelExport(&$settings, &$data, $filename, $a_mode)
	{
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($filename, FALSE);
		$workbook = $adapter->getWorkbook();
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

		$udf_ex_fields = $this->getUserDefinedExportFields();

		// title row
		foreach ($settings as $value)	// standard fields
		{
			if($value == 'ext_account')
			{
				$value = 'user_ext_account';
			}
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt($value), $a_mode), $format_title);
			$col++;
		}
		foreach ($udf_ex_fields as $f)	// custom fields
		{
			$worksheet->write($row, $col, ilExcelUtils::_convert_text($f["name"], $a_mode), $format_title);
			$col++;
		}

		$this->lng->loadLanguageModule("meta");
		foreach ($data as $index => $rowdata)
		{
			$row++;
			$col = 0;

			// standard fields
			foreach ($settings as $fieldname)
			{
				$value = $rowdata[$fieldname];
				switch ($fieldname)
				{
					case "language":
						$worksheet->write($row, $col, ilExcelUtils::_convert_text($this->lng->txt("meta_l_".$value), $a_mode));
						break;
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
						
					case "interests_general":
					case "interests_help_offered":
					case "interests_help_looking":
						if(is_array($value) && sizeof($value))
						{
							$value = implode(", ", $value);
						}									
						else
						{
							$value = null;
						}
						// fallthrough
						
					default:
						$worksheet->write($row, $col, ilExcelUtils::_convert_text($value, $a_mode));
						break;
				}
				$col++;
			}

			// custom fields
			reset($udf_ex_fields);
			if (count($udf_ex_fields) > 0)
			{
				include_once("./Services/User/classes/class.ilUserDefinedData.php");
				$udf = new ilUserDefinedData($rowdata["usr_id"]);
				foreach ($udf_ex_fields as $f)	// custom fields
				{
					$worksheet->write($row, $col, ilExcelUtils::_convert_text($udf->get("f_".$f["id"]), $a_mode));
					$col++;
				}
			}
		}
		$workbook->close();
	}

	/**
	 * getExport Settings
	 *
	 * @return array of exportable fields
	 */
	static function getExportSettings()
	{
		global $ilDB;

		$db_settings = array();
		
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->skipField("roles");
		$profile_fields = $up->getStandardFields();

		/*$profile_fields =& ilObjUserFolder::getProfileFields();
		$profile_fields[] = "preferences";*/

		$query = "SELECT * FROM settings WHERE ".
			$ilDB->like("keyword", "text", '%usr_settings_export_%');
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row["value"] == "1")
			{
				if (preg_match("/usr_settings_export_(.*)/", $row["keyword"], $setting))
				{
					array_push($db_settings, $setting[1]);
				}
			}
		}
		$export_settings = array();
		foreach ($profile_fields as $key => $value)
		{
			if (in_array($key, $db_settings))
			{
				if (strcmp($key, "password") == 0)
				{
					// we do not support password export with ILIAS >= 4.5.x
					continue;
				}
				else
				{
					array_push($export_settings, $key);
				}
			}
		}
		array_push($export_settings, "usr_id");
		array_push($export_settings, "login");
		array_push($export_settings, "last_login");
		array_push($export_settings, "last_update");
		array_push($export_settings, "create_date");
		array_push($export_settings, "time_limit_owner");
		array_push($export_settings, "time_limit_unlimited");
		array_push($export_settings, "time_limit_from");
		array_push($export_settings, "time_limit_until");
		array_push($export_settings, "time_limit_message");
		array_push($export_settings, "active");
		array_push($export_settings, "approve_date");
		array_push($export_settings, "agree_date");
		array_push($export_settings, "client_ip");
		array_push($export_settings, "auth_mode");
		array_push($export_settings, "ext_account");
		array_push($export_settings, "feedhash");
		
		return $export_settings;
	}

	/**
	* build xml export file
	*/
	function buildExportFile($a_mode = "userfolder_export_excel_x86", $user_data_filter = FALSE)
	{
		global $ilBench;
		global $log;
		global $ilDB;
		global $ilias;
		global $lng;

		//get Log File
		$expDir = $this->getExportDirectory();
		//$expLog = &$log;
		//$expLog->delete();
		//$expLog->setLogFormat("");
		//$expLog->write(date("[y-m-d H:i:s] ")."Start export of user data");

		// create export directory if needed
		$this->createExportDirectory();

		//get data
		//$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");
		$settings =& $this->getExportSettings();
		
		// user languages
		$query = "SELECT * FROM usr_pref WHERE keyword = ".$ilDB->quote('language','text');
		$res = $ilDB->query($query);
		$languages = array();
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$languages[$row['usr_id']] = $row['value'];
		}
		
		// multi-text
		$multi = array();
		$set = $ilDB->query("SELECT * FROM usr_data_multi");
		while($row = $ilDB->fetchAssoc($set))
		{			
			if(!is_array($user_data_filter) ||
				in_array($row["usr_id"], $user_data_filter))
			{
				$multi[$row["usr_id"]][$row["field_id"]][] = $row["value"];
			}					
		}			
		
		$data = array();
		$query = "SELECT usr_data.* FROM usr_data  ".
			" ORDER BY usr_data.lastname, usr_data.firstname";
		$result = $ilDB->query($query);
		while ($row = $ilDB->fetchAssoc($result))
		{
			if(isset($languages[$row['usr_id']]))
			{
				$row['language'] = $languages[$row['usr_id']];
			}
			else
			{
				$row['language'] = $lng->getDefaultLanguage();
			}
			
			if(isset($multi[$row["usr_id"]]))
			{
				$row = array_merge($row, $multi[$row["usr_id"]]);
			}		
			
			if (is_array($user_data_filter))
			{
				if (in_array($row["usr_id"], $user_data_filter)) array_push($data, $row);
			}
			else
			{
				array_push($data, $row);
			}
		}
		//$expLog->write(date("[y-m-d H:i:s] ")."User data export: build an array of all user data entries");

		$fullname = $expDir."/".$this->getExportFilename($a_mode);
		switch ($a_mode)
		{
			case "userfolder_export_excel_x86":
				$this->createExcelExport($settings, $data, $fullname, "latin1");
				break;
			case "userfolder_export_csv":
				$this->createCSVExport($settings, $data, $fullname);
				break;
			case "userfolder_export_xml":
				$this->createXMLExport($settings, $data, $fullname);
				break;
		}
		//$expLog->write(date("[y-m-d H:i:s] ")."Finished export of user data");

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

	
	/**
	 * Get profile fields (DEPRECATED, use ilUserProfile() instead)
	 *
	 * @return array of fieldnames
	 */
	static function &getProfileFields()
	{
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->skipField("username");
		$up->skipField("roles");
		$up->skipGroup("preferences");
		$fds = $up->getStandardFields();
		foreach ($fds as $k => $f)
		{
			$profile_fields[] = $k;
		}

		return $profile_fields;
	}

	function _writeNewAccountMail($a_lang, $a_subject, $a_sal_g, $a_sal_f, $a_sal_m, $a_body)
	{
		global $ilDB;
		
		if(self::_lookupNewAccountMail($a_lang))
		{
			$values = array(
				'subject'		=> array('text',$a_subject),
				'body'			=> array('clob',$a_body),
				'sal_g'			=> array('text',$a_sal_g),
				'sal_f'			=> array('text',$a_sal_f),
				'sal_m'			=> array('text',$a_sal_m)
				);
			$ilDB->update('mail_template',
				$values,
				array('lang' => array('text',$a_lang), 'type' => array('text','nacc'))
			);
		}
		else
		{
			$values = array(
				'subject'		=> array('text',$a_subject),
				'body'			=> array('clob',$a_body),
				'sal_g'			=> array('text',$a_sal_g),
				'sal_f'			=> array('text',$a_sal_f),
				'sal_m'			=> array('text',$a_sal_m),
				'lang'			=> array('text',$a_lang),
				'type'			=> array('text','nacc')
				);
			$ilDB->insert('mail_template',$values);
		}
	}
	
	function _updateAccountMailAttachment($a_lang, $a_tmp_name, $a_name)
	{
		global $ilDB;
		
		include_once "Services/User/classes/class.ilFSStorageUserFolder.php";
		$fs = new ilFSStorageUserFolder($this->getId());
		$fs->create();
		$path = $fs->getAbsolutePath()."/";
		
		move_uploaded_file($a_tmp_name, $path.$a_lang);		
		
		$ilDB->update('mail_template',
				array('att_file' => array('text', $a_name)),
				array('lang' => array('text',$a_lang), 'type' => array('text','nacc')));
	}
	
	function _deleteAccountMailAttachment($a_lang)
	{
		global $ilDB;
		
		include_once "Services/User/classes/class.ilFSStorageUserFolder.php";
		$fs = new ilFSStorageUserFolder($this->getId());
		$path = $fs->getAbsolutePath()."/";
		
		@unlink($path.$a_lang);
		
		$ilDB->update('mail_template',
				array('att_file' => array('text', '')),
				array('lang' => array('text',$a_lang), 'type' => array('text','nacc')));
	}

	function _lookupNewAccountMail($a_lang)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM mail_template ".
			" WHERE type='nacc' AND lang = ".$ilDB->quote($a_lang,'text'));

		if ($rec = $set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			return $rec;
		}
		return array();
	}
	
	/**
	 * Update user folder assignment
	 * Typically called after deleting a category with local user accounts.
	 * These users will be assigned to the global user folder. 
	 *
	 * @access public
	 * @static
	 *
	 * @param int old_id
	 * @param int new id
	 */
	public static function _updateUserFolderAssignment($a_old_id,$a_new_id)
	{
		global $ilDB;
		
		$query = "UPDATE usr_data SET time_limit_owner = ".$ilDB->quote($a_new_id, "integer")." ".
			"WHERE time_limit_owner = ".$ilDB->quote($a_old_id, "integer")." ";
		$ilDB->manipulate($query);
		
		return true;
	}
	

} // END class.ilObjUserFolder
?>
